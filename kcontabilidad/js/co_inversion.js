 
$(document).ready(function(){
     
      
        
 
    
        
        modulo(); 

		$("#MHeader").load('../view/View-HeaderModel.php');

	    $("#FormPie").load('../view/View-pie.php');
  		
	    
		

	    $('#load').on('click',function(){
 
            BusquedaGrilla();
 
		});

	     
	    
 
		
        var fecha = fecha_hoy();

        $("#fechai").val(fecha);
        
        $("#detalle").val('Contabilización de Inversiones de Desarrollo Social Bienes/Servicios/Remuneraciones correspondiente a	');
        
 
})

 
function changeAction(tipo,action,mensaje){

	 

			if (tipo =="confirmar"){			 

			

			  alertify.confirm("<p>"+mensaje+"<br><br></p>", function (e) {

			  if (e) {
 
			  		//$('#mytabs a[href="#tab2"]').tab('show');

                	

				  	LimpiarDatos();

				  

                    LimpiarPantalla();

 

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

 

    return today;

            

} 

 

  //------------------------------------------------------------------------- 

  function BusquedaGrilla(){        	 

	  
	  

	  var cmes = $("#cmes").val();
 	  var anio = $("#anio").val();

  

 

      var parametros = {
 				'mes' : cmes  ,
 				'anio' : anio   ,
                'accion': 'visor'
       };
      
       $.ajax({
        data:  parametros,
        url:   '../model/Model_ajax_inversion',
        type:  'GET' ,
        cache: false,
        beforeSend: function () { 

                    $("#Viewlibro").html('Procesando');

            },

        success:  function (data) {

                 $("#Viewlibro").html(data); 

                 

            } 

});
 
 

  }   
 //-----------
 function Marca_Datos(estado_seleccion){        	 

	  
	var anio    = $("#anio").val();
	var prove   =  $("#prove").val();
	var cuenta  =  $("#cuenta").val();
	var bandera =  $("#bandera").val();  
	var cmes =  $("#cmes").val();  

	var marca =  estado_seleccion;  
	
 
	var parametros = {
			   'anio' : anio  ,
			   'cuenta' : cuenta   ,
			   'prove' : prove  ,
			  'bandera' : bandera   ,
			  'cmes' : cmes,
			  'marca': marca
	 };
	
	
	$.ajax({
		  data:  parametros,
		  url:   '../model/Model_listaAuxqSRI_marca.php',
		  type:  'GET' ,
		  beforeSend: function () { 

				  $("#ViewFormAux").html('Procesando');

		  },
		  success:  function (data) {
			   $("#ViewFormAux").html(data);   

		  } 
  });	 



}    
   

//-----SumaTotal  
function SumaTotal(total,objeto)
 {
 
	var total_dato =  $("#parcial").val();

	var base    = parseFloat(total_dato)  ;
	var parcial = parseFloat(total) ;

	var total = 0;

	if (objeto.checked == true){
 		estado = 'S'

		 total = base + parcial;

		 var base1    = parseFloat(total).toFixed(2)  ;

		 $("#parcial").val(base1);

	} else {

		estado = 'N'

		total = base - parcial;

		var base1    = parseFloat(total).toFixed(2)  ;

		$("#parcial").val(base1);

	}

	$("#ViewParcial").html('<h4><b>' + base1 + '</b><h4>');

 }  
//------------------------------------------------------------------------- 
function myFunction(proveedor,codigo,objeto)

 {
 
	   var estado 		  =  '';

 


	    if (objeto.checked == true){

	    	estado = 'S'

	    } else {

	    	estado = 'N'

	    }

	    

	    var parametros = {
  
                'idaux' : codigo ,
				'estado':estado ,
				'proveedor' : proveedor,
                'bandera': 'S'

	  };

 
	    	 

		      $.ajax({
 						data:  parametros,
 						url:   '../model/Model-co_lista_sri.php',
 						type:  'GET' ,
 						cache: false,
 						beforeSend: function () { 
 									$("#ViewData").html('Procesando');
 							},
 						success:  function (data) {
 								 $("#ViewData").html(data);   
 
							} 
 				}); 

				//---------------
		/*		var parametros = {
					'idprov' : proveedor,
					'id_asiento' : codigo ,
					'id_asientod':codigo,
 				};

				$.ajax({
				data:  parametros,
				url:   '../model/ajax_PagoAuxParcial1.php',
				type:  'GET' ,
				cache: false,
			
				success:  function (data) {

				$("#ViewData").html(data);   

				} 

				});
		 */
 	

 }
//------------------------------------------------
function modulo()

 {

 	 var modulo1 =  'kcontabilidad';

 	 var parametros = {

			    'ViewModulo' : modulo1 

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

 }

//----------------- 

 
//----------------------

 function accion(id,modo,estado)

 {

  

	if (id > 0){

		 

  			 if (modo == 'aprobado'){

					$("#action").val('aprobado');

					$("#estado").val('aprobado');          

					$("#comprobante").val(estado);       

			 }else{

					$("#action").val(modo);

					$("#estado").val(estado);          

		 	 }

			 		

			  $("#id_asiento").val(id);

		 			 

		 			 

			 

		 		//		 BusquedaGrilla(oTable);

	 

	}



 }

 



//------------------------------------------------------------------------- 

 function ViewDetAuxiliar(codigoAux)

 {

   

 	 var parametros = {

			    'codigoAux' : codigoAux 

    };

 	 

 	$.ajax({

			data:  parametros,

			 url:   '../controller/Controller-co_asientos_aux01.php',

			type:  'GET' ,

			cache: false,

			beforeSend: function () { 

						$("#ViewFiltroAux").html('Procesando');

				},

			success:  function (data) {

					 $("#ViewFiltroAux").html(data);  // $("#cuenta").html(response);

				     

				} 

	});

 

 }

//----------------------------------------------
 

function openFile(url,ancho,alto) {

	   var posicion_x; 
       var posicion_y; 
       var enlace; 
 

  posicion_x=(screen.width/2)-(ancho/2); 

  posicion_y=(screen.height/2)-(alto/2); 
 
  enlace = url  ;
 
  window.open(enlace, '#','width='+ancho+',height='+alto+',toolbar=0,scrollbars=no,resizable=no,left='+posicion_x+',top='+posicion_y+'');

}

//--------------------

function goToURLNovedad( id , nombre) {

	  var anio = $("#anio").val();

	  $("#prove").val( id );
	  
	  $("#ViewProveedor").html( nombre );
	  
	 
	  
 	   var parametros = {
	 						    'idprov' : id  ,
	 						    'anio' : anio
	  	};
 
		$.ajax({
			data: parametros,
			url: "../model/Model_aux_resumen_cta.php",
			type: "GET",
			success: function(response)
			{
					$('#cuenta').html(response);
			}
		});
 	   
 
		
 	  $('#mytabs a[href="#tab2"]').tab('show');
}

//-
function TotalSeleccion( ) {

	  var anio = $("#anio").val();

 	 var id =  $("#prove").val(  );
 	 
	  
 	   var parametros = {
								  'anio' : anio,
								   'idprov' : id  
	  	};
 
	 $.ajax({
 			data:  parametros,
 			 url:   '../model/ajax_SeleccionPagoAux.php',
 			type:  'GET' ,
 			cache: false,
 			beforeSend: function () { 
 						$("#ViewFormTotal").html('Procesando');

				},
 			success:  function (data) {

					 $("#ViewFormTotal").html(data);  // $("#cuenta").html(response);
 
				} 

	});


}
//---- 
//----
function VerAsiento( did_asiento, did_asientod, dhaber,cuenta) {

	$("#d_id_asiento").val( did_asiento );
	$("#d_id_asientod").val( did_asientod );
	$("#d_cuenta").val( cuenta );
	$("#d_haber").val( dhaber  );
	$("#d_parcial").val( '0.00' );
	 
 
	
}
//---------------
function CalculaSaldo(id_asiento,id_asientod,idprov,valor) {

	
	var o_saldo   =  '#h_'+id_asientod;
	var o_parcial =  '#s_'+id_asientod;

	var parcial  =  '#p_'+id_asientod;

	var saldo_original   = $(o_saldo).val();
	var parcial_original = $(o_parcial).val();
	

	 
	var apagar_parcial    = parseFloat(valor)  ;

	var saldo = parseFloat(saldo_original)  - parseFloat(parcial_original);

	if ( apagar_parcial > saldo ){
		$("#ViewData").html('El valor excede: ' + saldo);
		$(parcial).val(saldo_original);
	}else{
		
				 var parametros = {
								'idprov' : idprov,
								'id_asiento' : id_asiento ,
								'id_asientod':id_asientod,
								'valor':valor,
								'saldo_original':saldo_original,
								'parcial_original':parcial_original


					};

					$.ajax({
					data:  parametros,
					url:   '../model/ajax_PagoAuxParcial.php',
					type:  'GET' ,
					cache: false,
				 
					success:  function (data) {

					$("#ViewData").html(data);   

					} 

					});

	}

	
	
}
//----
function CXPContabilizar( ) {
	
	 var d_id_asiento       =  $("#d_id_asiento").val();
 	 var d_id_asientod 	    =  $("#d_id_asientod").val(  );
 	 var d_cuenta   =  $("#d_cuenta").val(  );
 	 var d_haber    =  $("#d_haber").val(  );
	 var d_parcial       =  $("#d_parcial").val(  );

	  
 	   var parametros = {
								   'd_id_asiento' : d_id_asiento,
								   'd_id_asientod' : d_id_asientod  ,
								   'd_cuenta': d_cuenta,
								   'd_haber':d_haber,
								   'd_parcial' : d_parcial
	  	};
 
       if ( d_haber )
		 	alertify.confirm("<p>Desea generar la contabilizacion? </p>", function (e) {
 					if (e) {
 						$.ajax({
 								data:  parametros,
 								url:   '../model/Model_PagoCxP_Externo01.php',
 								type:  'POST' ,
 								cache: false,
 								beforeSend: function () { 
 											$("#ViewGuarda").html('Procesando');

									},
 								success:  function (data) {
 										$("#ViewGuarda").html(data);  // $("#cuenta").html(response);
 										BusquedaGrillaa();
 									} 
 						});
 			    }
 			 }); 
		 	
		 	
}
function Contabilizar( ) {

	 var anio       = $("#anio").val();
 	 var mes 	    =  $("#cmes").val(  );
  	 var fecha      =  $("#fechai").val(  );
	 var detalle       =  $("#detalle").val(  );
     var accion       =  'contabilizar_inversion'

	  
 	   var parametros = {
								   'anio' : anio,
								   'mes' : mes  ,
								   'fecha': fecha,
								   'detalle':detalle,
								   'accion' : accion
	  	};
 

		 	alertify.confirm("<p>Desea generar la contabilizacion? </p>", function (e) {

				 if (e) {
					
						$.ajax({

								data:  parametros,

								url:   '../model/Model_ajax_inversion.php',

								type:  'GET' ,

								cache: false,

								beforeSend: function () { 

											$("#ViewFormTotal").html('Procesando');

									},

								success:  function (data) {

                                    $("#ViewFormTotal").html(data);
									   alert(data);

								 
									} 

						});
 
			  }

			 }); 
 
}