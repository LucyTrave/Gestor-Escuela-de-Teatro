-- ═══════════════════════════════════════════════════════════════════════════════
-- datos_prueba_alumno.sql
-- Datos de prueba para la vista del alumno.
--
-- REQUISITO: ejecutar setup.php primero.
-- USUARIO: juan@mail.com / 1234
--
-- CONTENIDO:
--   - 2 grupos donde Juan está inscrito (teatro lunes + impro martes)
--   - 8 clases por grupo con fechas en el día correcto
--   - 1 clase con fecha de hoy (teatro, para probar regla 24h)
--   - 1 grupo de impro donde Juan NO está inscrito (jueves, para recuperar)
--   - 1 grupo de impro LLENO con cupo 2 (sábado, para probar "sin plazas")
-- ═══════════════════════════════════════════════════════════════════════════════

USE punto_de_partida;

SET @profesor_luis = '00000002B';
SET @alumno_juan   = (SELECT id FROM alumno WHERE nombre = 'Juan' LIMIT 1);

-- ═══════════════════════════════════════════════════════════════════════════
-- LIMPIEZA: borra los datos creados por una ejecución anterior de este seed.
-- Así el script es idempotente: puedes ejecutarlo varias veces sin errores
-- de "Entrada duplicada" en uk_alumno_grupo ni claves foráneas.
-- Solo borra los grupos de este seed por nombre exacto; no toca los grupos
-- iniciales de setup.php ni los que cree el administrador.
-- ═══════════════════════════════════════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 0;

-- Tokens ligados a asistencias de clases de los 4 grupos del seed.
DELETE FROM token WHERE asistencia_origen_id IN (
    SELECT a.id FROM asistencia a
    WHERE a.clase_id IN (
        SELECT c.id FROM clase c
        WHERE c.grupo_id IN (
            SELECT g.id FROM grupo g
            WHERE g.profesor_id = @profesor_luis
              AND g.nombre IN ('Iniciación Teatro', 'Impro Nivel 1', 'Impro Nivel 2', 'Impro Especial')
        )
    )
);

-- Asistencias de las clases del seed.
DELETE FROM asistencia WHERE clase_id IN (
    SELECT c.id FROM clase c
    WHERE c.grupo_id IN (
        SELECT g.id FROM grupo g
        WHERE g.profesor_id = @profesor_luis
          AND g.nombre IN ('Iniciación Teatro', 'Impro Nivel 1', 'Impro Nivel 2', 'Impro Especial')
    )
);

-- Clases de los 4 grupos del seed.
DELETE FROM clase WHERE grupo_id IN (
    SELECT g.id FROM grupo g
    WHERE g.profesor_id = @profesor_luis
      AND g.nombre IN ('Iniciación Teatro', 'Impro Nivel 1', 'Impro Nivel 2', 'Impro Especial')
);

-- Inscripciones alumno-grupo de esos grupos.
DELETE FROM alumno_grupo WHERE grupo_id IN (
    SELECT g.id FROM grupo g
    WHERE g.profesor_id = @profesor_luis
      AND g.nombre IN ('Iniciación Teatro', 'Impro Nivel 1', 'Impro Nivel 2', 'Impro Especial')
);

-- Los 4 grupos del seed.
DELETE FROM grupo
WHERE profesor_id = @profesor_luis
  AND nombre IN ('Iniciación Teatro', 'Impro Nivel 1', 'Impro Nivel 2', 'Impro Especial');

-- Alumnos Pablo y Ana (los que se crearon solo para llenar el grupo).
DELETE FROM alumno_grupo WHERE alumno_id IN (
    SELECT id FROM alumno WHERE usuario_id IN ('00000004D', '00000005E')
);
DELETE FROM alumno  WHERE usuario_id IN ('00000004D', '00000005E');
DELETE FROM usuario WHERE id          IN ('00000004D', '00000005E');

SET FOREIGN_KEY_CHECKS = 1;

-- ─── GRUPO 1: Teatro (lunes, Juan inscrito) ─────────────────────────────────

INSERT INTO grupo (profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin, nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso)
VALUES (@profesor_luis, 3, 'Iniciación Teatro', 'lunes', '17:30:00', '19:30:00', 'iniciacion', 'teatro', '2025-2026', '2025-09-01', '2026-06-30');

SET @g_teatro = LAST_INSERT_ID();
INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES (@alumno_juan, @g_teatro);

