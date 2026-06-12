<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Admin | Gestor Escuela') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
</head>
<body class="pagina-dashboard">
    <header class="cabecera-dashboard">

      <div class="marca-con-logo">
   <img src="<?= BASE_URL ?>/public/img/mascaras-rojas.png" alt="Logo Gestor Escuela" class="logo-cabecera" style="width: 42px;; max:  height: 42px;px; object-fit:contain; margin-right:10px;"

    <div>
        <div class="marca">GESTOR ESCUELA</div>
        <div class="subtitulo">ADMINISTRACIÓN</div>
    </div>
    </div>

        <nav>
            <a class="enlace-nav<?= $seccion === 'dashboard' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/admin">Inicio</a>
            <a class="enlace-nav<?= $seccion === 'posibles' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/admin/posibles">Posibles</a>
            <a class="enlace-nav<?= $seccion === 'matriculados' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/admin/matriculados">Matriculados</a>
            <a class="enlace-nav<?= $seccion === 'grupos' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/admin/grupos">Grupos</a>
            <a class="enlace-nav<?= $seccion === 'especiales' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/admin/especiales">Especiales</a>
        </nav>
        <div class="profesor-usuario">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></span>
            <a class="enlace-cerrar" href="<?= BASE_URL ?>/logout">Cerrar sesion</a>
        </div>
    </header>
    <main class="contenido contenido-admin">
        <?php if (!empty($mensaje['texto'])): ?>
            <div class="aviso aviso-<?= htmlspecialchars($mensaje['tipo']) ?>">
                <?= htmlspecialchars($mensaje['texto']) ?>
            </div>
        <?php endif; ?>
