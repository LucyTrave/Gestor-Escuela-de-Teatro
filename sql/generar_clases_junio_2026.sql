-- ═══════════════════════════════════════════════════════════════════════════
-- generar_clases_junio_2026.sql
--
-- Genera una clase por cada semana de junio 2026 para cada grupo activo,
-- respetando su dia_semana y horario.
-- Usa INSERT IGNORE para no duplicar si ya existen.
--
-- EJECUTAR EN phpMyAdmin o cliente MySQL:
--   SOURCE /ruta/a/sql/generar_clases_junio_2026.sql
-- ═══════════════════════════════════════════════════════════════════════════

USE punto_de_partida;

-- ─── Vista previa: grupos activos que recibirán clases ───────────────────────
SELECT
    g.id,
    g.nombre,
    g.dia_semana,
    g.hora_inicio,
    g.hora_fin,
    g.fecha_inicio_curso,
    g.fecha_fin_curso
FROM grupo g
WHERE g.activo = TRUE
  AND g.fecha_fin_curso >= '2026-06-01'
ORDER BY g.dia_semana, g.hora_inicio;

-- ─── Insertar clases de junio 2026 ──────────────────────────────────────────
INSERT IGNORE INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo, estado)
SELECT
    g.id        AS grupo_id,
    g.sala_id,
    d.fecha,
    g.hora_inicio,
    g.hora_fin,
    COALESCE(
        (SELECT c2.cupo_maximo
         FROM clase c2
         WHERE c2.grupo_id = g.id
         ORDER BY c2.id DESC
         LIMIT 1),
        16
    )           AS cupo_maximo,
    'programada' AS estado
FROM grupo g
JOIN (
    -- Todos los días de junio 2026
    SELECT DATE('2026-06-01') + INTERVAL n DAY AS fecha
    FROM (
        SELECT  0 n UNION SELECT  1 UNION SELECT  2 UNION SELECT  3 UNION SELECT  4
        UNION SELECT  5 UNION SELECT  6 UNION SELECT  7 UNION SELECT  8 UNION SELECT  9
        UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
        UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
        UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
        UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
    ) nums
    WHERE DATE('2026-06-01') + INTERVAL n DAY <= '2026-06-30'
) d ON (
    (g.dia_semana = 'lunes'     AND DAYOFWEEK(d.fecha) = 2) OR
    (g.dia_semana = 'martes'    AND DAYOFWEEK(d.fecha) = 3) OR
    (g.dia_semana = 'miercoles' AND DAYOFWEEK(d.fecha) = 4) OR
    (g.dia_semana = 'jueves'    AND DAYOFWEEK(d.fecha) = 5) OR
    (g.dia_semana = 'viernes'   AND DAYOFWEEK(d.fecha) = 6) OR
    (g.dia_semana = 'sabado'    AND DAYOFWEEK(d.fecha) = 7) OR
    (g.dia_semana = 'domingo'   AND DAYOFWEEK(d.fecha) = 1)
)
WHERE g.activo = TRUE
  AND g.fecha_fin_curso >= '2026-06-01';

-- ─── Verificación ────────────────────────────────────────────────────────────
SELECT
    g.nombre AS grupo,
    g.dia_semana,
    COUNT(c.id) AS clases_junio
FROM clase c
INNER JOIN grupo g ON g.id = c.grupo_id
WHERE c.fecha BETWEEN '2026-06-01' AND '2026-06-30'
  AND c.estado = 'programada'
GROUP BY g.id, g.nombre, g.dia_semana
ORDER BY g.dia_semana, g.hora_inicio;
