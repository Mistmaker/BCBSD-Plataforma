<?php
// Renombra el constructor estilo PHP 4 (método con el nombre de la clase) a __construct.
for ($a = 1; $a < count($argv); $a++) {
    $f = $argv[$a]; $src = file_get_contents($f); $t = @token_get_all($src); $n = count($t);
    $clase = null; $prof = 0; $prof_clase = -1; $tiene = false; $cand = array(); $cambios = 0;
    for ($i = 0; $i < $n; $i++) {
        $x = $t[$i];
        if ($x === '{' || (is_array($x) && in_array($x[0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))) { $prof++; continue; }
        if ($x === '}') { $prof--; if ($clase && $prof === $prof_clase) {
                if (!$tiene) foreach ($cand as $c) { $t[$c][1] = '__construct'; $cambios++; }
                $clase = null; } continue; }
        if (!is_array($x)) continue;
        if ($x[0] === T_CLASS) { $p = $i - 1; while ($p >= 0 && is_array($t[$p]) && $t[$p][0] === T_WHITESPACE) $p--;
            if ($p >= 0 && is_array($t[$p]) && $t[$p][0] === T_DOUBLE_COLON) continue;
            for ($j = $i + 1; $j < $n; $j++) if (is_array($t[$j]) && $t[$j][0] === T_STRING) { $clase = $t[$j][1]; break; }
            $prof_clase = $prof; $tiene = false; $cand = array(); }
        if ($clase && $prof === $prof_clase + 1 && $x[0] === T_FUNCTION) {
            for ($j = $i + 1; $j < $n; $j++) { if ($t[$j] === '(') break;
                if (is_array($t[$j]) && $t[$j][0] === T_STRING) {
                    if (strtolower($t[$j][1]) === '__construct') $tiene = true;
                    elseif (strtolower($t[$j][1]) === strtolower($clase)) $cand[] = $j;
                    break; } } }
    }
    if ($cambios) { $o = ''; foreach ($t as $x) $o .= is_array($x) ? $x[1] : $x; file_put_contents($f, $o); echo "$cambios $f\n"; }
}
