<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

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
    function __construct( ){
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
    function historial($anio,$tipo,$mes,$cuenta_depre){
          
        
       
        
        if ( $tipo== 'A'){
            
            $sql = "SELECT  cuenta, nombre_cuenta,
                       clasificador, count(*) as bienes,   sum(costo) compra,
                       sum(vresidual) as residual,  sum(cuotadp) as cdp,
                       sum(valor_periodo) as valor_periodo
                    FROM activo.view_bienes_depre
                    where anio_actual  = ".$this->bd->sqlvalue_inyeccion($anio,true)." and
                          tipo_depre = 'A' and
                          bandera = 'S'   
                    group by cuenta, nombre_cuenta, clasificador
                    order by cuenta";
        }else{
            
            
            $sql = "SELECT  cuenta, nombre_cuenta,
                       clasificador, count(*) as bienes,   sum(costo) compra,
                       sum(vresidual) as residual,  sum(cuotadp) as cdp,
                       sum(valor_periodo) as valor_periodo
                    FROM activo.view_bienes_depre
                    where anio_actual  = ".$this->bd->sqlvalue_inyeccion($anio,true)." and
                          tipo_depre = 'M' and
                          bandera = 'S'    and 
                          mes =".$this->bd->sqlvalue_inyeccion($mes,true)."
                    group by cuenta, nombre_cuenta, clasificador
                    order by cuenta";
            
        }
                    
        
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
        
        
       
        while($row=kpg_fetch_assoc ($resultado)) {
                
            //---141.99.08 D
            
            $cuenta_datos = explode('.',$row['cuenta']);
            
         $parte1 = $cuenta_datos[0];
            
         $parte2 = $cuenta_datos[2];
            
            
            $cuenta1 =   $parte1.'.99.'.$parte2;
            
          
            
            echo '<tr>
                 <td> <b>'.$cuenta_depre.' </b></td>
                 <td> '.'(-) Depreciacion Bienes ('. $row['nombre_cuenta'].') </td>
                 <td align="right"> '. knumber_format($row['valor_periodo'],2).' </td>
                 <td align="right"> 0.00 </td>
              </tr>';
            
            echo '<tr>
                 <td> <b>'. $cuenta1.' </b></td>
                 <td> '. $row['nombre_cuenta'].' </td>
                 <td align="right"> 0.00 </td>
                 <td align="right"> '. knumber_format($row['valor_periodo'],2).' </td>
              </tr>';
            
            $a1 = $a1 + $row['valor_periodo'];
 
 
            
        }
        
        
        echo '<tr>
                 <td> </td>
                 <td> </td>
                 <td align="right"><b> '. knumber_format($a1,2).'</b> </td>
                 <td align="right"> <b>'. knumber_format($a1,2).' </b></td>
              </tr>';
        
        
        echo "</tbody></table>";
        
        
    }
    
}
//-------------------------

$gestion         = 	new proceso;

if (isset($_GET['anio']))	{
    
    $anio    = $_GET['anio'];
    $tipo    = $_GET['tipo'];
    $mes     = $_GET['mes'];
    $cuenta_depre = $_GET['cuenta_depre'];
    
    $gestion->historial($anio,$tipo,$mes,$cuenta_depre) ;
    
}


?>
 
 
