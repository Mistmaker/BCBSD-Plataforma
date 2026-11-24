<?php
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
            $r = @pg_query("SELECT character_maximum_length FROM information_schema.columns
                             WHERE table_schema = 'public' AND table_name = 'par_usuario' AND column_name = 'clave'");
            if ($r && pg_num_rows($r) === 1) {
                $largo = pg_fetch_result($r, 0, 0);
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
