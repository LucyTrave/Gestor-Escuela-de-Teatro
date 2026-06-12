-- ═══════════════════════════════════════════════════════════════════════════════
-- datos_invitado.sql
-- Crea una alumna en estado 'posible' con 1 token de prueba.
--
-- REQUISITO: ejecutar setup.php + migracion_token_prueba.sql primero.
-- USUARIO: maria@mail.com / 1234
--
-- NOTA: Gracias a la migración, el token de prueba no necesita asistencia
--       ficticia. Se crea limpio con asistencia_origen_id = NULL y tipo = 'prueba'.
-- ═══════════════════════════════════════════════════════════════════════════════

USE punto_de_partida;

-- 1. Crear usuario (reutiliza el hash del admin para que funcione siempre)
SET @hash = (SELECT password_hash FROM usuario WHERE id = '00000001A' LIMIT 1);

INSERT IGNORE INTO usuario (id, email, password_hash, rol)
VALUES ('00000099Z', 'maria@mail.com', @hash, 'alumno');

-- 2. Crear alumna en estado 'posible'
INSERT IGNORE INTO alumno (usuario_id, nombre, apellidos, email, estado)
VALUES ('00000099Z', 'María', 'García López', 'maria@mail.com', 'posible');

SET @invitado_id = (SELECT id FROM alumno WHERE usuario_id = '00000099Z' LIMIT 1);

-- 3. Generar 1 token de prueba (limpio, sin asistencia ficticia)
INSERT INTO token (alumno_id, tipo, asistencia_origen_id, fecha_caducidad, usado)
VALUES (@invitado_id, 'prueba', NULL, '2026-06-30', FALSE);

-- Verificación
SELECT '✓ Invitada creada' AS test,
       a.nombre, a.apellidos, a.estado, u.email,
       (SELECT COUNT(*) FROM token WHERE alumno_id = a.id AND usado = FALSE) AS tokens
FROM alumno a
INNER JOIN usuario u ON u.id = a.usuario_id
WHERE a.usuario_id = '00000099Z';
