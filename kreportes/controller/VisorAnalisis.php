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
	function proceso( ){
		//inicializamos la clase para conectarnos a la bd

		$this->obj     = 	new objects;
		
		$this->bd	   =	new Db;

		$this->ruc       =  $_SESSION['ruc_registro'];
		
		$this->sesion 	 =  $_SESSION['login'];
		
		$this->hoy 	     =  $this->bd->hoy();

		$this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
		
		$this->ATabla = array(); // creo el array
 
	}
 

	//-----------------------------------------------------------------------------------------------------------
	//Constructor de la clase
	//-----------------------------------------------------------------------------------------------------------
 	public function AnalisisTabla( ){

 
 	    $sesionUser = $_SESSION['login'];
 	    

		  

 	    
 		$sqlTabla = "SELECT  wsql, wnomcolumna, wcolumna, wcol, wfiltro, wtabla
				    FROM ireport.ireport_vista_sesion
					WHERE wsesion = ".$this->bd->sqlvalue_inyeccion(trim($sesionUser), true); ;
 		
 		$resultado1 = $this->bd->ejecutar($sqlTabla);
 		
 		$datos1 = $this->bd->obtener_array( $resultado1);
 		
 		$COLUMNA_ARRAY = $datos1['wcol'];
 		
 		$tabla = trim($datos1['wtabla']);

		
 		$tabla_ejecuta = trim($datos1['wtabla']);

		//------- ver esquema
 
		$datos_esquema = $this->bd->query_array(
			'ireport.ireport',
			'esquema',
			'ivista='.$this->bd->sqlvalue_inyeccion($tabla,true) 
			);

			$esquema = trim($datos_esquema['esquema']);

			if ( $esquema == '-'){

			}else{
				$tabla_ejecuta  = $esquema.'.'.$tabla ;
			}

		 
			
		//---------------------------------------
 		
 		$filtro = $datos1['wfiltro'];
 		
 		$ArrayColumna = explode(",", $COLUMNA_ARRAY);
	  
 		$longitud= count($ArrayColumna);
 		
 		$this->secuencia= 1 ;
 		
 		$this->ATabla = array('0'  => '-- Seleccionar variables de Analisis --');
 
 		
 		echo '<div class="col-md-9" align="center">';
 		
	 	 for($i=0; $i<$longitud; $i++)
	 	 {
 
	 	 	$columna = $ArrayColumna[$i];
	 	 	
	 	 	if (!empty($columna)){
	 	 		
	 	 	     $this->TablaEtiqueta( $columna,  $tabla, $filtro,$tabla_ejecuta );
	 	 		
 	 	 	}
 
	 	 }
	 		
	 	 
	 	 $datos = array();
	 	 
	 	 
	 	 echo '<br>';
	 	
	 	 $evento =  ' onChange="GraficoDetalle( this.options[this.selectedIndex].text )" ';
   
	 			 $this->obj->list->listae('',$this->ATabla,'var_grafico',$datos,'required','',$evento,'div-0-6');	

				  
				 echo ' <button type="button" onClick="Graficopie()"  class="btn btn-success">Gráfico Pastel</button>
						<button type="button" onClick="Graficobarra()" class="btn btn-info">Gráfico Barra</button>
						<button type="button" onClick="Graficolinea()" class="btn btn-warning">Gráfico Linea</button>
						';
	 	 
	 	   echo '</div>';
		   //<button type="button" onClick="GrabarSesion()" class="btn btn-danger">Guardar Resultado</button>
	 	 
	 	
 		
	  return  1;
		
	}
	
	 
	//-----------------------------------
	function TablaEtiqueta($columna,$tabla,$filtro,$tabla_ejecuta ){
 
		
		$sqlCampo = "SELECT inom_columna,  imodulo,  icolumna,itipo, ilong, ifiltro, ianalisis
				      FROM ireport.ireport_vista
									WHERE ivista = '".$tabla."' AND icolumna = '".$columna."' AND iestado = 'S' ";


 		
		$resultadoCampo        = $this->bd->ejecutar($sqlCampo);
		$datosCol			   = $this->bd->obtener_array( $resultadoCampo);
		
		$etiqueta              =  trim($datosCol['inom_columna']);
		
  	
		
			 	if ($datosCol['itipo'] == 'VARCHAR2'){
			 		
 			 		if ($datosCol['ianalisis'] == 'S'){
 			 		 	
 			 			array_push($this->ATabla,   $etiqueta);
  
 			 		}				
			 	}			
			 	
			 	if ($datosCol['itipo'] == 'VARCHAR'){
			 	    
			 	    if ($datosCol['ianalisis'] == 'S'){
			 	        
						array_push($this->ATabla,   $etiqueta);
			 	        
			 	    }
			 	}	
			 	
			 	if ($datosCol['itipo'] == 'ENTERO'){
			 	    
			 	    if ($datosCol['ianalisis'] == 'S'){
			 	        
						array_push($this->ATabla,   $etiqueta);
			 	        
			 	    }
			 	}	
			 	
	//	 ------------------------------------------
		  		if ($datosCol['itipo'] == 'NUMBER'){
		 			
		 			$etiqueta =  ltrim(rtrim($datosCol['inom_columna']));
		 			
		 			$alias = ' total'  ;
		 			
		 			$sql_consulta = 'SELECT SUM( '.$datosCol['icolumna'].')  AS  '.$alias.' 
														   FROM '.$tabla_ejecuta .'
														WHERE '.$filtro ;
		 			
		 		 
		 			
		 			if ( trim($datosCol['ianalisis']) == 'S'){
 
		 				$resultadoTotal = $this->bd->ejecutar($sql_consulta);
		 				
		 				$datos1 = $this->bd->obtener_array( $resultadoTotal);
		 				
		 				echo ' <a href="#" class="list-group-item" style="font-size: 11px">'.$etiqueta.'<span class="badge" style="font-size: 11px">'. number_format($datos1['total'] , 2, ',', '.').'</span>
						        </a>';
		 				 
		 				
		 			}
		 		}
 
  
	}
	
 
	
}	 
 
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------



$gestion   = 	new proceso;

 

 $gestion->AnalisisTabla();

 

 
 
 
 
?>
 
  