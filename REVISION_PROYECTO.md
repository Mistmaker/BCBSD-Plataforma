# Revisión técnica — BCBSD-Plataforma

Fecha: 2026-11-24 · Alcance: todo el repositorio (commit `ca2393e3`).
Las cifras salen de análisis automáticos (hashes, expresiones regulares, `php -l` con PHP 8.3). Son órdenes de magnitud fiables, no conteos exactos.

## 1. Panorama

| Dato | Valor |
|---|---|
| Archivos totales | ~30.000 (500 MB en disco, 76 MB empaquetado en git) |
| PHP propio (sin librerías obvias) | ~4.400 archivos, ~900.000 líneas |
| Módulos | `kadmin`, `kactivos`, `kcms`, `kcontabilidad`, `kcrm`, `kdocumento`, `kgarantia`, `kinventario`, `kpersonal`, `kplanificacion`, `kpresupuesto`, `kreportes`, `kservicios`, `ktributacion`, `kventas`, `karchivo`, `facturae`, `reportes`, `spi`, `upload` |
| Núcleo compartido | `kconfig/` (`Db.class.php` 5.300 líneas, `Obj.grid.php` 7.000 líneas, `Obj.*.php`) |
| Patrón | PHP procedural + pseudo-MVC por carpeta (`controller/ model/ view/ grilla/ js/ reportes/`), PostgreSQL vía `pg_*` |
| Pruebas automáticas | Ninguna |

## 2. Seguridad — CRÍTICO (atender primero)

### 2.1 Credenciales en el código
- `kconfig/Db.class.php`: contraseñas de PostgreSQL en el constructor (líneas ~58-62) y de nuevo en `conectar()` (~1129-1133), que **ignora** los parámetros que recibe y siempre usa las credenciales fijas.
- Hay 8 métodos `conectar_*` más con credenciales de otros servidores, varios con IP pública (`factura100.com`, `167.99.0.234`, `181.112.225.37`, `186.4.189.205`, `catastro.ddns.net`…). 18 llamadas en total usan los métodos de servicios/pruebas/MySQL/WS.
- Credenciales también en `kadmin/controller/Controller-login.php:18`, `kconfig/Db.user.php` (base64, que no protege nada), `kconfig/prueba.php`, `kadmin/controller/test.php`, `upload/newfile.php`, `kcontabilidad/view/variables.php`, `kconfig/Db.email.php`, `Db.emailMarket.php`, `masivo.php`, `kpersonal/model/EnvioCorreo.php` y ~10 archivos de `kservicios/model/`.
- **Si la conexión falla, `conectar()` imprime la cadena de conexión con la contraseña** (`Db.class.php` ~1141-1146).

➡ Todas estas credenciales ya están en el historial de git: **hay que rotarlas**, no basta con borrarlas.

### 2.2 Certificados de firma electrónica en el repositorio
- `facturae/firmas/*.p12|*.pem` y `kdocumento/reportes/{jasapas,pruebacaja}.p12|.pem`.
- `ktrejo2025.pem`, `jasapas.pem` y `pruebacaja.pem` contienen la **clave privada sin cifrar**. `kdocumento/reportes/` se sirve por web, así que cualquiera puede descargarlas y firmar en nombre del titular.
- `reportes/prueba.php` hace referencia a otro `.p12` personal.

➡ Revocar y reemitir los certificados afectados, sacarlos del repo y de las carpetas públicas, y guardarlos fuera del *document root*.

### 2.3 Sin control de acceso real
- Solo **65** de ~4.400 archivos verifican `$_SESSION['usuario']`, y **los 65** hacen `header('Location: …')` **sin `exit`**. La página se ejecuta y se envía igual, así que la redirección no protege nada.
- Ninguno de los ~2.640 archivos de `model/` y `controller/` verifica sesión. Como `conectar()` usa credenciales fijas, todos funcionan **sin iniciar sesión**.
- `kcontabilidad/view/variables.php` está pensado para fabricar una sesión, con credenciales incluidas. Hoy no ejecuta porque tiene un error de sintaxis, pero debe eliminarse.

