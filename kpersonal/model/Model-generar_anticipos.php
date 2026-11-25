<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start();
require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/

$bd       =    new Db;
$bd->conectar($_SESSION['us'], '', $_SESSION['ac']);
$anio       =  $_SESSION['anio'];
$id_rol    =   $_GET["id_rol1"];

$rol = $bd->query_array(
    'nom_rol_pago',
    'id_periodo, mes, anio, registro,tipo',
    'id_rol=' . $bd->sqlvalue_inyeccion($id_rol, true)
);

$anio = $rol["anio"];
$id_config = 5;

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// NUEVA FUNCION
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 1 OBTENER TODOS LOS FUNCIONARIOS CON ANTICIPOS CONTABILIZADOS EN EL PERIODO
$sqlPersonal = "SELECT distinct a.idprov,s.razon FROM public.co_anticipo a INNER JOIN public.par_ciu s on a.idprov = s.idprov WHERE anio='" . $anio . "' and a.estado='contabilizado' order by razon";
$stmtPersonal = $bd->ejecutar($sqlPersonal);
while ($personal = $bd->obtener_fila($stmtPersonal)) {
    $idprov =  trim($personal['idprov']);
    // 2 BUSCAR EL ANTICIPO MAS RECIENTE EN CASO DE QUE TENGA MAS DE UNO (POR SEGURIDAD ASUMIR QUE SIEMPRE VA A TENER MAS DE UNO)
    $sqlAnticipos = "SELECT a.id_asiento,a.fecha,a.id_anticipo,a.solicita,a.mensual,a.plazo,s.razon as funcionario,g.razon as garante FROM public.co_anticipo a INNER JOIN public.par_ciu s on a.idprov = s.idprov INNER JOIN public.par_ciu g on a.idprov_ga = g.idprov WHERE a.anio='" . $anio . "' and a.estado='contabilizado' and a.idprov='" . $idprov . "' ORDER BY id_anticipo DESC limit 1";
    $stmtAnticipos = $bd->ejecutar($sqlAnticipos);
    while ($anticipos = $bd->obtener_fila($stmtAnticipos)) {
        $id_asiento = trim($anticipos['id_asiento']);
        $fecha = trim($anticipos['fecha']);
        $id_anticipo = trim($anticipos['id_anticipo']);
        $solicita = trim($anticipos['solicita']);
        $mensual = trim($anticipos['mensual']); // cuota mensual
        $plazo = trim($anticipos['plazo']);
        $funcionario = trim($anticipos['funcionario']);
        $garante = trim($anticipos['garante']);
        // 3 OBTENER TODO LO COBRADO EN LA CUENTA 112.01.03 AL FUNCIONARIO EN CUESTION 
        $sqlCobros = "SELECT * FROM public.view_aux WHERE anio='" . $anio . "' and estado='aprobado' and cuenta='112.01.03' and idprov='" . $idprov . "' and id_asiento > '" . $id_asiento . "'";
        $stmtCobros = $bd->ejecutar($sqlCobros);
        $sumaHaber = 0;
        while ($cobros = $bd->obtener_fila($stmtCobros)) {
            $haber = trim($cobros['haber']);
            $sumaHaber += $haber;
        }

        if (($solicita - $sumaHaber) > 0) { // todavía tiene valores por pagar
            $cuota = $mensual;
            $SaldoTotal = $solicita - $sumaHaber;
            if ($SaldoTotal < $mensual) {
                $cuota = $SaldoTotal;
            }

            $xx = $bd->query_array(
                'view_nomina_rol',
                'regimen,programa, sueldo,id_departamento,id_cargo,fecha',
                'idprov=' . $bd->sqlvalue_inyeccion(trim($idprov), true)
            );
            $x_id_config_reg = $bd->query_array(
                'nom_config_regimen',
                'id_config_reg,tipo_config',
                'id_config=' . $bd->sqlvalue_inyeccion($id_config, true) . ' and 
                            regimen=' . $bd->sqlvalue_inyeccion(trim($xx['regimen']), true) . ' and 
                            programa=' . $bd->sqlvalue_inyeccion(trim($xx['programa']), true)
            );
            $id_config_reg = $x_id_config_reg['id_config_reg'];
            $sql_valida = "select count(*) as numero
            from nom_rol_pagod
            where id_rol="        . $bd->sqlvalue_inyeccion($id_rol, true) . " and
                id_periodo= " . $bd->sqlvalue_inyeccion($rol["id_periodo"], true) . " and
                idprov= "    . $bd->sqlvalue_inyeccion(trim($idprov), true) . " and
                id_config =" . $bd->sqlvalue_inyeccion($id_config_reg, true);

            // verifica los datos para no duplica la generacoin
            $resultado2 = $bd->ejecutar($sql_valida);
            $rol_valida = $bd->obtener_array($resultado2);
            $ruc       =  trim($_SESSION['ruc_registro']);
            if ($rol_valida["numero"] >  0) {
                echo 'INFORMACION YA GENERADA';
            } else {
                $sql = "INSERT INTO nom_rol_pagod(
                            id_rol, id_periodo, idprov, id_config, ingreso, descuento, registro, anio, mes,
                            sueldo, id_departamento, id_cargo, regimen,dias,programa,fecha)
                        VALUES (" .
                    $bd->sqlvalue_inyeccion($id_rol, true) . "," .
                    $bd->sqlvalue_inyeccion($rol["id_periodo"], true) . "," .
                    $bd->sqlvalue_inyeccion(trim($idprov), true) . "," .
                    $bd->sqlvalue_inyeccion($id_config_reg, true) . "," .
                    $bd->sqlvalue_inyeccion(0, true) . "," .
                    $bd->sqlvalue_inyeccion($cuota, true) . "," .
                    $bd->sqlvalue_inyeccion($ruc, true) . "," .
                    $bd->sqlvalue_inyeccion($rol["anio"], true) . "," .
                    $bd->sqlvalue_inyeccion($rol["mes"], true) . "," .
                    $bd->sqlvalue_inyeccion($xx['sueldo'], true) . "," .
                    $bd->sqlvalue_inyeccion($xx['id_departamento'], true) . "," .
                    $bd->sqlvalue_inyeccion($xx['id_cargo'], true) . "," .
                    $bd->sqlvalue_inyeccion($xx['regimen'], true) . "," .
                    $bd->sqlvalue_inyeccion(30, true) . "," .
                    $bd->sqlvalue_inyeccion($xx['programa'], true) . "," .
                    $bd->sqlvalue_inyeccion($xx['fecha'], true) . ")";
                $bd->ejecutar($sql);
                echo 'INFORMACION ENLAZADA CON EXITO';
            }
        }
    }
}

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// FUNCION ORIGINAL
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// $sql = 'select  identificacion,
//                     funcionario,
//                      solicitado,
//                      coalesce(pagado,0) as pagado,
//                      solicitado - coalesce(pagado,0) saldo,
//                      mensual
//      from view_anticipo_res
//      where solicitado - coalesce(pagado,0) > 0 and
//            anio = ' . $bd->sqlvalue_inyeccion($anio, true) . ' order by funcionario';
// $resultado = $bd->ejecutar($sql);


