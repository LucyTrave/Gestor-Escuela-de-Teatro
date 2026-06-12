<?php ?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar grupo</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">

<style>
/* Estilos locales solo para esta pantalla */
.badge-accion {
    background: #c1121f !important;
    color: #ffffff !important;
}

.badge-accion:hover {
    background: #9f0f1a !important;
}
</style>

</head>

<body class="pagina-dashboard">

<div style="
max-width: 760px;
margin: 40px auto;
background: #ffffff;
border: 1px solid #d9d9d9;
border-radius: 8px;
padding: 22px;
box-shadow: 0 8px 24px rgba(0,0,0,.08);
">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px;">
        <div>
            <h2 style="margin:0 0 6px 0;">Editar grupo</h2>
            <p style="margin:0; color:#666;">
                Modifica los datos básicos del grupo.
            </p>
        </div>

        <a href="<?= BASE_URL ?>/admin/grupos"
           style="text-decoration:none; color:#c62828; font-weight:bold; font-size:18px;">
           ✕
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/grupos/actualizar">
        <?= Csrf::field() ?>

        <input type="hidden" name="id" value="<?= htmlspecialchars($grupo['id'] ?? ($_GET['id'] ?? '')) ?>">

        <label style="display:block; margin-bottom:6px; font-weight:bold;">Nombre del grupo</label>
        <input
            type="text"
            name="nombre"
            value="<?= htmlspecialchars($grupo['nombre'] ?? '') ?>"
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:16px;"
        >

        <label style="display:block; margin-bottom:6px; font-weight:bold;">Nivel</label>
        <select
            name="nivel"
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:22px;"
        >
            <option value="Iniciación" <?= ($grupo['nivel'] ?? '') === 'Iniciación' ? 'selected' : '' ?>>
                Iniciación
            </option>

            <option value="Intermedio" <?= ($grupo['nivel'] ?? '') === 'Intermedio' ? 'selected' : '' ?>>
                Intermedio
            </option>

            <option value="Avanzado" <?= ($grupo['nivel'] ?? '') === 'Avanzado' ? 'selected' : '' ?>>
                Avanzado
            </option>
        </select>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <a href="<?= BASE_URL ?>/admin/grupos" class="badge badge-anular">Cancelar</a>
            <button type="submit" class="badge badge-accion">Guardar cambios</button>
        </div>

    </form>

</div>

</body>
</html>