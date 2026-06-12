<?php $titulo = 'Detalle Alumno'; $seccion = in_array($alumno['estado'], ['matriculado', 'baja'], true) ? 'matriculados' : 'posibles'; $origen = $_GET['origen'] ?? 'posibles'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Detalle del alumno</h1>

<section class="profesor-seccion">
    <div class="admin-topbar">
        <div>
            <div class="titulo-seccion"><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></div>
            <p><?= htmlspecialchars($alumno['estado']) ?><?= !empty($alumno['grupo_nombre']) ? ' · ' . htmlspecialchars($alumno['grupo_nombre']) : '' ?></p>
        </div>
        <a class="badge badge-accion" href="<?= BASE_URL ?>/admin/alumnos/editar?id=<?= (int)$alumno['id'] ?>&origen=<?= htmlspecialchars($origen) ?>">Editar</a>
    </div>

    <div class="admin-detail-grid">
        <div><span>Email</span><p><?= htmlspecialchars($alumno['email'] ?: 'No disponible') ?></p></div>
        <div><span>Telefono</span><p><?= htmlspecialchars($alumno['telefono'] ?: 'No disponible') ?></p></div>
        <div><span>Nivel</span><p><?= htmlspecialchars($alumno['nivel'] ?: 'No definido') ?></p></div>
        <div><span>Tipo de interes</span><p><?= htmlspecialchars($alumno['tipo_interes'] ?: 'No definido') ?></p></div>
        <div><span>Fecha interes</span><p><?= htmlspecialchars($alumno['fecha_interes'] ?: '-') ?></p></div>
        <div><span>Primera clase</span><p><?= htmlspecialchars($alumno['fecha_primera_clase'] ?: '-') ?></p></div>
        <div><span>Clase de prueba</span><p><?= (int)$alumno['clase_prueba'] === 1 ? 'Si' : 'No' ?></p></div>
        <div><span>Fecha registro</span><p><?= htmlspecialchars($alumno['fecha_registro']) ?></p></div>
    </div>
</section>

<div class="volver">
    <?php if ($origen === 'matriculados'): ?>
        <a href="<?= BASE_URL ?>/admin/matriculados">← Volver a matriculados</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/admin/posibles">← Volver a posibles</a>
    <?php endif; ?>
</div>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
