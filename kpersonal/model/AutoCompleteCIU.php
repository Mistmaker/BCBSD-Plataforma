<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
  
	$bd	   = 	new Db ;
	
 
	$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
	
      
	$query	=	$bd->sqlvalue_inyeccion(strtoupper($_GET["query"]) . '%', true);
	
    
    	
		    $sql = "SELECT  razon 
					  FROM  par_ciu
					  WHERE modulo = 'N' and estado = 'S' AND upper(razon) like ".$query." order by razon";
					    
		 
		    $stmt = $bd->ejecutar($sql);
		    
		    while ($x=$bd->obtener_fila($stmt)){
		        
		    	$cnombre =  trim($x['razon']);
		    	
		    	$razon[] =  $cnombre ;
		    
		    }
		    
		    $n = kcount($razon);
		    
		    if ( $n ==  0 ) {
		        
		        $razon[] =  'NO EXISTE' ;
		   
		        
		    }
		    
		 
		    echo json_encode($razon);
 
    
		    kpg_free_result($stmt);
		    
    
?>
 
  