### 2.4 Inyección SQL
- `Db::sqlvalue_inyeccion()` solo duplica comillas simples. No se usan consultas preparadas (`pg_query_params`).
- Hay muchos casos donde la entrada se concatena directamente. Ejemplo sin autenticación: `kcms/model/AutoCompleteIDCedula.php:18` (`$_GET["query"]` dentro de `LIKE`).
- El análisis heurístico encontró ~240 líneas SQL con datos de `$_GET`/`$_POST` sin sanear en ~130 archivos. La cifra real probablemente es mayor, porque muchos valores pasan por varias variables antes de llegar a la consulta.
- `query_sql()`, `__between()`, `query_array()` y otros arman `WHERE`/`ORDER BY` con texto libre.

### 2.5 Subida de archivos → ejecución remota de código
- `kadmin/model/upload.php`: sin sesión y sin validar extensión, guarda el archivo **con su nombre original** en `kdocumento/reportes/`, que es pública. Subir `x.php` permite ejecutar código en el servidor.
- 38 archivos usan `move_uploaded_file`. Varios no validan el tipo (`kcms/view/seg_file.php`, `kdocumento/view/flowload.php`, `kinventario/view/importarDetalle.php`, `upload/upload1.php`…). Otros fuerzan `.pdf`, pero toman la carpeta destino de `$_GET['file']`, lo que permite *path traversal*.
- `upload/filebrowser.php` lista directorios sin autenticación.

### 2.6 Otros
- Las contraseñas de usuario se guardan en **base64** (`Obj.var.php::_codifica`), es decir, en texto plano. Hay que migrar a `password_hash()`/`password_verify()`.
- `facturae/ver.php` expone `phpinfo()`.
- `.htaccess` raíz activa `display_errors on`.
- 87 `echo` directos de `$_GET`/`$_POST` (XSS reflejado).
- `dompdf` antiguo (formato 0.7) copiado 11 veces; `kcms` y `ktributacion` incluyen `www/` (demo) y `dompdf.php`, que acepta `input_file` por GET.
- 18 versiones distintas de jQuery (de 1.1 a 3.4), la mayoría con CVEs de XSS conocidos.
- Datos personales versionados: `spi/1PROVEEDORES 2019.txt` (cédulas, nombres, cuentas bancarias, montos), CSV/XLS/PDF en `archivos/`, `upload/`, etc.
- No hay tokens CSRF en formularios.

## 3. Duplicación (lo "repetitivo")

### 3.1 Copias exactas
- **15.862 archivos son copias idénticas de otro** (~3.950 grupos), unos **209 MB** desperdiciados.
- Subárboles completos copiados en cada módulo:

| Componente | Copias |
|---|---|
| `phpqrcode/` (425 archivos) | 11-12 |
| `dompdf/` (cientos de archivos) | 11 |
| `reportes/font/` (FPDF) | 7-12 |
| `js/default/` y `view/default/` (tema easyui) | ~28 |
| `calendario/` | 6 |
| `view/js/` (70 archivos) | 3 (`kpresupuesto`, `kservicios`, `kventas`) |
| `RideSRI/libs` | 2 (`facturae`, `kventas/model`) |
| `assets/img/icons` | `app/` y `kconfig/` |

- PHP propio idéntico en varios módulos: `sesion.php` (×14), `moduloPanel.php`/`View-moduloPanel.php` (×13), `Controller-login.php`, etc.

### 3.2 Copias que divergieron (más peligrosas)
- 736 nombres de archivo se repiten entre módulos. Solo 183 son idénticos; **553 ya divergieron**. Un arreglo en una copia no llega a las demás.
  - `Head.php` ×24 (20 versiones), `index.php` ×21, `SesionInicio.php` ×18, `inicio.php` ×17, `View-HeaderModel.php` ×16 (16 versiones), `View-HeaderInicio.php` ×16, `Model-moduloOpcion.php` ×16, `AutoCompleteCIU.php` ×14 (9 versiones), `kreportes.php` ×12…
  - Lógica contable duplicada: `Model-co_asientos.php` en `kventas`, `kcontabilidad` y `kpresupuesto` (61-64 % similares); `Model-co_dasientos.php` (79 %).

