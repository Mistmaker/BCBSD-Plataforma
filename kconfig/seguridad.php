<?php
require_once __DIR__ . '/compat_php8.php'; // compatibilidad PHP 7/8

/**
 * Funciones de apoyo para validar datos que llegan de la petición.
 * Se carga desde kconfig/Db.class.php. Compatible con PHP 5.4+.
 */

if (!function_exists('knum')) {
    /**
     * Para valores que se insertan SIN comillas en SQL (ids, años, montos).
     * Devuelve el valor tal cual si es numérico; si no, '0'. Así una entrada
     * como "1 OR 1=1" no puede alterar la consulta.
     * Preferir siempre $bd->consulta($sql, $params) en código nuevo.
     */
    function knum($valor)
    {
        $v = trim((string) $valor);
        return is_numeric($v) ? $v : '0';
    }
}

if (!function_exists('kstr')) {
    /**
     * Literal de texto SQL seguro: agrega las comillas y duplica las internas.
     * Reemplaza el patrón inseguro  "campo = '".$valor."'"
     * por                           "campo = ".kstr($valor)
     * (válido con standard_conforming_strings = on, el valor por defecto de PostgreSQL).
     * Preferir siempre $bd->consulta($sql, $params) en código nuevo.
     */
    function kstr($valor)
    {
        return "'" . str_replace("'", "''", (string) $valor) . "'";
    }
}

