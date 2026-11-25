<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8
 require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?> <?php 
    session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
    
	$bd	   = new Db ;
	
	
    $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
	
      
    $txtcodigo = strtoupper($_GET['itemVariable']);
    
  
    
    $sql = "SELECT   idproducto
				  FROM web_producto
				  where upper(producto) =".$bd->sqlvalue_inyeccion(trim($txtcodigo),true);
    
    
    $resultado1 = $bd->ejecutar($sql);
    
    $datos_sql  = $bd->obtener_array( $resultado1);
    
    $idproducto= trim( $datos_sql['idproducto']);
    
   
    echo $idproducto;
     
    
    kpg_free_result($resultado1);
    
?>