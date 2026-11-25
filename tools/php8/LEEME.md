# Herramientas de migración a PHP 8 (Fase 3)

Transformaciones mecánicas que **conservan el comportamiento de PHP 7** y hacen
que el código funcione también en PHP 8. Usan el analizador de PHP
(`token_get_all`), no expresiones regulares, así que no tocan textos entre
comillas, comentarios, nombres de funciones/clases/métodos, etc.

| Script | Qué hace | Por qué |
|---|---|---|
| `comillas_constantes.php` | `array(campo => 1)` → `array('campo' => 1)`, `$x[campo]` → `$x['campo']` | PHP 7 trataba una constante no definida como su nombre en texto (con aviso); PHP 8 da error fatal. |
| `ctor_renombrar.php` | `class X { function X() }` → `function __construct()` | PHP 8 ya no ejecuta los constructores estilo PHP 4. |
| `renombrar_funciones.php` | `count(` → `kcount(`, `pg_fetch_assoc(` → `kpg_fetch_assoc(`, `number_format(` → `knumber_format(`, … | Ver `kconfig/compat_php8.php`: reproducen el resultado de PHP 7 donde PHP 8 lanza TypeError. |
| `llaves_offset.php` | `$s{0}` → `$s[0]` | Sintaxis eliminada en PHP 8. |

Uso (sobre archivos nuevos o traídos de otra versión):

```bash
php tools/php8/comillas_constantes.php tools/php8/constantes.txt archivo.php
php tools/php8/ctor_renombrar.php archivo.php
php tools/php8/renombrar_funciones.php archivo.php   # requiere cargar kconfig/compat_php8.php
php tools/php8/llaves_offset.php archivo.php
```

`constantes.txt` lista las constantes que **sí** existen (PHP 7.4, PHP 8.3 con sus
extensiones y los `define()` del proyecto); esas no se convierten. Si se agrega
una extensión de PHP (p. ej. oci8) o un `define()` nuevo, agregar sus nombres.

En código nuevo: escribir directamente `'clave' =>`, `__construct()` y usar las
funciones nativas, validando los datos antes (no depender de `kcount()` & cía.).
