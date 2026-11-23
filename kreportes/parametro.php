<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="stylesheet" href="../app/bootstrap/css/bootstrap.min.css">
  
  <script src="../app/js/jquery.min.js"></script>
  
  <script src="../app/bootstrap/js/bootstrap.min.js"></script>
 
 <script language="javascript">
$(document).ready(function() {
    $().ajaxStart(function() {
        $('#loading').show();
        $('#result').hide();
    }).ajaxStop(function() {
        $('#loading').hide();
        $('#result').fadeIn('slow');
    });
    $('#form, #fat, #fo3').submit(function() {
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(data) {
				
                $('#result').html(data);

            }
        })
        
        return false;
    }); 
})  
</script>
<title>GeneradorCodigo</title>
</head>

<body>
	
	
 <ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#home">Inicio</a></li>
    <li><a data-toggle="pill" href="#menu1">View</a></li>
    <li><a data-toggle="pill" href="#menu2">Javascript</a></li>
    <li><a data-toggle="pill" href="#menu3">Controlador</a></li>
	 <li><a data-toggle="pill" href="#menu4">Grilla</a></li>
	 <li><a data-toggle="pill" href="#menu5">Model</a></li>
	 <li><a data-toggle="pill" href="#menu6">Filtro</a></li>
  </ul>
  
	
	 
 <div class="tab-content">
    <div id="home" class="tab-pane fade in active">
     
		<div class="col-md-4">

		  <h2>Generador de codigo</h2>
	 
							  <div class="panel-group" id="accordion1">
								<div class="panel panel-default">
								  <div class="panel-heading">
									<h4 class="panel-title">
									  <a data-toggle="collapse" data-parent="#accordion1" href="#collapse77">Sentencia Select - tabla de datos</a>
									</h4>
								  </div>
								  <div id="collapse77" class="panel-collapse collapse in">
									<div class="panel-body"> 
									<form method="post" action="framekaipi.php" id="fo3" name="fo3" >
								  <h4>Herramienta de ApoyoKaipi</h4>

								  <p>
									  <select name="tipo" id="tipo" class="form-control">

									<option value="XX">TABLA OBJETO MVC CRUD</option>
									 <option value="YY">TABLA OBJETO CONTROLADOR</option>
									<option value="CA">QUERY SQL GRILLA</option>
									 <option value="GG">TABLA GRILLA</option>
									 <option value="AA">ADD ARRAY SQL</option>
									<option value="EE">EDIT ARRAY SQL</option>
									<option value="LI">JAVASCRIPT LIMPIAR</option>

									  </select></p>
								 <p>SentenciaSQL:</p>
								 <p>
								   <input name="otabla" type="text" class="form-control" id="otabla" placeholder="Nombre de tabla a generar">
								 </p>
								 <p>
								   <textarea name="textoedicion" class="form-control" cols="110" rows="8" id="textoedicion"></textarea>
								 </p>

								 <p> <input class="btn btn-success" type="submit" name="submit" id="submit" value="Enviar"></p>


							  <p><select name="archivop" id="archivop" class="form-control">

									<option value="-">[Seleccionar Parametros]</option>
									 <option value="VISTA">Crear Vista</option>
									 <option value="CONTROL">Crear Controlador</option>
									<option value="JAVAS">Crear JavaScript</option>
									 <option value="MODELO">Crear Modelo</option>

								 </select></p>

							</form>
							</div>
								  </div>
								</div>
							  </div> 
				</div>

									  <div class="col-md-8">

										  <div class="panel-group" id="accordion">
										<div class="panel panel-default">
										  <div class="panel-heading">
											<h4 class="panel-title">
											  <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Codigo generado</a>
											</h4>
										  </div>
										  <div id="collapse1" class="panel-collapse collapse in">
											<div class="panel-body">
												 <div class="col-md-12" style="background-color:aliceblue">
													<div id="result" > </div>

												  </div>


											  </div>
										  </div>
										</div>
										<div class="panel panel-default">
										  <div class="panel-heading">
											<h4 class="panel-title">
											  <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">funciones CRUD</a>
											</h4>
										  </div>
										  <div id="collapse2" class="panel-collapse collapse">
											<div class="panel-body">

											   <div class="well" style="font-size: 12px"> 
												 [Array resultado]	 <br>
												 $AResultado = $this->bd->query_array('tabla','campo1, campo2', 'key='.$this->bd->sqlvalue_inyeccion($key,true)); <br> <br>

												 [Array Query resultado]	 <br>
												 $datos = $this->bd->JqueryArrayVisor('wk_proceso',$qquery ); <br> <br>

												 [ Query Insert]	 <br>
												 $id = $this->bd->_InsertSQL($this->tabla,$this->ATabla,$id); <br> <br>

												  [ Query update]	 <br>
													$id = $this->bd->_UpdateSQL($this->tabla,$this->ATabla,$id); <br> <br>

													ADICIONAL <br>
												  [ Query Insert]	 <br>

													  $InsertQuery = array(    <br>
																		array( campo => 'id_producto',   valor => $id  ), <br>
																		array( campo => 'monto',   valor => @$_POST["monto"]), <br>
																		array( campo => 'activo',   valor => @$_POST["activo"]), <br>
																		array( campo => 'detalle',   valor => @$_POST["detalle"]), <br>
																		array( campo => 'principal',   valor => @$_POST["principal"]) <br>
																	); <br>
									  <br>

											  $bd->JqueryInsertSQL('inv_producto_vta',$InsertQuery);  <br>

												 <br>
												 DELETE 
												 <br>
										  $sql = 'delete from par_ciu  where idprov='.$this->bd->sqlvalue_inyeccion($id, true);  <br>
											 $this->bd->ejecutar($sql);  <br>

											</div>	

											</div>
										  </div>
										</div>


										<div class="panel panel-default">
										  <div class="panel-heading">
											<h4 class="panel-title">
											  <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">Objetos</a>
											</h4>
										  </div>
										  <div id="collapse3" class="panel-collapse collapse">
											<div class="panel-body">
											  <div class="well" style="font-size: 11px"> 
											 - retorna mensaje de guardar <br>

											   return $this->bd->resultadoCRUD('ACTUALIZACION',$accion,$id,$tipo); <br><br>

											 - Etiqueta label para separador <br>

											   $this->set->div_label(12,Mensaje);  <br> <br>

											 - lista de valores dinamico enlace tabla con evento <br>

											   $tipo = $this->bd->retorna_tipo();	<br>
											   $resultado = $this->bd->ejecutar("Select codigo, nombre from tabla where condicion" );  <br>

											   $evento =  'onChange="calculoFuente(this.value)"';<br>

											  $this->obj->list->listadbe($resultado,$tipo,'Retencion ','codretair',$datos,'required','',$evento,'div-2-4');  <br> <br>

											 - lista de valores estaticos enlace tabla con evento <br>

											 $MATRIZ = array( <br>
											   '0'    => 'Ahorros', <br>
											   '1'    => 'Corriente' <br>
											 ); <br>

											  $this->obj->list->listae('Estado',$MATRIZ,'estado',$datos,'required','',$evento,'div-2-4');<br><br>

											 -   busquedas de fecha <br>


												 $this->bd->__between('fecha',$fecha1,$fecha2); <br><br>



												 </div>
											  </div>
										  </div>
										</div>

										 <div class="panel panel-default">
										  <div class="panel-heading">
											<h4 class="panel-title">
											  <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">Codigo Jquery</a>
											</h4>
										  </div>
										  <div id="collapse4" class="panel-collapse collapse">
											<div class="panel-body">
												<div class="well" style="font-size: 11px"> 


													 - Javascript llena lista de valores dinamico con valores evento onchange <br><br>

												var parametros = {<br>
												 'Q_IDUNIDAD'  : Q_IDUNIDAD ,<br>
												'Q_IDPERIODO' : Q_IDPERIODO <br>
												}; <br>

													 $.ajax({<br>
														 data:  parametros,<br>
														 url: "../model/Model-ooTareaActividad.php",<br>
														 type: "GET",<br>
														success: function(response)<br>
														{<br>
															$('#IDACTIVIDAD1').html(response);<br>
														}<br>
													 });<br><br>

													 - php llena lista de valores dinamico con valores evento onchange <br><br>

													session_start( );  <br>
													require '../../kconfig/Db.class.php';   <br>
													require '../../kconfig/Obj.conf.php';  <br>
													$obj   = 	new objects;<br>
													$set   = 	new ItemsController;<br>
													$bd	   =	new Db;<br>
													$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);<br>
													$Q_IDUNIDAD  = $_GET['Q_IDUNIDAD'];  <br>
													$Q_IDPERIODO = $_GET['Q_IDPERIODO'];  <br><br>

												  $sql1 = 'SELECT IDACTIVIDAD,ACTIVIDAD <br>
															FROM VIEW_ACTIVIDAD_POA<br>
															where IDUNIDAD = '.$bd->sqlvalue_inyeccion($Q_IDUNIDAD,true).' and<br>
																  IDPERIODO = '.$bd->sqlvalue_inyeccion($Q_IDPERIODO,true);<br>

													$stmt1 = $bd->ejecutar($sql1);<br><br>

													while ($fila=$bd->obtener_fila($stmt1)){ <br><br>

													   <   option  value=" valor codigo "   >  valor etiqueta <    option  > <br><br>

													}<br><br>
												</div>
											  </div>
										  </div>
										</div>	  
 										 <div class="panel panel-default">
										  <div class="panel-heading">
											<h4 class="panel-title">
											  <a data-toggle="collapse" data-parent="#accordion" href="#collapse5">eventos</a>
											</h4>
										  </div>
										  <div id="collapse5" class="panel-collapse collapse">
											<div class="panel-body">
 											  </div>
										  </div>
										</div>
  									  </div> 
 									 </div>
        </div>
    <div id="menu1" class="tab-pane fade">
      <h3>Inicio</h3>
        
			<div class="col-md-12">
				<iframe src="inicio.txt" height="550" width="100%"></iframe>
 			</div>
			 
		 
 
    </div>
    <div id="menu2" class="tab-pane fade">
     	
			<div class="col-md-12">
				<iframe src="java.txt" height="550" width="100%"></iframe>
 			</div>
		
		
    </div>
    <div id="menu3" class="tab-pane fade">
     	   <div class="col-md-12">
				<iframe src="control.txt" height="550" width="100%"></iframe>
 			</div>
    </div>
	 
	<div id="menu4" class="tab-pane fade">
     	   <div class="col-md-12">
				<iframe src="grilla.txt" height="550" width="100%"></iframe>
 			</div>
    </div>
	 
	  <div id="menu5" class="tab-pane fade">
     	   <div class="col-md-12">
				<iframe src="model.txt" height="550" width="100%"></iframe>
 			</div>
    </div>
	 
	   <div id="menu6" class="tab-pane fade">
     	   <div class="col-md-12">
				<iframe src="filtro.txt" height="550" width="100%"></iframe>
 			</div>
    </div>
	 
  </div>
</div>
 

 
 
 
 
</body>
</html>
