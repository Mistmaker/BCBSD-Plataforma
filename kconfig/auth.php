<?php
require_once __DIR__ . '/compat_php8.php'; // compatibilidad PHP 7/8

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
 * También protege contra CSRF (pendiente P3): ver kcsrf_verificar().
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
            'kconfig/sesion_estado.php',   // informa el estado de la sesión (no la renueva salvo que se pida)
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
        if ($raiz === false || $script === false) {
            return null;
        }
        $raiz   = str_replace('\\', '/', $raiz);
        $script = str_replace('\\', '/', $script);
        // Windows (XAMPP): C:\xampp y c:\xampp son la misma carpeta
        $windows = DIRECTORY_SEPARATOR === '\\';
        if (($windows ? stripos($script, $raiz . '/') : strpos($script, $raiz . '/')) !== 0) {
            return null;
        }
        return substr($script, strlen($raiz) + 1);
    }

    function kauth_es_publica($relativo)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $relativo = strtolower($relativo); // Windows no distingue mayúsculas en rutas
        }
        foreach (kauth_rutas_publicas() as $patron) {
            if (DIRECTORY_SEPARATOR === '\\') {
                $patron = strtolower($patron);
            }
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

    /** URL base de la aplicación (termina en /), aunque esté en una subcarpeta. */
    function kauth_url_base($relativo)
    {
        $login = kauth_url_login($relativo);
        return substr($login, 0, -strlen('kadmin/view/login'));
    }

    /** Minutos de inactividad tras los cuales vence la sesión. */
    function kauth_minutos_sesion()
    {
        static $minutos = null; // se calcula una vez, antes de que auth ajuste gc_maxlifetime
        if ($minutos === null) {
            $seguridad = function_exists('kconfig') ? kconfig('seguridad') : array();
            $minutos = !empty($seguridad['sesion_minutos'])
                ? (int) $seguridad['sesion_minutos']
                : (int) kfloor(((int) ini_get('session.gc_maxlifetime')) / 60);
            $minutos = max(5, $minutos);
        }
        return $minutos;
    }

    function kauth_es_ajax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        $acepta = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
        return $acepta !== '' && strpos($acepta, 'text/html') === false;
    }

    /**
     * Agrega el aviso de sesión (kconfig/sesion.js) a las páginas HTML
     * autenticadas, sin tener que editar el <head> de cada módulo.
     */
    function kauth_inyectar_aviso($relativo)
    {
        if (kauth_es_ajax() || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET')) {
            return;
        }
        $base   = kauth_url_base($relativo);
        $login  = isset($_SESSION['login']) ? trim($_SESSION['login']) : '';
        $script = '<script src="' . htmlspecialchars($base, ENT_QUOTES) . 'kconfig/sesion.js?v=4"'
                . ' data-base="' . htmlspecialchars($base, ENT_QUOTES) . '"'
                . ' data-login="' . htmlspecialchars($login, ENT_QUOTES) . '"'
                . ' data-minutos="' . kauth_minutos_sesion() . '"'
                . ' data-csrf="' . htmlspecialchars(kcsrf_token(), ENT_QUOTES) . '" defer></script>';
        $campo  = '<input type="hidden" name="_csrf" value="' . htmlspecialchars(kcsrf_token(), ENT_QUOTES) . '">';
        ob_start(function ($html) use ($script, $campo) {
            // Solo HTML: no tocar PDF, Excel, JSON, imágenes, etc.
            foreach (headers_list() as $h) {
                if (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') === false) {
                    return $html;
                }
            }
            if (stripos($html, 'kconfig/sesion.js') !== false) {
                return $html;
            }
            $pos = strripos($html, '</body>');
            if ($pos === false && stripos($html, '</head>') === false) {
                return $html; // fragmento (p. ej. respuesta ajax en HTML): no se modifica
            }
            // Token CSRF en los formularios POST de la página
            $con_token = preg_replace('/<form\b(?=[^>]*\bmethod\s*=\s*["\']?post\b)[^>]*>/i', '$0' . $campo, $html);
            if ($con_token !== null) {
                $html = $con_token;
            }
            $pos = strripos($html, '</body>');
            if ($pos === false) {
                $pos = stripos($html, '</head>');
            }
            if ($pos === false) {
                return $html; // fragmento (p. ej. respuesta ajax en HTML): no se modifica
            }
            return substr($html, 0, $pos) . $script . substr($html, $pos);
        });
    }

    /**
     * Dirección a la que volver tras iniciar sesión: solo rutas internas
     * ("/..."), nunca otro sitio (evita redirecciones abiertas).
     */
    function kauth_volver_valido($volver)
    {
        $v = (string) $volver;
        if ($v === '' || $v[0] !== '/' || strpos($v, '//') === 0 || strpos($v, '\\') !== false || preg_match('/[\x00-\x1f]/', $v)
            || stripos($v, 'Controller-login') !== false || stripos($v, '/view/login') !== false) {
            return '';
        }
        return $v;
    }

    /** Respuesta cuando no hay sesión (o venció). */
    function kauth_sin_sesion($relativo)
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        // Pantallas que se abren normalmente: al login, recordando a dónde volver.
        $es_pantalla = $relativo !== null && strpos($relativo, '/view/') !== false && !kauth_es_ajax();
        if ($es_pantalla) {
            $login = kauth_url_login($relativo) . '?volver=' . rawurlencode($uri);
            if (!headers_sent()) {
                header('Location: ' . $login);
            } else {
                echo '<meta http-equiv="refresh" content="0; url=' . htmlspecialchars($login, ENT_QUOTES) . '">';
            }
            exit;
        }

        if (!headers_sent()) {
            http_response_code(401);
            header('X-Sesion: expirada');
        }

        // Peticiones ajax: texto breve; kconfig/sesion.js las detecta por el 401
        // y ofrece volver a ingresar sin salir de la página.
        if (kauth_es_ajax()) {
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            exit('Su sesión expiró. Ingrese nuevamente su clave para continuar.');
        }

        // Formularios enviados sin ajax: explicar cómo recuperar lo escrito.
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        $login = htmlspecialchars(kauth_url_login($relativo), ENT_QUOTES);
        exit('<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Sesión expirada</title>'
           . '<meta name="viewport" content="width=device-width, initial-scale=1"></head>'
           . '<body style="font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#333">'
           . '<h2 style="color:#b94a48">Su sesión expiró</h2>'
           . '<p>Por seguridad, la sesión se cierra tras un tiempo sin actividad. <b>Lo que envió no se guardó.</b></p>'
           . '<ol><li><a href="' . $login . '" target="_blank" rel="noopener">Ingrese nuevamente en otra pestaña</a>.</li>'
           . '<li>Vuelva a esta pestaña y use <b>Atrás</b> del navegador (Alt + &larr;): normalmente el formulario conserva lo escrito.</li>'
           . '<li>Envíe de nuevo.</li></ol></body></html>');
    }

    /* ------------------------------------------------------------------
     * CSRF: que otro sitio no pueda enviar operaciones con la sesión del usuario.
     *
     *  - La cookie de sesión es SameSite=Lax: el navegador no la envía en
     *    peticiones de fondo (formularios, imágenes, ajax) iniciadas por otro sitio.
     *  - Cada sesión tiene un token. Se agrega solo a los formularios POST de las
     *    páginas (kauth_inyectar_aviso) y a las peticiones ajax (kconfig/sesion.js).
     *  - Toda petición POST/PUT/PATCH/DELETE con sesión se acepta si trae el token
     *    válido o si el navegador indica que viene de este mismo sitio (Origin o,
     *    sin él, Referer). Si no, se rechaza.
     *
     *   'seguridad' => array('csrf' => 'bloquear')   // por defecto
     *   'seguridad' => array('csrf' => 'registrar')  // solo anota "[csrf]" en el log
     *   'seguridad' => array('csrf_hosts' => array('erp.cbsd.gob.ec'))  // nombres públicos
     *                                                // si hay un proxy que cambia el Host
     * ------------------------------------------------------------------ */
    function kcsrf_token()
    {
        if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
            $_SESSION['csrf'] = function_exists('random_bytes')
                ? bin2hex(random_bytes(32))
                : sha1(uniqid(mt_rand(), true) . session_id());
        }
        return $_SESSION['csrf'];
    }

    /**
     * Nombres de host (con puerto si no es el estándar) con los que se accede a
     * la aplicación, tal como los envía el navegador en la cabecera Host.
     */
    function kcsrf_hosts()
    {
        $hosts = array();
        foreach (array('HTTP_HOST', 'HTTP_X_FORWARDED_HOST') as $k) {
            if (!empty($_SERVER[$k])) {
                foreach (explode(',', $_SERVER[$k]) as $h) {
                    $hosts[] = strtolower(trim($h));
                }
            }
        }
        $seguridad = function_exists('kconfig') ? kconfig('seguridad') : array();
        if (!empty($seguridad['csrf_hosts'])) {
            foreach ((array) $seguridad['csrf_hosts'] as $h) {
                $hosts[] = strtolower(trim($h));
            }
        }
        return $hosts;
    }

    /** ¿La URL (Origin o Referer) es de este mismo sitio? null si no se puede saber. */
    function kcsrf_mismo_sitio($url)
    {
        $url = trim((string) $url);
        if ($url === '' || $url === 'null') {
            return null;
        }
        $p = parse_url($url);
        if (empty($p['host'])) {
            return false;
        }
        // Origen = esquema + host + puerto: otro puerto del mismo host es otro sitio
        $esquema = isset($p['scheme']) ? strtolower($p['scheme']) : '';
        $puerto  = isset($p['port']) ? (int) $p['port'] : 0;
        $estandar = ($esquema === 'http' && $puerto === 80) || ($esquema === 'https' && $puerto === 443);
        $host = strtolower($p['host']) . ($puerto && !$estandar ? ':' . $puerto : '');
        foreach (kcsrf_hosts() as $h) {
            if ($h === $host || preg_replace('/:(80|443)$/', '', $h) === $host) {
                return true;
            }
        }
        return false;
    }

    function kcsrf_verificar()
    {
        $metodo = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        if (in_array($metodo, array('GET', 'HEAD', 'OPTIONS'), true)) {
            return;
        }
        $token  = kcsrf_token();
        $enviado = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN']
                 : (isset($_POST['_csrf']) ? $_POST['_csrf'] : '');
        if (is_string($enviado) && $enviado !== '' && hash_equals($token, $enviado)) {
            return;
        }
        $origen = kcsrf_mismo_sitio(isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '');
        if ($origen === null) {
            $origen = kcsrf_mismo_sitio(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
        }
        if ($origen === true) {
            return;
        }

        $seguridad = function_exists('kconfig') ? kconfig('seguridad') : array();
        $modo = isset($seguridad['csrf']) ? $seguridad['csrf'] : 'bloquear';
        error_log('[csrf] (' . $modo . ') ' . (isset($_SESSION['login']) ? trim($_SESSION['login']) : '?') . ' '
            . $metodo . ' ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?')
            . ' origen=' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '-')
            . ' referer=' . (isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 200) : '-'));
        if ($modo === 'registrar') {
            return;
        }
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden', true, 403);
            header('X-Csrf: rechazado');
            header('Content-Type: text/html; charset=UTF-8');
        }
        exit('<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Solicitud no verificada</title></head>'
           . '<body style="font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#333">'
           . '<h2 style="color:#b94a48">No se pudo verificar la solicitud</h2>'
           . '<p>La operación no se realizó porque no se pudo confirmar que fue enviada desde esta aplicación.</p>'
           . '<p>Vuelva a la página anterior, recárguela (F5) e intente de nuevo.</p></body></html>');
    }

    function kauth_verificar()
    {
        if (PHP_SAPI === 'cli') {
            return; // tareas programadas por línea de comandos
        }

        // Cookie de sesión: no accesible desde JavaScript y no enviada en
        // peticiones de fondo iniciadas por otros sitios (CSRF).
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.cookie_samesite', 'Lax'); // PHP 7.3+
        }

        $relativo = kauth_script_relativo();
        if ($relativo !== null && kauth_es_publica($relativo)) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @ini_set('session.gc_maxlifetime', (string) (kauth_minutos_sesion() * 60 + 300));
            @session_start();
        }

        // Vencimiento por inactividad, de forma predecible (no depende del
        // recolector de sesiones de PHP).
        if (!empty($_SESSION['usuario']) && !empty($_SESSION['ultima_actividad'])
            && time() - (int) $_SESSION['ultima_actividad'] > kauth_minutos_sesion() * 60) {
            error_log('[auth] Sesión vencida por inactividad: ' . (isset($_SESSION['login']) ? trim($_SESSION['login']) : '?'));
            $_SESSION = array();
        }

        if (!empty($_SESSION['usuario'])) {
            kcsrf_verificar();
            $_SESSION['ultima_actividad'] = time();
            kauth_inyectar_aviso($relativo);
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

        kauth_sin_sesion($relativo);
    }
}

kauth_verificar();
