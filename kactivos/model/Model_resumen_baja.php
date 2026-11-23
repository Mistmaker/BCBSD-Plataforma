<?php
session_start( );
require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/

require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
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
        
        $this->set     = 	new ItemsController;
        
        $this->ruc       =  $_SESSION['ruc_registro'];
        $this->sesion 	 =  $_SESSION['login'];
        $this->hoy 	     =  $this->bd->hoy();
        
        $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
       
        $this->anio       =  $_SESSION['anio'];
        
    }
    //--------------------------------------
    function historial($anio,$id_acta,$mes){
          
        


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
          
        echo ' <table class="table table-responsive"   width="100%" style="font-size:12px" id="TablaResumen" >
            <thead>
                <tr>
                     <th width="10%">Cuenta</th>
                     <th width="40%">Detalle</th>
                      <th align="right" width="10%">Nro.Bienes</th>
                     <th align="right" width="10%">Costo Adquisicion</th>
                     <th align="right" width="10%">Valor Residual</th>
                     <th align="right" width="10%">CDP ($)</th>
                      </tr>
            </thead><tbody>';
 
        $a1 = 0;
        $a2 = 0;
        $a3 = 0;
        $a4 = 0;
        $a5 = 0;
 
        while($row=pg_fetch_assoc ($resultado)) {
                
            echo '<tr>
                 <td> <b>'. $row['cuenta'].' </b></td>
                 <td> '. $row['detalle'].' </td>
                  <td align="right"> '. $row['nn'].' </td>
                 <td align="right"> '. number_format($row['costo_adquisicion'],2).' </td>
                 <td align="right"> '. number_format($row['valor_residual'],2).' </td>
                 <td align="right"> '. number_format($row['valor_depreciacion'],2).' </td>
               </tr>';
            
            $a1 = $a1 + $row['costo_adquisicion'];
            $a2 = $a2 + $row['valor_residual'];
            $a3 = $a3 + $row['valor_depreciacion'];
            
            
        }
        
        
        echo '<tr>
                 <td> </td>
                  <td> </td>
                  <td> </td>
                 <td align="right"> <b>'. number_format($a1,2).'</b> </td>
                 <td align="right"><b> '. number_format($a2,2).'</b> </td>
                 <td align="right"> <b>'. number_format($a3,2).' </b></td>
               </tr>';
        
        
        echo "</tbody></table>";
        
        
        $this->set->div_label(12,'INFORMACION ENLACE FINANCIERO');
        
        $tipo_db = $this->bd->retorna_tipo();
        
        $union = "Select '-' as codigo, ' [ No aplica ] ' as nombre union" ;
        
        $datos = array();

        $MATRIZ = array(
      	    'S'    => 'Registro de cuenta de orden por pérdida de bienes',
      	    'N'    => 'No Aplica'
      	);

         
        $resultado = $this->bd->ejecutar($union." select trim(cuenta) as codigo, (trim(cuenta) || '. ' || trim(detalle))  as nombre
								                        from co_plan_ctas
                                                        where univel = 'S' and
                                                              anio = ".$this->bd->sqlvalue_inyeccion($this->anio ,true)." and
                                                              registro = ".$this->bd->sqlvalue_inyeccion($this->ruc,true)." and
                                                              substring(trim(cuenta),1,3) in ( '639') ORDER BY 1");
        
        $this->obj->list->listadb($resultado,$tipo_db,'Cuenta Cierre','cuenta_depre',$datos,'','','div-2-4');
        
        

      	$evento = '';
      	$this->obj->list->listae('',$MATRIZ,'vorden',$datos,'','',$evento,'div-0-4');

           
        
    }

 
    
}
//-------------------------

$gestion         = 	new proceso;

 
    $anio    = $_GET['anio'];
    $id_acta    = $_GET['id_acta'];
    $mes     = $_GET['mes'];
    
    $gestion->historial($anio,$id_acta,$mes) ;
    
 

?>
 
 
