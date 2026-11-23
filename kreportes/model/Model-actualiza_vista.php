<?php
session_start( );

// retorna el valor del campo para impresion de pantalla

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
/*Creamos la instancia del objeto. Ya estamos conectados*/


 
$bd	   = new Db;


$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$valor      = $_GET["valor"] ;
$id         = $_GET["id"] ;
$campo      = $_GET["campo"] ;

 

$sql = "update ireport.ireport_vista 
          set ".$campo."= ".$bd->sqlvalue_inyeccion($valor, true) ."
          where idreport_vista=".$bd->sqlvalue_inyeccion($id, true) ;

  $bd->ejecutar($sql);
 
  echo 'Dato Actualizado '.$campo;
 

?>
  
  