 function GraficoDetalle(ccolumna)  {
	  
 

     $("#filtro_analisis").val(ccolumna)     ;

    /*
Graficolinea( ccolumna );
 
Graficopie( ccolumna ) 

Graficobarra( ccolumna ) 
*/

}
 
 function AgrupaInformacion()  {
	  
	 GraficobarraResumen(  );
	 
	 GraficoResumenpie(   ) ;
	 
	 GraficolineaResumen();
}
//--------------------------------------- 
 
 function Graficolinea(   )  {
	 

    var ccolumna =    $("#filtro_analisis").val()     ;


	 var options = {
             chart: {
                 renderTo: 'div_linea',
                 type: 'line'
             },
             title: {
                 text: ' ',
                 x: -20 //center
             },
        /*     subtitle: {
                 text: 'Source: WorldClimate.com',
                 x: -20
             },*/
             xAxis: {
                 categories: [],
                 title: {
                     text: 'Referencia'
                 }
             },
             yAxis: {
                 title: {
                     text: 'Nro'
                 },
                 plotLines: [{
                         value: 0,
                         width: 1,
                         color: '#808080'
                     }]
             },
             
             legend: {
                 layout: 'vertical',
                 align: 'right',
                 verticalAlign: 'middle',
                 borderWidth: 0
             },
             credits: {
                 enabled: false
             },
             series: []
         };
	 
	 
      $.getJSON("../controller/Linea_sinagrupar.php?tipo=1&columna=" + ccolumna, function(json) {
         options.xAxis.categories = json[0]['data']; //xAxis: {categories: []}
         options.series[0] = json[1];
         chart = new Highcharts.Chart(options);
     });
 
 }
 //------------------------------------------------------------------------
 function Graficopie(  )  {


 var ccolumna =    $("#filtro_analisis").val()     ;
	 
	 var options = {
             chart: {
                 renderTo: 'div_linea',
                 plotBackgroundColor: null,
                 plotBorderWidth: null,
                 plotShadow: false,
                 type: 'pie'
             },
             title: {
                 text: ' '
             },
             tooltip: {
                 pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
              },
             plotOptions: {
                 pie: {
                	  allowPointSelect: true,
                      cursor: 'pointer',
                      dataLabels: {
                          enabled: true
                      },
                      showInLegend: true
                 }
             },
             credits: {
                 enabled: false
             },
             series: []
         };

	 
	 
	 $.getJSON("../controller/Linea_sinagrupar.php?tipo=2&columna=" + ccolumna, function(json) {
             options.series = json;
             chart = new Highcharts.Chart(options);
         });
	 
 
 }
 //----------------------------------------------------
function Graficobarra(   )  {
	 
    var ccolumna =    $("#filtro_analisis").val()     ;

	var options = {
            chart: {
                renderTo: 'div_linea',
                type: 'bar'
            },
            title: {
                text: '  ',
                x: -20 //center
            },
            subtitle: {
                text: '  ',
                x: -20
            },
            xAxis: {
                categories: []
            },
            yAxis: {
                title: {
                    text: ' '
                },
                plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>:<b>{point.y}</b> of total<br/>'
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '{point.y}'
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: []
        };
	
	   $.getJSON("../controller/Linea_sinagrupar.php?tipo=3&columna=" + ccolumna, function(json) {
            options.xAxis.categories = json[0]['data']; //xAxis: {categories: []}
            options.series[0] = json[1];
            chart = new Highcharts.Chart(options);
        });
	 
	  
 
 }

//---------------------------------------------
//----------------------------------------------------
function GraficobarraResumen(  )  {
	 
	 var ncolumna = $("#ncolumna").val()     ;
 
	
	var options = {
            chart: {
                renderTo: 'div_barra',
                type: 'bar'
            },
            title: {
                text: '  ',
                x: -20 //center
            },
            subtitle: {
                text: '  ',
                x: -20
            },
            xAxis: {
                categories: []
            },
            yAxis: {
                title: {
                    text: ' '
                },
                plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>:<b>{point.y}</b> of total<br/>'
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '{point.y}'
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 50,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: false
            },
            credits: {
                enabled: false
            },
            series: []
        };
		/* 	  
 	   options.series[5] = json[6];  
*/
	if ( ncolumna == '2'){
		  $.getJSON("../controller/VistaSqlBarra.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
 			   options.series[0] = json[1];
 	          chart = new Highcharts.Chart(options);
       });
	}
	
	if ( ncolumna == '3'){
		  $.getJSON("../controller/VistaSqlBarra.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
	          chart = new Highcharts.Chart(options);
     });
	}	
	if ( ncolumna == '4'){
		  $.getJSON("../controller/VistaSqlBarra.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
	          chart = new Highcharts.Chart(options);
   });
	}	
	if ( ncolumna == '5'){
		  $.getJSON("../controller/VistaSqlBarra.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
			   options.series[3] = json[4];
	          chart = new Highcharts.Chart(options);
 });
	}		 
	if ( ncolumna == '6'){
		  $.getJSON("../controller/VistaSqlBarra.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
			   options.series[3] = json[4];
			   options.series[4] = json[5];
	          chart = new Highcharts.Chart(options);
});
	}		
 
 }