// while ($x = $bd->obtener_fila($resultado)) {

//     $xx = $bd->query_array(
//         'view_nomina_rol',
//         'regimen,programa, sueldo,id_departamento,id_cargo,fecha',
//         'idprov=' . $bd->sqlvalue_inyeccion(trim($x['identificacion']), true)
//     );

//     $ingreso = $x['mensual'];
//     $saldo = $x['saldo'];
//     if ($saldo  < 1) {
//         $ingreso =   $saldo;
//     }

//     $x_id_config_reg = $bd->query_array(
//         'nom_config_regimen',
//         'id_config_reg,tipo_config',
//         'id_config=' . $bd->sqlvalue_inyeccion($id_config, true) . ' and 
//                 regimen=' . $bd->sqlvalue_inyeccion(trim($xx['regimen']), true) . ' and 
//                 programa=' . $bd->sqlvalue_inyeccion(trim($xx['programa']), true)
//     );

//     $id_config_reg = $x_id_config_reg['id_config_reg'];
//     $sql_valida = "select count(*) as numero
//             from nom_rol_pagod
//             where id_rol="        . $bd->sqlvalue_inyeccion($id_rol, true) . " and
//                 id_periodo= " . $bd->sqlvalue_inyeccion($rol["id_periodo"], true) . " and
//                 idprov= "    . $bd->sqlvalue_inyeccion(trim($x['identificacion']), true) . " and
//                 id_config =" . $bd->sqlvalue_inyeccion($id_config_reg, true);

//     // verifica los datos para no duplica la generacoin
//     $resultado2 = $bd->ejecutar($sql_valida);
//     $rol_valida = $bd->obtener_array($resultado2);
//     $ruc       =  trim($_SESSION['ruc_registro']);
//     if ($rol_valida["numero"] >  0) {
//         echo 'INFORMACION YA GENERADA';
//     } else {
//         $sql = "INSERT INTO nom_rol_pagod(
//                             id_rol, id_periodo, idprov, id_config, ingreso, descuento, registro, anio, mes,
//                             sueldo, id_departamento, id_cargo, regimen,dias,programa,fecha)
//                         VALUES (" .
//             $bd->sqlvalue_inyeccion($id_rol, true) . "," .
//             $bd->sqlvalue_inyeccion($rol["id_periodo"], true) . "," .
//             $bd->sqlvalue_inyeccion(trim($x['identificacion']), true) . "," .
//             $bd->sqlvalue_inyeccion($id_config_reg, true) . "," .
//             $bd->sqlvalue_inyeccion(0, true) . "," .
//             $bd->sqlvalue_inyeccion($ingreso, true) . "," .
//             $bd->sqlvalue_inyeccion($ruc, true) . "," .
//             $bd->sqlvalue_inyeccion($rol["anio"], true) . "," .
//             $bd->sqlvalue_inyeccion($rol["mes"], true) . "," .
//             $bd->sqlvalue_inyeccion($xx['sueldo'], true) . "," .
//             $bd->sqlvalue_inyeccion($xx['id_departamento'], true) . "," .
//             $bd->sqlvalue_inyeccion($xx['id_cargo'], true) . "," .
//             $bd->sqlvalue_inyeccion($xx['regimen'], true) . "," .
//             $bd->sqlvalue_inyeccion(30, true) . "," .
//             $bd->sqlvalue_inyeccion($xx['programa'], true) . "," .
//             $bd->sqlvalue_inyeccion($xx['fecha'], true) . ")";
//         $bd->ejecutar($sql);
//         echo 'INFORMACION ENLAZADA CON EXITO';
//     }
// }
