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
                : (int) floor(((int) ini_get('session.gc_maxlifetime')) / 60);
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
        $script = '<script src="' . htmlspecialchars($base, ENT_QUOTES) . 'kconfig/sesion.js?v=1"'
                . ' data-base="' . htmlspecialchars($base, ENT_QUOTES) . '"'
                . ' data-login="' . htmlspecialchars($login, ENT_QUOTES) . '"'
                . ' data-minutos="' . kauth_minutos_sesion() . '" defer></script>';
        ob_start(function ($html) use ($script) {
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
