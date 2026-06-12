<?php

require_once ROOT . '/app/helpers/Csrf.php';

class InvitadoController {

    private function requireAuth() {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        if (!isset($_SESSION['alumno_estado']) || $_SESSION['alumno_estado'] !== 'posible') {
            header('Location: ' . BASE_URL . '/alumno');
            exit;
        }
    }

    private function getAlumnoId() {
        return (int)($_SESSION['alumno_id'] ?? 0);
    }

    private function getRedirectInvitado($fallback = '/invitado') {
        $redirect = $_POST['redirect_to'] ?? $fallback;
        if (!is_string($redirect) || strpos($redirect, '/invitado') !== 0) {
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
        global $conexion;
        $alumno_id = $this->getAlumnoId();

        // Token de prueba disponible
        $q = $conexion->prepare(
            "SELECT COUNT(*) AS disponibles FROM token
             WHERE alumno_id = ? AND usado = FALSE
               AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $token_disponible = (int)$q->get_result()->fetch_assoc()['disponibles'] > 0;

        // ¿Ya tiene una clase de prueba reservada?
        $q = $conexion->prepare(
            "SELECT r.id AS recuperacion_id, c.fecha, c.hora_inicio, c.hora_fin,
                    g.nombre AS grupo_nombre,
                    CONCAT(p.nombre, ' ', p.apellidos) AS profesor,
                    s.nombre AS sala
             FROM recuperacion r
             INNER JOIN clase c ON c.id = r.clase_recuperacion_id
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN profesor p ON p.usuario_id = g.profesor_id
             LEFT JOIN sala s ON s.id = c.sala_id
             WHERE r.alumno_id = ? AND r.estado NOT IN ('cancelada','realizada')
               AND c.fecha >= CURDATE()
             LIMIT 1"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $clase_reservada = $q->get_result()->fetch_assoc();

        // Clases de impro disponibles (solo si tiene token y no ha reservado)
        $clases_disponibles = [];
        if ($token_disponible && !$clase_reservada) {
            $q = $conexion->prepare(
                "SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.cupo_maximo,
                        g.nombre AS grupo_nombre,
                        CONCAT(p.nombre, ' ', p.apellidos) AS profesor,
                        s.nombre AS sala,
                        (SELECT COUNT(*) FROM alumno_grupo ag2
                         WHERE ag2.grupo_id = c.grupo_id AND ag2.activo = TRUE) AS inscritos,
                        (SELECT COUNT(*) FROM recuperacion r2
                         WHERE r2.clase_recuperacion_id = c.id AND r2.estado NOT IN ('cancelada','realizada')) AS recuperaciones
                 FROM clase c
                 INNER JOIN grupo g ON g.id = c.grupo_id
                 LEFT JOIN profesor p ON p.usuario_id = g.profesor_id
                 LEFT JOIN sala s ON s.id = c.sala_id
                 WHERE g.tipo = 'improvisacion'
                   AND c.fecha > CURDATE() AND c.estado = 'programada'
                 ORDER BY c.fecha, c.hora_inicio
                 LIMIT 8"
            );
            $q->execute();
            $clases_disponibles = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $mensaje = $_GET['mensaje'] ?? '';
        $csrfToken = Csrf::token();
        require ROOT . '/app/views/invitado/index.php';
    }

    public function showCalendario() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();

        $q = $conexion->prepare(
            "SELECT COUNT(*) AS disponibles FROM token
             WHERE alumno_id = ? AND usado = FALSE
               AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $token_disponible = (int)$q->get_result()->fetch_assoc()['disponibles'] > 0;

        $q = $conexion->prepare(
            "SELECT r.id AS recuperacion_id, c.id AS clase_id, c.fecha, c.hora_inicio, c.hora_fin,
                    g.nombre AS grupo_nombre,
                    CONCAT(p.nombre, ' ', p.apellidos) AS profesor,
                    s.nombre AS sala
             FROM recuperacion r
             INNER JOIN clase c ON c.id = r.clase_recuperacion_id
             INNER JOIN grupo g ON g.id = c.grupo_id
             LEFT JOIN profesor p ON p.usuario_id = g.profesor_id
             LEFT JOIN sala s ON s.id = c.sala_id
             WHERE r.alumno_id = ? AND r.estado NOT IN ('cancelada','realizada')
               AND c.fecha >= CURDATE()
             LIMIT 1"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $clase_reservada = $q->get_result()->fetch_assoc();

        $monthInput = $_GET['month'] ?? ($clase_reservada ? substr($clase_reservada['fecha'], 0, 7) : date('Y-m'));
        $monthDate = DateTime::createFromFormat('Y-m-d', $monthInput . '-01');
        if (!$monthDate || $monthDate->format('Y-m') !== $monthInput) {
            $monthDate = new DateTime('first day of this month');
        }

        $monthStart = clone $monthDate;
        $monthEnd = (clone $monthDate)->modify('last day of this month');
        $monthStartSql = $monthStart->format('Y-m-d');
        $monthEndSql = $monthEnd->format('Y-m-d');

        $calendarClasses = [];
        if ($clase_reservada && $clase_reservada['fecha'] >= $monthStartSql && $clase_reservada['fecha'] <= $monthEndSql) {
            $calendarClasses[] = array_merge($clase_reservada, [
                'cupo_maximo' => null,
                'inscritos' => 0,
                'recuperaciones' => 0,
                'esta_reservada' => true,
            ]);
        } elseif ($token_disponible) {
            $q = $conexion->prepare(
                "SELECT c.id AS clase_id, c.fecha, c.hora_inicio, c.hora_fin, c.cupo_maximo,
                        g.nombre AS grupo_nombre,
                        CONCAT(p.nombre, ' ', p.apellidos) AS profesor,
                        s.nombre AS sala,
                        (SELECT COUNT(*) FROM alumno_grupo ag2
                         WHERE ag2.grupo_id = c.grupo_id AND ag2.activo = TRUE) AS inscritos,
                        (SELECT COUNT(*) FROM recuperacion r2
                         WHERE r2.clase_recuperacion_id = c.id AND r2.estado NOT IN ('cancelada','realizada')) AS recuperaciones,
                        FALSE AS esta_reservada
                 FROM clase c
                 INNER JOIN grupo g ON g.id = c.grupo_id
                 LEFT JOIN profesor p ON p.usuario_id = g.profesor_id
                 LEFT JOIN sala s ON s.id = c.sala_id
                 WHERE g.tipo = 'improvisacion'
                   AND c.fecha BETWEEN ? AND ? AND c.fecha > CURDATE()
                   AND c.estado = 'programada'
                 ORDER BY c.fecha, c.hora_inicio"
            );
            $q->bind_param('ss', $monthStartSql, $monthEndSql);
            $q->execute();
            $calendarClasses = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        }

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

        require ROOT . '/app/views/invitado/calendario.php';
    }

    public function reservarPrueba() {
        $this->requireAuth();
        global $conexion;
        $alumno_id = $this->getAlumnoId();
        $clase_id  = (int)($_POST['clase_id'] ?? 0);
        $redirect  = $this->getRedirectInvitado();
        $this->verificarCsrf($redirect);

        if ($clase_id === 0) { header('Location: ' . $redirect); exit; }

        // Verificar token disponible
        $q = $conexion->prepare(
            "SELECT t.id AS token_id, t.asistencia_origen_id
             FROM token t
             WHERE t.alumno_id = ? AND t.usado = FALSE
               AND (t.fecha_caducidad IS NULL OR t.fecha_caducidad >= CURDATE())
             LIMIT 1"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        $token = $q->get_result()->fetch_assoc();

        if (!$token) {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'sin_token'));
            exit;
        }

        // Verificar que no tiene ya una reserva activa
        $q = $conexion->prepare(
            "SELECT id FROM recuperacion
             WHERE alumno_id = ? AND estado NOT IN ('cancelada','realizada')
             LIMIT 1"
        );
        $q->bind_param('i', $alumno_id);
        $q->execute();
        if ($q->get_result()->fetch_assoc()) {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'ya_reservado'));
            exit;
        }

        // Verificar clase: existe, es impro, futura
        $q = $conexion->prepare(
            "SELECT c.id, c.cupo_maximo, c.grupo_id FROM clase c
             INNER JOIN grupo g ON g.id = c.grupo_id
             WHERE c.id = ? AND g.tipo = 'improvisacion'
               AND c.fecha > CURDATE() AND c.estado = 'programada'
             LIMIT 1"
        );
        $q->bind_param('i', $clase_id);
        $q->execute();
        $clase = $q->get_result()->fetch_assoc();

        if (!$clase) { header('Location: ' . $redirect); exit; }

        // Verificar plazas
        $q = $conexion->prepare(
            "SELECT
                (SELECT COUNT(*) FROM alumno_grupo WHERE grupo_id = ? AND activo = TRUE) AS inscritos,
                (SELECT COUNT(*) FROM recuperacion WHERE clase_recuperacion_id = ? AND estado NOT IN ('cancelada','realizada')) AS recuperaciones"
        );
        $q->bind_param('ii', $clase['grupo_id'], $clase_id);
        $q->execute();
        $o = $q->get_result()->fetch_assoc();

        if (((int)$o['inscritos'] + (int)$o['recuperaciones']) >= (int)$clase['cupo_maximo']) {
            header('Location: ' . $this->buildRedirectWithMessage($redirect, 'sin_plazas'));
            exit;
        }

        // Reservar: usamos asistencia_origen_id del token como clase_origen_id
        $q = $conexion->prepare(
            "SELECT clase_id FROM asistencia WHERE id = ? LIMIT 1"
        );
        $q->bind_param('i', $token['asistencia_origen_id']);
        $q->execute();
        $asist = $q->get_result()->fetch_assoc();
        $clase_origen_id = $asist ? (int)$asist['clase_id'] : $clase_id;

        $q = $conexion->prepare(
            "INSERT INTO recuperacion (alumno_id, token_id, clase_origen_id, clase_recuperacion_id, estado)
             VALUES (?, ?, ?, ?, 'pendiente')"
        );
        $q->bind_param('iiii', $alumno_id, $token['token_id'], $clase_origen_id, $clase_id);
        $q->execute();

        // Marcar token como usado
        $u = $conexion->prepare("UPDATE token SET usado = TRUE WHERE id = ?");
        $u->bind_param('i', $token['token_id']);
        $u->execute();

        header('Location: ' . $this->buildRedirectWithMessage($redirect, 'reservado'));
        exit;
    }

    public function cancelarPrueba() {
        $this->requireAuth();
        global $conexion;
        $alumno_id       = $this->getAlumnoId();
        $recuperacion_id = (int)($_POST['recuperacion_id'] ?? 0);
        $redirect        = $this->getRedirectInvitado();
        $this->verificarCsrf($redirect);

        if ($recuperacion_id === 0) { header('Location: ' . $redirect); exit; }

        // Verificar que existe y es futura
        $q = $conexion->prepare(
            "SELECT r.id, r.token_id, c.fecha, c.hora_inicio
             FROM recuperacion r
             INNER JOIN clase c ON c.id = r.clase_recuperacion_id
             WHERE r.id = ? AND r.alumno_id = ? AND r.estado NOT IN ('realizada','cancelada')
             LIMIT 1"
        );
        $q->bind_param('ii', $recuperacion_id, $alumno_id);
        $q->execute();
        $recuperacion = $q->get_result()->fetch_assoc();

        if (!$recuperacion || strtotime($recuperacion['fecha'] . ' ' . $recuperacion['hora_inicio']) < time()) {
            header('Location: ' . $redirect);
            exit;
        }

        // Borrar recuperación
        $d = $conexion->prepare("DELETE FROM recuperacion WHERE id = ?");
        $d->bind_param('i', $recuperacion_id);
        $d->execute();

        // Devolver token (invitado solo tiene 1, siempre se devuelve)
        $u = $conexion->prepare("UPDATE token SET usado = FALSE WHERE id = ?");
        $u->bind_param('i', $recuperacion['token_id']);
        $u->execute();

        header('Location: ' . $this->buildRedirectWithMessage($redirect, 'cancelado'));
        exit;
    }
}
