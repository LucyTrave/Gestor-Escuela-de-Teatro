<?php $titulo = 'Editar Alumno'; $seccion = in_array($alumno['estado'], ['matriculado', 'baja'], true) ? 'matriculados' : 'posibles'; $origen = $_GET['origen'] ?? 'posibles'; $mensaje = ['tipo' => '', 'texto' => '']; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Editar alumno</h1>

<section class="profesor-seccion">
    <form action="<?= BASE_URL ?>/admin/alumnos/actualizar" method="post" class="profesor-form-grid profesor-form-grid-amplio">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
        <input type="text" name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" placeholder="Nombre" required>
        <input type="text" name="apellidos" value="<?= htmlspecialchars($alumno['apellidos']) ?>" placeholder="Apellidos" required>
        <input type="email" name="email" value="<?= htmlspecialchars($alumno['email'] ?? '') ?>" placeholder="Email">
        <input type="text" name="telefono" value="<?= htmlspecialchars($alumno['telefono'] ?? '') ?>" placeholder="Telefono">
        <input type="hidden" name="origen" value="<?= htmlspecialchars($origen) ?>">
        <?php if ($origen === 'posibles'): ?>
            <select name="estado">
                <option value="posible"<?= $alumno['estado'] === 'posible' ? ' selected' : '' ?>>Posible</option>
                <option value="matriculado"<?= $alumno['estado'] === 'matriculado' ? ' selected' : '' ?>>Matriculado</option>
                <option value="baja"<?= $alumno['estado'] === 'baja' ? ' selected' : '' ?>>Baja</option>
            </select>
        <?php endif; ?>
        <select name="nivel"><option value="">Nivel</option><option value="iniciacion"<?= $alumno['nivel'] === 'iniciacion' ? ' selected' : '' ?>>Iniciacion</option><option value="intermedio"<?= $alumno['nivel'] === 'intermedio' ? ' selected' : '' ?>>Intermedio</option><option value="avanzado"<?= $alumno['nivel'] === 'avanzado' ? ' selected' : '' ?>>Avanzado</option></select>
        <?php if ($origen === 'posibles'): ?>
            <select name="tipo_interes">
                <option value="">Tipo de interes</option>
                <option value="intensivo"<?= $alumno['tipo_interes'] === 'intensivo' ? ' selected' : '' ?>>Intensivo</option>
                <option value="ex_alumno"<?= $alumno['tipo_interes'] === 'ex_alumno' ? ' selected' : '' ?>>Ex alumno</option>
                <option value="sin_horario"<?= $alumno['tipo_interes'] === 'sin_horario' ? ' selected' : '' ?>>Sin horario</option>
                <option value="no_insistir"<?= $alumno['tipo_interes'] === 'no_insistir' ? ' selected' : '' ?>>No insistir</option>
            </select>
        <?php endif; ?>
        <?php if ($origen === 'posibles'): ?>
            <select name="clase_prueba">
                <option value="0"<?= (int)$alumno['clase_prueba'] === 0 ? ' selected' : '' ?>>Sin clase de prueba</option>
                <option value="1"<?= (int)$alumno['clase_prueba'] === 1 ? ' selected' : '' ?>>Con clase de prueba</option>
            </select>
        <?php endif; ?> 
        <?php if ($origen === 'posibles'): ?>   
            <div>
                <label>Fecha interés</label>
                <input type="date" name="fecha_interes"value="<?= htmlspecialchars($alumno['fecha_interes'] ?? '') ?>">
            </div>
        <?php endif; ?>    
        <div>
            <label>Primera clase</label>
            <input type="date" name="fecha_primera_clase"value="<?= htmlspecialchars($alumno['fecha_primera_clase'] ?? '') ?>">
        </div>
        <button type="submit" class="boton-principal boton-profesor">Guardar cambios</button>
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
