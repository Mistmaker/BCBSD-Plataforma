<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />

	<title>Plataforma de Gestión Empresarial</title>

	<?php require('Head.php')  ?>

	<script>
		var parametros = {
			'tipo': 1
		};


		$.ajax({
			data: parametros,
			url: "../model/ajax_bienes_cuenta_uni.php",
			type: "GET",
			success: function(response) {

				$('#cuenta').html(response);

			}
		});
	</script>
</head>

<body>
	<div class="well">

		<div class="modal-content">

			<div class="modal-body">

				<div class="list-group">

					<form action="admin_clase" method="post" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8">

						<div style="padding-top: 5px;" class="col-md-12">
							<h4>Crear Clase</h4>
						</div>


						<label style="padding-top: 5px;text-align: right;" class="col-md-2">DETALLE DEL BIEN</label>
						<div style="padding-top: 5px;" class="col-md-4">
							<input name="nombre" type="text" required="required" class="form-control required" id="nombre">
						</div>

						<label style="padding-top: 5px;text-align: right;" class="col-md-2">CLASE </label>
						<div style="padding-top: 5px;" class="col-md-4">
							<input name="clase" type="text" required="required" placeholder="MUEBLES DE OFICINA" class="form-control required" id="clase">
						</div>

						<label style="padding-top: 5px;text-align: right;" class="col-md-2">Cuenta</label>

						<div style="padding-top: 5px;" class="col-md-4">

							<select name="cuenta" required="" id="cuenta" class="form-control">

								<option value="1.1.03.02.01.01.0">1.1.03.02.01.01.0.Equipos, Sistemas y Paquetes Informáticos</option>
								<option value="1.1.03.02.02.01.0">1.1.03.02.02.01.0.Muebles y Equipos</option>
								<option value="1.1.03.02.03.01.0">1.1.03.02.03.01.0.Vehículos</option>

							</select>
						</div>

						<label style="padding-top: 5px;text-align: right;" class="col-md-2">ITEM </label>
						<div style="padding-top: 5px;" class="col-md-4">
							<input name="item" type="text" required="required" placeholder="Ej.: 530104" class="form-control required" id="item">
						</div>

						<label style="padding-top: 5px;text-align: right;" class="col-md-2">IDENTIFICADOR (revisar catalogo) </label>
						<div style="padding-top: 5px;" class="col-md-4">
							<input name="identificador" type="text" required="required" placeholder="Ej.: 300100010001" class="form-control required" id="identificador">
						</div>

						

						<input type="hidden" id="action" value="add" name="action">

						<div style="padding-top: 5px;" class="col-md-12">

							<button type="submit" class="btn btn-success btn-sm">
								<span class="glyphicon glyphicon-floppy-saved"></span> Guardar </button>

							<button type="button" onClick="window.close();" class="btn btn-primary btn-sm">
								<span class="glyphicon glyphicon-log-out"></span> Cancelar </button>

						</div>

					</form>

				</div>
			</div>
		</div>
	</div>
</body>

</html>
<?php



if (isset($_POST['action'])) {
	$inserta = $_POST['action'];
	if ($inserta == 'add') {
		require 'SesionInicio.php';
		Agregar($obj, $bd, $set);
	}
}

function Agregar($obj, $bd, $set)
{



	$sql1 = 'SELECT last_value  as nn from activo.ac_bienes_id_bien_seq ';
	$stmt1 = $bd->ejecutar($sql1);


	while ($fila = $bd->obtener_fila($stmt1)) {

		$secuencia = $fila['nn'] + 1;
	}


	$xx     = trim(strtoupper($_POST["nombre"]));
	$cuenta = trim(strtoupper($_POST["cuenta"]));
	$clase  = trim(strtoupper($_POST["clase"]));
	$item  = trim(strtoupper($_POST["item"]));
	$identificador  = trim(strtoupper($_POST["identificador"]));

	// $identificador = '499999' .  $secuencia;

	$lon = strlen($xx);

	$InsertQuery = array(
		array('campo' => 'item',   	'valor' => $item),
		array('campo' => 'identificador',   	'valor' => $identificador),
		array('campo' => 'detalle',   	'valor' => $xx),
		array('campo' => 'cuenta',   	'valor' => $cuenta),
		array('campo' => 'clase',   	'valor' => $clase)
	);

	if ($lon > 3) {

		$bd->JqueryInsertSQL('activo.ac_matriz_esigef', $InsertQuery);


		$obj->var->kaipi_cierre_pop();
	} else {

		echo 'VERIFIQUE LA INFORMACION';
	}
}

function DivCategoria($div, $url)
{

	echo '<script type="text/javascript">';
	echo "  opener.$('" . $div . "').load('" . $url . "');   ";
	echo '</script>';
}

?>