SET @prox_lun = DATE_ADD(CURDATE(), INTERVAL (9 - DAYOFWEEK(CURDATE())) % 7 DAY);
SET @prox_lun = IF(@prox_lun = CURDATE(), DATE_ADD(@prox_lun, INTERVAL 7 DAY), @prox_lun);

INSERT INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo) VALUES
(@g_teatro, 3, CURDATE(),                                    '17:30:00', '19:30:00', 12),
(@g_teatro, 3, @prox_lun,                                    '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 7 DAY),          '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 14 DAY),         '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 21 DAY),         '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 28 DAY),         '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 35 DAY),         '17:30:00', '19:30:00', 12),
(@g_teatro, 3, DATE_ADD(@prox_lun, INTERVAL 42 DAY),         '17:30:00', '19:30:00', 12);

-- ─── GRUPO 2: Impro Nivel 1 (martes, Juan inscrito) ────────────────────────

INSERT INTO grupo (profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin, nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso)
VALUES (@profesor_luis, 2, 'Impro Nivel 1', 'martes', '20:00:00', '22:00:00', 'iniciacion', 'improvisacion', '2025-2026', '2025-09-01', '2026-06-30');

SET @g_impro1 = LAST_INSERT_ID();
INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES (@alumno_juan, @g_impro1);

SET @prox_mar = DATE_ADD(CURDATE(), INTERVAL (10 - DAYOFWEEK(CURDATE())) % 7 DAY);
SET @prox_mar = IF(@prox_mar = CURDATE(), DATE_ADD(@prox_mar, INTERVAL 7 DAY), @prox_mar);

INSERT INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo) VALUES
(@g_impro1, 2, @prox_mar,                                    '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 7 DAY),          '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 14 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 21 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 28 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 35 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 42 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro1, 2, DATE_ADD(@prox_mar, INTERVAL 49 DAY),         '20:00:00', '22:00:00', 14);

-- ─── GRUPO 3: Impro Nivel 2 (jueves, Juan NO inscrito, para recuperar) ─────

INSERT INTO grupo (profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin, nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso)
VALUES (@profesor_luis, 5, 'Impro Nivel 2', 'jueves', '20:00:00', '22:00:00', 'intermedio', 'improvisacion', '2025-2026', '2025-09-01', '2026-06-30');

SET @g_impro2 = LAST_INSERT_ID();

SET @prox_jue = DATE_ADD(CURDATE(), INTERVAL (12 - DAYOFWEEK(CURDATE())) % 7 DAY);
SET @prox_jue = IF(@prox_jue = CURDATE(), DATE_ADD(@prox_jue, INTERVAL 7 DAY), @prox_jue);

INSERT INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo) VALUES
(@g_impro2, 5, @prox_jue,                                    '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 7 DAY),          '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 14 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 21 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 28 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 35 DAY),         '20:00:00', '22:00:00', 14),
(@g_impro2, 5, DATE_ADD(@prox_jue, INTERVAL 42 DAY),         '20:00:00', '22:00:00', 14);

-- ─── GRUPO 4: Impro Especial (sábado, cupo 2, LLENO) ───────────────────────

SET @hash_comun = (SELECT password_hash FROM usuario WHERE id = '00000001A' LIMIT 1);

INSERT IGNORE INTO usuario (id, email, password_hash, rol) VALUES
('00000004D', 'pablo@mail.com', @hash_comun, 'alumno'),
('00000005E', 'ana@mail.com',   @hash_comun, 'alumno');

INSERT IGNORE INTO alumno (usuario_id, nombre, apellidos, email, estado) VALUES
('00000004D', 'Pablo', 'Test', 'pablo@mail.com', 'matriculado'),
('00000005E', 'Ana',   'Test', 'ana@mail.com',   'matriculado');

SET @alumno_pablo = (SELECT id FROM alumno WHERE usuario_id = '00000004D' LIMIT 1);
SET @alumno_ana   = (SELECT id FROM alumno WHERE usuario_id = '00000005E' LIMIT 1);

INSERT INTO grupo (profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin, nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso)
VALUES (@profesor_luis, 6, 'Impro Especial', 'sabado', '11:00:00', '13:00:00', 'iniciacion', 'improvisacion', '2025-2026', '2025-09-01', '2026-06-30');

SET @g_lleno = LAST_INSERT_ID();

INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES
(@alumno_pablo, @g_lleno),
(@alumno_ana,   @g_lleno);

SET @prox_sab = DATE_ADD(CURDATE(), INTERVAL (14 - DAYOFWEEK(CURDATE())) % 7 DAY);
SET @prox_sab = IF(@prox_sab = CURDATE(), DATE_ADD(@prox_sab, INTERVAL 7 DAY), @prox_sab);

INSERT INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo) VALUES
(@g_lleno, 6, @prox_sab, '11:00:00', '13:00:00', 2);

-- ─── TOKENS DE PRUEBA ─────────────────────────────────────────────────────
-- Generamos avisos válidos (+24h) para que cada uno produzca un token.
-- Así el profesor verá en sus tarjetas el icono 🎟️ con la cuenta de alumnos
-- de cada grupo que tienen token disponible para recuperar.

-- Juan avisa que no podrá ir a una clase futura de teatro (genera 1 token).
SET @clase_teatro_futura = (
    SELECT id FROM clase
    WHERE grupo_id = @g_teatro AND fecha > CURDATE()
    ORDER BY fecha ASC LIMIT 1
);
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
VALUES (@alumno_juan, @clase_teatro_futura, 'avisado', NOW(), TRUE);
SET @asistencia_juan_1 = LAST_INSERT_ID();
INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
VALUES (@alumno_juan, @asistencia_juan_1, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE);

-- Juan avisa también de otra clase futura de impro (segundo token).
SET @clase_impro1_futura = (
    SELECT id FROM clase
    WHERE grupo_id = @g_impro1 AND fecha > CURDATE()
    ORDER BY fecha ASC LIMIT 1
);
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
VALUES (@alumno_juan, @clase_impro1_futura, 'avisado', NOW(), TRUE);
SET @asistencia_juan_2 = LAST_INSERT_ID();
INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
VALUES (@alumno_juan, @asistencia_juan_2, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE);

-- Pablo (en el grupo lleno) también tiene un token disponible.
SET @clase_lleno_futura = (
    SELECT id FROM clase
    WHERE grupo_id = @g_lleno AND fecha > CURDATE()
    ORDER BY fecha ASC LIMIT 1
);
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
VALUES (@alumno_pablo, @clase_lleno_futura, 'avisado', NOW(), TRUE);
SET @asistencia_pablo = LAST_INSERT_ID();
INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
VALUES (@alumno_pablo, @asistencia_pablo, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE);

-- ─── VERIFICACIÓN ───────────────────────────────────────────────────────────

SELECT '✓ Clases de Juan' AS test, COUNT(*) AS total
FROM clase c INNER JOIN alumno_grupo ag ON ag.grupo_id = c.grupo_id
WHERE ag.alumno_id = @alumno_juan AND ag.activo = TRUE
  AND c.fecha >= CURDATE() AND c.estado = 'programada';

SELECT '✓ Clases hoy' AS test, COUNT(*) AS total
FROM clase c INNER JOIN alumno_grupo ag ON ag.grupo_id = c.grupo_id
WHERE ag.alumno_id = @alumno_juan AND ag.activo = TRUE
  AND c.fecha = CURDATE() AND c.estado = 'programada';

SELECT '✓ Impro para recuperar' AS test, COUNT(*) AS total
FROM clase c INNER JOIN grupo g ON g.id = c.grupo_id
WHERE g.tipo = 'improvisacion' AND c.fecha >= CURDATE() AND c.estado = 'programada'
  AND c.grupo_id NOT IN (SELECT grupo_id FROM alumno_grupo WHERE alumno_id = @alumno_juan AND activo = TRUE);

SELECT '✓ Clase llena' AS test, g.nombre,
       c.cupo_maximo,
       (SELECT COUNT(*) FROM alumno_grupo WHERE grupo_id = g.id AND activo = TRUE) AS inscritos
FROM grupo g INNER JOIN clase c ON c.grupo_id = g.id
WHERE g.nombre = 'Impro Especial' LIMIT 1;

SELECT '✓ Fechas correctas' AS test, c.fecha, DAYNAME(c.fecha) AS dia_real, g.nombre, g.dia_semana
FROM clase c INNER JOIN grupo g ON g.id = c.grupo_id
WHERE c.fecha >= CURDATE() AND c.estado = 'programada'
ORDER BY c.fecha LIMIT 10;
