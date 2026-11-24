<?php
/**
 * Compatibilidad PHP 7 / PHP 8.
 *
 * En PHP 8 varias funciones lanzan TypeError donde PHP 7 solo emitía una
 * advertencia y devolvía null (o 0/1 en count). El código heredado depende de
 * ese comportamiento en muchos lugares (p. ej. pg_fetch_assoc() sobre una
 * consulta que falló, count() de una variable no inicializada,
 * number_format('') con un campo vacío).
 *
 * Estas funciones reproducen el resultado de PHP 7.4:
 *  - En PHP 7 llaman directamente a la función original (sin cambios).
 *  - En PHP 8, si el argumento es inválido, registran el caso en el log y
 *    devuelven lo mismo que devolvía PHP 7, en lugar de detener la página.
 *
 * Se carga desde kconfig/config.php. Compatible con PHP 5.6+.
 */

if (!function_exists('kpg_resultado_valido')) {

    function kcompat_aviso($funcion, $detalle)
    {
        static $vistos = array();
        $clave = $funcion . '|' . $detalle;
        if (!isset($vistos[$clave])) {
            $vistos[$clave] = true;
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $donde = isset($bt[1]['file']) ? $bt[1]['file'] . ':' . $bt[1]['line'] : '?';
            error_log('[compat_php8] ' . $funcion . '(): ' . $detalle . ' en ' . $donde);
        }
    }

    /* ---------------- PostgreSQL: resultado de consulta ---------------- */

    function kpg_resultado_valido($r)
    {
        return is_resource($r) || (class_exists('PgSql\\Result', false) && $r instanceof \PgSql\Result);
    }

    function kpg_llamar($funcion, $args)
    {
        if (PHP_VERSION_ID < 80000 || kpg_resultado_valido(isset($args[0]) ? $args[0] : null)) {
            return call_user_func_array($funcion, $args);
        }
        kcompat_aviso($funcion, 'el resultado de la consulta no es válido (la consulta falló)');
        return null; // lo que devolvía PHP 7
    }

    function kpg_fetch_assoc()   { return kpg_llamar('pg_fetch_assoc', func_get_args()); }
    function kpg_fetch_array()   { return kpg_llamar('pg_fetch_array', func_get_args()); }
    function kpg_fetch_row()     { return kpg_llamar('pg_fetch_row', func_get_args()); }
    function kpg_fetch_object()  { return kpg_llamar('pg_fetch_object', func_get_args()); }
    function kpg_fetch_all()     { return kpg_llamar('pg_fetch_all', func_get_args()); }
    function kpg_fetch_result()  { return kpg_llamar('pg_fetch_result', func_get_args()); }
    function kpg_num_rows()      { return kpg_llamar('pg_num_rows', func_get_args()); }
    function kpg_num_fields()    { return kpg_llamar('pg_num_fields', func_get_args()); }
    function kpg_field_name()    { return kpg_llamar('pg_field_name', func_get_args()); }
    function kpg_result_error()  { return kpg_llamar('pg_result_error', func_get_args()); }
    function kpg_free_result()   { return kpg_llamar('pg_free_result', func_get_args()); }

    /* ---------------- PostgreSQL: consultas sobre una conexión ---------------- */

    function kpg_conexion_valida($c)
    {
        return is_resource($c) || (class_exists('PgSql\\Connection', false) && $c instanceof \PgSql\Connection);
    }

    /** pg_query($conexion, $sql) o pg_query($sql): si la conexión es inválida, PHP 7 devolvía null. */
    function kpg_query()
    {
        $a = func_get_args();
        if (PHP_VERSION_ID < 80000 || count($a) < 2 || kpg_conexion_valida($a[0])) {
            return call_user_func_array('pg_query', $a);
        }
        kcompat_aviso('pg_query', 'conexión inválida (no se llamó a conectar())');
        return null;
    }

    function kpg_query_params()
    {
        $a = func_get_args();
        if (PHP_VERSION_ID < 80000 || count($a) < 3 || kpg_conexion_valida($a[0])) {
            return call_user_func_array('pg_query_params', $a);
        }
        kcompat_aviso('pg_query_params', 'conexión inválida (no se llamó a conectar())');
        return null;
    }

    /* ---------------- count / sizeof ---------------- */

    function kcount($valor, $modo = COUNT_NORMAL)
    {
        if (is_array($valor) || $valor instanceof Countable) {
            return count($valor, $modo);
        }
        if (PHP_VERSION_ID < 80000) {
            return @count($valor, $modo);
        }
        kcompat_aviso('count', 'se pasó ' . gettype($valor) . ' en lugar de un array');
        return $valor === null ? 0 : 1; // lo que devolvía PHP 7
    }

    /* ---------------- funciones numéricas ---------------- */

    /**
     * Convierte un valor como lo hacía PHP 7 en funciones numéricas:
     * numérico -> tal cual; "12abc" -> 12 (dígitos iniciales); otro texto -> null.
     */
    function knum_php7($v, &$no_numerico)
    {
        $no_numerico = false;
        if ($v === null || is_bool($v)) {
            return (int) $v;
        }
        if (is_int($v) || is_float($v) || (is_string($v) && is_numeric($v))) {
            return $v;
        }
        if (is_string($v) && preg_match('/^\s*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/', $v, $m)) {
            return $m[0] + 0;
        }
        $no_numerico = true;
        return 0;
    }

    /**
     * $si_no_numerico: lo que devolvía PHP 7 cuando el argumento no era numérico:
     * 'null' (number_format) o 'cero' (round, floor, abs lo trataban como 0).
     */
    function knumerica($funcion, $args, $posiciones, $si_no_numerico)
    {
        if (PHP_VERSION_ID < 80000) {
            return call_user_func_array($funcion, $args);
        }
        foreach ($posiciones as $p) {
            if (!array_key_exists($p, $args)) {
                continue;
            }
            $orig = $args[$p];
            $args[$p] = knum_php7($orig, $no_numerico);
            if ($no_numerico || ($orig !== null && !is_numeric($orig) && !is_int($orig) && !is_float($orig) && !is_bool($orig))) {
                kcompat_aviso($funcion, 'valor no numérico ' . var_export($orig, true));
            }
            // El segundo argumento (decimales / precisión) siempre daba null en PHP 7 si no era numérico
            if ($no_numerico && ($si_no_numerico === 'null' || $p > 0)) {
                return null;
            }
        }
        if ($funcion === 'round' && array_key_exists(1, $args)) {
            $args[1] = (int) $args[1];
        }
        return call_user_func_array($funcion, $args);
    }

    function knumber_format() { return knumerica('number_format', func_get_args(), array(0, 1), 'null'); }
    function kround()         { return knumerica('round', func_get_args(), array(0, 1), 'cero'); }
    function kabs()           { return knumerica('abs', func_get_args(), array(0), 'cero'); }
    function kfloor()         { return knumerica('floor', func_get_args(), array(0), 'cero'); }
}

/* mysqli: desde PHP 8.1 lanza excepciones por defecto; PHP 7 devolvía false. */
if (PHP_VERSION_ID >= 80100 && function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

if (!function_exists('kigual')) {
    /**
     * Comparación "==" con las reglas de PHP 7 entre número y texto.
     * PHP 8 cambió 0 == '' (y 0 == 'abc') de true a false; los constructores de
     * listas (Obj.list.php) dependen del comportamiento anterior para marcar la
     * opción seleccionada.
     */
    function kigual($a, $b)
    {
        $na = is_int($a) || is_float($a);
        $nb = is_int($b) || is_float($b);
        if ($na && is_string($b) && !is_numeric($b)) {
            return $a == knum_php7($b, $ignorar);
        }
        if ($nb && is_string($a) && !is_numeric($a)) {
            return knum_php7($a, $ignorar) == $b;
        }
        return $a == $b;
    }
}
