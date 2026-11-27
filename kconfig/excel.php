<?php
/**
 * Exportación a Excel real (.xlsx).
 *
 * Antes las exportaciones enviaban una tabla HTML con extensión .xls: Excel la
 * abre como "página web" (avisa que el formato no coincide), al guardarla queda
 * como HTML y los números se convierten según la configuración regional del
 * equipo (1.500 puede quedar como 1,5 o 1500; se pierden o agregan ceros).
 *
 * Ahora se genera un libro .xlsx con cada celda de su tipo:
 *   - números como números, con el formato de miles y decimales de la columna;
 *   - fechas como fechas (dd/mm/aaaa);
 *   - texto como texto (cédulas, RUC, cuentas, códigos: conservan los ceros a la izquierda).
 * Encabezado en negrita, fila fija y filtros.
 *
 * Uso:
 *   kexcel_enviar_resultado($resultado, 'personal');       // resultado de pg_query (tipos de la base)
 *   kexcel_enviar_html($html, 'kardex');                   // tabla(s) HTML (deduce los tipos)
 *   $x = new KXlsx(); $x->hoja('Datos', $encabezados, $filas, $tipos); $x->enviar('archivo');
 *
 * No requiere librerías ni la extensión zip: arma el archivo con zlib (incluida en PHP).
 */

