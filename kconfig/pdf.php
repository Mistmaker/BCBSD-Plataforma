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
