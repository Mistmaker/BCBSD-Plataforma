<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );
 

$_SESSION['idmarca'] = $_GET['idmarca'];

$SaldoBodega = $_SESSION['idmarca'];

echo trim($SaldoBodega);

?>					 