if (!class_exists('KXlsx', false)) {

    class KXlsx
    {
        private $hojas  = array();
        private $estilos = array();   // clave de formato => índice de estilo
        private $formatos = array();  // código de formato personalizado => numFmtId

        // Estilos fijos
        const ESTILO_TEXTO = 1;
        const ESTILO_ENCABEZADO = 2;

        /**
         * Agrega una hoja.
         * @param string $nombre      nombre de la hoja
         * @param array  $encabezados textos de la primera fila (puede ser array vacío)
         * @param array  $filas       filas (arrays de valores ya tipados: int/float, string, KXlsxFecha, null)
         * @param array  $tipos       por columna: 'texto' | 'entero' | 'decimal:N' | 'fecha' | 'fechahora' | 'general'
         */
        public function hoja($nombre, array $encabezados, array $filas, array $tipos = array())
        {
            $nombre = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', (string) $nombre);
            $nombre = function_exists('mb_substr') ? mb_substr(trim($nombre) ?: 'Hoja', 0, 31, 'UTF-8') : substr(trim($nombre) ?: 'Hoja', 0, 31);
            $this->hojas[] = array($nombre, $encabezados, $filas, $tipos);
            return $this;
        }

        /** Envía el archivo al navegador y termina la petición. */
        public function enviar($nombre_archivo = 'exportacion')
        {
            $datos = $this->generar();
            while (ob_get_level() > 0) {
                ob_end_clean(); // descartar cualquier salida previa (espacios, avisos)
            }
            $archivo = kexcel_nombre_archivo($nombre_archivo);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
            header('Content-Disposition: attachment; filename="' . $archivo . '"; filename*=UTF-8\'\'' . rawurlencode($archivo), true);
            header('Content-Length: ' . strlen($datos), true);
            header('Cache-Control: private, max-age=0, must-revalidate', true);
            header('Pragma: public', true);
            echo $datos;
            exit;
        }

        /** Contenido binario del .xlsx. */
        public function generar()
        {
            if (!$this->hojas) {
                $this->hoja('Hoja1', array(), array());
            }
            $this->estilos = array();
            $this->formatos = array();
            $hojas_xml = array();
            foreach ($this->hojas as $h) {
                $hojas_xml[] = $this->hoja_xml($h[1], $h[2], $h[3]);
            }

            $zip = new KXlsxZip();
            $n = count($this->hojas);
            $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
            for ($i = 1; $i <= $n; $i++) {
                $ct .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            }
            $zip->agregar('[Content_Types].xml', $ct . '</Types>');
            $zip->agregar('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
                . '</Relationships>');
            $usuario = isset($_SESSION['login']) ? trim($_SESSION['login']) : '';
            $zip->agregar('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<dc:creator>' . self::x($usuario) . '</dc:creator>'
                . '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created>'
                . '</cp:coreProperties>');

            $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
            $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
            $usados = array();
            foreach ($this->hojas as $i => $h) {
                $nombre = $h[0];
                $base = $nombre;
                $k = 2;
                while (isset($usados[strtolower($nombre)])) {
                    $nombre = substr($base, 0, 28) . ' ' . $k++;
                }
                $usados[strtolower($nombre)] = true;
                $wb .= '<sheet name="' . self::x($nombre) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
                $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
                $zip->agregar('xl/worksheets/sheet' . ($i + 1) . '.xml', $hojas_xml[$i]);
            }
            $rels .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
            $zip->agregar('xl/workbook.xml', $wb . '</sheets></workbook>');
            $zip->agregar('xl/_rels/workbook.xml.rels', $rels . '</Relationships>');
            $zip->agregar('xl/styles.xml', $this->estilos_xml());
            return $zip->contenido();
        }

        /** Índice de estilo para un tipo de columna. */
        private function estilo($tipo)
        {
            if ($tipo === 'texto') {
                return self::ESTILO_TEXTO;
            }
            if ($tipo === 'general' || $tipo === '' || $tipo === null) {
                return 0;
            }
            if (!isset($this->estilos[$tipo])) {
                if ($tipo === 'entero') {
                    $codigo = '#,##0';
                } elseif ($tipo === 'fecha') {
                    $codigo = 'dd/mm/yyyy';
                } elseif ($tipo === 'fechahora') {
                    $codigo = 'dd/mm/yyyy hh:mm';
                } else { // decimal:N
                    $d = (int) substr($tipo, 8);
                    $codigo = $d > 0 ? '#,##0.' . str_repeat('0', min($d, 10)) : '#,##0';
                }
                if (!isset($this->formatos[$codigo])) {
                    $this->formatos[$codigo] = 164 + count($this->formatos);
                }
                $this->estilos[$tipo] = array(3 + count($this->estilos), $this->formatos[$codigo]);
            }
            return $this->estilos[$tipo][0];
        }

        private function estilos_xml()
        {
            $fmts = '';
            foreach ($this->formatos as $codigo => $id) {
                $fmts .= '<numFmt numFmtId="' . $id . '" formatCode="' . self::x($codigo) . '"/>';
            }
            $xfs = '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'                                         // 0 general
                 . '<xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'                  // 1 texto
                 . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment wrapText="1" vertical="center"/></xf>'; // 2 encabezado
            $orden = $this->estilos;
            uasort($orden, function ($a, $b) { return $a[0] - $b[0]; });
            foreach ($orden as $e) {
                $xfs .= '<xf numFmtId="' . $e[1] . '" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>';
            }
            return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . ($fmts ? '<numFmts count="' . count($this->formatos) . '">' . $fmts . '</numFmts>' : '')
                . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
                . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFD9E1F2"/><bgColor indexed="64"/></patternFill></fill></fills>'
                . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
                . '<border><left/><right/><top/><bottom style="thin"><color auto="1"/></bottom><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="' . (3 + count($this->estilos)) . '">' . $xfs . '</cellXfs>'
                . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
                . '</styleSheet>';
        }

        private function hoja_xml(array $encabezados, array $filas, array $tipos)
        {
            $ncol = count($encabezados);
            foreach ($filas as $f) {
                $ncol = max($ncol, count($f));
            }
            $anchos = array_fill(0, max($ncol, 1), 8);
            $estilos = array();
            for ($c = 0; $c < $ncol; $c++) {
                $estilos[$c] = $this->estilo(isset($tipos[$c]) ? $tipos[$c] : 'general');
            }

            $xml_filas = '';
            $r = 1;
            if ($encabezados) {
                $xml_filas .= '<row r="1">';
                foreach (array_values($encabezados) as $c => $v) {
                    $v = (string) $v;
                    $anchos[$c] = max($anchos[$c], min(50, self::largo($v) + 2));
                    $xml_filas .= '<c r="' . self::col($c) . '1" t="inlineStr" s="' . self::ESTILO_ENCABEZADO . '"><is><t xml:space="preserve">' . self::x($v) . '</t></is></c>';
                }
                $xml_filas .= '</row>';
                $r = 2;
            }
            foreach ($filas as $fila) {
                $xml_filas .= '<row r="' . $r . '">';
                foreach (array_values($fila) as $c => $v) {
                    if ($v === null || $v === '') {
                        continue;
                    }
                    $ref = self::col($c) . $r;
                    $s = isset($estilos[$c]) ? $estilos[$c] : 0;
                    if ($v instanceof KXlsxFecha) {
                        $xml_filas .= '<c r="' . $ref . '" s="' . $s . '"><v>' . $v->serial() . '</v></c>';
                        $anchos[$c] = max($anchos[$c], $v->hora ? 17 : 11);
                    } elseif (is_int($v) || is_float($v)) {
                        $num = is_float($v) ? rtrim(rtrim(sprintf('%.10F', $v), '0'), '.') : (string) $v;
                        $xml_filas .= '<c r="' . $ref . '" s="' . $s . '"><v>' . $num . '</v></c>';
                        $anchos[$c] = max($anchos[$c], min(20, strlen(number_format(abs((float) $v), 2)) + 2));
                    } else {
                        $v = (string) $v;
                        $xml_filas .= '<c r="' . $ref . '" t="inlineStr"' . ($s ? ' s="' . $s . '"' : '') . '><is><t xml:space="preserve">' . self::x($v) . '</t></is></c>';
                        $anchos[$c] = max($anchos[$c], min(60, self::largo($v) + 1));
                    }
                }
                $xml_filas .= '</row>';
                $r++;
            }
            $cols = '<cols>';
            foreach ($anchos as $c => $a) {
                $cols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $a . '" customWidth="1"/>';
            }
            $cols .= '</cols>';
            $ultima = self::col(max($ncol, 1) - 1) . max($r - 1, 1);
            $vista = $encabezados
                ? '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
                : '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
            return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<dimension ref="A1:' . $ultima . '"/>' . $vista . '<sheetFormatPr defaultRowHeight="15"/>' . $cols
                . '<sheetData>' . $xml_filas . '</sheetData>'
                . ($encabezados && $r > 2 ? '<autoFilter ref="A1:' . $ultima . '"/>' : '')
                . '</worksheet>';
        }

        /** Letra de columna (0 -> A, 26 -> AA). */
        public static function col($i)
        {
            $s = '';
            for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
                $s = chr(65 + ($i - 1) % 26) . $s;
            }
            return $s;
        }

        /** Texto seguro para XML (UTF-8 válido, sin caracteres de control). */
        public static function x($s)
        {
            $s = (string) $s;
            if (function_exists('mb_check_encoding') && !mb_check_encoding($s, 'UTF-8')) {
                $s = function_exists('kpdf_utf8_reparar') ? kpdf_utf8_reparar($s) : mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
            }
            $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
            return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        private static function largo($s)
        {
            return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
        }
    }

    /** Fecha para una celda (se guarda como número de serie de Excel). */
    class KXlsxFecha
    {
        public $y, $m, $d, $hora, $min, $seg;
        public function __construct($y, $m, $d, $hora = null, $min = 0, $seg = 0)
        {
            $this->y = (int) $y; $this->m = (int) $m; $this->d = (int) $d;
            $this->hora = $hora === null ? null : (int) $hora; $this->min = (int) $min; $this->seg = (int) $seg;
        }
        public function serial()
        {
            // días desde 1899-12-30 (base de Excel); sin depender de la extensión calendar
            $f = DateTime::createFromFormat('!Y-n-j', $this->y . '-' . $this->m . '-' . $this->d, new DateTimeZone('UTC'));
            $dias = (int) round($f->getTimestamp() / 86400) + 25569;
            if ($this->hora === null) {
                return (string) $dias;
            }
            return rtrim(rtrim(sprintf('%.8F', $dias + ($this->hora * 3600 + $this->min * 60 + $this->seg) / 86400), '0'), '.');
        }
    }

    /** ZIP mínimo (deflate con zlib), suficiente para un .xlsx. */
    class KXlsxZip
    {
        private $datos = '';
        private $central = '';
        private $n = 0;

        public function agregar($nombre, $contenido)
        {
            $crc = crc32($contenido);
            $comprimido = function_exists('gzdeflate') ? gzdeflate($contenido, 6) : $contenido;
            $metodo = function_exists('gzdeflate') ? 8 : 0;
            $offset = strlen($this->datos);
            $fecha = 0x21; // 1980-01-01
            $this->datos .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0x0800, $metodo, 0, $fecha, $crc,
                                 strlen($comprimido), strlen($contenido), strlen($nombre), 0) . $nombre . $comprimido;
            $this->central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, $metodo, 0, $fecha, $crc,
                                   strlen($comprimido), strlen($contenido), strlen($nombre), 0, 0, 0, 0, 0, $offset) . $nombre;
            $this->n++;
        }

        public function contenido()
        {
            return $this->datos . $this->central
                . pack('VvvvvVVv', 0x06054b50, 0, 0, $this->n, $this->n, strlen($this->central), strlen($this->datos), 0);
        }
    }
}

