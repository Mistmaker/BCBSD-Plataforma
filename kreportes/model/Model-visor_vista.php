<?php
session_start( );

// retorna el valor del campo para impresion de pantalla

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
/*Creamos la instancia del objeto. Ya estamos conectados*/


$obj     = 	new objects;
 
$bd	   = new Db;


$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$vista   = $_GET["ivista"] ;
$esquema = $_GET["esquema"] ;

$tabla = trim($esquema ).'.'.trim($vista);

    $qquery = array(
        array( campo => 'inom_columna',   	valor => '-',  filtro => 'N',   visor => 'S' , order=>'-'),
        array( campo => 'itipo',   	valor => '-',  filtro => 'N',   visor => 'S', order=>'-'),
        array( campo => 'ilong',   	valor => '-',  filtro => 'N',   visor => 'S' , order=>'-'),
        array( campo => 'icolumna', valor => '-',  filtro => 'N',   visor => 'S' , order=>'-'),
        array( campo => 'ivista',   valor => $vista,  filtro => 'S',   visor => 'S', order=>'-'),
        array( campo => 'iestado',  valor => '-' ,  filtro => 'N',   visor => 'S', order=>'-'),
    );
    
 
    $bd->_order_by('ilong');
    
    $resultado = $bd->JqueryCursorVisor('ireport.ireport_vista',$qquery );
    
    $nombreD = '';
    
    while ($fetch=$bd->obtener_fila($resultado)){
        $nombreD .=  trim($fetch['icolumna']).',';
        
    }


    $longitud = strlen($nombreD);
    
    $cadena = substr($nombreD, 0,$longitud -1 );
    
 
    
    
    $sql = 'SELECT '.$cadena.' 
            FROM '.$tabla.' limit 5 offset 0';
    
    $sql_ve =  'SELECT  *
                  FROM '.$tabla.' limit 5 offset 0';

     echo '<div class="col-md-12"> 
                <div class="col-md-4"> <b>  
                    '.$sql_ve.'</b>
                </div>     
         </div>
   <div class="col-md-12"> 
          <h5>Tabla de datos</h5>
          <div class="scrolly">
   ';

 
    $tipo = $bd->retorna_tipo();
    
    $resultado  = $bd->ejecutar($sql);
    
    $obj->grid->KP_GRID_CTAA($resultado,$tipo,'cuenta','','N','','','','');
    
    $resultado  = $bd->ejecutar($sql);
 
    echo '</div></div>';
 

?>
  
  