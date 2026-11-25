<?php
/**
 * Validación del filtro del generador de reportes (kreportes).
 *
 * La pantalla (js/vista.js, GeneraConsulta) arma el filtro como texto SQL y el
 * servidor lo ejecutaba tal cual: cualquier usuario con sesión podía ejecutar SQL
 * arbitrario (pendiente P1). Ahora el servidor solo acepta las condiciones que la
 * pantalla genera, unidas con "and":
 *
 *     columna = 'texto'
 *     columna = 123
 *     columna like 'texto%'
 *     ( columna BETWEEN 'desde' AND 'hasta' )
 *
 * Cada columna debe estar registrada para la vista en ireport.ireport_vista y
 * los valores se vuelven a escapar. Cualquier otra cosa se rechaza.
 */

require_once __DIR__ . '/../../kconfig/seguridad.php';

if (!function_exists('ireport_identificador_ok')) {

    /** Nombre simple de tabla, vista, esquema o columna. */
    function ireport_identificador_ok($nombre)
    {
        return is_string($nombre) && preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,62}$/', $nombre) === 1;
    }

    /**
     * Columnas (icolumna) registradas para la vista: array(minúsculas => nombre real).
     */
    function ireport_columnas_vista($bd, $vista)
    {
        $columnas = array();
        $res = $bd->ejecutar('select icolumna from ireport.ireport_vista where ivista = ' . kstr(trim($vista)));
        while ($res && ($f = $bd->obtener_array($res))) {
            $c = trim($f[0]);
            if ($c !== '') {
                $columnas[strtolower($c)] = $c;
            }
        }
        return $columnas;
    }

    /**
     * Reconstruye el filtro con columnas permitidas y valores escapados.
     *
     * @param string $filtro   texto recibido del navegador
     * @param array  $columnas resultado de ireport_columnas_vista()
     * @return string|false    filtro seguro ('' si no hay filtro) o false si no es válido
     */
    function ireport_filtro_seguro($filtro, array $columnas)
    {
        $filtro = trim((string) $filtro);
        if ($filtro === '') {
            return '';
        }

        $id   = '([A-Za-z_][A-Za-z0-9_]*)';
        $txt  = "'((?:[^']|'')*)'";
        $formas = array(
            'entre'  => '/\G\(\s*' . $id . '\s+between\s+' . $txt . '\s+and\s+' . $txt . '\s*\)/i',
            'like'   => '/\G' . $id . '\s+like\s+' . $txt . '/i',
            'texto'  => '/\G' . $id . '\s*=\s*' . $txt . '/i',
            'numero' => '/\G' . $id . '\s*=\s*(-?\d+(?:\.\d+)?)(?![\w.])/i',
        );
        $sin_comillas = function ($v) {
            return str_replace("''", "'", $v);
        };

        $partes = array();
        $pos = 0;
        $largo = strlen($filtro);
        while (true) {
            $ok = false;
            foreach ($formas as $tipo => $patron) {
                if (!preg_match($patron, $filtro, $m, 0, $pos)) {
                    continue;
                }
                $clave = strtolower($m[1]);
                if (!isset($columnas[$clave])) {
                    error_log('[ireport] columna no registrada en el filtro: ' . $m[1]);
                    return false;
                }
                $col = $columnas[$clave];
                switch ($tipo) {
                    case 'entre':
                        $partes[] = '( ' . $col . ' BETWEEN ' . kstr($sin_comillas($m[2])) . ' AND ' . kstr($sin_comillas($m[3])) . ' )';
                        break;
                    case 'like':
                        $partes[] = $col . ' like ' . kstr($sin_comillas($m[2]));
                        break;
                    case 'texto':
                        $partes[] = $col . ' = ' . kstr($sin_comillas($m[2]));
                        break;
                    default:
                        $partes[] = $col . ' = ' . $m[2];
                }
                $pos += strlen($m[0]);
                $ok = true;
                break;
            }
            if (!$ok) {
                error_log('[ireport] filtro rechazado: ' . substr($filtro, 0, 300));
                return false;
            }
            if ($pos >= $largo) {
                break;
            }
            if (!preg_match('/\G\s+and\s+/i', $filtro, $m, 0, $pos)) {
                error_log('[ireport] filtro rechazado: ' . substr($filtro, 0, 300));
                return false;
            }
            $pos += strlen($m[0]);
        }
        return implode(' and ', $partes);
    }
}
