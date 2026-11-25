<?php
/**
 * Punto único para generar PDF con dompdf (lib/dompdf, versión 3.x).
 *
 * Uso en un reporte:
 *     require_once __DIR__ . '/../../kconfig/pdf.php';
 *     $dompdf = new KDompdf();
 *     $dompdf->loadHtml($html);              // también acepta load_html() (nombre antiguo)
 *     $dompdf->setPaper('A4', 'portrait');
 *     $dompdf->render();
 *     $dompdf->stream('archivo.pdf', array('Attachment' => false));
 *
 * KDompdf es un Dompdf\Dompdf con la configuración del sistema:
 *  - chroot = carpeta de la aplicación: las imágenes con rutas relativas
 *    (../../kimages/logo.png, ../../archivos/..., códigos QR) se cargan igual
 *    que con las copias anteriores.
 *  - Caché de fuentes en una carpeta escribible del servidor (por defecto, la
 *    carpeta temporal del sistema). Las copias anteriores guardaban una caché
 *    generada en Windows con rutas "\Helvetica" que en Linux producía PDF sin texto.
 *  - Recursos remotos (http://) desactivados, como antes.
 *
 * Opcional en kconfig/config.local.php:
 *     'pdf' => array('cache_fuentes' => '/ruta/escribible')
 */

require_once __DIR__ . '/../lib/dompdf/vendor/autoload.php';

if (!function_exists('kpdf_utf8_reparar')) {
    /**
     * Devuelve el texto en UTF-8 válido sin tocar lo que ya lo es:
     *  - Si se indica una codificación distinta de UTF-8, se convierte desde ella.
     *  - Si es UTF-8 válido, queda igual.
     *  - Si no, cada secuencia UTF-8 válida se conserva y cada byte suelto
     *    (Latin-1 / Windows-1252, p. ej. "mi\xE9rcoles" o el resultado de
     *    utf8_decode()) se convierte a su carácter. Así se reparan documentos
     *    con codificaciones mezcladas sin dañar las partes correctas.
     */
    function kpdf_utf8_reparar($s, $codificacion = null)
    {
        if ($codificacion !== null && $codificacion !== '' && !in_array(strtoupper($codificacion), array('UTF-8', 'UTF8'), true)) {
            return mb_convert_encoding($s, 'UTF-8', $codificacion);
        }
        if (mb_check_encoding($s, 'UTF-8')) {
            return $s;
        }
        $utf8 = '/([\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'
              . '|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})|(.)/s';
        return preg_replace_callback($utf8, function ($m) {
            return isset($m[2]) ? mb_convert_encoding($m[2], 'UTF-8', 'Windows-1252') : $m[1];
        }, $s);
    }
}

if (!class_exists('KDompdf', false)) {

    class KDompdf extends \Dompdf\Dompdf
    {
        /**
         * @param array|\Dompdf\Options|null $opciones opciones adicionales de dompdf
         */
        public function __construct($opciones = null)
        {
            if ($opciones instanceof \Dompdf\Options) {
                $o = $opciones;
            } else {
                $o = new \Dompdf\Options(is_array($opciones) ? $opciones : array());
            }

            $raiz = realpath(__DIR__ . '/..');
            $o->setChroot(array($raiz));
            $o->setIsRemoteEnabled(false);
            $o->setTempDir(sys_get_temp_dir());
            $fuentes = self::carpetaFuentes();
            if ($fuentes !== null) {
                // dompdf escribe aquí las métricas de las fuentes web que encuentra
                // en el CSS (p. ej. Font Awesome / Glyphicons de Bootstrap)
                $o->setFontDir($fuentes);
                $o->setFontCache($fuentes);
            }

            parent::__construct($o);
        }

        /**
         * Carga el HTML siempre como UTF-8 válido. Los reportes heredados mezclan
         * codificaciones: utf8_decode() sobre un HTML que declara <meta charset="UTF-8">,
         * o fechas de strftime() en Latin-1 dentro de un HTML en UTF-8. Ver
         * kpdf_utf8_reparar().
         */
        public function loadHtml($str, $encoding = null)
        {
            $str = kpdf_utf8_reparar((string) $str, $encoding);
            // El texto ya es UTF-8: que la etiqueta <meta charset> no lo contradiga
            $str = preg_replace('/(<meta[^>]+charset\s*=\s*["\']?)[\w-]+/i', '${1}UTF-8', $str);
            return parent::loadHtml($str, 'UTF-8');
        }

        /** Nombre antiguo, usado por los reportes existentes. */
        public function load_html($str, $encoding = null)
        {
            return $this->loadHtml($str, $encoding);
        }

        /**
         * Carpeta escribible de fuentes: la primera vez se copian las fuentes que
         * trae dompdf (DejaVu, Helvetica, Times...). Devuelve null si no hay
         * ninguna carpeta escribible (se usan las de la librería, sin caché).
         */
        public static function carpetaFuentes()
        {
            static $dir = false;
            if ($dir !== false) {
                return $dir;
            }
            $conf = function_exists('kconfig') ? kconfig('pdf') : array();
            $dir  = !empty($conf['cache_fuentes'])
                ? rtrim($conf['cache_fuentes'], '/\\')
                : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bcbsd-dompdf-fuentes';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (!is_dir($dir) || !is_writable($dir)) {
                error_log('[pdf] La carpeta de fuentes no es escribible: ' . $dir);
                return $dir = null;
            }
            $origen = __DIR__ . '/../lib/dompdf/vendor/dompdf/dompdf/lib/fonts';
            foreach ((array) glob($origen . '/*.{afm,ttf,ufm,json}', GLOB_BRACE) as $f) {
                $destino = $dir . DIRECTORY_SEPARATOR . basename($f);
                if (!is_file($destino)) {
                    @copy($f, $destino);
                }
            }
            return $dir;
        }
    }
}
