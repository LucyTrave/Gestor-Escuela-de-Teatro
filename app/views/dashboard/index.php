<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Punto de Partida</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
</head>
<body class="pagina-dashboard">

    <header class="cabecera-dashboard">
        <h1>PUNTO DE PARTIDA</h1>
        <span class="subtitulo">ESCUELA DE TEATRO</span>
        <nav style="margin-left: auto; font-size: 0.9rem;">
            Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
            &nbsp;·&nbsp;
            <a href="<?= BASE_URL ?>/logout" style="color: #DC2626;">Cerrar sesión</a>
        </nav>
    </header>

    <main style="padding: 2rem;">
        <h2>Panel de control</h2>
        <p>Bienvenido al panel de <strong>Punto de Partida</strong>.</p>
        <p style="color: #666;">Usuario: <?= htmlspecialchars($_SESSION['usuario_email']) ?> · Rol: <?= htmlspecialchars($_SESSION['usuario_rol']) ?></p>
    </main>

</body>
</html>
