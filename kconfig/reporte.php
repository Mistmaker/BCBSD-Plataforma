<?php
/**
 * Formato institucional de reportes PDF (el de la orden de pago y los reportes de BCBSD-Api):
 *   - cabecera: bloque azul con área / documento / número, y el logo institucional;
 *   - el escudo como marca de agua;
 *   - pie institucional (dirección, teléfonos, web) con número de página;
 *   - vertical u horizontal, A4 u otro papel, márgenes configurables.
 *
 * Usa dompdf (kconfig/pdf.php). Las imágenes están optimizadas en kimages/reportes/
 * (82 KB en total; las anteriores pesaban hasta 2 MB y hacían lentos los reportes).
 *
 * Uso:
 *   require_once __DIR__ . '/../kconfig/reporte.php';
 *   $r = new KReporte(array(
 *       'area'       => 'DIRECCIÓN DE TALENTO HUMANO',
 *       'documento'  => 'ACCIÓN DE PERSONAL',
 *       'numero'     => 'Nº 0045-2026',
 *       'orientacion'=> 'P',                 // 'P' vertical | 'L' horizontal
 *       'archivo'    => 'accion_personal_0045',
 *   ));
 *   $r->agregar(kr_titulo('ACCIÓN DE PERSONAL'));
 *   $r->agregar(kr_datos(array('Apellidos' => $a, 'Nombres' => $n), 2));
 *   $r->agregar(kr_tabla(array('Partida', 'Valor'), $filas, array('', 'derecha')));
 *   $r->agregar(kr_firmas(array(array('nombre' => '...', 'cargo' => '...'))));
 *   $r->enviar();
 *
 * Los textos se escapan con kr_e(); lo que ya es HTML (p. ej. contenido guardado en
 * la base) se agrega tal cual con agregar().
 */

require_once __DIR__ . '/pdf.php';

