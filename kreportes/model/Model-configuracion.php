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
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function div_resultado($accion,$id,$tipo){
            //inicializamos la clase para conectarnos a la bd
      
        
             echo '<script type="text/javascript">';
             
             echo  '$("#action").val("'.$accion.'");';
             
             echo  '$("#idreport").val("'.$id.'");';
             
             echo '</script>';
             
             
         
             if ($tipo == 0){
                
                  if ($accion == 'editar')
                      $resultado = '<img src="../../kimages/kedit.png" align="absmiddle" />&nbsp;<b>EDITAR REGISTRO TRANSACCION ?</b>';
                      if ($accion == 'del')    
                          $resultado = '<img src="../../kimages/kdel.png" align="absmiddle" />&nbsp;<b>ELIMINAR REGISTRO TRANSACCION ?</b>';
                          
             }
             
             if ($tipo == 1){
                
                  
                 $resultado = '<img src="../../kimages/ksavee.png" align="absmiddle"/>&nbsp;<b>INFORMACION ACTUALIZADA CON EXITO ['.$id.']</b>';
                 
             }
             
             
            return $resultado;   
 
      }
 
      //-----------------------------------------------------------------------------------------------------------
      //Constructor de la clase
      //-----------------------------------------------------------------------------------------------------------
      function div_limpiar( ){
            //inicializamos la clase para conectarnos a la bd
       
             $resultado = '';
             
             echo '<script type="text/javascript">';
              
             echo  'LimpiarPantalla();';               
   
             echo '</script>';
 
            return $resultado;   
 
      }     
      	      
 //--------------------------------------------------------------------------------
    //--- busqueda de por codigo para llenar los datos
 //--------------------------------------------------------------------------------
 function consultaId($accion,$id ){
 
 
          
 $qquery = array( 
                    array( 'campo' => 'idreport',   'valor' =>$id ,  'filtro' => 'S',   'visor' => 'S'),
                        array( 'campo' => 'inombre',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S'),
                        array( 'campo' => 'ivista',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S'),
                        array( 'campo' => 'imodulo',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S'),
                        array( 'campo' => 'iestado',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S'),
                        array( 'campo' => 'idetalle',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S') ,
                        array( 'campo' => 'esquema',   'valor' => '-',  'filtro' => 'N',   'visor' => 'S') 
                     );
 

        $this->bd->JqueryArrayVisor('ireport.ireport',$qquery );           
  
        $result =  $this->div_resultado($accion,$id,0);
     
        echo  $result;
      }	
  
      //--------------------------------------------------------------------------------
      //---retorna el valor del campo para impresion de pantalla ?articulo=' + articulo + '&id='+id_asiento,
      //--------------------------------------------------------------------------------        
 
     function xcrud($action,$id){
          
 
                 // ------------------  agregar
                 if ($action == 'add'){
                    
                     $this->agregar( );
                 
                 }  
                 // ------------------  editar
                 if ($action == 'editar'){
        
                     $this->edicion($id );
     
                 }  
                 // ------------------  eliminar
                  if ($action == 'del'){
        
                     $this->eliminar($id );
     
                 }  
 
     }  
     function pathFile($id ){
         
         
         $ACarpeta = $this->bd->query_array('wk_config',
             'carpetasub',
             'tipo='.$this->bd->sqlvalue_inyeccion($id,true)
             );
         
         $carpeta = trim($ACarpeta['carpetasub']);
         
         return $carpeta;
         
     }
     //--------------------------------------------------------------------------------
      //---retorna el valor del campo para impresion de pantalla ?articulo=' + articulo + '&id='+id_asiento,
      //--------------------------------------------------------------------------------        
     function agregar(   ){
       
            $InsertQuery = array(   
                                     array( 'campo' => 'inombre',   'valor' => @$_POST["inombre"]),
                                    array( 'campo' => 'ivista',   'valor' => @$_POST["ivista"]),
                                    array( 'campo' => 'imodulo',   'valor' => @$_POST["imodulo"]),
                                    array( 'campo' => 'iestado',   'valor' => @$_POST["iestado"]),
                                    array( 'campo' => 'idetalle',   'valor' => @$_POST["idetalle"]),
                                    array( 'campo' => 'esquema',   'valor' => @$_POST["esquema"])
                                 );
 
                                 
                    
            $this->bd->pideSq(1);
            
            $idD = $this->bd->JqueryInsertSQL('ireport.ireport',$InsertQuery,'ireport.ireport_idreport_seq');
              
            
         
            //------------ seleccion de periodo
       
           $result = $this->div_resultado('editar',$idD,1);
   
            echo $result;
          
     }	
   
      //--------------------------------------------------------------------------------
      //--- edicion de registros
      //--------------------------------------------------------------------------------              
       function edicion($id  ){
 
  
           
             $UpdateQuery = array( 
                        array( 'campo' => 'idreport',   'valor' => $id ,  'filtro' => 'S'),
                        array( 'campo' => 'inombre',   'valor' => @$_POST["inombre"],  'filtro' => 'N'),
                        array( 'campo' => 'ivista',      'valor' => @$_POST["ivista"],  'filtro' => 'N'),
                        array( 'campo' => 'imodulo',      'valor' => @$_POST["imodulo"],  'filtro' => 'N'),
                        array( 'campo' => 'iestado',      'valor' => @$_POST["iestado"],    'filtro' => 'N'),
                        array( 'campo' => 'idetalle',      'valor' => @$_POST["idetalle"],    'filtro' => 'N'),
                        array( 'campo' => 'esquema',      'valor' => @$_POST["esquema"],    'filtro' => 'N')
                );
                
             
           $this->bd->JqueryUpdateSQL('ireport.ireport',$UpdateQuery);
             
           $result = $this->div_resultado('editar',$id,1);
 
 
           echo $result;
    }
  
      //--------------------------------------------------------------------------------
      //--- eliminar de registros
      //--------------------------------------------------------------------------------              
     function eliminar($id ){
      
               
    //     $sql = 'delete from par_usuario  where idusuario='.$this->bd->sqlvalue_inyeccion($id, true);
         
       //  $this->bd->ejecutar($sql);
        
         $this->div_limpiar();
       
        $result = '<img src="../../kimages/ksavee.png" align="absmiddle"/>&nbsp;<b>INFORMACION ELIMINADA CON EXITO ['.$id.']</b>';
   
        echo $result;
      
   }
   
 }    
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
 
    $gestion   = 	new proceso;
 
 
    //------ poner informacion en los campos del sistema
     if (isset($_GET['accion']))	{
            
            $accion    = $_GET['accion'];
            
            $id        = $_GET['id'];
            
            $gestion->consultaId($accion,$id);
     }  
  
      //------ grud de datos insercion
     if (isset($_POST["action"]))	{
        
            $action = @$_POST["action"];
        
            $id =     @$_POST["idreport"];
        
           $gestion->xcrud(trim($action),$id );
           
    }      
  
     
   
 ?>
 
  