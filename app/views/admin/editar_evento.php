<?php $titulo = 'Editar Evento'; $seccion = 'especiales'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Editar evento</h1>

<section class="profesor-seccion">
    <form method="POST" action="<?= BASE_URL ?>/admin/especiales/actualizar" class="profesor-form-grid profesor-form-grid-amplio">
        <?= Csrf::field() ?>

        <input type="hidden" name="id" value="<?= (int)$evento['id'] ?>">

        <input type="text" name="nombre" value="<?= htmlspecialchars($evento['nombre']) ?>" required>

        <select name="tipo">
            <option value="intensivo"<?= $evento['tipo'] === 'intensivo' ? ' selected' : '' ?>>Intensivo</option>
            <option value="salida_teatro"<?= $evento['tipo'] === 'salida_teatro' ? ' selected' : '' ?>>Salida teatro</option>
        </select>

        <input type="date" name="fecha" value="<?= $evento['fecha'] ?>" required>
        <div class="campo-formulario">
            <label for="hora">Hora</label>
            <input type="time" id="hora" name="hora" value="<?= htmlspecialchars(substr($evento['hora'] ?? '', 0, 5)) ?>">
        </div>

        <input type="number" name="plazas_maximas" value="<?= (int)$evento['plazas_maximas'] ?>" min="1" required>

        <textarea name="descripcion"><?= htmlspecialchars($evento['descripcion']) ?></textarea>

        <button class="boton-principal">Guardar cambios</button>
    </form>
</section>

<div class="volver">
    <a href="<?= BASE_URL ?>/admin/especiales">← Volver</a>
</div>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>