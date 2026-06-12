<?php

require_once ROOT . '/app/helpers/Csrf.php';

class AlumnoController {

    private function requireAuth() {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol === 'admin') {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        if ($rol === 'profesor') {
            header('Location: ' . BASE_URL . '/profesor');
            exit;
        }

        if ($rol !== 'alumno') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ((int)($_SESSION['alumno_id'] ?? 0) === 0) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $estadoAlumno = $_SESSION['alumno_estado'] ?? '';
        if ($estadoAlumno === 'posible') {
            header('Location: ' . BASE_URL . '/invitado');
            exit;
        }

        if ($estadoAlumno !== 'matriculado') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function getAlumnoId() {
        return (int)($_SESSION['alumno_id'] ?? 0);
    }

    private function getTokensDisponibles($alumno_id) {
        global $conexion;

        $q = $conexion->prepare(
            "SELECT COUNT(*) AS disponibles FROM token
             WHERE alumno_id = ? AND usado = FALSE
               AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();

        return (int)$q->get_result()->fetch_assoc()['disponibles'];
    }

    private function getProximasClases($alumno_id, $limit = 8) {
        global $conexion;

        $q = $conexion->prepare(
            "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin,
                    g.nombre AS grupo_nombre, g.tipo AS grupo_tipo,
                    a.id AS asistencia_id
             FROM clase c
             INNER JOIN alumno_grupo ag ON ag.grupo_id = c.grupo_id
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN asistencia a ON a.clase_id = c.id AND a.alumno_id = ?
             WHERE ag.alumno_id = ? AND ag.activo = TRUE
               AND c.fecha >= CURDATE() AND c.estado = 'programada'
             ORDER BY c.fecha, c.hora_inicio
             LIMIT ?"
        );
        $q->bind_param('iii', $alumno_id, $alumno_id, $limit);
        $q->execute();

        return $q->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getRedirectAlumno($fallback = '/alumno') {
        $redirect = $_POST['redirect_to'] ?? $fallback;
        if (!is_string($redirect) || strpos($redirect, '/alumno') !== 0) {
            $redirect = $fallback;
        }

        return BASE_URL . $redirect;
    }

    private function buildRedirectWithMessage($baseRedirect, $mensaje) {
        $separator = strpos($baseRedirect, '?') === false ? '?' : '&';
        return $baseRedirect . $separator . 'mensaje=' . urlencode($mensaje);
    }

    private function verificarCsrf($redirect) {
        if (!Csrf::validatePost()) {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'error_datos'));
            exit;
        }
    }

    public function showPanel() {
        $this->requireAuth();
        $alumno_id = $this->getAlumnoId();

        $tokens_disponibles = $this->getTokensDisponibles($alumno_id);
        $tokens_maximo = 4;

        $proximas_clases = $this->getProximasClases($alumno_id);
        $proxima_clase   = $proximas_clases[0] ?? null;

        $csrfToken = Csrf::token();
        $mensaje = $_GET['mensaje'] ?? '';
        require ROOT . '/app/views/alumno/index.php';
    }

    public function showTokens() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();

        $tokens_disponibles = $this->getTokensDisponibles($alumno_id);
        $tokens_maximo = 4;

        $q = $conexion->prepare(
            "SELECT t.id, t.usado, t.fecha_generacion, t.fecha_caducidad,
                    c.fecha AS clase_fecha, c.hora_inicio AS clase_hora,
                    g.nombre AS grupo_nombre, g.tipo AS grupo_tipo,
                    r.id AS recuperacion_id,
                    c2.fecha AS recup_fecha, c2.hora_inicio AS recup_hora,
                    g2.nombre AS recup_grupo
             FROM token t
             INNER JOIN asistencia a ON a.id = t.asistencia_origen_id
             INNER JOIN clase c ON c.id = a.clase_id
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN recuperacion r ON r.token_id = t.id AND r.estado != 'cancelada'
             LEFT JOIN clase c2 ON c2.id = r.clase_recuperacion_id
             LEFT JOIN grupo g2 ON g2.id = c2.grupo_id
             WHERE t.alumno_id = ?
             ORDER BY t.id DESC
             LIMIT 20"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $historial = $q->get_result()->fetch_all(MYSQLI_ASSOC);

        require ROOT . '/app/views/alumno/tokens.php';
    }

    public function showRecuperar() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();

        $tokens_disponibles = $this->getTokensDisponibles($alumno_id);
        $tokens_maximo = 4;

        $q = $conexion->prepare(
            "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.cupo_maximo,
                    g.tipo AS grupo_tipo,
                    (SELECT COUNT(*) FROM alumno_grupo ag2
                     WHERE ag2.grupo_id = c.grupo_id AND ag2.activo = TRUE) AS inscritos,
                    (SELECT COUNT(*) FROM recuperacion r2
                     WHERE r2.clase_recuperacion_id = c.id AND r2.estado != 'cancelada') AS recuperaciones,
                    r.id AS mi_recuperacion_id
             FROM clase c
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN recuperacion r ON r.clase_recuperacion_id = c.id AND r.alumno_id = ?
             WHERE g.tipo = 'improvisacion'
               AND c.fecha >= CURDATE() AND c.estado = 'programada'
               AND c.grupo_id NOT IN (
                   SELECT grupo_id FROM alumno_grupo WHERE alumno_id = ? AND activo = TRUE
               )
             ORDER BY c.fecha, c.hora_inicio
             LIMIT 8"
        );
        $q->bind_param('ii', $alumno_id, $alumno_id);
        $q->execute();
        $clases_recuperables = $q->get_result()->fetch_all(MYSQLI_ASSOC);

        $mensaje = $_GET['mensaje'] ?? '';
        $csrfToken = Csrf::token();
        require ROOT . '/app/views/alumno/recuperar.php';
    }

