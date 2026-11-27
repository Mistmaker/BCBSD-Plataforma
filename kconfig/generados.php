<?php
/**
 * Archivos temporales que el sistema genera para armar un documento (por ahora,
 * los códigos QR "logo_qr.png" y "documento.png").
 *
 * Antes se escribían con nombre fijo en la carpeta del código (kxxx/reportes,
 * kxxx/model): ensuciaban el repositorio, exigían permiso de escritura sobre el
 * código y, si dos usuarios imprimían a la vez, uno podía llevarse el QR del otro.
 *
 * Ahora:
 *  - la librería de QR (reportes/phpqrcode/qrimage.php) los guarda en una carpeta
 *    temporal propia de cada petición, que se borra al terminar;
 *  - al armar el PDF (KDompdf) y al enviar la página al navegador (kconfig/auth.php),
 *    src="logo_qr.png" se reemplaza por la imagen incrustada (data URI);
 *  - para FPDF/TCPDF se usa kgen_ruta('logo_qr.png') y en respuestas ajax kgen_src().
 *
 * Carpeta base configurable: 'archivos' => array('temporal' => '/ruta/escribible').
 */

if (!function_exists('kgen_dir')) {

    /** Nombres de archivo temporales conocidos (sin carpeta). */
    function kgen_nombres()
    {
        return array('logo_qr.png', 'documento.png');
    }

    /** Carpeta temporal de esta petición (se crea al primer uso y se borra al terminar). */
    function kgen_dir()
    {
        static $dir = null;
        if ($dir !== null) {
            return $dir;
        }
        $conf = function_exists('kconfig') ? kconfig('archivos') : array();
        $base = !empty($conf['temporal']) ? rtrim($conf['temporal'], '/\\') : sys_get_temp_dir();
        $base .= DIRECTORY_SEPARATOR . 'bcbsd-generados';
        $id = function_exists('random_bytes') ? bin2hex(random_bytes(8)) : uniqid('', true);
        $dir = $base . DIRECTORY_SEPARATOR . date('Ymd') . '-' . $id;
        if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
            error_log('[generados] no se pudo crear la carpeta temporal ' . $dir);
            $dir = false;
            return $dir;
        }
        register_shutdown_function('kgen_limpiar', $dir);
        return $dir;
    }

    function kgen_limpiar($dir)
    {
        if ($dir && is_dir($dir)) {
            foreach ((array) glob($dir . DIRECTORY_SEPARATOR . '*') as $f) {
                @unlink($f);
            }
            @rmdir($dir);
        }
    }

    /** Ruta del archivo temporal $nombre de esta petición. */
    function kgen_ruta($nombre)
    {
        $dir = kgen_dir();
        $nombre = basename(str_replace('\\', '/', (string) $nombre));
        return $dir ? $dir . DIRECTORY_SEPARATOR . $nombre : $nombre;
    }

    /**
     * Destino real de un archivo que la librería de QR va a escribir: si es uno
     * de los nombres temporales conocidos (sin carpeta), va a la carpeta temporal;
     * cualquier otra ruta (p. ej. etiquetas de activos en kactivos/code/) no cambia.
     */
    function kgen_qr_destino($archivo)
    {
        if (!is_string($archivo) || $archivo === '') {
            return $archivo;
        }
        $limpio = preg_replace('#^\./#', '', str_replace('\\', '/', $archivo));
        if (in_array($limpio, kgen_nombres(), true)) {
            $ruta = kgen_ruta($limpio);
            if ($ruta !== $limpio) {
                $r = &kgen_registro();
                $r[$limpio] = $ruta;
                return $ruta;
            }
        }
        return $archivo;
    }

    /** Archivos temporales generados en esta petición: nombre => ruta. */
    function &kgen_registro()
    {
        static $r = array();
        return $r;
    }

    /** ¿Se generó $nombre en esta petición? (no crea la carpeta temporal) */
    function kgen_existe($nombre)
    {
        $r = &kgen_registro();
        return isset($r[$nombre]) && is_file($r[$nombre]);
    }

    /** Ruta para FPDF/TCPDF: la del archivo generado en esta petición o el nombre tal cual. */
    function kgen_archivo($nombre)
    {
        $r = &kgen_registro();
        return kgen_existe($nombre) ? $r[$nombre] : $nombre;
    }

    /** src de una imagen temporal: la imagen incrustada si se generó; si no, el nombre tal cual. */
    function kgen_src($nombre)
    {
        if (!kgen_existe($nombre)) {
            return $nombre;
        }
        $r = &kgen_registro();
        return 'data:image/png;base64,' . base64_encode(file_get_contents($r[$nombre]));
    }

    /**
     * En un HTML, reemplaza src="logo_qr.png" / "documento.png" (también con
     * "./" o "../model/" delante) por la imagen generada en esta petición.
     */
    function kgen_reemplazar_html($html)
    {
        if (!is_string($html) || stripos($html, '.png') === false) {
            return $html;
        }
        $nombres = array();
        foreach (kgen_nombres() as $n) {
            if (kgen_existe($n)) {
                $nombres[] = preg_quote($n, '/');
            }
        }
        if (!$nombres) {
            return $html;
        }
        $r = preg_replace_callback(
            '/(\bsrc\s*=\s*)(["\']?)(?:\.\/|\.\.\/model\/)?(' . implode('|', $nombres) . ')\2/i',
            function ($m) {
                return $m[1] . '"' . kgen_src(strtolower($m[3])) . '"';
            },
            $html
        );
        return $r === null ? $html : $r;
    }
}
