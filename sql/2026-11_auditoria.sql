-- =============================================================================
-- Auditoría de cambios en tablas críticas
-- Base: db_kaipi        Requiere PostgreSQL 9.6 o superior
--
-- Un trigger en cada tabla auditada registra todo INSERT, UPDATE y DELETE,
-- venga de la pantalla que venga (las funciones de Db o SQL escrito a mano):
--   * quién (usuario de la aplicación, IP y pantalla, que Db::conectar()
--     informa a PostgreSQL con set_config), cuándo y en qué tabla/registro;
--   * en UPDATE: solo los campos que cambiaron, con su valor anterior y nuevo,
--     y además el registro completo como estaba antes;
--   * en INSERT el registro nuevo y en DELETE el registro eliminado.
--
-- La tabla auditoria.registro solo la escribe el trigger (SECURITY DEFINER):
-- el usuario de la aplicación no puede modificarla ni borrarla.
--
-- Ejecutar con un usuario administrador (dueño de la base), no con el de la
-- aplicación. Es seguro ejecutarlo más de una vez. Hacer respaldo antes.
--
-- Activar / desactivar en otras tablas:
--   SELECT auditoria.activar('public.mi_tabla');
--   SELECT auditoria.activar('public.mi_tabla', p_ocultas => 'clave');
--   SELECT auditoria.desactivar('public.mi_tabla');
-- Consultar:
--   SELECT * FROM auditoria.v_cambios WHERE tabla = 'public.par_ciu' AND registro_id = '1712345678';
-- =============================================================================

SET client_min_messages = warning;

BEGIN;

CREATE SCHEMA IF NOT EXISTS auditoria;

CREATE TABLE IF NOT EXISTS auditoria.registro (
    id           bigserial PRIMARY KEY,
    fecha        timestamptz NOT NULL DEFAULT now(),
    usuario      varchar(130),            -- login en la aplicación (o usuario de BD si no hay sesión)
    ip           varchar(100),
    modulo       varchar(250),            -- pantalla / script que hizo el cambio
    operacion    char(1)     NOT NULL,    -- I = alta, U = modificación, D = eliminación
    esquema      varchar(63) NOT NULL,
    tabla        varchar(63) NOT NULL,
    registro_id  varchar(250),            -- valor de la clave primaria (compuesta: separada por |)
    cambios      jsonb,                   -- U: {"campo": {"antes": .., "despues": ..}}
    antes        jsonb,                   -- U y D: registro completo antes del cambio
    despues      jsonb,                   -- I: registro nuevo
    motivo       varchar(500),            -- opcional, lo indica la aplicación (Db::auditoria_motivo)
    usuario_bd   varchar(63) NOT NULL DEFAULT current_user,
    transaccion  bigint      NOT NULL DEFAULT txid_current()
);

CREATE INDEX IF NOT EXISTS registro_tabla_id_idx ON auditoria.registro (esquema, tabla, registro_id);
CREATE INDEX IF NOT EXISTS registro_fecha_idx    ON auditoria.registro (fecha);
CREATE INDEX IF NOT EXISTS registro_usuario_idx  ON auditoria.registro (usuario, fecha);

-- Nadie fuera del dueño puede escribir, modificar ni borrar la auditoría
REVOKE ALL ON SCHEMA auditoria FROM PUBLIC;
REVOKE ALL ON ALL TABLES IN SCHEMA auditoria FROM PUBLIC;

-- -----------------------------------------------------------------------------
-- Función del trigger
--   TG_ARGV[0] columnas de la clave primaria (separadas por coma)
--   TG_ARGV[1] columnas cuyo valor no se guarda (p. ej. contraseñas): se ve "***"
--   TG_ARGV[2] columnas que no cuentan como cambio (fechas de modificación)
-- -----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION auditoria.fn_registrar() RETURNS trigger
LANGUAGE plpgsql SECURITY DEFINER SET search_path = pg_catalog, public AS $$
DECLARE
    v_pk      text[] := string_to_array(TG_ARGV[0], ',');
    v_ocultas text[] := string_to_array(coalesce(nullif(TG_ARGV[1], ''), ''), ',');
    v_ignorar text[] := string_to_array(coalesce(nullif(TG_ARGV[2], ''), ''), ',');
    v_antes   jsonb;
    v_despues jsonb;
    v_cambios jsonb;
    v_fila    jsonb;
    v_id      text;
    k         text;
