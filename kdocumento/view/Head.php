<?php
// Cabecera del módulo kdocumento: lo común está en compartido/view/Head.php;
// aquí solo lo propio del módulo (opciones y view/Head_estilos.php).
$_SESSION['directorio_crm'] = '../kimages/';
$kh = array(
    'estilos' => __DIR__ . '/Head_estilos.php',
);
require __DIR__ . '/../../compartido/view/Head.php';
