<?php 

	session_start( );  
 

  	if (empty($_SESSION['usuario']))  {
		
		header('Location: ../kadmin/');
		exit;
		
    }else{
		
		header('Location: view/inicio');
	}
	
	
?>
 