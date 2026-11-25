<?php require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión ?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    
    <title>Plataforma de Gestión Empresarial</title>
	
    <?php  require('Head.php')  ?> 
 
 	<script type="text/javascript" src="../js/configuracion.js"></script> 
	
	
	 <style type="text/css">
		 
  		#mdialTamanio{
  					width: 75% !important;
		}
	 
	 	 .form-control_asiento {  
		  display: block;
 		  height: 28px;
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
		 
		 
		.scrolly{
            width: 100%;
            height: 250px;
            overflow: auto;
            overflow-y: hidden;
            margin: 0 auto;
            white-space: nowrap
        }
 	 	
		  </style>
	
</head>
<body>

   
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
	       <ul id="mytabs" class="nav nav-tabs">          
                  		          
                   		<li class="active"><a href="#tab1" data-toggle="tab"></span>
 							<span class="glyphicon glyphicon-th-list"></span> <b>GRUPO DE INFORMACION</b></a>
                   		</li>
	
                  		<li><a href="#tab2" data-toggle="tab">
                  			<span class="glyphicon glyphicon-link"></span> PARAMETRIZACION DE DATOS</a>
                  		</li>
           </ul>
                    
           <!-- ------------------------------------------------------ -->
           <!-- Tab panes -->
           <!-- ------------------------------------------------------ -->
           <div class="tab-content">
            
				   <!-- ------------------------------------------------------ -->
				   <!-- Tab 1 -->
				   <!-- ------------------------------------------------------ -->

					<div class="tab-pane fade in active" id="tab1" style="padding-top: 3px">
							  <div class="panel panel-default">
								  <div class="panel-body" > 

									  <div class="col-md-12"> 


												 <div class="alert alert-info">
														<div class="row">
													  <div class="col-md-2"  style="padding-top: 5px;">
														  
														<select name="qestado"  id="qestado" class="form-control required">
															
																	 
														</select>
														  
													   </div> 
														  
													   <div class="col-md-2"  style="padding-top: 5px;"> 
															<button type="button" class="btn btn-sm btn-primary" id="load"><i class="icon-white icon-search"></i> Búsqueda</button>
														</div> 
													</div> 
												 </div> 		

									 </div> 


									 <div class="col-md-12"> 
										 
												  <table id="jsontable" class="display table-condensed" cellspacing="0" width="100%">
														 <thead  bgcolor=#F5F5F5>
														   <tr>   
																<th width="10%">Grupo</th>
																<th width="20%">Nombre</th>
																<th width="30%">Vista</th>
																<th width="10%">Estado</th>
																<th width="20%">Detalle</th>
																<th width="10%"></th>
														   </tr>
														</thead>
												 </table>

									 </div>  

								 </div>  
							 </div> 
					 </div>

					 <!-- ------------------------------------------------------ -->
					 <!-- Tab 2 -->
					 <!-- ------------------------------------------------------ -->
           
                   <div class="tab-pane fade in" id="tab2"  style="padding-top: 3px">
                      <div class="panel panel-default">
						
					        <div class="panel-body" > 
						    
						  		   <div id="ViewForm"> </div>
						   
               		       </div>
                	  </div>
             	 </div>
				
				
          	 </div>
		   
 		</div>
 
 <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog" id="mdialTamanio">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Visor</h4>
        </div>
        <div class="modal-body">
					<div class="modal-body">
								     <div class="panel panel-default">
 											  <div class="panel-body">
			
          											<div   id="visor_datos"></div>
											</div>
								</div>
					</div>	
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
 
 </body>
</html>
