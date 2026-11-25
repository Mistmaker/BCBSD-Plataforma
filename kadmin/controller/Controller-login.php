<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

$_SESSION['us'] = '';
$_SESSION['ac']= '';
	
require '../../kconfig/Obj.conf.php'; 
 
require '../../kconfig/Db.class.php';  

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$bd	   =	new Db;
$obj     = 	new objects;

// $bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
$bd->conectar('','','');

$tipo = 'principal';
 

$sql11 = "SELECT a.ruc_registro, a.url,a.razon, b.nombre,a.fondo
		    FROM web_registro a , par_catalogo b
		   WHERE b.idcatalogo =  a.idciudad and 
		   a.tipo =".$bd->sqlvalue_inyeccion($tipo ,true);
 
 
$resultado1 = $bd->ejecutar($sql11);

// echo "<b>Consulta generada:</b> " . $sql11 . "<br><br>";
// // 2. Si la consulta falló (error de sintaxis, variables vacías, etc), atrapamos el error
// if (!$resultado1) {
//     die("<b>Error de PostgreSQL:</b> " . pg_last_error());
// }

$datos11     = $bd->obtener_array( $resultado1);

// 3. Vemos qué devolvió el array antes de que PHP intente leerlo
// echo "<b>Datos obtenidos:</b><br>";
// var_dump($datos11);
// die(); // Detenemos la ejecución aquí para que no salgan los warnings

 
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
	
		  
		 
		    $user 		= isset($_POST['datoname']) ? $_POST['datoname'] : '';
			$password 	= isset($_POST['datolog'])  ? $_POST['datolog']  : '';

			// Verificación con password_verify (acepta y migra el formato base64 anterior)
			$usuario_ok = $bd->validar_usuario($user, $password);
			$datos = $usuario_ok ? array($usuario_ok['login'], $usuario_ok['email'], $usuario_ok['rol'],
			                             $usuario_ok['url'], $usuario_ok['idusuario'], $usuario_ok['enlace']) : array();
 						        
 			//---------------------------------------------EJECUCION------------------------------------
 		  	 
			  
 			    $anio = date("Y");
				$_SESSION['anio'] 		= $anio;
	
 		/*	$x = $bd->query_array('presupuesto.view_periodo',
 			                       'anio', 
 			                       'estado in ('."'ejecucion','proforma'".') and anio = '.$bd->sqlvalue_inyeccion($anio,true)
 			);
 			
 			
 			if ( $anio == $x['anio']) {
 			
 			}else{
 			    $_SESSION['anio'] 		= $x['anio'];
 			}*/
 			    
			// Ingreso desde la ventana "sesión expirada" (kconfig/sesion.js): respuesta JSON
			$modo_ajax = isset($_POST['modo']) && $_POST['modo'] === 'ajax';
			$volver    = kauth_volver_valido(isset($_POST['volver']) ? $_POST['volver'] : '');

 			 if (!empty($datos[0])){     
 						         	
			 						         	// Nuevo id de sesión al autenticarse (evita fijación de sesión)
			 						         	session_regenerate_id(true);

			 						         	$_SESSION['login'] 		= $datos[0];
			 						         	$_SESSION['email'] 		= $datos[1];
			 						         	$_SESSION['rol']   		=$datos[2];
			 						         	$_SESSION['foto']  		=$datos[3];
			 						         	$_SESSION['usuario']  = $datos[4];
			 						         	$_SESSION['ultima_actividad'] = time();
			 						         	
			 						         	$valida = $bd->_enlaceIp($_SESSION['login'] ,$_SESSION['enlace']);

			 						         	
			 						         	if ($modo_ajax) {
			 						         		header('Content-Type: application/json; charset=UTF-8');
			 						         		exit(json_encode(array('ok' => $valida == 0, 'error' => $valida == 0 ? '' : 'Acceso no permitido desde esta ubicación')));
			 						         	}

			 						         	if($valida == 0 && $volver !== ''){
 														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=' . htmlspecialchars($volver, ENT_QUOTES) . '">';
			 						         	}elseif($valida == 0 ){
 														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/View-panel">';
 			 						         	}else{
														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login?a=1">';
			 						         	}
  	 
                }else   {
 						 	// Demasiados intentos fallidos: ingreso bloqueado unos minutos
 						 	$espera = (int) ceil($bd->login_espera / 60);
 						 	if ($modo_ajax) {
 						 		header('Content-Type: application/json; charset=UTF-8');
 						 		exit(json_encode(array('ok' => false, 'error' => $espera > 0
 						 			? 'Demasiados intentos fallidos. Espere ' . $espera . ' min e intente de nuevo.'
 						 			: 'Usuario o clave incorrectos')));
 						 	}
 						 	echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login?a=' . ($espera > 0 ? '4&m=' . $espera : '2') . ($volver !== '' ? '&volver=' . rawurlencode($volver) : '') . '">';
							// echo "Query: ".var_dump($_POST);
                }
           
             
 
 
 

?>