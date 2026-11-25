<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión


session_start();

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/



$bd       =    new Db;

$obj     =     new objects;

// Credenciales: kconfig/config.local.php
$_cfg_db = kconfig_db('cbsd');
$servidor = $_cfg_db['host'];
$base_datos = $_cfg_db['dbname'];
$usuario = $_cfg_db['user'];
$password = $_cfg_db['password'];

$bd->conectar_sesion_externo($servidor, $base_datos, $usuario, $password);




$tipo = $bd->retorna_tipo();

$anio = date('Y');


$sql = "SELECT  orden_id,   orden_codigo, orden_doc_identidad  ,
                         orden_cliente_nombre,orden_concepto, orden_total,orden_email , orden_direccion 
                            FROM recaudacion.tb_ordenescobro
                            where orden_estado = 'GENERADA' 
							order by orden_id";
$sql = "SELECT  *
        FROM prevencion.vw_training
        where capacitacion_estado <> 'PENDIENTE' AND date_part('year',capacitacion_registro) = ".$anio."
        order by capacitacion_id DESC";


$resultado  = $bd->ejecutar($sql);


// filtro para fechas


while ($fetch = $bd->obtener_fila($resultado)) {


    $nnombre        = reemplazar($fetch['entidad_razonsocial']);

    $orden_concepto = reemplazar($fetch['tema_nombre']);



    $output[] = array(
        trim($fetch['capacitacion_id']),
        trim($fetch['capacitacion_codigo']),
        trim($fetch['persona_doc_identidad']),
        $nnombre,
        $orden_concepto,
        0,
        trim($fetch['persona_correo']),
        trim($fetch['tema_nombre'])
    );
}



kpg_free_result($resultado);

echo json_encode($output);

//------------------------------------------
function reemplazar($nombre)
{

    $nombre = trim($nombre);


    $resultado = str_replace('"', '',  $nombre);

    $resultado = str_replace('/', '',  $resultado);

    $resultado = str_replace('/', '',  $resultado);


    return $resultado;
}
