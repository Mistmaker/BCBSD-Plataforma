<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title><?php  echo trim($_SESSION['ruc_registro']).' '.trim($_SESSION['razon']) ?></title>
	
    <?php  require('Head.php')  ?> 
	
     
 <script type="text/javascript" src="../js/co_inversion.js"></script> 
    
 <style type="text/css">
	 
	 	iframe { 
			display:block; 
			width:100%;  
			border:none;
			height: 550px;
			margin:0; 
			padding:0;
		}
		#mdialTamanio{
  					width: 65% !important;
        }   
     
       #mdialNovedad{
  					width: 55% !important;
        }  
	 
		.highlight {
  					background-color: #FF9;
		}
		.ye {
  					background-color:#93ADFF;
		}
            
	 
	 .ya {
  					background-color:#FDA9AB;
		}
            
</style>		     	     	    		

     
	
</head>
	
<body>

 <!-- ------------------------------------------------------ -->  
	
	
	 <div class="col-md-12" role="banner">
		 
 	   <div id="MHeader"></div>
		 
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
	
    <div class="col-md-12"> 
    
		<!-- Content Here -->
	    
		 
 		
			      <div class="col-md-12">
							  
								<ul id="mytabs" class="nav nav-tabs">                    
										<li class="active"><a href="#tab1" data-toggle="tab"></span>
											<span class="glyphicon glyphicon-th-list"></span> <b> RESUMEN - CUENTAS DE INVERSION</b></a>
										</li>
										 
										 
		 			
								</ul>
		
								<!-- ------------------------------------------------------ -->
								<!-- Tab panes -->
								<!-- ------------------------------------------------------ -->
		
								<div class="tab-content">
									
										   <!-- Tab 1 -->
									
										  <div class="tab-pane fade in active"   id="tab1" style="padding-top: 3px">
											  
											  <div class="panel panel-default">
												  
												  <div class="panel-body" > 

												   <div class="col-md-12" style="padding: 1px">

													   <div class="col-md-12">
														   
														   	  <div class="col-md-3">
																  
																 <select id="anio" name = "anio" class="form-control">
																		   <option value="2024">2024</option>	 
																		   <option value="2023">2023</option>	 
																		   <option value="2022">2022</option>	 
																		   <option value="2021">2021</option>
																		   <option value="2025">2025</option>
																		   <option value="2026">2026</option>
																		   <option value="2027">2027</option>
																 </select>
																  
															  </div>  
													
														      <div class="col-md-3">
																  
																  <select id="cmes" name = "cmes" class="form-control">
																	   <option value="-">Todos</option>
																      <option value="1">Enero</option>
																      <option value="2">Febrero </option>
																	  <option value="3">Marzo </option>
																	  <option value="4">Abril </option>
																	  <option value="5">Mayo</option>
																	  <option value="6">Junio </option>
																	  <option value="7">Julio </option>
																	  <option value="8">Agosto </option>
																	  <option value="9">Septiembre</option>
																	 <option value="10">Octubre</option>
																	 <option value="11">Noviembre</option>
																	 <option value="12">Diciembre</option>
 																 </select>
																  
															  </div>  
														   
														      <div class="col-md-2" style="padding: 1px"> 

																 <button type="button" class="btn btn-primary btn-sm btn-block" id="load">  
																			<i class="icon-white icon-search"></i> Busqueda</button>	

															</div>
														   
														    <div class="col-md-2" style="padding: 1px"> 
 														     
														   	<button type="button"   data-toggle="modal" onClick="TotalSeleccion()" data-target="#myModalCxp" class="btn btn-default btn-sm" id="loadxls2">  
																					<i class="icon-white icon-ambulance"></i> Contabilizar
															  					</button>	
														   
														   	</div>
														   
														     
 														   
													   </div>  
													
													   	<div class="col-md-10">

 															<div class="table-responsive" id="employee_table">  

															    <div id="Viewlibro">  </div>   
  														   </div>   
 														</div>  

 				 
													</div>

											   </div>  
											 </div> 
										  </div>

										 <!-- Tab 2 -->
 
										  </div>

										<!-- Tab 3 -->	
 
					    </div>
			      
 		</div>
   
     <!-- /.auxiliar -->

  <input type="hidden" id="prove" name="prove">

  <div id="ViewFormAux">  </div>
    
  <div class="container"> 
  	  
	  <div class="modal fade" id="myModalCxp" tabindex="-1" role="dialog">
						  <div class="modal-dialog" id="mdialTamanio">
									<div class="modal-content">
											  <div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
												<h3  class="modal-title">Detalle </h3>
											  </div>
											  <div class="modal-body">

												<div class="form-group" style="padding-bottom: 2px">
															 <div class="panel panel-default">

																		 <div class="panel-body">
																			 
																	       <div class="col-md-12">
																			   
																			     <div class="col-md-4"  style="padding: 5px">
																			   
																					INGRESE LA FECHA DE CONTABILIZACIÓN
																			   
																			      </div>
																			   
																			   
																			          <div class="col-md-8" style="padding: 5px">
																						
																						<input type="date" name="fechai" id="fechai" required="required" autocomplete="off_s" class="form-control" placeholder="Fecha es requerido " size="15" maxlength="15" value="">
																						
																						
																						 </div>
																			   
																			 </div>
																			 
																			   <div class="col-md-12">
																			   
																			     <div class="col-md-4"  style="padding: 5px">
																			   
																					INGRESE DETALLE/OBSERVACION
																			   
																			      </div>
																			   
																			   
																			          <div class="col-md-8"  style="padding: 5px">
																						
																					 <textarea name="detalle" class="auto form-control" cols="45" rows="3" maxlength="300" id="detalle" placeholder="Detalle es requerido "></textarea>
																						
																						
																						 </div>
																			   
																			 </div>
																			 
																			 
																				  <div class="col-md-12" style="padding-bottom: 10px;padding-top: 10px">
																			 
																			   			 <p align="center">Realizar la contabilización de inversión</p> 
																				  
																				   <div id="ViewFormTotal">  </div>   
																					  
																				  </div>
 																		 </div>

															 </div>   
														 </div>
											  </div>

											  <div class="modal-footer">
												  
												  <button type="button" onClick="Contabilizar();" class="btn btn-sm btn-success">Contabilizar</button>
												  
												  <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Salir</button>
											  </div>
									</div>
									<!-- /.modal-content --> 
						  </div>
						  <!-- /.modal-dialog -->
	  </div>
	  
 </div>  
 
  <div class="container"> 
  	  
	  <div class="modal fade" id="myModalAsiento" tabindex="-1" role="dialog">
						  <div class="modal-dialog" id="mdialTamanio">
									<div class="modal-content">
											  <div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
												<h3  class="modal-title">Detalle </h3>
											  </div>
											  <div class="modal-body">

												<div class="form-group" style="padding-bottom: 2px">
															 <div class="panel panel-default">

																		 <div class="panel-body">
																			 <div id="ViewFiltroCxp"> var</div> 
																			 
																			  <div id="ViewGuarda" style="padding: 25px" align="center"> </div>
																			 
																		 </div>

															 </div>   
														 </div>
											  </div>

											  <div class="modal-footer">
												  
												  <button type="button" onClick="CXPContabilizar();" class="btn btn-sm btn-success">Generar Contabilizar</button>
												  
												  <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Salir</button>
											  </div>
									</div>
									<!-- /.modal-content --> 
						  </div>
						  <!-- /.modal-dialog -->
	  </div>
	  
 </div>  
   
	
  	<!-- Page Footer-->
    <div id="FormPie"></div>  
   
 </body>
</html>
 