if (!function_exists('kclave_guardar')) {
    /*
     * Contraseñas de usuarios (par_usuario.clave)
     * ------------------------------------------
     * Antes se guardaban en base64 (reversible = texto plano). Ahora se guardan
     * con password_hash (bcrypt, 60 caracteres). Mientras la columna no se haya
     * ampliado (sql/2026-11_fase1_clave_usuarios.sql) se sigue usando el formato
     * anterior, para que nada deje de funcionar. Tras ampliarla, cada usuario se
     * migra automáticamente la próxima vez que inicia sesión.
     */

    /** ¿La columna par_usuario.clave admite un hash de 60 caracteres? */
    function kclave_columna_ok()
    {
        static $ok = null;
        if ($ok === null) {
            $ok = false;
            $r = @kpg_query("SELECT character_maximum_length FROM information_schema.columns
                             WHERE table_schema = 'public' AND table_name = 'par_usuario' AND column_name = 'clave'");
            if ($r && kpg_num_rows($r) === 1) {
                $largo = kpg_fetch_result($r, 0, 0);
                $ok = ($largo === null || (int) $largo >= 60); // null = text/varchar sin límite
            }
        }
        return $ok;
    }

    function kclave_es_hash($guardada)
    {
        $g = trim((string) $guardada);
        return strpos($g, '$2y$') === 0 || strpos($g, '$2a$') === 0 || strpos($g, '$argon2') === 0;
    }

    /** Valor a guardar en par_usuario.clave para una contraseña nueva. */
    function kclave_guardar($plano)
    {
        return kclave_columna_ok() ? password_hash((string) $plano, PASSWORD_BCRYPT) : base64_encode((string) $plano);
    }

    /** Compara una contraseña con lo guardado (hash nuevo o base64 anterior). */
    function kclave_verificar($plano, $guardada)
    {
        $g = trim((string) $guardada);
        if ($g === '' || (string) $plano === '') {
            return false;
        }
        if (kclave_es_hash($g)) {
            return password_verify((string) $plano, $g);
        }
        return hash_equals($g, base64_encode((string) $plano));
    }
}

if (!function_exists('kmover_subida')) {
    /**
     * Reemplazo seguro de move_uploaded_file() (misma firma y retorno).
     *  - Rechaza nombres con extensiones ejecutables o de configuración en
     *    cualquier posición (x.php, x.php.jpg, .htaccess, x.phtml, x.svg...).
     *  - Quita del nombre cualquier componente de ruta.
     *  - La carpeta destino debe existir (algunas se configuran en la BD y
     *    pueden estar fuera de la carpeta web, lo cual es correcto).
     * Devuelve false (y registra el motivo) si el archivo no es aceptable.
     */
    function kmover_subida($temporal, $destino)
    {
        $prohibidas = array('php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
                            'cgi', 'pl', 'py', 'sh', 'bash', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd',
                            'htaccess', 'htpasswd', 'ini', 'html', 'htm', 'shtml', 'xhtml', 'svg', 'js');

        $carpeta = dirname((string) $destino);
        $nombre  = basename(str_replace('\\', '/', (string) $destino));
        $nombre  = preg_replace('/[\x00-\x1f]/', '', $nombre);

        $partes = explode('.', strtolower($nombre));
        array_shift($partes); // lo anterior al primer punto es el nombre, no una extensión
        foreach ($partes as $ext) {
            if (in_array(trim($ext), $prohibidas, true)) {
                error_log('[kmover_subida] Extensión no permitida: ' . $nombre);
                return false;
            }
        }
        // Windows/NTFS: "x.php::$DATA" o "x.php." se guardan como "x.php"
        if (strpos($nombre, ':') !== false || preg_match('/[. ]$/', $nombre)) {
            error_log('[kmover_subida] Nombre no permitido: ' . $nombre);
            return false;
        }
        if ($nombre === '' || $nombre[0] === '.') {
            error_log('[kmover_subida] Nombre no permitido: ' . $nombre);
            return false;
        }

        $real = realpath($carpeta);
        if ($real === false || !is_dir($real)) {
            error_log('[kmover_subida] Carpeta destino inexistente: ' . $carpeta);
            return false;
        }

        return move_uploaded_file($temporal, $real . DIRECTORY_SEPARATOR . $nombre);
    }
}

if (!function_exists('klogin_bloqueado')) {
    /*
     * Límite de intentos de login (pendiente P2).
     *
     * Tras N claves incorrectas para un mismo usuario (y, si se activa, muchas
     * desde una misma IP) dentro de la ventana, se bloquea el ingreso unos minutos.
     * El límite por IP viene desactivado: si los usuarios salen por una misma IP
     * pública o hay un proxy inverso, bloquearía a todos a la vez. Los contadores
     * se guardan en archivos de la carpeta temporal del servidor (no requiere
     * cambios en la base). Configurable en kconfig/config.local.php:
     *
     *   'seguridad' => array(
     *       'login_intentos'        => 5,   // por usuario
     *       'login_intentos_ip'     => 0,   // por dirección IP (0 = desactivado)
     *       'login_bloqueo_minutos' => 15,
     *   )
     */
    function klogin_config()
    {
        $c = function_exists('kconfig') ? kconfig('seguridad') : array();
        $c = is_array($c) ? $c : array();
        return array(
            'usuario' => isset($c['login_intentos']) ? max(1, (int) $c['login_intentos']) : 5,
            'ip'      => isset($c['login_intentos_ip']) ? max(0, (int) $c['login_intentos_ip']) : 0,
            'minutos' => isset($c['login_bloqueo_minutos']) ? max(1, (int) $c['login_bloqueo_minutos']) : 15,
        );
    }

    /** Claves de conteo para un intento: por usuario y por IP. */
    function klogin_claves($login)
    {
        $claves = array('usuario' => 'u_' . sha1(strtolower(trim((string) $login))));
        $c = klogin_config();
        if ($c['ip'] > 0) {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli';
            $claves['ip'] = 'i_' . sha1($ip);
        }
        return $claves;
    }

    function klogin_archivo($clave)
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bcbsd-login';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . DIRECTORY_SEPARATOR . $clave . '.json';
    }

    /**
     * Lee y opcionalmente modifica el registro de una clave, con bloqueo de archivo.
     * $cambio recibe el registro array('n' => fallos, 'desde' => t, 'hasta' => t) y lo devuelve.
     */
    function klogin_registro($clave, $cambio = null)
    {
        $vacio = array('n' => 0, 'desde' => 0, 'hasta' => 0);
        $f = @fopen(klogin_archivo($clave), 'c+');
        if (!$f) {
            return $vacio; // sin carpeta escribible no se bloquea (no impedir el ingreso)
        }
        flock($f, LOCK_EX);
        $r = json_decode(stream_get_contents($f), true);
        $r = is_array($r) ? array_merge($vacio, $r) : $vacio;
        if ($cambio !== null) {
            $r = $cambio($r);
            ftruncate($f, 0);
            rewind($f);
            fwrite($f, json_encode($r));
            fflush($f);
        }
        flock($f, LOCK_UN);
        fclose($f);
        return $r;
    }

    /** Segundos que faltan para poder intentar de nuevo (0 = puede intentar). */
    function klogin_bloqueado($login)
    {
        $ahora = time();
        $resta = 0;
        foreach (klogin_claves($login) as $clave) {
            $r = klogin_registro($clave);
            if ($r['hasta'] > $ahora) {
                $resta = max($resta, $r['hasta'] - $ahora);
            }
        }
        return $resta;
    }

    /** Registra una clave incorrecta. */
    function klogin_fallo($login)
    {
        $c = klogin_config();
        $ahora = time();
        $ventana = $c['minutos'] * 60;
        foreach (klogin_claves($login) as $tipo => $clave) {
            $limite = $c[$tipo];
            $r = klogin_registro($clave, function ($r) use ($ahora, $ventana, $limite) {
                if ($r['desde'] < $ahora - $ventana) {
                    $r = array('n' => 0, 'desde' => $ahora, 'hasta' => 0);
                }
                $r['n']++;
                if ($r['n'] >= $limite) {
                    $r['hasta'] = $ahora + $ventana;
                    $r['n'] = 0;
                    $r['desde'] = $ahora;
                }
                return $r;
            });
            if ($r['hasta'] > $ahora && $r['n'] === 0) {
                error_log('[auth] login bloqueado ' . $ventana / 60 . ' min (' . $tipo . '): '
                    . substr(trim((string) $login), 0, 60) . ' desde '
                    . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli'));
            }
        }
    }

    /** Ingreso correcto: reinicia el contador del usuario (el de la IP se mantiene). */
    function klogin_exito($login)
    {
        $claves = klogin_claves($login);
        @unlink(klogin_archivo($claves['usuario']));
    }
}
