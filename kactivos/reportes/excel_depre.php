<?php
session_start();

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


$obj   =     new objects;
$bd       =    new Db;


$bd->conectar($_SESSION['us'], '', $_SESSION['ac']);


$id_bien_dep = $_GET['id_bien_dep'];
$tipo               = $bd->retorna_tipo();


$p = "' '";



$sql = "SELECT   id_bien as codigo_bien,cuenta,     descripcion, costo,   vidautil,
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
                where   id_bien_dep = " . $bd->sqlvalue_inyeccion($id_bien_dep, true) . "   
                order by descripcion";

$resultado    = $bd->ejecutar($sql);

//excel.php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header('Content-disposition: attachment; filename=' . 'DEPRECIACION_'. $id_bien_dep . '.xls');
header("Pragma: no-cache");
header("Expires: 0");

echo utf8_decode($obj->grid->KP_GRID_EXCEL($resultado, $tipo));
