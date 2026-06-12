<?php $titulo = 'Nuevo Alumno'; $seccion = ($origen === 'matriculados') ? 'matriculados' : 'posibles'; $mensaje = ['tipo' => '', 'texto' => '']; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Nuevo alumno</h1>

<?php if (!empty($errores)): ?>
    <div class="aviso aviso-warning">
        <?= htmlspecialchars(implode(' ', $errores)) ?>
    </div>
<?php endif; ?>

<section class="profesor-seccion">
    <form action="<?= BASE_URL ?>/admin/alumnos/guardar" method="post" class="profesor-form-grid profesor-form-grid-amplio">
        <?= Csrf::field() ?>
        <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>" placeholder="Nombre" required>
        <input type="text" name="apellidos" value="<?= htmlspecialchars($datos['apellidos'] ?? '') ?>" placeholder="Apellidos" required>
        <input type="email" name="email" value="<?= htmlspecialchars($datos['email'] ?? '') ?>" placeholder="Email">
        <input type="hidden" name="origen" value="<?= htmlspecialchars($origen ?? 'posibles') ?>">
        <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" placeholder="Telefono">
        <?php if ($origen === 'posibles'): ?>
            <select name="estado">
                <option value="posible"<?= ($datos['estado'] ?? '') === 'posible' ? ' selected' : '' ?>>Posible</option>
                <option value="matriculado"<?= ($datos['estado'] ?? '') === 'matriculado' ? ' selected' : '' ?>>Matriculado</option>
                <option value="baja"<?= ($datos['estado'] ?? '') === 'baja' ? ' selected' : '' ?>>Baja</option>
            </select>
        <?php endif; ?>
        <select name="nivel"><option value="">Nivel</option><option value="iniciacion">Iniciacion</option><option value="intermedio">Intermedio</option><option value="avanzado">Avanzado</option></select>
        <?php if ($origen === 'posibles'): ?>
            <select name="tipo_interes">
                <option value="">Tipo de interes</option>
                <option value="intensivo">Intensivo</option>
                <option value="ex_alumno">Ex alumno</option>
                <option value="sin_horario">Sin horario</option>
                <option value="no_insistir">No insistir</option>
            </select>
        <?php endif; ?>
        <?php if ($origen === 'posibles'): ?>
            <select name="clase_prueba">
                <option value="0">Sin clase de prueba</option>
                <option value="1">Con clase de prueba</option>
            </select>
        <?php endif; ?>
        <?php if ($origen === 'posibles'): ?>   
            <div>
                <label>Fecha interés</label>
                <input type="date" name="fecha_interes"value="<?= htmlspecialchars($datos['fecha_interes'] ?? '') ?>">
            </div>
        <?php endif; ?>
            <div>
                <label>Primera clase</label>
                <input type="date" name="fecha_primera_clase"value="<?= htmlspecialchars($datos['fecha_primera_clase'] ?? '') ?>">
            </div>
        <button type="submit" class="boton-principal boton-profesor">Crear alumno</button>
    </form>
</section>

<div class="volver">
    <?php if ($origen === 'matriculados'): ?>
        <a href="<?= BASE_URL ?>/admin/matriculados">← Volver a matriculados</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/admin/posibles">← Volver a posibles</a>
    <?php endif; ?>
</div>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
