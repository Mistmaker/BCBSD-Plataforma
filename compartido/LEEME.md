# compartido/

Copia única de archivos PHP que eran **idénticos byte a byte** en varios módulos
(Fase 2 de la revisión). Cada ubicación original quedó como un archivo de una línea:

```php
return require __DIR__ . '/../../compartido/<tipo>/<archivo>.php';
```

- Las URL de cada módulo no cambian.
- Las rutas relativas dentro del código (`require 'Head.php'`, `'../../kconfig/...'`,
  enlaces HTML) se siguen resolviendo desde el módulo que hace la petición.
- **Un cambio aquí afecta a todos los módulos que usan el archivo.** Si un módulo
  necesita un comportamiento distinto, reemplazar su archivo de una línea por una
  copia propia y modificar esa copia.
- La carpeta no es accesible por web (`.htaccess`).

## view/Head.php — cabecera común

No es una copia idéntica sino una plantilla con opciones. Cada cabecera de módulo queda así:

```php
<?php
$kh = array(
    'jquery'  => '3.2.1',                       // solo lo que difiere del valor por defecto
    'estilos' => __DIR__ . '/Head_estilos.php', // bloque <style> propio del módulo
);
require __DIR__ . '/../../compartido/view/Head.php';
```

Opciones disponibles: `favicon`, `jquery`, `easyui_css`, `typeahead`, `estilos`, `raiz`, `js_modulo`
(documentadas al inicio de `view/Head.php`). La plantilla incluye la guardia de sesión.
