<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?> <?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

 
    session_start( );   

	require_once __DIR__ . '/../../kconfig/pdf.php'; // dompdf unico (lib/dompdf)


	  if (isset($_GET["ac12d2"]))	{

		 $codigo 	 = $_GET["ac12d2"];
 	
		 $dompdf = new KDompdf();
 
	
		$dompdf->load_html( file_get_contents( 'http://190.152.220.237/gestiona/kadmin/reportes/reporte_certificacion.php?codigo='.$codigo  ) );

		//  portrait   landscape

		$dompdf->render();

		// $dompdf->setPaper('A4', 'portrait');

		$dompdf->stream("solicitud.pdf", array("Attachment" => false));
	
		//   $dompdf->stream("mi_archivo.pdf");
		  

	   }
 
	
?>