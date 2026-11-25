<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/

require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


class proceso{

	//creamos la variable donde se instanciar la clase "mysql"

	private $obj;
	private $bd;

	private $ruc;
	private $sesion;
	private $hoy;
	private $POST;
	
 
	
	//-----------------------------------------------------------------------------------------------------------
	//Constructor de la clase
	//-----------------------------------------------------------------------------------------------------------
	function proceso( ){
		//inicializamos la clase para conectarnos a la bd

		$this->obj     = 	new objects;
		
		$this->bd	   =	new Db;

		$this->ruc       =  $_SESSION['ruc_registro'];
		
		$this->sesion 	 =  $_SESSION['login'];
		
		$this->hoy 	     =  $this->bd->hoy();

		$this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
		
 
	}
 

	//-----------------------------------------------------------------------------------------------------------
	//Constructor de la clase
	//-----------------------------------------------------------------------------------------------------------
 	public function BusquedaObj(){
 	
 
		$sql = "SELECT idgrupo, inombre, imodulo, iestado, idetalle
      					FROM ireport.ireport_grupo
						where iestado = 'S'" ;
					 
	
 
		 $resultado  = $this->bd->ejecutar($sql);
		 
		 
 
		 echo '<div class="panel panel-primary">
					 <div class="panel-heading">
					 <h3 class="panel-title">INFORMACION</h3>
					 </div>
					 <div class="panel-body">
						<div class="bs-example">
		    				<ul class="list-group">';
		 
        while ($fetch=$this->bd->obtener_fila($resultado)){
 		
        	$nombreOpcion =  trim($fetch['inombre']);
        	
 
        	
        	$modulOpcion =  trim($fetch['imodulo']);
        	
        	$objeto = ' <a href="#" onClick="Dir('."'".$modulOpcion."'".')">
						<img src="../view/db.png" align="absmiddle" title="Seleccione tipo de informacion"/> ';
        	
			  echo '<li class="list-group-item">'.$objeto.$nombreOpcion .'</a></li>';
				  
 
 
		}
		
		echo ' </ul> 
 			</div></div></div>';
		
 	 
 
 
	  return  1;
		
	}
 
}	 
 
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------

$opcion     = 	new proceso;

 
$opcion->BusquedaObj();

 
?>
 
  




     
        
        
       

 