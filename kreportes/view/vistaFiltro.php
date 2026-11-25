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
	
	private $ATabla;  // array de variables de la tabla
	private $tabla;  // nombre de la tabla
	private $secuencia; // secuencia de las tablas
	
	
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
 	public function BusquedaObj($Vista){

 		$Objeto = explode("-", $Vista);
 		
 		
		$qquery = array(
				array( campo => 'inom_columna',   	valor => '-',  filtro => 'N',   visor => 'S' , order=>'ASC'),
				array( campo => 'itipo',   	        valor => '-',  filtro => 'N',   visor => 'S', order=>'-'),
				array( campo => 'icolumna',   	    valor => '-',  filtro => 'N',   visor => 'S' , order=>'-'),
				array( campo => 'ivista',   		valor => $Objeto[0],  filtro => 'S',   visor => 'S', order=>'-'),
				array( campo => 'ifiltro',   		valor => 'S' ,  filtro => 'S',   visor => 'N', order=>'-'),
				array( campo => 'esquema',   		valor => '-' ,  filtro => 'N',   visor => 'S', order=>'-'),
		);
		
	   		
 	     $this->bd->_order_by('inom_columna');
		
		 $resultado = $this->bd->JqueryCursorVisor('ireport.ireport_vista',$qquery );
		
        while ($fetch=$this->bd->obtener_fila($resultado)){
			 
			$nombre  =  $fetch['icolumna'];

			$esquema =   trim($fetch['esquema']);
			
			$nombreD = trim( $fetch['inom_columna']);
			
			$nombre1 =  $fetch['icolumna'].'99';
			
			$tipo =  $fetch['itipo'];
			
			
        	if ($tipo == 'DATE' ){
        		
        	    $cadena = '<div  class="col-md-3">'.$nombreD.'</div><div  class="col-md-4">';
        	    $cadena .=  '<input placeholder="'.$nombreD.'  (Desde)'.'" class="input-xs_dato"   name="'.'_'.$nombre.'"   id= "'.'_'.$nombre.'" type="text" onfocus="(this.type='."'date'".')"  id="date"></div> ';
        	    $cadena .= '<div  class="col-md-4"><input placeholder="'.$nombreD. '  (Hasta)'.'" class="input-xs_dato"  name="'.'_'.$nombre1.'"   id= "'.'_'.$nombre1.'"  type="text" onfocus="(this.type='."'date'".')"  id="date"></div>  ';
         	    echo  $cadena ;
        	}else{
        	
        	
                echo '<div class="col-md-3">'.trim($nombreD).' </div><div class="col-md-9">';
                echo  ' <select class="input-xs_dato" name="'.$nombre.'_'.$tipo.'"   id= "'.$nombre.'_'.$tipo.'"> ';
                         $this->datosColumna($nombre, $Objeto[0],$nombreD,$esquema );
                 echo '</select></div>';
        			
                
     //     echo '<script type="text/javascript">'."$('#".$nombre."').editableSelect();".'</script>';
        	
        	}
        	 
      
        	 
		}

	 
		 
		 
	  $VerFiltro = '';
	  
	  echo $VerFiltro;
		
	}
	 
	 
	//--------------------------------------------------------------------------------
	//---retorna el valor del campo para impresion de pantalla ?articulo=' + articulo + '&id='+id_asiento,
	//--------------------------------------------------------------------------------
	function datosColumna(  $field,$vista,$nombreD,$esquema  ){


		$tabla_dato = $esquema .'.'.$vista;

		$stmt = $this->bd->ejecutar("SELECT distinct ".$field." as codigo, ".$field." as nombre FROM ".$tabla_dato.' order by 1');
	
		$objeto_texto = '';
		
		$cadena2= '<option  value="-">-</option>' ;

		while ($lp_row=$this->bd->obtener_fila($stmt)){
		    $val     = trim($lp_row['codigo']);
		    $cadena1 =  '  <option value="'.$val.'">'.$val.'</option> ';
		    $objeto_texto = $objeto_texto.$cadena1;
		    
		}
 		echo  trim($cadena2.$objeto_texto) ;
 
		 
	}
}	 
 
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------

$gestion   = 	new proceso;

$gestion->proceso();

 
//------ poner informacion en los campos del sistema
if (isset($_GET['VistaNombre']))	{

	 
	$VerFiltro    = base64_decode($_GET['VistaNombre']);
	 
 
  $gestion->BusquedaObj($VerFiltro) ;


}

 
 
 
 
?>
 
  