<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
	$obj   = 	new objects;
	$bd	   = 	new Db ;
	
	$registro= $_SESSION['ruc_registro'];
	
    
	$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
	
      
	$query	=	$bd->sqlvalue_inyeccion(strtoupper($_GET["query"]) . '%', true);
	
    
    	
		    $sql = "SELECT  idprov 
					  FROM  par_ciu
					  WHERE estado = 'S' AND upper(idprov) like ".$query." order by idprov";
					    
		 
		    $stmt = $bd->ejecutar($sql);
		    
		    while ($x=$bd->obtener_fila($stmt)){
		        
		    	$cnombre =  trim($x['idprov']);
		    	$idprov[] =  $cnombre ;
		    
		    }
		    
		    $n = kcount($razon);
		    
		    if ( $n ==  0 ) {
		        
		        $idprov[] =  'NO EXISTE' ;
		   
		        
		    }
		    
		 
		    echo json_encode($idprov);
 
    
   
    
?>
 
  