### 3.3 Duplicación dentro del núcleo
- `Db.class.php`: 12 métodos `conectar*` copiados y pegados; `KPcontador_usuario` y `KPcontador` son casi iguales; muchas funciones de fecha que se solapan (`_mes`, `_mesc`, `_formato_fecha`, `_fecha_completa`, `_fecha_texto`, `fecha`, `hoy`, `fecha_hoy`, `kfecha`…). Además `_refd` está tanto en `Db` como en `Obj.var.php`.
- `kconfig/newfile.php` y `upload/newfile.php` copian la clase `Conf`.

### 3.4 Respaldos y código muerto versionados
- ~79 archivos tipo `*_copia`, `*_old`, `*_OLD-032025`, `*_ori`, `*_ORIGINAL`, `prueba*`, `test*`, `"Controller-caso_tarea 01.php"`, `"index - copia.php"`.
- Carpeta completa `kservicios/reportes - copia/`.
- `kactivos/kreportes_old.php`, `kadmin/view/View-panel_original.php`, `kinventario/migra/_aux_chimbo.php`…
- `estructura_app.txt` (240 KB, listado de carpetas de Windows en UTF-16).
- `.gitignore` menciona `kadmin_ori`, `kpresupuesto_ori`, `z-backups`: ya existe la costumbre de respaldar copiando carpetas en lugar de usar git.

## 4. Compatibilidad / mantenibilidad

- **Atado a PHP ≤ 7.4**, sin soporte desde nov-2022:
  - ~**106.000** claves de array sin comillas (`array(campo => 'x', tipo => …)`) en ~935 archivos. En PHP 8 cada una es un *Fatal error: Undefined constant*.
  - `class Db { function Db() }`: constructor estilo PHP 4, que en PHP 8 deja de ser constructor.
  - `mysql_*` (215 usos en 4 archivos, p. ej. `Db::clean_dato`), `ereg`/`split`/`each`/`create_function` (6).
  - `utf8_encode/decode`: 462 usos (deprecado en 8.2).
- 2 archivos ni siquiera compilan: `kcontabilidad/view/variables.php:4` y `kcontabilidad/controller/Controller-login.php:14`. Ambos tienen `'' = '…'`, rastro de un buscar/reemplazar global de `$_SESSION['db']` que salió mal.
- 1.745 supresiones de errores con `@`.
- Mojibake de codificación en comentarios y cadenas (`conexiÃƒÂ³n`); `_caracteres()` y `poner_guion()` "parchan" el texto en lugar de fijar UTF-8 de punta a punta.
- `date_default_timezone_set('America/Lima')` en una institución de Ecuador. Tiene el mismo desfase horario, pero lo correcto es `America/Guayaquil`.
- `session_start()` en ~3.800 archivos; no hay *front controller* ni *bootstrap* común.
- Sin Composer para PHP: las librerías están copiadas a mano, salvo `kactivos/reportes/vendor` y `kcontabilidad/reportes/vendor`.

## 5. Plan de corrección propuesto

### Fase 0 — Contención (días)
1. Rotar **todas** las contraseñas de BD y SMTP encontradas. Crear un usuario de BD de aplicación con permisos mínimos (no `postgres`).
2. Revocar y reemitir los certificados de firma expuestos. Quitar `.p12`/`.pem` del repo y del *document root*.
3. Borrar o bloquear: `kadmin/model/upload.php`, `facturae/ver.php`, `kconfig/prueba.php`, `kadmin/controller/test.php`, `kcontabilidad/view/variables.php`, `upload/filebrowser.php`, `dompdf/www`, `dompdf/dompdf.php`, `reportes/prueba.php`.
4. Quitar el `die()` que imprime la cadena de conexión. Poner `display_errors off` en `.htaccess`.
5. Mover la configuración a un archivo fuera del repo (`.env` o `config.local.php` en `.gitignore`) y que `Db` la lea de ahí.
6. Tras rotar, limpiar el historial con `git filter-repo` (secretos, certificados, datos personales).

