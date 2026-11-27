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
| P1 | ~~`kreportes/controller/VistaSql.php` recibe SQL desde el navegador~~ **Resuelto** | Fase 1 | `kreportes/model/ireport_filtro.php` reconstruye el filtro: solo acepta `col = 'texto'`, `col = número`, `col like 'texto%'` y `( col BETWEEN 'a' AND 'b' )` unidos con `and`, con columnas registradas en `ireport.ireport_vista` y valores escapados; la vista y el esquema deben ser identificadores registrados. Escapados también `DirVista`, `Linea_sinagrupar`, `VisorAnalisis`, `grilla_configuracion` y el XSS de `visor.php`. |
| P2 | ~~Sin límite de intentos de login~~ **Resuelto** | Fase 1 | 5 claves incorrectas → usuario bloqueado 15 min (`klogin_*` en `kconfig/seguridad.php`, configurable). Límite por IP opcional (desactivado: con NAT o proxy común bloquearía a todos). |
| P3 | ~~Sin tokens CSRF~~ **Resuelto** | Fase 1 | Cookie de sesión `SameSite=Lax` + `HttpOnly`; token por sesión agregado solo a formularios POST y ajax (`auth.php` + `sesion.js`); todo POST con sesión exige token válido u Origin/Referer del mismo sitio. Modo `'csrf' => 'registrar'` para desplegar. **Residual (P3b):** operaciones que modifican datos por GET (p. ej. `ajax_del*.php?id=`) solo quedan cubiertas por SameSite (no por enlaces que el usuario abra); hay ~314 archivos que leen `$_GET` y contienen INSERT/UPDATE/DELETE: revisarlos y pasar las operaciones a POST. |
| P4 | 55 líneas marcadas por `tools/auditoria_sql.py` | Fase 1 | Mayoría falsos positivos (URLs); revisar el resto y migrar a `$bd->consulta()`. |
| P5 | Confirmar que ningún menú apunta a pantallas eliminadas | Fase 2 | Los menús están en `par_modulos.vinculo` (datos, no código). Consulta en el PR de la Fase 2. |
| P6 | Variantes que divergieron (no consolidadas) | Fase 2 | Ver sección 7. Unificarlas requiere revisar cada diferencia a mano. |
| P7 | Probar en PHP 8 los flujos que modifican datos | Fase 3 | El barrido cubre las pantallas por GET; guardar, contabilizar, facturar, nómina, SPI, etc. deben probarse en un servidor de pruebas con PHP 8.3 revisando el log (`[compat_php8]`, `TypeError`, `DivisionByZeroError`). |
| P8 | Comparaciones `==` entre número y texto | Fase 3 | PHP 8 cambió `0 == ''` (y `0 == 'abc'`) de verdadero a falso. Se corrigió en los constructores de listas (`kigual()`); el resto del código puede tener casos que solo aparecerán con datos reales. |
| P9 | ~~Una sola versión moderna de dompdf~~ **Resuelto** | Fase 2/3 | `lib/dompdf` (3.x) + `kconfig/pdf.php` (`KDompdf`) para los 121 reportes; se eliminaron las 11 copias. |
| P10 | ~~`strftime()` obsoleta desde PHP 8.1~~ **Resuelto** | Tildes PDF | `kstrftime()` en `kconfig/compat_php8.php` (nombres en español, UTF-8; idéntica a `strftime` con `es_ES.UTF-8` en 241 fechas probadas) para `reportes/reporteDiarioEspecie.php`; en nuSoap (`facturae/`) y PHPExcel (`spi/`) los formatos numéricos se pasaron a `date()`. |
| P11 | Propiedades dinámicas (`$this->x = ...` sin declarar) | Fase 3 | Obsoletas desde PHP 8.2 (aviso oculto) y **error en PHP 9**. La más frecuente es la clase `objects` de `kconfig/Obj.conf.php` (en cada pantalla). Declarar las propiedades en cada clase o marcarla con `#[\AllowDynamicProperties]`. Barrido en PHP 8.3: `objects` 19 272 avisos, `proceso` 64, `componente` 14, `Db` 10, `ReportePdf` 7, `Model_reportes_presupuesto` 4, `archivo_finanzas` 3, `Model_reportes_gestion` 1. |
| P12 | Reportes que fallaban sin parámetros | Head único | `FichaProceso.php` (kactivos, kcms, kcrm, kventas) y `kservicios/reportes/Resumen_conta_caja.php` daban error fatal sin `?id=`: ahora responden 400 con un mensaje. kactivos y kcms no tenían el modelo `FichaProcesosReportes.php`: usan el de `compartido/model`. **Resuelto.** |

## 7. Resultado de la Fase 2 (deduplicación)

Criterio: solo se consolidó lo **idéntico byte a byte**; las variantes se dejaron.

