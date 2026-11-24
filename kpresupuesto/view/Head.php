<?php
// Cabecera del módulo kpresupuesto: lo común está en compartido/view/Head.php;
// aquí solo lo propio del módulo (opciones y view/Head_estilos.php).
$kh = array(
    'favicon' => false,
    'jquery' => '3.2.1',
    'easyui_css' => 'modulo',
    'typeahead' => 'modulo',
    'estilos' => __DIR__ . '/Head_estilos.php',
);
require __DIR__ . '/../../compartido/view/Head.php';
