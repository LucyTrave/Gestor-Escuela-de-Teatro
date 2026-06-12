<?php $titulo = 'Alumnos Matriculados'; $seccion = 'matriculados'; $mensaje = ['tipo' => '', 'texto' => '']; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Alumnos matriculados</h1>

<?php if (isset($_GET['ok'])): ?>
    <div class="aviso aviso-exito">
        <?php if ($_GET['ok'] === 'creado'): ?>
            Alumno matriculado correctamente.
        <?php elseif ($_GET['ok'] === 'actualizado'): ?>
            Alumno actualizado correctamente.
        <?php elseif ($_GET['ok'] === 'eliminado'): ?>
            Alumno eliminado correctamente.
        <?php endif; ?>
    </div>
<?php endif; ?>

<section class="tarjetas tarjetas-profesor">
    <article class="tarjeta tarjeta-negra"><div class="tarjeta-etiqueta">Total alumnos</div><div class="tarjeta-valor-grande"><?= $metricas['total_alumnos'] ?></div></article>
    <article class="tarjeta tarjeta-roja"><div class="tarjeta-etiqueta">Con grupo asignado</div><div class="tarjeta-valor-grande"><?= $metricas['con_grupo'] ?></div></article>
    <article class="tarjeta tarjeta-negra"><div class="tarjeta-etiqueta">Sin grupo asignado</div><div class="tarjeta-valor-grande"><?= $metricas['sin_grupo'] ?></div></article>
</section>


<section class="profesor-seccion">
    <div class="admin-topbar">
        <div class="titulo-seccion">Listado de alumnos activos</div>
        <div class="acciones-topbar">
            <a class="boton-icono" href="<?= BASE_URL ?>/admin/alumnos/crear?origen=matriculados" title="Nuevo">&#43;</a>
            <a href="#" onclick="toggleFiltros(); return false;" class="boton-icono" title="Filtrar">&#9776;</a>
        </div>
    </div>

<?php
$filtrosActivos =
    !empty($_GET['nombre']) ||
    !empty($_GET['telefono']) ||
    !empty($_GET['grupo_id']) ||
    !empty($_GET['fecha_registro']) ||
    !empty($_GET['fecha_primera_clase']);
?>  
    
<div id="panel-filtros" style="display: <?= isset($_GET['mostrar_filtros']) || $filtrosActivos ? 'block' : 'none'  ?>;">

    <form method="GET" action="<?= BASE_URL ?>/admin/matriculados">
        <div class="profesor-form-grid">
            <input type="text" name="nombre" placeholder="Nombre o apellidos"
                value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">

            <input type="text" name="telefono" placeholder="Teléfono"
                value="<?= htmlspecialchars($_GET['telefono'] ?? '') ?>">

            <select name="nivel">
                <option value="">Nivel</option>
                <option value="iniciacion">Iniciación</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
            </select>

            <select name="grupo_id">
                <option value="">Grupo</option>
                <option value="sin_grupo"
                    <?= (($_GET['grupo_id'] ?? '') === 'sin_grupo') ? 'selected' : '' ?>>Sin grupo asignado
                </option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= $grupo['id'] ?>"
                        <?= (($_GET['grupo_id'] ?? '') == $grupo['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grupo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="filtros-extra"style="display: <?= (!empty($_GET['fecha_registro']) || !empty($_GET['fecha_primera_clase'])) ? 'grid' : 'none' ?>;"class="filtros-extra-grid">
            <div>
                <label>Fecha registro</label>
                <input type="date" name="fecha_registro"
                    value="<?= htmlspecialchars($_GET['fecha_registro'] ?? '') ?>">
            </div>

            <div>
                <label>Primera clase</label>
                <input type="date" name="fecha_primera_clase"
                    value="<?= htmlspecialchars($_GET['fecha_primera_clase'] ?? '') ?>">
            </div>
        </div>

        <div class="acciones-filtros">
            <button class="boton-principal boton-auto">Filtrar</button>
            <a href="#"onclick="toggleMasFiltros(); return false;"class="badge badge-limpiar">Mostrar más filtros</a>
            <a href="<?= BASE_URL ?>/admin/matriculados?mostrar_filtros=1"class="badge badge-limpiar">Limpiar</a>
        </div>

    </form>

</div>

    <div class="tabla-scroll">
        <table class="tabla-profesor">
            <thead><tr><th>Alumno</th><th>Grupo</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($alumnos)): ?><tr><td colspan="3" class="vacio-tabla">No hay alumnos matriculados.</td></tr><?php endif; ?>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></td>
                        <td><?= htmlspecialchars($alumno['grupo_nombre'] ?: 'Sin grupo asignado') ?></td>
                        <td class="acciones-profesor">
                            <a class="badge badge-accion" href="<?= BASE_URL ?>/admin/alumnos/detalle?id=<?= (int)$alumno['id'] ?>&origen=matriculados">Ver detalle</a>
                            <a class="badge badge-anular" title="Editar alumno"href="<?= BASE_URL ?>/admin/alumnos/editar?id=<?= (int)$alumno['id'] ?>&origen=matriculados">&#9998;</a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/alumnos/eliminar">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                <input type="hidden" name="origen" value="matriculados">
                                <button type="submit"class="badge badge-eliminar"onclick="return confirm('¿Eliminar este alumno?')">🗑</button>
                            </form>
                        </td>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function toggleFiltros() {
    const panel = document.getElementById('panel-filtros');
    if (!panel) return;
    panel.style.display = (panel.style.display === 'none') ? 'block' : 'none';
}

function toggleMasFiltros() {
    const panel = document.getElementById('filtros-extra');
    if (!panel) return;
    panel.style.display = (panel.style.display === 'none') ? 'grid' : 'none';
}
</script>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
