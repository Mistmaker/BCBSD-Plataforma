<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

// ob_start();
// Include html2pdf
require_once './vendor/autoload.php';

require('kreportes.php');
require('numToLetter.php');
$gestion   		= 	new ReportePdf;
$convertidor = new numberToLetter;
$idasiento 		= $_GET['a'];
$datos 			=	$gestion->CabAsientoOrden($idasiento);
// print_r($datos);
$gestion->QR_DocumentoDoc($idasiento);
$forma_detalle = ' Forma de pago ' . $datos['tipo'] . ' Nro.Documento ' . $datos['cheque'];

if ($datos["tipo"] == "N") { // si es tipo N es orden de pago para roles
	$montoTexto = $convertidor->convertir($datos['suma_total_pagar']);
} else{ // es orden de pago para proveedores
	$montoTexto = $convertidor->convertir(number_format($datos['apagar'],2));
}
$datos["montoTexto"] = $montoTexto;
$datos["factura"] = str_replace(',', ' <br> ',  $datos['factura']) ?: 'S/N';
$datos["retencion"] = $datos['retencion'] ?: 'S/N';

$globalCbsdData = [
	'INSTITUTION_FULL_NAME' => 'BENEMÉRITO CUERPO DE BOMBEROS DE SANTO DOMINGO',
	'sistema_ruc' => '2360003540001',
	'sistema_direccion' => 'Av. Jacinto Cortez y Jorge Icaza',
	'sistema_telefono' => '(02) 3959-340',
	'sistema_correo' => 'info@cbsd.gob.ec',
	'sistema_sitioweb' => 'https://www.bomberossantodomingo.gob.ec/',
	'SYSTEM_FACEBOOK_URI' => '/CuerpoDeBomberosSantoDomingo',
	'SYSTEM_TWITTER_URI' => '@BomberosSD'
];

$estilos = file_get_contents('recursos/style.css');
$HEADER = file_get_contents('formatos/HEADER_PROCESS_CBSD.html');
$FOOTER = file_get_contents('formatos/FOOTER_PAGE_MAIN.html');

$estilos = str_replace("{{margin_left}}", "10", $estilos); //10mm
$estilos = str_replace("{{margin_right}}", "10", $estilos); //10mm


$html = '<style>{{css}}p{font-size:12px;}table tbody th{background:none;color:black}table td,table th{border-color:#222d32;}</style>
<page backtop="{{margin_top}}mm" backbottom="{{margin_bottom}}mm" backleft="{{margin_left}}mm" backright="{{margin_right}}mm">
	<!-- HEADER PROCESS CBSD -->
	{{HEADER_PROCESS_CBSD}}
	<!-- FOOTER DE REPORTE -->
	{{FOOTER_PAGE_MAIN}}
	<!-- CUERPO DE REPORTE -->
	{{ORDEN_PAGO_TEMPLATE}}
</page>';

$html = str_replace("{{css}}", $estilos, $html);
$html = str_replace("{{HEADER_PROCESS_CBSD}}", $HEADER, $html);
$html = str_replace("{{FOOTER_PAGE_MAIN}}", $FOOTER, $html);

$html = str_replace("{{margin_top}}", "35", $html); //10mm
$html = str_replace("{{margin_bottom}}", "20", $html); //10mm
$html = str_replace("{{margin_left}}", "25", $html); //10mm
$html = str_replace("{{margin_right}}", "25", $html); //10mm

$formato = file_get_contents('formatos/ordenPago.html');
$formatoDetProveedores = file_get_contents('formatos/ordenPagoDetProveedores.html');
$formatoDetRoles = file_get_contents('formatos/ordenPagoDetRoles.html');
$html = str_replace("{{ORDEN_PAGO_TEMPLATE}}", $formato, $html);

if ($datos["tipo"] == "N") { // si es tipo N es orden de pago para roles
	$html = str_replace("{{detalle_pagos}}", $formatoDetRoles, $html);
} else{ // es orden de pago para proveedores
	$html = str_replace("{{detalle_pagos}}", $formatoDetProveedores, $html);
}

$infoEntidad = 'DIRECCIÓN FINANCIERA <br> ORDEN DE PAGO <br> <b>{{op}}-{{anio}}</b>';
$html = str_replace("{{report_info_entity}}", $infoEntidad, $html);

$datos = array_merge($datos, $globalCbsdData);
foreach ($datos as $key => $value) {
	$html = str_replace('{{' . $key . '}}', $value, $html);
}


// print_r( $datos);
// print_r( $html);

try {
	ob_start();

	$rutaArchivo = __DIR__ . '/docs/';

	$orientacion = 'P'; // P = portrait    |   L = landscape
	$hoja = 'A4';
	$html2pdf = new Html2Pdf($orientacion, $hoja, 'es', true, 'UTF-8', array(0, 0, 0, 0));
	$html2pdf->pdf->SetDisplayMode('real');
	$html2pdf->pdf->SetCreator('CBSD');
	$html2pdf->pdf->SetAuthor('CBSD');
	$html2pdf->pdf->SetTitle('Orden de Pago N° '.$datos["op"].'-'.$datos["anio"].'');
	$html2pdf->pdf->SetSubject('OrdenPago');
	$html2pdf->pdf->SetKeywords('CBSD, Orden, Pago');
	$html2pdf->writeHTML($html);
	// $html2pdf->output($rutaArchivo.'OrdenPago-'.$datos["op"].'-'.$datos["anio"].'.pdf','F');
	$html2pdf->output($rutaArchivo.'OrdenPago-'.$datos["op"].'-'.$datos["anio"].'.pdf');
} catch (Html2PdfException $e) {
	$html2pdf->clean();

	$formatter = new ExceptionFormatter($e);
	echo $formatter->getHtmlMessage();
}
