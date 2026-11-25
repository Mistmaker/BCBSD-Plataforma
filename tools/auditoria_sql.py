#!/usr/bin/env python3
"""
Auditoría heurística de posibles inyecciones SQL en el código PHP propio.

Busca líneas con SQL que concatenan datos de $_GET/$_POST/$_REQUEST (directo o
a través de una variable) sin pasar por sqlvalue_inyeccion(), knum(), kstr()
o consultas parametrizadas ($bd->consulta()).

Es una heurística: puede dar falsos positivos (p. ej. URLs) y no sigue los
datos entre funciones. Sirve para medir avance y priorizar revisiones.

Uso:  python3 tools/auditoria_sql.py            (resumen)
      python3 tools/auditoria_sql.py --detalle  (cada línea)
"""
import os
import re
import subprocess
import sys

EXCLUIR = re.compile(r"/(dompdf|phpqrcode|tcpdf|fpdf[^/]*|PHPExcel|Classes|PHPMailer|vendor|libs?|font|nusoap|plugins|tinymce)/|^tinymce/|^app/")
ASIGNA = re.compile(r"\$(\w+)\s*=\s*([^;]*\$_(?:GET|POST|REQUEST)\[[^;]*);")
SEGURO = re.compile(r"->fecha\(|inyeccion|intval|\(int\)|\(float\)|floatval|is_numeric|knum|kstr|json_decode|htmlspecialchars")
SQL = re.compile(r"(select|insert|update|delete|where|like|values|order\s+by|\bin\s*\(|=\s*[\"']?\s*\.)", re.I)


def archivos():
    raiz = subprocess.check_output(["git", "rev-parse", "--show-toplevel"]).decode().strip()
    os.chdir(raiz)
    salida = subprocess.check_output(["git", "ls-files", "-z", "*.php"]).decode("utf8")
    return [f for f in salida.split("\0") if f and not EXCLUIR.search(f)]


def analizar(ruta):
    texto = open(ruta, encoding="utf8", errors="ignore").read()
    contaminadas = {m.group(1) for m in ASIGNA.finditer(texto) if not SEGURO.search(m.group(2))}
    hallazgos = []
    for n, linea in enumerate(texto.splitlines(), 1):
        limpia = linea.strip()
        if limpia.startswith(("//", "*", "#")) or SEGURO.search(linea) or not SQL.search(linea):
            continue
        if re.search(r"[\"']\s*\.\s*(strtoupper\(|trim\()?\s*\$_(GET|POST|REQUEST)\[", linea):
            hallazgos.append((n, "directo", limpia))
            continue
        for v in contaminadas:
            if re.search(r"[\"']\s*\.\s*\$" + v + r"\b", linea) and re.search(
                    r"(where|and|or|like|=|in\s*\(|values|set)\s*[\"'(]?\s*[\"']?\s*(\.\s*)?\$" + v, linea, re.I):
                hallazgos.append((n, "$" + v, limpia))
                break
    return hallazgos


def main():
    detalle = "--detalle" in sys.argv
    total = 0
    por_archivo = []
    for ruta in archivos():
        h = analizar(ruta)
        if h:
            por_archivo.append((ruta, h))
            total += len(h)
    for ruta, h in sorted(por_archivo):
        print(f"{ruta}: {len(h)}")
        if detalle:
            for n, tipo, linea in h:
                print(f"    {n:>5} [{tipo}] {linea[:150]}")
    print(f"\nTOTAL: {total} líneas sospechosas en {len(por_archivo)} archivos")


if __name__ == "__main__":
    main()
