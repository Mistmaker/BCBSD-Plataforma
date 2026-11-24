<?php
// Reemplaza llamadas a funciones (no métodos) según un mapa. Uso: php renombrar_funciones.php archivo...
$mapa = array('pg_query'=>'kpg_query','pg_query_params'=>'kpg_query_params','pg_fetch_assoc'=>'kpg_fetch_assoc','pg_fetch_array'=>'kpg_fetch_array','pg_fetch_row'=>'kpg_fetch_row',
  'pg_fetch_object'=>'kpg_fetch_object','pg_fetch_all'=>'kpg_fetch_all','pg_fetch_result'=>'kpg_fetch_result',
  'pg_num_rows'=>'kpg_num_rows','pg_num_fields'=>'kpg_num_fields','pg_field_name'=>'kpg_field_name',
  'pg_result_error'=>'kpg_result_error','pg_free_result'=>'kpg_free_result','count'=>'kcount','sizeof'=>'kcount',
  'number_format'=>'knumber_format','round'=>'kround','abs'=>'kabs','floor'=>'kfloor');
$ign = array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT);
for ($a = 1; $a < count($argv); $a++) {
    $f = $argv[$a]; $t = @token_get_all(file_get_contents($f)); $n = count($t); $c = 0;
    for ($i = 0; $i < $n; $i++) {
        if (!is_array($t[$i]) || $t[$i][0] !== T_STRING) continue;
        $nom = strtolower($t[$i][1]); if (!isset($mapa[$nom])) continue;
        $s = $i + 1; while ($s < $n && is_array($t[$s]) && in_array($t[$s][0], $ign, true)) $s++;
        if ($s >= $n || $t[$s] !== '(') continue;
        $p = $i - 1; while ($p >= 0 && is_array($t[$p]) && in_array($t[$p][0], $ign, true)) $p--;
        if ($p >= 0 && is_array($t[$p]) && in_array($t[$p][0], array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_NS_SEPARATOR), true)) continue;
        if ($p >= 0 && is_array($t[$p]) && defined('T_NULLSAFE_OBJECT_OPERATOR') && $t[$p][0] === T_NULLSAFE_OBJECT_OPERATOR) continue;
        $t[$i][1] = $mapa[$nom]; $c++;
    }
    if ($c) { $o = ''; foreach ($t as $x) $o .= is_array($x) ? $x[1] : $x; file_put_contents($f, $o); echo "$c $f\n"; }
}
