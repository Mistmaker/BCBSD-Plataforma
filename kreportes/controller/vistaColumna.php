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
	private $tablaNombre;
	
	//-----------------------------------------------------------------------------------------------------------
	//Constructor de la clase
	//-----------------------------------------------------------------------------------------------------------
	function __construct( ){
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
 		
 		$this->tablaNombre = $Objeto[0];
 		
		$qquery = array(
				array( 'campo' => 'inom_columna',   	'valor' => '-',  'filtro' => 'N',   'visor' => 'S' , 'order'=>'-'),
				array( 'campo' => 'itipo',   	'valor' => '-',  'filtro' => 'N',   'visor' => 'S', 'order'=>'-'),
				array( 'campo' => 'ilong',   	'valor' => '-',  'filtro' => 'N',   'visor' => 'S' , 'order'=>'-'),
				array( 'campo' => 'icolumna',   	'valor' => '-',  'filtro' => 'N',   'visor' => 'S' , 'order'=>'-'),
				array( 'campo' => 'ivista',   		'valor' => $Objeto[0],  'filtro' => 'S',   'visor' => 'S', 'order'=>'-'),
				array( 'campo' => 'iestado',   			'valor' => 'S' ,  'filtro' => 'S',   'visor' => 'N', 'order'=>'-'),
		);
 
		
		$this->bd->_order_by('ilong');
	   		
		 $resultado = $this->bd->JqueryCursorVisor('ireport.ireport_vista',$qquery );
		 
	      $k = 0;
		 
        while ($fetch=$this->bd->obtener_fila($resultado)){
 		 	 	$nombreD =  trim($fetch['inom_columna']);
        	echo '<li class="list-group-item1">'.$nombreD.'</li>';
  
 		     $k++;
		}
		
 
 
	  
	  return  1;
		
	}
	
	function empresa(){
		
		$sql = "SELECT 
            substr(PAR00RAZON,8,120) as RAZON
				FROM PAR00
				where PAR00CODI = -1";
		
		$resultado = $this->bd->ejecutar($sql);
		
		$datos = $this->bd->obtener_array( $resultado);
		
		return trim($datos['RAZON']);
	}
	
	function tituloTabla(){
		
		$sql = "SELECT  upper(inombre) as nombre 
			FROM ireport.ireport where ivista = ".$this->bd->sqlvalue_inyeccion(trim($this->tablaNombre),true);
					
		$resultado = $this->bd->ejecutar($sql);
		
		$datos = $this->bd->obtener_array( $resultado);
		
		return trim($datos['nombre']);
	}
	//-----------------------------------
	function TablaEtiqueta($Vista){
		
		$Objeto = explode("-", $Vista);
		
		
		$this->tablaNombre = $Objeto[0];
		
		$sql = "SELECT  upper(inombre) as nombre
			FROM ireport.ireport where ivista = ".$this->bd->sqlvalue_inyeccion(trim($this->tablaNombre),true);
		
		$resultado = $this->bd->ejecutar($sql);
		
		$datos = $this->bd->obtener_array( $resultado);
		
		return trim($datos['nombre']);
	}
	//--------------------------
	function DatosReportes(){
		

 

		$sql_consulta = "SELECT wnomcolumna,idreport_sesion,  wsql, wcolumna, wcol, wfiltro, wtabla
			FROM  ireport.ireport_vista_sesion
		WHERE guarda = 'S' and 
			  wsesion = ".$this->bd->sqlvalue_inyeccion(trim($this->sesion ),true)."
		ORDER BY 1";

			$resultado  = $this->bd->ejecutar($sql_consulta);

 
		 
		
		  
		  echo '<ul class="list-group">';
		  while ($r=$this->bd->obtener_row($resultado)){
			
			 
				  echo '<li class="list-group-item"><a href="#" onClick="LLenaDatos()">'. $r[0].'</a></li>' ;
				 
 			}

			echo '</ul>';

	 
	}
}	 
 
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------



 


 
 
 
 
?>
 
  