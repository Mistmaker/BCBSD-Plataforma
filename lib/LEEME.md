# lib/

Librerías PHP de terceros, **una sola copia para todos los módulos**.

## dompdf (`lib/dompdf/`)

- Versión: ver `lib/dompdf/composer.lock` (dompdf 3.x, compatible con PHP 7.4 y 8.x).
- Los reportes **no** la cargan directamente: usan `kconfig/pdf.php` y la clase `KDompdf`,
  que aplica la configuración del sistema (imágenes desde la carpeta de la aplicación,
  carpeta de fuentes escribible, recursos remotos desactivados).

```php
require_once __DIR__ . '/../../kconfig/pdf.php';
$dompdf = new KDompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('reporte.pdf', array('Attachment' => false));
```

### Actualizar dompdf

```bash
cd lib/dompdf
composer update --no-dev      # respeta "platform": php 7.4 en composer.json
find vendor -name .git -type d -prune -exec rm -rf {} +
```

Después, generar algunos reportes y compararlos (texto con `pdftotext`, páginas e imágenes).

### Fuentes

`KDompdf` copia las fuentes incluidas (DejaVu, Helvetica, Times…) a una carpeta escribible
(por defecto `<temp del sistema>/bcbsd-dompdf-fuentes`, configurable con
`'pdf' => array('cache_fuentes' => ...)` en `kconfig/config.local.php`) y guarda ahí las
métricas de las fuentes web que aparezcan en el CSS (Font Awesome, Glyphicons).

`lib/` no es accesible por web (`lib/.htaccess`).
