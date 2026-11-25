<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

// retorna el valor del campo para impresion de pantalla

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
/*Creamos la instancia del objeto. Ya estamos conectados*/


 
$bd	   = new Db;

$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$accion = $_GET["accion"];


$sesion 	 =  trim($_SESSION['login']);
   
$hoy 	     =     date("Y-m-d"); 



$datos = $bd->query_array('ireport.ireport_vista_sesion',
                          'count(*) as nn, max(idreport_sesion) as idreport_sesion,max(wfiltro) as wfiltro', 
                          'wsesion='.$bd->sqlvalue_inyeccion($sesion,true). ' and 
                           guarda='.$bd->sqlvalue_inyeccion('N',true)
                        );

 if ( trim($accion) == 'editar' ){

        if ( $datos['nn'] > 0 ){

            $idreport_sesion = $datos['idreport_sesion'];

            $wfiltro    = trim($datos['wfiltro']);

            $wfiltro    = str_replace("_"," ", $wfiltro);
            $wfiltro    = str_replace("'","", $wfiltro);
            $wfiltro    = str_replace("=","", $wfiltro);
            $wfiltro    = str_replace("like","", $wfiltro);
            $wfiltro    = str_replace("%","", $wfiltro);
            $wfiltro    = str_replace("and"," ", $wfiltro);
            

            $wnomcolumna = 'Reporte '.$hoy.' ('.$wfiltro.')';
            
            $result_Doc = '<h5><b>Reporte Guardado con exito.... </b></h5>' ;
        
                $sql       = "UPDATE ireport.ireport_vista_sesion 
                                 SET guarda = ".$bd->sqlvalue_inyeccion('S',true).",
                                     wnomcolumna= ".$bd->sqlvalue_inyeccion($wnomcolumna,true).",
                                     fcreacion  = ".$bd->sqlvalue_inyeccion($hoy ,true)."
                                WHERE idreport_sesion = ".$bd->sqlvalue_inyeccion( $idreport_sesion, true) ;


                $bd->ejecutar($sql);

                echo  $result_Doc;
        }

 }



 

?>
  
  