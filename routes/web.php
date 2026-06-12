<?php

$uri_completa = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = substr($uri_completa, strlen(BASE_URL));
if ($uri === '' || $uri === false) $uri = '/';

$metodo = $_SERVER['REQUEST_METHOD'];

// ─── Auth ────────────────────────────────────────────────────────────────────

if ($uri === '/') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if ($uri === '/login') {
    require_once ROOT . '/app/controllers/AuthController.php';
    $ctrl = new AuthController();
    if ($metodo === 'POST') {
        $ctrl->processLogin();
    } else {
        $ctrl->showLogin();
    }
    exit;
}

if ($uri === '/logout') {
    require_once ROOT . '/app/controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

if ($uri === '/dashboard') {
    require_once ROOT . '/app/controllers/AuthController.php';
    (new AuthController())->showDashboard();
    exit;
}

// ─── Admin ───────────────────────────────────────────────────────────────────

if ($uri === '/admin') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->dashboard();
    exit;
}

if ($uri === '/admin/posibles') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->posibles();
    exit;
}

if ($uri === '/admin/matriculados') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->matriculados();
    exit;
}
if ($uri === '/admin/grupos') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->grupos();
    exit;
}
if ($uri === '/admin/salas/eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarSala();
    exit;
}
if ($uri === '/admin/salas/actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->actualizarSala();
    exit;
}
if ($uri === '/admin/salas/crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->crearSala();
    exit;
}

// Esta ruta procesa la URL de creación de Grupos

// GEST : Muestra formulario de creación
if ($uri === '/admin/grupos/crear' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->crearGrupo();
    exit;
}

// POST: Procesa formulario de creación
if ($uri === '/admin/grupos/crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->crearGrupo();
    exit;
}

// Esta ruta procesa la URL de edición de Grupos
if ($uri === '/admin/grupos/editar') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->editarGrupo();
    exit;
}
if ($uri === '/admin/grupos/actualizar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->actualizarGrupo();
    exit;
}
if ($uri === '/admin/alumnos/eliminar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarAlumno();
    exit;
}

if ($uri === '/admin/alumnos/eliminar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarAlumno();
    exit;
}

if ($uri === '/admin/alumnos/matricular' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->matricularAlumno();
    exit;
}

if ($uri === '/admin/especiales') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->especiales();
    exit;
}

if ($uri === '/admin/grupos/alumnos') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->alumnosGrupo();
    exit;
}

if ($uri === '/admin/grupos/alumnos') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->alumnosGrupo();
    exit;
}

if ($uri === '/admin/grupos/eliminar-alumno' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarAlumnoGrupo();
    exit;
}

if ($uri === '/admin/grupos/agregar-alumno' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->agregarAlumnoGrupo();
    exit;
}

if ($uri === '/admin/especiales/gestionar') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->gestionarEvento();
    exit;
}

if ($uri === '/admin/especiales/quitar-alumno' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->quitarAlumnoEvento();
    exit;
}

if (rtrim($uri, '/') === '/admin/alumnos/crear') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->crearAlumno();
    exit;
}

if ($uri === '/admin/alumnos/guardar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->guardarAlumno();
    exit;
}

if ($uri === '/admin/alumnos/detalle') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->detalleAlumno();
    exit;
}

if ($uri === '/admin/alumnos/editar') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->editarAlumno();
    exit;
}

if ($uri === '/admin/alumnos/actualizar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->actualizarAlumno();
    exit;
}

// Vista especiales
if ($uri === '/admin/especiales/crear') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->crearEvento();
    exit;
}

if ($uri === '/admin/especiales/guardar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->guardarEvento();
    exit;
}

if ($uri === '/admin/especiales/inscribir' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->inscribirAlumnoEvento();
    exit;
}

if ($uri === '/admin/especiales/editar') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->editarEvento();
    exit;
}

if ($uri === '/admin/especiales/actualizar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->actualizarEvento();
    exit;
}

if ($uri === '/admin/especiales/eliminar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarEvento();
    exit;
}

// Esta ruta procesa la URL de eliminación de Grupos
if ($uri === '/admin/grupos/eliminar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AdminController.php';
    (new AdminController())->eliminarGrupo();
    exit;
}


// ─── Profesor ────────────────────────────────────────────────────────────────

if ($uri === '/profesor') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->showPanel();
    exit;
}

if ($uri === '/profesor/alumnos') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->alumnos();
    exit;
}

if ($uri === '/profesor/grupos') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->grupos();
    exit;
}

if ($uri === '/profesor/clases') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->clases();
    exit;
}

if ($uri === '/profesor/asistencia') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->asistencia();
    exit;
}

// ─── Invitado (alumno en estado posible) ─────────────────────────────────────

if ($uri === '/invitado') {
    require_once ROOT . '/app/controllers/InvitadoController.php';
    (new InvitadoController())->showPanel();
    exit;
}

if ($uri === '/invitado/calendario') {
    require_once ROOT . '/app/controllers/InvitadoController.php';
    (new InvitadoController())->showCalendario();
    exit;
}

if ($uri === '/invitado/reservar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/InvitadoController.php';
    (new InvitadoController())->reservarPrueba();
    exit;
}

if ($uri === '/invitado/cancelar' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/InvitadoController.php';
    (new InvitadoController())->cancelarPrueba();
    exit;
}

if ($uri === '/profesor/calendario') {
    require_once ROOT . '/app/controllers/ProfesorController.php';
    (new ProfesorController())->calendario();
    exit;
}

// ─── Alumno ──────────────────────────────────────────────────────────────────

if ($uri === '/alumno') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->showPanel();
    exit;
}

if ($uri === '/alumno/tokens') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->showTokens();
    exit;
}

if ($uri === '/alumno/recuperar') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->showRecuperar();
    exit;
}

if ($uri === '/alumno/calendario') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->showCalendario();
    exit;
}

if ($uri === '/alumno/avisar-ausencia' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->avisarAusencia();
    exit;
}

if ($uri === '/alumno/anular-ausencia' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->anularAusencia();
    exit;
}

if ($uri === '/alumno/anular-recuperacion' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->anularRecuperacion();
    exit;
}

if ($uri === '/alumno/recuperar-clase' && $metodo === 'POST') {
    require_once ROOT . '/app/controllers/AlumnoController.php';
    (new AlumnoController())->recuperarClase();
    exit;
}

// ─── 404 ─────────────────────────────────────────────────────────────────────

http_response_code(404);
echo '<h1>404 - Página no encontrada</h1>';
