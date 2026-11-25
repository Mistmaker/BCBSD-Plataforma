<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

/*
SELECCIONA LA BODEGA PARA EL FILTRO DE INFORMACION Y
SELECCION DE PRODUCTOS
*/

$_SESSION['idbodega'] = $_GET['idbodega'];   
$Bodega               = $_SESSION['idbodega'];
 
echo trim($Bodega);
    
?>					 