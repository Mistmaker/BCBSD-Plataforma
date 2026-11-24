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
