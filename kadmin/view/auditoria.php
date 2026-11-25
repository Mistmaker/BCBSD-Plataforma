<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
/**
 * Auditoría de cambios: quién cambió qué, cuándo y desde dónde.
 *
 *   auditoria.php                          búsqueda general
 *   auditoria.php?grupo=asiento&id=14883   historial de un registro (botón "Historial")
 *
 * Acceso: ver kconfig/auditoria.php (kauditoria_permitida).
 */
require_once __DIR__ . '/../../kconfig/Db.class.php';
require_once __DIR__ . '/../../kconfig/auditoria.php';

$bd = new Db;
$bd->conectar('', '', '');
$permitido = kauditoria_permitida($bd);

$grupos = kauditoria_grupos();
$grupo  = isset($_GET['grupo']) && isset($grupos[$_GET['grupo']]) ? $_GET['grupo'] : '';
$id     = isset($_GET['id']) ? substr(trim((string) $_GET['id']), 0, 60) : '';
$h = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $grupo ? 'Historial - ' . $h($grupos[$grupo]['titulo']) : 'Auditoría de cambios'; ?></title>
<link rel="stylesheet" href="../../app/dist/css/bootstrap.min.css">
<script src="../../app/js/jquery.min.js"></script>
<style>
  body { padding: 16px; font-size: 13px; background: #fff; }
  h3 { margin-top: 0; color: #2b5797; }
  .filtros .form-group { margin-right: 10px; margin-bottom: 8px; }
  .op-I { color: #2e7d32; font-weight: bold; }
  .op-U { color: #1565c0; font-weight: bold; }
  .op-D { color: #c62828; font-weight: bold; }
  table.cambios { margin: 0; background: #fafafa; }
  table.cambios td { padding: 2px 6px !important; border-top: 1px solid #eee !important; }
  .antes { color: #b71c1c; text-decoration: line-through; }
  .nuevo { color: #1b5e20; font-weight: bold; }
  .campo { color: #555; white-space: nowrap; }
  .motivo { background: #fff8e1; padding: 1px 6px; border-radius: 3px; }
  .ip { color: #888; font-size: 11px; }
  #estado { margin: 8px 0; color: #555; }
</style>
</head>
<body>
<?php if (!$permitido) { ?>
  <div class="alert alert-warning">
    <b>No tiene permiso para consultar la auditoría.</b><br>
    Solicite al administrador la opción de menú de auditoría.
  </div>
<?php } else { ?>
  <h3><?php echo $grupo ? 'Historial: ' . $h($grupos[$grupo]['titulo']) . ' ' . $h($id) : 'Auditoría de cambios'; ?></h3>

  <form id="filtros" class="form-inline filtros" onsubmit="buscar(); return false;">
    <input type="hidden" name="grupo" value="<?php echo $h($grupo); ?>">
    <input type="hidden" name="id" value="<?php echo $h($id); ?>">
<?php if (!$grupo) { ?>
    <div class="form-group">
      <label>Tabla</label>
      <select name="tabla" class="form-control input-sm">
        <option value="">(todas)</option>
<?php foreach (kauditoria_tablas() as $t => $nombre) { ?>
        <option value="<?php echo $h($t); ?>"><?php echo $h($nombre); ?></option>
<?php } ?>
      </select>
    </div>
    <div class="form-group"><label>Registro</label>
      <input name="registro" class="form-control input-sm" size="12" placeholder="cédula, n.º asiento..."></div>
<?php } ?>
    <div class="form-group"><label>Usuario</label>
      <input name="usuario" class="form-control input-sm" size="12"></div>
    <div class="form-group"><label>Desde</label>
      <input name="desde" type="date" class="form-control input-sm"></div>
    <div class="form-group"><label>Hasta</label>
      <input name="hasta" type="date" class="form-control input-sm"></div>
    <div class="form-group"><label>Contiene</label>
      <input name="texto" class="form-control input-sm" size="14" placeholder="valor buscado"></div>
    <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
  </form>
  <div id="estado"></div>

  <table class="table table-condensed table-bordered" id="resultado">
    <thead><tr>
      <th style="width:140px">Fecha</th><th style="width:120px">Usuario</th><th style="width:90px">Acción</th>
      <th style="width:220px">Tabla / registro</th><th>Cambios</th>
    </tr></thead>
    <tbody></tbody>
  </table>

<script>
var OPERACION = { I: 'Alta', U: 'Modificación', D: 'Eliminación' };
function esc(s) {
  return s === null || s === undefined ? '' : String(s).replace(/[&<>"']/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
}
function cambios(f) {
  var h = '<table class="table cambios">';
  f.detalle.forEach(function (c) {
    h += '<tr><td class="campo">' + esc(c[0]) + '</td><td>';
    if (f.operacion === 'U') {
      h += '<span class="antes">' + (c[1] === null ? '<i>(vacío)</i>' : esc(c[1])) + '</span> &rarr; '
         + '<span class="nuevo">' + (c[2] === null ? '<i>(vacío)</i>' : esc(c[2])) + '</span>';
    } else {
      h += esc(f.operacion === 'I' ? c[2] : c[1]);
    }
    h += '</td></tr>';
  });
  return h + '</table>';
}
function buscar() {
  $('#estado').text('Buscando...');
  $.ajax({ url: '../model/auditoria_datos.php', type: 'POST', dataType: 'json', data: $('#filtros').serialize() })
    .done(function (d) {
      var tb = $('#resultado tbody').empty();
      if (!d.filas.length) { $('#estado').text('No hay cambios registrados con estos filtros.'); return; }
      d.filas.forEach(function (f) {
        tb.append('<tr><td>' + esc(f.fecha) + '</td>'
          + '<td>' + esc(f.usuario) + '<div class="ip">' + esc(f.ip) + '</div></td>'
          + '<td class="op-' + f.operacion + '">' + OPERACION[f.operacion] + '</td>'
          + '<td>' + esc(f.tabla_nombre) + '<br><b>' + esc(f.registro_id) + '</b>'
          + '<div class="ip" title="' + esc(f.modulo) + '">' + esc((f.modulo || '').split('/').pop()) + '</div></td>'
          + '<td>' + (f.motivo ? '<span class="motivo">Motivo: ' + esc(f.motivo) + '</span>' : '') + cambios(f) + '</td></tr>');
      });
      $('#estado').text(d.filas.length + ' cambio(s)' + (d.limite ? ' (se muestran los 500 más recientes; use los filtros para acotar)' : '') + '.');
    })
    .fail(function (x) {
      var e = x.responseJSON && x.responseJSON.error ? x.responseJSON.error : 'Error al consultar (' + x.status + ').';
      $('#estado').html('<span class="text-danger">' + esc(e) + '</span>');
    });
}
$(buscar);
</script>
<?php } ?>
</body>
</html>
