<?php 
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
      function componente( ){
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
      
 
      	

        $sqlg ="select '-' as codigo, '-- 0. Grupo Presupuestario --' as nombre union
        SELECT a.grupo as codigo,  a.grupo || ' '  || b.detalle as nombre
         FROM presupuesto.view_grupo_ingreso a, presupuesto.pre_catalogo b
         where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)." and  
                b.nivel = 2 and 
                b.subcategoria = 'ingreso' and 
                a.grupo = b.codigo order by 2"   ;
 
        $resultado1 = $this->bd->ejecutar($sqlg);

  
      	
      	$sqlf ="select '-' as codigo, '-- 0. Fuente de Financiamiento -- ' as nombre union 
                SELECT  codigo, 	codigo || ' '  || detalle as nombre
                FROM presupuesto.pre_catalogo
                where categoria = 'fuente' and estado = 'S'  order by 2 desc"   ;
      	
      	$resultadof = $this->bd->ejecutar($sqlf);
      	
      	 

        $datos['gfecha11'] =  $datos['fanio'].'-01-01';
         
        $datos['gfecha22'] = date('Y-m-d');
 
       
       $this->obj->text->text_yellow('',"date",'gfecha11',15,15,$datos,'required','','div-0-2');
       
       $this->obj->text->text_yellow('',"date",'gfecha22',15,15,$datos,'required','','div-0-2');

 
        $this->obj->list->listadbe($resultadof,$tipo,'','vfuente',$datos,'','',$evento,'div-0-3');
      	
      	$this->obj->list->listadbe($resultado1,$tipo,'','vgrupo',$datos,'','',$evento,'div-0-3');
      	
      	
      	$this->obj->text->text_place('Buscar x clasificador',"texto" ,'clasificadori' ,80,80, $datos ,'','','div-0-2') ;
      
      }
///------------------------------------------------------------------------
///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
  
 
   $gestion->FiltroFormulario( );

 ?>


 
  