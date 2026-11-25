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
      	
        $datos['fanio']	     = $this->anio  ;
      	
      	$datos['clasig']	 = '%%';
 
      	
      	$evento ='';
      	
      	$sql ="select '-' as codigo, 'Funcion/Programa' as nombre union
               select '0' as codigo, 'Agrupado por Funcion/Programa' as nombre union
               SELECT a.funcion as codigo,   b.detalle as nombre
                FROM presupuesto.view_gasto_programa a, presupuesto.pre_catalogo b
                where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)." and
                       b.nivel = 0 and
                       b.categoria = 'programa' and
                       a.funcion = b.codigo order by 1"   ;

         $sql_act ="select '-' as codigo, '-- 0. Actividad Presupuestaria -- ' as nombre union
                       SELECT a.actividad as codigo,   a.nombre_actividad as nombre
                        FROM presupuesto.view_gasto_actividad a 
                        where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)."  
                             group by actividad,nombre_actividad
                               order by 2"   ;   
     
          $sql_gr ="select '-' as codigo, '-- 0. Grupo Presupuestario -- ' as nombre union
                               SELECT a.grupo as codigo,  a.grupo || ' '  || a.nombre_grupo as nombre
                                FROM presupuesto.view_grupo_gasto a
                                where  a.anio =  ". $this->bd->sqlvalue_inyeccion($datos['fanio'],true)."    
                                      group by a.grupo,  a.nombre_grupo
                                       order by 2"   ;
                        
          $sql_fuente ="select '-' as codigo, '-- 0. Fuente de Financiamiento --' as nombre union
                                       SELECT  codigo, 	codigo || ' '  || detalle as nombre
                                       FROM presupuesto.pre_catalogo
                                       where categoria = 'fuente' and estado = 'S' order by 2"   ;
                               


        $sql_oo ="select '-' as codigo, ' - 000. Orientador de Gasto -' as nombre union
        SELECT  codigo, 	codigo || ' '  || detalle as nombre
        FROM presupuesto.pre_catalogo
        where categoria = 'orientador' and estado = 'S' order by 2"   ;


        $resultado_oo = $this->bd->ejecutar($sql_oo);

         $resultado_fuente = $this->bd->ejecutar($sql_fuente);

         $resultado_grupo = $this->bd->ejecutar($sql_gr);                             
      	
        $resultado = $this->bd->ejecutar($sql);

         $resultado_pro =  $this->sql('PROYECTO');
 
      	
      	$resultado_ac = $this->bd->ejecutar($sql_act);
 

        $datos['gfecha1'] =  $datos['fanio'].'-01-01';
         
        $datos['gfecha2'] = date('Y-m-d');
       
       $this->obj->text->text_yellow('',"date",'gfecha1',15,15,$datos,'required','','div-0-3');
       $this->obj->text->text_yellow('',"date",'gfecha2',15,15,$datos,'required','','div-0-3');

      	
      	$this->obj->list->listadbe($resultado,$tipo,'Funcion-Programa','vprograma',$datos,'','',$evento,'div-0-3');
      	 
  
        
        
      	$this->obj->list->listadbe($resultado_grupo,$tipo,'Grupo','vgrupog',$datos,'','',$evento,'div-0-3');
      	
      	$this->obj->list->listadbe($resultado_fuente,$tipo,'','vfuentegg',$datos,'','',$evento,'div-0-3');
        
        $this->obj->list->listadb($resultado_pro,$tipo,'Proyecto','vproyecto11',$datos,'','','div-0-3');
      	$this->obj->list->listadbe($resultado_ac,$tipo,'Actividad','vactividad',$datos,'','',$evento,'div-0-3');

        // $this->obj->list->listadbe($resultado_oo,$tipo,'','vorientador',$datos,'','',$evento,'div-0-3');
        	
        // $this->obj->text->textautocomplete('Buscar x clasificador',"texto",'clasificador',140,145,$datos,'required','','div-0-3');

 
      
      }
