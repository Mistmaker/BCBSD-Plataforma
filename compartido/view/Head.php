<?php
/**
 * Cabecera común (<head>) de las pantallas de todos los módulos.
 *
 * Cada módulo tiene un view/Head.php mínimo que define sus opciones y luego
 * incluye este archivo:
 *
 *     $kh = array('jquery' => '3.2.1');          // solo lo que difiere del valor por defecto
 *     require __DIR__ . '/../../compartido/view/Head.php';
 *
 * Opciones ($kh):
 *   favicon     true|false   ícono del sitio (por defecto true)
 *   jquery      '1.11.3'|'3.2.1'  versión de jQuery que queda activa (se carga
 *               después de jQuery 1.10.2, como en las cabeceras originales).
 *               NO unificar sin probar cada pantalla del módulo.
 *   easyui_css  'comun'|'modulo'  tema easyui de app/easyui/default o el del módulo (../js/default)
 *   typeahead   'comun'|'modulo'  bootstrap3-typeahead de app/js o el del módulo (../js)
 *   estilos     ruta a un archivo con el bloque <style> propio del módulo
 *   raiz        prefijo hasta la raíz de la aplicación desde la página (por defecto '../../';
 *               '../' para páginas en carpetas de primer nivel como reportes/)
 *   js_modulo   carpeta de los JS propios del módulo (por defecto '../js/'; reportes/
 *               usa los de kadmin: '../kadmin/js/')
 *
 * Rutas: las URL son relativas a la página (module/view/x.php o module/controller/x.php),
 * igual que en las cabeceras originales. "../js/..." apunta a los JS propios del módulo.
 * Todas las bibliotecas se sirven desde app/ (sin depender de servidores externos).
 */
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

$kh = array_merge(array(
    'favicon'    => true,
    'jquery'     => '1.11.3',
    'easyui_css' => 'comun',
    'typeahead'  => 'comun',
    'estilos'    => null,
    'raiz'       => '../../',
    'js_modulo'  => '../js/',
), isset($kh) && is_array($kh) ? $kh : array());

$kh_jquery = array(
    '1.11.3' => 'app/js/jquery.min.js',
    '3.2.1'  => 'app/3.2.1/jquery.min.js',
);
$kh_app = htmlspecialchars($kh['raiz'], ENT_QUOTES) . 'app/';
$kh_js  = htmlspecialchars($kh['js_modulo'], ENT_QUOTES);
?>
<?php if ($kh['favicon']) { ?>
    <link rel="shortcut icon" href="<?php echo $kh_app; ?>kaipi-favicon.png">
<?php } ?>
    <link rel="stylesheet" href="<?php echo $kh_app; ?>fontawesome/font-awesome.min.css"/>
    <link href="<?php echo $kh_app; ?>dist/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $kh_app; ?>dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="<?php echo $kh_app; ?>css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo $kh_app; ?>css/jquery-ui.min.css" type="text/css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $kh['easyui_css'] === 'modulo' ? $kh_js . 'default/easyui.css' : $kh_app . 'easyui/default/easyui.css'; ?>">
    <link rel="stylesheet" href="<?php echo $kh_app; ?>dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $kh_app; ?>dist/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $kh_app; ?>themes/alertify.core.css" />
    <link rel="stylesheet" href="<?php echo $kh_app; ?>themes/alertify.default.css" />

    <script type="text/javascript" src="<?php echo $kh_app; ?>js/jquery-1.10.2.min.js"></script>
    <script src="<?php echo $kh_app; ?>dist/js/bootstrap.min.js"></script>
    <script src="<?php echo htmlspecialchars($kh['raiz'], ENT_QUOTES) . (isset($kh_jquery[$kh['jquery']]) ? $kh_jquery[$kh['jquery']] : $kh_jquery['1.11.3']); ?>"></script>
    <script type="text/javascript" src="<?php echo $kh_js; ?>jquery.easyui.min.js"></script>
    <script type="text/javascript" src="<?php echo $kh_app; ?>js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $kh_app; ?>js/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $kh_js; ?>kaipi.js"></script>
    <script type="text/javascript" src="<?php echo $kh_app; ?>lib/alertify.js"></script>
    <script src="<?php echo $kh['typeahead'] === 'modulo' ? $kh_js . 'bootstrap3-typeahead.min.js' : $kh_app . 'js/bootstrap3-typeahead.min.js'; ?>"></script>
<?php
if (!empty($kh['estilos']) && is_file($kh['estilos'])) {
    readfile($kh['estilos']); // bloque <style> propio del módulo, tal cual
}
?>
    <link href="<?php echo $kh_app; ?>css/elegant-icons-style.css" rel="stylesheet" />
    <link href="<?php echo $kh_app; ?>css/font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo $kh_app; ?>css/widgets.css" rel="stylesheet">
    <link href="<?php echo $kh_app; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo $kh_app; ?>css/style-responsive.css" rel="stylesheet" />
