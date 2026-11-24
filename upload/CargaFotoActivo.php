<!DOCTYPE html>
<html>
<?php
	session_start();
    require '../kconfig/Db.class.php';    
 	require '../kconfig/Obj.conf.php';  

	//  error_reporting(E_ALL);
	//  ini_set('display_errors', 1);
 
	$obj   = new objects;
 	$bd    = new Db ;
 
	$bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
	
 	if  (isset($_GET['status'])) {
		
			 	$carpeta =  $_GET['file'] ;
				 
				$id =  $_GET['id'] ;
				$archivo =  $_POST["userfile"];
     	
			    $filename = guarda_imagen('sas',$carpeta,$archivo,$bd,$id  ); 
				
				$filename = trim($filename) ;
 	} 
 ?>
<head>
	
 <meta charset="utf-8">
   
    <link rel="shortcut icon" href="../app/kaipi-favicon.png">
    <link rel="stylesheet" href="../app/fontawesome/font-awesome.min.css"/>
    <link href="../app/dist/css/bootstrap.css" rel="stylesheet">
    <link href="../app/dist/css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Ionicons -->
	<link href="../app/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    
    <link rel="stylesheet" href="../app/css/jquery-ui.min.css" type="text/css" />
     
    <link rel="stylesheet" href="../app/dist/css/bootstrap.min.css" />
    
    <link rel="stylesheet" href="../app/dist/css/dataTables.bootstrap.min.css" />
    
   <script type="text/javascript" src="../app/js/jquery-1.10.2.min.js"></script>
   
   <script src="../app/dist/js/bootstrap.min.js"></script>  
     
   <script src="../app/js/jquery.min.js"></script>  
 
   <script type="text/javascript" src="../kconfig/kaipi.js"></script>

   <script type="text/javascript">

 function retorna_valor(symbol) {
 
      var valor = document.getElementById(symbol).value;
	 
	  var url   = '../../archivos/activos/' + valor ;
    
 	  window.opener.document.getElementById('foto').value = valor;
 	 
      window.opener.document.getElementById('ImagenActivo').src = url;
	  
 	  window.top.close(); 	 
	 
}
function url_query(url) {
 	     window.location= url ;
		 return true;
 } 
</script> 
	
</head>
	
<body>
	
	<div class="container" style="padding: 15px">
	 
	     <div class="panel panel-primary">
    
      			  <div class="panel-heading">Carga de archivos</div>
        
						<div class="panel-body">

							<form action="CargaFotoActivo?status=ok&file=<?php echo htmlspecialchars((string) $_GET['file'], ENT_QUOTES) ?>&id=<?php echo htmlspecialchars((string) $_GET['id'], ENT_QUOTES) ?>" method="post" enctype="multipart/form-data" id="forma">
	
							<input name="idprov" type="text" class="form-control" id="idprov" autocomplete="off" value="<?php 
													echo htmlspecialchars((string) $_GET['id'], ENT_QUOTES)
													?>" size="60" readonly />

									   <div class="col-md-10" style="padding: 5px"> 
												<input name="userfile" type="file" accept="image/*" id="userfile" >
									   </div>
								 
										<div class="col-md-10" style="padding: 5px"> 
											
											 <button type="submit" class="btn btn-success">
												<span class="glyphicon glyphicon-cloud-upload"></span> Cargar archivo
											  </button>
  											
 									   </div>

										<div class="col-md-10" style="padding: 5px"> 
											
											 <button type="button" class="btn btn-info" onClick="retorna_valor('archivo_file')">
												<span class="glyphicon glyphicon-paste"></span> Enlazar archivo
											  </button>
   											
									   </div>
									   
									   <div class="col-md-10" style="padding: 2px"> 
										   
												<input name="archivo_file" type="text" class="form-control" id="archivo_file" autocomplete="off" value="<?php 
													global $filename ;
													echo str_replace('"', '&quot;', trim($filename)); 
													?>" size="60" readonly />
										   
									   </div>
									   
									   
						       </form>     
							
        	           </div>  
		 </div>  	
		 			  
   	</div>  				  
 		
 </body>
	
</html> 

<?php
   function guarda_imagen($sesion_variable,$carpeta_variable,$archivo ,$bd ,$id  ){

	       global $filename;
	   
	   	    $folder    		  = '../archivos/activos/';//$bd->_carpeta_archivo('2'); // carpeta parametrizada
	   
	   		$archivo_temporal = $_FILES["userfile"]['tmp_name'];
			   
			$fileInfo = pathinfo($_FILES["userfile"]['name']);
			$extensionArchivo = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
			$archivo 		  = $id.'.'.$extensionArchivo;

		    $copiado 		  = kmover_subida($archivo_temporal,$folder.$archivo); 
  	   
			if($copiado){ 
				
				echo '<div class="alert alert-success">  <strong>Success!</strong> El archivo se subio correctamente.</div>';
 				return $archivo;
				
				
			}else{ 
				
				echo '<div class="alert alert-danger">  <strong>Error!</strong>'."Not uploaded because of error #".$_FILES["userfile"]["error"].'</div>';
				return     "Error #".$_FILES["userfile"]["error"];
				
			}
 	
 	
		 
	}
?>