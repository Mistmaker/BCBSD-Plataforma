// JavaScript Docume
$(function(){
	
    $(document).bind("contextmenu",function(e){
        return false;
    });
	
});


$(document).ready(function() {

		 $("#show").load('../controller/grupo.php');
		 
	     $("#FormPie").load('../view/View-Pie.php');
	     
	     $("#NavMod").load('../view/View-HeaderInicio.php');
 
	     
	     var modulo =  'kreportes';
	     
		 var parametros = {
				    'ViewModulo' : modulo 
	    };

		$.ajax({
				data:  parametros,
				url:   '../model/Model-moduloOpcion.php',
				type:  'GET' ,
				cache: false,
			   beforeSend: function () { 
							$("#ViewModulo").html('Procesando');
					},
				success:  function (data) {
						 $("#ViewModulo").html(data);  // $("#cuenta").html(response);
					} 
		}); 
	     
});
		
 
function Dir(type)
		{
 
 	       var parametros = {
					"type" : type 
			};
 		 
			$.ajax({
					data:  parametros,
					url:   '../controller/DirVista.php',
					type:  'GET' ,
					beforeSend: function () { 
 							$("#ListaVista").html('<img src="loader.gif"/>');
  					},
 					success: function(data) {
							 $("#ListaVista").html(data); 
                   },
				   error: function() {
					 alert( "Ha ocurrido un error" );
					 
				  }
			}); 
	 	 
 }
//-------------------------------------------------------------------- 
function capavisor(url,parameter){      
	 	
  "use strict";
	 
	$("#capa").load(url);
   
 
	  
}   

function getParameterByName(name) {
 
	
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}