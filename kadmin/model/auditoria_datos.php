<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión
/**
 * Datos de la pantalla de auditoría (kadmin/view/auditoria.php). Responde JSON.
 */
require_once __DIR__ . '/../../kconfig/Db.class.php';
require_once __DIR__ . '/../../kconfig/auditoria.php';

header('Content-Type: application/json; charset=utf-8');

$bd = new Db;
$bd->conectar('', '', '');

$responder = function ($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

if (!kauditoria_permitida($bd)) {
    $responder(array('error' => 'No tiene permiso para consultar la auditoría.'), 403);
}
if (!kauditoria_instalada($bd)) {
    $responder(array('error' => 'La auditoría no está instalada en la base de datos o el usuario de la aplicación no puede leerla (ver sql/2026-11_auditoria.sql).'), 500);
}

$filtros = array();
foreach (array('grupo', 'id', 'tabla', 'registro', 'usuario', 'desde', 'hasta', 'texto') as $k) {
    if (isset($_POST[$k]) && is_string($_POST[$k])) {
        $filtros[$k] = substr($_POST[$k], 0, 200);
    }
}
$hay_filtro = false;
foreach ($filtros as $v) {
    if (trim($v) !== '') {
        $hay_filtro = true;
    }
}
if (!$hay_filtro) {
    // sin filtros: últimos 7 días
    $filtros['desde'] = date('Y-m-d', strtotime('-7 days'));
}

$r = kauditoria_buscar($bd, $filtros, 500);
if ($r === false) {
    $responder(array('error' => 'No se pudo consultar la auditoría.'), 500);
}
$responder($r);
