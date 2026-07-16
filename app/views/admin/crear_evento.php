<?php $titulo = 'Nuevo Evento'; $seccion = 'especiales'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Nuevo evento especial</h1>

<section class="profesor-seccion">
    <form method="POST" action="<?= BASE_URL ?>/admin/especiales/guardar" class="profesor-form-grid profesor-form-grid-amplio">
        <?= Csrf::field() ?>

        <input type="text" name="nombre" placeholder="Nombre del evento" required>

        <select name="tipo" required>
            <option value="">Tipo</option>
            <option value="intensivo">Intensivo</option>
            <option value="salida_teatro">Salida teatro</option>
        </select>

        <select name="profesor_id" required>
    <option value="">Profesor</option>

    <?php foreach ($profesores as $profesor): ?>
        <option value="<?= (int)$profesor['usuario_id'] ?>">
            <?= htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellidos']) ?>
        </option>
    <?php endforeach; ?>
</select>

        <input type="date" name="fecha" required>
        <div class="campo-formulario">
            <label for="hora">Hora</label>
            <input type="time" id="hora" name="hora">
        </div>

        <input type="number" name="plazas_maximas" placeholder="Aforo máximo" min="1" required>

        <textarea name="descripcion" placeholder="Descripción"></textarea>

        <button type="submit" class="boton-principal boton-profesor">
            Crear evento
        </button>
    </form>
</section>

<div class="volver">
    <a href="<?= BASE_URL ?>/admin/especiales">← Volver</a>
</div>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>