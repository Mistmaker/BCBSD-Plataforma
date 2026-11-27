<?php
require_once __DIR__ . '/../kconfig/auth.php'; // exige sesión
/**
 * Acción de personal (Talento Humano) con el formato institucional (kconfig/reporte.php).
 * Antes: TCPDF con un logo de 5571x2076 px (2,1 MB) que hacía lento el reporte.
 */
session_start();
require('kreportes.php');
require_once __DIR__ . '/../kconfig/reporte.php';

$gestion = new ReportePdf;
$id      = trim($_GET['id']);
$datos   = $gestion->Accion($id);
$d       = function ($k) use ($datos) { return isset($datos[$k]) ? trim((string) $datos[$k]) : ''; };

$estado = $d('estado') === 'S' ? '' : ($d('estado') === 'N' ? 'DIGITADO' : 'ANULADO');
$motivo = $d('motivo');

$r = new KReporte(array(
    'area'      => 'DIRECCIÓN DE TALENTO HUMANO',
    'documento' => 'ACCIÓN DE PERSONAL',
    'numero'    => 'Nº ' . $d('comprobante'),
    'archivo'   => 'accion_personal_' . $d('comprobante'),
    'css'       => '.kr-estado { color: #c0392b; font-weight: bold; }',
));

$r->agregar(kr_titulo('Acción de personal Nº ' . $d('comprobante'), 'Rige a partir del ' . kr_fecha_larga($d('fecha_rige'))));
if ($estado !== '') {
    $r->agregar('<div class="kr-centro kr-estado" style="margin:-1mm 0 2mm 0">' . $estado . '</div>');
}
$r->agregar(kr_datos(array(
    'Apellidos'      => $d('apellido'),
    'Nombres'        => $d('nombre'),
    'Identificación' => $d('idprov'),
    'Fecha'          => $d('fecha'),
    'Rige a partir'  => $d('fecha_rige'),
    'Motivo'         => $motivo . ($d('finalizado') === 'S' ? ' (FINALIZADA ' . $d('ffinalizacion') . ')' : ''),
    'Referencia'     => $d('referencia'),
), 2));

$r->agregar(kr_texto($d('novedad'), 'Explicación'));
// la base legal viene como "-ART. 1 ... -ART. 2 ...": cada artículo en su línea
$r->agregar(kr_texto(trim(str_replace('-ART', "\n-ART", $d('baselegal'))), 'Base legal'));

// Situación actual / propuesta (en renuncia o cesación no hay situación propuesta; en remoción, "-")
$sin_propuesta = in_array($motivo, array('RENUNCIA', 'CESACIÓN DEFINITIVA/DESTITUCIÓN'), true);
$remocion      = $motivo === 'REMOCIÓN';
$propuesta = function ($k) use ($d, $sin_propuesta, $remocion) {
    return $sin_propuesta ? '' : ($remocion ? '-' : $d($k));
};
$r->agregar(kr_bloque('Situación actual y propuesta'));
$r->agregar(kr_tabla(
    array('Concepto', 'Situación actual', 'Situación propuesta'),
    array(
        array('Régimen laboral',      $d('regimen'),  $propuesta('p_regimen')),
        array('Programa',             $d('programa'), $propuesta('p_programa')),
        array('Dirección',            $d('unidad'),   $propuesta('p_unidad')),
        array('Puesto',               $d('cargo'),    $propuesta('p_cargo')),
        array('Remuneración mensual', $d('sueldo'),   $propuesta('p_sueldo')),
    ),
    array('', '', ''), null, array('28%', '36%', '36%')
));

// Firmas
$th = $gestion->datos_cargos(17);
if ($d('idprovc') === '-') {
    $autoridad = $gestion->datos_cargos(10);
    $firma_autoridad = array('nombre' => $autoridad['d1'], 'cargo' => $autoridad['d2']);
} else {
    $firma_autoridad = array('nombre' => $d('nombre_firma'), 'cargo' => $d('responsable_firma'));
}
$r->agregar(kr_firmas(array(
    array('titulo' => 'Dirección de Talento Humano', 'nombre' => $th['d1'], 'cargo' => $th['d2']),
    array('titulo' => 'Dios, Patria y Libertad') + $firma_autoridad,
)));
$r->agregar(kr_firmas(array(
    array('titulo' => 'Administración del Talento Humano', 'nombre' => $d('nom_elabora'),
          'cargo' => $d('car_elabora') . ' - Responsable del registro',
          'nota' => 'Acción Nº ' . $d('comprobante') . '  ·  Fecha: ' . $d('fecha')),
    array('titulo' => 'Servidor público', 'nombre' => $d('razon'), 'cargo' => 'C.I. ' . $d('idprov'),
          'nota' => 'Fecha: ......................  Hora: ..............'),
)));

$r->enviar();
