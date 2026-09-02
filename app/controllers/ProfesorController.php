<?php

require_once ROOT . '/app/helpers/Csrf.php';

class ProfesorController {

    private function requireProfesorAuth(): void {
        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (!in_array($_SESSION['usuario_rol'], ['profesor', 'admin'], true)) {
            header('Location: ' . BASE_URL . '/alumno');
            exit;
        }
    }

    private function esAdmin(): bool {
        return ($_SESSION['usuario_rol'] ?? '') === 'admin';
    }

    private function profesorId(): string {
        return (string)($_SESSION['usuario_id'] ?? '');
    }

    /**
     * Devuelve (y crea si hace falta) el token CSRF de la sesion actual.
     * Se inyecta en cada formulario para evitar peticiones falsificadas.
     */
    private function csrfToken(): string {
        return Csrf::token();
    }

    /**
     * Verifica el token CSRF enviado por POST. Si no coincide, redirige.
     */
    private function verificarCsrf(string $rutaRedirect): void {
        if (!Csrf::validatePost()) {
            $this->redirect($rutaRedirect, 'error_datos');
        }
    }

    /**
     * Carga las observaciones del profesor para una lista de alumnos o grupos.
     * Devuelve un array indexado por destino_id con las observaciones (mas recientes primero).
     */
    private function cargarObservaciones(string $tipoDestino, array $ids): array {
        global $conexion;
        if (empty($ids) || !in_array($tipoDestino, ['alumno', 'grupo', 'clase'], true)) {
            return [];
        }
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Cada profesor solo ve sus propias observaciones. El admin las ve todas.
        $filtroProfesor = '';
        $tipos = 's' . str_repeat('i', count($ids));
        $params = array_merge([$tipoDestino], $ids);
        if (!$this->esAdmin()) {
            $filtroProfesor = ' AND o.profesor_id = ?';
            $tipos .= 's';
            $params[] = $this->profesorId();
        }

        $sql = "SELECT o.id, o.destino_id, o.texto, o.fecha_creacion, o.profesor_id,
                       COALESCE(CONCAT(p.nombre, ' ', p.apellidos), o.profesor_id) AS autor
                FROM observacion_profesor o
                LEFT JOIN profesor p ON p.usuario_id = o.profesor_id
                WHERE o.tipo_destino = ? AND o.destino_id IN ($placeholders)$filtroProfesor
                ORDER BY o.fecha_creacion DESC, o.id DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $resultado = [];
        foreach ($rows as $r) {
            $resultado[(int)$r['destino_id']][] = $r;
        }
        return $resultado;
    }

    private function redirect(string $ruta, string $mensaje = ''): void {
        $destino = BASE_URL . $ruta;
        if ($mensaje !== '') {
            $separador = (strpos($ruta, '?') === false) ? '?' : '&';
            $destino .= $separador . 'mensaje=' . urlencode($mensaje);
        }
        header('Location: ' . $destino);
        exit;
    }

    private function mensaje(string $codigo): array {
        $mensajes = [
            'alumno_creado' => ['tipo' => 'exito', 'texto' => 'Alumno creado correctamente.'],
            'alumno_actualizado' => ['tipo' => 'exito', 'texto' => 'Alumno actualizado correctamente.'],
            'alumno_eliminado' => ['tipo' => 'exito', 'texto' => 'Alumno eliminado correctamente.'],
            'alumno_repetido' => ['tipo' => 'exito', 'texto' => 'Alumno anadido al grupo seleccionado.'],
            'grupo_creado' => ['tipo' => 'exito', 'texto' => 'Grupo creado correctamente.'],
            'grupo_actualizado' => ['tipo' => 'exito', 'texto' => 'Grupo actualizado correctamente.'],
            'grupo_eliminado' => ['tipo' => 'exito', 'texto' => 'Grupo eliminado correctamente.'],
            'clase_creada' => ['tipo' => 'exito', 'texto' => 'Clase creada correctamente.'],
            'clase_actualizada' => ['tipo' => 'exito', 'texto' => 'Clase actualizada correctamente.'],
            'clase_eliminada' => ['tipo' => 'exito', 'texto' => 'Clase eliminada correctamente.'],
            'asistencia_guardada' => ['tipo' => 'exito', 'texto' => 'Asistencia guardada correctamente.'],
            'observacion_guardada' => ['tipo' => 'exito', 'texto' => 'Observacion guardada correctamente.'],
            'observacion_eliminada' => ['tipo' => 'exito', 'texto' => 'Observacion eliminada correctamente.'],
            'sin_permiso' => ['tipo' => 'warning', 'texto' => 'No tienes permiso para realizar esta accion sobre este registro.'],
            'error_relacion' => ['tipo' => 'warning', 'texto' => 'No se pudo completar la acción porque el registro tiene datos relacionados.'],
            'error_datos' => ['tipo' => 'warning', 'texto' => 'Revisa los datos del formulario y vuelve a intentarlo.'],
            'clase_duplicada' => ['tipo' => 'warning', 'texto' => 'Ya existe una clase de ese grupo en la misma fecha y hora.'],
            'clase_solapada'  => ['tipo' => 'warning', 'texto' => 'El profesor ya tiene otra clase en ese horario; no se puede solapar.'],
            'sala_ocupada'    => ['tipo' => 'warning', 'texto' => 'La sala está ocupada por otra clase en ese intervalo de tiempo.'],
        ];

        return $mensajes[$codigo] ?? ['tipo' => '', 'texto' => ''];
    }

