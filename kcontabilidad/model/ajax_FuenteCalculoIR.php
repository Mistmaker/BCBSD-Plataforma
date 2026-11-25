<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
    session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
    
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
	
	$obj   = 	new objects;
	$bd	   =    new Db ;
	
	$registro= $_SESSION['ruc_registro'];
	
    $bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
	
 
      
    $codigo	=	$_GET["codigoAux"];
    $base	=	$_GET["baseimpair"];
    
    
    $sql1 = "SELECT valor1
         		        FROM co_catalogo
        		       WHERE tipo = 'Fuente de Impuesto a la Renta' and
                             activo = 'S' and codigo=".$bd->sqlvalue_inyeccion($codigo ,true);
    
    $Amonto = $bd->ejecutar($sql1);
    
    $Aporcentaje = $bd->obtener_array( $Amonto);
    
    $porcentaje = $Aporcentaje['valor1'] /100 ;
    
    $total = kround($porcentaje,2) * kround($base,2) ;
   
    $valorretrenta = $total;
    
    
    echo $valorretrenta;
    
    
?>
 
  