<?php
session_start();
ob_start();
require 'inventarios-factura.php';   /*Incluimos el fichero de la clase Db*/

$g  	 = 	new componente;


$fecha 	 = $_GET["fecha"];
$date = strtotime($fecha);
$formatted = strftime('%A, %d de %B %Y', $date);


// $cajero  =  trim($_SESSION['email']);

$frubro   = trim($_GET["frubro"]);
$cajero = $g->_obtenerCajero($frubro, $fecha);

$g->_get_caja();

$g->_cajero($cajero);

$g->QR_DocumentoDoc($cajero);

$datosEspecie = $g->Especie_nombreCant($frubro, $fecha, $cajero);
$datosPermisos = $g->Especie_nombreCant(14, $fecha, $cajero);
/*
$datos_parte = $g->parte_cierre($parte,$cajero ) ;

$fecha_caja  = $datos_parte["fecha_pago"];
*/

?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<style type="text/css">
		body {
			font-size: 10px;
			color: #000;
			margin: 2mm 10mm 2mm 15mm;
		}

		.tableCabecera {
			margin: 3px 0 3px 0;
			border-collapse: collapse;
			border: .40mm solid thin #909090;
			width: 100%
		}

		.tableForm {
			/*	margin:3px 0 3px 0; */
			border-collapse: collapse;
			border: .40mm solid thin #909090;
			width: 100%
		}

		.tableFirmas {
			margin: 10px 0 10px 0;
			border-collapse: collapse;
			border: .40mm solid thin #909090;
		}

		.titulo {
			padding-left: 10px;
			padding-bottom: 2px;
			font-weight: bold;
			color: #5B5B5B
		}

		.titulo1 {
			padding-left: 10px;
			padding-bottom: 2px;
			color: #5B5B5B
		}

		.MensajeCab {
			padding-left: 10px;
			padding-bottom: 5px;
			font-weight: 100;
			font-size: 11px;
			color: #636363
		}

		.Mensaje {
			font-size: 11px;
			padding-left: 10px;
			padding-right: 5px;
			padding-bottom: 5px;
			color: #000000
		}

		.grillaTexto {
			font-size: 11px;
			padding-left: 10px;
			padding-right: 5px;
			padding-bottom: 5px;
		}

		.Mensaje1 {
			font-size: 12px;
			padding-left: 5px;
			padding-right: 15px;
			padding-bottom: 5px;
			color: #000000
		}

		.linea {
			border: .40mm solid thin #909090;
			padding: 20px;
		}

		.linea1 {
			border: .40mm solid thin #909090;
			padding: 5px;
		}

		table {
			border-collapse: collapse;
		}

		table,
		th,
		td {
			border: .40mm solid thin #909090;
		}

		.cabecera_font {
			font-size: 10px;
			font-family: "Gill Sans", "Gill Sans MT", "Myriad Pro", "DejaVu Sans Condensed", Helvetica, Arial, "sans-serif";
			color:rgb(0, 0, 0);
			border-collapse: collapse;
			width: 100%;
		}
	</style>

</head>

