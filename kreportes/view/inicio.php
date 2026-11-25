<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

	session_start( ); 
?>		
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title>Plataforma de Gestión Empresarial</title>
	
    <?php  require('Head.php')  ?> 
    
   <script type="text/javascript" src="../js/ireport.js"></script>
    
 
     
</head>

<body>

	<div class="col-md-12" role="banner">
		
 	   <div id="NavMod"></div>
		
 	</div> 
	
	
 	
		<div id="mySidenav" class="sidenav">
		<div class="panel panel-primary">
		  <div class="panel-heading"><b>OPCIONES DEL MODULO</b></div>
				<div class="panel-body">
					<div id="ViewModulo"></div>
				</div>
		</div>
   </div>
       <!-- Content Here -->
	 
		 <div class="col-md-2">
											  <div class="widget box">
												<div class="widget-header">
												  <h5><i class="icon-reorder"></i> Grupos</h5>
												</div>
												<div class="widget-content">
													<div class="form-group">
														<div id="show"></div>  
													</div>  
												</div>
											</div>	
		 </div> 
		
		<!-- /.col-md-6 -->
										
		<div class="col-md-3">
 											<div class="widget box">
												<div class="widget-header">
												  <h5><i class="icon-reorder"></i> Bandeja de Información</h5>
												</div>
												<div class="widget-content">
													<div class="form-group">
													  	<div id="ListaVista"></div>  
												  		<br>
												  		<a href="#" onClick="location.reload(true);" class="list-group-item">Nuevo Reporte </a>
												  
													</div>  
												</div>
											</div> <!-- /.widget box -->         
 		 </div> <!-- /.col-md-6 -->
 										
 		 <div class="col-md-7">
 											<div class="widget box">
												<div class="widget-header">
												  <h5><i class="icon-reorder"></i> Parámetros Información</h5>
												</div>
												<div class="widget-content">
													<div class="form-group">
														 
														<div id="capa"></div>  
													</div>  
												</div>
											</div> <!-- /.widget box -->         
 		 </div> <!-- /.col-md-6 -->
 										
		
    </div>
  	<!-- Page Footer-->
      
    </div>   
</body>
</html>
 