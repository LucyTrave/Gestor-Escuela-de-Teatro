-- ═══════════════════════════════════════════════════════════════════════════
-- 30_alumnos_demo.sql
--
-- Crea 30 alumnos demo con nombres y apellidos únicos, los reparte entre los
-- 4 grupos del seed (Iniciación Teatro, Impro Nivel 1, Impro Nivel 2, Impro
-- Especial) y genera tokens para algunos de ellos.
--
-- REQUISITO: ejecutar antes setup.php y datos_prueba_alumno.sql.
-- IDEMPOTENTE: limpia primero los alumnos demo previos (IDs A0001..A0030).
-- ═══════════════════════════════════════════════════════════════════════════

USE punto_de_partida;

SET @profesor_luis = '00000002B';

-- ─── LIMPIEZA: borrar datos demo previos si ya existían ──────────────────
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM token       WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id LIKE 'DEMO%');
DELETE FROM asistencia  WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id LIKE 'DEMO%');
DELETE FROM alumno_grupo WHERE alumno_id IN (SELECT id FROM alumno WHERE usuario_id LIKE 'DEMO%');
DELETE FROM alumno      WHERE usuario_id LIKE 'DEMO%';
DELETE FROM usuario     WHERE id LIKE 'DEMO%';
SET FOREIGN_KEY_CHECKS = 1;

-- ─── CREAR USUARIOS + ALUMNOS ─────────────────────────────────────────────
SET @hash = (SELECT password_hash FROM usuario WHERE id = '00000001A' LIMIT 1);

INSERT INTO usuario (id, email, password_hash, rol) VALUES
('DEMO01', 'ana.martinez@mail.com',     @hash, 'alumno'),
('DEMO02', 'carlos.lopez@mail.com',     @hash, 'alumno'),
('DEMO03', 'lucia.fernandez@mail.com',  @hash, 'alumno'),
('DEMO04', 'pedro.gomez@mail.com',      @hash, 'alumno'),
('DEMO05', 'sofia.ruiz@mail.com',       @hash, 'alumno'),
('DEMO06', 'miguel.diaz@mail.com',      @hash, 'alumno'),
('DEMO07', 'clara.hernandez@mail.com',  @hash, 'alumno'),
('DEMO08', 'pablo.jimenez@mail.com',    @hash, 'alumno'),
('DEMO09', 'elena.moreno@mail.com',     @hash, 'alumno'),
('DEMO10', 'david.alvarez@mail.com',    @hash, 'alumno'),
('DEMO11', 'marta.romero@mail.com',     @hash, 'alumno'),
('DEMO12', 'javier.serrano@mail.com',   @hash, 'alumno'),
('DEMO13', 'laura.sanchez@mail.com',    @hash, 'alumno'),
('DEMO14', 'sergio.gutierrez@mail.com', @hash, 'alumno'),
('DEMO15', 'patricia.castro@mail.com',  @hash, 'alumno'),
('DEMO16', 'raul.iglesias@mail.com',    @hash, 'alumno'),
('DEMO17', 'irene.molina@mail.com',     @hash, 'alumno'),
('DEMO18', 'alvaro.delgado@mail.com',   @hash, 'alumno'),
('DEMO19', 'noelia.ortega@mail.com',    @hash, 'alumno'),
('DEMO20', 'hugo.vargas@mail.com',      @hash, 'alumno'),
('DEMO21', 'rocio.medina@mail.com',     @hash, 'alumno'),
('DEMO22', 'daniel.santos@mail.com',    @hash, 'alumno'),
('DEMO23', 'cristina.parra@mail.com',   @hash, 'alumno'),
('DEMO24', 'oscar.cabrera@mail.com',    @hash, 'alumno'),
('DEMO25', 'beatriz.peña@mail.com',     @hash, 'alumno'),
('DEMO26', 'andres.lara@mail.com',      @hash, 'alumno'),
('DEMO27', 'natalia.vidal@mail.com',    @hash, 'alumno'),
('DEMO28', 'ivan.gallego@mail.com',     @hash, 'alumno'),
('DEMO29', 'silvia.rios@mail.com',      @hash, 'alumno'),
('DEMO30', 'jorge.bravo@mail.com',      @hash, 'alumno');

