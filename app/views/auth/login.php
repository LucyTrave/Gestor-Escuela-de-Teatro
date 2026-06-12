<?php
// session_start() se llama por el controlador antes de cargar esta vista.
// Si el usuario YA está logueado, el controlador habrá redirigido.

$mensaje_error = '';
if (isset($_GET['error'])) {
    $mensaje_error = 'Email o contraseña incorrectos';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - Gestor Escuela de Teatro</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
</head>
<body class="pagina-login">

    <div class="tarjeta-login">
        <div class="franja-roja"></div>

        <div class="cabecera">
            <h1>GESTOR ESCUELA DE TEATRO</h1>
            <p class="subtitulo">CONTROL DE ALUMNOS, GRUPOS Y ACTIVIDADES</p>
        </div>

        <?php if ($mensaje_error !== ''): ?>
            <div class="mensaje-error">
                <?= $mensaje_error ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login" method="POST" class="formulario">
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

           <button type="submit" class="boton-principal">
            Acceder al sistema
            </button>
        </form>

        <p class="ayuda">
            Admin <strong>lucia@mail.com</strong>
            Profesor <strong>luis@mail.com</strong>
            Alumno <strong>juan@mail.com</strong>
            Invitado <strong>maria@mail.com</strong> / Contraseñas <strong>1234</strong>
        </p>

        <div class="franja-roja"></div>
    </div>

</body>
</html>