| Qué | Antes | Después |
|---|---|---|
| Archivos en el repositorio | 29.505 | 23.370 |
| phpqrcode | 11 copias (1 corrupta) | 1 (`reportes/phpqrcode`) |
| PHP propio idéntico entre módulos | 1.102 archivos en 454 grupos | 454 archivos en `compartido/` + archivos de 1 línea |
| Tema easyui | 28 copias idénticas + 3 de otra variante | 1 (`app/easyui/default`) + la variante |
| calendario | 6 copias | 1 (`app/calendario`) |
| Respaldos (`_old`, `_ori`, `- copia`…) | 29 archivos + `kservicios/reportes - copia/` (1.005) | 0 |

**No consolidado (son variantes, no duplicados):**
- **dompdf** (11 copias): las 11 difieren; varias en código (p. ej. `kcrm` y `kdocumento` usan la API antigua `include/*.cls.php`). Unificarlas cambiaría la generación de PDF → requiere elegir una versión y probar cada reporte.
- **Fuentes FPDF** (`*/reportes/font`): 3 grupos idénticos, pero cada uno va con su versión de FPDF.
- **RideSRI** (`facturae/` y `kventas/model/`): difieren en `RideGlobal.php`.
- **Tema easyui** de `kcontabilidad`, `kpresupuesto`, `kreportes`: imágenes distintas.
- **553 nombres de archivo** repetidos entre módulos con contenido distinto (p. ej. `Head.php` ×20 versiones, `View-HeaderModel.php` ×16, `Model-co_asientos.php` ×3). Unificar cada uno exige revisar sus diferencias.
- `app/lib` y `kconfig/lib` (alertify, 2 copias en uso): pendiente menor.

## 8. Recursos compartidos para módulos existentes y nuevos

**Sí se puede, y ya está en marcha.** Propuesta de estructura (una sola copia de cada cosa):

| Carpeta | Contenido | Estado |
|---|---|---|
| `kconfig/` | Núcleo PHP: `Db.class.php`, `config.php`, `auth.php`, `seguridad.php`, `compat_php8.php`, constructores de formularios `Obj.*.php`, `sesion.js` | Existe (Fases 0-3) |
| `compartido/<tipo>/` | Pantallas, modelos y controladores que son iguales en varios módulos (cada módulo conserva un archivo de 1 línea que los incluye) | Existe (Fase 2): 454 archivos |
| `app/` | Recursos web: jQuery, Bootstrap, tema easyui (`app/easyui/default`), calendario, iconos | Existe; se consolidó tema y calendario |
| `lib/` | Librerías PHP de terceros, **una versión** de cada una | dompdf ya está (`lib/dompdf`, vía `kconfig/pdf.php`). Faltan tcpdf, fpdf, PHPMailer, PhpSpreadsheet; `reportes/phpqrcode` hace de copia única |

**Para un módulo nuevo** (`knuevo/`):

```
knuevo/
  index.php                  -> redirige a view/inicio (como los demás)
  view/inicio.php            -> require_once __DIR__.'/../../kconfig/auth.php'; usa Head común
  view/Head.php              -> $kh = array(...opciones...); require __DIR__.'/../../compartido/view/Head.php';
  controller/ model/ grilla/ -> lógica propia del módulo
  js/                        -> JS propio; las librerías se cargan desde ../../app/
```

Reglas: nada de copiar librerías dentro del módulo; credenciales solo en `kconfig/config.local.php`;
consultas nuevas con `$bd->consulta($sql, $params)`; código compatible con PHP 8 (claves entre comillas, `__construct`).

**Siguientes pasos sugeridos** (en este orden, cada uno con su barrido de regresión):
1. ~~`lib/` con una sola dompdf moderna (P9)~~ — hecho (`kconfig/pdf.php`).
2. ~~Unificar `Head.php`~~ — hecho: 21 cabeceras usan `compartido/view/Head.php` (ver 8.1).
3. Revisar las variantes con lógica de negocio (`Model-co_asientos.php` ×3, `Model-co_dasientos.php`…) y fusionarlas.
4. Plantilla de módulo nuevo (carpeta `plantilla_modulo/`) siguiendo lo anterior.

### 8.1 Cabecera única (`compartido/view/Head.php`)

Las 21 cabeceras (`k*/view/Head.php`, `kcrm/enlaces`, `kdocumento/enlaces`, `kpresupuesto/reportes`,
`compartido/reportes`, `reportes/`) tenían la misma lista de 25 recursos y solo diferían en:
versión de jQuery activa (1.11.3 o 3.2.1), favicon, tema easyui y typeahead propios del módulo,
CDN o copia local, y su bloque `<style>`. Ahora cada una es un archivo de pocas líneas con sus
opciones (`$kh`) y su `<style>` en `Head_estilos.php` al lado, copiado tal cual.

