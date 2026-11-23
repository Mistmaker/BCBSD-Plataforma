<?php
session_start();

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/

require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


$bd       = new Db;

$registro = $_SESSION['ruc_registro'];

$idbodega = $_SESSION['idbodega'];


$bd->conectar($_SESSION['us'], '', $_SESSION['ac']);


$detalle = trim($_GET['itemVariable']);
$modulo = trim($_GET['modulo']);

$idproducto = 0;

if (!empty($detalle)) {

    // separa el nombre del producto del precio, si es que viene con el formato "Producto $Precio"
    if (strpos($detalle, '$') !== false) {
        // $detalle = explode('$', $detalle)[0]; // Tomamos solo la parte del producto
        //CONCAT(producto,' $', round(costo,2))
    }

    if ($modulo == 'I') {
        $sql = "SELECT   idproducto
        				  FROM web_producto
        				  where producto  = " . $bd->sqlvalue_inyeccion(trim($detalle), true) . " AND
                                registro = " . $bd->sqlvalue_inyeccion($registro, true) . " AND
                                idbodega = " . $bd->sqlvalue_inyeccion($idbodega, true);
        if (strpos($detalle, '$') !== false) {
            //CONCAT(producto,' $', round(costo,2))
            $sql = "SELECT   idproducto
        				  FROM web_producto
        				  where CONCAT(producto,' $', round(costo,2))  = " . $bd->sqlvalue_inyeccion(trim($detalle), true) . " AND
                                registro = " . $bd->sqlvalue_inyeccion($registro, true) . " AND
                                idbodega = " . $bd->sqlvalue_inyeccion($idbodega, true);
        }
    } else {
        $sql = "SELECT   idproducto
        				  FROM web_producto
        				  where producto  = " . $bd->sqlvalue_inyeccion(trim($detalle), true) . " AND
                                registro = " . $bd->sqlvalue_inyeccion($registro, true) . " AND
                                idbodega = " . $bd->sqlvalue_inyeccion($idbodega, true) . " AND saldo > 0";
    }


    // $sql = "SELECT   idproducto
    // 			  FROM web_producto
    // 			  where producto  = ".$bd->sqlvalue_inyeccion(trim($detalle), true) ." AND
    //                     registro = ".$bd->sqlvalue_inyeccion($registro,true)." AND
    //                     idbodega = ".$bd->sqlvalue_inyeccion($idbodega,true);


    $resultado1 = $bd->ejecutar($sql);

    $datos_sql  = $bd->obtener_array($resultado1);

    $idproducto =   $datos_sql['idproducto'];
}

echo trim($idproducto);
