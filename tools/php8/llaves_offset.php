<?php
// Convierte el acceso a posiciones con llaves ($s{0}) en corchetes ($s[0]); PHP 8 ya no lo admite.
for ($a = 1; $a < count($argv); $a++) {
    $f = $argv[$a]; $t = @token_get_all(file_get_contents($f)); $n = count($t); $c = 0;
    for ($i = 1; $i < $n; $i++) {
        if ($t[$i] !== '{') continue;
        $p = $t[$i - 1];
        $ok = (is_array($p) && $p[0] === T_VARIABLE) || $p === ']' || $p === '}' && false;
        if (is_array($p) && $p[0] === T_STRING && $i >= 2 && is_array($t[$i - 2]) && $t[$i - 2][0] === T_OBJECT_OPERATOR) $ok = true;
        if (!$ok) continue;
        $d = 0;
        for ($j = $i; $j < $n; $j++) {
            if ($t[$j] === '{' || (is_array($t[$j]) && in_array($t[$j][0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))) $d++;
            elseif ($t[$j] === '}') { $d--; if ($d === 0) break; }
        }
        if ($j >= $n) continue;
        $t[$i] = '['; $t[$j] = ']'; $c++;
    }
    if ($c) { $o = ''; foreach ($t as $x) $o .= is_array($x) ? $x[1] : $x; file_put_contents($f, $o); echo "$c $f\n"; }
}
