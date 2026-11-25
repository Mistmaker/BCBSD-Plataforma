<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?> <?php   
 session_start(); 
 
 
    require '../../kconfig/Db.class.php';   //Incluimos el fichero de la clase Db
    require '../../kconfig/Obj.conf.php'; //Incluimos el fichero de la clase objetos
  
 
   $bd	   =	new Db;
   
   $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

   $sesionUser = $_SESSION['login'];
   
   
	$fecha1  =  date('Y-m-d', strtotime('-2 month'));
	$anio    = date('Y');

	$fecha_inicio = $anio.'-01-01';

 
	$sqlDel = "DELETE 
	FROM ireport.ireport_vista_sesion 
	  WHERE guarda = 'S' and 
	        wsesion= ".$bd->sqlvalue_inyeccion(trim($sesionUser), true)." and 
	        fcreacion  between ".$bd->sqlvalue_inyeccion($fecha_inicio , true). ' and  '.$bd->sqlvalue_inyeccion($fecha1 , true);

	$bd->ejecutar($sqlDel);



   $sqlDel = "DELETE 
                FROM ireport.ireport_vista_sesion 
   			   WHERE guarda = 'N' and 
			   	     wsesion = ".$bd->sqlvalue_inyeccion(trim($sesionUser), true);
   
   $bd->ejecutar($sqlDel);
   
					$variable = base64_decode($_GET['VistaNombre']);
					
					$Objeto = explode("-",$variable) ;
					
					$tabla  = $Objeto[0];
					
					$ecolumna = $_GET['etiqueta'];
					
					$contador = $_GET['c1'];
					
					$ObjetoE		 = explode(",",$ecolumna) ;
					
					$agrupado =  $_GET['tiposql'];
					
					$filtro 			= $_GET['sfiltro'];
					
					$columna1	= '';
					$columnaCadena ='';
					$columnagra ='';
					
					$columnag	= '';
					$columnaCadenag ='';
					$columnaGrafico ='';
   
   //----------------------------------------------------------------------------------------------------------------
   foreach($ObjetoE as $cat) {
			   
			   	   $cat = trim($cat);
			   	   
			      	$sqlc = 'select icolumna, itipo,esquema    
                               from ireport.ireport_vista   
								where ivista = '.$bd->sqlvalue_inyeccion($tabla, true).' and  
                                      inom_columna = '.$bd->sqlvalue_inyeccion($cat, true);
			  	  
			     
			      	
			      	
			      	   $ResColumna 			 =  $bd->ejecutar($sqlc);
			      	   $col                  =  $bd->obtener_array( $ResColumna);
			      	
			   		   $columna1   =  $col[0];
			   		   $columnag  =   $col[0];
			   		   $tipo			=     $col[1];
			   		   $columnagra		=  	  $col[0];
			   		   $columna2 		=  	  $col[0];
					   $esquema 		=  	  trim($col[2]);
 
			   		   
			   		   $columnaCadena2 = '';
			   		   
			   		   //-------------------------------------------------------------------------------
			   		   if ($agrupado == 'S'){
				   		     	if ($tipo == 'NUMBER'){
			   		 		 		
				   		     	    //$columna2     = "to_char(SUM(".$col[0]."),'999G999G990D00') AS ".$col[0];
				   		     	    
				   		     	    $columna2     = "SUM(".$col[0].")  AS ".$col[0];
			   		 		 		
			   		 		  		$columna1     = "SUM(".$col[0].")  AS ".$col[0];
				   		      
			   		 		  		$columnagra = "SUM(".$col[0].") AS ".$col[0];
			   		 		  	}else{
			   		 		  		$columnaCadenag  = $columnaCadenag.$columnag.',';
			   		 		  	}
 
			   		 		  	$columnaCadena2  = $columnaCadena2.$columna2.',';
			   		 		  	$columnaCadena  = $columnaCadena.$columna1.',';
  			   		 		  	$columnaGrafico=$columnaGrafico.$columnagra.',';
			   		   //-------------------------------------------------------------------------------
			   		   }else{
			   		   		    $columnaCadena= $columnaCadena.$columna1.',';
			   		   		    $columnaGrafico=$columnaGrafico.$columnagra.',';
			   		   }
 
}
 
 //----------------------------------------------
   $len = strlen($columnaCadena);
   $len2 = strlen($columnaCadena2);
   
   if ($agrupado == 'S'){
	   	$ColSql = substr($columnaCadena,0,$len  - 1);
	   	$ColSql1 = substr($columnaCadena2,0,$len2  - 1);
   	
   }else{
   		$ColSql = substr($columnaCadena,0,$len  - 1);
   }
   
   if ($len < 2){
   	$columnaCadena = '-';
   }
   
   
   
   $len2 = strlen($columnaGrafico);
   $GraficoCol = substr($columnaGrafico,0,$len2 -1);
   
   
   $len1 = strlen($columnaCadenag);
   $ColumnasG = substr($columnaCadenag,0,$len1 -1);
   
   $colCount         = '';
   $colCountNro  =  '';
   
   if (!empty($ColumnasG)){
        	$GroupBy =  ' group by '.$ColumnasG;
        	if ($contador == 'S'){
        		$colCount =',count(*) as Nro';
        		$colCountNro=',Nro';
        	} 
  }else{
	   	$GroupBy ='';
   }
	   
	 $orderBy = ' order by 1';

	 $tabla_dato = $esquema.'.'.$tabla;
	   
	 if (!empty($filtro)){
		   	$sql             = 'SELECT '.$ColSql.$colCount.' FROM  '. $tabla_dato. ' where '.$filtro.$GroupBy.$orderBy;
		   	$sqlgrafico		  =  'SELECT '.$GraficoCol.$colCount.' FROM  '. $tabla_dato. ' where '.$filtro.$GroupBy.$orderBy;
		   	$sql1             = 'SELECT '.$ColSql1.$colCount.' FROM  '. $tabla_dato. ' where '.$filtro.$GroupBy.$orderBy;
	 }else{
		   	$sql                 = 'SELECT '.$ColSql.$colCount.' FROM  '. $tabla_dato.$GroupBy.$orderBy;
		   	$sqlgrafico   		 = 'SELECT '.$GraficoCol.$colCount.' FROM  '. $tabla_dato.$GroupBy.$orderBy;
		   	$sql1                = 'SELECT '.$ColSql1.$colCount.' FROM  '. $tabla_dato.$GroupBy.$orderBy;
	 }
	 
 
	 $colCountNro = '--';
 	   	//-------------------------------------
	   	$InsertQuery = array(
		   			array( 'campo' => 'wsesion',    'valor' =>$sesionUser),
		   			array( 'campo' => 'wsql',  	  'valor' =>$sqlgrafico),
		   			array( 'campo' => 'wnomcolumna','valor' => $colCountNro),
		   			array( 'campo' => 'wcol',  	  'valor' => $ColSql),
		   			array( 'campo' => 'wfiltro',    'valor' => $filtro),
		   			array( 'campo' => 'wtabla',  	  'valor' => $tabla),
 		   			array( 'campo' => 'wcolumna',   'valor' =>$ecolumna.$colCountNro)
		   	);
 
		 	   	$bd->JqueryInsertSQL('ireport.ireport_vista_sesion',$InsertQuery);
    
				$stmt = $bd->ejecutar($sql);
				
				$output = array();
				
				$recordsTotal = 1;
		
				while ($fetch=$bd->obtener_row($stmt)){
			
				$output[] =  $fetch;
				
				$recordsTotal++;
		
			}
		 
		 if (isset ( $_GET['draw'] ) ){
		 	$draw = $_GET['draw'];
		 }else
		 {
		 	$draw = 1;
		 	$_GET['draw'] = 1;
		 }
	 
 	 $recordsFiltered = $recordsTotal;
	 	 
	 
	 $response = array(
	 		"draw" => $draw,
	 		"recordsTotal" => $recordsTotal,
	 		"recordsFiltered" => $recordsFiltered,
	 		"data" => $output
	 );
 
 
	 
  	  echo json_encode($response);
 
?>
  