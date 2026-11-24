<?php
/**
 * Convierte constantes no definidas usadas como valor (p. ej. claves de array
 * `campo => 1`, `$x[campo]`) en cadenas ('campo'). En PHP 7 una constante no
 * definida ya valía su propio nombre como cadena; en PHP 8 es un error fatal.
 * Uso: php comillas_constantes.php constantes.txt archivo1.php [archivo2.php ...]
 * Imprime "N archivo" por cada archivo modificado.
 */
$conocidas = array_flip(array_map('trim', file($argv[1])));
$siempre = array('true' => 1, 'false' => 1, 'null' => 1, 'self' => 1, 'parent' => 1, 'static' => 1);
$prev_no = array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_CLASS, T_EXTENDS, T_IMPLEMENTS,
    T_INSTANCEOF, T_CONST, T_USE, T_INTERFACE, T_TRAIT, T_NAMESPACE, T_GOTO, T_INSTEADOF, T_NS_SEPARATOR, T_AS);
if (defined('T_NULLSAFE_OBJECT_OPERATOR')) { $prev_no[] = T_NULLSAFE_OBJECT_OPERATOR; }
$ign = array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT);
for ($a = 2; $a < count($argv); $a++) {
    $f = $argv[$a];
    $src = file_get_contents($f);
    $t = @token_get_all($src);
    $n = count($t); $cambios = 0; $en_cadena = false; $llaves = 0;
    $sig = function ($i, $d) use ($t, $n, $ign) {
        for ($j = $i + $d; $j >= 0 && $j < $n; $j += $d) {
            if (is_array($t[$j]) && in_array($t[$j][0], $ign, true)) continue;
            return $j;
        }
        return -1;
    };
    for ($i = 0; $i < $n; $i++) {
        $tk = $t[$i];
        if ($tk === '"' && $llaves === 0) { $en_cadena = !$en_cadena; continue; }
        if ($en_cadena && is_array($tk) && ($tk[0] === T_CURLY_OPEN || $tk[0] === T_DOLLAR_OPEN_CURLY_BRACES)) { $llaves++; continue; }
        if ($en_cadena && $llaves > 0 && ($tk === '{')) { $llaves++; continue; }
        if ($en_cadena && $llaves > 0 && $tk === '}') { $llaves--; continue; }
        if (is_array($tk) && $tk[0] === T_START_HEREDOC) { $en_cadena = true; continue; }
        if (is_array($tk) && $tk[0] === T_END_HEREDOC) { $en_cadena = false; continue; }
        if (($en_cadena && $llaves === 0) || !is_array($tk) || $tk[0] !== T_STRING) continue;
        $nom = $tk[1];
        if (isset($conocidas[$nom]) || isset($siempre[strtolower($nom)])) continue;
        $p = $sig($i, -1); $s = $sig($i, +1);
        $pt = $p >= 0 ? $t[$p] : null; $st = $s >= 0 ? $t[$s] : null;
        if ($st === '(' || (is_array($st) && in_array($st[0], array(T_DOUBLE_COLON, T_VARIABLE, T_NS_SEPARATOR, T_ELLIPSIS), true))) continue;
        if ($st === '&') { $s2 = $sig($s, +1); if ($s2 >= 0 && is_array($t[$s2]) && $t[$s2][0] === T_VARIABLE) continue; } // tipo por referencia
        if (is_array($pt) && in_array($pt[0], $prev_no, true)) continue;
        if ($pt === '?' && is_array($st) && $st[0] === T_VARIABLE) continue;
        if ($pt === ':' ) { $pp = $sig($p, -1); if ($pp >= 0 && $t[$pp] === ')') continue; } // tipo de retorno
        if ($st === ':' && ($pt === ';' || $pt === '{' || $pt === '}')) continue; // etiqueta goto
        if ($pt === '#[' || (is_array($pt) && defined('T_ATTRIBUTE') && $pt[0] === T_ATTRIBUTE)) continue;
        $t[$i][1] = "'" . $nom . "'";
        $cambios++;
    }
    if ($cambios) {
        $out = '';
        foreach ($t as $x) { $out .= is_array($x) ? $x[1] : $x; }
        file_put_contents($f, $out);
        echo $cambios, ' ', $f, "\n";
    }
}
