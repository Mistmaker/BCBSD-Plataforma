<?php 

	session_start( );  
 

  	if (empty($_SESSION['usuario']))  {
		
		header('Location: view/login');
		exit;
		
    }else{
		
		header('Location: view/View-panel');
	}
	
	
?>
 