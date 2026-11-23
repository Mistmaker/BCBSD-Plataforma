<?php
ob_start();
require('kreportes.php');
require('numToLetter.php');
$gestion   		= 	new ReportePdf;
$convertidor = new numberToLetter;
$idasiento 		= $_GET['a'];
$datos 			=	$gestion->CabAsientoOrden($idasiento);
// print_r($datos);
$gestion->QR_DocumentoDoc($idasiento);

$forma_detalle = ' Forma de pago ' . $datos['tipo'] . ' Nro.Documento ' . $datos['cheque'];
$montoTexto = $convertidor->convertir($datos['apagar']);
/*
$datos['cheque'] = $datosB['cheque'];  
		$datos['tipo']   = $datosB['tipo'];  
		$datos['pagado'] = $datosB['debe'] +  $datosB['haber'];  
		 
		$datos['tipoc']   = $tipo_mov;  
*/

?>
<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<link rel="stylesheet" href="impresion.css" />
	<style>
		@page {
			margin: 180px 50px;
		}

		body {
			font-size: 10px;
			color: #000;
			margin: 2mm 10mm 2mm 15mm;
		}

		#header {
			position: fixed;
			left: 0px;
			top: -110px;
			right: 0px;
			height: 110px;
			background-color: #FFFFFF;
			text-align: center;
		}

		#footer {
			position: fixed;
			left: 0px;
			bottom: -125px;
			right: 0px;
			height: 180px;
			background-color: #FFFFFF;
		}

		#footer .page:after {
			/*	content: counter(page, upper-roman); */
			counter-increment: section;
			content: "Pag " counter(section) " ";

		}

		.round3 {
			border: 1px solid #767676;
			border-radius: 5px;
		}

		.tabla {
			border: #767676 1px solid;
			margin: 1px;
			padding: 1px;
		}

		.tablaTotal {
			margin: 3px;
			padding: 3px;
		}

		.tablaPie {
			border: #767676 1px solid;
			margin: 25px;
			padding: 25px;
		}

		.tablaPie1 {
			border: #767676 1px solid;
			margin: 3px;
			padding: 3px;
		}

		.neg {
			font-weight: bold;
		}
	</style>

</head>

