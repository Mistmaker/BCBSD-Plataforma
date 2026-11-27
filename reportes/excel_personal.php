<?php
session_start( );

require '../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


$obj   = 	new objects;
$bd	   =	new Db ;

 

$bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
 
			$sql ="SELECT programa,regimen,fecha,idprov as identificacion, 
						  razon as funcionario,cargo,genero,direccion, telefono, fechan as fecha_nacimiento, tsangre as tipo_sangre,
						  correo,emaile as correo_institucional,sueldo
					FROM view_nomina_rol
					WHERE estado='S'
					order by programa,regimen,razon" ;

if (isset($_GET['qestado']))	{      
	$sql ="SELECT programa,regimen,fecha,idprov as identificacion, 
						  razon as funcionario,cargo,genero,direccion, telefono, fechan as fecha_nacimiento, tsangre as tipo_sangre,
						  correo,emaile as correo_institucional,sueldo,fecha_salida
					FROM view_nomina_rol
					WHERE estado='N'
					order by programa,regimen,razon" ;
}
 
$resultado	= $bd->ejecutar($sql);

// Excel real (.xlsx): números, fechas y cédulas con su tipo (kconfig/excel.php)
require_once __DIR__ . '/../kconfig/excel.php';
kexcel_enviar_resultado($resultado, isset($_GET['qestado']) ? 'personal_inactivo' : 'personal');
?>  

 
