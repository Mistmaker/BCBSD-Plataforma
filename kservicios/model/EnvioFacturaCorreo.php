<?php
session_start();

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
require '../../kconfig/Db.emailMarket.php'; /*Incluimos el fichero de la clase objetos*/


//  error_reporting(E_ALL);
//  ini_set('display_errors', 1);

$obj      =     new objects;
$bd       =    new Db;
$mail     =    new EmailEnvio;

$bd->conectar($_SESSION['us'], '', $_SESSION['ac']);

$id             = trim($_GET['id']);
// $id_rol             = $_GET['id_rol1'];

$sesion              =  trim($_SESSION['email']);
$razon_social       = $_SESSION['razon'];

$response           = 'Error de conexion...';



$mail->_DBconexion($obj, $bd);
$mail->_smtp_factura_electronica();


// $plantilla     =   $bd->query_array(
//     'ven_plantilla',
//     'contenido,   variable',
//     'id_plantilla=' . $bd->sqlvalue_inyeccion(-3, true)
// );
// $datos_web     =   $mail->WebEnvio();
// //  print_r($datos_web);
// $pagina_web    =   trim($datos_web);
$pagina_web    =   trim('https://kaipi.cbsd.gob.ec/');

$datosFactura = $bd->query_array('rentas.view_ren_factura', 'razon, correo,fecha, detalle,autorizacion,secuencial,total',  'id_ren_movimiento=' . $bd->sqlvalue_inyeccion(trim($id), true));
// print_r($datosFactura);

$htmlCorreo = '
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura Electr&oacute;nica</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .email-container {
      background-color: #ffffff;
      padding: 30px;
      max-width: 600px;
      margin: auto;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .logo {
      max-width: 200px;
      margin-bottom: 20px;
    }
    h2 {
      color: #333;
    }
    p {
      color: #555;
      line-height: 1.5;
      text-align: left;
    }
    .btn {
      display: inline-block;
      background-color: #DA291C;
      color: white;
      padding: 10px 20px;
      margin-top: 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
    .footer {
      margin-top: 30px;
      font-size: 12px;
      color: #888;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <img src="https://kaipi.cbsd.gob.ec/kimages/Logocbsd.png" alt="Logotipo de la empresa" class="logo">
    
    <h2>Factura Electr&oacute;nica Disponible</h2>
    
    <p>Estimado(a) <strong>[[NOMBRE_CLIENTE]]</strong>,</p>
    <p>Le informamos que su factura electr&oacute;nica correspondiente a la transacci&oacute;n realizada con nosotros est&aacute; disponible.</p>
    <p><strong>N&uacute;mero de factura:</strong> [[NUMERO_FACTURA]]<br>
    <strong>Fecha de emisi&oacute;n:</strong> [[FECHA_EMISION]]</p>
    <p>Puede descargarla en el siguiente enlace:</p>
    <a target="_blank" href="[[URL_XML]]" class="btn" style="display:inline-block;padding:12px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;text-decoration:none;background:#DA291C;border-radius:8px;color:#ffffff;">Descargar XML Factura</a>
    <a target="_blank" href="[[URL_FACTURA]]" class="btn" style="display:inline-block;padding:12px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;text-decoration:none;background:#DA291C;border-radius:8px;color:#ffffff;">Descargar PDF Factura</a>
    <p>Si tiene alguna duda o necesita m&aacute;s informaci&oacute;n, no dude en contactarnos.</p>
    
    <div class="footer">
      Este es un mensaje autom&aacute;tico, por favor no responda a este correo.<br>
      &copy; [[AÑO]] [[NOMBRE_EMPRESA]] - Todos los derechos reservados.
    </div>
  </div>
</body>
</html>
';

$sec = substr(trim($datosFactura["autorizacion"]) , 24, 6);
$url_xml='https://kaipi.cbsd.gob.ec/facturae/xml/'.trim($datosFactura["autorizacion"]).'_A.xml';
$url_fac='https://kaipi.cbsd.gob.ec/facturae/_factura_electronica.php?id='.$id;

$htmlCorreo = str_replace("[[NOMBRE_CLIENTE]]", trim($datosFactura["razon"]), $htmlCorreo);
$htmlCorreo = str_replace("[[NUMERO_FACTURA]]", $sec.'-'.trim($datosFactura["secuencial"]), $htmlCorreo);
$htmlCorreo = str_replace("[[FECHA_EMISION]]", trim($datosFactura["fecha"]), $htmlCorreo);
$htmlCorreo = str_replace("[[URL_XML]]", trim($url_xml), $htmlCorreo);
$htmlCorreo = str_replace("[[URL_FACTURA]]", trim($url_fac), $htmlCorreo);
$htmlCorreo = str_replace("[[AÑO]]", date('Y'), $htmlCorreo);
$htmlCorreo = str_replace("[[NOMBRE_EMPRESA]]", 'Cuerpo de Bomberos del Gobierno Autónomo Descentralizado Municipal de Santo Domingo', $htmlCorreo);

// echo $htmlCorreo;
// return;

$asunto = 'Factura Electronica Emitida - BCBSD';

if (!empty($htmlCorreo)) {
    // print_r('envio correo');
    // $mail->_DeCRM( $sesion,$datos_empresa['razon']);
    $mail->_ParaCRM(trim($datosFactura["correo"]), $datosFactura['razon']);
    $mail->_AsuntoCRM($asunto, $htmlCorreo);
    $mensaje_enviado = $mail->_EnviarElectronica();
    echo 'Msg: '.$mensaje_enviado;
}

// if (trim($x['correo']) == trim($x['emaile'])) {
//     $mail->_ParaCRM(trim($x['correo']), trim($x['razon']));
// } else {
//     $mail->_ParaCRM(trim($x['correo']), trim($x['razon']));
//     $mail->_ParaCRM(trim($x['emaile']), trim($x['razon']));
// }
// $mail->_AsuntoCRM($asunto, $content);
// $response = $mail->_EnviarElectronica();
// sleep(2);

// echo $response . ' ' . trim($x['razon']);
