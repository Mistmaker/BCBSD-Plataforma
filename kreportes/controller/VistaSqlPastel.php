<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?> <?php   
  
   session_start(); 
 
 
    require '../../kconfig/Db.class.php';   //Incluimos el fichero de la clase Db
    require '../../kconfig/Obj.conf.php'; //Incluimos el fichero de la clase objetos
 
   
   $bd	   =	new Db;
   
   
   $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
   $sesionUser = $_SESSION['login'];
   
 
   $sqlTabla = "SELECT  wsql, wnomcolumna, wcolumna, wcol, wfiltro, wtabla
				    FROM ireport.ireport_vista_sesion
					WHERE wsesion = ".$bd->sqlvalue_inyeccion(trim($sesionUser), true); ;
   
   
   $Rsql 	= $bd->ejecutar($sqlTabla);
   $tabla  =  $bd->obtener_array( $Rsql);
   
 
   $sqltitulo =  $tabla['wcol'];
 
   
   $new     = str_replace(' ', '_',$sqltitulo);
   
   $ObjetoE		 = explode(",",$new) ;

   $i = 0;
   foreach($ObjetoE as $cat) {
 		   	if ($i==0){
		   		$cat = trim($cat);
		   		$alias= strtolower($cat);
 		   	}
    	$i++;
   }
   
   $rows = array();
   
   //$rows = array();
   $rows['type']   = 'pie';
   $rows['name'] =$alias;
   
   
   $sql = $tabla['wsql'];
   
   $resultado= $bd->ejecutar($sql);
   
   while ($r=$bd->obtener_row($resultado)){
   	
	   	$items = (string)$r[0].'  ';    // $items === "5";
	   	
	   	$rows['data'][] = array($items, $r[1]);
   	
   }
   
   $rslt = array();
   
   array_push($rslt,$rows);
   
   print json_encode($rslt, JSON_NUMERIC_CHECK);
   
    
   
    
 
?>
  