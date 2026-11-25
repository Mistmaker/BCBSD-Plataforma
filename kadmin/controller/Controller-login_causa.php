<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

$_SESSION['us'] = '';
$_SESSION['ac']= '';
	
require '../../kconfig/Obj.conf.php'; 
 
require '../../kconfig/Db.class.php';  

$bd	   =	new Db;
$obj     = 	new objects;

$bd->conectar($_SESSION['us'],'',$_SESSION['ac']);

$tipo = 'principal';
 

$sql11 = "SELECT a.ruc_registro, a.url,a.razon, b.nombre,a.fondo
		    FROM web_registro a , par_catalogo b
		   WHERE b.idcatalogo =  a.idciudad and 
		   a.tipo =".$bd->sqlvalue_inyeccion($tipo ,true);
 
 
$resultado1 = $bd->ejecutar($sql11);

$datos11     = $bd->obtener_array( $resultado1);

 
$_SESSION['sesion_actual']  = 0;
$_SESSION['ruc_registro']   =  $datos11['ruc_registro'];
$_SESSION['logo']	 	    =  $datos11['url'];
$_SESSION['razon'] 	        =  $datos11['razon'];
$_SESSION['ciudad'] 		=  $datos11['nombre'];
$_SESSION['tiempo'] 		=  date('Y-m-d H:i:s'); 
$_SESSION['fondo']		    =  $datos11['fondo'];
 
;


$_SESSION['captcha'] = 1;

//if (isset($_POST['s'])){
	
		  
		 
		    $user 		= $_POST['datoname'];		
			$password 	= $_POST['datolog'];
			//$codigos 	= @$_POST['codigos'];
			//---------------------------------------------variable------------------------------------
			// Verificacion con password_verify (acepta y migra el formato base64 anterior)
			$usuario_ok = $bd->validar_usuario($user, $password);
			$datos = $usuario_ok ? array($usuario_ok['login'], $usuario_ok['email'], $usuario_ok['rol'],
			                             $usuario_ok['url'], $usuario_ok['idusuario'], $usuario_ok['enlace']) : array();
 						        
 			//---------------------------------------------EJECUCION------------------------------------
 		  	 
			  
 			    $anio = date("Y");
				$_SESSION['anio'] 		= $anio;
 
 			    
 			 if (!empty($datos[0])){     
 						         	
			 						         	$_SESSION['login'] 		= $datos[0];
			 						         	$_SESSION['email'] 		= $datos[1];
			 						         	$_SESSION['rol']   		=$datos[2];
			 						         	$_SESSION['foto']  		=$datos[3];
			 						         	$_SESSION['usuario']  = $datos[4];
			 						         	
			 						         	$valida = $bd->_enlaceIp($_SESSION['login'] ,$_SESSION['enlace']);

			 						         	
			 						         	if($valida == 0 ){
 														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../../kSeguimientoTCE/">';
 			 						         	}else{
														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login_causa?a=1">';
			 						         	}
  	 
                }else   {
 						 	echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login_causa?a=2">';
							//echo "Query: ".var_dump($_POST);
                }
           
             
 
 
 

?>