### Fase 1 — Control de acceso y entrada (semanas)
1. Crear `kconfig/auth.php` (`session_start` + verificación + `http_response_code(401)` + `exit`) e incluirlo en **todos** los endpoints `model/`, `controller/`, `grilla/`, `reportes/` y `upload/`. Se puede hacer con un script.
2. Agregar `exit;` después de cada `header('Location')`.
3. Añadir a `Db` un método `query($sql, array $params)` con `pg_query_params` y migrar primero los endpoints con `$_GET`/`$_POST` directos.
4. Uploads: lista blanca de extensiones y MIME, nombre aleatorio, carpeta fuera del *document root* o con `.htaccess` que impida ejecutar PHP.
5. Migrar contraseñas a `password_hash` con rehash transparente en el login.
6. Escapar salidas (`htmlspecialchars`) donde se hace echo de la entrada.

### Fase 2 — Deduplicar (semanas)
1. Borrar respaldos y código muerto (sección 3.4). Git ya guarda la historia.
2. Una sola copia de cada librería: `composer require dompdf/dompdf tecnickcom/tcpdf setasign/fpdf chillerlan/php-qrcode phpmailer/phpmailer phpoffice/phpspreadsheet` en `/vendor` raíz. Los JS y el tema, solo en `/app`. Actualizar los `require`/`<script src>`.
3. Unificar los archivos idénticos (`sesion.php`, `moduloPanel.php`…) en `kconfig/` (o un nuevo `shared/`).
4. Para los que divergieron (`Head.php`, `View-HeaderModel.php`, `AutoComplete*.php`, `Model-co_asientos.php`…): comparar versiones, quedarse con una parametrizada por módulo y eliminar el resto.
5. Limpiar `Db.class.php`: un solo `conectar()` configurable, una utilidad de fechas, quitar métodos sin uso.

### Fase 3 — Migración a PHP 8.x
1. Script automático para poner comillas a las claves de array (`campo => …` → `'campo' => …`). Es mecánico pero toca ~935 archivos; hacerlo módulo por módulo con revisión.
2. `function Db()` → `__construct()`; eliminar `mysql_*`, `ereg`, `each`, `create_function`; sustituir `utf8_encode` por `mb_convert_encoding`.
3. Arreglar los 2 errores de sintaxis.
4. Probar en PHP 8.2/8.3 con `error_reporting(E_ALL)` y registro de errores a archivo.

### Fase 4 — Base para seguir mejorando
- Linter en CI: `php -l`, PHPStan nivel bajo y PHP_CodeSniffer.
- Pruebas de humo de los flujos críticos (login, asientos, facturación electrónica, SPI).
- Un `bootstrap.php` común (zona horaria, sesión, conexión, auth) en lugar de repetirlo en cada archivo.
- README con instalación, configuración y despliegue.

## 6. Pendientes registrados

| # | Pendiente | Origen | Detalle |
|---|---|---|---|
| P1 | **`kreportes/controller/VistaSql.php` recibe SQL desde el navegador** | Fase 1 | El generador de reportes arma la consulta con `$_GET['sfiltro']` (un `WHERE` completo enviado por el cliente) y nombres de vista/columnas también de la petición. Cualquier usuario con sesión puede ejecutar SQL arbitrario. Requiere rediseño: que el cliente envíe filtros estructurados (columna, operador, valor), validar columnas contra `ireport.ireport_vista` y usar `$bd->consulta()` con parámetros. |
| P2 | Sin límite de intentos de login | Fase 1 | Agregar bloqueo temporal por usuario/IP tras N intentos fallidos. |
| P3 | Sin tokens CSRF en formularios | Fase 1 | Token por sesión verificado en los endpoints que modifican datos. |
| P4 | 55 líneas marcadas por `tools/auditoria_sql.py` | Fase 1 | Mayoría falsos positivos (URLs); revisar el resto y migrar a `$bd->consulta()`. |
