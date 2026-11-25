<?php 
	session_start( );  
	
 

  	if (empty($_SESSION['usuario']))  {
		header('Location: ../kadmin/view/login');
		exit;
    }else{
		header('Location: view/inicio');
	}
	
	
?>

 