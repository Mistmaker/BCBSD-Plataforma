<?php
/**
 * PLANTILLA de configuración.
 *
 * 1. Copiar este archivo a kconfig/config.local.php (está en .gitignore).
 * 2. Completar los valores reales.
 * 3. Restringir permisos: chmod 640 config.local.php (dueño root, grupo del servidor web).
 *
 * NUNCA versionar config.local.php ni escribir credenciales en otros archivos.
 * Las conexiones que no se usen en esta instalación pueden eliminarse.
 */
return array(

    'seguridad' => array(
        // 'bloquear'  : exige sesión en todo script que use la base de datos (recomendado).
        // 'registrar' : no bloquea; solo escribe "[auth] SIN SESION" en el log del servidor.
        //               Útil 1-3 días tras desplegar para detectar accesos públicos legítimos
        //               que falten en kconfig/auth.php (kauth_rutas_publicas).
        'auth' => 'bloquear',
        // Minutos sin actividad tras los cuales se cierra la sesión. Unos minutos
        // antes se avisa al usuario (kconfig/sesion.js). Si se omite, se usa
        // session.gc_maxlifetime de php.ini (24 min por defecto).
        'sesion_minutos' => 30,
        // Límite de intentos de login: tras N claves incorrectas se bloquea el
        // ingreso por unos minutos. El límite por IP (0 = desactivado) solo
        // conviene si cada usuario llega con su propia IP (sin proxy ni NAT común).
        'login_intentos'        => 5,
        'login_intentos_ip'     => 0,
        'login_bloqueo_minutos' => 15,
        // CSRF (operaciones enviadas desde otro sitio con la sesión del usuario):
        // 'bloquear' (por defecto) o 'registrar' (solo anota "[csrf]" en el log;
        // útil los primeros días tras desplegar).
        'csrf' => 'bloquear',
        // Si un proxy inverso cambia la cabecera Host, indicar aquí los nombres
        // públicos con que se entra (con puerto si no es 80/443):
        // 'csrf_hosts' => array('erp.cbsd.gob.ec'),
    ),

    // Facturación electrónica: ambiente del SRI
    //   'pruebas'    -> siempre celcer.sri.gob.ec, <ambiente>1</ambiente> (POR DEFECTO)
    //   'produccion' -> siempre cel.sri.gob.ec,    <ambiente>2</ambiente>
    //   'bd'         -> lo que diga web_registro.ambiente de cada empresa
    // Solo el servidor de producción debe tener 'produccion' (o 'bd').
    'sri' => array(
        'ambiente' => 'pruebas',
    ),

    // Archivos temporales que se generan para armar documentos (QR de los reportes).
    // Se crean en una carpeta propia de cada petición y se borran al terminar.
    // Si se omite, se usa la carpeta temporal del sistema.
    // 'archivos' => array('temporal' => '/var/lib/bcbsd/tmp'),

    // Reportes PDF (kconfig/pdf.php). Carpeta escribible para fuentes/métricas de dompdf.
    // Si se omite, se usa la carpeta temporal del sistema.
    // 'pdf' => array('cache_fuentes' => '/var/lib/bcbsd/dompdf-fuentes'),

    'db' => array(
        // Base principal del ERP (Db::conectar)
        'principal' => array(
            'host'     => '127.0.0.1',
            'port'     => 5432,
            'dbname'   => 'db_kaipi',
            'user'     => 'usuario_app',
            'password' => 'CAMBIAR',
        ),
        // Recaudación / órdenes de cobro de bomberos (módulo kservicios)
        'cbsd' => array(
            'host'     => '127.0.0.1',
            'port'     => 5432,
            'dbname'   => 'db_cbsd',
            'user'     => 'usuario_app',
            'password' => 'CAMBIAR',
        ),
        // Db::conectar_sesion_catastro
        'catastro' => array(
            'host' => '', 'port' => 5431, 'dbname' => 'sigcat', 'user' => '', 'password' => '',
        ),
        // Db::conectar_sesion_servicios
        'servicios' => array(
            'host' => '127.0.0.1', 'port' => 5432, 'dbname' => 'bomberos_val', 'user' => '', 'password' => '',
        ),
        // Db::conectar_sesion_ventas y kventas/enlace/enlace_agua.php
        'ventas' => array(
            'host' => '127.0.0.1', 'port' => 5432, 'dbname' => 'pillaro', 'user' => '', 'password' => '',
        ),
        // Db::conectar_sesion (facturación electrónica externa)
        'facturacion' => array(
            'host' => '', 'port' => 5432, 'dbname' => 'ct_facturacion_electronica', 'user' => '', 'password' => '',
        ),
        // Db::conectar_prueba
        'prueba' => array(
            'host' => '', 'port' => 5432, 'dbname' => '', 'user' => '', 'password' => '',
        ),
        // Db::conectar_sesionWS
        'ws' => array(
            'host' => '', 'port' => 5432, 'dbname' => 'ct_cliente', 'user' => '', 'password' => '',
        ),
        // Db::conectar_Mysql (MySQL)
        'mysql_emapa' => array(
            'host' => '', 'port' => 3306, 'dbname' => 'anetran_emapa', 'user' => '', 'password' => '',
        ),
    ),

    'smtp' => array(
        // Cuenta institucional de notificaciones (Db.email.php, Db.emailMarket.php)
        'principal' => array(
            'host'     => 'mail.cbsd.gob.ec',
            'port'     => '587',
            'secure'   => 'tls',
            'user'     => 'no-replay@cbsd.gob.ec',
            'password' => 'CAMBIAR',
            'from'     => 'no-replay@cbsd.gob.ec',
        ),
        // Servidor propio usado por Db.emailMarket.php cuando tipo_enlace != 1
        'propio' => array(
            'host'     => '',
            'port'     => '587',
            'secure'   => 'tls',
            'user'     => 'no-replay@cbsd.gob.ec',
            'password' => 'CAMBIAR',
            'from'     => 'no-replay@cbsd.gob.ec',
        ),
        // Gmail (contraseña de aplicación) usado por Db.emailMarket.php cuando tipo_enlace == 1
        'gmail' => array(
            'host'     => 'smtp.gmail.com',
            'port'     => '465',
            'secure'   => 'ssl',
            'user'     => '',
            'password' => 'CAMBIAR',
            'from'     => '',
        ),
    ),
);
