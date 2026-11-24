<?php
	session_start( );
    if (empty($_SESSION['usuario']))  {
	    header('Location: ../../kadmin/view/login');
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title>Plataforma de Gestión Empresarial</title>
	
    <?php  require('Head.php')  ?> 
 
 	<script type="text/javascript" src="../js/producto.js"></script> 
 	 		 
</head>
<body>

<!-- ------------------------------------------------------ -->
 
 
<!-- ------------------------------------------------------ -->
 <div>
 	  <div class="col-md-12"> 
  						    
						  		   <div id="ViewForm"> </div>
						   
   	  </div> 
  </body>
</html>
