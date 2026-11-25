<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesiÃ³n ?> <?php   
  
   session_start(); 
 
 
    require '../../kconfig/Db.class.php';   //Incluimos el fichero de la clase Db
    require '../../kconfig/Obj.conf.php'; //Incluimos el fichero de la clase objetos
  
 
 
   
   $bd	   =	new Db;
   
   
   $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
   $sesionUser = $_SESSION['login'];
   
 
   $sqlTabla = "SELECT  wsql, wnomcolumna, wcolumna, wcol, wfiltro, wtabla
				    FROM ireport.ireport_vista_sesion
					WHERE wsesion = ".$bd->sqlvalue_inyeccion(trim($sesionUser), true) .
					" ORDER BY CASE WHEN guarda = 'N' THEN 0 ELSE 1 END, idreport_sesion DESC LIMIT 1"; // el analisis actual (guarda N) o el ultimo guardado
   
 
   $Rsql 	= $bd->ejecutar($sqlTabla);
   $tabla  =  $bd->obtener_array( $Rsql);
   
 
  
 
   $sqltitulo =  trim($tabla['wcolumna']);
   
 
   $new     = str_replace(' ', '_',$sqltitulo);
   
   
   $ObjetoE		 = explode(",",$new) ;

 
   
   $category = array();
   $series1 = array();
   $series2 = array();
   $series3 = array();
   $series4 = array();
   $series5 = array();
   $series6 = array();
   
   
   $i = 0;
   
   foreach($ObjetoE as $cat) {
   	$url = '';
		   if ($i==0){
		   	$cat = trim($cat);
		   	$url = strtolower($cat);
		   	$category['name'] =$url;
   		   }
   		   
   		   if ($i==1){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series1['name'] =$url;
   		   }
   		   if ($i==2){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series2['name'] =$url;
   		   }
   		   if ($i==3){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series3['name'] =$url;
   		   }
   		   if ($i==4){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series4['name'] =$url;
   		   }
   		   if ($i==5){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series5['name'] =$url;
   		   }
   		   if ($i==6){
   		   	$cat = trim($cat);
   		   	$url = strtolower($cat);
   		   	$series6['name'] =$url;
   		   }
  
   	$i++;
   }
  
   $columnas =  	$i;
   /// información de los datos
    
   $sql = $tabla['wsql'];
   
   $resultado= $bd->ejecutar($sql);
   
   while ($r=$bd->obtener_fila($resultado)){
   					   $k = 0;
 					   	foreach ($r as $item){
									   		if ($k == 0){
									   			$category['data'][] = $item.' ' ;
									   		}
									   		if ($k == 1){
									   			$series1['data'][] =(int) $item;
									   		}
									   		if ($k == 2){
									   			$series2['data'][] =(int) $item;
									   		}
									   		if ($k == 3){
									   			$series3['data'][] =(int) $item;
									   		}
									   		if ($k == 4){
									   			$series4['data'][] =(int) $item;
									   		}
									   		if ($k == 5){
									   			$series5['data'][] =(int) $item;
									   		}
									   		if ($k == 6){
									   			$series6['data'][] =(int) $item;
									   		}
					 		
					   		$k++;
					   	}
 	}
	
 	$result = array();
 	array_push($result,$category);
 	
 	for ( $i =1; $i < $columnas  ; $i++){
 	  
 		if ($i == 1){
 			array_push($result,$series1);
 		}
 		if ($i == 2){
 			array_push($result,$series2);
 		}
 		if ($i == 3){
 			array_push($result,$series3);
 		}
 		if ($i == 4){
 			array_push($result,$series4);
 		}
 		if ($i == 5){
 			array_push($result,$series5);
 		}
 		if ($i == 5){
 			array_push($result,$series6);
 		}
 	}
  
 
 
 	
 	print json_encode($result, JSON_NUMERIC_CHECK);
   
    
 
?>
  