<body>

	<div class="col-md-12" style="padding: 7px">
		<table width="95%">
			<tr>
				<td width="10%" align="left" valign="top" style="font-size: 11px">
					<img src="../../kimages/<?php echo trim($_SESSION['logo']) ?>" width="122">
				</td>
				<td width="80%" align="left" style="font-size: 11px">
					<?php
					echo $gestion->EmpresaCab() . '<br>';
					echo $gestion->_Cab('ruc_registro') . '<br>';
					echo $gestion->_Cab('direccion') . '<br>';
					echo $gestion->_Cab('telefono') . '<br>';
					echo $gestion->_Cab('ciudad') . '- Ecuador' . '<br>';
					?>
				</td>
			</tr>
		</table>
	</div>

	<table width="95%">

		<tr bgcolor=#ECECEC>
			<td colspan="4" align="center" style="font-size: 14px"><b>DIRECCIÓN FINANCIERA</b> </td>
		</tr>
		<tr bgcolor=#ECECEC>
			<td colspan="4" align="center" style="font-size: 14px"><b>ORDEN DE PAGO</b> </td>
		</tr>
		<tr bgcolor=#ECECEC>
			<td colspan="4" align="center" style="font-size: 14px"><?php echo $datos['op'] . '-' . $datos['anio'] ?></td>
		</tr>
		<tr>
			<td rowspan="3" style="font-size: 12px">Para :</td>
			<td colspan="3" style="font-size: 10px">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="font-size: 12px"><?php echo $datos['t10'] ?><br><?php echo $datos['t11'] ?></td>
		</tr>

		<tr>
			<td colspan="4" style="font-size: 10px">&nbsp;</td>
		</tr>
	</table>

	<table width="95%">

		<tr bgcolor=#ECECEC>
			<td class="solid neg" colspan="4" style="font-size: 10px">INFORMACIÓN DEL DEVENGADO</td>
		</tr>
		<tr>
			<td class="solid neg" colspan="4" style="font-size: 10px">Comprobante Nro <?php echo $datos['comprobante'] ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px" width="20%">Fecha orden Pago</td>
			<td class="solid" colspan="3" style="font-size: 10px" width="30%"><?php echo $datos['forden'] ?></td>
		</tr>
		<tr>
			<td class="solid neg" style="font-size: 10px" width="20%">Nro. Asiento</td>
			<td class="solid neg" style="font-size: 10px" width="30%"><?php echo $datos['id_asiento'] ?></td>
			<td class="solid" style="font-size: 10px" width="20%">Fecha Asiento</td>
			<td class="solid" style="font-size: 10px" width="30%"><?php echo $datos['fecha'] ?></td>
		</tr>
		<tr>
			<td class="solid neg" style="font-size: 10px">Nro. Trámite</td>
			<td class="solid neg" style="font-size: 10px"><?php echo $datos['id_tramite'] ?></td>
			<td class="solid" style="font-size: 10px">Nro. Documento</td>
			<td class="solid" style="font-size: 10px"><?php echo $datos['documento'] ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Solicita</td>
			<td class="solid" style="font-size: 10px"><?php echo $datos['solicitante_razon'] . ', ' . $datos['solicitante_cargo'] ?></td>
			<td class="solid" style="font-size: 10px">Identificacion Solicita</td>
			<td class="solid" style="font-size: 10px"><?php echo $datos['solicitante_idprov'] ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Elaborador por</td>
			<td class="solid" style="font-size: 10px"><?php echo $datos['sesion'] ?></td>
			<td class="solid" bgcolor=#ECECEC style="font-size: 10px">Estado</td>
			<td class="solid neg" bgcolor=#ECECEC style="font-size: 10px"><?php echo $datos['estado'] ?></td>
		</tr>
		<tr>
			<td class="solid neg" colspan="4" style="font-size: 10px">Detalle</td>
		</tr>
		<tr>
			<td class="solid" colspan="4" style="font-size: 10px"><?php echo trim($datos['detalle'])   ?></td>
		</tr>

	</table>

	<p></p>

	<table>
		<tr bgcolor=#ECECEC>
			<td colspan="2" align="left" class="solid neg" style="font-size: 10px">Solicito realizar el pago bajo los datos estipulados a continuacion</td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px" width="20%">Fecha</td>
			<td class="solid" style="font-size: 10px" width="80%"><?php echo $datos['fecha'] ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Beneficiario</td>
			<td class="solid" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['proveedor'] ?></span></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Nro.Identificacion</td>
			<td class="solid" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['idprov'] ?></span></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Banco Beneficiario</td>
			<td class="solid" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['banco'] ?></span></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Tipo Cuenta</td>
			<td class="solid" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['tipo_cuenta'] ?></span></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px">Nro.Cuenta</td>
			<td class="solid" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['cuenta'] ?></span></td>
		</tr>
	</table>

	<p></p>

	<table>
		<tr bgcolor=#ECECEC>
			<td colspan="2" class="solid neg" align="left" style="font-size: 10px">Solicito realizar el pago con el monto a pagar de acuerdo a: (Deducciones de impuestos/anticipos)</td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px" width="20%">Documento</td>
			<td class="solid" style="font-size: 10px" width="80%"><?php echo $datos['documento'] ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px" width="20%"><span class="solid" style="font-size: 10px">Nro.Factura(s)</span></td>
			<td class="solid" style="font-size: 10px" width="80%"><?php echo str_replace(',', ' <br> ',  $datos['factura']) ?></td>
		</tr>
		<tr>
			<td class="solid" style="font-size: 10px" width="20%">Nro.Comprobante Retención</td>
			<td class="solid" style="font-size: 10px" width="80%"><?php echo $datos['retencion'] ?: 'S/N' ?></td>
		</tr>
		<tr>
			<td class="solid neg" style="font-size: 10px"><span class="solid" style="font-size: 10px">Monto a pagar</span></td>
			<td class="solid neg" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $datos['apagar'] ?></span></td>
		</tr>
		<tr>
			<td class="solid neg" style="font-size: 10px"><span class="solid" style="font-size: 10px">A la suma de</span></td>
			<td class="solid neg" style="font-size: 10px"><span class="solid" style="font-size: 10px"><?php echo $montoTexto ?></span></td>
		</tr>
	</table>

	<br>

	<table style="border: 0;">
		<tr style="border: 0;" bgcolor=#ECECEC>
			<td align="left" style="font-size: 10px">Elaborado por :</td>
		</tr>
		<tr>
			<td style="font-size: 10px">
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="border: 0;" align="left">
			<span style="font-size: 12px;">
				<?php echo $datos["f10"]; ?>

			</span> <br>
			<span style="font-size: 12px; font-weight: bold;">
				<?php echo $datos["f11"]; ?>

			</span>

			</td>
		</tr>

	</table>
	<p></p>
	<p>&nbsp;</p>
	<img width="80" height="80" src='logo_qr.png' /><br>
	<span style="font-size: 8px;color:#8F8F8F;font-style: italic"> <?php $gestion->QR_Firma(); ?></span>


</body>

</html>
<?php



require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;


$dompdf = new DOMPDF();


$dompdf->setPaper('A4', 'portrait'); // (Opcional) Configurar papel y orientaciónl landscape

// $dompdf->load_html(utf8_decode(ob_get_clean()));
$dompdf->load_html((ob_get_clean()));

$dompdf->render(); // Generar el PDF desde contenido HTML

$pdf = $dompdf->output(); // Obtener el PDF generado

//$dompdf->stream(); // Enviar el PDF generado al navegador



//$filename = "Anexo".time().'.pdf';
$filename = "ComprobanteAsiento" . '.pdf';

file_put_contents($filename, $pdf);

$dompdf->stream($filename, array("Attachment" => false));


?>