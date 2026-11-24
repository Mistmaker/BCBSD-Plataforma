	<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

    session_start( );  
  			$etiqueta      =  $_POST['etiqueta'];
 		    $titulo			= $_POST['editorFull']; 
			$filter        =  $_POST['sfiltro'];
			$VistaNombre   =  $_POST['VistaNombre'];
			$s1	   		   =  $_POST['s1'];
			$c1 		   =  $_POST['c1'];
	  		$tiposql	   =  $_POST['tiposql'];
			$validareporte =  $_POST['scolumna'];

			$variable 	   = base64_decode($VistaNombre);
			$Objeto 		= explode("-",$variable) ;
			$tabla  		= $Objeto[0];
			$nombrereporte  = $Objeto[1];
			$parafiltro     =  $_POST['editorfiltro'];
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	
	<meta name="viewport" content="width=device-width,initial-scale=1">
	
	
	
 
	<link rel="stylesheet" type="text/css" href="js/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="js/bootstrap.min.css" >	 
   	<link rel="stylesheet" type="text/css" href="js/dataTables.bootstrap4.min.css"   > 
    <!--	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">  -->
   <link rel="stylesheet" type="text/css" href="js/buttons.bootstrap4.min.css"> 
   <!--	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css"> -->
   <link rel="stylesheet" type="text/css" href="js/jquery-ui.css">
 
 
	<title><?php echo $nombrereporte; ?></title>
	
	<style type="text/css">
		.bs-example{
			margin: 20px;
		}
		
		 hr{ 
		   height:3px; 
		   background-color:#C3C3C3; 
		   border:none; 
		 } 
	</style>
	
 

<script type="text/javascript" language="javascript" src="js/jquery-1.12.4.js"> </script>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"> </script> 
<script type="text/javascript" language="javascript" src="js/dataTables.buttons.min.js"> </script> 
<script type="text/javascript" language="javascript" src="js/buttons.bootstrap4.min.js"> </script> 
<script type="text/javascript" language="javascript" src="js/jszip.min.js"> </script> 
<script type="text/javascript" language="javascript" src="js/pdfmake.min.js"> </script>   
<script type="text/javascript" language="javascript" src="js/vfs_fonts.js"> </script>   
<script type="text/javascript" language="javascript" src="js/buttons.html5.min.js"> </script>        
<script type="text/javascript" language="javascript" src="js/buttons.print.min.js"> </script>    
<script type="text/javascript" language="javascript" src="js/buttons.colVis.min.js"></script>  
    
    
 
  <script type="text/javascript" language="javascript" src="../js/vistaquery.js"></script>
  <script type="text/javascript" language="javascript" src="../js/graficoDet.js"></script>
	     	 	     	
	  <script type="text/javascript" class="init">
	

		$(document).ready(function(){
			 	
			<?php 
 			
				if ($validareporte == '1') {
						echo "IniObj('".$nombrereporte."','".$etiqueta."',".'"'.$filter.'"'.",'".$VistaNombre."','".$tiposql."','".$s1."','".$c1."','".$titulo."'); 
							  CreaObjTable(); 
							  DrawObjTable();";	
					
 							  echo 'SinAgruparTable();';
							 
					} 
				else
					{
						echo "window.close()";
					}
  			 ?>  
 
      	});

	</script>
 
     
	 <style type="text/css">
  		#mdialTamanio{
  					width: 75% !important;
		}
	 
	    #mdialTamanio4{
  					width: 65% !important;
		}
	 
	 
	 	 .form-control{  
		  display: block;
 		  height: 32px;
		  padding: 3px 3px;
		  font-size: 13px;
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
 	 
</head>
<body> 
	
	
    <div class="bs-example">
		 <div class="panel panel-default" >
			<!-- Default panel contents -->
			<div class="panel-heading" id = "datoVista">User Information</div>
			<div class="panel-body">
		 
				    <div class="col-md-12"  >  
							<div class="content" id="imprime">
								<div id="TablaDinamica"></div>
							</div>
					</div>	
				
					<div class="col-md-12">  
							<?php 
						
								 $pieces = explode("-", $parafiltro);
						
 								  echo '<h5 align="center"><b>Filtro Busqueda'.'<br><br>';
						
									foreach ($pieces as $values)
											{
											 echo  '&nbsp;'.$values.'<br>';
											}
 
								 echo '</b></h5>';

									  if ($tiposql == 'S') {	 

											echo '<div id="div_pie" style="min-width: 100%; height: 350px; margin: 0 auto"> </div> ' ;

										 }
							?>	 
 							<div align="center" id="analisisDB"></div>
 					</div>	
 		      
		            <div class="col-md-12">   
  								 <hr>
											<div class="col-md-12" align="center">  
												
												   <div id="result"> </div>
											
													<div id="div_linea" style="min-width: 100%; height: 650px; margin: 0 auto"> </div>
							 
			     							</div>
 	         
 	     
						
		  <script src="highcharts.js"></script>
		  <script src="exporting.js"></script>
		  <script src="export-data.js"></script>
		  <script src="accessibility.js"></script>	  	
						
		   </div>
		</div>
 	</div>
	
 	     <input name="titulo" type="hidden" id="titulo"> 
		 <input name="sfiltro" type="hidden" id="sfiltro"> 
		 <input name="etiqueta" type="hidden" id="etiqueta"> 
		 <input name="tiposql" type="hidden" id="tiposql"> 
		 <input name="scolumna" type="hidden" id="scolumna">     
		 <input name="VistaNombre" type="hidden" id="VistaNombre">  
		 <input name="s1" type="hidden" id="s1"> 
		 <input name="c1" type="hidden" id="c1">  
 		 <input name="ncolumna" type="hidden" id="ncolumna">  
	
	     <input name="filtro_analisis" type="hidden" id="filtro_analisis">  
		  
   <footer class="main-footer">
            <div class="container-fluid">
              <div class="row">
                <div class="col-sm-6">
                  <p style="font-size: 9px">Kaipi &copy; 2017-2019</p>
                </div>
                <div class="col-sm-6 text-right">
                  <p style="font-size: 9px">Design by <a href="#"> JASAPAS</a> framework Kaipi V.2017</p>
                  <!-- Please do not remove the backlink to us unless you support further theme's development at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
                </div>
              </div>
            </div>
 </footer> 
</body>
</html>