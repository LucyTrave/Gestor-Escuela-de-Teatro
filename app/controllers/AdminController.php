<?php

require_once ROOT . '/app/helpers/Csrf.php';

class AdminController {

    private function requireAdminAuth(): void {
        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            if ($_SESSION['usuario_rol'] === 'profesor') {
                header('Location: ' . BASE_URL . '/profesor');
                exit;
            }

            header('Location: ' . BASE_URL . '/alumno');
            exit;
        }
    }

    private function redirect(string $ruta, string $mensaje = ''): void {
        $destino = BASE_URL . $ruta;
        if ($mensaje !== '') {
            $destino .= (strpos($ruta, '?') === false ? '?' : '&') . 'mensaje=' . urlencode($mensaje);
        }
        header('Location: ' . $destino);
        exit;
    }

    private function verificarCsrf(string $rutaRedirect): void {
        if (!Csrf::validatePost()) {
            $this->redirect($rutaRedirect, 'error_datos');
        }
    }

    private function mensaje(string $codigo): array {
        $mensajes = [
            'creado' => ['tipo' => 'exito', 'texto' => 'Registro creado correctamente.'],
            'actualizado' => ['tipo' => 'exito', 'texto' => 'Registro actualizado correctamente.'],
            'alumno_quitado' => ['tipo' => 'exito', 'texto' => 'Alumno quitado del evento correctamente.'],
            'error_datos' => ['tipo' => 'warning', 'texto' => 'Revisa los datos del formulario y vuelve a intentarlo.'],
            'evento_no_encontrado' => ['tipo' => 'warning', 'texto' => 'No se ha encontrado el evento solicitado.'],
            'evento_creado' => ['tipo' => 'exito','texto' => 'Evento creado correctamente.'],
            'aforo_lleno' => ['tipo' => 'warning','texto' => 'No hay plazas disponibles en este evento.'],
            'evento_actualizado' => ['tipo' => 'exito','texto' => 'Evento actualizado correctamente.'],          
            'evento_eliminado' => ['tipo' => 'exito','texto' => 'Evento eliminado correctamente.'],
        ];

        return $mensajes[$codigo] ?? ['tipo' => '', 'texto' => ''];
    }


    // Métodos para cargar datos comunes
    private function cargarSalas(): array {
        require ROOT . '/config/database.php';
        $resultado = $conexion->query("SELECT id, nombre, espacio_nombre, direccion FROM sala ORDER BY espacio_nombre, nombre");
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }


    private function cargarProfesores(): array{
    global $conexion;
     $resultado = $conexion->query("
        SELECT p.usuario_id AS id, 
               CONCAT(p.nombre, ' ', p.apellidos) AS nombre
        FROM profesor p
        ORDER BY p.nombre ASC
    ");

    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

    public function dashboard(): void {
        $this->requireAdminAuth();
        global $conexion;

        $metricas = [
            'posibles' => (int)$conexion->query("SELECT COUNT(*) total FROM alumno WHERE estado = 'posible'")->fetch_assoc()['total'],
            'matriculados' => (int)$conexion->query("SELECT COUNT(*) total FROM alumno WHERE estado = 'matriculado'")->fetch_assoc()['total'],
            'grupos' => (int)$conexion->query("SELECT COUNT(*) total FROM grupo WHERE activo = TRUE")->fetch_assoc()['total'],
            'eventos' => (int)$conexion->query("SELECT COUNT(*) total FROM evento_grupal")->fetch_assoc()['total'],
        ];

        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');
        require ROOT . '/app/views/admin/dashboard.php';
    }

    public function posibles(): void {
        $this->requireAdminAuth();
        global $conexion;
    
        //Pongo query dinámica para que funcionen los filtros de tablas y añado filtros
        $sql = "SELECT id, nombre, apellidos, email, telefono, nivel, tipo_interes, clase_prueba
                FROM alumno
                WHERE estado = 'posible'";
        $params = [];
        $tipos = '';    
        //Filtros
            if (!empty($_GET['nombre'])) {
                $sql .= " AND nombre LIKE ?";
                $params[] = '%' . $_GET['nombre'] . '%';
                $tipos .= 's';
            }
            if (!empty($_GET['email'])) {
                $sql .= " AND email LIKE ?";
                $params[] = '%' . $_GET['email'] . '%';
                $tipos .= 's';
            }
            if (!empty($_GET['telefono'])) {
                $sql .= " AND telefono LIKE ?";
                $params[] = '%' . $_GET['telefono'] . '%';
                $tipos .= 's';
            }
            if (!empty($_GET['nivel'])) {
                $sql .= " AND nivel = ?";
                $params[] = $_GET['nivel'];
                $tipos .= 's';
            }
            if (!empty($_GET['tipo_interes'])) {
                $sql .= " AND tipo_interes = ?";
                $params[] = $_GET['tipo_interes'];
                $tipos .= 's';
            }
            if (isset($_GET['clase_prueba']) && $_GET['clase_prueba'] !== '') {
                $sql .= " AND clase_prueba = ?";
                $params[] = (int)$_GET['clase_prueba'];
                $tipos .= 'i';
            }
            if (!empty($_GET['fecha_interes'])) {
                $sql .= " AND fecha_interes = ?";
                $params[] = $_GET['fecha_interes'];
                $tipos .= 's';
            }

            if (!empty($_GET['fecha_primera_clase'])) {
                $sql .= " AND fecha_primera_clase = ?";
                $params[] = $_GET['fecha_primera_clase'];
                $tipos .= 's';
            }
            if (!empty($_GET['fecha_registro'])) {
                $sql .= " AND fecha_registro = ?";
                $params[] = $_GET['fecha_registro'];
                $tipos .= 's';
            }

        $sql .= " ORDER BY nombre, apellidos";
        $stmt = $conexion->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($tipos, ...$params);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            $alumnos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];    

        $metricas = [
            'total' => count($alumnos),
            'clase_prueba' => 0,
            'intensivo' => 0,
            'ex_alumno' => 0,
        ];

        foreach ($alumnos as $alumno) {
            if ((int)$alumno['clase_prueba'] === 1) {
                $metricas['clase_prueba']++;
            }
            if (($alumno['tipo_interes'] ?? '') === 'intensivo') {
                $metricas['intensivo']++;
            }
            if (($alumno['tipo_interes'] ?? '') === 'ex_alumno') {
                $metricas['ex_alumno']++;
            }
        }

        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');
        require ROOT . '/app/views/admin/posibles.php';
    }

    //Pongo query dinámica para que funcionen los filtros de tablas y añado filtros
    public function matriculados(): void {
        $this->requireAdminAuth();
        global $conexion;

        // ─── QUERY BASE ─────────────────────────────────────────
        $sql = "SELECT a.id, a.nombre, a.apellidos,
                    g.nombre AS grupo_nombre
                FROM alumno a
                LEFT JOIN alumno_grupo ag ON ag.alumno_id = a.id AND ag.activo = TRUE
                LEFT JOIN grupo g ON g.id = ag.grupo_id
                WHERE a.estado = 'matriculado'";

        $params = [];
        $tipos = '';

        // ─── FILTROS ───────────────────────────────────────────
        if (!empty($_GET['nombre'])) {
            $sql .= " AND (a.nombre LIKE ? OR a.apellidos LIKE ?)";
            $busqueda = '%' . $_GET['nombre'] . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
            $tipos .= 'ss';
        }
        if (!empty($_GET['telefono'])) {
            $sql .= " AND a.telefono LIKE ?";
            $params[] = '%' . $_GET['telefono'] . '%';
            $tipos .= 's';
        }
        if (!empty($_GET['nivel'])) {
            $sql .= " AND a.nivel = ?";
            $params[] = $_GET['nivel'];
            $tipos .= 's';
        }
        if (!empty($_GET['grupo_id'])) {
            if ($_GET['grupo_id'] === 'sin_grupo') {
                $where[] = "a.grupo_id IS NULL";
            } else {
                $where[] = "a.grupo_id = ?";
                $params[] = (int)$_GET['grupo_id'];
                $tipos .= 'i';
            }
        }

        if (!empty($_GET['fecha_registro'])) {
            $sql .= " AND a.fecha_registro = ?";
            $params[] = $_GET['fecha_registro'];
            $tipos .= 's';
        }
        if (!empty($_GET['fecha_primera_clase'])) {
            $sql .= " AND a.fecha_primera_clase = ?";
            $params[] = $_GET['fecha_primera_clase'];
            $tipos .= 's';
        }

        $sql .= " ORDER BY a.nombre, a.apellidos";
        $stmt = $conexion->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $alumnos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

        $metricas = [
            'total_alumnos' => count($alumnos),
            'con_grupo' => 0,
            'sin_grupo' => 0,
        ];
        foreach ($alumnos as $alumno) {
            if (!empty($alumno['grupo_nombre'])) {
                $metricas['con_grupo']++;
            } else {
                $metricas['sin_grupo']++;
            }
        }       

        $grupos_result = $conexion->query("SELECT id, nombre FROM grupo WHERE activo = TRUE ORDER BY nombre");
        $grupos = $grupos_result ? $grupos_result->fetch_all(MYSQLI_ASSOC) : [];

        require ROOT . '/app/views/admin/matriculados.php';
    }


/**
 * Consulta principal de grupos.
 * 
 * Obtenemos:
 * - Datos del grupo
 * - Sala y espacio asignado
 * - Profesor responsable
 * - Número total de alumnos activos por grupo
 * 
 * Solo mostramos grupos activos (activo = 1)
 * para evitar enseñar grupos eliminados lógicamente.
 */
    public function grupos(): void {
        $this->requireAdminAuth();
        global $conexion;

       $resultado = $conexion->query(
    "SELECT g.id, g.nombre, g.dia_semana, g.hora_inicio, g.hora_fin, g.nivel, g.tipo, g.activo, g.curso, g.sala_id, g.profesor_id, g.fecha_inicio_curso, g.fecha_fin_curso,

        s.nombre AS sala_nombre,
        s.espacio_nombre,

        p.nombre AS profesor_nombre,

        MAX(c.cupo_maximo) AS cupo_maximo,

        COUNT(CASE WHEN ag.activo = TRUE THEN 1 END) AS total_alumnos

    FROM grupo g

    LEFT JOIN sala s
    ON s.id = g.sala_id

    LEFT JOIN profesor p
    ON p.usuario_id = g.profesor_id

    LEFT JOIN alumno_grupo ag
    ON ag.grupo_id = g.id

    LEFT JOIN clase c
    ON c.grupo_id = g.id

     WHERE g.activo = 1
     GROUP BY g.id
     ORDER BY g.dia_semana, g.hora_inicio, g.nombre"
);
        $grupos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        foreach ($grupos as &$grupo) { //coger horarios reales de clases

        $idGrupo = (int)$grupo['id']; 

        $consultaClases = "
            SELECT
                fecha,
                hora_inicio,
                hora_fin,
                cupo_maximo
            FROM clase
            WHERE grupo_id = $idGrupo
            ORDER BY hora_inicio ASC
        ";

        $resultadoClases = $conexion->query($consultaClases);

        $grupo['clases'] = [];

        if ($resultadoClases) {

            while ($clase = $resultadoClases->fetch_assoc()) {
                $grupo['clases'][] = $clase;
            }

        }

        }

        unset($grupo);
        $salas = $this->cargarSalas();
        $profesores = $this->cargarProfesores();

        // Variables por defecto del modal de alumnos
        $modalAlumnosActivo = false;
        $grupoModal = null;
        $alumnos = [];
        $alumnosDisponibles = [];



// Variables para controlar el modal de alumnos
$modalAlumnosActivo = false;
$grupoModal = null;
$alumnos = [];
$alumnosDisponibles = [];

if (isset($_GET['modal'], $_GET['id']) && $_GET['modal'] === 'alumnos') {
    $modalAlumnosActivo = true;
    $idGrupoModal = (int) $_GET['id'];

    foreach ($grupos as $grupo) {
        if ((int)$grupo['id'] === $idGrupoModal) {
            $grupoModal = $grupo;
            break;
        }
    }
}

// Si el modal de alumnos está activo y se ha encontrado el grupo, cargamos los alumnos del grupo y los disponibles para agregar

if ($grupoModal !== null) {
    $consultaAlumnos = "
        SELECT a.id AS alumno_id, a.nombre, a.apellidos, a.email, a.telefono
        FROM alumno_grupo ag
        INNER JOIN alumno a ON ag.alumno_id = a.id
        WHERE ag.grupo_id = $idGrupoModal
        AND ag.activo = 1
        ORDER BY a.nombre ASC
    ";

    $resultadoAlumnos = $conexion->query($consultaAlumnos);

    if ($resultadoAlumnos) {
        while ($fila = $resultadoAlumnos->fetch_assoc()) {
            $alumnos[] = $fila;
        }
    }

$nivelGrupoModal = $conexion->real_escape_string($grupoModal['nivel']);

$consultaDisponibles = "
    SELECT id, nombre, apellidos,nivel
    FROM alumno
    WHERE estado = 'matriculado'
    AND nivel = '$nivelGrupoModal'
    AND id NOT IN (
        SELECT alumno_id
        FROM alumno_grupo
        WHERE grupo_id = $idGrupoModal
    )
    ORDER BY nombre ASC
";

    $resultadoDisponibles = $conexion->query($consultaDisponibles);

    if ($resultadoDisponibles) {
        while ($fila = $resultadoDisponibles->fetch_assoc()) {
            $alumnosDisponibles[] = $fila;
        }
    }
}






        $metricas = ['total' => count($grupos), 'iniciacion' => 0, 'intermedio' => 0, 'avanzado' => 0];
        foreach ($grupos as $grupo) {
            $nivel = $grupo['nivel'] ?? '';
            if (isset($metricas[$nivel])) {
                $metricas[$nivel]++;
            }
        }

        require ROOT . '/app/views/admin/grupos.php';
    }

    public function especiales(): void {
        $this->requireAdminAuth();
        global $conexion;

        $resultado = $conexion->query(
            "SELECT e.id, e.nombre, e.tipo, e.descripcion, e.fecha, e.hora, e.plazas_maximas,
                    COUNT(CASE WHEN i.estado = 'inscrito' THEN 1 END) AS apuntados
             FROM evento_grupal e
             LEFT JOIN inscripcion_evento i ON i.evento_id = e.id
             GROUP BY e.id
             ORDER BY e.fecha ASC"
        );
        $eventos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($eventos as &$evento) {
            $stmt = $conexion->prepare(
                "SELECT a.id, a.nombre, a.apellidos
                 FROM inscripcion_evento i
                 INNER JOIN alumno a ON a.id = i.alumno_id
                 WHERE i.evento_id = ? AND i.estado = 'inscrito'
                 ORDER BY a.nombre, a.apellidos"
            );
            $stmt->bind_param('i', $evento['id']);
            $stmt->execute();
            $evento['alumnos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        unset($evento);

        $metricas = ['total' => count($eventos), 'intensivos' => 0, 'salidas' => 0];
        foreach ($eventos as $evento) {
            if ($evento['tipo'] === 'intensivo') {
                $metricas['intensivos']++;
            }
            if ($evento['tipo'] === 'salida_teatro') {
                $metricas['salidas']++;
            }
        }

        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');
        require ROOT . '/app/views/admin/especiales.php';
    }

    public function gestionarEvento(): void {
        $this->requireAdminAuth();
        global $conexion;

        $idEvento = (int)($_GET['id'] ?? 0);
        if ($idEvento <= 0) {
            $this->redirect('/admin/especiales', 'evento_no_encontrado');
        }

        $stmt = $conexion->prepare(
            "SELECT e.id, e.nombre, e.tipo, e.fecha, e.hora, e.descripcion, e.plazas_maximas
             FROM evento_grupal e
             WHERE e.id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $idEvento);
        $stmt->execute();
        $evento = $stmt->get_result()->fetch_assoc();

        if (!$evento) {
            $this->redirect('/admin/especiales', 'evento_no_encontrado');
        }

        $stmt = $conexion->prepare(
            "SELECT a.id, a.nombre, a.apellidos
             FROM inscripcion_evento i
             INNER JOIN alumno a ON a.id = i.alumno_id
             WHERE i.evento_id = ? AND i.estado = 'inscrito'
             ORDER BY a.nombre, a.apellidos"
        );
        $stmt->bind_param('i', $idEvento);
        $stmt->execute();
        $alumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt = $conexion->prepare(
            "SELECT id, nombre, apellidos, email
             FROM alumno
             WHERE estado = 'matriculado'
             ORDER BY nombre, apellidos"
        );
        $stmt->execute();
        $todosAlumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');

        $total_apuntados = count($alumnos);
        $plazas_maximas = (int)$evento['plazas_maximas'];
        $plazas_libres = max(0, $plazas_maximas - $total_apuntados);

        $inscritos_ids = array_column($alumnos, 'id');

        $alumnos_inscritos = [];
        $alumnos_no_inscritos = [];

        foreach ($todosAlumnos as $a) {
            if (in_array($a['id'], $inscritos_ids)) {
                $alumnos_inscritos[] = $a;
            } else {
                $alumnos_no_inscritos[] = $a;
            }
        }

        $todosAlumnosOrdenados = array_merge($alumnos_inscritos, $alumnos_no_inscritos);
        
        require ROOT . '/app/views/admin/gestionar_evento.php';
    }

    //Inscribir alumno en evento y controlar aforo máximo
    public function inscribirAlumnoEvento(): void {
        $this->requireAdminAuth();
        global $conexion;

        $evento_id = (int)($_POST['evento_id'] ?? 0);
        $alumno_id = (int)($_POST['alumno_id'] ?? 0);
        $this->verificarCsrf($evento_id > 0 ? '/admin/especiales/gestionar?id=' . $evento_id : '/admin/especiales');

        if ($evento_id > 0 && $alumno_id > 0) {

            //Obtener plazas máximas
            $stmt = $conexion->prepare("
                SELECT plazas_maximas
                FROM evento_grupal
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->bind_param('i', $evento_id);
            $stmt->execute();
            $evento = $stmt->get_result()->fetch_assoc();

            $plazas_maximas = (int)($evento['plazas_maximas'] ?? 0);

            //Contar inscritos actuales
            $stmt = $conexion->prepare("
                SELECT COUNT(*) AS total
                FROM inscripcion_evento
                WHERE evento_id = ? AND estado = 'inscrito'
            ");
            $stmt->bind_param('i', $evento_id);
            $stmt->execute();
            $total = (int)$stmt->get_result()->fetch_assoc()['total'];

            //Comprobar aforo
            if ($total >= $plazas_maximas) {
                header('Location: ' . BASE_URL . '/admin/especiales/gestionar?id=' . $evento_id . '&mensaje=aforo_lleno#lista-alumnos');
                exit;
            }

            //Evitar duplicados
            $check = $conexion->prepare("
                SELECT id FROM inscripcion_evento
                WHERE evento_id = ? AND alumno_id = ?
                LIMIT 1
            ");
            $check->bind_param('ii', $evento_id, $alumno_id);
            $check->execute();
            $existe = $check->get_result()->num_rows > 0;

            if (!$existe) {
                $stmt = $conexion->prepare("
                    INSERT INTO inscripcion_evento (evento_id, alumno_id, estado)
                    VALUES (?, ?, 'inscrito')
                ");
                $stmt->bind_param('ii', $evento_id, $alumno_id);
                $stmt->execute();
            }
        }

        header('Location: ' . BASE_URL . '/admin/especiales/gestionar?id=' . $evento_id . '#lista-alumnos');
        exit;
    }

    public function quitarAlumnoEvento(): void {
        $this->requireAdminAuth();
        global $conexion;

        $idEvento = (int)($_POST['evento_id'] ?? 0);
        $idAlumno = (int)($_POST['alumno_id'] ?? 0);
        $this->verificarCsrf($idEvento > 0 ? '/admin/especiales/gestionar?id=' . $idEvento : '/admin/especiales');

        if ($idEvento > 0 && $idAlumno > 0) {
            $stmt = $conexion->prepare("DELETE FROM inscripcion_evento WHERE evento_id = ? AND alumno_id = ? LIMIT 1");
            $stmt->bind_param('ii', $idEvento, $idAlumno);
            $stmt->execute();
        }

        header('Location: ' . BASE_URL . '/admin/especiales/gestionar?id=' . $idEvento . '#lista-alumnos');
        exit;
    }

    public function detalleAlumno(): void {
        $this->requireAdminAuth();
        global $conexion;

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/posibles', 'error_datos');
        }

        $stmt = $conexion->prepare(
            "SELECT a.*, g.nombre AS grupo_nombre
             FROM alumno a
             LEFT JOIN alumno_grupo ag ON ag.alumno_id = a.id AND ag.activo = TRUE
             LEFT JOIN grupo g ON g.id = ag.grupo_id
             WHERE a.id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $alumno = $stmt->get_result()->fetch_assoc();

        if (!$alumno) {
            $this->redirect('/admin/posibles', 'error_datos');
        }

        $mensaje = $this->mensaje($_GET['mensaje'] ?? '');
        require ROOT . '/app/views/admin/detalle_alumno.php';
    }

    public function crearAlumno(): void {
        $this->requireAdminAuth();

        $origen = $_GET['origen'] ?? 'posibles';
        $errores = $_SESSION['errores_formulario'] ?? [];
        $datos = $_SESSION['datos_formulario'] ?? [];
        unset($_SESSION['errores_formulario'], $_SESSION['datos_formulario']);

        require ROOT . '/app/views/admin/crear_alumno.php';
    }

    public function guardarAlumno(): void {
        $this->requireAdminAuth();
        $this->verificarCsrf('/admin/alumnos/crear');
        global $conexion;

        $origen = $_POST['origen'] ?? 'posibles';

        $estado = ($origen === 'matriculados')
            ? 'matriculado'
            : ($_POST['estado'] ?? 'posible');

        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'estado' => $estado,
            'nivel' => $_POST['nivel'] ?? '',
            'tipo_interes' => $_POST['tipo_interes'] ?? '',
            'clase_prueba' => (int)($_POST['clase_prueba'] ?? 0),
            'fecha_interes' => $_POST['fecha_interes'] ?? '',
            'fecha_primera_clase' => $_POST['fecha_primera_clase'] ?? '',
        ];

        $errores = [];
        if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
        if ($datos['apellidos'] === '') $errores[] = 'Los apellidos son obligatorios.';
        if ($datos['email'] !== '' && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no tiene un formato valido.';
        if ($datos['telefono'] !== '' && !preg_match('/^[0-9]{9}$/', $datos['telefono'])) $errores[] = 'El telefono debe tener 9 digitos.';

        if ($errores) {
            $_SESSION['errores_formulario'] = $errores;
            $_SESSION['datos_formulario'] = $datos;
            $this->redirect('/admin/alumnos/crear');
        }

        $stmt = $conexion->prepare(
            "INSERT INTO alumno (
                nombre, apellidos, email, telefono, estado, nivel, tipo_interes,
                clase_prueba, fecha_interes, fecha_primera_clase
            ) VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, NULLIF(?, ''), NULLIF(?, ''), ?, NULLIF(?, ''), NULLIF(?, ''))"
        );
        $stmt->bind_param(
            'sssssssiss',
            $datos['nombre'],
            $datos['apellidos'],
            $datos['email'],
            $datos['telefono'],
            $datos['estado'],
            $datos['nivel'],
            $datos['tipo_interes'],
            $datos['clase_prueba'],
            $datos['fecha_interes'],
            $datos['fecha_primera_clase']
        );
        $stmt->execute();

        $ruta = ($origen === 'matriculados') ? '/admin/matriculados' : '/admin/posibles';
        header('Location: ' . BASE_URL . $ruta . '?ok=creado');
        exit;
    }

    public function editarAlumno(): void {
        $this->requireAdminAuth();
        global $conexion;

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/posibles', 'error_datos');
        }

        $stmt = $conexion->prepare("SELECT * FROM alumno WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $alumno = $stmt->get_result()->fetch_assoc();

        if (!$alumno) {
            $this->redirect('/admin/posibles', 'error_datos');
        }

        require ROOT . '/app/views/admin/editar_alumno.php';
    }

    public function actualizarAlumno(): void {
        $this->requireAdminAuth();
        global $conexion;

        $id = (int)($_POST['id'] ?? 0);
        $origen = $_POST['origen'] ?? 'posibles';
        $this->verificarCsrf($id > 0 ? '/admin/alumnos/editar?id=' . $id . '&origen=' . $origen : '/admin/posibles');

        if ($id <= 0) {
            $this->redirect('/admin/posibles', 'error_datos');
        }

        $stmt = $conexion->prepare(
            "UPDATE alumno SET
                nombre = ?, apellidos = ?, email = NULLIF(?, ''), telefono = NULLIF(?, ''),
                estado = ?, nivel = NULLIF(?, ''), tipo_interes = NULLIF(?, ''), clase_prueba = ?,
                fecha_interes = NULLIF(?, ''), fecha_primera_clase = NULLIF(?, '')
            WHERE id = ?"
        );

        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        $estado = ($origen === 'matriculados')
            ? 'matriculado'
            : ($_POST['estado'] ?? 'posible');

        $nivel = $_POST['nivel'] ?? '';
        $tipoInteres = $_POST['tipo_interes'] ?? '';
        $clasePrueba = (int)($_POST['clase_prueba'] ?? 0);
        $fechaInteres = $_POST['fecha_interes'] ?? '';
        $fechaPrimeraClase = $_POST['fecha_primera_clase'] ?? '';

        $stmt->bind_param(
            'sssssssissi',
            $nombre,
            $apellidos,
            $email,
            $telefono,
            $estado,
            $nivel,
            $tipoInteres,
            $clasePrueba,
            $fechaInteres,
            $fechaPrimeraClase,
            $id
        );

        $stmt->execute();

        $this->redirect('/admin/alumnos/detalle?id=' . $id . '&origen=' . $origen, 'actualizado');
    }

    //Método para eliminar alumno de posibles y de matriculados
    public function eliminarAlumno(): void {
        $this->requireAdminAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/matriculados');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $origen = $_POST['origen'] ?? 'posibles';
        $ruta = ($origen === 'matriculados') ? '/admin/matriculados' : '/admin/posibles';
        $this->verificarCsrf($ruta);

        if ($id <= 0) {
            header('Location: ' . BASE_URL . $ruta);
            exit;
        }

        global $conexion;

        $stmt = $conexion->prepare("UPDATE alumno SET estado = 'baja' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        header('Location: ' . BASE_URL . $ruta . '?ok=eliminado');
        exit;
    }

    // Método para desactivar un grupo sin borrarlo físicamente de la base de datos

    public function eliminarGrupo(): void
    {
    $this->requireAdminAuth();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/admin/grupos');
        exit;
    }
    $this->verificarCsrf('/admin/grupos');

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ' . BASE_URL . '/admin/grupos');
        exit;
    }

    global $conexion;

    $stmt = $conexion->prepare("UPDATE grupo SET activo = 0 WHERE id = ?");

    if (!$stmt) {
        die("Error preparando la consulta: " . $conexion->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmtClase = $conexion->prepare("
        UPDATE clase
        SET estado = 'cancelada'
        WHERE grupo_id = ?
    ");

    $stmtClase->bind_param('i', $id);
    $stmtClase->execute();

    header('Location: ' . BASE_URL . '/admin/grupos');
    exit;
}




    // Método para mostrar el formulario de edición de un grupo
    public function editarGrupo() {
        $this->requireAdminAuth();
        require ROOT . '/config/database.php';

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $consulta = "SELECT * FROM grupo WHERE id = $id LIMIT 1";
        $resultado = $conexion->query($consulta);

        if (!$resultado || $resultado->num_rows === 0) {
        die('Grupo no encontrado');
        }

        $grupo = $resultado->fetch_assoc();

    require ROOT . '/app/views/admin/editar_grupo.php';
}


   // Método para procesar la actualización de un Grupo
public function actualizarGrupo()
{
    $this->requireAdminAuth();
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $this->verificarCsrf($id > 0 ? '/admin/grupos/editar?id=' . $id : '/admin/grupos');

    require ROOT . '/config/database.php';

    $nombre = trim($_POST['nombre'] ?? '');
    $dia_semana = trim($_POST['dia_semana'] ?? '');
    $hora_inicio = trim($_POST['hora_inicio'] ?? '');
    $curso = trim($_POST['curso'] ?? '');
    $nivel = trim($_POST['nivel'] ?? '');
    $profesor_id = trim($_POST['profesor_id'] ?? '');
    $sala_id = isset($_POST['sala_id']) ? (int) $_POST['sala_id'] : 0;
    $hora_fin = trim($_POST['hora_fin'] ?? '');
    $aforo_maximo = (int)($_POST['aforo_maximo'] ?? 16);
    $fecha_inicio_curso = trim($_POST['fecha_inicio_curso'] ?? '');
    $fecha_fin_curso = trim($_POST['fecha_fin_curso'] ?? '');

    if (
        $id <= 0 ||
        $nombre === '' ||
        $dia_semana === '' ||
        $hora_inicio === '' ||
        $hora_fin === '' ||
        $nivel === '' ||
        $profesor_id === '' ||
        $sala_id <= 0 ||
        $fecha_inicio_curso === '' ||
        $fecha_fin_curso === ''
    ) {
        die('Faltan datos obligatorios');
    }

   $stmt = $conexion->prepare("
    UPDATE grupo
    SET dia_semana = ?,
        hora_inicio = ?,
        hora_fin = ?,
        nivel = ?,
        curso = ?,
        profesor_id = ?,
        sala_id = ?,
        fecha_inicio_curso = ?,
        fecha_fin_curso = ?
    WHERE id = ?
");

if (!$stmt) {
    die('Error preparando la consulta: ' . $conexion->error);
}

$stmt->bind_param(
    "sssssiissi",
    $dia_semana,
    $hora_inicio,
    $hora_fin,
    $nivel,
    $curso,
    $profesor_id,
    $sala_id,
    $fecha_inicio_curso,
    $fecha_fin_curso,
    $id
);

$stmt->execute();

    $stmtClase = $conexion->prepare("
    UPDATE clase
    SET hora_inicio = ?,
        hora_fin = ?,
        cupo_maximo = ?
    WHERE grupo_id = ?
    ");

    $stmtClase->bind_param(
        "ssii",
        $hora_inicio,
        $hora_fin,
        $aforo_maximo,
        $id
    );

    $stmtClase->execute();

    header('Location: ' . BASE_URL . '/admin/grupos?editado=1');
    exit;
}

// Método para ver alumnos de un grupo
public function alumnosGrupo()
{
    $this->requireAdminAuth();
    require ROOT . '/config/database.php';

    $idGrupo = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($idGrupo <= 0) {
        die('Grupo no válido');
    }

     // Consulta para obtener los datos del grupo
    $consultaGrupo = "SELECT * FROM grupo WHERE id = $idGrupo LIMIT 1";
    $resultadoGrupo = $conexion->query($consultaGrupo);

    if (!$resultadoGrupo || $resultadoGrupo->num_rows === 0) {
        die('Grupo no encontrado');
    }

    $grupo = $resultadoGrupo->fetch_assoc();

    // Consulta para obtener los alumnos del grupo
    $consultaAlumnos = "
        SELECT a.id AS alumno_id, a.nombre, a.apellidos, a.email, a.telefono
        FROM alumno_grupo ag
        INNER JOIN alumno a ON ag.alumno_id = a.id
        WHERE ag.grupo_id = $idGrupo
        AND ag.activo = 1
        ORDER BY a.nombre ASC
        ";

    $resultadoAlumnos = $conexion->query($consultaAlumnos);

    $alumnos = [];

    if ($resultadoAlumnos) {
    while ($fila = $resultadoAlumnos->fetch_assoc()) {
        $alumnos[] = $fila;
    }
    }


$consultaDisponibles = "
SELECT id, nombre, apellidos
FROM alumno
WHERE estado = 'matriculado'
AND id NOT IN (
    SELECT alumno_id
    FROM alumno_grupo
    WHERE grupo_id = $idGrupo
)
ORDER BY nombre ASC
";

$resultadoDisponibles = $conexion->query($consultaDisponibles);

$alumnosDisponibles = [];

if ($resultadoDisponibles) {
    while ($fila = $resultadoDisponibles->fetch_assoc()) {
        $alumnosDisponibles[] = $fila;
    }
}
    require ROOT . '/app/views/admin/alumnos_grupo.php';
}

// Método para eliminar un alumno de un grupo
public function eliminarAlumnoGrupo()
{
    $this->requireAdminAuth();
    require ROOT . '/config/database.php';

    $idGrupo = isset($_POST['grupo_id']) ? (int) $_POST['grupo_id'] : 0;
    $idAlumno = isset($_POST['alumno_id']) ? (int) $_POST['alumno_id'] : 0;
    $this->verificarCsrf($idGrupo > 0 ? '/admin/grupos?modal=alumnos&id=' . $idGrupo : '/admin/grupos');

    if ($idGrupo <= 0 || $idAlumno <= 0) {
        $this->redirect('/admin/grupos', 'error_datos');
    }

    $stmt = $conexion->prepare("
        DELETE FROM alumno_grupo
        WHERE grupo_id = ?
        AND alumno_id = ?
    ");

    $stmt->bind_param("ii", $idGrupo, $idAlumno);
    $stmt->execute();

    //header('Location: ' . BASE_URL . '/admin/grupos/alumnos?id=' . $idGrupo);lo modifico para que no redirija 
    //al mismo sitio y así evitar que al actualizar la página se vuelva a ejecutar la eliminación.

header('Location: ' . BASE_URL . '/admin/grupos?modal=alumnos&id=' . $idGrupo . '&ok=alumno_eliminado');
    exit;
}

// Método para pasar a un alumno de posible a matriculado
public function matricularAlumno(): void {
    $this->requireAdminAuth();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/admin/posibles');
        exit;
    }
    $this->verificarCsrf('/admin/posibles');

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ' . BASE_URL . '/admin/posibles');
        exit;
    }

    global $conexion;
    $stmt = $conexion->prepare("UPDATE alumno SET estado = 'matriculado' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header('Location: ' . BASE_URL . '/admin/posibles?ok=matriculado');
    exit;
}


// MÉTODO para agregar un alumno a un grupo
public function agregarAlumnoGrupo()
{
    $this->requireAdminAuth();
    require ROOT . '/config/database.php';

    $idGrupo = isset($_POST['grupo_id']) ? (int) $_POST['grupo_id'] : 0;
    $idAlumno = isset($_POST['alumno_id']) ? (int) $_POST['alumno_id'] : 0;
    $this->verificarCsrf($idGrupo > 0 ? '/admin/grupos?modal=alumnos&id=' . $idGrupo : '/admin/grupos');

    if ($idGrupo <= 0 || $idAlumno <= 0) {
        $this->redirect('/admin/grupos', 'error_datos');
    }

    // Comprobar si ya existe
$check = $conexion->prepare("
    SELECT id FROM alumno_grupo
    WHERE alumno_id = ?
    AND grupo_id = ?
    LIMIT 1
");

$check->bind_param("ii", $idAlumno, $idGrupo);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Ya existe → no insertar
    //header('Location: ' . BASE_URL . '/admin/grupos/alumnos?id=' . $idGrupo);lo modifico para que no redirija
    // al mismo sitio y así evitar que al actualizar la página se vuelva a ejecutar la inserción
    header('Location: ' . BASE_URL . '/admin/grupos?modal=alumnos&id=' . $idGrupo);
    exit;
}

    $stmt = $conexion->prepare("
        INSERT INTO alumno_grupo (alumno_id, grupo_id, activo)
        VALUES (?, ?, 1)
    ");

    $stmt->bind_param("ii", $idAlumno, $idGrupo);
    $stmt->execute();

    //header('Location: ' . BASE_URL . '/admin/grupos/alumnos?id=' . $idGrupo);lo modifico para que no redirija
    // al mismo sitio y así evitar que al actualizar la página se vuelva a ejecutar la inserción

    header('Location: ' . BASE_URL . '/admin/grupos?modal=alumnos&id=' . $idGrupo . '&ok=alumno_agregado');
    exit;
}

//VISTA ESPECIALES
public function crearEvento(): void {
    $this->requireAdminAuth();
    require ROOT . '/app/views/admin/crear_evento.php';
}

public function guardarEvento(): void {
    $this->requireAdminAuth();
    $this->verificarCsrf('/admin/especiales/crear');
    global $conexion;

    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $plazas = (int)($_POST['plazas_maximas'] ?? 0);
    $hora = $_POST['hora'] ?? null;
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '' || $tipo === '' || $fecha === '' || $plazas <= 0) {
        $this->redirect('/admin/especiales/crear', 'error_datos');
    }

    $stmt = $conexion->prepare("
        INSERT INTO evento_grupal (nombre, tipo, descripcion, fecha, hora, plazas_maximas)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param('sssssi', $nombre, $tipo, $descripcion, $fecha, $hora, $plazas);
    $stmt->execute();

    $this->redirect('/admin/especiales', 'evento_creado');
}

public function editarEvento(): void {
    $this->requireAdminAuth();
    global $conexion;

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        $this->redirect('/admin/especiales', 'evento_no_encontrado');
    }

    $stmt = $conexion->prepare("
        SELECT * FROM evento_grupal WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();

    if (!$evento) {
        $this->redirect('/admin/especiales', 'evento_no_encontrado');
    }

    require ROOT . '/app/views/admin/editar_evento.php';
}

public function actualizarEvento(): void {
    $this->requireAdminAuth();
    global $conexion;

    $id = (int)($_POST['id'] ?? 0);
    $this->verificarCsrf($id > 0 ? '/admin/especiales/editar?id=' . $id : '/admin/especiales');

    $stmt = $conexion->prepare("
        UPDATE evento_grupal SET
            nombre = ?, tipo = ?, descripcion = ?, fecha = ?, hora = ?, plazas_maximas = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        'sssssii',
        $_POST['nombre'],
        $_POST['tipo'],
        $_POST['descripcion'],
        $_POST['fecha'],
        $_POST['hora'],
        $_POST['plazas_maximas'],
        $id
    );

    $stmt->execute();

    $this->redirect('/admin/especiales', 'evento_actualizado');
}

public function eliminarEvento(): void {
    $this->requireAdminAuth();
    $this->verificarCsrf('/admin/especiales');
    global $conexion;

    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {

        // borrar inscripciones primero
        $stmt = $conexion->prepare("DELETE FROM inscripcion_evento WHERE evento_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // borrar evento
        $stmt = $conexion->prepare("DELETE FROM evento_grupal WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    $this->redirect('/admin/especiales', 'evento_eliminado');
}

// Método para Crear un Nuevo Grupo
public function crearGrupo()
{
    $this->requireAdminAuth();
    // Si entra por GET, muestra el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        require ROOT . '/config/database.php';
        $profesores = $conexion->query(
            "SELECT p.usuario_id, p.nombre, p.apellidos FROM profesor p
             INNER JOIN usuario u ON u.id = p.usuario_id
             WHERE u.rol = 'profesor'
             ORDER BY p.nombre ASC"
        )->fetch_all(MYSQLI_ASSOC);
        require ROOT . '/app/views/admin/grupos_crear.php';
        return;
    }
    // Si entra por POST, guarda el grupo en la base de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->verificarCsrf('/admin/grupos/crear');

    // Si el administrador selecciona "Otro nombre", guardamos el nombre personalizado
        $nombre = $_POST['nombre'] ?? ''; 

    if ($nombre === 'otro') {
        $nombre = trim($_POST['nombre_personalizado'] ?? '');
        }
        $dia_semana = $_POST['dia_semana'] ?? '';
        $hora_inicio = $_POST['hora_inicio'] ?? '';
        $nivel = $_POST['nivel'] ?? '';
        $curso = $_POST['curso'] ?? '';
        $sala_id = intval($_POST['sala_id'] ?? 0);
        $hora_inicio = $_POST['hora_inicio'] ?? '';
        $hora_fin = $_POST['hora_fin'] ?? '';

        $cupo_maximo = (int)($_POST['cupo_maximo'] ?? 16);

        $fecha_inicio_curso = $_POST['fecha_inicio_curso'] ?? '';
        $fecha_fin_curso = $_POST['fecha_fin_curso'] ?? '';

        $profesor_id = $_POST['profesor_id'] ?? '';
        $activo = 1;

        require ROOT . '/config/database.php';

        $sql = "INSERT INTO grupo
                (nombre, dia_semana, hora_inicio, hora_fin, nivel, curso, sala_id, profesor_id, fecha_inicio_curso, fecha_fin_curso, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            die("Error preparando la consulta: " . $conexion->error);
        }

        $stmt->bind_param(
        "ssssssisssi",
        $nombre,
        $dia_semana,
        $hora_inicio,
        $hora_fin,
        $nivel,
        $curso,
        $sala_id,
        $profesor_id,
        $fecha_inicio_curso,
        $fecha_fin_curso,
        $activo
        );


        if ($stmt->execute()) {

        $grupo_id = $conexion->insert_id;

        $sqlClase = "
            INSERT INTO clase
            (
                grupo_id,
                sala_id,
                fecha,
                hora_inicio,
                hora_fin,
                cupo_maximo,
                estado
            )
            VALUES (?, ?, ?, ?, ?, ?, 'programada')
        ";

        $stmtClase = $conexion->prepare($sqlClase);

        if (!$stmtClase) {
            die("Error preparando clase: " . $conexion->error);
        }

        $stmtClase->bind_param(
            "iisssi",
            $grupo_id,
            $sala_id,
            $fecha_inicio_curso,
            $hora_inicio,
            $hora_fin,
            $cupo_maximo
        );

        $stmtClase->execute();

        header('Location: ' . BASE_URL . '/admin/grupos?creado=1');
        exit;

        }

        die("Error al crear el grupo: " . $stmt->error);
    }
}

// Eliminar sala
public function eliminarSala(): void
{
    $this->requireAdminAuth();
    $this->verificarCsrf('/admin/grupos?tab=salas');
    global $conexion;

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ' . BASE_URL . '/admin/grupos');
        exit;
    }

    // Comprobar si existen grupos asociados a esta sala
    $sqlComprobar = "
        SELECT COUNT(*) AS total
        FROM grupo
        WHERE sala_id = ?
    ";

    $stmtComprobar = $conexion->prepare($sqlComprobar);

    if (!$stmtComprobar) {
        die('Error preparando comprobación: ' . $conexion->error);
    }

    $stmtComprobar->bind_param('i', $id);
    $stmtComprobar->execute();

    $resultado = $stmtComprobar->get_result();
    $fila = $resultado->fetch_assoc();

    // Si hay grupos usando esta sala, no permitir eliminar
    if ((int)$fila['total'] > 0) {

        header('Location: ' . BASE_URL . '/admin/grupos?tab=salas&error=sala_con_grupos');
        exit;
    }

    // Eliminar sala
    $stmt = $conexion->prepare("
        DELETE FROM sala
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die('Error preparando consulta: ' . $conexion->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    header('Location: ' . BASE_URL . '/admin/grupos?tab=salas&eliminada_sala=1');
    exit;
}

// Editar sala
public function actualizarSala(): void
{
    $this->requireAdminAuth();
    $this->verificarCsrf('/admin/grupos?tab=salas');
    global $conexion;

    $id = (int)($_POST['id'] ?? 0);

    $nombre = trim($_POST['nombre'] ?? '');
    $espacio = trim($_POST['espacio_nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (
        $id <= 0 ||
        $nombre === '' ||
        $espacio === '' ||
        $direccion === ''
    ) {
        header('Location: ' . BASE_URL . '/admin/grupos');
        exit;
    }

    $stmt = $conexion->prepare("
        UPDATE sala
        SET nombre = ?,
            espacio_nombre = ?,
            direccion = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        die('Error preparando consulta: ' . $conexion->error);
    }

    $stmt->bind_param(
        'sssi',
        $nombre,
        $espacio,
        $direccion,
        $id
    );

    $stmt->execute();

    header('Location: ' . BASE_URL . '/admin/grupos?tab=salas&editada_sala=1');
    exit;
}

//Crear sala
public function crearSala(): void
{
    $this->requireAdminAuth();
    $this->verificarCsrf('/admin/grupos?tab=salas');
    global $conexion;

    $nombre = trim($_POST['nombre'] ?? '');
    $espacio = trim($_POST['espacio_nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (
        $nombre === '' ||
        $espacio === '' ||
        $direccion === ''
    ) {
        header('Location: ' . BASE_URL . '/admin/grupos');
        exit;
    }

    $tipo = 'aula';
    $aforo = 16;

    $stmt = $conexion->prepare("
        INSERT INTO sala
        (nombre, espacio_nombre, direccion, tipo, aforo_maximo)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die('Error preparando consulta: ' . $conexion->error);
    }

    $stmt->bind_param(
        'ssssi',
        $nombre,
        $espacio,
        $direccion,
        $tipo,
        $aforo
    );

    $stmt->execute();

    header('Location: ' . BASE_URL . '/admin/grupos?tab=salas&creada_sala=1');
    exit;
}

}