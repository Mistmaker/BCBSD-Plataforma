<?php
/**
 * Transacciones de base de datos: todo o nada.
 *
 * Una operación (p. ej. guardar o aprobar un asiento) escribe en varias tablas:
 * encabezado, detalle, auxiliares, saldos, estado de la certificación o del
 * trámite. Sin transacción, si algo falla a mitad, quedan datos a medias.
 *
 * 1. Automática por petición (rutas de ktx_rutas()): Db::conectar() abre una
 *    transacción y, al terminar la petición:
 *      - si no hubo errores, se CONFIRMA todo (también si el código termina con exit);
 *      - si hubo un error de SQL o un error fatal de PHP, se REVIERTE todo.
 *    Todos los objetos Db de la petición comparten la misma conexión
 *    (pg_connect con los mismos datos), así que todo queda dentro.
 *
 * 2. Explícita, para un flujo concreto:
 *        $bd->transaccion_iniciar();
 *        ... encabezado, detalle, saldos ...
 *        if (!$bd->transaccion_confirmar()) { echo 'No se guardó'; }
 *    o  $bd->transaccion_revertir('motivo');
 *    Las llamadas pueden anidarse: solo la más externa confirma.
 *
 * Configuración (kconfig/config.local.php):
 *   'transacciones' => array(
 *       'modo'    => 'activar',     // 'registrar': no abre transacciones, solo anota en el
 *                                   //              log "[transaccion]" lo que se habría revertido
 *                                   // 'desactivar': sin transacciones automáticas
 *       'rutas'   => array(...),    // rutas adicionales (patrones fnmatch)
 *       'excluir' => array(...),    // rutas a excluir
 *   )
 */

