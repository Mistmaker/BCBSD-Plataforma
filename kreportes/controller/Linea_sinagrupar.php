 <?php   
 session_start(); 
 
 require '../../kconfig/Db.class.php';   //Incluimos el fichero de la clase Db
 require '../../kconfig/Obj.conf.php'; //Incluimos el fichero de la clase objetos
   
   $bd	   =	new Db;
   
   
   $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
   $sesionUser = $_SESSION['login'];
   
   $columna   = $_GET['columna'];
   $tipo      = $_GET['tipo'];
 
   //----------------------------------------------
   $sqlTabla = "SELECT  wsql, wnomcolumna, wcolumna, wcol, wfiltro, wtabla
				    FROM ireport.ireport_vista_sesion
					WHERE wsesion = ".$bd->sqlvalue_inyeccion(trim($sesionUser), true). " AND guarda= 'N'"; 
   
   
   $resultado1 = $bd->ejecutar($sqlTabla);
   
   $datosq = $bd->obtener_array( $resultado1);
   
   $tabla =  $datosq['wtabla'];
   $filtro = $datosq['wfiltro'];
   //----------------------------------------------
   
   $sqlCampo = "SELECT inom_columna,  imodulo,  icolumna,itipo, ilong, ifiltro, ianalisis,esquema
				      FROM ireport.ireport_vista
									WHERE ivista = '".$tabla."' AND inom_columna = ".kstr($columna)." AND iestado = 'S' ";
 
 
    $resultadoCampo        = $bd->ejecutar($sqlCampo);

    $datosCol			   = $bd->obtener_array( $resultadoCampo);

	$esquema              = trim($datosCol['esquema']);

    $tabla_dato           = $esquema.'.'.$tabla;
	
	$alias                 =  ltrim(rtrim($datosCol['inom_columna']))  ;
				   
	 $sql_consulta = 'SELECT '.$datosCol['icolumna'].' AS  TITULO , COUNT(*) as  REGISTROS
																			   FROM '.$tabla_dato.'
																			WHERE '.$filtro.'
																		     GROUP BY '.$datosCol['icolumna'].'
																		     ORDER BY 1';
 
	 $resultado  = $bd->ejecutar($sql_consulta);
	 
	 $rows = array();
	 
	//---------------------------------------------- linea
 				   if ($tipo == 1){ 
  						   $bln = array();
						   
						   $bln['name'] =$alias;
						   $rows['name'] = 'Nro';
						   
						   while ($r=$bd->obtener_row($resultado)){
						   	
											   	$bln['data'][]  = $r[0];
											   	$rows['data'][] = $r[1];
 						   	//  	$rows['data'][] = (int) $r[0];
						   }
 						   $rslt = array();
						   array_push($rslt, $bln);
						   array_push($rslt, $rows);
						   print json_encode($rslt, JSON_NUMERIC_CHECK);
 				    }
 				    //-------------------------- pie
 				    if ($tipo == 2){
 				     
 				    	//$rows = array();
 				    	$rows['type']   = 'pie';
 				    	$rows['name'] =$alias;
 				    	
 				    	//$rows['innerSize'] = '50%';
 				    	while ($r=$bd->obtener_row($resultado)){
 				    		
 				    		$items = (string)$r[0].'  ';    // $items === "5";
 				    		
 				    		$rows['data'][] = array($items, $r[1]);
 				    
 				    	}
 				    	
 				    	$rslt = array();
 				    	array_push($rslt,$rows);
 				    	print json_encode($rslt, JSON_NUMERIC_CHECK);
 				    	
  				    }
  				    //-------------------------- barra
  				    if ($tipo == 3){
  				    	
  			 
  				    	
  				    	$bln = array();
  				    	
  				    	$bln['name'] =$alias;
  				    	$rows['name'] = 'Nro';
  				    	
  				    	while ($r=$bd->obtener_row($resultado)){
  				    		$bln['data'][] = $r[0];
  				    		$rows['data'][] = $r[1];
  				    	}
  				    	
  				    	$rslt = array();
  				    	
  				    	array_push($rslt, $bln);
  				    	array_push($rslt, $rows);
  				    	print json_encode($rslt, JSON_NUMERIC_CHECK);
  				    	
  				    }
 
   ?>
 
  