- Verificado: la secuencia de recursos renderizada es idéntica **por contenido** en las 21
  (los CDN de jQuery/DataTables se sirven desde las copias locales idénticas de `app/`).
  En navegador, la misma versión de jQuery en cada módulo y ningún error JS nuevo.
- Las líneas `session_start()`/redirección de las cabeceras se eliminaron: la guardia
  `kconfig/auth.php` ya se ejecuta antes (en `reportes/notificacion.php` se agregó).
- `kcontabilidad/view/HeadS.php` se eliminó (sin referencias).
- **No se unificó la versión de jQuery**: hacerlo exige probar cada pantalla del módulo.

## 12. Exportación a Excel real (.xlsx) — `kconfig/excel.php`

**Problema:** las exportaciones enviaban una **tabla HTML con extensión .xls**. Excel la abre como «página web»
(avisa que el formato no coincide), al guardarla queda en HTML y los números se interpretan según la configuración
regional del equipo: `1.500` puede quedar como 1,5 o 1500, se pierden o agregan ceros, y las cédulas pierden el cero
inicial (por eso algunas consultas anteponían un espacio: `' ' || idprov`).

**Cómo se exportaba (inventario):**
| Forma | Cantidad | Estado |
|---|---|---|
| Servidor: `KP_GRID_EXCEL()` (SQL fijo o guardado en sesión, p. ej. `$_SESSION['isql']`) + cabecera `.xls` | 41 archivos | piloto: `reportes/excel_personal.php` (listado de personal de nómina); faltan 40 |
| Servidor: tabla HTML propia + cabecera `.xls` | `reportes/PlanCtasExcel.php`, `kpresupuesto/reportes/excelReformaGasto.php` (y 2 utilidades de migración) | pendiente |
| Navegador: `window.open('data:application/vnd.ms-excel,' + html)`, enlace `a.download='x.xls'`, `tableToExcel` base64 | 38 archivos | **resuelto en forma central** (`kconfig/sesion.js`) |
| DataTables `excelHtml5` | 3 | ya generaban .xlsx |
| TableTools (Flash) | 6 | no funcionan en navegadores actuales; reemplazar por `excelHtml5` o `kExcelHtml()` |

**Solución:**
- `kconfig/excel.php` genera un .xlsx real en PHP puro (sin librerías ni la extensión zip): números como números con
  el formato de decimales de la columna, fechas como fechas, textos como texto (cédulas, RUC, cuentas conservan los
  ceros), encabezado en negrita, fila fija y filtros.
  - `kexcel_enviar_resultado($resultado, 'nombre')`: desde una consulta, con el **tipo de cada columna en la base**.
  - `kexcel_enviar_html($html, 'nombre')`: desde tablas HTML, deduciendo el tipo por columna (reconoce `1.234,56` y
    `1,234.56`, `(120,00)` contable, `dd/mm/aaaa`, `colspan`/`rowspan`, columnas ocultas).
- `kconfig/excel_html.php` + `kconfig/sesion.js`: las descargas `data:application/vnd.ms-excel` de las pantallas se
  interceptan y se convierten a .xlsx en el servidor. Para código nuevo: `kExcelHtml(elemento, 'nombre')`.
- **Reglas de tipos** (validadas con montos, fechas e identificadores):
  - columnas que identifican algo (cédula, RUC, identificación, trámite, comprobante, código, cuenta, partida,
    documento, factura, teléfono, número...) van **siempre como texto**, tal como vienen, aunque en la base sean
    números; si la columna tiene decimales es un monto y queda como número;
  - las cédulas que empiezan con 0 (09...) y todo número de 10 o más dígitos quedan como texto;
  - fechas en **dd/mm/aaaa** (también `d/m/aa`); enteros sin separador de miles (año 2026, no 2.026), salvo que la
    pantalla los mostrara con separador; decimales con los de su columna (2, 4...).
- **Aplicado** a las 41 exportaciones del servidor con `KP_GRID_EXCEL` y a `reportes/PlanCtasExcel.php`.
  Nombre del archivo: módulo + reporte + fecha (`tributacion_compras_2026-11-27.xlsx`).
- **Se mantiene en CSV** `kpresupuesto/reportes/excelReformaGasto.php`: es la plantilla para cargar reformas.
- **Pendiente:** los botones TableTools de la plantilla `table-tabletools` (Excel/copiar dependían de Flash; solo
  «Imprimir» funciona); los enlaces `window.open('reportes/ficheroExcel')` de los `kaipi.js` y las copias
  `*/reportes/font/ficheroExcel.php` y `reportes/excel.php` apuntan a rutas inexistentes desde el código original.
