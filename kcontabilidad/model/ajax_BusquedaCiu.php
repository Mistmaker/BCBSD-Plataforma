<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
    session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
 
	$bd	   = new Db ;
 
	
    $bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
	
      
    $term	=	$_GET["query"];
    
    $variable = strtoupper($term).'%';
    
    $sql = "select razon  from par_ciu  where razon like ".$bd->sqlvalue_inyeccion($variable, true) ;
    
    
    $stmt = $bd->ejecutar($sql);
    
    while ($x=$bd->obtener_fila($stmt)){
    	$cnombre =  trim($x['razon']);
    	$proveedor[] =  $cnombre ;
    }
   
    $n = kcount($proveedor);
    
    if ( $n ==  0 ) {
        
        $proveedor[] =  'NO EXISTE' ;
        
        
    }
    
    
    echo json_encode($proveedor);
    
?>