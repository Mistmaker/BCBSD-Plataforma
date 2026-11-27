# Exportación a Excel real (.xlsx) — `kconfig/excel.php`

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
