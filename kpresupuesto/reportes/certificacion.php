<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
/**
 * Certificación presupuestaria con el formato institucional (kconfig/reporte.php).
 * Las firmas salen de la plantilla editable par_reporte 'PR-CE' (firma_reportes).
 */
session_start();
require('kreportes.php');
require_once __DIR__ . '/../../kconfig/reporte.php';

$gestion = new ReportePdf;
$id      = trim($_GET['codigo']);
$datos   = $gestion->Memorando($id);
$gestion->QR_DocumentoDoc($id);
$d = function ($k) use ($datos) { return isset($datos[$k]) ? trim((string) $datos[$k]) : ''; };

$r = new KReporte(array(
    'area'      => 'DIRECCIÓN FINANCIERA - PRESUPUESTO',
    'documento' => 'CERTIFICACIÓN PRESUPUESTARIA',
    'numero'    => 'Nº ' . $d('comprobante'),
    'archivo'   => 'certificacion_' . $d('comprobante'),
    'css'       => '.kr-firmas-plantilla { margin-top: 12mm; page-break-inside: avoid; font-size: 9pt; }
                    .kr-firmas-plantilla table { width: 100%; }
                    .kr-qr { margin-top: 6mm; font-size: 7pt; color: #707070; }
                    .kr-qr img { width: 22mm; height: 22mm; }',
));

$r->agregar(kr_titulo('Certificación presupuestaria Nº ' . $d('comprobante'), 'Trámite Nº ' . $id));
$r->agregar(kr_datos(array(
    'Fecha'     => $d('fcertifica'),
    'Memorando' => $d('documento'),
    'Solicita'  => $d('user_sol'),
    'Unidad'    => $d('unidad'),
    'Asunto'    => $d('detalle'),
), 1));

$r->agregar(kr_texto('De conformidad con lo expresado en el Art. 115 del Código Orgánico de Planificación y Finanzas Públicas '
    . 'y una vez revisado el presupuesto aprobado para el ejercicio fiscal, certifico que existe disponibilidad '
    . 'presupuestaria en la(s) partida(s) presupuestaria(s) detallada(s) a continuación, previo a iniciar el proceso '
    . 'de contratación correspondiente:'));

// Partidas certificadas (misma consulta que GrillaCertificacion)
$bd = new Db;
$bd->conectar('', '', '');
$res = $bd->consulta('SELECT partida, dprograma, detalle, dfuente, sum(certificado) AS certificado
                        FROM presupuesto.view_certificacionesd
                       WHERE id_tramite = $1
                       GROUP BY partida, actividad, fuente, clasificador, detalle, dactividad, dfuente, programa, dprograma
                       ORDER BY partida', array($id));
$filas = array();
$total = 0;
while ($res && ($y = kpg_fetch_assoc($res))) {
    $total += (float) $y['certificado'];
    $filas[] = array(trim($y['dprograma']), trim($y['partida']), trim($y['detalle']), trim($y['dfuente']), kr_dinero($y['certificado'], ''));
}
$r->agregar(kr_tabla(
    array('Actividad', 'Partida', 'Detalle', 'Fuente', 'Monto'),
    $filas,
    array('', '', '', '', 'derecha'),
    array(array('TOTAL CERTIFICADO', 4), kr_dinero($total)),
    array('24%', '17%', '35%', '12%', '12%')
));
$r->agregar(kr_texto('El monto de la presente certificación presupuestaria asciende a ' . kr_dinero($total) . ' dólares de los Estados Unidos de América.'));

// Firmas: plantilla editable PR-CE
ob_start();
$gestion->firma_reportes('PR-CE');
$r->agregar('<div class="kr-firmas-plantilla">' . ob_get_clean() . '</div>');

// QR del documento
ob_start();
$gestion->QR_Firma();
$r->agregar('<div class="kr-qr"><img src="logo_qr.png"><br>' . kr_e(ob_get_clean()) . '</div>');

$r->enviar();
