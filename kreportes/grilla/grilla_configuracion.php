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
      function __construct( ){
            //inicializamos la clase para conectarnos a la bd
 
                $this->obj     = 	new objects;
                $this->bd	   =	new Db ;
    
                $this->ruc       =  $_SESSION['ruc_registro'];
                $this->sesion 	 =  $_SESSION['email'];
                $this->hoy 	     =  $this->bd->hoy();
        
                $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
 
      }
   
      //-----------------------------------------------------------------------------------------------------------
      //--- busqueda de grilla primer tab
      //-----------------------------------------------------------------------------------------------------------
      public function BusquedaGrilla($PK_codigo){
      
      	// Soporte Tecnico
      
       $output = array();
          
 
 
      	
      	$sql = 'SELECT idreport, inombre, ivista, imodulo, iestado, idetalle
                from ireport.ireport a where imodulo='.kstr($PK_codigo).'  order by inombre ';
      	
      	// echo $sql;
      	
      	$resultado  = $this->bd->ejecutar($sql);
      	
      	
       
      	
      	while ($fetch=$this->bd->obtener_fila($resultado)){
      		 
      		$output[] = array (
      		    $fetch['idreport'],
      		    $fetch['inombre'],
      		    $fetch['ivista'],
      		    $fetch['iestado'],
      		    $fetch['idetalle'] );
      
 
      	}
 
      	
      	
      	echo json_encode($output);
      }
   
     
   
 }    
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
 
    		$gestion   = 	new proceso;
 
   
          
            //------ consulta grilla de informacion
            if (isset($_GET['GrillaCodigo']))	{
            
            	$PKcodigo  = $_GET['GrillaCodigo'];
             	 
            	$gestion->BusquedaGrilla($PKcodigo );
            	 
            }
  
  
   
 ?>
 
  