<?php
	session_start( );
	
	if (empty($_SESSION['usuario']))  {
	
	    header('Location: login');
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
    

	
    <script type="text/javascript" src="../js/mipersonal.js"></script>
    

 
</head>
<body>

<div id="main">
	
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
	 
	
    <div class="col-md-12"> 
       <!-- Content Here -->
	   <div class="row">
 		 	 
				 <div class="col-md-6">
						 <div class="panel panel-success">
							 <div class="panel-heading">Información Personales</div>
							<div class="panel-body"  >
									  
									 <div class="col-md-12" style="padding-top: 10px;padding-bottom: 10px">
										 
 										  <div id="viewform"></div>
  
 									</div><!--/.row-->
								
								   <button type="button" onClick="ActualizarDatos()" class="btn btn-success">Actualizar Información</button>
								
								   <div style="padding: 15px" align="center" id="resultado"></div>
 							</div>
						</div>
				 </div>
				 <div class="col-md-6">
					 
						 <div class="panel panel-warning">
							 <div class="panel-heading">Informacion Complementaria</div>
							 
							  <div class="panel-body">
   									 
									  <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Declaración Juramentada</button>
									  <div id="demo" class="collapse">
										 .
									  </div>
  								  
								  
								    <button type="button" class="btn btn-warning" data-toggle="collapse" data-target="#demo1">Cargas Familiares</button>
									  <div id="demo1" class="collapse">
									 .
									  </div>
								  
								  
								   <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#demo2">Cursos Realizados</button>
									  <div id="demo2" class="collapse">
									 .
									  </div>
								  
								    <button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#demo3">Experiencia Laboral</button>
									  <div id="demo3" class="collapse">
									 .
									  </div>
								  
								  
							  </div>
						  </div>
				 </div> 	
		</div>
    </div>
 
	       
 
	
  <div id="FormPie"></div>    
    </div>   
</body>
</html>