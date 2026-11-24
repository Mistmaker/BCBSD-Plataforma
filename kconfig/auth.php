<?php
/**
 * Guardia de sesión central.
 *
 * Se incluye automáticamente desde kconfig/Db.class.php, de modo que todo
 * script que accede a la base de datos exige un usuario autenticado, salvo
 * los puntos públicos listados en kauth_rutas_publicas().
 *
 * Configuración (kconfig/config.local.php):
 *   'seguridad' => array('auth' => 'bloquear')   // por defecto
 *   'seguridad' => array('auth' => 'registrar')  // solo anota en el log lo que bloquearía
 *
 * El modo 'registrar' sirve para desplegar sin riesgo: se revisa el log del
 * servidor buscando "[auth] SIN SESION" y, si aparece un acceso público
 * legítimo, se agrega a la lista blanca antes de pasar a 'bloquear'.
 *
 * Compatible con PHP 5.4+.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/seguridad.php';

if (!function_exists('kauth_verificar')) {

    /**
     * Scripts accesibles sin sesión (rutas relativas a la raíz de la aplicación).
     * Se admiten comodines estilo fnmatch: * y ?.
     */
    function kauth_rutas_publicas()
    {
        return array(
            // Inicio de sesión
            'kadmin/view/login.php',
            'kadmin/view/login_modelo.php',
            'kadmin/view/login_causa.php',
            'kadmin/controller/Controller-login.php',
            'kadmin/controller/Controller-login_causa.php',
            'kplanificacion/view/login.php',
            'kplanificacion/controller/Controller-login.php',
            'kconfig/captcha.php',
            // Comprobantes electrónicos enlazados desde los correos a clientes
            'facturae/ride/RideComprobante.php',
            'facturae/ride/ComprobantePdf.php',
            'facturae/ride/downloadXml.php',
        );
    }

    /**
     * Ruta del script en ejecución relativa a la raíz de la aplicación,
     * o null si no se puede determinar.
     */
    function kauth_script_relativo()
    {
        if (empty($_SERVER['SCRIPT_FILENAME'])) {
            return null;
        }
        $raiz   = realpath(dirname(__DIR__));
        $script = realpath($_SERVER['SCRIPT_FILENAME']);
        if ($raiz === false || $script === false || strpos($script, $raiz) !== 0) {
            return null;
        }
        return ltrim(str_replace('\\', '/', substr($script, strlen($raiz))), '/');
    }

    function kauth_es_publica($relativo)
    {
        foreach (kauth_rutas_publicas() as $patron) {
            if ($relativo === $patron || fnmatch($patron, $relativo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * URL del login, calculada a partir de la URL del script actual
     * (funciona aunque la aplicación esté en una subcarpeta del servidor).
     */
    function kauth_url_login($relativo)
    {
        $nombre = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $base   = '/';
        if ($relativo !== null && $nombre !== '' && substr($nombre, -strlen($relativo)) === $relativo) {
            $base = substr($nombre, 0, -strlen($relativo));
        }
        return $base . 'kadmin/view/login';
    }

    function kauth_verificar()
    {
        if (PHP_SAPI === 'cli') {
            return; // tareas programadas por línea de comandos
        }

        $relativo = kauth_script_relativo();
        if ($relativo !== null && kauth_es_publica($relativo)) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['usuario'])) {
            return;
        }

        $seguridad = function_exists('kconfig') ? kconfig('seguridad') : array();
        $modo      = isset($seguridad['auth']) ? $seguridad['auth'] : 'bloquear';

        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?';
        $ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?';
        error_log('[auth] SIN SESION (' . $modo . ') ' . $ip . ' ' . $uri);

        if ($modo === 'registrar') {
            return;
        }

        // Pantallas: redirigir al login. Endpoints (ajax, reportes, etc.): 401.
        $es_pantalla = $relativo !== null && strpos($relativo, '/view/') !== false
            && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest');

        if ($es_pantalla) {
            $login = kauth_url_login($relativo);
            if (!headers_sent()) {
                header('Location: ' . $login);
            } else {
                echo '<meta http-equiv="refresh" content="0; url=' . htmlspecialchars($login, ENT_QUOTES) . '">';
            }
            exit;
        }

        if (!headers_sent()) {
            http_response_code(401);
            header('Content-Type: text/plain; charset=UTF-8');
        }
        exit('Sesión no válida o expirada. Ingrese nuevamente al sistema.');
    }
}

kauth_verificar();
