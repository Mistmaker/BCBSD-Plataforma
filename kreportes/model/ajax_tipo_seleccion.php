<?php

session_start( );

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/



$bd	   =	new Db;

$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$sesion 	 =  trim($_SESSION['email']);
 
 
    $sql1 =  "SELECT   inombre, imodulo 
    FROM ireport.ireport_grupo
    where iestado = 'S'
    order by inombre";

 
$stmt1 = $bd->ejecutar($sql1);

 

while ($fila=$bd->obtener_fila($stmt1)){
    
    echo '<option value="'.$fila['imodulo'].'">'.trim($fila['inombre']).'</option>';
    
}


?>
 
  