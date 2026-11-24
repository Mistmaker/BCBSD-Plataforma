<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Visor</title>

  	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.datatables.net/rss.xml">
	<link rel="stylesheet" type="text/css" href="https://datatables.net/media/css/site-examples.css?_=28caf719f2a3866baf6bb3a3255c9c35">
   
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
   	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.jqueryui.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.dataTables.min.css">
   
 
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.6/semantic.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.semanticui.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.semanticui.min.css">
     
      
    <link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="resources/demo.css">

 
   	
	<style type="text/css" class="init">
	
	</style>
	
	<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-1.12.4.js"> </script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"> </script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"> </script>
	
	<script type="text/javascript" language="javascript" src="http://code.jquery.com/jquery-1.12.4.js"> </script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js"> </script>
	<script type="text/javascript" language="javascript" src="resources/syntax/shCore.js"> </script>
	<script type="text/javascript" language="javascript" src="resources/demo.js"> </script>
	
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"> </script>
 
 
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"> </script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.semanticui.min.js"> </script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"> </script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.semanticui.min.js"> </script>
	
 
 
 <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"> </script>
 <script type="text/javascript" language="javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"> </script>
  <script type="text/javascript" language="javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"> </script>
 <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"> </script>
 <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"> </script>
 <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.colVis.min.js"> </script>	
 <script type="text/javascript" language="javascript" src="../js/vistaquery.js"></script>
 <script type="text/javascript" language="javascript" class="init">
		
  $(document).ready(function(){
 <?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
 
 		$Columnas   =  ($_GET['Col']);
 		$filter   =    ($_GET['filter']);
 		$view   =      ($_GET['view']);
		$name   =      $_GET['name'];
 		$etiqueta   =  ($_GET['etiqueta']);
 		$group  =      ($_GET['group']);
		
		 echo " 
		 		 
					IniObj('SqlCol','SqlEtiq','".$name."');
					CreaObjTable('".$etiqueta."') ;
					DrawObjTableE('".$view."','".$Columnas."','".$filter."','".$group ."') ;
					";
 		echo  "  ";
		
 ?>  
	  });
	  </script>
</head>
<body>
 <div class="container" style="padding:5px;5px;">
  <div class="panel panel-primary">
   <div class="panel-heading" id = "datoVista">Visor de información </div>
   <div class="panel-body">
     
      <div id="TablaDinamica"></div>
     
     </div>
   </div> 
  </div>  
 
 
</body>
</html>