<?php
require_once __DIR__ . '/auth.php'; // exige sesión
/**
 * Convierte a .xlsx real la tabla que una pantalla quiere exportar.
 *
 * Las pantallas exportaban con window.open('data:application/vnd.ms-excel,' + html)
 * o con un enlace data:...: el navegador descargaba la tabla HTML con extensión
 * .xls (Excel la abre como "página web"). kconfig/sesion.js intercepta esas
 * descargas y envía aquí el HTML; se responde con el .xlsx (kconfig/excel.php).
 *
 * Solo convierte el HTML recibido: no consulta la base de datos.
 */
require_once __DIR__ . '/excel.php';

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['html'])) {
    http_response_code(400);
    exit('Solicitud no válida.');
}
$html   = (string) $_POST['html'];
$nombre = isset($_POST['nombre']) ? (string) $_POST['nombre'] : 'exportacion';

if (stripos($html, '<table') === false) {
    // contenido sin tabla: una sola celda por línea de texto
    $html = '<table>' . implode('', array_map(function ($l) {
        return '<tr><td>' . htmlspecialchars(trim($l), ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }, preg_split('/\r?\n/', trim(strip_tags($html))))) . '</table>';
}
kexcel_enviar_html($html, $nombre);