if (!function_exists('kexcel_enviar_resultado')) {

    /** Nombre de archivo seguro: "<nombre>_AAAA-MM-DD.xlsx". */
    function kexcel_nombre_archivo($nombre)
    {
        $nombre = preg_replace('/\.(xlsx?|csv)$/i', '', basename(str_replace('\\', '/', (string) $nombre)));
        $nombre = trim(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $nombre), '_');
        if ($nombre === '' || ctype_digit($nombre)) {
            $nombre = 'exportacion';
        }
        return $nombre . '_' . date('Y-m-d') . '.xlsx';
    }

    /** Encabezado legible a partir del nombre de columna ("fecha_nacimiento" -> "FECHA NACIMIENTO"). */
    function kexcel_titulo_columna($nombre)
    {
        $t = str_replace('_', ' ', trim((string) $nombre));
        return function_exists('mb_strtoupper') ? mb_strtoupper($t, 'UTF-8') : strtoupper($t);
    }

    /**
     * Envía como .xlsx el resultado de una consulta (pg_query / $bd->ejecutar),
     * usando el tipo de cada columna en la base de datos.
     */
    function kexcel_enviar_resultado($resultado, $nombre = 'exportacion', $hoja = 'Datos')
    {
        $x = new KXlsx();
        list($enc, $filas, $tipos) = kexcel_desde_resultado($resultado);
        $x->hoja($hoja, $enc, $filas, $tipos)->enviar($nombre);
    }

    /** Encabezados, filas tipadas y tipos de columna de un resultado de PostgreSQL. */
    function kexcel_desde_resultado($resultado)
    {
        $enc = array();
        $tipos_pg = array();
        $nc = $resultado ? kpg_num_fields($resultado) : 0;
        for ($i = 0; $i < $nc; $i++) {
            $enc[] = kexcel_titulo_columna(pg_field_name($resultado, $i));
            $tipos_pg[] = strtolower(pg_field_type($resultado, $i));
        }
        $filas = array();
        $decimales = array_fill(0, $nc, 0);
        while ($resultado && ($r = kpg_fetch_row($resultado))) {
            $fila = array();
            foreach ($r as $i => $v) {
                $fila[] = kexcel_valor_pg($v, $tipos_pg[$i], $decimales[$i]);
            }
            $filas[] = $fila;
        }
        $tipos = array();
        foreach ($tipos_pg as $i => $t) {
            if (in_array($t, array('int2', 'int4', 'int8', 'oid'), true)) {
                $tipos[] = 'entero';
            } elseif (in_array($t, array('numeric', 'float4', 'float8', 'money'), true)) {
                $tipos[] = 'decimal:' . $decimales[$i];
            } elseif ($t === 'date') {
                $tipos[] = 'fecha';
            } elseif (strpos($t, 'timestamp') === 0) {
                $tipos[] = 'fechahora';
            } else {
                $tipos[] = 'texto';
            }
        }
        return array($enc, $filas, $tipos);
    }

    /** Valor de PostgreSQL (texto) convertido al tipo de la celda. */
    function kexcel_valor_pg($v, $tipo, &$decimales)
    {
        if ($v === null) {
            return null;
        }
        if (in_array($tipo, array('int2', 'int4', 'int8', 'oid'), true)) {
            return is_numeric($v) ? (int) $v : $v;
        }
        if (in_array($tipo, array('numeric', 'float4', 'float8'), true)) {
            if (!is_numeric($v)) {
                return $v; // NaN, Infinity
            }
            $p = strpos($v, '.');
            if ($p !== false) {
                $decimales = max($decimales, min(10, strlen(rtrim(substr($v, $p + 1), ' '))));
            }
            return (float) $v;
        }
        if ($tipo === 'money') {
            $n = preg_replace('/[^\d\.\-]/', '', $v);
            $decimales = max($decimales, 2);
            return is_numeric($n) ? (float) $n : $v;
        }
        if ($tipo === 'date' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
            return new KXlsxFecha($m[1], $m[2], $m[3]);
        }
        if (strpos($tipo, 'timestamp') === 0 && preg_match('/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):?(\d{2})?/', $v, $m)) {
            return new KXlsxFecha($m[1], $m[2], $m[3], $m[4], $m[5], isset($m[6]) ? $m[6] : 0);
        }
        if ($tipo === 'bool') {
            return $v === 't' ? 'SI' : 'NO';
        }
        return rtrim($v); // character(n) viene con espacios al final
    }

    /**
     * Envía como .xlsx las tablas de un HTML (una hoja por tabla), deduciendo el
     * tipo de cada columna: número (1.234,56 o 1234.56), fecha (dd/mm/aaaa o
     * aaaa-mm-dd) o texto. Lo que parece número pero tiene ceros a la izquierda
     * o 10 o más dígitos (cédula, RUC, cuenta) queda como texto.
     */
    function kexcel_enviar_html($html, $nombre = 'exportacion')
    {
        $x = new KXlsx();
        foreach (kexcel_tablas_html($html) as $i => $t) {
            list($enc, $filas) = $t;
            list($filas, $tipos) = kexcel_tipar($filas);
            $x->hoja($i === 0 ? 'Datos' : 'Datos ' . ($i + 1), $enc, $filas, $tipos);
        }
        $x->enviar($nombre);
    }

    /**
     * Tablas de un HTML: array de array(encabezados, filas de texto).
     * Encabezados: la última fila de <thead> o, si no hay, la primera fila formada solo por <th>.
     * Las filas anteriores al encabezado (títulos del reporte) se conservan como filas de datos.
     */
    function kexcel_tablas_html($html)
    {
        $html = (string) $html;
        if (function_exists('mb_check_encoding') && !mb_check_encoding($html, 'UTF-8')) {
            $html = function_exists('kpdf_utf8_reparar') ? kpdf_utf8_reparar($html) : mb_convert_encoding($html, 'UTF-8', 'Windows-1252');
        }
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"?><html><body>' . $html . '</body></html>');
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $tablas = array();
        foreach ($dom->getElementsByTagName('table') as $tabla) {
            // solo tablas "hoja" (sin tablas dentro): las de maquetación se recorren por sus hijas
            if ($tabla->getElementsByTagName('table')->length > 0) {
                continue;
            }
            $enc = array();
            $filas = array();
            $pendiente = array(); // rowspan: columna => [filas restantes, texto]
            foreach ($tabla->getElementsByTagName('tr') as $tr) {
                $fila = array();
                $solo_th = true;
                $col = 0;
                foreach ($tr->childNodes as $celda) {
                    if (!($celda instanceof DOMElement) || !in_array(strtolower($celda->nodeName), array('td', 'th'), true)) {
                        continue;
                    }
                    while (isset($pendiente[$col]) && $pendiente[$col][0] > 0) {
                        $fila[$col] = '';
                        $pendiente[$col][0]--;
                        $col++;
                    }
                    if (strtolower($celda->nodeName) !== 'th') {
                        $solo_th = false;
                    }
                    $estilo = strtolower($celda->getAttribute('style') . ' ' . $celda->getAttribute('class'));
                    if (strpos(preg_replace('/\s+/', '', $estilo), 'display:none') !== false) {
                        continue; // columnas ocultas en pantalla
                    }
                    $texto = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $celda->textContent)));
                    $fila[$col] = $texto;
                    $span = max(1, (int) $celda->getAttribute('colspan'));
                    $rs = max(1, (int) $celda->getAttribute('rowspan'));
                    for ($k = 0; $k < $span; $k++) {
                        if ($k > 0) {
                            $fila[$col + $k] = '';
                        }
                        if ($rs > 1) {
                            $pendiente[$col + $k] = array($rs - 1, '');
                        }
                    }
                    $col += $span;
                }
                if (!$fila) {
                    continue;
                }
                ksort($fila);
                $fila = array_values($fila);
                $en_thead = $tr->parentNode && strtolower($tr->parentNode->nodeName) === 'thead';
                if (($en_thead || ($solo_th && !$filas)) && !$enc) {
                    $enc = $fila;
                } elseif ($en_thead) {
                    $filas[] = $enc; // varias filas de encabezado: la última queda como encabezado
                    $enc = $fila;
                } else {
                    $filas[] = $fila;
                }
            }
            if ($enc || $filas) {
                $tablas[] = array($enc, $filas);
            }
        }
        return $tablas ?: array(array(array(), array()));
    }

    /**
     * Convierte las celdas de texto a número o fecha por columna. El separador
     * decimal se decide por columna (1.234,56 frente a 1,234.56).
     */
    function kexcel_tipar(array $filas)
    {
        $ncol = 0;
        foreach ($filas as $f) {
            $ncol = max($ncol, count($f));
        }
        $tipos = array();
        for ($c = 0; $c < $ncol; $c++) {
            $coma = 0; $punto = 0; $numeros = 0; $fechas = 0; $no_vacias = 0;
            foreach ($filas as $f) {
                $v = isset($f[$c]) ? $f[$c] : '';
                if ($v === '') {
                    continue;
                }
                $no_vacias++;
                $limpio = preg_replace('/^\$\s?|\s?%$/', '', $v);
                if (kexcel_es_codigo($limpio)) {
                    continue;
                }
                if (preg_match('/^-?\(?\d{1,3}(\.\d{3})+(,\d+)?\)?$|^-?\d+,\d+$/', $limpio)) {
                    $coma++; $numeros++;
                } elseif (preg_match('/^-?\(?\d{1,3}(,\d{3})+(\.\d+)?\)?$|^-?\d+\.\d+$/', $limpio)) {
                    $punto++; $numeros++;
                } elseif (preg_match('/^-?\d+$/', $limpio)) {
                    $numeros++;
                } elseif (kexcel_fecha_texto($v)) {
                    $fechas++;
                }
            }
            if ($no_vacias === 0) {
                $tipos[$c] = 'general';
            } elseif ($fechas > 0 && $fechas >= $no_vacias * 0.6 && $numeros === 0) {
                $tipos[$c] = 'fecha';
                foreach ($filas as $f) {
                    $d = isset($f[$c]) && $f[$c] !== '' ? kexcel_fecha_texto($f[$c]) : null;
                    if ($d && $d->hora !== null) {
                        $tipos[$c] = 'fechahora';
                        break;
                    }
                }
            } elseif ($numeros > 0 && $numeros >= $no_vacias * 0.6) {
                $tipos[$c] = $coma >= $punto ? 'num_coma' : 'num_punto';
            } else {
                $tipos[$c] = 'texto';
            }
        }
        $dec = array_fill(0, $ncol, 0);
        foreach ($filas as &$f) {
            for ($c = 0; $c < $ncol; $c++) {
                $v = isset($f[$c]) ? $f[$c] : '';
                if ($v === '') {
                    $f[$c] = null;
                    continue;
                }
                if ($tipos[$c] === 'fecha' || $tipos[$c] === 'fechahora') {
                    $d = kexcel_fecha_texto($v);
                    $f[$c] = $d ?: $v;
                } elseif ($tipos[$c] === 'num_coma' || $tipos[$c] === 'num_punto') {
                    $n = kexcel_numero_texto($v, $tipos[$c] === 'num_coma', $decs);
                    if ($n === null) {
                        $f[$c] = $v; // p. ej. un subtítulo dentro de la columna
                    } else {
                        $f[$c] = $n;
                        $dec[$c] = max($dec[$c], $decs);
                    }
                }
            }
            ksort($f);
        }
        unset($f);
        $final = array();
        for ($c = 0; $c < $ncol; $c++) {
            $final[$c] = ($tipos[$c] === 'num_coma' || $tipos[$c] === 'num_punto') ? 'decimal:' . $dec[$c] : $tipos[$c];
        }
        return array($filas, $final);
    }

    /** Cédula, RUC, cuenta, código con ceros a la izquierda: se queda como texto. */
    function kexcel_es_codigo($v)
    {
        return (bool) preg_match('/^0\d+$|^\d{10,}$/', $v);
    }

    /** Número desde texto con separador decimal coma o punto; null si no es número. */
    function kexcel_numero_texto($v, $decimal_coma, &$decimales)
    {
        $decimales = 0;
        $s = trim(preg_replace('/^\$\s?|\s?%$/', '', $v));
        if (kexcel_es_codigo($s)) {
            return null;
        }
        $negativo = false;
        if (preg_match('/^\((.*)\)$/', $s, $m)) { // (1.234,56) contable
            $negativo = true;
            $s = $m[1];
        }
        if ($decimal_coma) {
            if (!preg_match('/^-?\d{1,3}(\.\d{3})*(,\d+)?$|^-?\d+(,\d+)?$/', $s)) {
                return null;
            }
            $s = str_replace(array('.', ','), array('', '.'), $s);
        } else {
            if (!preg_match('/^-?\d{1,3}(,\d{3})*(\.\d+)?$|^-?\d+(\.\d+)?$/', $s)) {
                return null;
            }
            $s = str_replace(',', '', $s);
        }
        $p = strpos($s, '.');
        $decimales = $p === false ? 0 : strlen($s) - $p - 1;
        $n = $p === false && strlen(ltrim($s, '-')) < 16 ? (int) $s : (float) $s;
        return $negativo ? -$n : $n;
    }

    /** Fecha desde texto (dd/mm/aaaa, dd-mm-aaaa, aaaa-mm-dd, con hora opcional) o null. */
    function kexcel_fecha_texto($v)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/', $v, $m)) {
            list($y, $mes, $d) = array($m[1], $m[2], $m[3]);
        } elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?: (\d{2}):(\d{2})(?::(\d{2}))?)?$/', $v, $m)) {
            list($d, $mes, $y) = array($m[1], $m[2], $m[3]);
        } else {
            return null;
        }
        if (!checkdate((int) $mes, (int) $d, (int) $y)) {
            return null;
        }
        return isset($m[4]) && $m[4] !== '' ? new KXlsxFecha($y, $mes, $d, $m[4], $m[5], isset($m[6]) ? $m[6] : 0)
                                             : new KXlsxFecha($y, $mes, $d);
    }
}
