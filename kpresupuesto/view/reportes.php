<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title>Plataforma de Gestión Empresarial</title>
	
    <?php  require('Head.php')  ?> 
	
 <script src="../js/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>	
 
	
	
	
	 <script>        // Crear un elemento <script>       
		var script = document.createElement('script');        // Asignar la URL del archivo JavaScript con un timestamp para evitar caché     
		script.src = '../js/reportes.js?v=' + new Date().getTime();        // Agregar el script al <head> del documento       <script language="javascript" src="../js/requerimiento_p.js">  
		document.head.appendChild(script);    
	</script>
	
	
    
 <style type="text/css">
	 
	 	iframe { 
			display:block; 
			width:100%;  
			border:none;
			height: 550px;
			margin:0; 
			padding:0;
		}
	 
	    thead tr th{ 
          position: sticky;
          top: 0;
          z-index: 10;
          background-color: #ffffff;
        }
		
	 
	 .highlight {
 			 background-color: #FF9;
	   }
	
	 .de {
  			 background-color:#A8FD9E;
	  }
	 
	 .ye {
  			 background-color:#93ADFF;
	  }
	 
	 .di {
  			 background-color:#F5C0C1;
	  }
 	     	     	    		
	  #mdialTamanio{
        width: 85% !important;
       }
		
 
	.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 3px;
    line-height: 1.42857143;
    vertical-align: top;
    border-top: 1px solid #ddd;
   }
	 
     </style>
	
	
</head>
	
<body>


 <div id="mySidenav" class="sidenav hijo">
	 
		<div class="panel panel-primary">
		  <div class="panel-heading"><b>OPCIONES DEL MODULO</b></div>
				<div class="panel-body">
					<div id="ViewModulo"></div>
				</div>
		</div>
   </div>
    

