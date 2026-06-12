-- ═══════════════════════════════════════════════════════════════════════════
-- limpiar_clases_duplicadas.sql
--
-- Limpia los datos sucios de la tabla `clase`:
--   1) Clases duplicadas exactas (mismo grupo + misma fecha + misma hora).
--   2) Solapamientos: clases distintas del mismo profesor que ocupan el mismo
--      tramo horario el mismo día (un profesor no puede dar 2 clases a la vez).
--   3) Solapamientos de sala (la misma sala no puede tener 2 clases a la vez).
--
-- Estrategia: deja la primera clase (ID más bajo) y elimina las siguientes.
-- Al final añade un UNIQUE KEY para que no vuelvan a aparecer duplicados.
-- ═══════════════════════════════════════════════════════════════════════════

USE punto_de_partida;

-- ─── 1) Limpieza: arrastra asistencias y tokens de las clases que vamos a borrar
SET FOREIGN_KEY_CHECKS = 0;

-- IDs de clases duplicadas (todas menos la primera de cada grupo/fecha/hora).
CREATE TEMPORARY TABLE _clases_borrar AS
SELECT c1.id
FROM clase c1
INNER JOIN clase c2
  ON c1.grupo_id    = c2.grupo_id
 AND c1.fecha       = c2.fecha
 AND c1.hora_inicio = c2.hora_inicio
 AND c1.id          > c2.id;

-- IDs de clases del mismo profesor que se solapan (mismo día, horarios cruzados).
INSERT IGNORE INTO _clases_borrar (id)
SELECT c1.id
FROM clase c1
INNER JOIN clase c2
INNER JOIN grupo g1 ON g1.id = c1.grupo_id
INNER JOIN grupo g2 ON g2.id = c2.grupo_id
 ON g1.profesor_id = g2.profesor_id
AND c1.fecha       = c2.fecha
AND NOT (c1.hora_fin <= c2.hora_inicio OR c1.hora_inicio >= c2.hora_fin)
AND c1.id          > c2.id;

-- IDs de clases en la misma sala que se solapan.
INSERT IGNORE INTO _clases_borrar (id)
SELECT c1.id
FROM clase c1
INNER JOIN clase c2
  ON c1.sala_id = c2.sala_id
 AND c1.fecha   = c2.fecha
 AND NOT (c1.hora_fin <= c2.hora_inicio OR c1.hora_inicio >= c2.hora_fin)
 AND c1.id      > c2.id;

-- Borrar tokens ligados a asistencias de esas clases.
DELETE t FROM token t
INNER JOIN asistencia a ON a.id = t.asistencia_origen_id
INNER JOIN _clases_borrar b ON b.id = a.clase_id;

-- Borrar asistencias de esas clases.
DELETE a FROM asistencia a
INNER JOIN _clases_borrar b ON b.id = a.clase_id;

-- Finalmente borrar las clases sobrantes.
DELETE c FROM clase c
INNER JOIN _clases_borrar b ON b.id = c.id;

DROP TEMPORARY TABLE _clases_borrar;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── 2) Constraint UNIQUE: impide volver a crear clases duplicadas a nivel BBDD
-- Si el constraint ya existe el ALTER falla. Lo eliminamos antes con IF EXISTS
-- (compatible con MySQL 8.0.19+; en versiones antiguas dará error, ignóralo).
ALTER TABLE clase
    DROP INDEX uk_clase_unica;

ALTER TABLE clase
    ADD CONSTRAINT uk_clase_unica UNIQUE (grupo_id, fecha, hora_inicio);

-- ─── 3) Verificación final
SELECT 'Total de clases tras la limpieza' AS info, COUNT(*) AS total FROM clase;

SELECT 'Duplicados pendientes' AS info, COUNT(*) AS total
FROM (
    SELECT grupo_id, fecha, hora_inicio
    FROM clase
    GROUP BY grupo_id, fecha, hora_inicio
    HAVING COUNT(*) > 1
) AS subq;

SELECT 'Solapamientos profesor pendientes' AS info, COUNT(*) AS total
FROM clase c1
INNER JOIN clase c2
INNER JOIN grupo g1 ON g1.id = c1.grupo_id
INNER JOIN grupo g2 ON g2.id = c2.grupo_id
ON g1.profesor_id = g2.profesor_id
 AND c1.fecha       = c2.fecha
 AND NOT (c1.hora_fin <= c2.hora_inicio OR c1.hora_inicio >= c2.hora_fin)
 AND c1.id          <> c2.id;
