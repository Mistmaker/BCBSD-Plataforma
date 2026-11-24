<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

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
    function historial($id){
          
        
       

              
        $sq1_excel = "SELECT   id_bien as codigo_bien,cuenta,     descripcion, costo,   vidautil,
                        anio_bien || '  ' AS anio_bien, 
                        cuotadp, diferencia ,acumulado,valor_residual,
												 CASE
												WHEN (anio_actual - anio_adquisicion) <= vidautil  THEN valor_periodo
												ELSE 0.00
											END AS valor_periodo,
												anio_adquisicion,
												anio_actual,
                        anio_actual - anio_adquisicion as tiempo,fecha_adquisicion
        FROM activo.view_bienes_depre
                where   id_bien_dep = ".$this->bd->sqlvalue_inyeccion($id,true)."   
                order by descripcion";
                

         $_SESSION['sql_activo'] =  $sq1_excel;
      
        $sql = "SELECT  id_bien_dep, id_bien,cuenta,     descripcion, costo,vresidual,   vidautil,
                        anio_bien || '  ' AS anio_bien, 
                        cuotadp, diferencia ,acumulado,valor_residual,valor_periodo,anio_adquisicion,anio_actual,
                        anio_actual - anio_adquisicion as perido,mes_util,fecha_adquisicion
        FROM activo.view_bienes_depre
                where   id_bien_dep = ".$this->bd->sqlvalue_inyeccion($id,true)." order by descripcion";
                    
        
   

        $resultado    =  $this->bd->ejecutar($sql);
          
        echo '<table class="table table-responsive"   width="100%" style="font-size:11px" id="TablaAsignada" >
            <thead>
                    <tr>
                    <th width="5%" align="center" rowspan="2">Nro.Bien</th>
                    <th width="5%" align="center" rowspan="2"> Cuenta</th>
                    <th width="25%" align="center" rowspan="2">Detalle</th>
                    <th colspan="3" align="center">Costo Bien</th>
                    <th colspan="2" align="center">Costo Proporcional Depreciacion</th>
                    <th width="10%"  align="center" rowspan="2">C = (A) - (B)</th>
                    <th width="5%" align="center" rowspan="2">Dias utilizacion</th>
                    <th width="5%" align="center" rowspan="2">Nro.Mes(es)</th>
                    <th width="5%" align="center" rowspan="2">Valor Periodo</th>
                    <th width="5%" align="center" rowspan="2">Adquisicion</th>
                    <th width="5%" align="center" rowspan="2">  </th>
                </tr>
                <tr>
                    <th align="center" width="10%">(A) Costo</th>
                    <th align="center" width="5%">Residual</th>
                    <th align="center" width="5%">Vida Util</th>
                    <th align="center" width="5%">Anio</th>
                    <th align="center" width="10%">(B) CDP</th>
                    </tr>
            </thead><tbody>';
         
 
        while($row=kpg_fetch_assoc ($resultado)) {
             
            
            $cadena =  $row['anio_bien'];
            $entero = intval($cadena);  

            $anio_adquisicion = $row['anio_bien'];
            $vidautil = $row['vidautil'];

            $anio_valida = $row['anio_actual'] -    $anio_adquisicion; 

            $descripcion=    $row['descripcion'];

            if (    $anio_valida > $vidautil){
                $descripcion = '<span style="color: #FF0004">'.   $descripcion.'</span>';
                $entero = '<span style="color: #FF0004">'.   $entero.'</span>';
            }


 
            echo '<tr>
             <td>'.$row['id_bien'].' </td>
             <td> '. $row['cuenta'].' </td>
             <td> <b>'.   $descripcion .' </b></td>
             <td> '. kround($row['costo'],2).' </td>
             <td> '. kround($row['valor_residual'],2).' </td>
             <td> '. $row['vidautil'].' </td>
             <td> '. $entero.' </td>
              <td> '. kround($row['cuotadp'],2).' </td>
              <td> '. kround($row['diferencia'],2).' </td>
            <td> '. kround($row['acumulado'],2).' </td>
            <td> '. kround($row['mes_util'],2).' </td>
            <td> '. kround($row['valor_periodo'],4).' </td>
               <td> '. $row['fecha_adquisicion'].' </td>
            <td> <a href="#" onClick="EliminarBiend('.$row['id_bien'].')" title="eliminar bien">  <span class="glyphicon glyphicon-trash"></span>    </a> </td>
             </tr>';
        }
        
        echo "</tbody></table>";
        
        $_SESSION['sql_activo'] =  $sq1_excel;
        
    }
    
}
//-------------------------

$gestion         = 	new proceso;


if (isset($_GET['id_bien_dep']))	{
    
    $id    = $_GET['id_bien_dep'];
    
    $gestion->historial($id) ;
    
}
 


?>
<script>
var $jq = jQuery.noConflict();
$jq( document ).ready(function( $ ) {
	   $jq('#TablaAsignada').DataTable();
});
 
</script>
 
