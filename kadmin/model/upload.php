<?php
/**
 * Subida del certificado de firma electrónica (.p12) del usuario
 * desde su perfil (kadmin/view/View-panel.php).
 *
 * Responde el nombre del archivo guardado (lo usa EnvioUserEmail.php).
 */
session_start();

if (empty($_SESSION['usuario'])) {
    http_response_code(401);
    exit('Sesión no válida');
}

$TAMANO_MAXIMO = 1048576; // 1 MB: un .p12 normal pesa unos pocos KB

if (!isset($_FILES['userfile']) || !is_array($_FILES['userfile']) ||
    $_FILES['userfile']['error'] !== UPLOAD_ERR_OK ||
    !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    http_response_code(400);
    exit('No se recibió el archivo');
}

if ($_FILES['userfile']['size'] <= 0 || $_FILES['userfile']['size'] > $TAMANO_MAXIMO) {
    http_response_code(400);
    exit('Tamaño de archivo no permitido');
}

// Solo certificados .p12; nombre limitado a caracteres seguros (sin rutas)
$original = basename(trim($_FILES['userfile']['name']));
$base     = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
$ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));

if ($ext !== 'p12' || $base === '' || $base === null) {
    http_response_code(400);
    exit('Solo se permiten certificados .p12');
}

$archivo = substr($base, 0, 80) . '.p12';
$folder  = __DIR__ . '/../../kdocumento/reportes/';

if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $folder . $archivo)) {
    error_log('[upload.php] No se pudo guardar ' . $archivo);
    http_response_code(500);
    exit('Error al guardar el archivo');
}

@chmod($folder . $archivo, 0640);

echo $archivo;