<body>

	<table class="cabecera_font" style="border: 0;">

		<tbody>
			<tr>
				<td width="15%" style="border: 0;" align="center"><img src="../kimages/<?php echo trim($_SESSION['logo']) ?>" width="90" height="90"></td>
				<td width="85%" style="padding-left: 10px;border: 0;" valign="top"> <b><?php echo $g->Empresa(); ?></b><br><br><?php echo 'RUC: ' . $g->_Cab('ruc_registro'); ?><br>
					<?php echo 'Dir.: ' . $g->_Cab('direccion'); ?><br><?php echo 'Telf.: ' . $g->_Cab('telefono'); ?><br>Modulo: RECAUDACIÓN<br>
					<b>CONTROL DE ESPECIES VALORADAS</b>
				</td>
			</tr>
		</tbody>
	</table>

	<br>

	<h3 style="font-size: 14px"> DETALLE DIARIO CONTROL DE ESPECIES VALORADAS - CIERRE DE CAJA</h3>

	<h4 style="font-size: 14px">

		CAJA DEL DIA: <?php echo $fecha; ?><br>
		USUARIO: <?php echo $g->_get_cajero('completo'); ?><br><br>

		<!-- <?php echo $g->Especie_nombre($frubro, $fecha, $cajero); ?> -->

	</h4>


	<p align="justify" style="font-size: 14px">Por medio del presente dejo constancia de la entrega de los valores recaudado el día de hoy <b> <?php echo $formatted; ?> </b>, mediante los siguientes documentos:</p>

	<table width="100%" style="font-size: 14px">
		<tbody>
			<tr>
				<td align="center" colspan="2" valign="middle">
					<h3>INGRESOS POR ESPECIES VALORADAS</h3>
				</td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Desde:</td>
				<td align="center" valign="middle"><?php echo $datosEspecie['inicio']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Hasta:</td>
				<td align="center" valign="middle"><?php echo $datosEspecie['fin']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Cantidad:</td>
				<td align="center" valign="middle"><?php echo $datosEspecie['cantidad']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Valor Unitario:</td>
				<td align="center" valign="middle">$ 3.00</td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Valor Total Especies:</td>
				<td align="center" valign="middle"><?php echo '$ ' . number_format(($datosEspecie['cantidad'] * 3), 2, ',', ' '); ?></td>
			</tr>
		</tbody>
	</table>

	<br>

	<table width="100%" style="font-size: 14px">
		<tbody>
			<tr>
				<td align="center" colspan="2" valign="middle">
					<h3>INGRESOS POR ESPECIES NO VALORADAS</h3>
				</td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Desde:</td>
				<td align="center" valign="middle"><?php echo $datosPermisos['inicio']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Hasta:</td>
				<td align="center" valign="middle"><?php echo $datosPermisos['fin']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Cantidad:</td>
				<td align="center" valign="middle"><?php echo $datosPermisos['cantidad']; ?></td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Valor Unitario:</td>
				<td align="center" valign="middle">$ 0.00</td>
			</tr>
			<tr>
				<td align="left" style="width: 30%;" valign="middle">Valor Total Especies:</td>
				<td align="center" valign="middle">$ 0.00</td>
			</tr>
		</tbody>
	</table>


	<!-- <h4> DETALLE DE COMPROBANTES EMTIDOS </h4>
	<table width="100%" style="font-size: 10px">
		<tbody>
			<tr>
				<td align="center" valign="middle">Fecha</td>
				<td align="center" valign="middle">Identificacion</td>
				<td align="center" valign="middle">Nombres</td>
				<td align="center" valign="middle">Nro.Especie</td>
				<td align="center" valign="middle">Pago</td>
				<td align="center" valign="middle">Cantidad</td>
				<td align="center" valign="middle">Total</td>
			</tr>
			<?php $g->resumenClientes_especie($fecha, $cajero, $frubro);  ?>
		</tbody>
	</table> -->


	<p align="justify" style="font-size: 14px">CERTIFICO: Que el efectivo y justificantes de caja arriba indicados, fueron verificados en mi presencia y devueltos a mi poder. No existiendo otros fondos,
		valores o documentos en mi poder que no hayan sido mostrados en el reporte diario. </p>




	<table class="tableFirmas" width="100%">
		<tr>
			<td class="linea">&nbsp; <br> <br> <br> </td>
			<td class="linea">&nbsp; </td>
		</tr>
		<tr>
			<td class="linea1" align="center" valign="middle"> </td>
			<td class="linea1" align="center" valign="middle"><?php echo $g->_get_cajero('completo'); ?></td>
		</tr>
		<tr>
			<td width="50%" class="linea1" align="center" valign="middle">Supervisor de Recaudación</td>
			<td width="50%" class="linea1" align="center" valign="middle">Recaudador</td>
		</tr>

	</table>

	<p>&nbsp; </p>
	<img width="80" height="80" src='logo_qr.png' /><br>
	<span style="font-size: 8px;color:#8F8F8F;font-style: italic"> <?php $g->QR_Firma(); ?></span>

</body>

</html>
<?php



require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new DOMPDF();


$dompdf->setPaper('A4', 'portrait'); // (Opcional) Configurar papel y orientaciónl landscape

$dompdf->load_html((ob_get_clean()));

$dompdf->render(); // Generar el PDF desde contenido HTML

$pdf = $dompdf->output(); // Obtener el PDF generado


$filename = "Cierre_Caja" . '.pdf';

file_put_contents($filename, $pdf);

$dompdf->stream($filename, array("Attachment" => false));




?>