if (!function_exists('kr_e')) {

    /** Escapa texto para el reporte. */
    function kr_e($v)
    {
        return htmlspecialchars(trim((string) $v), ENT_QUOTES, 'UTF-8');
    }

    /** Título centrado del cuerpo (banda gris). */
    function kr_titulo($texto, $subtitulo = '')
    {
        return '<div class="kr-titulo">' . kr_e($texto) . ($subtitulo !== '' ? '<div class="kr-subtitulo">' . kr_e($subtitulo) . '</div>' : '') . '</div>';
    }

    /** Encabezado de sección. */
    function kr_bloque($texto)
    {
        return '<div class="kr-bloque">' . kr_e($texto) . '</div>';
    }

    /**
     * Datos etiqueta: valor en una grilla de $columnas pares (1, 2 o 3).
     * Valores: texto (se escapa) o array('html' => '...') para HTML ya armado.
     */
    function kr_datos(array $pares, $columnas = 2)
    {
        $columnas = max(1, min(3, (int) $columnas));
        $ancho_e = $columnas === 1 ? 25 : ($columnas === 2 ? 17 : 12);
        $ancho_v = (100 - $ancho_e * $columnas) / $columnas;
        $h = '<table class="kr-datos">';
        $celdas = array();
        foreach ($pares as $etiqueta => $valor) {
            $v = is_array($valor) && isset($valor['html']) ? $valor['html'] : kr_e($valor);
            $celdas[] = '<td class="kr-etq" style="width:' . $ancho_e . '%">' . kr_e($etiqueta) . '</td>'
                      . '<td class="kr-val" style="width:' . $ancho_v . '%">' . ($v === '' ? '&nbsp;' : $v) . '</td>';
        }
        foreach (array_chunk($celdas, $columnas) as $fila) {
            while (count($fila) < $columnas) {
                $fila[] = '<td class="kr-etq"></td><td class="kr-val"></td>';
            }
            $h .= '<tr>' . implode('', $fila) . '</tr>';
        }
        return $h . '</table>';
    }

    /** Párrafo con título opcional (texto escapado; saltos de línea respetados). */
    function kr_texto($texto, $titulo = '', $es_html = false)
    {
        $cuerpo = $es_html ? (string) $texto : nl2br(kr_e($texto));
        return '<div class="kr-texto">' . ($titulo !== '' ? '<div class="kr-texto-titulo">' . kr_e($titulo) . '</div>' : '')
             . ($cuerpo === '' ? '&nbsp;' : $cuerpo) . '</div>';
    }

    /**
     * Tabla con encabezados. $alineacion por columna: '' | 'centro' | 'derecha'.
     * $pie: fila final opcional (array de celdas; se respeta colspan con array('texto','colspan')).
     */
    function kr_tabla(array $encabezados, array $filas, array $alineacion = array(), $pie = null, array $anchos = array())
    {
        $h = '<table class="kr-tabla"><thead><tr>';
        foreach ($encabezados as $i => $t) {
            $h .= '<th' . (isset($anchos[$i]) ? ' style="width:' . $anchos[$i] . '"' : '') . '>' . kr_e($t) . '</th>';
        }
        $h .= '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            $h .= '<tr>';
            foreach (array_values($fila) as $i => $v) {
                $cls = isset($alineacion[$i]) && $alineacion[$i] !== '' ? ' class="kr-' . $alineacion[$i] . '"' : '';
                $h .= '<td' . $cls . '>' . (is_array($v) && isset($v['html']) ? $v['html'] : kr_e($v)) . '</td>';
            }
            $h .= '</tr>';
        }
        if (!$filas) {
            $h .= '<tr><td colspan="' . count($encabezados) . '" class="kr-centro">Sin registros</td></tr>';
        }
        $h .= '</tbody>';
        if ($pie) {
            $h .= '<tfoot><tr>';
            $col = 0; // columna real (las celdas con colspan ocupan varias)
            foreach ($pie as $c) {
                list($t, $cs) = is_array($c) ? array($c[0], isset($c[1]) ? (int) $c[1] : 1) : array($c, 1);
                $al = isset($alineacion[$col + $cs - 1]) ? $alineacion[$col + $cs - 1] : '';
                $cls = $cs > 1 && $al === '' ? ' class="kr-derecha"' : ($al !== '' ? ' class="kr-' . $al . '"' : '');
                $h .= '<td' . ($cs > 1 ? ' colspan="' . $cs . '"' : '') . $cls . '>' . kr_e($t) . '</td>';
                $col += $cs;
            }
            $h .= '</tr></tfoot>';
        }
        return $h . '</table>';
    }

    /**
     * Firmas en columnas: cada una array('titulo' => 'ELABORADO POR', 'nombre' => '...', 'cargo' => '...', 'nota' => '...').
     */
    function kr_firmas(array $firmas, $por_fila = 2)
    {
        $por_fila = max(1, (int) $por_fila);
        $h = '';
        foreach (array_chunk($firmas, $por_fila) as $grupo) {
            $ancho = round(100 / count($grupo), 2);
            $h .= '<table class="kr-firmas"><tr>';
            foreach ($grupo as $f) {
                $h .= '<td style="width:' . $ancho . '%">'
                    . '<div class="kr-firma-titulo">' . (isset($f['titulo']) ? kr_e($f['titulo']) : '') . '</div>'
                    . '<div class="kr-firma-espacio"></div><div class="kr-firma-linea"></div>'
                    . '<div class="kr-firma-nombre">' . kr_e(isset($f['nombre']) ? $f['nombre'] : '') . '</div>'
                    . '<div class="kr-firma-cargo">' . kr_e(isset($f['cargo']) ? $f['cargo'] : '') . '</div>'
                    . (isset($f['nota']) && $f['nota'] !== '' ? '<div class="kr-firma-nota">' . kr_e($f['nota']) . '</div>' : '')
                    . '</td>';
            }
            $h .= '</tr></table>';
        }
        return $h;
    }

    /** Salto de página. */
    function kr_salto()
    {
        return '<div style="page-break-after:always"></div>';
    }

    /** "1234.5" -> "1.234,50" (formato de Ecuador). */
    function kr_dinero($v, $simbolo = '$ ')
    {
        return $simbolo . number_format((float) $v, 2, ',', '.');
    }

    /** '2026-11-05' -> '05 de noviembre de 2026'. */
    function kr_fecha_larga($fecha)
    {
        $t = $fecha ? strtotime((string) $fecha) : time();
        if (!$t) {
            return (string) $fecha;
        }
        $meses = array(1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        return date('d', $t) . ' de ' . $meses[(int) date('n', $t)] . ' de ' . date('Y', $t);
    }
}

