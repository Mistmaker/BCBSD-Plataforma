<?php
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
    private $anio;
    //-----------------------------------------------------------------------------------------------------------
    //Constructor de la clase
    //-----------------------------------------------------------------------------------------------------------
    function proceso( ){
        //inicializamos la clase para conectarnos a la bd
        
        $this->obj     = 	new objects;
        $this->bd     = 	new Db;
        
        $this->ruc       =  $_SESSION['ruc_registro'];
        $this->sesion 	 =  $_SESSION['login'];
        $this->hoy 	     =  $this->bd->hoy();
        
        $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
       
        $this->anio       =  $_SESSION['anio'];
        
    }
    //--------------------------------------
    function historial($anio,$id_acta,$mes,$cuenta_depre,$vorden){
          
        
         $sql = "SELECT   b.cuenta,_cuenta(b.cuenta) as detalle, 
                        sum(b.valor_residual) valor_residual,
                        count(*) as nn,
                        sum(b.costo_adquisicion) costo_adquisicion,
                        sum(b.valor_depreciacion) valor_depreciacion
                FROM activo.ac_movimiento_det a, activo.view_bienes b 
                where a.id_acta = ".$this->bd->sqlvalue_inyeccion($id_acta,true)." and 
                      a.id_bien = b.id_bien
                group by  b.cuenta" ;
 
                    
        
        $resultado    =  $this->bd->ejecutar($sql);
          
        echo ' <table class="table table-responsive"   width="100%" style="font-size:12px" id="TablaResumenc" >
            <thead>
                <tr>
                     <th width="10%">Cuenta</th>
                     <th width="25%">Detalle</th>
                     <th align="right" width="10%">Debe</th>
                     <th align="right" width="10%">Haber</th>
                      </tr>
            </thead><tbody>';
 
        $a1 = 0;
        
        
         $a2 = 0;
        
       
        while($row=pg_fetch_assoc ($resultado)) {
                
            //---141.99.08 D
            
            $cuenta_datos = explode('.',$row['cuenta']);
            
            $parte1 = $cuenta_datos[0];
                
            $parte2 = $cuenta_datos[2];
                
                
             $cuenta1 =   $parte1.'.99.'.$parte2;
                
             $gasto = $row['costo_adquisicion'] - $row['valor_depreciacion']; 
             
            
            echo '<tr>
                 <td> <b>'.$cuenta_depre.' </b></td>
                 <td> '.'Gasto por Pérdida de Activos Fijos ('. $row['detalle'].') </td>
                 <td align="right"> '. number_format(   $gasto ,2).' </td>
                 <td align="right"> 0.00</td>
              </tr>';
            
            echo '<tr>
                 <td> <b>'. $cuenta1.' </b></td>
                 <td> '.'(-) Depreciacion Acumulada Bienes ('. $row['detalle'].') </td>
                 <td align="right">  '. number_format($row['valor_depreciacion'],2).' </td>
                 <td align="right"> 0.00 </td>
              </tr>';

                echo '<tr>
                 <td> <b>'.$row['cuenta'].' </b></td>
                 <td> '. $row['detalle'].' </td>
                 <td align="right"> 0.00 </td>
                 <td align="right"> '. number_format($row['costo_adquisicion'],2).'</td>
              </tr>';
            
                 $a1 = $a1 + $row['costo_adquisicion'];
                
                 $a2 =  $a2  +  $gasto;


             
        }
        
          if ( trim($vorden) == 'S'){

                    echo '<tr>
                    <td> <b>911.47.01</b></td>
                    <td> '.'Pérdida de Activos Fijos ('. $row['detalle'].') </td>
                    <td align="right"> '. number_format(   $a2 ,2).' </td>
                    <td align="right"> 0.00</td>
                    </tr>';

                    echo '<tr>
                    <td> <b> 921.47.01 </b></td>
                    <td> '.'Responsabilidad Por pérdida de Activos Fijos ('. $row['detalle'].') </td>
                     <td align="right"> 0.00</td>
                     <td align="right"> '. number_format(   $a2 ,2).' </td>
                </tr>';

                   $a1 = $a1 +  $a2;
              }

        
        echo '<tr>
                 <td> </td>
                 <td> </td>
                 <td align="right"><b> '. number_format($a1,2).'</b> </td>
                 <td align="right"> <b>'. number_format($a1,2).' </b></td>
              </tr>';
        
        
        echo "</tbody></table>";
        
        
    }
    
}
//-------------------------

$gestion         = 	new proceso;

 
    $anio           = $_GET['anio'];
    $id_acta        = $_GET['id_acta'];
    $mes            = $_GET['mes'];
    $cuenta_depre   = $_GET['cuenta_depre'];

    $vorden         = $_GET['vorden'];
    
    $gestion->historial($anio,$id_acta,$mes,$cuenta_depre, $vorden ) ;
     

?>
 
 
