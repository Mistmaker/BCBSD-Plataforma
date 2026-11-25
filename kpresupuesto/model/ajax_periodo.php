<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
    session_start( );   
  

    
    $data= array();
    
    $data['anio'] = $_SESSION['anio'];
    
    $json = json_encode($data);
    
    header('Content-type: application/json; charset=utf-8');
    
    echo $json;
?>