    public function showCalendario() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();

        $monthInput = $_GET['month'] ?? date('Y-m');
        $monthDate = DateTime::createFromFormat('Y-m-d', $monthInput . '-01');
        if (!$monthDate || $monthDate->format('Y-m') !== $monthInput) {
            $monthDate = new DateTime('first day of this month');
        }

        $monthStart = clone $monthDate;
        $monthEnd = (clone $monthDate)->modify('last day of this month');

        $q = $conexion->prepare(
            "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin,
                    g.nombre AS grupo_nombre, g.tipo AS grupo_tipo,
                    a.id AS asistencia_id
             FROM clase c
             INNER JOIN alumno_grupo ag ON ag.grupo_id = c.grupo_id
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN asistencia a ON a.clase_id = c.id AND a.alumno_id = ?
             WHERE ag.alumno_id = ? AND ag.activo = TRUE
               AND c.estado = 'programada'
               AND c.fecha BETWEEN ? AND ?
             ORDER BY c.fecha, c.hora_inicio"
        );
        $monthStartSql = $monthStart->format('Y-m-d');
        $monthEndSql = $monthEnd->format('Y-m-d');
        $q->bind_param('iiss', $alumno_id, $alumno_id, $monthStartSql, $monthEndSql);
        $q->execute();
        $calendarClasses = $q->get_result()->fetch_all(MYSQLI_ASSOC);

        $eventsByDate = [];
        foreach ($calendarClasses as $classItem) {
            $eventsByDate[$classItem['fecha']][] = $classItem;
        }

        $calendarStart = clone $monthStart;
        $calendarStart->modify('-' . ((int)$calendarStart->format('N') - 1) . ' days');
        $calendarEnd = clone $monthEnd;
        $calendarEnd->modify('+' . (7 - (int)$calendarEnd->format('N')) . ' days');

