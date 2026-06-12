<?php
// -----------------------------------------------------------------------------
// setup.php - Crear la base de datos completa y usuarios demo.
// Visita: http://localhost/NOMBRE_DE_TU_CARPETA/setup.php
// -----------------------------------------------------------------------------

$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($base_url === '/') {
    $base_url = '';
}

$servidor = 'localhost';
$usuario  = 'root';
$password = '';

$con = new mysqli($servidor, $usuario, $password);
if ($con->connect_error) {
    die('No se pudo conectar a MySQL: ' . $con->connect_error);
}

$pasos = [];

// 1. Base de datos
$con->query("CREATE DATABASE IF NOT EXISTS `punto_de_partida` CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci");
$pasos[] = 'Base de datos <strong>punto_de_partida</strong> lista.';
$con->select_db('punto_de_partida');
$con->query("SET default_storage_engine = InnoDB");

// 2. Tablas (en orden de dependencia de FKs)
$tablas = [

    'usuario' => "CREATE TABLE IF NOT EXISTS `usuario` (
        `id`            VARCHAR(20)  PRIMARY KEY,
        `email`         VARCHAR(200) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `rol`           ENUM('alumno','profesor','admin') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'profesor' => "CREATE TABLE IF NOT EXISTS `profesor` (
        `usuario_id` VARCHAR(20)  PRIMARY KEY,
        `nombre`     VARCHAR(100) NOT NULL,
        `apellidos`  VARCHAR(150) NOT NULL,
        `telefono`   VARCHAR(20),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'admin' => "CREATE TABLE IF NOT EXISTS `admin` (
        `usuario_id` VARCHAR(20)  PRIMARY KEY,
        `nombre`     VARCHAR(100) NOT NULL,
        `apellidos`  VARCHAR(150) NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'sala' => "CREATE TABLE IF NOT EXISTS `sala` (
        `id`             INT          AUTO_INCREMENT PRIMARY KEY,
        `nombre`         VARCHAR(100) NOT NULL,
        `espacio_nombre` VARCHAR(100) NOT NULL,
        `direccion`      VARCHAR(200) NOT NULL,
        `tipo`           ENUM('aula','sala_representacion') NOT NULL DEFAULT 'aula',
        `aforo_maximo`   INT          NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'alumno' => "CREATE TABLE IF NOT EXISTS `alumno` (
        `id`                  INT          AUTO_INCREMENT PRIMARY KEY,
        `usuario_id`          VARCHAR(20)  UNIQUE,
        `nombre`              VARCHAR(100) NOT NULL,
        `apellidos`           VARCHAR(150) NOT NULL,
        `email`               VARCHAR(200),
        `telefono`            VARCHAR(20),
        `estado`              ENUM('posible','matriculado','baja') NOT NULL DEFAULT 'posible',
        `fecha_registro`      DATE         NOT NULL DEFAULT (CURRENT_DATE),
        `nivel`               ENUM('iniciacion','intermedio','avanzado'),
        `fecha_interes`       DATE,
        `tipo_interes`        ENUM('no_insistir','avisar_sep2026','avisar_ene2027','ex_alumno','sin_horario','intensivo','no_vive_madrid'),
        `clase_prueba`        BOOLEAN      DEFAULT FALSE,
        `fecha_primera_clase` DATE,
        FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'horario_posible' => "CREATE TABLE IF NOT EXISTS `horario_posible` (
        `id`            INT  AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`     INT  NOT NULL,
        `dia_semana`    ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
        `tramo_horario` ENUM('11-13','16-18','18-20','20-22','fds_11-13','fds_17-19') NOT NULL,
        FOREIGN KEY (alumno_id) REFERENCES alumno(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'grupo' => "CREATE TABLE IF NOT EXISTS `grupo` (
        `id`                 INT          AUTO_INCREMENT PRIMARY KEY,
        `profesor_id`        VARCHAR(20)  NOT NULL,
        `sala_id`            INT          NOT NULL,
        `nombre`             VARCHAR(150) NOT NULL,
        `dia_semana`         ENUM('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
        `hora_inicio`        TIME         NOT NULL,
        `hora_fin`           TIME         NOT NULL,
        `nivel`              ENUM('iniciacion','intermedio','avanzado') NOT NULL,
        `tipo`               ENUM('teatro','improvisacion','actuacion','danza','canto') NOT NULL,
        `curso`              VARCHAR(50),
        `fecha_inicio_curso` DATE         NOT NULL,
        `fecha_fin_curso`    DATE         NOT NULL,
        `activo`             BOOLEAN      DEFAULT TRUE,
        FOREIGN KEY (profesor_id) REFERENCES profesor(usuario_id),
        FOREIGN KEY (sala_id)     REFERENCES sala(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'alumno_grupo' => "CREATE TABLE IF NOT EXISTS `alumno_grupo` (
        `id`                INT  AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`         INT  NOT NULL,
        `grupo_id`          INT  NOT NULL,
        `fecha_inscripcion` DATE NOT NULL DEFAULT (CURRENT_DATE),
        `activo`            BOOLEAN DEFAULT TRUE,
        UNIQUE KEY uk_alumno_grupo (alumno_id, grupo_id),
        FOREIGN KEY (alumno_id) REFERENCES alumno(id),
        FOREIGN KEY (grupo_id)  REFERENCES grupo(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'clase' => "CREATE TABLE IF NOT EXISTS `clase` (
        `id`          INT  AUTO_INCREMENT PRIMARY KEY,
        `grupo_id`    INT  NOT NULL,
        `sala_id`     INT  NOT NULL,
        `fecha`       DATE NOT NULL,
        `hora_inicio` TIME NOT NULL,
        `hora_fin`    TIME NOT NULL,
        `cupo_maximo` INT  NOT NULL,
        `estado`      ENUM('programada','cancelada','realizada') DEFAULT 'programada',
        FOREIGN KEY (grupo_id) REFERENCES grupo(id),
        FOREIGN KEY (sala_id)  REFERENCES sala(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'asistencia' => "CREATE TABLE IF NOT EXISTS `asistencia` (
        `id`           INT  AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`    INT  NOT NULL,
        `clase_id`     INT  NOT NULL,
        `estado`       ENUM('asiste','ausente','avisado') NOT NULL,
        `fecha_aviso`  DATETIME,
        `aviso_valido` BOOLEAN DEFAULT FALSE,
        UNIQUE KEY uk_asistencia (alumno_id, clase_id),
        FOREIGN KEY (alumno_id) REFERENCES alumno(id),
        FOREIGN KEY (clase_id)  REFERENCES clase(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'token' => "CREATE TABLE IF NOT EXISTS `token` (
        `id`                   INT  AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`            INT  NOT NULL,
        `tipo`                 ENUM('ausencia','prueba','cortesia') NOT NULL  DEFAULT 'ausencia',
        `asistencia_origen_id` INT  NULL,
        `fecha_generacion`     DATE NOT NULL DEFAULT (CURRENT_DATE),
        `fecha_caducidad`      DATE NOT NULL,
        `usado`                BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (alumno_id)            REFERENCES alumno(id) ON DELETE CASCADE,
        FOREIGN KEY (asistencia_origen_id) REFERENCES asistencia(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'recuperacion' => "CREATE TABLE IF NOT EXISTS `recuperacion` (
        `id`                    INT      AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`             INT      NOT NULL,
        `token_id`              INT      NOT NULL,
        `clase_origen_id`       INT      NOT NULL,
        `clase_recuperacion_id` INT      NOT NULL,
        `estado`                ENUM('pendiente','confirmada','realizada','cancelada') DEFAULT 'pendiente',
        `fecha_solicitud`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (alumno_id)             REFERENCES alumno(id),
        FOREIGN KEY (token_id)              REFERENCES token(id),
        FOREIGN KEY (clase_origen_id)       REFERENCES clase(id),
        FOREIGN KEY (clase_recuperacion_id) REFERENCES clase(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'bloque_pago' => "CREATE TABLE IF NOT EXISTS `bloque_pago` (
        `id`           INT          AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`    INT          NOT NULL,
        `grupo_id`     INT          NOT NULL,
        `descripcion`  VARCHAR(200),
        `fecha_inicio` DATE         NOT NULL,
        `fecha_fin`    DATE         NOT NULL,
        `importe`      DECIMAL(8,2) NOT NULL,
        `pagado`       BOOLEAN      DEFAULT FALSE,
        `fecha_pago`   DATE,
        FOREIGN KEY (alumno_id) REFERENCES alumno(id),
        FOREIGN KEY (grupo_id)  REFERENCES grupo(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'evento_grupal' => "CREATE TABLE IF NOT EXISTS `evento_grupal` (
        `id`             INT          AUTO_INCREMENT PRIMARY KEY,
        `nombre`         VARCHAR(200) NOT NULL,
        `tipo`           ENUM('intensivo','salida_teatro') NOT NULL,
        `descripcion`    TEXT,
        `fecha`          DATE         NOT NULL,
        `plazas_maximas` INT,
        `sala_id`        INT,
        `hora`           TIME,
        FOREIGN KEY (sala_id) REFERENCES sala(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'inscripcion_evento' => "CREATE TABLE IF NOT EXISTS `inscripcion_evento` (
        `id`                INT      AUTO_INCREMENT PRIMARY KEY,
        `alumno_id`         INT      NOT NULL,
        `evento_id`         INT      NOT NULL,
        `fecha_inscripcion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `estado`            ENUM('inscrito','cancelado') DEFAULT 'inscrito',
        UNIQUE KEY uk_inscripcion_evento (alumno_id, evento_id),
        FOREIGN KEY (alumno_id) REFERENCES alumno(id),
        FOREIGN KEY (evento_id) REFERENCES evento_grupal(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'observacion_profesor' => "CREATE TABLE IF NOT EXISTS `observacion_profesor` (
        `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `profesor_id`    VARCHAR(20)  NOT NULL,
        `tipo_destino`   ENUM('alumno','grupo','clase') NOT NULL,
        `destino_id`     INT UNSIGNED NOT NULL,
        `texto`          TEXT         NOT NULL,
        `fecha_creacion` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_destino  (tipo_destino, destino_id),
        KEY idx_profesor (profesor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tablas as $nombre => $sql) {
    if ($con->query($sql)) {
        $pasos[] = "Tabla <strong>$nombre</strong> lista.";
    } else {
        $pasos[] = "<span style='color:red'>Error en <strong>$nombre</strong>: " . $con->error . "</span>";
    }
}

// 2.b. Migraciones para BDs existentes (idempotentes)
$con->query("ALTER TABLE `grupo` MODIFY `tipo` ENUM('teatro','improvisacion','actuacion','danza','canto') NOT NULL");
    //Añado columna "hora" para eventos
    $resultado = $con->query("
    SHOW COLUMNS FROM evento_grupal LIKE 'hora'
");

if ($resultado->num_rows === 0) {
    $con->query("
        ALTER TABLE evento_grupal
        ADD hora TIME NULL
    ");
    if ($con->errno === 0) {
        $pasos[] = 'Columna <strong>hora</strong> añadida a evento_grupal.';
    } else {
        $pasos[] = "<span style='color:red'>Error añadiendo hora: {$con->error}</span>";
    }
}

if ($con->errno === 0) {
    $pasos[] = 'Tipos de grupo ampliados: <strong>teatro, improvisacion, actuacion, danza, canto</strong>.';
}

// 3. Salas iniciales
$con->query("
    INSERT IGNORE INTO sala (id, nombre, espacio_nombre, direccion, tipo, aforo_maximo) VALUES
    (1, 'Sala sótano', 'Espacio en Blanco', 'C/Mira el Sol 5', 'aula', 16),
    (2, 'Sala arriba', 'Espacio en Blanco', 'C/Mira el Sol 5', 'aula', 16),
    (3, 'Sala blanca', 'Espacio en Blanco', 'C/Mira el Sol 7', 'aula', 16),
    (4, 'Sala ocre',   'Espacio en Blanco', 'C/Mira el Sol 7', 'aula', 16),
    (5, 'La Íntegra Teatro', 'La Íntegra Teatro', 'C/Palma 74', 'aula', 16)
");
$pasos[] = 'Salas listas.';
// 3.1 Grupos iniciales (para mostrar algo en el dashboard de grupos)
$con->query("
    INSERT IGNORE INTO grupo
    (id, profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin, nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso, activo)
    VALUES
    (1, '00000002B', 2, 'Iniciación Segundo Año', 'lunes', '17:30:00', '19:00:00', 'iniciacion', 'teatro', '2025-2026', '2025-09-01', '2026-06-30', 1),
    (2, '00000002B', 3, 'Avanzado',               'lunes', '18:00:00', '19:30:00', 'avanzado',   'teatro', '2025-2026', '2025-09-01', '2026-06-30', 1),
    (3, '00000002B', 4, 'Iniciación',             'lunes', '20:00:00', '21:30:00', 'iniciacion', 'teatro', '2025-2026', '2025-09-01', '2026-06-30', 1)
");
$pasos[] = 'Grupos iniciales listos.';
// 4. Usuarios demo 
$usuarios_demo = [
    ['id' => '00000001A', 'email' => 'lucia@mail.com', 'password' => '1234', 'rol' => 'admin',    'nombre' => 'Lucia', 'apellidos' => 'Demo'],
    ['id' => '00000002B', 'email' => 'luis@mail.com',  'password' => '1234', 'rol' => 'profesor', 'nombre' => 'Luis',  'apellidos' => 'Demo'],
    ['id' => '00000003C', 'email' => 'juan@mail.com',  'password' => '1234', 'rol' => 'alumno',   'nombre' => 'Juan',  'apellidos' => 'Demo',          'estado' => 'matriculado'],
    ['id' => '00000099Z', 'email' => 'maria@mail.com', 'password' => '1234', 'rol' => 'alumno',   'nombre' => 'Maria', 'apellidos' => 'Garcia Lopez', 'estado' => 'posible'],
];

$stmt_u = $con->prepare("INSERT IGNORE INTO usuario (id, email, password_hash, rol) VALUES (?, ?, ?, ?)");

foreach ($usuarios_demo as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmt_u->bind_param('ssss', $u['id'], $u['email'], $hash, $u['rol']);
    $stmt_u->execute();
    $creado = $stmt_u->affected_rows > 0;

    if ($u['rol'] === 'admin') {
        $s = $con->prepare("INSERT IGNORE INTO admin (usuario_id, nombre, apellidos) VALUES (?, ?, ?)");
        $s->bind_param('sss', $u['id'], $u['nombre'], $u['apellidos']);
        $s->execute();
    } elseif ($u['rol'] === 'profesor') {
        $s = $con->prepare("INSERT IGNORE INTO profesor (usuario_id, nombre, apellidos) VALUES (?, ?, ?)");
        $s->bind_param('sss', $u['id'], $u['nombre'], $u['apellidos']);
        $s->execute();
    } elseif ($u['rol'] === 'alumno') {
        $estado = $u['estado'] ?? 'matriculado';
        $s = $con->prepare("INSERT IGNORE INTO alumno (usuario_id, nombre, apellidos, email, estado) VALUES (?, ?, ?, ?, ?)");
        $s->bind_param('sssss', $u['id'], $u['nombre'], $u['apellidos'], $u['email'], $estado);
        $s->execute();
    }

    $estado_txt = $creado ? 'creado' : 'ya existia';
    $pasos[] = "Usuario <strong>{$u['email']}</strong> ({$u['rol']}) &mdash; $estado_txt.";
}

$con->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Setup - Punto de Partida</title>
    <style>
        body { font-family: Georgia, serif; max-width: 640px; margin: 3rem auto; padding: 1rem; }
        h1   { color: #DC2626; }
        p    { line-height: 1.8; }
        a    { color: #DC2626; }
        .ok  { color: #16a34a; }
        .err { color: #DC2626; }
    </style>
</head>
<body>
    <h1>Setup - Punto de Partida</h1>
    <?php foreach ($pasos as $paso): ?>
        <p class="<?= str_contains($paso, 'Error') ? 'err' : 'ok' ?>">&#10003; <?= $paso ?></p>
    <?php endforeach; ?>
    <hr>
    <p><a href="<?= htmlspecialchars($base_url) ?>/login">Ir al login</a></p>
</body>
</html>
