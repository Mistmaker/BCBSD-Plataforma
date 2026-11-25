 <?php   
  
   session_start(); 
 
    require '../../kconfig/Db.class.php';   //Incluimos el fichero de la clase Db
    
    require '../../kconfig/Obj.conf.php'; //Incluimos el fichero de la clase objetos
 

   
   $bd	   =	new Db;
   
   
   $bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
   $vista = $_GET['type'];
   
   
   $modulo = moduloDato($vista, $bd);
   
   $sql =" SELECT idreport, inombre, ivista,  imodulo, iestado, idetalle
				  FROM ireport.ireport
			   where imodulo= ".kstr($vista)." and  iestado= 'S'
                 order by 1 desc"; 
 
   
   $stmt = $bd->ejecutar($sql);
 
 
   
   $ListaVista .= '<div class="panel panel-primary"> <div class="panel-heading">  <h5 class="panel-title">'.$modulo.'</h5> </div>';

   $ListaVista .= '<div class="panel-body">';

   
  	while ($x=$bd->obtener_fila($stmt)){
	   
	   		$parametro = ltrim(rtrim($x['ivista'])).'-'.ltrim(rtrim($x['inombre'])).'-'.ltrim(rtrim($x['imodulo']));
	   
	   		$cadena = trim(base64_encode($parametro));
	   
	   		
 	
	   		$evento =  'javascript:capavisor('."'".'../view/vista.php?parameter='.$cadena."'". ",'".$cadena."'".')';
	   		
	  		$ListaVista .='<img src="../view/xfolder.png" align="absmiddle"/><a href="'.$evento.'">'.ltrim(rtrim($x['inombre'])) .'</a><br>';
	   		
  	}
  	$ListaVista .= '</div></div>';
  
  
   echo  $ListaVista;
    
   
   function moduloDato($vista, $bd){
   	
       $sql = "SELECT  inombre, imodulo,  idetalle
      					FROM ireport.ireport_grupo where imodulo="."'".$vista."'";

 
   	
   	$resultado = $bd->ejecutar($sql);
   	
   	$datos = $bd->obtener_array( $resultado);
   	
   	return trim($datos['inombre']);
   	
   	
   }
 
?>
  