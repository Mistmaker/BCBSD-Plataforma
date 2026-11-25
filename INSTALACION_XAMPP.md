# Ambiente de pruebas con XAMPP (PHP 8.3, Windows)

## 1. Código
- Clonar o copiar el proyecto en `C:\xampp\htdocs\<carpeta>`.
- Git en Windows convierte los fines de línea al clonar; `.gitattributes` deja
  sin convertir `lib/` (dompdf) y las métricas de fuentes.

## 2. PHP (`C:\xampp\php\php.ini`)
Quitar el `;` del inicio de estas líneas y reiniciar Apache:

```ini
extension=pgsql        ; PostgreSQL (obligatoria; usa libpq.dll que trae XAMPP)
extension=pdo_pgsql
extension=mbstring     ; textos y PDF (obligatoria)
extension=gd           ; imágenes en PDF, códigos QR
extension=curl         ; envío al SRI y servicios externos
extension=openssl      ; firma electrónica y correo
extension=zip          ; lectura/escritura de Excel
extension=fileinfo
```

`dom`, `simplexml`, `json`, `iconv` y `bcmath` vienen incluidas en PHP para Windows.

Valores recomendados (iguales a los probados):

```ini
output_buffering = 4096
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
date.timezone = America/Guayaquil
```

Comprobar con `C:\xampp\php\php.exe -m` que aparezcan `pgsql`, `mbstring` y `gd`.

## 3. Apache
- `mod_rewrite` activo (XAMPP lo trae activo) y `AllowOverride All` para
  `htdocs` (valor por defecto de XAMPP): el proyecto usa `.htaccess`.

## 4. Configuración (`kconfig/config.local.php`)
1. Copiar `kconfig/config.example.php` a `kconfig/config.local.php`.
2. Completar las conexiones `db` (principal y `cbsd`) y `smtp`.
3. Dejar `'sri' => array('ambiente' => 'pruebas')` (celcer.sri.gob.ec).
4. Para los primeros días de prueba se puede usar
   `'auth' => 'registrar'` y `'csrf' => 'registrar'`: no bloquean, solo anotan
   en `C:\xampp\apache\logs\error.log` lo que bloquearían (`[auth]`, `[csrf]`).
   Volver a `'bloquear'` antes de producción.

## 5. Base de datos
- Ejecutar `sql/2026-11_fase1_clave_usuarios.sql` (amplía la columna de la
  clave para guardar hashes). Las claves anteriores siguen funcionando y se
  convierten solas al ingresar.

## 6. Qué revisar en las pruebas
- Ingreso, cierre por inactividad y la ventana "sesión expirada".
- 5 claves incorrectas bloquean al usuario 15 min (`login_intentos`).
- Flujos que guardan datos (P7): asientos, facturación (ambiente de pruebas
  del SRI), nómina, SPI, subida de archivos.
- Reportes PDF (dompdf) con tildes.
- En el log de Apache: `PHP Fatal error`, `TypeError`, `[compat_php8]`,
  `[csrf]`, `[auth]`, `[Db] Error SQL`.

La carpeta temporal del sistema (`sys_get_temp_dir()`) debe ser escribible por
Apache: ahí se guardan las fuentes de dompdf (`bcbsd-dompdf-fuentes`) y los
contadores de intentos de login (`bcbsd-login`).
