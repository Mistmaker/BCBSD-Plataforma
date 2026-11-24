<?php
/**
 * Estado de la sesión para kconfig/sesion.js (JSON).
 *
 *   GET  sesion_estado.php            -> {"activa":true,"restante":530,"minutos":24,"login":"jperez"}
 *                                        (consultar NO renueva la sesión)
 *   POST sesion_estado.php  renovar=1 -> renueva la sesión (botón "Seguir trabajando")
 *
 * Está en la lista blanca de kconfig/auth.php porque debe responder también
 * cuando la sesión ya venció.
 */
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$minutos  = kauth_minutos_sesion();
$activa   = !empty($_SESSION['usuario']);
$ultima   = isset($_SESSION['ultima_actividad']) ? (int) $_SESSION['ultima_actividad'] : time();
$restante = $minutos * 60 - (time() - $ultima);

if ($activa && $restante <= 0) {
    $_SESSION = array(); // venció por inactividad
    $activa   = false;
}

if ($activa && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['renovar'])) {
    $_SESSION['ultima_actividad'] = time();
    $restante = $minutos * 60;
}

echo json_encode(array(
    'activa'   => $activa,
    'restante' => $activa ? max(0, $restante) : 0,
    'minutos'  => $minutos,
    'login'    => $activa && isset($_SESSION['login']) ? trim($_SESSION['login']) : '',
));
