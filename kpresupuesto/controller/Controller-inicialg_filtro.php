<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
    session_start( );   
    
    require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
 	
    require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
    
    require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
  
    class componente{
 
      //creamos la variable donde se instanciar? la clase "mysql"
 
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
      	
        $datos['fanio']	     = $this->anio  ;
      	
      	$datos['clasig']	 = '%%';
      	
      	
      	$evento ='';
      	
      	$sql ="select '-' as codigo, '00. Funcion - Programa' as nombre union
               SELECT a.funcion as codigo,  a.funcion || ' ' ||  b.detalle as nombre
                FROM presupuesto.view_gasto_programa a, presupuesto.pre_catalogo b
                where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)." and
                       b.nivel = 0 and
                       b.categoria = 'programa' and
                       a.funcion = b.codigo order by 2"   ;
      	
      	$resultado = $this->bd->ejecutar($sql);
      	
      	$this->obj->list->listadbe($resultado,$tipo,'Funcion-Programa','vprograma',$datos,'','',$evento,'div-0-3');
      	
      	
      	
      	$sql ="select '-' as codigo, '00. Actividad Presupuestaria' as nombre union
               SELECT a.actividad as codigo,   b.detalle as nombre
                FROM presupuesto.view_gasto_actividad a, presupuesto.pre_catalogo b
                where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)." and
                       b.nivel = 0 and
                       b.categoria = 'actividad' and
                       a.actividad = b.codigo order by 2"    ;
      	
      	$resultado = $this->bd->ejecutar($sql);
      	
      	$this->obj->list->listadbe($resultado,$tipo,'Actividad','vactividad',$datos,'','',$evento,'div-0-3');
      	 
      	
     
      	$sql ="select '-' as codigo, '00. Grupo Presupuestario' as nombre union
               SELECT a.grupo as codigo,  a.grupo || ' '  || b.detalle as nombre
                FROM presupuesto.view_grupo_gasto a, presupuesto.pre_catalogo b
                where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)." and  
                       b.nivel = 2 and 
                       b.subcategoria = 'gasto' and 
                       a.grupo = b.codigo order by 2"   ;
      	
      	$resultado = $this->bd->ejecutar($sql);
      	
      	$this->obj->list->listadbe($resultado,$tipo,'Grupo','vgrupog',$datos,'','',$evento,'div-0-3');
      	
       
      	$sql ="select '-' as codigo, ' - 000. Fuente de Financiamiento -' as nombre union
                SELECT  codigo, 	codigo || ' '  || detalle as nombre
                FROM presupuesto.pre_catalogo
                where categoria = 'fuente' and estado = 'S' order by 2"   ;
      	
      	$resultado = $this->bd->ejecutar($sql);
      	
      	$this->obj->list->listadbe($resultado,$tipo,'','vfuentegg',$datos,'','',$evento,'div-0-3');


        $sql ="select '-' as codigo, ' - 000. Orientador de Gasto -' as nombre union
        SELECT  codigo, 	codigo || ' '  || detalle as nombre
        FROM presupuesto.pre_catalogo
        where categoria = 'orientador' and estado = 'S' order by 2"   ;

      $resultado = $this->bd->ejecutar($sql);

      $this->obj->list->listadbe($resultado,$tipo,'','vorientador',$datos,'','',$evento,'div-0-3');

      	
      
      }
///------------------------------------------------------------------------
///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
  
 
   $gestion->FiltroFormulario( );

 ?>


 
  