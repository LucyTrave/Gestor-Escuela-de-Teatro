-- =============================================================================
-- Migracion: Tabla observacion_profesor
-- Fecha: 2026-05-04
-- =============================================================================
-- La tabla `observacion_profesor` faltaba en setup.php (se habia creado a mano
-- en phpMyAdmin durante el desarrollo). Esta migracion la crea para entornos
-- donde la base de datos ya existia y no se va a recrear desde cero.
--
-- Si el ENUM ya existe pero le falta 'clase', se actualiza al final.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `observacion_profesor` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `profesor_id`    VARCHAR(20)  NOT NULL,
    `tipo_destino`   ENUM('alumno','grupo','clase') NOT NULL,
    `destino_id`     INT UNSIGNED NOT NULL,
    `texto`          TEXT         NOT NULL,
    `fecha_creacion` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_destino  (tipo_destino, destino_id),
    KEY idx_profesor (profesor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Por si la tabla ya existia con ENUM('alumno','grupo') sin 'clase'
ALTER TABLE `observacion_profesor`
    MODIFY `tipo_destino` ENUM('alumno','grupo','clase') NOT NULL;


DESCRIBE evento_grupal;