<?php

class AuthController {

    public function showLogin() {
        session_start();
        if (isset($_SESSION['usuario_id'])) {
            $ruta = '/admin';
            if ($_SESSION['usuario_rol'] === 'alumno') {
                $ruta = (isset($_SESSION['alumno_estado']) && $_SESSION['alumno_estado'] === 'posible') ? '/invitado' : '/alumno';
            } elseif ($_SESSION['usuario_rol'] === 'profesor') {
                $ruta = '/profesor';
            }
            header('Location: ' . BASE_URL . $ruta);
            exit;
        }
        require ROOT . '/app/views/auth/login.php';
    }

    public function processLogin() {
        session_start();
        global $conexion;

        $email      = trim($_POST['email']    ?? '');
        $contrasena = trim($_POST['password'] ?? '');

        if ($email === '' || $contrasena === '') {
            header('Location: ' . BASE_URL . '/login?error=1');
            exit;
        }

        // Buscar en tabla usuario (id = DNI)
        $q = $conexion->prepare("SELECT id, email, password_hash, rol FROM usuario WHERE email = ? LIMIT 1");
        $q->bind_param('s', $email);
        $q->execute();
        $usuario = $q->get_result()->fetch_assoc();

        if (!$usuario || !password_verify($contrasena, $usuario['password_hash'])) {
            header('Location: ' . BASE_URL . '/login?error=1');
            exit;
        }

        // Obtener nombre/apellidos de la tabla especifica del rol
        switch ($usuario['rol']) {
            case 'alumno':
                $q2 = $conexion->prepare("SELECT id, nombre, apellidos FROM alumno WHERE usuario_id = ? LIMIT 1");
                break;
            case 'profesor':
                $q2 = $conexion->prepare("SELECT usuario_id AS id, nombre, apellidos FROM profesor WHERE usuario_id = ? LIMIT 1");
                break;
            default: // admin
                $q2 = $conexion->prepare("SELECT usuario_id AS id, nombre, apellidos FROM admin WHERE usuario_id = ? LIMIT 1");
                break;
        }
        $q2->bind_param('s', $usuario['id']);
        $q2->execute();
        $perfil = $q2->get_result()->fetch_assoc();

        $_SESSION['usuario_id']        = $usuario['id'];
        $_SESSION['usuario_nombre']    = $perfil['nombre']    ?? 'Usuario';
        $_SESSION['usuario_apellidos'] = $perfil['apellidos'] ?? '';
        $_SESSION['usuario_email']     = $usuario['email'];
        $_SESSION['usuario_rol']       = $usuario['rol'];

        // Para alumnos guardamos tambien el id INT de la tabla alumno
        if ($usuario['rol'] === 'alumno') {
            $_SESSION['alumno_id'] = (int)($perfil['id'] ?? 0);

            // Consultar estado del alumno para distinguir matriculado de posible
            $q3 = $conexion->prepare("SELECT estado FROM alumno WHERE id = ? LIMIT 1");
            $q3->bind_param('i', $_SESSION['alumno_id']);
            $q3->execute();
            $estado_data = $q3->get_result()->fetch_assoc();
            $_SESSION['alumno_estado'] = $estado_data['estado'] ?? 'matriculado';
        }

        $ruta = '/admin';
        if ($usuario['rol'] === 'alumno') {
            $ruta = ($_SESSION['alumno_estado'] === 'posible') ? '/invitado' : '/alumno';
        } elseif ($usuario['rol'] === 'profesor') {
            $ruta = '/profesor';
        }
        header('Location: ' . BASE_URL . $ruta);
        exit;
    }

    public function logout() {
        session_start();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function showDashboard() {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        // Los alumnos no tienen acceso al dashboard de admin/profesor
        if ($_SESSION['usuario_rol'] === 'alumno') {
            header('Location: ' . BASE_URL . '/alumno');
            exit;
        }
        if ($_SESSION['usuario_rol'] === 'profesor') {
            header('Location: ' . BASE_URL . '/profesor');
            exit;
        }
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }
}
