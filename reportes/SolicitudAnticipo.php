<?php
require_once __DIR__ . '/../kconfig/auth.php'; // exige sesión
/**
 * Solicitud de anticipo de remuneración con el formato institucional (kconfig/reporte.php).
 * El texto sale de las plantillas editables ven_plantilla 1 (solicitud) y 2 (garante y
 * autorización), con sus datos ya reemplazados en ReportePdf::solicitud_anticipo().
 */
session_start();
require('kreportes.php');
require_once __DIR__ . '/../kconfig/reporte.php';

$gestion = new ReportePdf;
$id      = $_GET['id'];
$datos   = $gestion->solicitud_anticipo($id);
$doc     = trim(isset($datos['documento']) ? $datos['documento'] : '');

$r = new KReporte(array(
    'area'      => 'DIRECCIÓN DE TALENTO HUMANO',
    'documento' => 'SOLICITUD DE ANTICIPO DE REMUNERACIÓN',
    'numero'    => $doc !== '' ? 'Nº ' . $doc : '',
    'archivo'   => 'solicitud_anticipo_' . ($doc !== '' ? $doc : $id),
    // el contenido viene del editor de plantillas: tamaño legible y párrafos justificados
    'css'       => '.kr-plantilla { font-size: 10pt; line-height: 1.45; text-align: justify; }
                    .kr-plantilla p { margin: 0 0 2.5mm 0; }
                    .kr-plantilla table { border-collapse: collapse; }
                    .kr-fecha { text-align: right; font-size: 9pt; margin: 0 0 3mm 0; }
                    .kr-bloque + .kr-plantilla { margin-top: 2mm; }',
));

$r->agregar(kr_titulo('Solicitud de anticipo de remuneración'));
$r->agregar('<div class="kr-fecha">Fecha de solicitud: ' . kr_e(isset($datos['fecha_completa']) ? $datos['fecha_completa'] : '') . '</div>');
$r->agregar('<div class="kr-plantilla">' . (isset($datos['contenido']) ? $datos['contenido'] : '') . '</div>');

if (!empty($datos['contenido2'])) {
    $r->agregar(kr_bloque('Garante y autorización'));
    $r->agregar('<div class="kr-plantilla">' . $datos['contenido2'] . '</div>');
}

$r->enviar();