if (!class_exists('KReporte', false)) {

    class KReporte
    {
        private $op;
        private $cuerpo = '';

        public function __construct(array $opciones = array())
        {
            $this->op = array_merge(array(
                'area'        => '',
                'documento'   => '',
                'numero'      => '',
                'orientacion' => 'P',          // P | L
                'papel'       => 'A4',
                'margen_sup'  => 34,           // mm (cabecera)
                'margen_inf'  => 33,           // mm (pie institucional: 31 mm)
                'margen_izq'  => 18,
                'margen_der'  => 18,
                'marca_agua'  => true,
                'pie'         => true,
                'titulo'      => '',
                'archivo'     => 'reporte',
                'css'         => '',           // estilos adicionales del reporte
            ), $opciones);
        }

        /** Agrega HTML al cuerpo. */
        public function agregar($html)
        {
            $this->cuerpo .= $html;
            return $this;
        }

        /** HTML completo (para depurar o para guardar). */
        public function html()
        {
            $o = $this->op;
            $horizontal = strtoupper($o['orientacion']) === 'L';
            list($ancho_pag, $alto_pag) = $horizontal ? array(297, 210) : array(210, 297);
            if (strtoupper($o['papel']) !== 'A4') {
                // otros papeles: medidas en mm
                $papeles = array('LETTER' => array(216, 279), 'LEGAL' => array(216, 356));
                $p = isset($papeles[strtoupper($o['papel'])]) ? $papeles[strtoupper($o['papel'])] : array(210, 297);
                list($ancho_pag, $alto_pag) = $horizontal ? array($p[1], $p[0]) : $p;
            }
            $ancho_util = $ancho_pag - $o['margen_izq'] - $o['margen_der'];
            $alto_pie = round(210 * 209 / 1400, 1); // la misma altura en vertical y horizontal (31,4 mm)
            $img_pie = $horizontal ? 'pie_horizontal.png' : 'pie.png';
            $img = kreporte_recurso('');
            $marca = (int) round(min($ancho_util * 0.62, 120));
            $marca_x = ($ancho_util - $marca) / 2;
            $marca_y = (($alto_pag - $o['margen_sup'] - $o['margen_inf']) - $marca * 1.01) / 2;

            $lineas = array();
            foreach (array('area', 'documento') as $k) {
                if (trim($o[$k]) !== '') {
                    $lineas[] = kr_e($o[$k]);
                }
            }
            if (trim($o['numero']) !== '') {
                $lineas[] = '<b>' . kr_e($o['numero']) . '</b>';
            }

            $css = '
                @page { margin: ' . $o['margen_sup'] . 'mm ' . $o['margen_der'] . 'mm ' . $o['margen_inf'] . 'mm ' . $o['margen_izq'] . 'mm; }
                body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #202020; }
                #kr-cabecera { position: fixed; top: -' . ($o['margen_sup'] - 7) . 'mm; left: 0; right: 0; height: 22mm; }
                #kr-cabecera table { width: 100%; border-collapse: collapse; }
                .kr-entidad { border-left: 2px solid #0d4378; padding: 2mm 0 2mm 3mm; color: #0d4378; font-size: 8.5pt;
                              line-height: 1.35; text-transform: uppercase; vertical-align: middle; }
                .kr-logo { text-align: right; vertical-align: middle; width: 62mm; }
                .kr-logo img { width: 54mm; }
                #kr-marca { position: fixed; top: ' . round($marca_y, 1) . 'mm; left: ' . round($marca_x, 1) . 'mm;
                            width: ' . $marca . 'mm; z-index: -1; }
                #kr-pie { position: fixed; bottom: -' . $o['margen_inf'] . 'mm; left: -' . $o['margen_izq'] . 'mm;
                          width: ' . $ancho_pag . 'mm; height: ' . $alto_pie . 'mm; }
                #kr-pie img { width: ' . $ancho_pag . 'mm; }
                .kr-titulo { background: #e3e7ee; color: #0d4378; font-size: 12pt; font-weight: bold; text-align: center;
                             padding: 2.2mm; margin: 0 0 3mm 0; text-transform: uppercase; }
                .kr-subtitulo { font-size: 9pt; font-weight: normal; color: #404040; margin-top: 1mm; text-transform: none; }
                .kr-bloque { background: #0d4378; color: #fff; font-size: 9pt; font-weight: bold; padding: 1.3mm 2mm;
                             margin: 3mm 0 0 0; text-transform: uppercase; }
                table.kr-datos { width: 100%; border-collapse: collapse; margin: 0 0 2mm 0; }
                table.kr-datos td { border: 0.6pt solid #b8c0cc; padding: 1.4mm 1.8mm; vertical-align: top; font-size: 9pt; }
                td.kr-etq { background: #f1f3f7; font-weight: bold; color: #303a4a; font-size: 8pt; text-transform: uppercase; }
                .kr-texto { border: 0.6pt solid #b8c0cc; padding: 2mm 2.5mm; margin: 0 0 2mm 0; text-align: justify; line-height: 1.4; }
                .kr-texto-titulo { font-weight: bold; color: #0d4378; font-size: 8.5pt; text-transform: uppercase; margin-bottom: 1mm; }
                table.kr-tabla { width: 100%; border-collapse: collapse; margin: 1mm 0 3mm 0; font-size: 8.5pt; }
                table.kr-tabla th { background: #0d4378; color: #fff; padding: 1.5mm; border: 0.6pt solid #0d4378; font-size: 8pt; }
                table.kr-tabla td { padding: 1.3mm 1.5mm; border: 0.6pt solid #b8c0cc; }
                table.kr-tabla tbody tr:nth-child(even) td { background: #f7f8fb; }
                table.kr-tabla tfoot td { font-weight: bold; background: #e3e7ee; }
                .kr-derecha { text-align: right; } .kr-centro { text-align: center; }
                table.kr-firmas { width: 100%; margin-top: 9mm; page-break-inside: avoid; }
                table.kr-firmas td { text-align: center; vertical-align: top; padding: 0 5mm; }
                .kr-firma-titulo { font-size: 8pt; font-weight: bold; color: #0d4378; text-transform: uppercase; height: 4mm; }
                .kr-firma-espacio { height: 13mm; }
                .kr-firma-linea { border-top: 0.7pt solid #202020; margin: 0 6mm 1.2mm 6mm; }
                .kr-firma-nombre { font-weight: bold; font-size: 9pt; text-transform: uppercase; }
                .kr-firma-cargo { font-size: 8pt; color: #404040; text-transform: uppercase; }
                .kr-firma-nota { font-size: 7pt; color: #606060; margin-top: 1mm; }
            ' . $o['css'];

            return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>'
                . kr_e($o['titulo'] !== '' ? $o['titulo'] : trim($o['documento'] . ' ' . $o['numero'])) . '</title>'
                . '<style>' . $css . '</style></head><body>'
                . '<div id="kr-cabecera"><table><tr><td class="kr-entidad">' . implode('<br>', $lineas) . '</td>'
                . '<td class="kr-logo"><img src="' . $img . 'logo_cabecera.png"></td></tr></table></div>'
                . ($o['marca_agua'] ? '<div id="kr-marca"><img src="' . $img . 'marca_agua.jpg" style="width:' . $marca . 'mm"></div>' : '')
                . ($o['pie'] ? '<div id="kr-pie"><img src="' . $img . $img_pie . '"></div>' : '')
                . '<main>' . $this->cuerpo . '</main></body></html>';
        }

        /** Genera el PDF y lo muestra en el navegador. */
        public function enviar($descargar = false)
        {
            $pdf = $this->generar();
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $archivo = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $this->op['archivo']) . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . ($descargar ? 'attachment' : 'inline') . '; filename="' . $archivo . '"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
            exit;
        }

        /** Contenido binario del PDF. */
        public function generar()
        {
            $o = $this->op;
            $dompdf = new KDompdf();
            $dompdf->setPaper($o['papel'], strtoupper($o['orientacion']) === 'L' ? 'landscape' : 'portrait');
            $dompdf->loadHtml($this->html());
            $dompdf->render();
            // número de página en el pie
            $canvas = $dompdf->getCanvas();
            $fuente = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
            $w = $canvas->get_width();
            $h = $canvas->get_height();
            // encima del pie institucional (la imagen del pie ocupa la franja inferior)
            $alto_pie = $o['pie'] ? (210 * 209 / 1400) * 72 / 25.4 : 0;
            $canvas->page_text($w - ($o['margen_der'] * 72 / 25.4) - 52, $h - $alto_pie - 4, 'Página {PAGE_NUM} de {PAGE_COUNT}', $fuente, 7, array(0.3, 0.3, 0.3));
            $titulo = $o['titulo'] !== '' ? $o['titulo'] : trim($o['documento'] . ' ' . $o['numero']);
            $dompdf->addInfo('Title', $titulo);
            $dompdf->addInfo('Author', isset($_SESSION['login']) ? trim($_SESSION['login']) : 'BCBSD');
            $dompdf->addInfo('Creator', 'BCBSD');
            return $dompdf->output();
        }
    }

    /** Ruta de una imagen de kimages/reportes/ (para dompdf, en el sistema de archivos). */
    function kreporte_recurso($nombre)
    {
        return str_replace('\\', '/', realpath(__DIR__ . '/../kimages/reportes')) . '/' . $nombre;
    }
}
