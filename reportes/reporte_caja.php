<?php
require_once __DIR__ . '/../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../kconfig/auth.php'; // exige sesión

session_start( );   

    require 'inicio.php';   

   $g  = 	new componente;

   $id =    $_GET["id"];

   $codigo =    $_GET["codigo"];

 

	 

$stmt_detalle =  $g->detalle_movimiento( $codigo );



 $conta = 1;

   while ($row=kpg_fetch_assoc($stmt_detalle)){
	 

	       	$file =   trim($row["reporte"]) ;
	 
	    
		   	$file = trim($file).'?id='.$id.'&codigo='. $codigo  ;

		    echo "<script>window.open("."'".$file."'".", '_blank'); </script>";
		   
	 
  	}


 
?>
<script> 

  setTimeout(window.close, 8000);
 
 
</script>	