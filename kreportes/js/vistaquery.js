
function CreaObjTable() {
 
  var temp = new Array();
	  
  var name = 	$("#etiqueta").val() ;
	  
  temp = name.split(",");
 
 	
 	
 	var tabla='	<table id="example" class="display nowrap table table-striped table-bordered" cellspacing="0" width="100%" style="font-size: 11px"><thead><tr>';
 	
	var c1			      =     $("#c1").val(); 
	var tiposql			  =     $("#tiposql").val(); 
	var ncontador         = 1;
	
  	for (a in temp ) {
	
 		tabla = tabla + '<th>' + temp[a] +'</th>';	
 		
 		ncontador = ncontador + 1;
 
  	}
  	
  	ncontador  = ncontador  - 1;
  	
	 $("#ncolumna").val(ncontador) ;
  	
 	if (c1 == 'S'){
 			tabla = tabla + '<th>' +'Numero'+'</th>';	
 	}
 	
	 tabla = tabla + '</tr></thead> </table>';
	 
	 $("#TablaDinamica").html(tabla) ;
 
 }
  
//--------------------

function DrawObjTable( ) {

	var VistaNombre	 =    $("#VistaNombre").val(); 
	var sfiltro				 =     $("#sfiltro").val(); 
	var etiqueta			 =     $("#etiqueta").val(); 
	var tiposql			     =     $("#tiposql").val(); 
	var titulo			     =     $("#titulo").val(); 
	
 
	var s1			     =     $("#s1").val(); 
	var c1			     =     $("#c1").val(); 
 
	  
 var parametros = {
				"VistaNombre" : VistaNombre ,
				"tiposql" : tiposql ,
				"sfiltro" : sfiltro ,
				"etiqueta": etiqueta,
				"s1" : s1 ,
				"c1": c1
   };
 
 
 var table =  $('#example').DataTable( 
		 {
	       dom: 'Bfrtip',
	        buttons: [
	            'colvis',
	            'copyHtml5',
	        	'excelHtml5',
	            'csvHtml5',
	            {
	                extend: 'pdfHtml5',
	                orientation: 'landscape',
	                pageSize: 'LEGAL'
	            },
	            {
	                extend: 'print',
	                customize: function ( win ) {
	                    $(win.document.body)
	                        .css( 'font-size', '10pt' )
	                        .prepend( 	'<h3>Gestión Ireports</h3>' );
	 
	                    $(win.document.body).find( 'table' )
	                        .addClass( 'compact' )
	                        .css( 'font-size', 'inherit' );
	                }
	            },
	            {
	                text: 'Cerrar',
	                action: function ( e, dt, node, config ) {
	                	window.close() ;
	                }
	            }
	        ],
      	   processing : true,
 		   ordering: true,
    	   searching: false,
			 ajax  : {
				 data:  parametros,
				 url : "../controller/VistaSql.php",
			     type:  'GET' ,
			     dataType : "json"
			},
			"scrollX": true,
			"scrollY": "380px",
	        "paging": false
 		} );
  
 
    table.buttons().container()
   .appendTo( $('div.eight.column:eq(0)', table.table().container()) );
	 
 }
//-------------------------
function IniObj(Nombre,e1,f1,v1,tipo,s1,c1,titulo) {
	 
	   $("#etiqueta").val(e1) ;
	   
 	   $("#sfiltro").val(f1); 
	
 	   $("#VistaNombre").val(v1); 
 	
 	   $("#tiposql").val(tipo); 
 	  
	   $("#s1").val(s1); 
	 	
	  $("#c1").val(c1); 
	  
	  $("#titulo").val(titulo); 
 
 	  $("#datoVista").html('<b>'+ Nombre  +'</b>') ;
 
 }
//-------------------------
function graficoBarras() {
	 
	
	var enlace = "../view/visorG.php";
	
	 $("#grafico").load(enlace);
 
	
}
function imprimir(id1) {
	
	var tipo =  $("#tiposql").val(); 
	
	var imp = 'example';
	
	var ficha = document.getElementById(imp);
	
 	var cab = tinyMCE.get('cabecera').getContent()//  $('#cabecera').text();
	 
	 var ventimp = window.open(' ', '_blank');
	 	
 	if (tipo == 'S') {
 	   var grafico = document.getElementById('grafico1');
 	   var htmlcod =  cab + ficha.outerHTML +  grafico.outerHTML;
 	}
 	else{
 	   var htmlcod =  cab + ficha.outerHTML ;
 	}
 	 

    
  ventimp.document.close();
	  
	 var css = ventimp.document.createElement("link");
	  
	css.setAttribute("href", "//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.css");
	css.setAttribute("rel", "stylesheet");
	css.setAttribute("type", "text/css");
	
	ventimp.document.head.appendChild(css);
	
	 ventimp.document.write(htmlcod);
	
	 ventimp.print( );
	
	 ventimp.close(); 
 
 
}

 
//-------------------------
function graficoPastel() {
	 
	
	var enlace = "../view/visorP.php";
	
   /* posicion_x=(screen.width/2)-(1024/2); 
    posicion_y=(screen.height/2)-(500/2); 
    
	 window.open(enlace,'#','width=1024,height=500,left='+posicion_x+',top='+posicion_y+'');
	 */
	 
	 $("#grafico").load(enlace);
	 
	 
	
}
//-------------------------
function graficoLinea() {
	 
	
	var enlace = "../view/visorL.php";
 	
	 $("#grafico").load(enlace);
}
 
//-------------------------
function SinAgruparTable() {
	 
	
	$.ajax({
 		url:   '../controller/VisorAnalisis.php',
		type:  'GET' ,
		 beforeSend: function () { 
				$("#analisisDB").html('Creando Analisis de datos');
		},
		success: function(data) {
				 $("#analisisDB").html(data); 
     },
	  error: function() {
		 alert( "Ha ocurrido un error" );
		 
	  }
}); 
	 
	 
}
  //-----
  function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}