<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
session_start( );  
ob_start(); 
require '../../kconfig/Db.class.php';
require '../../kconfig/Obj.conf.php';
require '../../kconfig/convertir.php'; /*Incluimos el fichero de la clase objetos*/	
$obj     = 	new objects;
$bd     = 	new Db;

$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$id		= $_GET['a'];

 
$sql = 'SELECT a.fecha,a.idprov,a.haber,  b.razon
		FROM co_asiento_aux a, par_ciu b 
		where a.haber > 0 and a.idprov = b.idprov and a.id_asiento='.$bd->sqlvalue_inyeccion($id,true);
	
     $resultado = $bd->ejecutar($sql);

  	 $datos = $bd->obtener_array( $resultado);
	
	$pago = $datos['haber'];
	
	if ($datos['haber'] == 0){
		$pago = 0;
	 }

    $monto_imprime = 0;
	$monto_imprime =  knumber_format($pago,2) ;
    $imprime = (convertir($pago)). ' dolares -----------------' ;	
?> 
<!DOCTYPE html>
<html>
<head lang="en">
<meta charset="UTF-8">
</head>		
<body leftmargin="90">
   
   <table width="420px" border="0" cellpadding="0" cellspacing="0" style="font-size: 11px;font-weight:400 ;font-family: Verdana, 'sans-serif'">
  <tbody>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="300"><img src="li.jpg" width="70" height="15" alt=""/><?php echo trim($datos['razon']); ?></td>
      <td width="120">&nbsp;&nbsp;<?php echo $monto_imprime ?></td>
    </tr>
    <tr>
      <td colspan="2"><img src="li.jpg" width="5" height="12" alt=""/></td>
    </tr>
    <tr>
      <td colspan="2"><img src="b.jpg" width="1" height="14" alt=""/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo trim($imprime); ?></td>
    </tr>
    <tr>
      <td colspan="2"><img src="li.jpg" width="5" height="26" alt=""/></td>
    </tr>
    <tr>
      <td><?php echo trim($_SESSION['ciudad']); ?>, <?php echo trim($datos['fecha']); ?></td>
      <td>&nbsp;</td>
    </tr>
  </tbody>
</table>
</body>
</html>
<?php 
require_once __DIR__ . '/../../kconfig/pdf.php'; // dompdf unico (lib/dompdf)

use Dompdf\Dompdf;


$dompdf = new KDompdf();


$dompdf->setPaper('A5', 'portrait'); // (Opcional) Configurar papel y orientaciónl landscape

$dompdf->load_html(utf8_decode(ob_get_clean()));

$dompdf->render(); // Generar el PDF desde contenido HTML


//$dompdf->stream(); // Enviar el PDF generado al navegador
 
//$filename = "Anexo".time().'.pdf';
$filename = "ComprobanteAsiento".'.pdf';

 
$dompdf->stream($filename, array("Attachment" => false));
 
 
?>
