<?php
/**
 * Acceso centralizado a la configuración sensible del sistema
 * (credenciales de bases de datos, SMTP, etc.).
 *
 * Los valores reales viven en kconfig/config.local.php, que NO se versiona
 * (ver .gitignore). Para instalar: copiar kconfig/config.example.php a
 * kconfig/config.local.php y completar los valores.
 *
 * Compatible con PHP 5.4+ (no usa ??, tipos ni sintaxis moderna).
 */

if (!function_exists('kconfig')) {

    /**
     * Error de configuración: se registra el detalle en el log del servidor
     * y al usuario solo se le muestra un mensaje genérico.
     */
    function kconfig_falla($detalle)
    {
        error_log('[kconfig] ' . $detalle);
        if (!headers_sent()) {
            http_response_code(500);
        }
        die('Configuración del sistema no disponible. Contacte al administrador.');
    }

    /**
     * Devuelve toda la configuración o una sección de ella.
     */
    function kconfig($seccion = null)
    {
        static $cfg = null;

        if ($cfg === null) {
            $archivo = __DIR__ . '/config.local.php';
            if (!is_file($archivo)) {
                kconfig_falla('No existe ' . $archivo . ' (copie config.example.php)');
            }
            $cfg = require $archivo;
            if (!is_array($cfg)) {
                kconfig_falla($archivo . ' debe retornar un array');
            }
        }

        if ($seccion === null) {
            return $cfg;
        }
        return isset($cfg[$seccion]) ? $cfg[$seccion] : array();
    }

    /**
     * Datos de conexión de una base de datos definida en config.local.php.
     * Retorna array('host','port','dbname','user','password').
     */
    function kconfig_db($nombre = 'principal')
    {
        $dbs = kconfig('db');
        if (!isset($dbs[$nombre]) || !is_array($dbs[$nombre])) {
            kconfig_falla('Falta la conexión "' . $nombre . '" en config.local.php');
        }
        return array_merge(
            array('host' => '127.0.0.1', 'port' => 5432, 'dbname' => '', 'user' => '', 'password' => ''),
            $dbs[$nombre]
        );
    }

    /**
     * Cadena de conexión para pg_connect, con valores escapados.
     */
    function kconfig_pg_dsn($c)
    {
        $partes = array();
        foreach (array('host', 'port', 'dbname', 'user', 'password') as $k) {
            $v = str_replace(array('\\', "'"), array('\\\\', "\\'"), (string) $c[$k]);
            $partes[] = $k . "='" . $v . "'";
        }
        return implode(' ', $partes);
    }

    /**
     * Datos de una cuenta SMTP definida en config.local.php.
     * Retorna array('host','port','secure','user','password','from').
     */
    function kconfig_smtp($nombre = 'principal')
    {
        $cuentas = kconfig('smtp');
        if (!isset($cuentas[$nombre]) || !is_array($cuentas[$nombre])) {
            kconfig_falla('Falta la cuenta SMTP "' . $nombre . '" en config.local.php');
        }
        return array_merge(
            array('host' => '', 'port' => '587', 'secure' => 'tls', 'user' => '', 'password' => '', 'from' => ''),
            $cuentas[$nombre]
        );
    }
}

if (!function_exists('ksri_ambiente')) {
    /**
     * Ambiente del SRI para comprobantes electrónicos: '1' = PRUEBAS, '2' = PRODUCCIÓN.
     *
     * Lo decide la configuración del servidor (config.local.php), no la base de
     * datos ni el navegador, para que una copia de desarrollo o de pruebas nunca
     * envíe comprobantes reales:
     *   'sri' => array('ambiente' => 'pruebas')     // por defecto si no se configura
     *   'sri' => array('ambiente' => 'produccion')  // solo en el servidor de producción
     *   'sri' => array('ambiente' => 'bd')          // usar web_registro.ambiente de cada empresa
     */
    function ksri_ambiente($valor_bd = null)
    {
        $sri  = kconfig('sri');
        $modo = isset($sri['ambiente']) ? $sri['ambiente'] : 'pruebas';
        if ($modo === 'produccion') {
            return '2';
        }
        if ($modo === 'bd') {
            $v = trim((string) $valor_bd);
            return ($v === '2') ? '2' : '1';
        }
        return '1';
    }
}

if (!function_exists('ksri_permite_produccion')) {
    /** ¿Este servidor puede enviar comprobantes al ambiente de PRODUCCIÓN del SRI? */
    function ksri_permite_produccion()
    {
        $sri  = kconfig('sri');
        $modo = isset($sri['ambiente']) ? $sri['ambiente'] : 'pruebas';
        return $modo === 'produccion' || $modo === 'bd';
    }
}
