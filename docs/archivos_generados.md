# Archivos generados fuera de las carpetas del código

**Problema:** al imprimir, el sistema escribía archivos en las carpetas del código (`kxxx/reportes`, `kxxx/model`),
que git mostraba como cambios:
- **83 reportes** guardaban una copia del PDF con nombre fijo (`ComprobanteAsiento.pdf`, `nota.pdf`, `Cierre_Caja.pdf`...)
  justo antes de enviarlo al navegador. Nadie usaba esa copia, y quedaba **descargable por URL sin sesión**
  (los archivos estáticos no pasan por `auth.php`): el último comprobante generado por cualquier usuario.
- Los QR (`logo_qr.png`, `documento.png`) se escribían con nombre fijo: con dos usuarios imprimiendo a la vez,
  uno podía llevarse el QR del otro, y exigían permiso de escritura sobre el código.
- `compromiso.php` mostraba el último QR que otro reporte había dejado en la carpeta (no generaba el suyo).

**Solución:**
- Se quitaron las 83 copias de PDF (el PDF se sigue enviando igual al navegador).
- `kconfig/generados.php`: la librería de QR (`reportes/phpqrcode/qrimage.php`) guarda los QR temporales en una
  carpeta propia de cada petición (temporal del sistema o `'archivos' => array('temporal' => ...)`), que se borra
  al terminar. `KDompdf` y `auth.php` reemplazan `src="logo_qr.png"` por la imagen incrustada; FPDF usa
  `kgen_archivo()` y las respuestas ajax `kgen_reemplazar_html()`. Las etiquetas de activos (`kactivos/code/`) no cambian.
- `compromiso.php` genera el QR de su propio documento.
- Revisión de las 263 páginas de `*/reportes/`: se quitaron además 108 `$pdf = $dompdf->output();` que quedaron sin uso
  (generaban una segunda copia del PDF en memoria); `kservicios/reportes/barcode.php` ya no acepta `?filepath=`
  (permitía escribir la imagen en cualquier ruta del servidor); se eliminó `reportes/firma_prueba.php` (script de prueba
  que generaba claves privadas en la carpeta del código).
- Se mantienen a propósito: los documentos firmados de `kdocumento` (se guardan en la carpeta de archivos configurada) y el
  `.pem` que arma la firma electrónica en la ruta configurada (requiere revisar la firma con un certificado real antes de cambiarlo).
- `.gitignore`: PDF y QR generados en carpetas de código, CSV de facturación y documentos de `archivos/doc`,
  `archivos/inventarios`, `archivos/activos`. Se quitaron del repositorio 33 archivos generados
  (los de `archivos/` se dejaron para que un `git pull` en el servidor no los borre).

**Pendiente:**
- `facturae/xml` y `kactivos/code` acumulan archivos dentro de carpetas de código (ya ignorados por git): moverlos a
  `archivos/` requiere revisar la facturación electrónica con cuidado.
- **Los CSV de facturación** (`kventas/controller/datas*.csv`, `archivos/datas*.csv`) contienen datos de facturas y
  se leen por URL desde un proveedor externo: hoy son públicos. Revisar si ese proveedor se sigue usando.
- `reportes/requerimiento.php` busca la cabecera en `../../kadmin/view/Head.php` (un nivel de más): error 500 desde el código original.
