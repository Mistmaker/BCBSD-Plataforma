 var oTable;
  
$(function(){
	
    $(document).bind("contextmenu",function(e){
        return false;
    });
	
});


//-------------------------------------------------------------------------
$(document).ready(function(){
    
	
		$("#MHeader").load('../view/View-HeaderModel.php');
	
		modulo();
 		
	    FormView();
	    
 
   	    oTable = $('#jsontable').dataTable(); 
	    
	   
	 	   $('#load').on('click',function(){
	 	 		 
	 		   			BusquedaGrilla(oTable);
	          
		});
     

		

		$.ajax({
			url: "../model/ajax_tipo_seleccion.php",
			type: "GET",
			success: function(response)
			{
					$('#qestado').html(response);
			}
			});
 	   
    
    
	         
});  
//-----------------------------------------------------------------
function changeAction(tipo,action,mensaje){
			
			if (tipo =="confirmar"){			 
			
			  alertify.confirm("<p>"+mensaje+"<br><br></p>", function (e) {
			  if (e) {
				 
                 	
                    LimpiarPantalla();
                    
					$("#action").val("add");
					
					$("#result").html('<img src="../../kimages/z_add.png" align="absmiddle"/><b> AGREGAR NUEVO REGISTRO</b>');

					 
			  }
			 }); 
			}
			if (tipo =="alerta"){			 
			  alertify.alert("<b>"+mensaje+"<br><br></p>", function () {
			  });
			 }		  
			return false	  
		   }
//-------------------------------------------------------------------------
// ir a la opcion de editar
function goToURL(accion,id) {
 
 
     var parametros = {
					'accion' : accion ,
                    'id' : id 
 	  };
	  $.ajax({
					data:  parametros,
					url:   '../model/Model-configuracion.php',
					type:  'GET' ,
 					beforeSend: function () { 
 							$("#result").html('Procesando');
  					},
					success:  function (data) {
							 $("#result").html(data);  // $("#cuenta").html(response);
						     
							 DetalleVariables();
  					} 
			}); 

    }
 
//-------------------------------------------------------------------------
var formatNumber = {
 separador: ".", // separador para los miles
 sepDecimal: ',', // separador para los decimales
 formatear:function (num){
  num +='';
  var splitStr = num.split('.');
  var splitLeft = splitStr[0];
  var splitRight = splitStr.length > 1 ? this.sepDecimal + splitStr[1] : '';
  var regx = /(\d+)(\d{3})/;
  while (regx.test(splitLeft)) {
  splitLeft = splitLeft.replace(regx, '$1' + this.separador + '$2');
  }
  return this.simbol + splitLeft  +splitRight;
 },
 new:function(num, simbol){
  this.simbol = simbol ||'';
  return this.formatear(num);
 }
}
//-------------------------------------------------------------------------
// ir a la opcion de editar
function LimpiarPantalla() {
  
	$("#idreport").val("");
	$("#inombre").val("");
	$("#ivista").val("");
	$("#imodulo").val("");
	$("#iestado").val("");
	$("#idetalle").val("");

	$("#esquema").val("public");
	
       			   
    }
   
 
 //---------------------------
 function fecha_hoy()
{
   
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    
    if(dd < 10){
        dd='0'+ dd
    } 
    if(mm < 10){
        mm='0'+ mm
    } 
  
    
    var today = yyyy + '-' + mm + '-' + dd;
    
 
            
} 
 
  //------------------------------------------------------------------------- 
  function BusquedaGrilla(oTable){        	 

	 //  var oTable = $('#jsontable').dataTable();  //Initialize the datatable

 	  
       
     	var GrillaCodigo = $("#qestado").val();
    
          
      var parametros = {
				'GrillaCodigo' : GrillaCodigo  
      };

	 
		$.ajax({
		 	data:  parametros,
		    url: '../grilla/grilla_configuracion.php',
			dataType: 'json',
			success: function(s){
			 console.log(s); 
			oTable.fnClearTable();
			
			for(var i = 0; i < s.length; i++) {
			    	oTable.fnAddData([
							s[i][0],
							s[i][1],
							s[i][2],
 	                        s[i][3],
                          s[i][4],
                         	'<button class="btn btn-xs btn-warning" onClick="javascript:goToURL('+"'editar'"+','+ s[i][0] +')"><i class="glyphicon glyphicon-edit"></i></button> ' + 
							'<button class="btn btn-xs btn-danger" onClick="javascript:goToURL('+"'del'"+','+ s[i][0] +')"><i class="glyphicon glyphicon-remove"></i></button>' 
						]);										
					} // End For
										
			},
			error: function(e){
			   console.log(e.responseText);	
			}
			});
	 
		
		 
 		 
		
  }   
