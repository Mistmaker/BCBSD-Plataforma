<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8
 require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?> <?php 
    session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
 	$bd	   = 	new Db ;
	
    
	$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
	
      
	$query	=	$bd->sqlvalue_inyeccion($_GET["query"] . '%', true);
	
    
    	
		    $sql = "SELECT  idprov 
					  FROM  par_ciu
					  WHERE estado = 'S' AND upper(idprov) like ".$query." order by idprov";
					    
		 
		    $stmt = $bd->ejecutar($sql);
		    
		    while ($x=$bd->obtener_fila($stmt)){
		        
		    	$cnombre =  trim($x['idprov']);
		    	$idprov[] =  $cnombre ;
		    
		    }
		    
		    $n = kcount($idprov);
		    
		    if ( $n ==  0 ) {
		        
		        $idprov[] =  'NO EXISTE' ;
		   
		    }else {
		        
		        $bd->libera_cursor($stmt);
		        
		    }
		    
		 
		    echo json_encode($idprov);
 
     
    
?>  