<div id="main">
	
	<!-- Header -->
   
	<div class="col-md-12" role="banner">
 	   <div id="MHeader"></div>
 	</div> 
		
 
    
   <div class="col-md-12" > 
	   
       <!-- Content Here -->
	   
	    <div class="row">
			   
  		 <div class="col-md-12" style="padding: 15px;background-color: #C1D9FF">
				<div id="ViewFiltro"></div> 
 		 </div>	
			
 			
								<ul id="mytabs" class="nav nav-tabs">                   
									
										<li class="active"><a href="#tab1" data-toggle="tab"></span>
											<span class="glyphicon glyphicon-th-list"></span> <b> PRESUPUESTO INGRESOS</b></a>
										</li>
										 
										<li><a href="#tab2" data-toggle="tab">
											<span class="glyphicon glyphicon-link"></span> PRESUPUESTO GASTOS</a>
										</li>
			
	  								 	<li><a href="#tab3" data-toggle="tab">
											<span class="glyphicon glyphicon-link"></span> RESUMEN EJECUTIVO</a>
										</li>
	   
	  									 <li><a href="#tab4" data-toggle="tab">
											<span class="glyphicon glyphicon-asterisk"></span> RESUMEN MENSUAL EJECUCIÓN</a>
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

																<div class="col-md-12" style="padding: 5px">
																			<div id="ViewFiltro_i"></div> 
 																</div>	
 
																<div class="col-md-3" style="padding: 7px">
																	
																	
																	<button type="button" class="btn btn-sm btn-success" id="loadSaldos" title="Actualizar Saldos">  
																	<i class="icon-white icon-ambulance"></i> </button>
																	
																	
																	
																	<!--
																	<button type="button" class="btn btn-sm btn-primary" id="load">  
																	<i class="icon-white icon-search"></i> Buscar Mensual</button>	
																	-->
																	
																
																
																	
																	  	<button type="button" class="btn btn-sm btn-warning" id="loadtotalii">  
																	<i class="icon-white icon-search"></i> Buscar Periodo (*)</button>	
																	  



																	<button type="button" class="btn btn-sm btn-default" id="loadxls" title="Descargar archivo en excel">  
																	<i class="icon-white icon-download-alt"></i></button>	

																   <button type="button" class="btn btn-sm btn-default" id="loadprint" title="Imprimir Presupuesto de ingreso">  
																	<i class="icon-white icon-print"></i></button>	

															  </div>	
													 

																<div class="col-md-12" style="padding-bottom:5;padding-top:5px"> 

																	<div style="height: 650px; overflow-y: scroll;width: 100%">

																		
																		
																		  <div id="ViewFormIngresos"> </div>
																	      
																		   <div id="SaldoIngreso"> </div>

																   </div>   

															   </div>

												</div>
												  
								    </div>  

											 </div> 
 									
									    
										   <!-- Tab 2 -->

										   <div class="tab-pane fade in" id="tab2"  style="padding-top: 3px">

											  <div class="panel panel-default">

												   <div class="panel-body" > 

														 <div class="col-md-12" style="padding: 10px">
															 
																	<div id="ViewFiltrog"></div> 
															 
												    		 </div>	 

														   <div class="col-md-12" style="padding: 10px">
															   
															   <div class="col-md-2" style="display:none;padding:5px">
																   
																   <select class="form-control" name="ordena" id="ordena">
																   		<option   value="-">Ordenar por Partida</option>
																	     <option   value="funcion">Programa</option>
																	    <option   value="actividad">Actividad</option>
																	    <option   value="clasificador">Clasificador</option>
																	    <option   value="fuente">fuente</option>
																   </select>
 															   </div>		   
																  <div class="col-md-6">   
																	<button type="button" class="btn btn-sm btn-success" id="loadSaldosg" title="Actualizar Saldos">  
																	<i class="icon-white icon-ambulance"></i> </button>	

																	  
																	<button type="button" class="btn btn-sm btn-primary" id="loadPeriodo00">  
																	<i class="icon-white icon-search"></i> Buscar</button>	
																	  
																	  
																	  <button type="button" class="btn btn-sm btn-info" onClick="Gastos_sin()">  
																	<i class="icon-white icon-search"></i> Buscar Periodo (*)</button>
																	  
																	  
																	<!-- <button type="button" class="btn btn-sm btn-warning" id="loadg">  
																	<i class="icon-white icon-search"></i> Busqueda Ejecución  (Orientador)</button>	
																	   -->
																<!--	    		-->
																	  
																	  
																	

																	
																	  

																	<button type="button" class="btn btn-sm btn-default" id="loadxlsg" title="Descargar archivo en excel">  
																	<i class="icon-white icon-download-alt"></i></button>	

																   <button type="button" class="btn btn-sm btn-default" id="loadprintg" title="Imprimir Presupuesto de Gasto">  
																	<i class="icon-white icon-print"></i></button>	
																</div>
													      </div>	


														  <div class="col-md-4 mb-3">
																	<label for="searchInput" class="form-label">Busqueda en tabla de resultados:</label>
																	<input type="text" class="form-control" id="searchInput" onkeyup="filterTable()" placeholder="Buscar en el listado de partidas presupuestarias...">
																</div>

														   <div class="col-md-12" style="padding: 1px">

																	  <input type="hidden" id="bi" value="0">
																	  <input type="hidden" id="bg" value="0">



																	   <div class="col-md-12" style="padding-bottom:8;padding-top:8px;" > 

 
																				  <div id="ViewFormGastos"> </div>

 
																		</div>

														</div>

												   </div>
											  </div>
								  </div>
									
									
									      <div class="tab-pane fade in" id="tab3"  style="padding-top: 3px">

											  <div class="panel panel-default">

												   <div class="panel-body" > 

													 

														   <div class="col-md-12" style="padding: 10px">
																 
																	<button type="button" class="btn btn-sm btn-primary" id="loadg1">  
																	<i class="icon-white icon-search"></i> Generar Resumen Ejecutivo</button>	
															   
															        <div class="btn-group">
																	  <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
																	  Resumen Cedula Presupuestaria <span class="caret"></span></button>
																	  <ul class="dropdown-menu" role="menu">
																		<li><a id="loadg2" href="#">Gastos Grupo</a></li>
																		<li><a id="loadg3" href="#">Ingreso Grupo</a></li>
																	  </ul>
																	</div>

															 

   																 <button type="button" class="btn btn-sm btn-default" title = "Copiar en Excel" onClick="copiarAlPortapapeles('ViewFormResumen')">  
																	<i class="icon-white icon-paste"></i></button>	

															   

																   <button type="button" class="btn btn-sm btn-default" id="loadg122" >  
																	<i class="icon-white icon-print"></i></button>	

													       </div>	

 
														   <div class="col-md-12" style="padding: 1px">

															   <div class="col-md-12" style="padding-bottom:8;padding-top:8px"> 

																		 

																		  <div id="ViewFormResumen"> </div>

																	  

																</div>


														    </div>

												   </div>
											  </div>
								  </div>
									
									
									    <div class="tab-pane fade in" id="tab4"  style="padding-top: 3px">

											  <div class="panel panel-default">

												   <div class="panel-body" > 
 

													   		 <div class="col-md-12">
																 
																   <div class="col-md-3" style="padding:5px">
 																	   <select name="vtipo11" id="vtipo11" class="form-control"> 
																		 <option style="font-weight:bold" value="-">Grupo Presupuestario</option>
																		 <option style="font-weight:bold" value="1">Clasificador Presupuestario</option> 
  																	   </select>
																  </div>	
																 
																 
																  <div class="col-md-3" style="padding:5px">
 																	   <select name="vgrupog11" id="vgrupog11" class="form-control"> 
																		   <option style="font-weight:bold" value="-">-- 0. Grupo Presupuestario --</option> 
																		   <option style="font-weight:bold" value="71">71 GASTOS EN PERSONAL PARA INVERSION</option> 
																		   <option style="font-weight:bold" value="73">73 INVERSION</option> 
																		   <option style="font-weight:bold" value="77">77 OTROS GASTOS DE INVERSION</option> 
																		   <option style="font-weight:bold" value="78">78 TRANSFERENCIAS Y DONACIONES PARA INVERSION</option> 
																		   <option style="font-weight:bold" value="84">84 BIENES DE LARGA DURACION</option>
																		   <option style="font-weight:bold" value="97">97 CUENTAS PPR COBRAR</option>
																		 </select>
																	</div>	
																   <div class="col-md-3" style="padding:5px">
 																	   <select name="vfuentegg11" id="vfuentegg11" class="form-control"> 
																		 <option style="font-weight:bold" value="-">-- 0. Fuente de Financiamiento --</option>
																		 <option style="font-weight:bold" value="001">001 Recursos Municipales</option> 
																		 <option style="font-weight:bold" value="002">002 Propias</option> 
 																	   </select>
																  </div>		   
 
															 </div>	
													   
														       <div class="col-md-12" style="padding: 10px">
 																	<button type="button" class="btn btn-sm btn-primary" onClick="ResumenMes()">  
																	<i class="icon-white icon-search"></i> Generar Detalle Periodo</button>	
															  
													          </div>	

															   <div class="col-md-12" style="padding-bottom:8;padding-top:8px"> 
 
																		  <div id="ViewFormPeriodo"> </div>
 
																</div>


														    

												   </div>
											  </div>
								  </div>
									
				 				</div>
			 
 		</div>
    </div>
     <!-- /.auxiliar -->  
   
  <div class="container"> 
	  <div class="modal fade" id="myModalAux" tabindex="-1" role="dialog">
  	  <div class="modal-dialog" id="mdialTamanio">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3  class="modal-title">Detalle de Movimientos</h3>
		  </div>
				  <div class="modal-body">
				   <div class="form-group" style="padding-bottom: 10px">
			          <div class="panel panel-default">
			          
 				         <div class="panel-body">
 					  		 
							 
							 		<div id="ViewFiltroAux"> var</div> 
							 
							 
 					  		 <div id="guardarAux"></div> 
					     </div>
					     </div>   
  					 </div>
				  </div>
				
		  <div class="modal-footer">
 
			<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Salir</button>
 		  </div>
		</div><!-- /.modal-content --> 
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
   </div>  
	
	

  <div class="container"> 
	  <div class="modal fade" id="myModalCostos" tabindex="-1" role="dialog">
  	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 class="modal-title">Selección de Costos</h3>
		  </div>
				  <div class="modal-body">
				   <div class="form-group" style="padding-bottom: 10px">
			          <div class="panel panel-default">
			          
 				         <div class="panel-body">
 					  		 <div id="ViewFiltroCosto"> var</div> 
 					  		 <div id="guardarCosto"></div> 
					     </div>
					     </div>   
  					 </div>
				  </div>
				
		  <div class="modal-footer">
		    <button type="button" class="btn btn-sm btn-primary"  onClick="GuardarCosto()">
		    <i class="icon-white icon-search"></i> Guardar</button> 
			<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Salir</button>
 		  </div>
		</div><!-- /.modal-content --> 
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
   </div> 
	
  	<!-- Page Footer-->
    <div id="FormPie"></div>  
 </div>   
 </body>
</html>
 