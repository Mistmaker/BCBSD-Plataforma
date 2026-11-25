<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
    session_start( );   
    
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
 	
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
    
    require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
  
    class componente{
 
  
 
      private $obj;
      private $bd;
      private $set;
      
       private $formulario;
       private $evento_form;
          
      private $ruc;
      private $sesion;
      private $hoy;
      private $POST;
      private $anio;
      //-----------------------------------------------------------------------------------------------------------
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function __construct( ){
            //inicializamos la clase para conectarnos a la bd
 
                $this->obj     = 	new objects;
                
                $this->set     = 	new ItemsController;
                   
                $this->bd	   =	new  Db ;
             
                $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
      
                $this->ruc       =  $_SESSION['ruc_registro'];
                
                $this->sesion 	 =  $_SESSION['email'];
                
                $this->hoy 	     =  date("Y-m-d");
        
                $this->anio       =  $_SESSION['anio'];
 
      }
       //-----------------------------------------------------------------------------------------------------------
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function FiltroFormulario(){
      
        $datos = array();
       
        $tipo = $this->bd->retorna_tipo();
      	
        $datos['fanio']	= $this->anio;
       	
      	
      	 
      	$evento = '';
      
      	$MATRIZ = array(
      	    '01'  => 'Enero',
      	    '02'  => 'Febrero',
      	    '03'  => 'Marzo',
      	    '04'  => 'Abril',
      	    '05'  => 'Mayo',
      	    '06'  => 'Junio',
      	    '07'  => 'Julio',
      	    '08'  => 'Agosto',
      	    '09'  => 'Septiembre',
      	    '10'  => 'Octubre',
      	    '11'  => 'Noviembre',
      	    '12'  => 'Diciembre'
      	);
      	 
      
      	
      	
      	$MATRIZ1 = array(
      	    '-'  => 'Ejecucion '.$datos['fanio']	,
      	    '01'  => 'Enero',
      	    '02'  => 'Febrero',
      	    '03'  => 'Marzo',
      	    '04'  => 'Abril',
      	    '05'  => 'Mayo',
      	    '06'  => 'Junio',
      	    '07'  => 'Julio',
      	    '08'  => 'Agosto',
      	    '09'  => 'Septiembre',
      	    '10'  => 'Octubre',
      	    '11'  => 'Noviembre',
      	    '12'  => 'Diciembre'
      	);

 
 

         $this->obj->text->texto_oculto("fanio",$datos); 
         $this->obj->text->texto_oculto("cmesi",$datos); 
         $this->obj->text->texto_oculto("cmes",$datos); 


 
      
      }
///------------------------------------------------------------------------
///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
  
 
   $gestion->FiltroFormulario( );

 ?>


 
  