///------------------------------------------------------------------------
//----------------------------------------------
function sql($titulo){
  	
   
  if ( $titulo == 'CLASIFICADOR'){
    
      $sqlb = "SELECT  '-' as codigo, '[ 0. No aplica ]'  AS nombre  union
             SELECT  codigo, (codigo || ' ' || detalle) as nombre
              FROM presupuesto.pre_catalogo
              where tipo = 'arbol' and subcategoria = 'gasto' and nivel = 5 and pac = 'S' order by 1 ";
      $resultado = $this->bd->ejecutar($sqlb);
    
  }
  
  
  if ( $titulo == 'PROGRAMA'){
      
      $sqlb = "SELECT  '-' as codigo, '[ 0. Programa ]'  AS nombre  union
             SELECT  trim(codigo) as codigo, (codigo || ' ' || detalle) as nombre
              FROM presupuesto.pre_catalogo
              where tipo= 'catalogo' and categoria = 'programa'    order by 1 ";
      $resultado = $this->bd->ejecutar($sqlb);
      
  }

    if ( $titulo == 'PROYECTO'){
      
      $sqlb = "SELECT  '-' as codigo, '[ -- 0. Proyecto  -- ]'  AS nombre  union
           SELECT  trim(a.codigo) as codigo, (a.codigo || ' ' || a.detalle) as nombre
              FROM presupuesto.pre_catalogo a, presupuesto.view_estructura b
              where a.tipo= 'catalogo' and 
                    b.tipo = 'G' and 
                    b.anio= ".$this->bd->sqlvalue_inyeccion( $this->anio , true)." and 
                    b.proyecto = a.codigo and
                    a.categoria = 'proyecto'    
                    group by 1,2
                    order by 1 ";

 
      $resultado = $this->bd->ejecutar($sqlb);
      
  }

    if ( $titulo == 'FUENTE'){
      
      $sqlb = "SELECT  '-' as codigo, '[ -- 0. No aplica  --]'  AS nombre  union
           SELECT  trim(codigo) as codigo, (codigo || ' ' || detalle) as nombre
              FROM presupuesto.pre_catalogo
              where tipo= 'catalogo' and categoria = 'fuente'    order by 1 ";
      $resultado = $this->bd->ejecutar($sqlb);
      
  }
  
  
  if ( $titulo == 'ACTIVIDAD'){
      
      $sqlb = "SELECT  '-' as codigo, '[ -- 0. Actividad  -- ]'  AS nombre  union
             SELECT  trim(a.codigo) as codigo, a.detalle as nombre
              FROM presupuesto.pre_catalogo a, presupuesto.view_estructura b
              where a.tipo= 'catalogo' and 
                    a.categoria = 'actividad' and    
                    b.tipo = 'G' and 
                    b.anio= ".$this->bd->sqlvalue_inyeccion( $this->anio , true)." and 
                    b.actividad = a.codigo and
                     group by 1,2
                    order by 2 ";

      $resultado = $this->bd->ejecutar($sqlb);
      
  }
  
  
  if ( $titulo == 'GRUPO'){
      
      $sqlb = "SELECT  '-' as codigo, '[ 0. No aplica ]'  AS nombre  union
             SELECT  trim(codigo) as codigo, (codigo || ' ' || detalle) as nombre
              FROM presupuesto.pre_catalogo
              where tipo = 'arbol' and pac = 'S' and subcategoria = 'gasto' and nivel = 2  order by 1 ";
      $resultado = $this->bd->ejecutar($sqlb);
      
  }
  

  
  
  if ( $titulo == 'SUBGRUPO'){
      
      $sqlb = "SELECT  '-' as codigo, '[ 0. No aplica ]'  AS nombre  union
             SELECT  trim(codigo) as codigo, (codigo || ' ' || detalle) as nombre
              FROM presupuesto.pre_catalogo
               where tipo = 'arbol' and subcategoria = 'gasto' and nivel = 3  order by 1 ";
      $resultado = $this->bd->ejecutar($sqlb);
      
  }
  
  
  
  if ( $titulo == 'IDACTIVIDAD1'){
      $sqlb = "SELECT  0 as codigo, '[ 0. Matriz Actividades ]'  AS nombre  ";
      $resultado = $this->bd->ejecutar($sqlb);
  }
  
  if ( $titulo == 'FUNCIONARIO'){

      $sqlb = "SELECT  ' ' as codigo, '- 0. Matriz Funcionarios -'  AS nombre  union
               SELECT idprov as codigo ,razon as nombre 
              FROM view_nomina_user
              where responsable = 'S' order by 2";
       

                  
      $resultado = $this->bd->ejecutar($sqlb);
 }
  
  return $resultado;		
  

}

///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
  
 
   $gestion->FiltroFormulario( );

 ?>
<script type="text/javascript">

jQuery.noConflict(); 

jQuery('#clasificador').typeahead({
       source:  function (query, process) {
       return $.get('../model/ajax_lista_item.php', { query: query }, function (data) {
               console.log(data);
               data = $.parseJSON(data);
               return process(data);
           });
       } 
   });
   
 

 
</script>

 
  