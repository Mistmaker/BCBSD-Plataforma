<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
ob_start(); 
require('resumen_ivaPdf_Cliente.php');
$gestion   = 	new ReportePdf; //			 
$mes 		= $_GET['mes'];
$anio 		= $_GET['anio'];
$Periodo 	= 'PERIODO '.$mes .' - '. $anio ;
?>
<!DOCTYPE html>
<html>
<head lang="en">
<meta charset="UTF-8">
	<style>
           body{
 				  font-family: verdana, sans-serif;
					color: #000;
					background: #fff;
 				}
				.nombre{
					border-bottom: 1px solid cornsilk;
					font-size: 24px;
					font-family: Courier, "Courier new", monospace;
					font-style: italic;
				}
		    .encabezado{
				border="0"
             }
			 table {
				border-collapse: collapse;
				width: 100%
			  }
			  td {
				border: 1px solid #ddd;
				padding: 2px;
			  }
		.datos{
			font-size: 8px;
			font-family: verdana, sans-serif;
			font-weight:normal; 
			 
		}
		.titulo{
			font-size: 9px;
			padding: 2px;
		}
  			</style>
</head>		
<body>
 <div style="padding: 15px">
  <table border="0" cellpadding="0" cellspacing="0">
			    <tr> <td style="padding-bottom: 0px;padding-top: 0px; font-size: 10px"><?php echo $gestion->Empresa(); ?></td> </tr>
				<tr> <td style="padding-bottom: 0px;padding-top: 0px; font-size: 10px">GESTION TRIBUTARIA</td> </tr>
				<tr> <td style="padding-bottom: 1px;padding-top: 1px;font-size: 9px">DETALLE VENTAS</td></tr>
				<tr> <td style="padding-bottom: 1px;padding-top: 1px;font-size: 9px"><?php echo $Periodo; ?></td> </tr>
			
 </table>
 <div style="padding-bottom: 5px;padding-top: 5px"></div>
 <?php   
	 
	 $transaccion = $gestion->SqlGridProve($anio,$mes);
	 
	// $transaccion = $gestion->SqlGrid($anio,$mes);
	 
	// $transaccion = $gestion->SqlGridRenta($anio,$mes);
	 
	 
	 
	  $transaccion = $gestion->SqlGridMes($anio,$mes);
	// $gestion->GrillaResumenRentas( $transaccion,$anio,$mes); 
 ?>
</div>
</body>
</html>
<?php
 
// require_once __DIR__ . '/../../kconfig/pdf.php'; // dompdf unico (lib/dompdf)

// use Dompdf\Dompdf;


// $dompdf = new KDompdf();


// $dompdf->setPaper('A4', 'portrait'); // (Opcional) Configurar papel y orientaciónl landscape

// $dompdf->load_html(utf8_decode(ob_get_clean()));

// $dompdf->render(); // Generar el PDF desde contenido HTML

// $pdf = $dompdf->output(); // Obtener el PDF generado

// //$dompdf->stream(); // Enviar el PDF generado al navegador


 
// //$filename = "Anexo".time().'.pdf';
// $filename = "DevolucionIVA".'.pdf';

// file_put_contents($filename, $pdf);
 
// $dompdf->stream($filename, array("Attachment" => false));

 
?>