//--------------
  function CargaDatos()
  {
   
	 var ivista  = $("#ivista").val();
	 var esquema = $("#esquema").val();
    
 
   if ( ivista ) {
 		 
		 	 var parametros = {
		 			    'ivista' : ivista,
						'esquema': esquema
		     };
		 	  
		 	  
		 	$.ajax({
		 			data:  parametros,
		 			 url:   '../model/Model-carga_vista.php',
		 			type:  'GET' ,
		 				beforeSend: function () { 
		 						$("#GuardaVariables").html('Procesando');
		 				},
		 			success:  function (data) {
		 				
		 					 $("#GuardaVariables").html(data);  
		 				     
		 					DetalleVariables() ;
		 				} 
		 	});
       
	  }
  }
  
//--------------
  function DetalleVariables()
  {
   
	 var ivista = $("#ivista").val();
    
 
   if ( ivista ) {
 		 
		 	 var parametros = {
		 			    'ivista' : ivista
		     };
 		 	  
		 	$.ajax({
		 			data:  parametros,
		 			 url:   '../model/Model-detalle_vista.php',
		 			type:  'GET' ,
		 				beforeSend: function () { 
		 						$("#DetalleVariables").html('Procesando');
		 				},
		 			success:  function (data) {
		 					 $("#DetalleVariables").html(data);  // $("#cuenta").html(response);
		 				     
		 				} 
		 	});
       
	  }
  }  
  
//-----------------  
  function VisorDatos()
  {
   
	 var ivista = $("#ivista").val();
	 var esquema = $("#esquema").val();
    
 
   if ( ivista ) {
 		 
		 	 var parametros = {
		 			    'ivista' : ivista,
						'esquema':esquema
		     };
 		 	  
		 	$.ajax({
		 			data:  parametros,
		 			 url:   '../model/Model-visor_vista.php',
		 			type:  'GET' ,
		 				beforeSend: function () { 
		 						$("#visor_datos").html('Procesando');
		 				},
		 			success:  function (data) {
		 					 $("#visor_datos").html(data);  // $("#cuenta").html(response);
		 				     
		 				} 
		 	});
       
	  }
  }  
//------------------  
  function actualiza_datod(valor,id,campo)
  {
    
		 	 var parametros = {
		 			    'valor' : valor,
		 			    'id' : id,
		 			    'campo' : campo
		     };
 		 	  
		 	$.ajax({
		 			data:  parametros,
		 			 url:   '../model/Model-actualiza_vista.php',
		 			type:  'GET' ,
		 				beforeSend: function () { 
		 						$("#GuardaVariables").html('Procesando');
		 				},
		 			success:  function (data) {
		 					 $("#GuardaVariables").html(data);  // $("#cuenta").html(response);
		 				     
		 				} 
		 	});
       
	 
  }  
//---------------------  
  function myFunction(id,objeto,campo)
  {
    
	  
	  if (objeto.checked == true){
	    	estado = 'S'
	    } else {
	    	estado = 'N'

	    }
	   
		 	 var parametros = {
		 			    'valor' : estado,
		 			    'id' : id,
		 			    'campo' : campo
		     };
 		 	  
		 	$.ajax({
		 			data:  parametros,
		 			 url:   '../model/Model-actualiza_vista.php',
		 			type:  'GET' ,
		 				beforeSend: function () { 
		 						$("#GuardaVariables").html('Procesando');
		 				},
		 			success:  function (data) {
		 					 $("#GuardaVariables").html(data);  // $("#cuenta").html(response);
		 				     
		 				} 
		 	});
       
	 
  }   
//------
 function modulo()
 {
  
   	 var modulo1 =  'kreportes';
		 
	 var parametros = {
			    'ViewModulo' : modulo1
    };
	  
	  
	$.ajax({
			data:  parametros,
			 url:   '../model/Model-moduloOpcion.php',
			type:  'GET' ,
				beforeSend: function () { 
						$("#ViewModulo").html('Procesando');
				},
			success:  function (data) {
					 $("#ViewModulo").html(data);  // $("#cuenta").html(response);
				     
				} 
	});
      

 }
//-----------------
 function FormView()
 {
    

	 $("#ViewForm").load('../controller/Controller-configuracion.php');
      

 }
    
  