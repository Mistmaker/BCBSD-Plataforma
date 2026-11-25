<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

 
  session_start( );  
	
  require '../controller/vistaColumna.php';
 	
  $gestion   = 	new proceso;

?>

<script>
	<?php
 
		$parameter = $_GET['parameter'];
	
 		echo "$('#VistaNombre').val('".$parameter."')";
 
	?>
	
	var parameter = 	$('#VistaNombre').val();
	
	var VistaNombre =  parameter;
	
	
	$('#VistaNombre').val(parameter);
	 
 
 
     var parametros = {
				"VistaNombre" : VistaNombre 
		};
	 
		$.ajax({
			
				data:  parametros,
				url:   'vistaFiltro.php',
				type:  'GET' ,
				 beforeSend: function () { 
						$("#VerFiltro").html('Creando variables filtro');
				},
				success: function(data) {
						 $("#VerFiltro").html(data); 
             },
			  error: function() {
				 alert( "Ha ocurrido un error" );
				 
			  }
		}); 
</script>

 

   <script src="js/jquery-1.12.4.min.js"></script>
  
   <script src="js/jquery-editable-select.min.js"></script>
  
  <link href="js/jquery-editable-select.min.css" rel="stylesheet">

  <link rel="stylesheet" type="text/css" href="../css/multi-select.css">

 <script type="text/javascript" src="../js/vista.js"></script>    
  
  <style>
 	
	select{width:180px;margin:0 0 50px 0;border:1px solid #ccc;padding:10px;border-radius:1px 0 0 10px;}
	
	.clear{clear:both;text-align:center}
	 
	input {margin:3px 1px 0 1px;border:1px solid #ccc;padding:3px;}
	
	  .input-xs_dato {
  height: 28px;
  padding: 2px 5px;
 margin: 0px 0px 2px;		 
 width: 100%;		  
  font-size: 12px;
  line-height: 1.5; /* If Placeholder of the input is moved up, rem/modify this. */ 
  border-radius: 3px;
 }
	  
 .list-group-item1 { padding: 2px}
		  
 
 .list-group-item1.active  {
  color: #428bca;
  background-color: #ffffff;
}	  
		  
  .dual-list .list-group {
            margin-top: 2px;
        }

   .list-left li, .list-right li {
            cursor: pointer;
        }

   .list-arrows {
            padding-top: 100px;
        }

   .list-arrows button {
                margin-bottom: 10px;
      }

	  
	   .form-control_filtro {  
		  display: block;
 		  height: 28px;
		  width: 100%;
		  padding: 3px 3px;
		  font-size: 12px;
		  line-height: 1.428571429;
		  color: #555555;
		  vertical-align:baseline;
		  background-color: #ffffff;
		  background-image: none;
		  border: 1px solid #cccccc;
		  border-radius: 4px;
		  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
				  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
		  -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
				  transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
   }
	  
 </style>               


      <div class="panel panel-primary">

		  <div class="panel-heading">
          
			  <h4 class="panel-title" >
            <?php 
			
			    $VerFiltro    = base64_decode($_GET['parameter']);

			    $VistaTitulo =   $gestion->TablaEtiqueta($VerFiltro);
		
				echo   $VistaTitulo ; 
			
			?></h4>
        </div>
		  
        <div class="panel-body"> 
			
			 
 
					 <div class="panel-group" id="accordion">

						 <!--
						 
						  <div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseCero"><h6>Reportes Generados</h6></a>
									</h4>
								</div>
								<div id="collapseCero" class="panel-collapse collapse">
									<div class="panel-body">
									 <?php 

											 

											//  $gestion->DatosReportes();

											 

										?>
									</div>
								</div>
							</div>
					-->
						 

							 <div class="panel panel-default">

								 <div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><h6>Selección de Columnas</h6></a>
									</h4>
								</div>

								 <div id="collapseOne" class="panel-collapse collapse">   
									 <div class="panel-body">

														<div class="dual-list list-left col-md-5" style="padding: 1px;font-size: 13px;font-weight: 500">
															<div class="well text-left"  style="padding: 1px">
																<div class="row">
																	<div class="col-md-2">
																		<div class="btn-group" style="padding: 1px">
																			<a class="btn btn-default selector" title="select all"><i class="glyphicon glyphicon-unchecked"></i></a>
																		</div>
																	</div>
																</div>
																<ul class="list-group">
																  <?php 	
																//------ poner informacion en los campos del sistema
																if (isset($_GET['parameter']))	{
																	  $VerFiltro    = base64_decode($_GET['parameter']);
																	  $gestion->BusquedaObj($VerFiltro) ;
																}

																 ?>
																</ul>
															</div>
														</div>
														<div class="list-arrows col-md-1 text-center" style="padding: 1px">
															<button class="btn btn-default btn-sm move-left">
																<span class="glyphicon glyphicon-chevron-left"></span>
															</button>

															<button class="btn btn-default btn-sm move-right">
																<span class="glyphicon glyphicon-chevron-right"></span>
															</button>
														</div>
														<div class="dual-list list-right col-md-5"  style="padding: 1px;font-size: 13px;font-weight: 500">
															<div class="well" id="recorre"  style="padding: 1px;" >
																<div class="row">
																	<div class="col-md-2" >
																		<div class="btn-group" style="padding: 1px">
																			<a class="btn btn-default selector" title="select all"><i class="glyphicon glyphicon-unchecked"></i></a>
																		</div>
																	</div>
																</div>
																<ul class="list-group">

																</ul>
															</div>
														</div>                      
							 </div>
						</div>
						 </div>

							 <div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><h6>Filtro de datos</h6></a>
									</h4>
								</div>
								<div id="collapseTwo" class="panel-collapse collapse">
									<div class="panel-body">
										<div id='VerFiltro' style="padding: 1px;font-size: 13px;font-weight: 500"></div>
									</div>
								</div>
							</div>

							 <div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"><h6>Generar Reporte</h6></a>
							</h4>
						</div>
						<div id="collapseThree" class="panel-collapse collapse">

								  <div class="panel-body">

										 <form action="visor.php" method="post" enctype="multipart/form-data" target="_blank" accept-charset="UTF-8">    

												  <div class="col-md-12">
														   <div class="panel-body">
																<div class="col-md-12">

																		<input type="hidden" name="tipo" id="tipo" value="N">
																		<input type="hidden" name="contador" id="contador" value="N">

																	  <button type="button" class="btn btn-block btn-info" onClick="GeneraConsulta()">
																	  <small>&nbsp;&nbsp;1.  Procesar Información  </small></button><br>  

																	  <small><input type="submit"  class="btn btn-block btn-warning" value="&nbsp;&nbsp;2. Generar reportes">
																	  </small>  

																 </div>              
														   </div>  
																  <input name="etiqueta" type="hidden" id="etiqueta">   
																  <input name="sfiltro" type="hidden" id="sfiltro"> 
																  <input name="tiposql" type="hidden" id="tiposql"> 
																  <input name="scolumna" type="hidden" id="scolumna">     
																  <input name="VistaNombre" type="hidden" id="VistaNombre"> 

																  <input name="s1" type="hidden" id="s1">     
																  <input name="c1" type="hidden" id="c1">     

																 <input name="editorFull" type="hidden" id="editorFull">      
																  <input name="editorfiltro" type="hidden" id="editorfiltro">    

														</div>
												</form>     
								  </div>
						</div>
					</div>
					</div>
	 
        </div>
    </div>