//-------------------
// ir a la opcion de editar
function GrabarSesion() {
 
 
    var parametros = {
                   'accion' : 'editar' 
      };


      if (confirm("Guarda resultado de la búsqueda?...") == true) {

                    $.ajax({
                        data:  parametros,
                        url:   '../model/Model_guarda_sesion.php',
                        type:  'GET' ,
                        beforeSend: function () { 
                                $("#result").html('Procesando');
                        },
                        success:  function (data) {
                                $("#result").html(data);  
                                
                        } 
                }); 
                    
      }


   

   }
 

//------------------------------------------------------------------------
function GraficoResumenpie(   )  {
	 
	 var options = {
            chart: {
                renderTo: 'div_pie',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: ' '
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
             },
            plotOptions: {
                pie: {
               	  allowPointSelect: true,
                     cursor: 'pointer',
                     dataLabels: {
                         enabled: false
                     },
                     showInLegend: true
                }
            },
            credits: {
                enabled: false
            },
            series: []
            
        };

	  $.getJSON("../controller/VistaSqlPastel.php", function(json) {
            options.series = json;
            chart = new Highcharts.Chart(options);
        });
}



///---------------
function GraficolineaResumen(  )  {
	 
	 var ncolumna = $("#ncolumna").val()     ;

	
	 var options = {
             chart: {
                 renderTo: 'div_linea',
                 type: 'line'
             },
             title: {
                 text: ' ',
                 x: -20 //center
             },
        /*     subtitle: {
                 text: 'Source: WorldClimate.com',
                 x: -20
             },*/
             xAxis: {
                 categories: [],
                 title: {
                     text: 'Referencia'
                 }
             },
             yAxis: {
                 title: {
                     text: 'Nro'
                 },
                 plotLines: [{
                         value: 0,
                         width: 1,
                         color: '#808080'
                     }]
             },
             
             legend: {
                 layout: 'vertical',
                 align: 'right',
                 verticalAlign: 'middle',
                 borderWidth: 0
             },
             credits: {
                 enabled: false
             },
             series: []
         };
		/* 	  
	   options.series[5] = json[6];  
*/
	if ( ncolumna == '2'){
		  $.getJSON("../controller/VistaSqlLinea.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
	          chart = new Highcharts.Chart(options);
      });
	}
	
	if ( ncolumna == '3'){
		  $.getJSON("../controller/VistaSqlLinea.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
	          chart = new Highcharts.Chart(options);
    });
	}	
	if ( ncolumna == '4'){
		  $.getJSON("../controller/VistaSqlLinea.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
	          chart = new Highcharts.Chart(options);
  });
	}	
	if ( ncolumna == '5'){
		  $.getJSON("../controller/VistaSqlLinea.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
			   options.series[3] = json[4];
	          chart = new Highcharts.Chart(options);
});
	}		 
	if ( ncolumna == '6'){
		  $.getJSON("../controller/VistaSqlLinea.php", function(json) {
		       options.xAxis.categories = json[0]['data'];
			   options.series[0] = json[1];
			   options.series[1] = json[2];
			   options.series[2] = json[3];
			   options.series[3] = json[4];
			   options.series[4] = json[5];
	          chart = new Highcharts.Chart(options);
});
	}		

}