if (!function_exists('ktx_rutas')) {

    /** Scripts que trabajan dentro de una transacción automática. */
    function ktx_rutas()
    {
        $rutas = array(
            'kcontabilidad/model/*',
            'kpresupuesto/model/*',
            'kpersonal/model/*',
            'ktesoreria/model/*',
            'kcontabilidad/controller/*',
            'kpresupuesto/controller/*',
            'kpersonal/controller/*',
        );
        $c = ktx_config();
        return array_merge($rutas, $c['rutas']);
    }

    /**
     * Nunca en transacción automática: esperan respuesta de servicios externos
     * (SRI, correo) y bloquearían registros mientras tanto.
     */
    function ktx_excluidas()
    {
        $excluir = array(
            '*ComprobanteElectronico*', '*emite_fe*', '*genera_xml*', '*lote_xml*',
            '*autoriza*', '*firma*', '*Firma*', '*Correo*', '*correo*', '*email*', '*Email*',
            '*/ajax_inicio.php', '*resumen_panel*',
        );
        $c = ktx_config();
        return array_merge($excluir, $c['excluir']);
    }

    function ktx_config()
    {
        static $c = null;
        if ($c === null) {
            $t = function_exists('kconfig') ? kconfig('transacciones') : array();
            $t = is_array($t) ? $t : array();
            $c = array(
                'modo'    => isset($t['modo']) ? $t['modo'] : 'activar',
                'rutas'   => isset($t['rutas']) ? (array) $t['rutas'] : array(),
                'excluir' => isset($t['excluir']) ? (array) $t['excluir'] : array(),
            );
        }
        return $c;
    }

    /** Estado compartido de la petición. */
    function &ktx_estado()
    {
        static $e = array('link' => null, 'nivel' => 0, 'error' => false, 'auto' => false,
                          'cierre' => false, 'registrar' => false, 'ruta' => '');
        return $e;
    }

    function ktx_en_transaccion($link)
    {
        return $link && in_array(pg_transaction_status($link),
            array(PGSQL_TRANSACTION_INTRANS, PGSQL_TRANSACTION_INERROR, PGSQL_TRANSACTION_ACTIVE), true);
    }

    /** Un error de SQL dentro de la transacción: al final se revertirá. */
    function ktx_marcar_error()
    {
        $e = &ktx_estado();
        if ($e['nivel'] > 0 || $e['registrar']) {
            $e['error'] = true;
        }
    }

    function ktx_iniciar($link)
    {
        $e = &ktx_estado();
        if (!$link) {
            return false;
        }
        if ($e['nivel'] === 0) {
            if (pg_transaction_status($link) === PGSQL_TRANSACTION_IDLE) {
                if (!@pg_query($link, 'BEGIN')) {
                    error_log('[transaccion] no se pudo iniciar: ' . pg_last_error($link));
                    return false;
                }
            }
            $e['link']  = $link;
            $e['error'] = false;
        }
        $e['nivel']++;
        ktx_registrar_cierre();
        return true;
    }

    /** Confirma si es la transacción más externa. false si hubo errores (se revirtió). */
    function ktx_confirmar($link)
    {
        $e = &ktx_estado();
        if ($e['nivel'] <= 0) {
            return true;
        }
        $e['nivel']--;
        if ($e['nivel'] > 0) {
            return !$e['error'];
        }
        $e['auto'] = false;
        if ($e['error'] || pg_transaction_status($link) === PGSQL_TRANSACTION_INERROR) {
            ktx_deshacer($link, 'hubo un error de SQL');
            return false;
        }
        if (!@pg_query($link, 'COMMIT')) {
            error_log('[transaccion] no se pudo confirmar: ' . pg_last_error($link));
            ktx_deshacer($link, 'falló la confirmación');
            return false;
        }
        return true;
    }

    function ktx_revertir($link, $motivo = '')
    {
        $e = &ktx_estado();
        $e['nivel'] = 0;
        $e['auto']  = false;
        ktx_deshacer($link, $motivo !== '' ? $motivo : 'revertida por la aplicación', false);
    }

    function ktx_deshacer($link, $motivo, $avisar = true)
    {
        $e = &ktx_estado();
        if ($link && ktx_en_transaccion($link)) {
            @pg_query($link, 'ROLLBACK');
        }
        $e['nivel'] = 0;
        $e['error'] = false;
        error_log('[transaccion] REVERTIDA (' . $motivo . ') ' . ktx_descripcion());
        if ($avisar) {
            ktx_avisar_usuario();
        }
    }

    function ktx_descripcion()
    {
        return (isset($_SESSION['login']) ? trim($_SESSION['login']) : '?') . ' '
             . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI') . ' '
             . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '?'));
    }

    /** Mensaje visible en respuestas HTML (no en JSON, PDF, etc.). */
    function ktx_avisar_usuario()
    {
        // Solo cuando se enviaban datos: en una consulta (GET) no había nada que guardar
        if (PHP_SAPI === 'cli' || !isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }
        foreach (headers_list() as $h) {
            if (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') === false) {
                return;
            }
        }
        echo '<div style="margin:8px 0;padding:8px 12px;border:1px solid #d9534f;background:#f2dede;color:#a94442;font-weight:bold">'
           . 'La operación NO se guardó: ocurrió un error y se revirtieron todos los cambios. Intente de nuevo o comuníquese con el administrador.'
           . '</div>';
    }

    function ktx_registrar_cierre()
    {
        $e = &ktx_estado();
        if (!$e['cierre']) {
            $e['cierre'] = true;
            register_shutdown_function('ktx_cierre');
        }
    }

    /** Al terminar la petición: confirma si todo salió bien, revierte si hubo errores. */
    function ktx_cierre()
    {
        $e = &ktx_estado();
        $fatal = error_get_last();
        $hubo_fatal = $fatal && in_array($fatal['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true);

        if ($e['registrar']) {
            if ($e['error'] || $hubo_fatal) {
                error_log('[transaccion] (registrar) se habría REVERTIDO (' . ($hubo_fatal ? 'error fatal de PHP' : 'error de SQL') . ') ' . ktx_descripcion());
            }
            return;
        }
        $link = $e['link'];
        if ($e['nivel'] <= 0 || !$link || !ktx_en_transaccion($link)) {
            return;
        }
        if ($hubo_fatal) {
            ktx_deshacer($link, 'error fatal de PHP: ' . substr($fatal['message'], 0, 200));
            return;
        }
        if (!$e['auto']) {
            // transacción explícita que el código no confirmó (terminó antes con exit)
            error_log('[transaccion] la aplicación no confirmó la transacción explícita; se confirma al terminar. ' . ktx_descripcion());
        }
        $e['nivel'] = 1;
        ktx_confirmar($link);
    }

    /**
     * Transacción automática para la petición actual, si su ruta lo indica.
     * La llama Db::conectar().
     */
    function ktx_auto($link)
    {
        $e = &ktx_estado();
        if ($e['auto'] || $e['registrar'] || $e['nivel'] > 0 || PHP_SAPI === 'cli') {
            return;
        }
        $c = ktx_config();
        if ($c['modo'] === 'desactivar') {
            return;
        }
        $ruta = function_exists('kauth_script_relativo') ? kauth_script_relativo() : null;
        if ($ruta === null || !ktx_coincide($ruta, ktx_rutas()) || ktx_coincide($ruta, ktx_excluidas())) {
            return;
        }
        $e['ruta'] = $ruta;
        if ($c['modo'] === 'registrar') {
            $e['registrar'] = true;
            ktx_registrar_cierre();
            return;
        }
        if (ktx_iniciar($link)) {
            $e['auto'] = true;
        }
    }

    function ktx_coincide($ruta, array $patrones)
    {
        foreach ($patrones as $p) {
            if (fnmatch($p, $ruta) || fnmatch($p, basename($ruta))) {
                return true;
            }
        }
        return false;
    }
}