INSERT INTO alumno (usuario_id, nombre, apellidos, email, estado) VALUES
('DEMO01', 'Ana',       'Martínez',          'ana.martinez@mail.com',     'matriculado'),
('DEMO02', 'Carlos',    'López',             'carlos.lopez@mail.com',     'matriculado'),
('DEMO03', 'Lucía',     'Fernández',         'lucia.fernandez@mail.com',  'matriculado'),
('DEMO04', 'Pedro',     'Gómez',             'pedro.gomez@mail.com',      'matriculado'),
('DEMO05', 'Sofía',     'Ruiz',              'sofia.ruiz@mail.com',       'matriculado'),
('DEMO06', 'Miguel',    'Díaz',              'miguel.diaz@mail.com',      'matriculado'),
('DEMO07', 'Clara',     'Hernández',         'clara.hernandez@mail.com',  'matriculado'),
('DEMO08', 'Pablo',     'Jiménez',           'pablo.jimenez@mail.com',    'matriculado'),
('DEMO09', 'Elena',     'Moreno',            'elena.moreno@mail.com',     'matriculado'),
('DEMO10', 'David',     'Álvarez',           'david.alvarez@mail.com',    'matriculado'),
('DEMO11', 'Marta',     'Romero',            'marta.romero@mail.com',     'matriculado'),
('DEMO12', 'Javier',    'Serrano',           'javier.serrano@mail.com',   'matriculado'),
('DEMO13', 'Laura',     'Sánchez',           'laura.sanchez@mail.com',    'matriculado'),
('DEMO14', 'Sergio',    'Gutiérrez',         'sergio.gutierrez@mail.com', 'matriculado'),
('DEMO15', 'Patricia',  'Castro',            'patricia.castro@mail.com',  'matriculado'),
('DEMO16', 'Raúl',      'Iglesias',          'raul.iglesias@mail.com',    'matriculado'),
('DEMO17', 'Irene',     'Molina',            'irene.molina@mail.com',     'matriculado'),
('DEMO18', 'Álvaro',    'Delgado',           'alvaro.delgado@mail.com',   'matriculado'),
('DEMO19', 'Noelia',    'Ortega',            'noelia.ortega@mail.com',    'matriculado'),
('DEMO20', 'Hugo',      'Vargas',            'hugo.vargas@mail.com',      'matriculado'),
('DEMO21', 'Rocío',     'Medina',            'rocio.medina@mail.com',     'matriculado'),
('DEMO22', 'Daniel',    'Santos',            'daniel.santos@mail.com',    'matriculado'),
('DEMO23', 'Cristina',  'Parra',             'cristina.parra@mail.com',   'matriculado'),
('DEMO24', 'Óscar',     'Cabrera',           'oscar.cabrera@mail.com',    'matriculado'),
('DEMO25', 'Beatriz',   'Peña',              'beatriz.pena@mail.com',     'matriculado'),
('DEMO26', 'Andrés',    'Lara',              'andres.lara@mail.com',      'matriculado'),
('DEMO27', 'Natalia',   'Vidal',             'natalia.vidal@mail.com',    'matriculado'),
('DEMO28', 'Iván',      'Gallego',           'ivan.gallego@mail.com',     'matriculado'),
('DEMO29', 'Silvia',    'Ríos',              'silvia.rios@mail.com',      'matriculado'),
('DEMO30', 'Jorge',     'Bravo',             'jorge.bravo@mail.com',      'matriculado');

-- ─── ASIGNAR A LOS 4 GRUPOS DEL SEED ──────────────────────────────────────
-- Capturamos los IDs de los grupos (sin asumir un orden de IDs).
SET @g_teatro  = (SELECT id FROM grupo WHERE profesor_id = @profesor_luis AND nombre = 'Iniciación Teatro' LIMIT 1);
SET @g_impro1  = (SELECT id FROM grupo WHERE profesor_id = @profesor_luis AND nombre = 'Impro Nivel 1'     LIMIT 1);
SET @g_impro2  = (SELECT id FROM grupo WHERE profesor_id = @profesor_luis AND nombre = 'Impro Nivel 2'     LIMIT 1);
SET @g_lleno   = (SELECT id FROM grupo WHERE profesor_id = @profesor_luis AND nombre = 'Impro Especial'    LIMIT 1);

-- Iniciación Teatro (10 alumnos: DEMO01..DEMO10).
INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES
((SELECT id FROM alumno WHERE usuario_id='DEMO01'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO02'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO03'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO04'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO05'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO06'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO07'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO08'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO09'), @g_teatro),
((SELECT id FROM alumno WHERE usuario_id='DEMO10'), @g_teatro);

-- Impro Nivel 1 (10 alumnos: DEMO11..DEMO20).
INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES
((SELECT id FROM alumno WHERE usuario_id='DEMO11'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO12'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO13'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO14'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO15'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO16'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO17'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO18'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO19'), @g_impro1),
((SELECT id FROM alumno WHERE usuario_id='DEMO20'), @g_impro1);

