<?php
/**
 * Consulta de la auditoría de cambios (sql/2026-11_auditoria.sql).
 *
 * Quién puede consultarla:
 *  - los usuarios con la opción de menú de auditoría asignada (par_modulos.vinculo
 *    que contenga "auditoria", asignada en par_rol), o
 *  - los logins listados en kconfig/config.local.php:
 *        'seguridad' => array('auditoria_usuarios' => array('dbuenano'))
 */

require_once __DIR__ . '/seguridad.php';

if (!function_exists('kauditoria_grupos')) {

    /**
     * Registros "de negocio" que abarcan varias tablas. Para cada tabla: la
     * columna que guarda el identificador del registro principal.
     */
    function kauditoria_grupos()
    {
        return array(
            'asiento' => array(
                'titulo' => 'Asiento contable',
                'tablas' => array(
                    'public.co_asiento'     => 'id_asiento',
                    'public.co_asientod'    => 'id_asiento',
                    'public.co_asiento_aux' => 'id_asiento',
                ),
            ),
            'persona' => array(
                'titulo' => 'Proveedor / cliente / empleado',
                'tablas' => array(
                    'public.par_ciu'      => 'idprov',
                    'public.nom_personal' => 'idprov',
                ),
            ),
            'tramite' => array(
                'titulo' => 'Trámite / certificación presupuestaria',
                'tablas' => array(
                    'presupuesto.pre_tramite'     => 'id_tramite',
                    'presupuesto.pre_tramite_det' => 'id_tramite',
                ),
            ),
            'reforma' => array(
                'titulo' => 'Reforma presupuestaria',
                'tablas' => array(
                    'presupuesto.pre_reforma'     => 'id_reforma',
                    'presupuesto.pre_reforma_det' => 'id_reforma',
                ),
            ),
            'accion' => array(
                'titulo' => 'Acción de personal',
                'tablas' => array('public.nom_accion' => 'id_accion'),
            ),
            'spi' => array(
                'titulo' => 'Pago SPI',
                'tablas' => array(
                    'tesoreria.spi_mov'     => 'id_spi',
                    'tesoreria.spi_mov_det' => 'id_spi',
                ),
            ),
        );
    }

    /** Nombre legible de cada tabla auditada. */
    function kauditoria_tablas()
    {
        return array(
            'public.co_asiento'            => 'Asientos contables',
            'public.co_asientod'           => 'Asientos - detalle (cuentas y montos)',
            'public.co_asiento_aux'        => 'Asientos - auxiliares / beneficiarios',
            'public.co_plan_ctas'          => 'Plan de cuentas',
            'public.co_compras'            => 'Compras / retenciones',
            'presupuesto.pre_tramite'      => 'Trámites / certificaciones',
            'presupuesto.pre_tramite_det'  => 'Trámites - partidas',
            'presupuesto.pre_reforma'      => 'Reformas presupuestarias',
            'presupuesto.pre_reforma_det'  => 'Reformas - detalle',
            'public.par_ciu'               => 'Proveedores / clientes / empleados',
            'public.nom_personal'          => 'Personal',
            'public.nom_cargo'             => 'Cargos',
            'public.nom_accion'            => 'Acciones de personal',
            'public.nom_accion_lista'      => 'Tipos de acción de personal',
            'public.nom_config'            => 'Configuración de nómina',
            'public.nom_rol_pago'          => 'Rol de pagos',
            'public.nom_rol_pagod'         => 'Rol de pagos - detalle',
            'tesoreria.spi_mov'            => 'SPI',
            'tesoreria.spi_mov_det'        => 'SPI - detalle',
            'public.par_usuario'           => 'Usuarios del sistema',
        );
    }

    /** ¿El usuario de la sesión puede consultar la auditoría? */
    function kauditoria_permitida($bd)
    {
        if (empty($_SESSION['usuario']) || empty($_SESSION['login'])) {
            return false;
        }
        $seguridad = function_exists('kconfig') ? kconfig('seguridad') : array();
        if (!empty($seguridad['auditoria_usuarios'])) {
            foreach ((array) $seguridad['auditoria_usuarios'] as $u) {
                if (strcasecmp(trim($u), trim($_SESSION['login'])) === 0) {
                    return true;
                }
            }
        }
        $r = @kpg_query_params($bd->link(),
            "SELECT 1 FROM par_rol r JOIN par_modulos m ON m.id_par_modulo = r.id_par_modulo
              WHERE r.idusuario = $1 AND m.vinculo ILIKE '%auditoria%' LIMIT 1",
            array((int) $_SESSION['usuario']));
        return $r && kpg_num_rows($r) > 0;
    }

    /**
     * Botón "Historial" para la barra de herramientas de una pantalla
     * (Obj.boton.php, ToolMenuDiv). Devuelve null si el usuario no tiene permiso.
     *   if ($b = kauditoria_boton($this->bd, 'asiento', 'id_asiento')) { $ToolArray[] = $b; }
     */
    function kauditoria_boton($bd, $grupo, $campo)
    {
        static $permitido = null;
        if ($permitido === null) {
            $permitido = kauditoria_permitida($bd);
        }
        if (!$permitido) {
            return null;
        }
        return array('boton' => 'Historial de cambios (auditoría)',
                     'evento' => "javascript:kHistorial('" . $grupo . "','" . $campo . "')",
                     'grafico' => 'glyphicon glyphicon-time', 'type' => 'button_default');
    }

    /** ¿Está instalada la auditoría y el usuario de la aplicación puede leerla? */
    function kauditoria_instalada($bd)
    {
        $r = @kpg_query($bd->link(), "SELECT has_table_privilege('auditoria.registro', 'SELECT')");
        if (!$r) {
            return false;
        }
        $f = kpg_fetch_row($r);
        return $f && $f[0] === 't';
    }

    /**
     * Busca cambios. Filtros (todos opcionales):
     *   grupo + id     historial de un registro de negocio (ver kauditoria_grupos)
     *   tabla          'esquema.tabla'
     *   registro       valor de la clave del registro
     *   usuario        login (parcial)
     *   desde, hasta   fechas AAAA-MM-DD
     *   texto          busca en los valores anteriores y nuevos
     * Devuelve array('filas' => [...], 'limite' => bool) o false si hubo error.
     */
    function kauditoria_buscar($bd, array $f, $limite = 500)
    {
        $donde  = array();
        $params = array();
        $p = function ($valor) use (&$params) {
            $params[] = $valor;
            return '$' . count($params);
        };

        $grupos = kauditoria_grupos();
        if (!empty($f['grupo']) && isset($grupos[$f['grupo']]) && isset($f['id']) && trim($f['id']) !== '') {
            $id = trim($f['id']);
            $o  = array();
            foreach ($grupos[$f['grupo']]['tablas'] as $tabla => $columna) {
                list($esq, $tab) = explode('.', $tabla);
                $o[] = '(r.esquema = ' . $p($esq) . ' AND r.tabla = ' . $p($tab)
                     . ' AND btrim(coalesce(r.antes, r.despues) ->> ' . $p($columna) . ') = ' . $p($id) . ')';
            }
            $donde[] = '(' . implode(' OR ', $o) . ')';
        }
        if (!empty($f['tabla']) && strpos($f['tabla'], '.') !== false) {
            list($esq, $tab) = explode('.', $f['tabla'], 2);
            $donde[] = 'r.esquema = ' . $p($esq) . ' AND r.tabla = ' . $p($tab);
        }
        if (isset($f['registro']) && trim($f['registro']) !== '') {
            $donde[] = 'r.registro_id = ' . $p(trim($f['registro']));
        }
        if (!empty($f['usuario'])) {
            $donde[] = 'r.usuario ILIKE ' . $p('%' . $bd->like_literal(trim($f['usuario'])) . '%');
        }
        if (!empty($f['desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['desde'])) {
            $donde[] = 'r.fecha >= (' . $p($f['desde']) . '::date)::timestamp AT TIME ZONE ' . $p(date_default_timezone_get());
        }
        if (!empty($f['hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['hasta'])) {
            $donde[] = 'r.fecha < (' . $p($f['hasta']) . '::date + 1)::timestamp AT TIME ZONE ' . $p(date_default_timezone_get());
        }
        if (!empty($f['texto'])) {
            $t = $p('%' . $bd->like_literal(trim($f['texto'])) . '%');
            $donde[] = '(r.cambios::text ILIKE ' . $t . ' OR r.antes::text ILIKE ' . $t
                     . ' OR r.despues::text ILIKE ' . $t . ' OR r.registro_id ILIKE ' . $t . ')';
        }
        // Hora local de la aplicación (la base guarda la fecha con zona horaria)
        $zona = $p(date_default_timezone_get());
        $sql = "SELECT r.id, to_char(r.fecha AT TIME ZONE " . $zona . ", 'YYYY-MM-DD HH24:MI:SS') AS fecha, r.usuario, r.ip, r.modulo,
                       r.operacion, r.esquema || '.' || r.tabla AS tabla, r.registro_id,
                       r.cambios, r.antes, r.despues, r.motivo
                  FROM auditoria.registro r"
             . ($donde ? ' WHERE ' . implode(' AND ', $donde) : '')
             . ' ORDER BY r.id DESC LIMIT ' . ((int) $limite + 1);

        $res = @kpg_query_params($bd->link(), $sql, $params);
        if (!$res) {
            error_log('[auditoria] ' . pg_last_error($bd->link()));
            return false;
        }
        $nombres = kauditoria_tablas();
        $filas = array();
        while ($r = kpg_fetch_assoc($res)) {
            $r['tabla_nombre'] = isset($nombres[$r['tabla']]) ? $nombres[$r['tabla']] : $r['tabla'];
            $r['detalle'] = kauditoria_detalle($r);
            unset($r['cambios'], $r['antes'], $r['despues']);
            $filas[] = $r;
        }
        $mas = count($filas) > $limite;
        return array('filas' => array_slice($filas, 0, $limite), 'limite' => $mas);
    }

    /** Lista de campos para mostrar: [campo, anterior, nuevo]. */
    function kauditoria_detalle(array $r)
    {
        $limpio = function ($v) {
            if ($v === null) {
                return null;
            }
            return is_scalar($v) ? rtrim((string) $v) : json_encode($v, JSON_UNESCAPED_UNICODE);
        };
        $campos = array();
        if ($r['operacion'] === 'U') {
            foreach ((array) json_decode($r['cambios'], true) as $campo => $c) {
                $campos[] = array($campo, $limpio($c['antes']), $limpio($c['despues']));
            }
        } else {
            $datos = (array) json_decode($r['operacion'] === 'I' ? $r['despues'] : $r['antes'], true);
            foreach ($datos as $campo => $v) {
                $v = $limpio($v);
                if ($v !== null && $v !== '') {
                    $campos[] = $r['operacion'] === 'I' ? array($campo, null, $v) : array($campo, $v, null);
                }
            }
        }
        return $campos;
    }
}
