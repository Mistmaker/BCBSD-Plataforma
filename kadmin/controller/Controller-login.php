<?php
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
$bd->conectar('postgres','db_kaipi','Cbsd2019');

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
	
		  
		 
		    $user 		= $_POST['datoname'];		
			$password 	= $_POST['datolog'];
			//$codigos 	= @$_POST['codigos'];
			//---------------------------------------------variable------------------------------------
			$clave = $obj->var->_codifica($password);
			//---------------------------------------------variable------------------------------------
 
			$sql='SELECT login,email,rol,url,idusuario ,enlace
											    FROM par_usuario
					    					  WHERE login='.$bd->sqlvalue_inyeccion($user,true). ' AND
											  	     estado='.$bd->sqlvalue_inyeccion('S',true). ' AND 
											  	    clave='.$bd->sqlvalue_inyeccion(trim($clave),true);
			$stmt  = $bd->ejecutar($sql);
 			$datos  =$bd->Arrayfila($stmt);
 						        
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
 			    
 			 if (!empty($datos[0])){     
 						         	
			 						         	$_SESSION['login'] 		= $datos[0];
			 						         	$_SESSION['email'] 		= $datos[1];
			 						         	$_SESSION['rol']   		=$datos[2];
			 						         	$_SESSION['foto']  		=$datos[3];
			 						         	$_SESSION['usuario']  = $datos[4];
			 						         	
			 						         	$valida = $bd->_enlaceIp($_SESSION['login'] ,$_SESSION['enlace']);

			 						         	
			 						         	if($valida == 0 ){
 														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/View-panel">';
 			 						         	}else{
														echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login?a=1">';
			 						         	}
  	 
                }else   {
 						 	echo '<meta HTTP-EQUIV="REFRESH" content="0; url=../view/login?a=2">';
							// echo "Query: ".var_dump($_POST);
                }
           
             
 
 
 

?>