        $selectedDate = $_GET['fecha'] ?? '';
        $isValidSelectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) === 1;
        if ($isValidSelectedDate) {
            $selectedDateObj = DateTime::createFromFormat('Y-m-d', $selectedDate);
            $isValidSelectedDate = $selectedDateObj && $selectedDateObj->format('Y-m-d') === $selectedDate;
        }

        if (!$isValidSelectedDate || $selectedDate < $calendarStart->format('Y-m-d') || $selectedDate > $calendarEnd->format('Y-m-d')) {
            $selectedDate = array_key_first($eventsByDate) ?: $monthStart->format('Y-m-d');
        }

        $selectedEvents = $eventsByDate[$selectedDate] ?? [];

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
        $nextMonth = (clone $monthStart)->modify('+1 month')->format('Y-m');
        $mensaje = $_GET['mensaje'] ?? '';
        $csrfToken = Csrf::token();

        require ROOT . '/app/views/alumno/calendario.php';
    }

    public function avisarAusencia() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();
        $clase_id  = (int)($_POST['clase_id'] ?? 0);
        $fallback  = '/alumno';
        $redirect  = $this->getRedirectAlumno($fallback);
        $this->verificarCsrf($redirect);

        if ($clase_id === 0) { header('Location: ' . $redirect); exit; }

        $q = $conexion->prepare(
            "SELECT c.id, c.fecha, c.hora_inicio FROM clase c
             INNER JOIN alumno_grupo ag ON ag.grupo_id = c.grupo_id
             WHERE c.id = ? AND ag.alumno_id = ? AND ag.activo = TRUE AND c.estado = 'programada' LIMIT 1"
        );
        $q->bind_param('ii', $clase_id, $alumno_id);
        $q->execute();
        $clase = $q->get_result()->fetch_assoc();
        if (!$clase) { header('Location: ' . $redirect); exit; }

        $q = $conexion->prepare("SELECT id FROM asistencia WHERE alumno_id = ? AND clase_id = ? LIMIT 1");
        $q->bind_param('ii', $alumno_id, $clase_id);
        $q->execute();
        if ($q->get_result()->fetch_assoc()) {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'ya_avisado')); exit;
        }

        $horas_hasta  = (strtotime($clase['fecha'] . ' ' . $clase['hora_inicio']) - time()) / 3600;
        $genera_token = ($horas_hasta >= 24);

        $q = $conexion->prepare(
            "INSERT INTO asistencia (alumno_id, clase_id, estado, fecha_aviso, aviso_valido)
             VALUES (?, ?, 'avisado', NOW(), ?)"
        );
        $aviso_valido = $genera_token ? 1 : 0;
        $q->bind_param('iii', $alumno_id, $clase_id, $aviso_valido);
        $q->execute();
        $asistencia_id = $conexion->insert_id;

        if ($genera_token) {
            $q = $conexion->prepare(
                "SELECT COUNT(*) AS disponibles FROM token
                 WHERE alumno_id = ? AND usado = FALSE
                   AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())"
            );
            $q->bind_param('i', $alumno_id);
            $q->execute();
            $disponibles = (int)$q->get_result()->fetch_assoc()['disponibles'];

            if ($disponibles < 4) {
                $caducidad = '2026-06-30';
                $q = $conexion->prepare(
                    "INSERT INTO token (alumno_id, asistencia_origen_id, fecha_caducidad, usado)
                     VALUES (?, ?, ?, FALSE)"
                );
                $q->bind_param('iis', $alumno_id, $asistencia_id, $caducidad);
                $q->execute();
                header('Location: ' . $this->buildRedirectWithMessage($redirect, 'avisado_token'));
            } else {
                header('Location: ' . $this->buildRedirectWithMessage($redirect, 'avisado_sin_token_maximo'));
            }
        } else {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'avisado_sin_token'));
        }
        exit;
    }

    public function anularAusencia() {
        $this->requireAuth();
        global $conexion;
        $alumno_id     = $this->getAlumnoId();
        $asistencia_id = (int)($_POST['asistencia_id'] ?? 0);
        $fallback      = '/alumno';
        $redirect      = $this->getRedirectAlumno($fallback);
        $this->verificarCsrf($redirect);

        if ($asistencia_id === 0) { header('Location: ' . $redirect); exit; }

        $q = $conexion->prepare(
            "SELECT a.id, a.aviso_valido, c.fecha, c.hora_inicio
             FROM asistencia a INNER JOIN clase c ON c.id = a.clase_id
             WHERE a.id = ? AND a.alumno_id = ? AND a.estado = 'avisado' LIMIT 1"
        );
        $q->bind_param('ii', $asistencia_id, $alumno_id);
        $q->execute();
        $asistencia = $q->get_result()->fetch_assoc();

        if (!$asistencia || strtotime($asistencia['fecha'] . ' ' . $asistencia['hora_inicio']) < time()) {
            header('Location: ' . $redirect); exit;
        }

        $token_borrado = false;
        if ($asistencia['aviso_valido']) {
            $q = $conexion->prepare(
                "SELECT id, usado FROM token WHERE asistencia_origen_id = ? AND alumno_id = ? LIMIT 1"
            );
            $q->bind_param('ii', $asistencia_id, $alumno_id);
            $q->execute();
            $token = $q->get_result()->fetch_assoc();

            if ($token) {
                if ($token['usado']) {
                    $q = $conexion->prepare(
                        "SELECT r.id FROM recuperacion r
                         INNER JOIN clase c ON c.id = r.clase_recuperacion_id
                         WHERE r.token_id = ? AND r.alumno_id = ? AND r.estado != 'cancelada'
                           AND c.fecha >= CURDATE() LIMIT 1"
                    );
                    $q->bind_param('ii', $token['id'], $alumno_id);
                    $q->execute();
                    $recup = $q->get_result()->fetch_assoc();
                    if ($recup) {
                        $d = $conexion->prepare("DELETE FROM recuperacion WHERE id = ?");
                        $d->bind_param('i', $recup['id']);
                        $d->execute();
                    }
                    $u = $conexion->prepare("UPDATE token SET usado = FALSE WHERE id = ?");
                    $u->bind_param('i', $token['id']);
                    $u->execute();
                }
                $d = $conexion->prepare("DELETE FROM token WHERE id = ?");
                $d->bind_param('i', $token['id']);
                $d->execute();
                $token_borrado = true;
            }
        }

        $d = $conexion->prepare("DELETE FROM asistencia WHERE id = ?");
        $d->bind_param('i', $asistencia_id);
        $d->execute();

        $mensaje = $token_borrado ? 'anulado_con_token' : 'anulado';
        header('Location: ' . $this->buildRedirectWithMessage($redirect, $mensaje));
        exit;
    }

    public function anularRecuperacion() {
        $this->requireAuth();
        global $conexion;
        $alumno_id       = $_SESSION['alumno_id'] ?? 0;
        $recuperacion_id = (int)($_POST['recuperacion_id'] ?? 0);
        $this->verificarCsrf(BASE_URL . '/alumno/recuperar');

        if ($recuperacion_id === 0) { header('Location: ' . BASE_URL . '/alumno/recuperar'); exit; }

        $q = $conexion->prepare(
            "SELECT r.id, r.token_id, c.fecha, c.hora_inicio
             FROM recuperacion r INNER JOIN clase c ON c.id = r.clase_recuperacion_id
             WHERE r.id = ? AND r.alumno_id = ? AND r.estado != 'realizada' LIMIT 1"
        );
        $q->bind_param('ii', $recuperacion_id, $alumno_id);
        $q->execute();
        $recuperacion = $q->get_result()->fetch_assoc();

        if (!$recuperacion || strtotime($recuperacion['fecha'] . ' ' . $recuperacion['hora_inicio']) < time()) {
            header('Location: ' . BASE_URL . '/alumno/recuperar'); exit;
        }

        $d = $conexion->prepare("DELETE FROM recuperacion WHERE id = ?");
        $d->bind_param('i', $recuperacion_id);
        $d->execute();

        // Comprobar si devolver el token superaría el límite de 4
        $q = $conexion->prepare(
            "SELECT COUNT(*) AS disponibles FROM token
             WHERE alumno_id = ? AND usado = FALSE
               AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $disponibles = (int)$q->get_result()->fetch_assoc()['disponibles'];

        if ($disponibles >= 4) {
            // Ya tiene 4 tokens: borrar el token en vez de devolverlo
            $d = $conexion->prepare("DELETE FROM token WHERE id = ?");
            $d->bind_param('i', $recuperacion['token_id']);
            $d->execute();
            header('Location: ' . BASE_URL . '/alumno/recuperar?mensaje=recuperacion_anulada_sin_token');
        } else {
            // Hay hueco: devolver el token
            $u = $conexion->prepare("UPDATE token SET usado = FALSE WHERE id = ?");
            $u->bind_param('i', $recuperacion['token_id']);
            $u->execute();
            header('Location: ' . BASE_URL . '/alumno/recuperar?mensaje=recuperacion_anulada');
        }
        exit;
    }

    public function recuperarClase() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $_SESSION['alumno_id'] ?? 0;
        $clase_id  = (int)($_POST['clase_id'] ?? 0);
        $this->verificarCsrf(BASE_URL . '/alumno/recuperar');

        if ($clase_id === 0) { header('Location: ' . BASE_URL . '/alumno/recuperar'); exit; }

        $q = $conexion->prepare(
            "SELECT c.id, c.cupo_maximo, c.grupo_id FROM clase c
             INNER JOIN grupo g ON g.id = c.grupo_id
             WHERE c.id = ? AND g.tipo = 'improvisacion'
               AND c.fecha >= CURDATE() AND c.estado = 'programada' LIMIT 1"
        );
        $q->bind_param('i', $clase_id);
        $q->execute();
        $clase = $q->get_result()->fetch_assoc();
        if (!$clase) { header('Location: ' . BASE_URL . '/alumno/recuperar'); exit; }

        $q = $conexion->prepare("SELECT id FROM alumno_grupo WHERE alumno_id = ? AND grupo_id = ? AND activo = TRUE LIMIT 1");
        $q->bind_param('ii', $alumno_id, $clase['grupo_id']);
        $q->execute();
        if ($q->get_result()->num_rows > 0) { header('Location: ' . BASE_URL . '/alumno/recuperar'); exit; }

        $q = $conexion->prepare("SELECT id FROM recuperacion WHERE alumno_id = ? AND clase_recuperacion_id = ? AND estado != 'cancelada' LIMIT 1");
        $q->bind_param('ii', $alumno_id, $clase_id);
        $q->execute();
        if ($q->get_result()->num_rows > 0) { header('Location: ' . BASE_URL . '/alumno/recuperar'); exit; }

        $q = $conexion->prepare(
            "SELECT t.id AS token_id, a.clase_id AS clase_origen_id
             FROM token t INNER JOIN asistencia a ON a.id = t.asistencia_origen_id
             WHERE t.alumno_id = ? AND t.usado = FALSE
               AND (t.fecha_caducidad IS NULL OR t.fecha_caducidad >= CURDATE())
             ORDER BY t.id ASC LIMIT 1"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $token_data = $q->get_result()->fetch_assoc();
        if (!$token_data) { header('Location: ' . BASE_URL . '/alumno/recuperar?mensaje=sin_tokens'); exit; }

        $q = $conexion->prepare(
            "SELECT
                (SELECT COUNT(*) FROM alumno_grupo WHERE grupo_id = ? AND activo = TRUE) AS inscritos,
                (SELECT COUNT(*) FROM recuperacion WHERE clase_recuperacion_id = ? AND estado != 'cancelada') AS recuperaciones"
        );
        $q->bind_param('ii', $clase['grupo_id'], $clase_id);
        $q->execute();
        $o = $q->get_result()->fetch_assoc();
        if (((int)$o['inscritos'] + (int)$o['recuperaciones']) >= (int)$clase['cupo_maximo']) {
            header('Location: ' . BASE_URL . '/alumno/recuperar?mensaje=sin_plazas'); exit;
        }

        $q = $conexion->prepare(
            "INSERT INTO recuperacion (alumno_id, token_id, clase_origen_id, clase_recuperacion_id, estado)
             VALUES (?, ?, ?, ?, 'pendiente')"
        );
        $q->bind_param('iiii', $alumno_id, $token_data['token_id'], $token_data['clase_origen_id'], $clase_id);
        $q->execute();

        $u = $conexion->prepare("UPDATE token SET usado = TRUE WHERE id = ?");
        $u->bind_param('i', $token_data['token_id']);
        $u->execute();

        header('Location: ' . BASE_URL . '/alumno/recuperar?mensaje=recuperado');
        exit;
    }
}
