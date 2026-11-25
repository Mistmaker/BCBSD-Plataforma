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
      //-----------------------------------------------------------------------------------------------------------
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function componente( ){
            //inicializamos la clase para conectarnos a la bd
 
                $this->obj     = 	new objects;
                
                $this->set     = 	new ItemsController;
                   
            	$this->bd	   =	new Db;
             
                $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
      
                $this->ruc       =  $_SESSION['ruc_registro'];
                
                $this->sesion 	 =  $_SESSION['email'];
                
                $this->hoy 	     =  $this->bd->hoy();
        
                
               $this->formulario = 'Model-configuracion.php'; 
   
               $this->evento_form = '../model/Model-configuracion.php';        // eventos para ejecucion de editar eliminar y agregar 
      }
       //-----------------------------------------------------------------------------------------------------------
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function fecha( ){
          $todayh =  getdate();
          $d = $todayh[mday];
          $m = $todayh[mon];
          $y = $todayh[year];
          return '<h6>'.$d.'/'.$m.'/'.$y.'</h6>';
      }
      //---------------------------------------
      
     function Formulario( ){
      
         
         $titulo = '';
 
        $this->set->evento_formulario( $this->evento_form,'inicio' ); // activa ajax para insertar informacion
   
        $this->set->body_tab($titulo,'inicio');
 
        $tipo = $this->bd->retorna_tipo();

        $sqlb = "SELECT imodulo  as codigo , inombre as nombre
		         FROM ireport.ireport_grupo 
                 ORDER BY inombre";
  		
          $MATRIZ = array(
            'S'    => 'SI',
            'N'    => 'NO' 
        );
  	
  	    $resultado_tipo2 = $this->bd->ejecutar($sqlb);
 
    
                $this->BarraHerramientas();
 
                    // idusuario, login, estado, email, cedula , nombre, apellido, idciudad, direccion
                    // telefono, movil, tipo, clave, nomina, caja, supervisor,noticia,tarea ,url
 
                $datos = array();
                
                $this->set->div_panel9('<b> DATOS DE LA MATRIZ DE INFORMACION </b>');
                
                $this->obj->text->text('Id',"number",'idreport',0,10,$datos,'','readonly','div-2-4') ; 
                
 
                $this->obj->list->lista('Habilitado',$MATRIZ,'iestado',$datos,'required','','div-2-4');
             
                $this->obj->list->listadbe($resultado_tipo2,$tipo,'Modulo','imodulo',$datos,'required','','','div-2-10' );
                
                $this->obj->text->text_blue('<b>Nombre Reporte</b>',"texto" ,'inombre' ,80,80, $datos ,'required','','div-2-10') ;

                $this->obj->text->text('Detalle Reporte',"texto" ,'idetalle' ,80,80, $datos ,'required','','div-2-10') ;



                $this->obj->text->text_yellow('Vista BD',"texto" ,'ivista' ,80,80, $datos ,'required','','div-2-4') ;
 
                
                $this->obj->text->text('Esquema',"texto" ,'esquema' ,180,180, $datos ,'required','','div-2-4') ;
 
                
                
                
                 $this->set->div_panel9('fin');
               
               
                 $this->set->div_panel3('<b> PROCESO  VARIABLES</b>');
                 
                 echo '<button type="button" onClick="CargaDatos()" class="btn btn-success">Generar Variable(Campos) Reporte</button><br><br>';
                 
               
                 
                 echo '<h5>Generar variables para generar los reportes</h5>';
                 
                 
                 
                 echo '<div id="GuardaVariables"></div>';
                 
                 echo '<button type="button" onClick="DetalleVariables()" class="btn btn-info">Actualizar Variables</button><br><br>';
                 
                 echo '<button type="button" onClick="VisorDatos()" data-toggle="modal" data-target="#myModal" class="btn btn-default">Visor de Informacion</button><br><br>';
                 
                 $this->set->div_panel3('fin');
                 
                 
                 
                    
               $this->set->div_panel12('<b> VARIABLES DE GESTION  </b>');
                    
               
                    echo '<div id="DetalleVariables"></div>';
               
                   
               $this->set->div_panel12('fin');
                 
                	
                	 
                	 
                    
         $this->obj->text->texto_oculto("action",$datos); 
         
         $this->set->evento_formulario('-','fin'); 
 
  
      
   }
   //----------------------------------------------
   function BarraHerramientas(){
 
 
 
   
   $eventoi = "";
    
   
    $ToolArray = array( 
                array( boton => 'Nuevo Regitros',    evento =>'', grafico => 'icon-white icon-plus' ,  type=>"add"),
                array( boton => 'Guardar Registros-Grabar', evento =>'', grafico => 'glyphicon glyphicon-floppy-saved' ,  type=>"submit") ,
                array( boton => 'Imprimir Informe', evento =>$eventoi,  grafico => 'glyphicon glyphicon-print' ,  type=>"button")
                );
                  
   $this->obj->boton->ToolMenuDiv($ToolArray); 
 
  }  
   //----------------------------------------------
   function header_titulo($titulo){
 
         $this->set->header_titulo($titulo);
 
  }  
  
///------------------------------------------------------------------------
///------------------------------------------------------------------------
 }    
   $gestion   = 	new componente;
 
   $gestion->Formulario( );

 ?>


 
  