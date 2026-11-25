<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?><script >// <![CDATA[

jQuery.noConflict(); 

jQuery(document).ready(function() {
  
// InjQueryerceptamos el evento submit
jQuery('#form, #fat, #fo3').submit(function() {
  // Enviamos el formulario usando AJAX
    jQuery.ajax({
        type: 'POST',
        url: jQuery(this).attr('action'),
        data: jQuery(this).serialize(),
        // Mostramos un mensaje con la respuesta de PHP
        success: function(data) {

            jQuery('#result').html(data);

           jQuery( "#result" ).fadeOut( 1600 );

           jQuery("#result").fadeIn("slow");

           
  }
    })        
    return false;
}); 
})
</script>	
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
          
 
          $datos['ffecha1'] = $this->anio.'-01-01';
          
          $datos['ffecha2'] = $this->bd->_actual_dia($this->hoy);
          
          
          $this->obj->text->text(' Inicio',"date",'ffecha1',15,15,$datos,'required','','div-0-2');
          $this->obj->text->text(' Final',"date",'ffecha2',15,15,$datos,'required','','div-0-2');
          
         

          $resultado = $this->bd->ejecutar("select '-' as codigo , '  [  02. Seleccionar partida ]' as nombre union
          SELECT partida AS codigo, partida || ' ' || detalle  as nombre
           FROM presupuesto.view_dettramites
           where anio = ".$this->bd->sqlvalue_inyeccion($this->anio,true)."
                group by  partida,detalle   ORDER BY 2 ");

          $this->obj->list->listadb($resultado,$tipo,'Partida','partidac',$datos,'required','','div-0-5');
      	
  
          $this->obj->text->textautocomplete('Buscar x partida',"texto",'clasificador',140,145,$datos,'required','','div-0-3');

          
      
      }
///------------------------------------------------------------------------
///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
  
 
   $gestion->FiltroFormulario( );

 ?>
<script type="text/javascript">

jQuery.noConflict(); 

jQuery('#clasificador').typeahead({
       source:  function (query, process) {
       return $.get('../model/ajax_lista_partida.php', { query: query }, function (data) {
               console.log(data);
               data = $.parseJSON(data);
               return process(data);
           });
       } 
   });
   
 

 
</script>

 

 
  