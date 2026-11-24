<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

$anio		=   $_GET["anio"];


$_SESSION['anio'] = $anio;

 

$response = 'Periodo de gestion '.$anio;

echo $response;



?>