BEGIN
    IF TG_OP IN ('UPDATE', 'DELETE') THEN v_antes   := to_jsonb(OLD); END IF;
    IF TG_OP IN ('UPDATE', 'INSERT') THEN v_despues := to_jsonb(NEW); END IF;

    IF TG_OP = 'UPDATE' THEN
        SELECT jsonb_object_agg(n.key, jsonb_build_object('antes', o.value, 'despues', n.value))
          INTO v_cambios
          FROM jsonb_each(v_despues) n
          JOIN jsonb_each(v_antes) o ON o.key = n.key
         WHERE n.value IS DISTINCT FROM o.value
           AND NOT (n.key = ANY (v_ignorar));
        IF v_cambios IS NULL THEN
            RETURN NULL;              -- se guardó sin cambiar nada: no se registra
        END IF;
        v_despues := NULL;            -- lo nuevo ya está en "cambios"
    END IF;

    -- Valores que no deben quedar en la auditoría (se indica que cambiaron)
    FOREACH k IN ARRAY v_ocultas LOOP
        IF v_antes   ? k THEN v_antes   := jsonb_set(v_antes,   ARRAY[k], '"***"'); END IF;
        IF v_despues ? k THEN v_despues := jsonb_set(v_despues, ARRAY[k], '"***"'); END IF;
        IF v_cambios ? k THEN v_cambios := jsonb_set(v_cambios, ARRAY[k], '{"antes": "***", "despues": "***"}'); END IF;
    END LOOP;

    v_fila := coalesce(to_jsonb(NEW), to_jsonb(OLD));
    SELECT string_agg(coalesce(btrim(v_fila ->> c), ''), '|' ORDER BY o)  -- btrim: columnas character(n)
      INTO v_id
      FROM unnest(v_pk) WITH ORDINALITY AS t(c, o);

    INSERT INTO auditoria.registro
           (usuario, ip, modulo, operacion, esquema, tabla, registro_id, cambios, antes, despues, motivo, usuario_bd)
    VALUES (coalesce(nullif(current_setting('kaipi.usuario', true), ''), session_user),
            nullif(current_setting('kaipi.ip', true), ''),
            nullif(current_setting('kaipi.modulo', true), ''),
            left(TG_OP, 1), TG_TABLE_SCHEMA, TG_TABLE_NAME, v_id,
            v_cambios, v_antes, v_despues,
            nullif(current_setting('kaipi.motivo', true), ''),
            session_user);
    RETURN NULL;
END;
$$;

-- -----------------------------------------------------------------------------
-- Activar / desactivar la auditoría de una tabla
-- -----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION auditoria.activar(
    p_tabla       regclass,
    p_ocultas     text DEFAULT '',
    p_ignorar     text DEFAULT 'modificacion,fmodificacion,fecha_modificacion',
    p_operaciones text DEFAULT 'INSERT OR UPDATE OR DELETE'
) RETURNS text LANGUAGE plpgsql AS $$
DECLARE
    v_pk text;
BEGIN
    SELECT string_agg(a.attname, ',' ORDER BY array_position(i.indkey::int2[], a.attnum))
      INTO v_pk
      FROM pg_index i
      JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY (i.indkey)
     WHERE i.indrelid = p_tabla AND i.indisprimary;
    IF v_pk IS NULL THEN
        RAISE EXCEPTION 'La tabla % no tiene clave primaria', p_tabla;
    END IF;
    IF p_operaciones !~ '^(INSERT|UPDATE|DELETE)( OR (INSERT|UPDATE|DELETE))*$' THEN
        RAISE EXCEPTION 'Operaciones no válidas: %', p_operaciones;
    END IF;
    EXECUTE format('DROP TRIGGER IF EXISTS zz_auditoria ON %s', p_tabla);
    EXECUTE format('CREATE TRIGGER zz_auditoria AFTER %s ON %s FOR EACH ROW '
                   'EXECUTE PROCEDURE auditoria.fn_registrar(%L, %L, %L)',
                   p_operaciones, p_tabla, v_pk, p_ocultas, p_ignorar);
    RETURN format('auditoría activa en %s (clave: %s; %s)', p_tabla, v_pk, p_operaciones);