    private function obtenerSalas(): array {
        global $conexion;

        $resultado = $conexion->query("SELECT id, nombre, espacio_nombre FROM sala ORDER BY nombre");
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function obtenerGruposProfesor(bool $soloActivos = false): array {
        global $conexion;

        $sql = "SELECT g.id, g.nombre, g.tipo, g.dia_semana, g.hora_inicio, g.hora_fin,
                       g.nivel, g.sala_id, g.activo
                FROM grupo g";
        $tipos = '';
        $params = [];
        $where = [];

        if (!$this->esAdmin()) {
            $where[] = 'g.profesor_id = ?';
            $tipos .= 's';
            $params[] = $this->profesorId();
        }

        if ($soloActivos) {
            $where[] = 'g.activo IS NOT FALSE';
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY g.nombre';

        if ($tipos === '') {
            $resultado = $conexion->query($sql);
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        }

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function sincronizarGrupoAlumno(int $alumnoId, ?int $grupoId): void {
        global $conexion;

        $stmt = $conexion->prepare("UPDATE alumno_grupo SET activo = FALSE WHERE alumno_id = ?");
        $stmt->bind_param('i', $alumnoId);
        $stmt->execute();

        if (!$grupoId) {
            return;
        }

        $stmt = $conexion->prepare(
            "INSERT INTO alumno_grupo (alumno_id, grupo_id, fecha_inscripcion, activo)
             VALUES (?, ?, CURDATE(), TRUE)
             ON DUPLICATE KEY UPDATE activo = TRUE"
        );
        $stmt->bind_param('ii', $alumnoId, $grupoId);
        $stmt->execute();
    }

    private function redirectConClase(string $mensaje, int $claseId): void {
        $ruta = '/profesor/asistencia';
        if ($claseId > 0) {
            $ruta .= '?clase_id=' . $claseId;
            $this->redirect($ruta, $mensaje);
        }
        $this->redirect($ruta, $mensaje);
    }
// Metodos de la clase ProfesorController (showPanel, alumnos, grupos, etc.) se encuentran aquí.
    public function showPanel(): void {
        $this->requireProfesorAuth();
        global $conexion;

        $tipos = '';
        $params = [];
        $filtroProfesor = '';

        if (!$this->esAdmin()) {
            $filtroProfesor = ' WHERE g.profesor_id = ?';
            $tipos = 's';
            $params[] = $this->profesorId();
        }

        if ($tipos === '') {
            $gruposActivos = (int)$conexion->query("SELECT COUNT(*) total FROM grupo g WHERE g.activo = TRUE")->fetch_assoc()['total'];
            $alumnosActivos = (int)$conexion->query(
                "SELECT COUNT(DISTINCT ag.alumno_id) total
                 FROM alumno_grupo ag
                 INNER JOIN grupo g ON g.id = ag.grupo_id
                 WHERE ag.activo = TRUE"
            )->fetch_assoc()['total'];
            $proximasClases = $conexion->query(
                "SELECT c.id, c.fecha, c.hora_inicio, g.nombre, g.tipo, s.nombre AS sala,
                        (SELECT COUNT(*) FROM alumno_grupo ag WHERE ag.grupo_id = g.id AND ag.activo = TRUE) AS total_alumnos,
                        (SELECT COUNT(*) FROM asistencia a WHERE a.clase_id = c.id AND a.estado = 'asiste') AS confirmados,
                        (SELECT COUNT(*) FROM asistencia a WHERE a.clase_id = c.id AND a.estado = 'avisado') AS avisados
                 FROM clase c
                 INNER JOIN grupo g ON g.id = c.grupo_id
                 LEFT JOIN sala s ON s.id = c.sala_id
                 WHERE c.estado = 'programada' AND c.fecha >= CURDATE()
                 ORDER BY c.fecha, c.hora_inicio
                 LIMIT 5"
            )->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $conexion->prepare("SELECT COUNT(*) total FROM grupo g WHERE g.activo = TRUE AND g.profesor_id = ?");
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $gruposActivos = (int)$stmt->get_result()->fetch_assoc()['total'];

            $stmt = $conexion->prepare(
                "SELECT COUNT(DISTINCT ag.alumno_id) total
                 FROM alumno_grupo ag
                 INNER JOIN grupo g ON g.id = ag.grupo_id
                 WHERE ag.activo = TRUE AND g.profesor_id = ?"
            );
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $alumnosActivos = (int)$stmt->get_result()->fetch_assoc()['total'];

            $stmt = $conexion->prepare(
                "SELECT c.id, c.fecha, c.hora_inicio, g.nombre, g.tipo, s.nombre AS sala,
                        (SELECT COUNT(*) FROM alumno_grupo ag WHERE ag.grupo_id = g.id AND ag.activo = TRUE) AS total_alumnos,
                        (SELECT COUNT(*) FROM asistencia a WHERE a.clase_id = c.id AND a.estado = 'asiste') AS confirmados,
                        (SELECT COUNT(*) FROM asistencia a WHERE a.clase_id = c.id AND a.estado = 'avisado') AS avisados
                 FROM clase c
                 INNER JOIN grupo g ON g.id = c.grupo_id
                 LEFT JOIN sala s ON s.id = c.sala_id
                 WHERE c.estado = 'programada' AND c.fecha >= CURDATE() AND g.profesor_id = ?
                   AND c.id IN (SELECT MIN(c2.id) FROM clase c2 GROUP BY c2.grupo_id, c2.fecha, c2.hora_inicio)
                 ORDER BY c.fecha, c.hora_inicio
                 LIMIT 3"
            );
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $proximasClases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }


    // Obtener los próximos eventos especiales asignados al profesor conectado.
    if ($this->esAdmin()) {

    // Si entra un administrador, puede ver todos los eventos especiales.
    $stmtEventos = $conexion->prepare(
        "SELECT
            e.id,
            e.nombre,
            e.tipo,
            e.descripcion,
            e.fecha,
            e.hora,
            e.plazas_maximas,
            p.nombre AS profesor_nombre,
            p.apellidos AS profesor_apellidos,
            COUNT(CASE WHEN i.estado = 'inscrito' THEN 1 END) AS apuntados
         FROM evento_grupal e
         LEFT JOIN profesor p
            ON p.usuario_id = e.profesor_id
         LEFT JOIN inscripcion_evento i
            ON i.evento_id = e.id
         WHERE e.fecha >= CURDATE()
         GROUP BY
            e.id,
            e.nombre,
            e.tipo,
            e.descripcion,
            e.fecha,
            e.hora,
            e.plazas_maximas,
            p.nombre,
            p.apellidos
         ORDER BY e.fecha ASC, e.hora ASC"
    );

    $stmtEventos->execute();
} else {
    // Si entra un profesor, solo verá los eventos que tiene asignados.
    $stmtEventos = $conexion->prepare(
        "SELECT
            e.id,
            e.nombre,
            e.tipo,
            e.descripcion,
            e.fecha,
            e.hora,
            e.plazas_maximas,
            p.nombre AS profesor_nombre,
            p.apellidos AS profesor_apellidos,
            COUNT(CASE WHEN i.estado = 'inscrito' THEN 1 END) AS apuntados
         FROM evento_grupal e
         LEFT JOIN profesor p
            ON p.usuario_id = e.profesor_id
         LEFT JOIN inscripcion_evento i
            ON i.evento_id = e.id
         WHERE e.fecha >= CURDATE()
           AND e.profesor_id = ?
         GROUP BY
            e.id,
            e.nombre,
            e.tipo,
            e.descripcion,
            e.fecha,
            e.hora,
            e.plazas_maximas,
            p.nombre,
            p.apellidos
         ORDER BY e.fecha ASC, e.hora ASC"
    );

    $profesorId = $this->profesorId();
    $stmtEventos->bind_param('s', $profesorId);
    $stmtEventos->execute();
}

$eventosEspeciales = $stmtEventos
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);


        $vista = 'inicio';
        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');
        require ROOT . '/app/views/profesor/inicio.php';
    }

    public function alumnos(): void {
        $this->requireProfesorAuth();
        global $conexion;

        // Exportar CSV: GET /profesor/alumnos?exportar=csv
        if (($_GET['exportar'] ?? '') === 'csv') {
            $this->exportarAlumnosCsv();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Toda accion POST en /profesor/alumnos requiere token CSRF valido.
            $this->verificarCsrf('/profesor/alumnos');
            $accion = $_POST['accion'] ?? '';

            // Profesor y admin pueden anadir observaciones sobre los alumnos.
            if ($accion === 'observaciones') {
                $alumnoId = (int)($_POST['destino_id'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');
                if ($alumnoId <= 0 || $texto === '') {
                    $this->redirect('/profesor/alumnos', 'error_datos');
                }
                if (!$this->esAdmin()) {
                    // El alumno debe ser visible para este profesor:
                    // o no tiene grupo, o esta en un grupo asignado al profesor.
                    $stmt = $conexion->prepare(
                        "SELECT a.id FROM alumno a
                         LEFT JOIN alumno_grupo ag ON ag.alumno_id = a.id AND ag.activo = TRUE
                         LEFT JOIN grupo g ON g.id = ag.grupo_id
                         WHERE a.id = ? AND (ag.grupo_id IS NULL OR g.profesor_id = ?)
                         LIMIT 1"
                    );
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $alumnoId, $profesorId);
                    $stmt->execute();
                    if (!$stmt->get_result()->fetch_assoc()) {
                        $this->redirect('/profesor/alumnos', 'sin_permiso');
                    }
                }
                $stmt = $conexion->prepare(
                    "INSERT INTO observacion_profesor (profesor_id, tipo_destino, destino_id, texto)
                     VALUES (?, 'alumno', ?, ?)"
                );
                $profesorId = $this->profesorId();
                $stmt->bind_param('sis', $profesorId, $alumnoId, $texto);
                $stmt->execute();
                $this->redirect('/profesor/alumnos', 'observacion_guardada');
            }

            if ($accion === 'eliminar_observacion') {
                $obsId = (int)($_POST['observacion_id'] ?? 0);
                if ($obsId <= 0) {
                    $this->redirect('/profesor/alumnos', 'error_datos');
                }
                if ($this->esAdmin()) {
                    $stmt = $conexion->prepare("DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'alumno'");
                    $stmt->bind_param('i', $obsId);
                } else {
                    // Profesor solo puede borrar SUS propias observaciones
                    $stmt = $conexion->prepare(
                        "DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'alumno' AND profesor_id = ?"
                    );
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $obsId, $profesorId);
                }
                $stmt->execute();
                $this->redirect('/profesor/alumnos', 'observacion_eliminada');
            }

            // Crear / editar / eliminar / repetir de grupo son acciones reservadas al admin.
            // El profesor solo tiene acceso de lectura a esta vista.
            if (!$this->esAdmin()) {
                $this->redirect('/profesor/alumnos', 'sin_permiso');
            }

            if ($accion === 'repetir') {
                $id = (int)($_POST['id'] ?? 0);
                $grupoId = (int)($_POST['grupo_id'] ?? 0);

                if ($id <= 0 || $grupoId <= 0) {
                    $this->redirect('/profesor/alumnos', 'error_datos');
                }

                // Verificar que el grupo pertenezca al profesor (o que sea admin)
                if (!$this->esAdmin()) {
                    $stmt = $conexion->prepare("SELECT id FROM grupo WHERE id = ? AND profesor_id = ?");
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $grupoId, $profesorId);
                    $stmt->execute();
                    if (!$stmt->get_result()->fetch_assoc()) {
                        $this->redirect('/profesor/alumnos', 'error_datos');
                    }
                }

                $stmt = $conexion->prepare(
                    "INSERT INTO alumno_grupo (alumno_id, grupo_id, fecha_inscripcion, activo)
                     VALUES (?, ?, CURDATE(), TRUE)
                     ON DUPLICATE KEY UPDATE activo = TRUE"
                );
                $stmt->bind_param('ii', $id, $grupoId);
                $stmt->execute();
                $this->redirect('/profesor/alumnos', 'alumno_repetido');
            }

            if ($accion === 'crear') {
                $nombre = trim($_POST['nombre'] ?? '');
                $apellidos = trim($_POST['apellidos'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $grupoId = (int)($_POST['grupo_id'] ?? 0);

                if ($nombre === '' || $apellidos === '') {
                    $this->redirect('/profesor/alumnos', 'error_datos');
                }

                $stmt = $conexion->prepare(
                    "INSERT INTO alumno (nombre, apellidos, email, telefono, estado, fecha_registro)
                     VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), 'matriculado', CURDATE())"
                );
                $stmt->bind_param('ssss', $nombre, $apellidos, $email, $telefono);
                $stmt->execute();

                $alumnoId = (int)$conexion->insert_id;
                $this->sincronizarGrupoAlumno($alumnoId, $grupoId > 0 ? $grupoId : null);
                $this->redirect('/profesor/alumnos', 'alumno_creado');
            }

            if ($accion === 'actualizar') {
                $id = (int)($_POST['id'] ?? 0);
                $nombre = trim($_POST['nombre'] ?? '');
                $apellidos = trim($_POST['apellidos'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $estado = $_POST['estado'] ?? 'matriculado';
                $grupoId = (int)($_POST['grupo_id'] ?? 0);

                if ($id <= 0 || $nombre === '' || $apellidos === '') {
                    $this->redirect('/profesor/alumnos', 'error_datos');
                }

                $stmt = $conexion->prepare(
                    "UPDATE alumno
                     SET nombre = ?, apellidos = ?, email = NULLIF(?, ''), telefono = NULLIF(?, ''), estado = ?
                     WHERE id = ?"
                );
                $stmt->bind_param('sssssi', $nombre, $apellidos, $email, $telefono, $estado, $id);
                $stmt->execute();

                $this->sincronizarGrupoAlumno($id, $grupoId > 0 ? $grupoId : null);
                $this->redirect('/profesor/alumnos', 'alumno_actualizado');
            }

            if ($accion === 'eliminar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $stmt = $conexion->prepare("DELETE FROM alumno WHERE id = ?");
                        $stmt->bind_param('i', $id);
                        $stmt->execute();
                        $this->redirect('/profesor/alumnos', 'alumno_eliminado');
                    } catch (mysqli_sql_exception $e) {
                        $this->redirect('/profesor/alumnos', 'error_relacion');
                    }
                }
            }
        }

        $gruposDisponibles = $this->obtenerGruposProfesor(true);
        $editarId = (int)($_GET['editar'] ?? 0);
        $mensaje  = $this->mensaje($_GET['mensaje'] ?? '');

        // Filtros: buscador por nombre/apellidos/email y filtro por estado.
        $busqueda        = trim((string)($_GET['q']      ?? ''));
        $filtroEstado    = (string)($_GET['estado']      ?? '');
        $estadosValidos  = ['', 'posible', 'matriculado', 'baja'];
        if (!in_array($filtroEstado, $estadosValidos, true)) {
            $filtroEstado = '';
        }

        // Filtro extra: solo alumnos con tokens disponibles (para recuperaciones).
        $soloConTokens = isset($_GET['solo_tokens']) && $_GET['solo_tokens'] === '1';

        $sqlBase = "SELECT a.id, a.nombre, a.apellidos, a.email, a.telefono, a.estado,
                           ag.grupo_id,
                           g.nombre AS grupo_nombre,
                           (SELECT COUNT(*) FROM token t
                            WHERE t.alumno_id = a.id
                              AND t.usado = FALSE
                              AND (t.fecha_caducidad IS NULL OR t.fecha_caducidad >= CURDATE())
                           ) AS tokens_disponibles
                    FROM alumno a
                    LEFT JOIN alumno_grupo ag ON ag.alumno_id = a.id AND ag.activo = TRUE
                    LEFT JOIN grupo g ON g.id = ag.grupo_id";
        $where  = [];
        $tipos  = '';
        $params = [];

        if (!$this->esAdmin()) {
           $where[] = '(ag.grupo_id IS NULL OR (
                g.profesor_id = ?
                AND g.activo = TRUE
                AND (g.fecha_inicio_curso IS NULL OR g.fecha_inicio_curso <= CURDATE())
                AND (g.fecha_fin_curso IS NULL OR g.fecha_fin_curso >= CURDATE())
            ))';
            $tipos  .= 's';
            $params[] = $this->profesorId();

            // Regla fija del profesor: solo le interesan los alumnos que pueden
            // asistir = matriculados O alumnos con token activo (para recuperar).
            // No se muestran bajas/posibles sin token.
            $where[] = "(a.estado = 'matriculado'
                         OR EXISTS (SELECT 1 FROM token t
                                    WHERE t.alumno_id = a.id
                                      AND t.usado = FALSE
                                      AND (t.fecha_caducidad IS NULL
                                           OR t.fecha_caducidad >= CURDATE())))";
        }
        if ($busqueda !== '') {
            $where[] = '(a.nombre LIKE ? OR a.apellidos LIKE ? OR a.email LIKE ?)';
            $like    = '%' . $busqueda . '%';
            $tipos  .= 'sss';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if ($filtroEstado !== '') {
            $where[] = 'a.estado = ?';
            $tipos  .= 's';
            $params[] = $filtroEstado;
        }
        if ($soloConTokens) {
            $where[] = 'EXISTS (SELECT 1 FROM token t WHERE t.alumno_id = a.id
                                AND t.usado = FALSE
                                AND (t.fecha_caducidad IS NULL OR t.fecha_caducidad >= CURDATE()))';
        }
        if ($where) {
            $sqlBase .= ' WHERE ' . implode(' AND ', $where);
        }
        $sqlBase .= ' ORDER BY a.apellidos, a.nombre';

        if ($tipos === '') {
            $resultado = $conexion->query($sqlBase);
            $alumnos   = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $stmt = $conexion->prepare($sqlBase);
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $alumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $observacionesPorAlumno = $this->cargarObservaciones('alumno', array_column($alumnos, 'id'));

        $vista     = 'alumnos';
        $csrfToken = $this->csrfToken();
        require ROOT . '/app/views/profesor/alumnos.php';
    }

    public function grupos(): void {
        $this->requireProfesorAuth();
        global $conexion;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Toda accion POST en /profesor/grupos requiere token CSRF valido.
            $this->verificarCsrf('/profesor/grupos');
            $accion = $_POST['accion'] ?? '';

            // Profesor y admin pueden anadir observaciones sobre los grupos.
            if ($accion === 'observaciones') {
                $grupoId = (int)($_POST['destino_id'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');
                if ($grupoId <= 0 || $texto === '') {
                    $this->redirect('/profesor/grupos', 'error_datos');
                }
                if (!$this->esAdmin()) {
                    // Verificar que el grupo pertenezca al profesor
                    $stmt = $conexion->prepare("SELECT id FROM grupo WHERE id = ? AND profesor_id = ? LIMIT 1");
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $grupoId, $profesorId);
                    $stmt->execute();
                    if (!$stmt->get_result()->fetch_assoc()) {
                        $this->redirect('/profesor/grupos', 'sin_permiso');
                    }
                }
                $stmt = $conexion->prepare(
                    "INSERT INTO observacion_profesor (profesor_id, tipo_destino, destino_id, texto)
                     VALUES (?, 'grupo', ?, ?)"
                );
                $profesorId = $this->profesorId();
                $stmt->bind_param('sis', $profesorId, $grupoId, $texto);
                $stmt->execute();
                $this->redirect('/profesor/grupos', 'observacion_guardada');
            }

            if ($accion === 'eliminar_observacion') {
                $obsId = (int)($_POST['observacion_id'] ?? 0);
                if ($obsId <= 0) {
                    $this->redirect('/profesor/grupos', 'error_datos');
                }
                if ($this->esAdmin()) {
                    $stmt = $conexion->prepare("DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'grupo'");
                    $stmt->bind_param('i', $obsId);
                } else {
                    $stmt = $conexion->prepare(
                        "DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'grupo' AND profesor_id = ?"
                    );
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $obsId, $profesorId);
                }
                $stmt->execute();
                $this->redirect('/profesor/grupos', 'observacion_eliminada');
            }

            // Crear / editar / eliminar son acciones reservadas al admin.
            // El profesor solo tiene acceso de lectura a esta vista.
            if (!$this->esAdmin()) {
                $this->redirect('/profesor/grupos', 'sin_permiso');
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $salaId = (int)($_POST['sala_id'] ?? 0);
            $diaSemana = $_POST['dia_semana'] ?? '';
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            $nivel = $_POST['nivel'] ?? 'iniciacion';
            $tipo = $_POST['tipo'] ?? 'teatro';
            $curso = trim($_POST['curso'] ?? '');
            $fechaInicio = $_POST['fecha_inicio_curso'] ?? '';
            $fechaFin = $_POST['fecha_fin_curso'] ?? '';
            $activo = isset($_POST['activo']) ? 1 : 0;

            // Si el formulario no envia fecha_fin_curso, la calculamos como +1 anio.
            if ($fechaFin === '' && $fechaInicio !== '') {
                $ts = strtotime($fechaInicio . ' +1 year');
                if ($ts !== false) {
                    $fechaFin = date('Y-m-d', $ts);
                }
            }

            if (in_array($accion, ['crear', 'actualizar'], true)) {
                if (
                    $nombre === '' || $salaId <= 0 || $diaSemana === '' ||
                    $horaInicio === '' || $horaFin === '' || $fechaInicio === '' || $fechaFin === ''
                ) {
                    $this->redirect('/profesor/grupos', 'error_datos');
                }
            }

            if ($accion === 'crear') {
                $stmt = $conexion->prepare(
                    "INSERT INTO grupo (
                        profesor_id, sala_id, nombre, dia_semana, hora_inicio, hora_fin,
                        nivel, tipo, curso, fecha_inicio_curso, fecha_fin_curso, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?, ?)"
                );
                $profesorId = $this->profesorId();
                $stmt->bind_param(
                    'sisssssssssi',
                    $profesorId,
                    $salaId,
                    $nombre,
                    $diaSemana,
                    $horaInicio,
                    $horaFin,
                    $nivel,
                    $tipo,
                    $curso,
                    $fechaInicio,
                    $fechaFin,
                    $activo
                );
                $stmt->execute();
                $this->redirect('/profesor/grupos', 'grupo_creado');
            }

            if ($accion === 'actualizar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $this->redirect('/profesor/grupos', 'error_datos');
                }

                $sql = "UPDATE grupo
                        SET sala_id = ?, nombre = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ?,
                            nivel = ?, tipo = ?, curso = NULLIF(?, ''), fecha_inicio_curso = ?, fecha_fin_curso = ?, activo = ?";
                $tipos = 'isssssssssi';
                $params = [$salaId, $nombre, $diaSemana, $horaInicio, $horaFin, $nivel, $tipo, $curso, $fechaInicio, $fechaFin, $activo];

                if ($this->esAdmin()) {
                    $sql .= " WHERE id = ?";
                    $tipos .= 'i';
                    $params[] = $id;
                } else {
                    $sql .= " WHERE id = ? AND profesor_id = ?";
                    $tipos .= 'is';
                    $params[] = $id;
                    $params[] = $this->profesorId();
                }

                $stmt = $conexion->prepare($sql);
                $stmt->bind_param($tipos, ...$params);
                $stmt->execute();
                $this->redirect('/profesor/grupos', 'grupo_actualizado');
            }

            if ($accion === 'eliminar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        if ($this->esAdmin()) {
                            $stmt = $conexion->prepare("DELETE FROM grupo WHERE id = ?");
                            $stmt->bind_param('i', $id);
                        } else {
                            $stmt = $conexion->prepare("DELETE FROM grupo WHERE id = ? AND profesor_id = ?");
                            $profesorId = $this->profesorId();
                            $stmt->bind_param('is', $id, $profesorId);
                        }
                        $stmt->execute();
                        $this->redirect('/profesor/grupos', 'grupo_eliminado');
                    } catch (mysqli_sql_exception $e) {
                        $this->redirect('/profesor/grupos', 'error_relacion');
                    }
                }
            }
        }

        $salas    = $this->obtenerSalas();
        $editarId = (int)($_GET['editar'] ?? 0);
        $mensaje  = $this->mensaje($_GET['mensaje'] ?? '');

        // Filtros para el listado de grupos.
        $busqueda    = trim((string)($_GET['q']     ?? ''));
        $filtroTipo  = (string)($_GET['tipo']      ?? '');
        $filtroEstado = (string)($_GET['activo']   ?? ''); // '', '1', '0'
        $tiposValidos = ['', 'teatro', 'improvisacion', 'actuacion', 'danza', 'canto'];
        if (!in_array($filtroTipo, $tiposValidos, true)) {
            $filtroTipo = '';
        }

        $sqlBase = "SELECT g.*, s.nombre AS sala_nombre, s.espacio_nombre,
                           s.aforo_maximo AS sala_aforo, s.tipo AS sala_tipo,
                           CONCAT(p.nombre, ' ', p.apellidos) AS profesor_nombre,
                           COUNT(CASE WHEN ag.activo = TRUE THEN 1 END) AS total_alumnos
                    FROM grupo g
                    LEFT JOIN sala s ON s.id = g.sala_id
                    LEFT JOIN profesor p ON p.usuario_id = g.profesor_id
                    LEFT JOIN alumno_grupo ag ON ag.grupo_id = g.id";
        $where  = [];
        $tipos  = '';
        $params = [];
        if (!$this->esAdmin()) {
            $where[] = 'g.profesor_id = ?';
            $tipos  .= 's';
            $params[] = $this->profesorId();
        }
        if ($busqueda !== '') {
            $where[] = '(g.nombre LIKE ? OR g.curso LIKE ?)';
            $like    = '%' . $busqueda . '%';
            $tipos  .= 'ss';
            $params[] = $like; $params[] = $like;
        }
        if ($filtroTipo !== '') {
            $where[] = 'g.tipo = ?';
            $tipos  .= 's';
            $params[] = $filtroTipo;
        }
        if ($filtroEstado === '1' || $filtroEstado === '0') {
            $where[] = 'g.activo = ?';
            $tipos  .= 'i';
            $params[] = (int)$filtroEstado;
        } elseif (!$this->esAdmin()) {
            // Por defecto, un profesor solo ve grupos activos (los que admin no ha "eliminado").
            $where[] = 'g.activo IS NOT FALSE';
        }
        if ($where) {
            $sqlBase .= ' WHERE ' . implode(' AND ', $where);
        }
        $sqlBase .= ' GROUP BY g.id ORDER BY g.nombre';

        if ($tipos === '') {
            $resultado = $conexion->query($sqlBase);
            $grupos    = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $stmt = $conexion->prepare($sqlBase);
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $grupos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $observacionesPorGrupo = $this->cargarObservaciones('grupo', array_column($grupos, 'id'));

        $vista     = 'grupos';
        $csrfToken = $this->csrfToken();


        require ROOT . '/app/views/profesor/grupos.php';
    }

    public function clases(): void {
        $this->requireProfesorAuth();
        global $conexion;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Toda accion POST en /profesor/clases requiere token CSRF valido.
            $this->verificarCsrf('/profesor/clases');
            $accion = $_POST['accion'] ?? '';

            // Profesor y admin pueden anadir observaciones sobre cada clase.
            if ($accion === 'observaciones') {
                $claseId = (int)($_POST['destino_id'] ?? 0);
                $texto = trim($_POST['texto'] ?? '');
                if ($claseId <= 0 || $texto === '') {
                    $this->redirect('/profesor/clases', 'error_datos');
                }
                if (!$this->esAdmin()) {
                    // Verificar que la clase pertenezca a un grupo de este profesor.
                    $stmt = $conexion->prepare(
                        "SELECT c.id
                         FROM clase c
                         INNER JOIN grupo g ON g.id = c.grupo_id
                         WHERE c.id = ? AND g.profesor_id = ?
                         LIMIT 1"
                    );
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $claseId, $profesorId);
                    $stmt->execute();
                    if (!$stmt->get_result()->fetch_assoc()) {
                        $this->redirect('/profesor/clases', 'sin_permiso');
                    }
                }
                $stmt = $conexion->prepare(
                    "INSERT INTO observacion_profesor (profesor_id, tipo_destino, destino_id, texto)
                     VALUES (?, 'clase', ?, ?)"
                );
                $profesorId = $this->profesorId();
                $stmt->bind_param('sis', $profesorId, $claseId, $texto);
                $stmt->execute();
                $this->redirect('/profesor/clases', 'observacion_guardada');
            }

            if ($accion === 'eliminar_observacion') {
                $obsId = (int)($_POST['observacion_id'] ?? 0);
                if ($obsId <= 0) {
                    $this->redirect('/profesor/clases', 'error_datos');
                }
                if ($this->esAdmin()) {
                    $stmt = $conexion->prepare("DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'clase'");
                    $stmt->bind_param('i', $obsId);
                } else {
                    $stmt = $conexion->prepare(
                        "DELETE FROM observacion_profesor WHERE id = ? AND tipo_destino = 'clase' AND profesor_id = ?"
                    );
                    $profesorId = $this->profesorId();
                    $stmt->bind_param('is', $obsId, $profesorId);
                }
                $stmt->execute();
                $this->redirect('/profesor/clases', 'observacion_eliminada');
            }

            $grupoId = (int)($_POST['grupo_id'] ?? 0);
            $salaId = (int)($_POST['sala_id'] ?? 0);
            $fecha = $_POST['fecha'] ?? '';
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            $cupo = (int)($_POST['cupo_maximo'] ?? 0);
            $estado = $_POST['estado'] ?? 'programada';

            // Si el formulario no envia hora_fin, la tomamos del grupo (misma duracion).
            if ($horaFin === '' && $horaInicio !== '' && $grupoId > 0) {
                $stmtGrupo = $conexion->prepare("SELECT hora_inicio, hora_fin FROM grupo WHERE id = ?");
                $stmtGrupo->bind_param('i', $grupoId);
                $stmtGrupo->execute();
                $grupoHorario = $stmtGrupo->get_result()->fetch_assoc();
                if ($grupoHorario && !empty($grupoHorario['hora_inicio']) && !empty($grupoHorario['hora_fin'])) {
                    $inicioGrupo = strtotime($grupoHorario['hora_inicio']);
                    $finGrupo    = strtotime($grupoHorario['hora_fin']);
                    if ($inicioGrupo !== false && $finGrupo !== false && $finGrupo > $inicioGrupo) {
                        $duracion = $finGrupo - $inicioGrupo;
                        $finCalc  = strtotime($horaInicio) + $duracion;
                        if ($finCalc !== false) {
                            $horaFin = date('H:i:s', $finCalc);
                        }
                    }
                }
                // Fallback: +1 hora si no se pudo calcular la duracion del grupo.
                if ($horaFin === '') {
                    $ts = strtotime($horaInicio . ' +1 hour');
                    if ($ts !== false) {
                        $horaFin = date('H:i:s', $ts);
                    }
                }
            }

            if (in_array($accion, ['crear', 'actualizar'], true)) {
                if ($grupoId <= 0 || $salaId <= 0 || $fecha === '' || $horaInicio === '' || $horaFin === '' || $cupo <= 0) {
                    $this->redirect('/profesor/clases', 'error_datos');
                }

                // ─── Validar conflictos de agenda ────────────────────────────
                $idEditando = $accion === 'actualizar' ? (int)($_POST['id'] ?? 0) : 0;

                // 1) Duplicado exacto: mismo grupo, misma fecha, misma hora_inicio.
                $sqlDup = "SELECT id FROM clase
                           WHERE grupo_id = ? AND fecha = ? AND hora_inicio = ?";
                $tiposDup  = 'iss';
                $paramsDup = [$grupoId, $fecha, $horaInicio];
                if ($idEditando > 0) {
                    $sqlDup .= " AND id != ?";
                    $tiposDup .= 'i';
                    $paramsDup[] = $idEditando;
                }
                $sqlDup .= " LIMIT 1";
                $stmtDup = $conexion->prepare($sqlDup);
                $stmtDup->bind_param($tiposDup, ...$paramsDup);
                $stmtDup->execute();
                if ($stmtDup->get_result()->fetch_assoc()) {
                    $this->redirect('/profesor/clases', 'clase_duplicada');
                }

                // 2) Solapamiento de horario del mismo profesor en el mismo dia.
                $sqlSolape = "SELECT c.id FROM clase c
                              INNER JOIN grupo g ON g.id = c.grupo_id
                              WHERE g.profesor_id = (SELECT profesor_id FROM grupo WHERE id = ?)
                                AND c.fecha = ?
                                AND c.estado <> 'cancelada'
                                AND NOT (c.hora_fin <= ? OR c.hora_inicio >= ?)";
                $tiposSol  = 'isss';
                $paramsSol = [$grupoId, $fecha, $horaInicio, $horaFin];
                if ($idEditando > 0) {
                    $sqlSolape .= " AND c.id != ?";
                    $tiposSol .= 'i';
                    $paramsSol[] = $idEditando;
                }
                $sqlSolape .= " LIMIT 1";
                $stmtSol = $conexion->prepare($sqlSolape);
                $stmtSol->bind_param($tiposSol, ...$paramsSol);
                $stmtSol->execute();
                if ($stmtSol->get_result()->fetch_assoc()) {
                    $this->redirect('/profesor/clases', 'clase_solapada');
                }

                // 3) Sala ocupada por otra clase en el mismo intervalo.
                $sqlSala = "SELECT c.id FROM clase c
                            WHERE c.sala_id = ?
                              AND c.fecha = ?
                              AND c.estado <> 'cancelada'
                              AND NOT (c.hora_fin <= ? OR c.hora_inicio >= ?)";
                $tiposSala  = 'isss';
                $paramsSala = [$salaId, $fecha, $horaInicio, $horaFin];
                if ($idEditando > 0) {
                    $sqlSala .= " AND c.id != ?";
                    $tiposSala .= 'i';
                    $paramsSala[] = $idEditando;
                }
                $sqlSala .= " LIMIT 1";
                $stmtSala = $conexion->prepare($sqlSala);
                $stmtSala->bind_param($tiposSala, ...$paramsSala);
                $stmtSala->execute();
                if ($stmtSala->get_result()->fetch_assoc()) {
                    $this->redirect('/profesor/clases', 'sala_ocupada');
                }
            }

            if ($accion === 'crear') {
                $stmt = $conexion->prepare(
                    "INSERT INTO clase (grupo_id, sala_id, fecha, hora_inicio, hora_fin, cupo_maximo, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('iisssis', $grupoId, $salaId, $fecha, $horaInicio, $horaFin, $cupo, $estado);
                $stmt->execute();
                $this->redirect('/profesor/clases', 'clase_creada');
            }

            if ($accion === 'actualizar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $this->redirect('/profesor/clases', 'error_datos');
                }

                $sql = "UPDATE clase c
                        INNER JOIN grupo g ON g.id = c.grupo_id
                        SET c.grupo_id = ?, c.sala_id = ?, c.fecha = ?, c.hora_inicio = ?, c.hora_fin = ?, c.cupo_maximo = ?, c.estado = ?
                        WHERE c.id = ?";
                $tipos = 'iisssisi';
                $params = [$grupoId, $salaId, $fecha, $horaInicio, $horaFin, $cupo, $estado, $id];

                if (!$this->esAdmin()) {
                    $sql .= " AND g.profesor_id = ?";
                    $tipos .= 's';
                    $params[] = $this->profesorId();
                }

                $stmt = $conexion->prepare($sql);
                $stmt->bind_param($tipos, ...$params);
                $stmt->execute();
                $this->redirect('/profesor/clases', 'clase_actualizada');
            }

            if ($accion === 'eliminar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        if ($this->esAdmin()) {
                            $stmt = $conexion->prepare("DELETE FROM clase WHERE id = ?");
                            $stmt->bind_param('i', $id);
                        } else {
                            $stmt = $conexion->prepare(
                                "DELETE c FROM clase c
                                 INNER JOIN grupo g ON g.id = c.grupo_id
                                 WHERE c.id = ? AND g.profesor_id = ?"
                            );
                            $profesorId = $this->profesorId();
                            $stmt->bind_param('is', $id, $profesorId);
                        }
                        $stmt->execute();
                        $this->redirect('/profesor/clases', 'clase_eliminada');
                    } catch (mysqli_sql_exception $e) {
                        $this->redirect('/profesor/clases', 'error_relacion');
                    }
                }
            }
        }

        $gruposDisponibles = $this->obtenerGruposProfesor(true);
        $salas             = $this->obtenerSalas();
        $editarId          = (int)($_GET['editar'] ?? 0);
        $mensaje           = $this->mensaje($_GET['mensaje'] ?? '');

        // Filtros del listado de clases.
        $filtroGrupo       = (int)($_GET['grupo']  ?? 0);
        $filtroEstadoClase = (string)($_GET['estado_clase'] ?? '');
        $filtroDesde       = (string)($_GET['desde'] ?? date('Y-m-d'));
        $filtroHasta       = (string)($_GET['hasta'] ?? '');
        $estadosClaseValidos = $this->esAdmin()
            ? ['', 'programada', 'cancelada', 'realizada']
            : ['', 'programada', 'realizada'];
        if (!in_array($filtroEstadoClase, $estadosClaseValidos, true)) {
            $filtroEstadoClase = '';
        }
        $fechaValida = static fn(string $s): bool =>
            $s === '' || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
        if (!$fechaValida($filtroDesde)) $filtroDesde = '';
        if (!$fechaValida($filtroHasta)) $filtroHasta = '';

        // Construir WHERE y parámetros para los filtros del listado completo.
        $where  = [];
        $tipos  = '';
        $params = [];
        if (!$this->esAdmin()) {
            // Histórico completo: el INNER JOIN con grupo ya descarta clases
            // huérfanas. No filtramos por g.activo para no ocultar las clases
            // de grupos desactivados (misma fuente de datos que el calendario).
            $where[] = 'g.profesor_id = ?';
            $tipos  .= 's';
            $params[] = $this->profesorId();
            // El profesor nunca ve clases canceladas en su listado.
            $where[] = "c.estado <> 'cancelada'";
        }
        if ($filtroGrupo > 0) {
            $where[] = 'c.grupo_id = ?';
            $tipos  .= 'i';
            $params[] = $filtroGrupo;
        }
        if ($filtroEstadoClase !== '') {
            $where[] = 'c.estado = ?';
            $tipos  .= 's';
            $params[] = $filtroEstadoClase;
        }
        if ($filtroDesde !== '') {
            $where[] = 'c.fecha >= ?';
            $tipos  .= 's';
            $params[] = $filtroDesde;
        }
        if ($filtroHasta !== '') {
            $where[] = 'c.fecha <= ?';
            $tipos  .= 's';
            $params[] = $filtroHasta;
        }
        // Deduplicar: si existen registros duplicados en la BD (mismo grupo +
        // fecha + hora), mostrar solo el de ID más bajo, igual que el calendario.
        $where[] = 'c.id IN (SELECT MIN(c2.id) FROM clase c2 GROUP BY c2.grupo_id, c2.fecha, c2.hora_inicio)';
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        // ─── Paginación del listado completo (6 por página) ──────────────────
        $paginaListado     = isset($_GET['pl']) ? max(1, (int)$_GET['pl']) : 1;
        $porPaginaListado  = 6;

        // Total de filas que cumplen los filtros (sin paginar).
        $sqlCount = "SELECT COUNT(*) AS total
                     FROM clase c
                     INNER JOIN grupo g ON g.id = c.grupo_id
                     LEFT JOIN sala s ON s.id = c.sala_id" . $whereSql;
        if ($tipos === '') {
            $totalClases = (int)$conexion->query($sqlCount)->fetch_assoc()['total'];
        } else {
            $stmt = $conexion->prepare($sqlCount);
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $totalClases = (int)$stmt->get_result()->fetch_assoc()['total'];
        }
        $totalPaginasListado = max(1, (int)ceil($totalClases / $porPaginaListado));
        if ($paginaListado > $totalPaginasListado) $paginaListado = $totalPaginasListado;
        $offsetListado = ($paginaListado - 1) * $porPaginaListado;

        $sqlBase = "SELECT c.*, g.nombre AS grupo_nombre, g.tipo AS grupo_tipo, s.nombre AS sala_nombre
                    FROM clase c
                    INNER JOIN grupo g ON g.id = c.grupo_id
                    LEFT JOIN sala s ON s.id = c.sala_id"
                  . $whereSql
                  . ' ORDER BY c.fecha ASC, c.hora_inicio ASC'
                  . " LIMIT $porPaginaListado OFFSET $offsetListado";

        if ($tipos === '') {
            $resultado = $conexion->query($sqlBase);
            $clases    = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $stmt = $conexion->prepare($sqlBase);
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $clases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // Cargamos las observaciones especificas de cada clase (tipo_destino='clase').
        $idsClases = array_map(static fn($c) => (int)$c['id'], $clases);
        $observacionesPorClase = $this->cargarObservaciones('clase', $idsClases);

        // ─── Tarjetas superiores ──────────────────────────────────────────────
        // Regla: si hay clases hoy o mañana, mostrar esas páginas (modo "urgente").
        //        Si no hay nada inmediato, mostrar las próximas programadas paginadas (modo "proximas").
        // 6 tarjetas por página en ambos modos.
        $pagina    = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $porPagina = 6;
        $offset    = ($pagina - 1) * $porPagina;

        $columnasProximas = "c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado,
                             g.nombre AS grupo_nombre, g.tipo AS grupo_tipo, g.id AS grupo_id,
                             s.nombre AS sala_nombre,
                             (SELECT COUNT(*) FROM alumno_grupo ag
                              WHERE ag.grupo_id = g.id AND ag.activo = TRUE) AS total_alumnos,
                             (SELECT COUNT(*) FROM asistencia a
                              WHERE a.clase_id = c.id AND a.estado = 'avisado') AS total_avisos,
                             (SELECT COUNT(*) FROM asistencia a
                              WHERE a.clase_id = c.id AND a.estado = 'asiste') AS total_confirmados,
                             (SELECT COUNT(DISTINCT t.alumno_id) FROM token t
                              INNER JOIN alumno_grupo ag2 ON ag2.alumno_id = t.alumno_id
                              WHERE ag2.grupo_id = g.id
                                AND ag2.activo = TRUE
                                AND t.usado = FALSE
                                AND (t.fecha_caducidad IS NULL
                                     OR t.fecha_caducidad >= CURDATE())) AS total_tokens";

        // Subquery defensiva: para cada (profesor, fecha, hora_inicio) toma SOLO la
        // clase con el id más bajo. Así no se muestran duplicados aunque la BBDD
        // contenga registros repetidos. Sirve mientras admin ejecuta el script
        // sql/limpiar_clases_duplicadas.sql para arreglar el origen.
        $subqDedupe = "(SELECT MIN(c2.id) FROM clase c2
                        INNER JOIN grupo g2 ON g2.id = c2.grupo_id
                        WHERE c2.estado = 'programada'
                        GROUP BY c2.grupo_id, c2.fecha, c2.hora_inicio)";

        // 1) Contar cuantas clases urgentes hay (hoy y mañana) para decidir el modo.
        $sqlCountUrg = "SELECT COUNT(*) AS total
                        FROM clase c
                        INNER JOIN grupo g ON g.id = c.grupo_id
                        WHERE c.estado = 'programada'
                          AND c.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                          AND c.id IN $subqDedupe";
        if ($this->esAdmin()) {
            $totalUrgentes = (int)$conexion->query($sqlCountUrg)->fetch_assoc()['total'];
        } else {
            $stmt = $conexion->prepare($sqlCountUrg . " AND g.profesor_id = ?");
            $profesorId = $this->profesorId();
            $stmt->bind_param('s', $profesorId);
            $stmt->execute();
            $totalUrgentes = (int)$stmt->get_result()->fetch_assoc()['total'];
        }

        // 2) Si hay clases hoy o mañana, modo "urgente": muestra esas y, a
        //    continuación, las siguientes clases futuras (para no dejar 1 sola
        //    tarjeta). Trae desde HOY en adelante, ordenadas por fecha.
        if ($totalUrgentes > 0) {
            // Contar el total de clases desde hoy (para la paginación).
            $sqlCountDesdeHoy = "SELECT COUNT(*) AS total
                                 FROM clase c
                                 INNER JOIN grupo g ON g.id = c.grupo_id
                                 WHERE c.estado = 'programada'
                                   AND c.fecha >= CURDATE()
                                   AND c.id IN $subqDedupe";
            if ($this->esAdmin()) {
                $totalDesdeHoy = (int)$conexion->query($sqlCountDesdeHoy)->fetch_assoc()['total'];
            } else {
                $stmt = $conexion->prepare($sqlCountDesdeHoy . " AND g.profesor_id = ?");
                $profesorId = $this->profesorId();
                $stmt->bind_param('s', $profesorId);
                $stmt->execute();
                $totalDesdeHoy = (int)$stmt->get_result()->fetch_assoc()['total'];
            }

            $sqlUrgentes = "SELECT $columnasProximas
                            FROM clase c
                            INNER JOIN grupo g ON g.id = c.grupo_id
                            LEFT JOIN sala s ON s.id = c.sala_id
                            WHERE c.estado = 'programada'
                              AND c.fecha >= CURDATE()
                              AND c.id IN $subqDedupe";

            if ($this->esAdmin()) {
                $sqlUrgentes .= " ORDER BY c.fecha, c.hora_inicio
                                  LIMIT $porPagina OFFSET $offset";
                $resultado = $conexion->query($sqlUrgentes);
                $proximasClases = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                $sqlUrgentes .= " AND g.profesor_id = ?
                                  ORDER BY c.fecha, c.hora_inicio
                                  LIMIT $porPagina OFFSET $offset";
                $stmt = $conexion->prepare($sqlUrgentes);
                $profesorId = $this->profesorId();
                $stmt->bind_param('s', $profesorId);
                $stmt->execute();
                $proximasClases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            $modoTarjetas       = 'urgente';
            $tarjetasPaginables = ($totalDesdeHoy > $porPagina);
        } else {
            // 3) Modo "proximas": las siguientes programadas, paginadas de 6 en 6.
            // Total de próximas (para que el pager sepa cuántas páginas hay).
            $sqlCountProx = "SELECT COUNT(*) AS total
                             FROM clase c
                             INNER JOIN grupo g ON g.id = c.grupo_id
                             WHERE c.estado = 'programada'
                               AND c.fecha > DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                               AND c.id IN $subqDedupe";
            if ($this->esAdmin()) {
                $totalProximas = (int)$conexion->query($sqlCountProx)->fetch_assoc()['total'];
            } else {
                $stmt = $conexion->prepare($sqlCountProx . " AND g.profesor_id = ?");
                $profesorId = $this->profesorId();
                $stmt->bind_param('s', $profesorId);
                $stmt->execute();
                $totalProximas = (int)$stmt->get_result()->fetch_assoc()['total'];
            }

            $sqlProximas = "SELECT $columnasProximas
                            FROM clase c
                            INNER JOIN grupo g ON g.id = c.grupo_id
                            LEFT JOIN sala s ON s.id = c.sala_id
                            WHERE c.estado = 'programada'
                              AND c.fecha > DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                              AND c.id IN $subqDedupe";

            if ($this->esAdmin()) {
                $sqlProximas .= " ORDER BY c.fecha, c.hora_inicio
                                  LIMIT $porPagina OFFSET $offset";
                $resultado = $conexion->query($sqlProximas);
                $proximasClases = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
            } else {
                $sqlProximas .= " AND g.profesor_id = ?
                                  ORDER BY c.fecha, c.hora_inicio
                                  LIMIT $porPagina OFFSET $offset";
                $stmt = $conexion->prepare($sqlProximas);
                $profesorId = $this->profesorId();
                $stmt->bind_param('s', $profesorId);
                $stmt->execute();
                $proximasClases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            $modoTarjetas       = 'proximas';
            $tarjetasPaginables = ($totalProximas > $porPagina);
        }

        $vista     = 'clases';
        $csrfToken = $this->csrfToken();
        require ROOT . '/app/views/profesor/clases.php';
    }

    // ─── ASISTENCIA ──────────────────────────────────────────────────────────────
    public function asistencia(): void {
        $this->requireProfesorAuth();
        global $conexion;

        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $porPagina = 4;
        $offset = ($pagina - 1) * $porPagina;

        // Indica si el pase de lista corresponde a una clase o a un evento especial.
        $tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? 'clase';

        $claseId = (int)($_GET['clase_id'] ?? $_POST['clase_id'] ?? 0);

        // Recoge el identificador del evento especial cuando el modo es "evento".
        $eventoId = (int)($_GET['evento_id'] ?? $_POST['evento_id'] ?? 0);

        // Evita valores manipulados o no previstos en la URL.
        if (!in_array($tipo, ['clase', 'evento'], true)) {
        $tipo = 'clase';
        }

        // Guarda en una sola variable el ID que corresponde al tipo seleccionado.
        $idOrigen = ($tipo === 'evento') ? $eventoId : $claseId;


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {

            // Construye la ruta correcta según se esté guardando una clase o un evento.
        $rutaError = $idOrigen > 0
        ? '/profesor/asistencia?tipo=' . urlencode($tipo)
        . ($tipo === 'evento'
            ? '&evento_id=' . $eventoId
            : '&clase_id=' . $claseId)
        : '/profesor/asistencia?tipo=' . urlencode($tipo);

            $this->verificarCsrf($rutaError);

            $filas = $_POST['filas'] ?? [];

           // Comprueba que exista un identificador válido para la clase o el evento.
        if ($idOrigen <= 0) {
            $this->redirect(
        '/profesor/asistencia?tipo=' . urlencode($tipo),
        'error_datos'
    );
    }

            foreach ($filas as $alumnoId => $datos) {
                $alumnoId = (int)$alumnoId;
                $estado = $datos['estado'] ?? 'ausente';
                if ($alumnoId <= 0 || !in_array($estado, ['asiste', 'ausente', 'avisado'], true)) {
                    continue;
                }

             // Guarda la asistencia en la tabla correspondiente.
if ($tipo === 'evento') {
    $stmt = $conexion->prepare(
        "INSERT INTO asistencia_evento
            (alumno_id, evento_id, estado, fecha_aviso, aviso_valido)
         VALUES (?, ?, ?, NULL, FALSE)
         ON DUPLICATE KEY UPDATE
            estado = VALUES(estado)"
    );

    $stmt->bind_param('iis', $alumnoId, $eventoId, $estado);
} else {
    $stmt = $conexion->prepare(
        "INSERT INTO asistencia
            (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
         VALUES (?, ?, ?, NULL, FALSE)
         ON DUPLICATE KEY UPDATE
            estado = VALUES(estado)"
    );

    $stmt->bind_param('iis', $alumnoId, $claseId, $estado);
}

$stmt->execute();
            }

            // Vuelve al pase de lista correcto después de guardar.
if ($tipo === 'evento') {
    $this->redirect(
        '/profesor/asistencia?tipo=evento&evento_id=' . $eventoId,
        'asistencia_guardada'
    );
}

$this->redirectConClase('asistencia_guardada', $claseId);
        }

        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');

        // Listado de próximas clases del profesor (desde hoy) con conteo de avisos, confirmados y alumnos
        $sqlProximas = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado,
                               g.nombre AS grupo_nombre, g.tipo AS grupo_tipo, g.id AS grupo_id,
                               s.nombre AS sala_nombre,
                               (SELECT COUNT(*) FROM alumno_grupo ag
                                WHERE ag.grupo_id = g.id AND ag.activo = TRUE) AS total_alumnos,
                               (SELECT COUNT(*) FROM asistencia a
                                WHERE a.clase_id = c.id AND a.estado = 'avisado') AS total_avisos,
                               (SELECT COUNT(*) FROM asistencia a
                                WHERE a.clase_id = c.id AND a.estado = 'asiste') AS total_confirmados,
                               (SELECT COUNT(DISTINCT t.alumno_id) FROM token t
                                INNER JOIN alumno_grupo ag2 ON ag2.alumno_id = t.alumno_id
                                WHERE ag2.grupo_id = g.id
                                  AND ag2.activo = TRUE
                                  AND t.usado = FALSE
                                  AND (t.fecha_caducidad IS NULL
                                       OR t.fecha_caducidad >= CURDATE())) AS total_tokens
                        FROM clase c
                        INNER JOIN grupo g ON g.id = c.grupo_id
                        LEFT JOIN sala s ON s.id = c.sala_id
                        WHERE c.fecha >= CURDATE() AND c.estado = 'programada'";

        if ($this->esAdmin()) {
            $sqlProximas .= " ORDER BY c.fecha, c.hora_inicio LIMIT $porPagina OFFSET $offset";
            $resultado = $conexion->query($sqlProximas);
            $proximasClases = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $sqlProximas .= " AND g.profesor_id = ? ORDER BY c.fecha, c.hora_inicio LIMIT $porPagina OFFSET $offset";
            $stmt = $conexion->prepare($sqlProximas);
            $profesorId = $this->profesorId();
            $stmt->bind_param('s', $profesorId);
            $stmt->execute();
            $proximasClases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // Historial breve (clases pasadas recientes) por si quiere editar la asistencia a posteriori
        $sqlPasadas = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado,
                              g.nombre AS grupo_nombre, g.tipo AS grupo_tipo,
                              s.nombre AS sala_nombre,
                              (SELECT COUNT(*) FROM asistencia a
                               WHERE a.clase_id = c.id AND a.estado = 'avisado') AS total_avisos
                       FROM clase c
                       INNER JOIN grupo g ON g.id = c.grupo_id
                       LEFT JOIN sala s ON s.id = c.sala_id
                       WHERE c.fecha < CURDATE()";

        if ($this->esAdmin()) {
            $sqlPasadas .= " ORDER BY c.fecha DESC, c.hora_inicio DESC LIMIT 10";
            $resultado = $conexion->query($sqlPasadas);
            $clasesPasadas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $sqlPasadas .= " AND g.profesor_id = ? ORDER BY c.fecha DESC, c.hora_inicio DESC LIMIT 10";
            $stmt = $conexion->prepare($sqlPasadas);
            $profesorId = $this->profesorId();
            $stmt->bind_param('s', $profesorId);
            $stmt->execute();
            $clasesPasadas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $detalleClase = null;
        $alumnos = [];
        $registros = [];
        $avisos = [];
        $resumen = ['asiste' => 0, 'ausente' => 0, 'avisado' => 0];

// Datos del evento especial cuando el pase de lista trabaja en modo evento.
$detalleEvento = null;


// Carga el evento especial y sus alumnos inscritos cuando el modo es "evento".
if ($tipo === 'evento' && $eventoId > 0) {
    $sqlDetalleEvento = "
        SELECT
            e.id,
            e.nombre,
            e.tipo,
            e.descripcion,
            e.fecha,
            e.hora,
            e.plazas_maximas
        FROM evento_grupal e
        WHERE e.id = ?
    ";

    if (!$this->esAdmin()) {
        $sqlDetalleEvento .= " AND e.profesor_id = ?";
        $stmt = $conexion->prepare($sqlDetalleEvento);
        $profesorId = $this->profesorId();
        $stmt->bind_param('is', $eventoId, $profesorId);
    } else {
        $stmt = $conexion->prepare($sqlDetalleEvento);
        $stmt->bind_param('i', $eventoId);
    }

    $stmt->execute();
    $detalleEvento = $stmt->get_result()->fetch_assoc();

    if ($detalleEvento) {
        $stmt = $conexion->prepare(
            "SELECT
                a.id,
                a.nombre,
                a.apellidos,
                0 AS tokens_disponibles
             FROM inscripcion_evento i
             INNER JOIN alumno a ON a.id = i.alumno_id
             WHERE i.evento_id = ?
               AND i.estado = 'inscrito'
             ORDER BY a.apellidos, a.nombre"
        );
        $stmt->bind_param('i', $eventoId);
        $stmt->execute();
        $alumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt = $conexion->prepare(
            "SELECT alumno_id, estado, fecha_aviso, aviso_valido
             FROM asistencia_evento
             WHERE evento_id = ?"
        );
        $stmt->bind_param('i', $eventoId);
        $stmt->execute();

        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $registro) {
            $aid = (int)$registro['alumno_id'];
            $registros[$aid] = $registro['estado'];

            if ($registro['estado'] === 'avisado') {
                $avisos[$aid] = [
                    'fecha_aviso'  => $registro['fecha_aviso'],
                    'aviso_valido' => (int)$registro['aviso_valido'],
                ];
            }
        }

        foreach ($alumnos as $alumno) {
            $estado = $registros[(int)$alumno['id']] ?? 'asiste';
            $resumen[$estado]++;
        }
    }
}
       // Solo carga una clase cuando el pase de lista está en modo "clase".
if ($tipo === 'clase' && $claseId > 0) {
            $sqlDetalle = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado, c.grupo_id,
                                  g.nombre AS grupo_nombre, g.tipo AS grupo_tipo, s.nombre AS sala_nombre
                           FROM clase c
                           INNER JOIN grupo g ON g.id = c.grupo_id
                           LEFT JOIN sala s ON s.id = c.sala_id
                           WHERE c.id = ?";

            if ($this->esAdmin()) {
                $stmt = $conexion->prepare($sqlDetalle);
                $stmt->bind_param('i', $claseId);
            } else {
                $stmt = $conexion->prepare($sqlDetalle . " AND g.profesor_id = ?");
                $profesorId = $this->profesorId();
                $stmt->bind_param('is', $claseId, $profesorId);
            }
            $stmt->execute();
            $detalleClase = $stmt->get_result()->fetch_assoc();

            if ($detalleClase) {
                $stmt = $conexion->prepare(
                    "SELECT DISTINCT a.id, a.nombre, a.apellidos,
                            (SELECT COUNT(*) FROM token t
                             WHERE t.alumno_id = a.id
                               AND t.usado = FALSE
                               AND (t.fecha_caducidad IS NULL OR t.fecha_caducidad >= CURDATE())
                            ) AS tokens_disponibles
                     FROM alumno_grupo ag
                     INNER JOIN alumno a ON a.id = ag.alumno_id
                     WHERE ag.grupo_id = ? AND ag.activo = TRUE
                     ORDER BY a.apellidos, a.nombre"
                );
                $stmt->bind_param('i', $detalleClase['grupo_id']);
                $stmt->execute();
                $alumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                $stmt = $conexion->prepare(
                    "SELECT alumno_id, estado, fecha_aviso, aviso_valido
                     FROM asistencia WHERE clase_id = ?"
                );
                $stmt->bind_param('i', $claseId);
                $stmt->execute();
                foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $registro) {
                    $aid = (int)$registro['alumno_id'];
                    $registros[$aid] = $registro['estado'];
                    if ($registro['estado'] === 'avisado') {
                        $avisos[$aid] = [
                            'fecha_aviso'  => $registro['fecha_aviso'],
                            'aviso_valido' => (int)$registro['aviso_valido'],
                        ];
                    }
                }

                foreach ($alumnos as $alumno) {
                    // Por defecto todos los alumnos asisten salvo aviso explicito.
                    $estado = $registros[(int)$alumno['id']] ?? 'asiste';
                    $resumen[$estado]++;
                }
            }
        }


// Unifica el detalle para que la vista pueda trabajar con clases y eventos.
$detalleAsistencia = ($tipo === 'evento')
    ? $detalleEvento
    : $detalleClase;

        $vista     = 'asistencia';
        $csrfToken = $this->csrfToken();
        require ROOT . '/app/views/profesor/asistencia.php';
    }

    public function calendario(): void {
        $this->requireProfesorAuth();
        global $conexion;

        // Mes a mostrar (YYYY-MM); por defecto, el actual
        $monthInput = $_GET['month'] ?? date('Y-m');
        $monthDate = DateTime::createFromFormat('Y-m-d', $monthInput . '-01');
        if (!$monthDate || $monthDate->format('Y-m') !== $monthInput) {
            $monthDate = new DateTime('first day of this month');
        }

        $monthStart = clone $monthDate;
        $monthEnd   = (clone $monthDate)->modify('last day of this month');
        $monthStartSql = $monthStart->format('Y-m-d');
        $monthEndSql   = $monthEnd->format('Y-m-d');

        // TODAS las clases asignadas al profesor (no se limita al mes en curso).
        $sql = "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado, c.cupo_maximo,
                       g.id AS grupo_id, g.nombre AS grupo_nombre, g.tipo AS grupo_tipo,
                       s.nombre AS sala_nombre,
                       (SELECT COUNT(*) FROM alumno_grupo ag
                        WHERE ag.grupo_id = g.id AND ag.activo = TRUE) AS total_alumnos,
                       (SELECT COUNT(*) FROM asistencia a
                        WHERE a.clase_id = c.id AND a.estado = 'asiste') AS total_confirmados,
                       (SELECT COUNT(*) FROM asistencia a
                        WHERE a.clase_id = c.id AND a.estado = 'avisado') AS total_avisos,
                       (SELECT COUNT(*) FROM asistencia a
                        WHERE a.clase_id = c.id AND a.estado = 'ausente') AS total_ausentes
                FROM clase c
                INNER JOIN grupo g ON g.id = c.grupo_id
                LEFT JOIN sala s ON s.id = c.sala_id
                WHERE c.estado <> 'cancelada'
                  AND c.id IN (SELECT MIN(c2.id)
                               FROM clase c2
                               GROUP BY c2.grupo_id, c2.fecha, c2.hora_inicio)";

        if ($this->esAdmin()) {
            $sql .= " ORDER BY c.fecha, c.hora_inicio";
            $stmt = $conexion->prepare($sql);
        } else {
            $sql .= " AND g.profesor_id = ? ORDER BY c.fecha, c.hora_inicio";
            $stmt = $conexion->prepare($sql);
            $profesorId = $this->profesorId();
            $stmt->bind_param('s', $profesorId);
        }
        $stmt->execute();
        $calendarClasses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Agrupar eventos por fecha
        $eventsByDate = [];
        foreach ($calendarClasses as $classItem) {
            $eventsByDate[$classItem['fecha']][] = $classItem;
        }

        // Rango visible del calendario (semanas completas Lun-Dom)
        $calendarStart = clone $monthStart;
        $calendarStart->modify('-' . ((int)$calendarStart->format('N') - 1) . ' days');
        $calendarEnd = clone $monthEnd;
        $calendarEnd->modify('+' . (7 - (int)$calendarEnd->format('N')) . ' days');

        // Fecha seleccionada (validar formato y rango)
        $selectedDate = $_GET['fecha'] ?? '';
        $isValidSelectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) === 1;
        if ($isValidSelectedDate) {
            $selectedDateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
            $isValidSelectedDate = $selectedDateObj && $selectedDateObj->format('Y-m-d') === $selectedDate;
        }
        if (!$isValidSelectedDate || $selectedDate < $calendarStart->format('Y-m-d') || $selectedDate > $calendarEnd->format('Y-m-d')) {
            // Si no llega ?fecha=, preferir HOY cuando esta dentro del rango visible
            // del calendario (asi el panel de la derecha siempre muestra el dia actual
            // al entrar a /profesor/calendario sin parametros).
            $hoyStr = date('Y-m-d');
            if ($hoyStr >= $calendarStart->format('Y-m-d') && $hoyStr <= $calendarEnd->format('Y-m-d')) {
                $selectedDate = $hoyStr;
            } else {
                $selectedDate = array_key_first($eventsByDate) ?: $monthStart->format('Y-m-d');
            }
        }
        $selectedEvents = $eventsByDate[$selectedDate] ?? [];

        // Construccion de las semanas
        $calendarWeeks = [];
        $cursor = clone $calendarStart;
        while ($cursor <= $calendarEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->format('Y-m-d');
                $week[] = [
                    'date' => $dateKey,
                    'day' => (int)$cursor->format('j'),
                    'is_current_month' => $cursor->format('Y-m') === $monthStart->format('Y-m'),
                    'has_events' => isset($eventsByDate[$dateKey]),
                    'is_selected' => $dateKey === $selectedDate,
                    'is_today' => $dateKey === date('Y-m-d'),
                ];
                $cursor->modify('+1 day');
            }
            $calendarWeeks[] = $week;
        }

        $previousMonth = (clone $monthStart)->modify('-1 month')->format('Y-m');
        $nextMonth     = (clone $monthStart)->modify('+1 month')->format('Y-m');
        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');

        $vista = 'calendario';
        require ROOT . '/app/views/profesor/calendario.php';
    }

    private function exportarAlumnosCsv(): void {
        global $conexion;

        $sql = "SELECT a.id, a.nombre, a.apellidos, a.email, a.telefono, a.estado,
                       GROUP_CONCAT(DISTINCT g.nombre ORDER BY g.nombre SEPARATOR ' | ') AS grupos
                FROM alumno a
                LEFT JOIN alumno_grupo ag ON ag.alumno_id = a.id AND ag.activo = TRUE
                LEFT JOIN grupo g ON g.id = ag.grupo_id";

        if (!$this->esAdmin()) {
            $sql .= " WHERE (g.profesor_id = ? OR ag.grupo_id IS NULL)
                      GROUP BY a.id
                      ORDER BY a.apellidos, a.nombre";
            $stmt = $conexion->prepare($sql);
            $profesorId = $this->profesorId();
            $stmt->bind_param('s', $profesorId);
            $stmt->execute();
            $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $sql .= " GROUP BY a.id ORDER BY a.apellidos, a.nombre";
            $resultado = $conexion->query($sql);
            $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        }

        $nombreArchivo = 'alumnos_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM para que Excel reconozca UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Nombre', 'Apellidos', 'Email', 'Telefono', 'Estado', 'Grupos'], ';');
        foreach ($filas as $fila) {
            fputcsv($out, [
                (int)$fila['id'],
                $fila['nombre'],
                $fila['apellidos'],
                $fila['email'] ?? '',
                $fila['telefono'] ?? '',
                $fila['estado'],
                $fila['grupos'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }
}