-- Impro Nivel 2 (intermedio): 8 alumnos (DEMO21..DEMO28).
INSERT INTO alumno_grupo (alumno_id, grupo_id) VALUES
((SELECT id FROM alumno WHERE usuario_id='DEMO21'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO22'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO23'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO24'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO25'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO26'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO27'), @g_impro2),
((SELECT id FROM alumno WHERE usuario_id='DEMO28'), @g_impro2);

-- Impro Especial: solo aceptaba cupo 2 → ya estaban Pablo y Ana del seed.
-- Añadimos los 2 últimos demo en este grupo para tener un grupo casi lleno.
INSERT IGNORE INTO alumno_grupo (alumno_id, grupo_id) VALUES
((SELECT id FROM alumno WHERE usuario_id='DEMO29'), @g_lleno),
((SELECT id FROM alumno WHERE usuario_id='DEMO30'), @g_lleno);

-- ─── GENERAR TOKENS PARA UNOS CUANTOS ALUMNOS ────────────────────────────
-- Estos avisaron con +24h de antelación a alguna clase futura, así que tienen
-- un token disponible para asistir a otra clase como compensación.
SET @clase_teatro_fut = (SELECT id FROM clase WHERE grupo_id = @g_teatro AND fecha > CURDATE() ORDER BY fecha LIMIT 1);
SET @clase_impro1_fut = (SELECT id FROM clase WHERE grupo_id = @g_impro1 AND fecha > CURDATE() ORDER BY fecha LIMIT 1);
SET @clase_impro2_fut = (SELECT id FROM clase WHERE grupo_id = @g_impro2 AND fecha > CURDATE() ORDER BY fecha LIMIT 1);

-- 4 alumnos con token del grupo Teatro.
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
SELECT id, @clase_teatro_fut, 'avisado', NOW(), TRUE FROM alumno WHERE usuario_id IN ('DEMO01','DEMO03','DEMO05','DEMO07');

INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
SELECT a.id, asi.id, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE
FROM alumno a
INNER JOIN asistencia asi ON asi.alumno_id = a.id AND asi.clase_id = @clase_teatro_fut
WHERE a.usuario_id IN ('DEMO01','DEMO03','DEMO05','DEMO07');

-- 3 alumnos con token del grupo Impro Nivel 1.
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
SELECT id, @clase_impro1_fut, 'avisado', NOW(), TRUE FROM alumno WHERE usuario_id IN ('DEMO11','DEMO14','DEMO17');

INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
SELECT a.id, asi.id, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE
FROM alumno a
INNER JOIN asistencia asi ON asi.alumno_id = a.id AND asi.clase_id = @clase_impro1_fut
WHERE a.usuario_id IN ('DEMO11','DEMO14','DEMO17');

-- 2 alumnos con token del grupo Impro Nivel 2.
INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
SELECT id, @clase_impro2_fut, 'avisado', NOW(), TRUE FROM alumno WHERE usuario_id IN ('DEMO22','DEMO25');

INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
SELECT a.id, asi.id, DATE_ADD(CURDATE(), INTERVAL 60 DAY), FALSE
FROM alumno a
INNER JOIN asistencia asi ON asi.alumno_id = a.id AND asi.clase_id = @clase_impro2_fut
WHERE a.usuario_id IN ('DEMO22','DEMO25');

-- ─── VERIFICACIÓN ──────────────────────────────────────────────────────────
SELECT '✓ Alumnos demo creados'        AS test, COUNT(*) AS total FROM alumno WHERE usuario_id LIKE 'DEMO%';
SELECT '✓ Inscripciones a grupos'      AS test, COUNT(*) AS total FROM alumno_grupo ag INNER JOIN alumno a ON a.id = ag.alumno_id WHERE a.usuario_id LIKE 'DEMO%' AND ag.activo = TRUE;
SELECT '✓ Alumnos con token activo'    AS test, COUNT(DISTINCT t.alumno_id) AS total FROM token t INNER JOIN alumno a ON a.id = t.alumno_id WHERE a.usuario_id LIKE 'DEMO%' AND t.usado = FALSE;
SELECT '✓ Distribución por grupo' AS test, g.nombre, COUNT(*) AS alumnos FROM alumno_grupo ag INNER JOIN grupo g ON g.id = ag.grupo_id INNER JOIN alumno a ON a.id = ag.alumno_id WHERE a.usuario_id LIKE 'DEMO%' AND ag.activo = TRUE GROUP BY g.id, g.nombre;
