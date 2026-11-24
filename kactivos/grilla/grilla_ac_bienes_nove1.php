<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
     session_start( );   
  
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
 	
 	
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
  
  
    class proceso{
 
    
 
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
                $this->bd	   =	new Db ;
    
                $this->ruc       =  $_SESSION['ruc_registro'];
                
                $this->sesion 	 =  $_SESSION['email'];
         
                $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
      }
   
      //-----------------------------------------------------------------------------------------------------------
      //--- busqueda de grilla primer tab
      //-----------------------------------------------------------------------------------------------------------
      public function BusquedaGrilla($vtipo_bien,$vcuenta,$Vid_departamento,$vuso,$vtiene_acta,$vidsede,$vactivo,$vcodigo,$tipo){
      
 

          
          if ( $Vid_departamento == '0'){
              $filtro2 = 'N';
          }else {
              $filtro2 = 'S';
          }
          
          if ( $vtiene_acta == '-'){
              $filtro3 = 'N';
          }else {
              $filtro3 = 'S';
          }
          
          if ( $vcuenta == '-'){
              $filtro4 = 'N';
          }else {
              $filtro4 = 'S';
          }
          
          
          $len = strlen($vactivo);
          $filtro5 = 'N';
          
          if ( $len  > 5 ){
              $filtro4 = 'N';
              $filtro1 = 'N';
              $filtro2 = 'N';
              $filtro3 = 'N';
              $filtro5 = 'S';
              $variable = 'LIKE.%'.strtoupper($vactivo).'%';
          }
          
          $filtro6 = 'N'; 
          $filtro8 = 'S';
          if ( $vcodigo  > 0 ){
              $filtro4 = 'N';
              $filtro1 = 'N';
              $filtro2 = 'N';
              $filtro3 = 'N';
              $filtro5 = 'N';
              $filtro6 = 'S';
              $filtro8 = 'N';
          }
          
          $filtroNO = 'N';
          if ( $tipo == '2'){
              $filtroNO = 'S';
          }

          if ( $vuso == '-'){
            $filtro1 = 'N';
        }else {
            $filtro1 = 'S';
        }
          
          $qquery = array( 
              array( campo => 'id_bien',        valor => $vcodigo,filtro => $filtro6, visor => 'S'),
              array( campo => 'descripcion',    valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'detalle',            valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'cuenta',         valor => $vcuenta,filtro => $filtro4, visor => 'S'),
              array( campo => 'unidad',         valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'razon as custodio',          valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'fecha_adquisicion',valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'estado',         valor => 'Malo',filtro => $filtroNO, visor => 'S'),
              array( campo => 'tiempo_anio',    valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'vida_util',          valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'costo_adquisicion',  valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'valor_residual',     valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'color',              valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'marca',              valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'serie',              valor => '-',filtro => 'N', visor => 'S'),
             array( campo => 'origen',              valor => '-',filtro => 'N', visor => 'S'),
             array( campo => 'detalle_ubica as ubicacion',              valor => '-',filtro => 'N', visor => 'S'),
             array( campo => 'material',              valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'ramos',              valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'factura',            valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'constatacion',       valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'tipo_bien',          valor => $vtipo_bien,filtro => 'S', visor => 'S'),
              array( campo => 'estado_bien',        valor =>'1',filtro => 'S', visor => 'N'),
              array( campo => 'tiene_acta',         valor => $vtiene_acta,filtro => $filtro3, visor => 'S') ,
              array( campo => 'bandera',            valor => '-',filtro => 'N', visor => 'S'),
              array( campo => 'id_departamento',    valor => $Vid_departamento,filtro => $filtro2, visor => 'N'),
              array( campo => 'idsede',             valor => $vidsede,filtro => $filtro8, visor => 'N'),
              array( campo => 'busqueda',           valor => $variable,filtro => $filtro5, visor => 'N'),

          );
            
   
          
          $resultado = $this->bd->JqueryCursorVisor('activo.view_bienes',$qquery  );
 
 
      	$output = array();
      	
      	while ($fetch=$this->bd->obtener_fila($resultado)){
 
  
      	    $x = $this->bd->query_array('activo.ac_bienes_historico',   // TABLA
      	        'count(*) as nn',                        // CAMPOS
      	        'id_bien='.$this->bd->sqlvalue_inyeccion($fetch['id_bien'],true) ." and 
                tipo_h = 'revalorizado'"
      	        );
      	    
      	    $reva = 'NO';
      	    
      	    if ( $x['nn'] > 0 ){
      	        $reva = 'SI';
      	    }

                $chequeo = '<input type="checkbox" id="che'. $fetch['id_bien'].'" name"che'. $fetch['id_bien'].'" onclick="myFunction('. $fetch['id_bien'].' ,this)">';

            if ( $fetch['bandera'] == 'S' ){
                $chequeo = '<input type="checkbox" id="che'. $fetch['id_bien'].'" name"che'. $fetch['id_bien'].'" onclick="myFunction('. $fetch['id_bien'].' ,this)" checked>';
            }

 
            $chequeoc = ' <input type="checkbox" id="chec'. $fetch['id_bien'].'" name"chec'. $fetch['id_bien'].'" onclick="myFunctionc('. $fetch['id_bien'].' ,this)">';

            if ( $fetch['constatacion'] == 'S' ){
                $chequeoc = ' <input type="checkbox" id="chec'. $fetch['id_bien'].'" name"chec'. $fetch['id_bien'].'" onclick="myFunctionc('. $fetch['id_bien'].' ,this)" checked>';
            }


            
            $acta = '<img src="../../kimages/m_rojo.png" width="16" height="16" align="absmiddle"  />';
             if ( trim($fetch['tiene_acta']) == 'S' ){
                $acta = '<img src="../../kimages/m_verde.png" width="16" height="16" align="absmiddle" />';      
              }else{
                $chequeo    = '<img src="../../kimages/alert.png" width="16" height="16" align="absmiddle" />';     
              }
            

              $id = str_pad($fetch['id_bien'],6,"0",STR_PAD_LEFT);
               
              $cuenta = trim($fetch['cuenta']).'-'.$id; 
               
              $len = strlen(trim($fetch['codigo_actual']));
       
              if (  $len > 3){
                    $informacion_activo = trim($fetch['descripcion']).'- ('. trim($fetch['codigo_actual']).') '.trim($fetch['estado']).' ' . 
                    trim($fetch['color']).' '.trim($fetch['detalle']).' Factura: '.trim($fetch['factura']);

              }else{
                    $informacion_activo = trim($fetch['descripcion']).' '.trim($fetch['estado']).' ' . 
                    trim($fetch['color']).' '.trim($fetch['detalle']).' Factura: '.trim($fetch['factura']);
               }
            

		 	$output[] = array (
		      				    $fetch['id_bien'],
                                $informacion_activo ,
                                $acta .' '.$fetch['custodio'],
		      				    $fetch['estado'],
                		 	    $fetch['fecha_adquisicion'],
                		 	    $fetch['tiempo_anio'],
                		 	    $fetch['vida_util'],
                                 $chequeo,
                		 	    $fetch['costo_adquisicion'],
                		 	    $fetch['valor_residual'] ,
		 	                    $reva,
                                 $cuenta 
 		      		);	 
      		
      	}
 
 
 	echo json_encode($output);
      	
      	
      	}
 
   
 }    
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
 
///------------------------------------------------------------------------ 
 
    		$gestion   = 	new proceso;
 
   
          
            //------ consulta grilla de informacion
            if (isset($_GET['vtipo_bien']))	{
            
            	 
                $vtipo_bien       = $_GET['vtipo_bien'];
                $vcuenta          = $_GET['vcuenta'];
                $Vid_departamento = $_GET['Vid_departamento'];
                $vuso             = $_GET['vuso'];
                $vtiene_acta      = $_GET['vtiene_acta'];
                $vidsede      = $_GET['vidsede'];
                $vactivo      = $_GET['vactivo'];
                $vcodigo      = $_GET['vcodigo'];
                
                $tipo      = $_GET['tipo'];
                
                
                
                $gestion->BusquedaGrilla($vtipo_bien,$vcuenta,$Vid_departamento,$vuso,$vtiene_acta,$vidsede,$vactivo,$vcodigo,$tipo);
            	 
            }
  
  
   
 ?>
 
  