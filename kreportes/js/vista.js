$(function(){
	
    $(document).bind("contextmenu",function(e){
        return false;
    });
    
 
    
    $('body').on('click', '.list-group .list-group-item1', function () {
        $(this).toggleClass('active');
    });
    $('.list-arrows button').click(function () {
        var $button = $(this), actives = '';
        if ($button.hasClass('move-left')) {
            actives = $('.list-right ul li.active');
            actives.clone().appendTo('.list-left ul');
            actives.remove();
        } else if ($button.hasClass('move-right')) {
            actives = $('.list-left ul li.active');
            actives.clone().appendTo('.list-right ul');
            actives.remove();
        }
    });
    $('.dual-list .selector').click(function () {
        var $checkBox = $(this);
        if (!$checkBox.hasClass('selected')) {
            $checkBox.addClass('selected').closest('.well').find('ul li:not(.active)').addClass('active');
            $checkBox.children('i').removeClass('glyphicon-unchecked').addClass('glyphicon-check');
        } else {
            $checkBox.removeClass('selected').closest('.well').find('ul li.active').removeClass('active');
            $checkBox.children('i').removeClass('glyphicon-check').addClass('glyphicon-unchecked');
        }
    });
    $('[name="SearchDualList"]').keyup(function (e) {
        var code = e.keyCode || e.which;
        if (code == '9') return;
        if (code == '27') $(this).val(null);
        var $rows = $(this).closest('.dual-list').find('.list-group li');
        var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
        $rows.show().filter(function () {
            var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
    });
	
});
 
 
 ///------------------
 function GeneraConsulta( ) {
	 
	 
	    
//	   $('#VistaNombre').val(parameter);
	   $("#etiqueta").val('') ;
	   $("#scolumna").val('') ;
	   $("#sfiltro").val(''); 
	 
	   var phrase = '';
	    
	  //---- ---  ETIQUETA
	   $('#recorre').each(function(){
	   
	       $(this).find('li').each(function(){
	       
	    	   phrase = phrase + $(this).text()+',' ;
	    	   
	       });
	     
	   });
	   n = phrase.length;
	   
	   filtroSQLE =  phrase.substring(0,n - 1);
	   
	   $("#etiqueta").val(filtroSQLE) 
		  
	   //---- ---  FILTRO	  
	   sqlfiltro = '';
	   
	   sqlfiltroetiqueta = '';
	   
	   sqlfiltrotitulo = '';
	   
	   
	   // filtro de lista de valores
 	   $("#VerFiltro select").each(function () 	{
		   
		      cobjeto1 = $(this).attr('id');
			  
		      var array = cobjeto1.split('_');
		      

			  var numero_filas  = array.length;
			  var tipo_variable = numero_filas-1; 


		      var cobjeto 	    = array[0];
		      var tipo_variable = array[tipo_variable];


			  if ( numero_filas == 3){
				cobjeto = cobjeto + '_' + array[1];
			  }
		      
		      valor = $('#'+cobjeto1).val();
			  
			  res = cobjeto.substring(0,1);
 
			  
			  if (valor != '-'){
		    		 
 				  
				   if ( tipo_variable == 'VARCHAR'){
					   sqlfiltro                   = sqlfiltro + cobjeto + ' = ' + "'" + valor +"'"  + ' and ';  
				   }
				   
				   if ( tipo_variable == 'NUMBER'){
					   sqlfiltro                   = sqlfiltro + cobjeto + ' = ' +   valor  + ' and ';  
				   }
		    		 
				   if ( tipo_variable == 'ENTERO'){
					   sqlfiltro                   = sqlfiltro + cobjeto + ' = ' +   valor  + ' and ';  
				   }
				   
				   if ( tipo_variable == 'VARCHAR2'){
					   sqlfiltro                   = sqlfiltro + cobjeto + ' like ' + "'" + valor + "%'" + ' and ';  
					    
				   }
		    		 
		    		 sqlfiltroetiqueta     = sqlfiltroetiqueta + valor + " <br> ";
		    		 sqlfiltrotitulo          = sqlfiltrotitulo + valor + ' - ';
		    	 } 
			  
	   });


 	   // filtro si hay fecha
 	  $("#VerFiltro input").each(function () 	
 	   { 
 	      cobjeto = $(this).attr('id');
		  valor = $('#'+cobjeto).val();
		  res = cobjeto.substring(0,1);
		  filtro_fecha_dato = '';
 		  if (res == '_'){
	    	  if  (valor != ""){ 
	    		
	    		  pf = cobjeto.length; // etiqueta
	    		  f2val  = cobjeto.substring(pf - 2,pf);
	    		  
	    		  if (f2val != '99'){
	    			  fecha1 = cobjeto.substring(1,pf);
	    			  valor1 = valor;
	    		  }
	    		  else{
	    			  valor2 = valor;
	    			  
	    			  //filtro_fecha = "( to_char(" + fecha1 + ",'RRRR-MM-DD') BETWEEN "  + "'" +valor1 +"'" + ' AND ' + "'" + valor2 + "'" + ') and ';
	    			  
	    			  filtro_fecha = "( " + fecha1 + " BETWEEN "  + "'" +valor1 +"'" + ' AND ' + "'" + valor2 + "'" + ') and ';
	    			  
	    			  
	    			 
	    			  var valor11 = valor1.replace(/[-]/g, '/');
	    			  var valor21 = valor2.replace(/[-]/g, '/');
	    			  
	    			  
	    			  filtro_fecha_dato = 'Periodo  ' + fecha1 +  ' :  ' + valor11 + ' al ' + valor21  ;
	      
	    			  
	    			  sqlfiltro = sqlfiltro + filtro_fecha;
	    			  
	    			  sqlfiltrotitulo = sqlfiltrotitulo + filtro_fecha_dato  ;
 	    		  }
  	    	  }
 	      }  
 	  });
 	  
	  n = sqlfiltro.length;
	  
	  filtroSQL =  sqlfiltro.substring(0,n - 5);
	  
	  $("#sfiltro").val(filtroSQL) ;
	  
	  
	 $("#editorfiltro").val(sqlfiltrotitulo) ;
 
 
	  
	  //-------------------------------------------------------------
	     var tipo_agrupar = $('input:radio[name=tipo]:checked').val();
	    var s1 = $('input:radio[name=suma]:checked').val();
	    var c1 = $('input:radio[name=contador]:checked').val();
		  
		tipo_agrupar = 'N';
		c1 = 'N';

	     $("#s1").val('N');
	     $("#c1").val(c1);
 
	   nbandera = 0;
	 	
	     $("#tiposql").val(tipo_agrupar);
	 	  
	     if (tipo_agrupar == 'S'){
	    	 alertify.alert('[ Ok ]... Parametros establecidos para generar información ');
    		 $("#scolumna").val(1);
	     }
	     
	     if (tipo_agrupar == 'N'){
	 	     if (n > 1){
			    		 alertify.alert('[ Ok ]... Parametros establecidos para generar información ');
			    		 $("#scolumna").val(1);	    	 
	          }else
	    		 {
			    		 alertify.alert("Seleccione al menos una columna para su reporte");
			    		 $("#scolumna").val(0);
	    		 }
 	     }
 	  
	       $('#editorFull').val( sqlfiltroetiqueta); 
 
		  
	     
 } 
 
 function replaceAll( text, busca, reemplaza ){
	  while (text.toString().indexOf(busca) != -1)
	      text = text.toString().replace(busca,reemplaza);
	  return text;
}
//-------------
function LLenaDatos(etiqueta,sfiltro,scolumna,editorFull ){
	 
	

	$("#etiqueta").val(etiqueta);
    $("#sfiltro").val(sfiltro);

	$("#tiposql").val('N');
    $("#scolumna").val(scolumna);
	$("#s1").val('N');
    $("#c1").val('N');
	$("#editorFull").val(editorFull);
    $("#editorfiltro").val(editorFull);

	
	
	
	
	
	

}

