<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start();

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


$obj   =       new objects;
$bd         =      new Db;

$bd->conectar($_SESSION['us'], '', $_SESSION['ac']);

$anio       =  $_SESSION['anio'];

//      $tipo = $bd->retorna_tipo();



//      $sql = 'select  identificacion,
//                     funcionario,
//                      solicitado, 
//                      coalesce(pagado,0) as pagado,
//                      solicitado - coalesce(pagado,0) saldo, 
//                      mensual --,mes as MES_SOLICITA,plazo
//      from view_anticipo_res
//      where solicitado - coalesce(pagado,0) > 0 and
//            anio = '.$bd->sqlvalue_inyeccion( $anio , true).' order by funcionario';


//     $resultado = $bd->ejecutar($sql);

//     $variables  = 'id_rol=0';

//     //$obj->grid->KP_sumatoria(7,"Ingreso","Descuento", "Pagar","");

//     $obj->grid->KP_GRID_POP_NOM($resultado,$tipo,'id', $variables,'N','','','' ,$bd  );

// PRUEBA DE BUSQUEDA CORRECTA DE SALDOS
$filas = "";
$sumaSolicitado = 0;
$sumaPagado = 0;
$sumaSaldo = 0;
// 1 OBTENER TODOS LOS FUNCIONARIOS CON ANTICIPOS CONTABILIZADOS EN EL PERIODO
$sqlPersonal = "SELECT distinct a.idprov,s.razon FROM public.co_anticipo a INNER JOIN public.par_ciu s on a.idprov = s.idprov INNER JOIN public.nom_personal p on p.idprov = a.idprov WHERE anio='" . $anio . "' and a.estado='contabilizado' and p.fecha_salida is null order by razon";
$stmtPersonal = $bd->ejecutar($sqlPersonal);
while ($personal = $bd->obtener_fila($stmtPersonal)) {
      $idprov =  trim($personal['idprov']);
      // echo '<li class="list-group-item">(1) - '.$idprov.'</li>';

      // 2 BUSCAR EL ANTICIPO MAS RECIENTE EN CASO DE QUE TENGA MAS DE UNO (POR SEGURIDAD ASUMIR QUE SIEMPRE VA A TENER MAS DE UNO)
      $sqlAnticipos = "SELECT a.id_asiento,a.fecha,a.id_anticipo,a.solicita,a.mensual,a.plazo,s.razon as funcionario,g.razon as garante FROM public.co_anticipo a INNER JOIN public.par_ciu s on a.idprov = s.idprov INNER JOIN public.par_ciu g on a.idprov_ga = g.idprov WHERE a.anio='" . $anio . "' and a.estado='contabilizado' and a.idprov='" . $idprov . "' ORDER BY id_anticipo DESC limit 1";
      $stmtAnticipos = $bd->ejecutar($sqlAnticipos);
      while ($anticipos = $bd->obtener_fila($stmtAnticipos)) {
            $id_asiento = trim($anticipos['id_asiento']);
            $fecha = trim($anticipos['fecha']);
            $id_anticipo = trim($anticipos['id_anticipo']);
            $solicita = trim($anticipos['solicita']);
            $mensual = trim($anticipos['mensual']);
            $plazo = trim($anticipos['plazo']);
            $funcionario = trim($anticipos['funcionario']);
            $garante = trim($anticipos['garante']);
            // echo '<li class="list-group-item">----------(2) - '.$id_asiento.'  - '.$fecha.'  - '.$id_anticipo.'  - '.$solicita.'  - '.$mensual.' </li>';

            // 3 OBTENER TODO LO COBRADO EN LA CUENTA 112.01.03 AL FUNCIONARIO EN CUESTION 
            $sqlCobros = "SELECT * FROM public.view_aux WHERE anio='" . $anio . "' and estado='aprobado' and cuenta='112.01.03' and idprov='" . $idprov . "' and id_asiento > '" . $id_asiento . "'";
            $stmtCobros = $bd->ejecutar($sqlCobros);
            $sumaHaber = 0;
            $cantidadPagos = 0;
            while ($cobros = $bd->obtener_fila($stmtCobros)) {
                  $haber = trim($cobros['haber']);
                  $sumaHaber += $haber;
                  $cantidadPagos += 1;
                  // echo '<li class="list-group-item">----------(3) - '.$haber.'</li>';
            }

            
            if (($solicita - $sumaHaber) > 0 ){
                  $sumaSolicitado += $solicita;
                  $sumaPagado += $sumaHaber;
                  $filas .= '<tr>
                  <td>' . $idprov . '</td>
                  <td>' . $funcionario . '</td>
                  <td>' . $garante . '</td>
                  <td align="right">' . knumber_format($solicita, 2) . '</td>
                  <td align="right">' . knumber_format($plazo, 2) . '</td>
                  <td align="right">' . knumber_format($mensual, 2) . '</td>
                  <td align="right">' . knumber_format($cantidadPagos, 2) . '</td>
                  <td align="right">' . knumber_format($sumaHaber, 2) . '</td>
                  <td align="right"><b>' . knumber_format(($solicita - $sumaHaber), 2) . '</b></td>
                  </tr>';
            }



            // if (($solicita-$sumaHaber) > 0 ) { // hay saldo pendiente
            // 	echo '<li class="list-group-item">----------(4) - Saldo =  '.($solicita-$sumaHaber).'</li>';
            // }else{
            // 	echo '<li class="list-group-item">----------(4) - Deuda Saldada '.($solicita-$sumaHaber).'</li>';
            // }

      }
}

$tabla = '<table id="caso_1" class="table table-bordered table-hover table-tabletools" style="font-size:80%" border="0" width="100%">
      <thead>
            <tr>
                  <th style="background-color: #4993eb;color:#ffffff;">Identificacion</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Funcionario</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Garante</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Solicitado</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Plazo</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Cuota</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Cuotas Pagadas</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Pagado</th>
                  <th style="background-color: #4993eb;color:#ffffff;">Saldo</th>
            </tr>
      </thead>
      <tbody>
            #FILAS
            <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td align="right"><b>' . knumber_format($sumaSolicitado, 2) . '</b></td>
                  <td align="right"><b></b></td>
                  <td align="right"><b></b></td>
                  <td align="right"><b></b></td>
                  <td align="right"><b>' . knumber_format($sumaPagado, 2) . '</b></td>
                  <td align="right"><b>' . knumber_format($sumaSolicitado - $sumaPagado, 2) . '</b></td>
            </tr>
      </tbody>
</table>';
$tabla = str_replace("#FILAS", $filas, $tabla);

echo $tabla;


// FIN DE PRUEBA DE BUSQUEDA CORRECTA DE SALDOS