END;
$$;

CREATE OR REPLACE FUNCTION auditoria.desactivar(p_tabla regclass) RETURNS text LANGUAGE plpgsql AS $$
BEGIN
    EXECUTE format('DROP TRIGGER IF EXISTS zz_auditoria ON %s', p_tabla);
    RETURN format('auditoría desactivada en %s', p_tabla);
END;
$$;

REVOKE ALL ON FUNCTION auditoria.activar(regclass, text, text, text) FROM PUBLIC;
REVOKE ALL ON FUNCTION auditoria.desactivar(regclass) FROM PUBLIC;

-- -----------------------------------------------------------------------------
-- Vista para consultar: una fila por campo modificado
-- -----------------------------------------------------------------------------
CREATE OR REPLACE VIEW auditoria.v_cambios AS
SELECT r.id, r.fecha, r.usuario, r.ip, r.modulo,
       CASE r.operacion WHEN 'I' THEN 'alta' WHEN 'U' THEN 'modificación' ELSE 'eliminación' END AS operacion,
       r.esquema || '.' || r.tabla AS tabla, r.registro_id,
       c.key AS campo, rtrim(c.value ->> 'antes') AS valor_anterior, rtrim(c.value ->> 'despues') AS valor_nuevo,
       r.motivo
  FROM auditoria.registro r
 CROSS JOIN LATERAL jsonb_each(r.cambios) c
 WHERE r.operacion = 'U'
UNION ALL
SELECT r.id, r.fecha, r.usuario, r.ip, r.modulo,
       CASE r.operacion WHEN 'I' THEN 'alta' ELSE 'eliminación' END,
       r.esquema || '.' || r.tabla, r.registro_id,
       NULL, NULL, NULL, r.motivo
  FROM auditoria.registro r
 WHERE r.operacion IN ('I', 'D');

-- -----------------------------------------------------------------------------
-- Tablas auditadas (primera etapa)
-- -----------------------------------------------------------------------------
-- Contabilidad
SELECT auditoria.activar('public.co_asiento');
SELECT auditoria.activar('public.co_asientod');
SELECT auditoria.activar('public.co_asiento_aux');
SELECT auditoria.activar('public.co_plan_ctas');
SELECT auditoria.activar('public.co_compras');
-- Presupuesto: certificaciones y reformas
SELECT auditoria.activar('presupuesto.pre_tramite');
SELECT auditoria.activar('presupuesto.pre_tramite_det');
SELECT auditoria.activar('presupuesto.pre_reforma');
SELECT auditoria.activar('presupuesto.pre_reforma_det');
-- Proveedores, clientes y empleados (datos y cuenta bancaria)
SELECT auditoria.activar('public.par_ciu');
SELECT auditoria.activar('public.nom_personal');
-- Talento humano: parámetros legales
SELECT auditoria.activar('public.nom_cargo');
SELECT auditoria.activar('public.nom_accion');
SELECT auditoria.activar('public.nom_accion_lista');
SELECT auditoria.activar('public.nom_config');
-- Nómina: el rol se genera masivamente; se auditan sus cambios y eliminaciones
SELECT auditoria.activar('public.nom_rol_pago');
SELECT auditoria.activar('public.nom_rol_pagod', p_operaciones => 'UPDATE OR DELETE');
-- Tesorería: pagos SPI
SELECT auditoria.activar('tesoreria.spi_mov');
SELECT auditoria.activar('tesoreria.spi_mov_det');
-- Usuarios del sistema (la clave nunca se guarda)
SELECT auditoria.activar('public.par_usuario', p_ocultas => 'clave');

COMMIT;
