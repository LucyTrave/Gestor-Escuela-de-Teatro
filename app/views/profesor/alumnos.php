<?php
// =============================================================================
// Vista: Listado de alumnos del profesor (página autónoma).
//
// Variables disponibles desde ProfesorController::alumnos():
//   $alumnos, $gruposDisponibles, $editarId, $observacionesPorAlumno,
//   $mensaje, $busqueda, $filtroEstado, $csrfToken
// =============================================================================
$vista = 'alumnos';
require __DIR__ . '/_header.php';
$esAdminVista = (($_SESSION['usuario_rol'] ?? '') === 'admin');
$usuarioId    = $_SESSION['usuario_id'] ?? '';
$busqueda     = $busqueda     ?? '';
$filtroEstado = $filtroEstado ?? '';
$csrfToken    = $csrfToken    ?? '';

// -- Agrupar alumnos por nombre de grupo (Sin grupo al final) --
$alumnosPorGrupo = [];
foreach ($alumnos as $a) {
    $clave = !empty($a['grupo_nombre']) ? $a['grupo_nombre'] : 'Sin grupo';
    $alumnosPorGrupo[$clave][] = $a;
}
ksort($alumnosPorGrupo, SORT_NATURAL | SORT_FLAG_CASE);
if (isset($alumnosPorGrupo['Sin grupo'])) {
    $sin = $alumnosPorGrupo['Sin grupo'];
    unset($alumnosPorGrupo['Sin grupo']);
    $alumnosPorGrupo['Sin grupo'] = $sin;
}
$totalColumnas = $esAdminVista ? 8 : 7;

// Plantillas de observaciones frecuentes para los alumnos.
$plantillasObsAlumno = [
    'Llegó tarde',
    'Buen trabajo en la sesión',
    'Necesita refuerzo',
    'Falta sin justificar',
    'Hablar con la familia',
];
?>

            <div class="etiqueta">ÁREA PROFESORADO</div>
            <h1>Alumnos</h1>

            <?php if (!$esAdminVista): ?>
                <p class="aviso aviso-info">Los alumnos los gestiona el administrador. Como profesor puedes consultarlos y añadir observaciones.</p>
            <?php endif; ?>

            <section class="profesor-seccion">
                <form method="get" action="<?= BASE_URL ?>/profesor/alumnos" class="barra-filtros" role="search">
                    <label class="filtro-busqueda">
                        <span class="visualmente-oculto">Buscar alumno</span>
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="search" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre, apellidos o email…">
                    </label>
                    <label class="filtro-select">
                        <span class="visualmente-oculto">Filtrar por estado</span>
                        <select name="estado" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="matriculado"<?= $filtroEstado === 'matriculado' ? ' selected' : '' ?>>Matriculado</option>
                            <option value="posible"<?= $filtroEstado === 'posible' ? ' selected' : '' ?>>Posible</option>
                            <option value="baja"<?= $filtroEstado === 'baja' ? ' selected' : '' ?>>Baja</option>
                        </select>
                    </label>
                    <label class="filtro-check" title="Mostrar solo alumnos con token disponible para recuperar">
                        <input type="checkbox" name="solo_tokens" value="1" onchange="this.form.submit()"<?= !empty($_GET['solo_tokens']) ? ' checked' : '' ?>>
                        <span aria-hidden="true">&#127915;</span> Solo con token
                    </label>
                    <button type="submit" class="badge badge-accion">Filtrar</button>
                    <?php if ($busqueda !== '' || $filtroEstado !== '' || !empty($_GET['solo_tokens'])): ?>
                        <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/alumnos">Limpiar</a>
                    <?php endif; ?>
                </form>

                <div class="resumen-filtros">
                    <strong><?= count($alumnos) ?></strong>
                    alumno<?= count($alumnos) === 1 ? '' : 's' ?>
                    <?= $busqueda !== '' ? ' coincidentes con «' . htmlspecialchars($busqueda) . '»' : '' ?>
                    <?= $filtroEstado !== '' ? ' en estado ' . htmlspecialchars($filtroEstado) : '' ?>
                </div>
            </section>

            <section class="profesor-seccion">
                <div class="titulo-seccion">Listado</div>
                <div class="tabla-scroll">
                    <table class="tabla-profesor">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Email</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Grupo</th>
                                <th scope="col">Observaciones</th>
                                <?php if ($esAdminVista): ?><th scope="col">Acciones</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alumnos)): ?>
                                <tr><td colspan="<?= $totalColumnas ?>" class="vacio-tabla">
                                    <div class="estado-vacio">
                                        <i class="fas fa-user-slash" aria-hidden="true"></i>
                                        <p>No hay alumnos que coincidan con los filtros.</p>
                                        <?php if ($busqueda !== '' || $filtroEstado !== ''): ?>
                                            <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/alumnos">Quitar filtros</a>
                                        <?php endif; ?>
                                    </div>
                                </td></tr>
                            <?php endif; ?>
                            <?php foreach ($alumnosPorGrupo as $nombreGrupo => $alumnosDelGrupo): ?>
                                <?php $claveGrupo = 'g-' . md5($nombreGrupo); ?>
                                <tr class="fila-grupo-separador grupo-plegado" data-toggle-grupo="<?= htmlspecialchars($claveGrupo) ?>" tabindex="0" role="button" aria-expanded="false" title="Pulsa para desplegar">
                                    <td colspan="<?= $totalColumnas ?>">
                                        <span class="grupo-tabla-chevron" aria-hidden="true">&#9662;</span>
                                        <span class="grupo-tabla-icono" aria-hidden="true">&#128101;</span>
                                        <span class="grupo-tabla-nombre"><?= htmlspecialchars($nombreGrupo) ?></span>
                                        <span class="grupo-tabla-conteo"><?= count($alumnosDelGrupo) ?> alumno<?= count($alumnosDelGrupo) === 1 ? '' : 's' ?></span>
                                    </td>
                                </tr>
                            <?php foreach ($alumnosDelGrupo as $alumno): ?>
                                <?php $editando = $esAdminVista && $editarId === (int)$alumno['id']; ?>
                                <tr data-grupo-clave="<?= htmlspecialchars($claveGrupo) ?>" style="display: none;">
                                    <?php if ($editando): ?>
                                        <?php $formId = 'editar-alumno-' . (int)$alumno['id']; ?>
                                        <td><?= (int)$alumno['id'] ?>
                                            <form id="<?= $formId ?>" method="post" action="<?= BASE_URL ?>/profesor/alumnos">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="accion" value="actualizar">
                                                <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                            </form>
                                        </td>
                                        <td class="celda-doble">
                                            <input form="<?= $formId ?>" type="text" name="nombre"    value="<?= htmlspecialchars($alumno['nombre']) ?>" required aria-label="Nombre">
                                            <input form="<?= $formId ?>" type="text" name="apellidos" value="<?= htmlspecialchars($alumno['apellidos']) ?>" required aria-label="Apellidos">
                                        </td>
                                        <td><input form="<?= $formId ?>" type="email" name="email"    value="<?= htmlspecialchars($alumno['email'] ?? '') ?>"  aria-label="Email"></td>
                                        <td><input form="<?= $formId ?>" type="text"  name="telefono" value="<?= htmlspecialchars($alumno['telefono'] ?? '') ?>" aria-label="Teléfono"></td>
                                        <td>
                                            <select form="<?= $formId ?>" name="estado" aria-label="Estado">
                                                <option value="posible"<?= $alumno['estado'] === 'posible' ? ' selected' : '' ?>>Posible</option>
                                                <option value="matriculado"<?= $alumno['estado'] === 'matriculado' ? ' selected' : '' ?>>Matriculado</option>
                                                <option value="baja"<?= $alumno['estado'] === 'baja' ? ' selected' : '' ?>>Baja</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select form="<?= $formId ?>" name="grupo_id" aria-label="Grupo">
                                                <option value="0">Sin grupo</option>
                                                <?php foreach ($gruposDisponibles as $grupo): ?>
                                                    <option value="<?= (int)$grupo['id'] ?>"<?= (int)($alumno['grupo_id'] ?? 0) === (int)$grupo['id'] ? ' selected' : '' ?>><?= htmlspecialchars($grupo['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="celda-observaciones"><span class="tabla-subdato"><?= count($observacionesPorAlumno[(int)$alumno['id']] ?? []) ?> observ.</span></td>
                                        <td class="acciones-profesor">
                                            <button form="<?= $formId ?>" type="submit" class="badge badge-accion">Guardar</button>
                                            <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/alumnos">Cancelar</a>
                                        </td>
                                    <?php else: ?>
                                        <td><?= (int)$alumno['id'] ?></td>
                                        <td>
                                            <?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>
                                            <?php $tokAlumno = (int)($alumno['tokens_disponibles'] ?? 0); ?>
                                            <?php if ($tokAlumno > 0): ?>
                                                <span class="badge-token-alumno" title="<?= $tokAlumno ?> token<?= $tokAlumno === 1 ? '' : 's' ?> disponible<?= $tokAlumno === 1 ? '' : 's' ?> para recuperar clase">
                                                    <span aria-hidden="true">&#127915;</span> <?= $tokAlumno ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($alumno['email'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($alumno['telefono'] ?: '-') ?></td>
                                        <td><span class="badge-estado badge-estado-<?= htmlspecialchars($alumno['estado']) ?>"><?= htmlspecialchars(ucfirst($alumno['estado'])) ?></span></td>
                                        <td><?= htmlspecialchars($alumno['grupo_nombre'] ?: 'Sin grupo') ?></td>
                                        <td class="celda-observaciones">
                                            <?php $obsAlumno = $observacionesPorAlumno[(int)$alumno['id']] ?? []; ?>
                                            <?php if (empty($obsAlumno)): ?>
                                                <p class="obs-vacio">Sin observaciones</p>
                                            <?php else: ?>
                                                <ul class="obs-lista obs-lista-visible">
                                                    <?php foreach ($obsAlumno as $obs): ?>
                                                        <li class="obs-item">
                                                            <div class="obs-meta"><span aria-hidden="true">&#128197;</span> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($obs['fecha_creacion']))) ?></div>
                                                            <div class="obs-texto"><?= nl2br(htmlspecialchars($obs['texto'])) ?></div>
                                                            <?php if ($esAdminVista || $obs['profesor_id'] === $usuarioId): ?>
                                                                <form method="post" action="<?= BASE_URL ?>/profesor/alumnos" class="obs-eliminar"
                                                                      data-swal-confirm="La observación se eliminará permanentemente."
                                                                      data-swal-title="¿Eliminar esta observación?"
                                                                      data-swal-ok="Sí, eliminar"
                                                                      data-swal-icon="question"
                                                                      data-swal-danger>
                                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                    <input type="hidden" name="accion" value="eliminar_observacion">
                                                                    <input type="hidden" name="observacion_id" value="<?= (int)$obs['id'] ?>">
                                                                    <button type="submit" class="badge badge-anular" title="Eliminar" aria-label="Eliminar observación">
                                                                        <span aria-hidden="true">&#128465;</span>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>

                                            <details class="obs-anadir">
                                                <summary class="obs-boton-anadir"><span aria-hidden="true">&#43;</span> Añadir</summary>
                                                <form method="post" action="<?= BASE_URL ?>/profesor/alumnos" class="form-observaciones" data-form-obs="alumno-<?= (int)$alumno['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="accion" value="observaciones">
                                                    <input type="hidden" name="destino_id" value="<?= (int)$alumno['id'] ?>">
                                                    <textarea name="texto" rows="2" required placeholder="Escribe tu observación…" aria-label="Texto de la observación"></textarea>
                                                    <div class="plantillas-obs" role="group" aria-label="Plantillas rápidas">
                                                        <?php foreach ($plantillasObsAlumno as $plantilla): ?>
                                                            <button type="button" class="badge badge-plantilla" data-plantilla="<?= htmlspecialchars($plantilla) ?>"><?= htmlspecialchars($plantilla) ?></button>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="submit" class="badge badge-accion"><span aria-hidden="true">&#128190;</span> Guardar</button>
                                                </form>
                                            </details>
                                        </td>
                                        <?php if ($esAdminVista): ?>
                                            <td class="acciones-profesor">
                                                <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/alumnos?editar=<?= (int)$alumno['id'] ?>" title="Editar"><span aria-hidden="true">&#9998;</span> Editar</a>
                                                <?php $gruposRepetibles = array_filter($gruposDisponibles, fn($g) => (int)$g['id'] !== (int)($alumno['grupo_id'] ?? 0)); ?>
                                                <?php if (!empty($gruposRepetibles)): ?>
                                                    <form method="post" action="<?= BASE_URL ?>/profesor/alumnos" class="form-repetir"
                                                          data-swal-confirm="El alumno quedará inscrito también en este grupo."
                                                          data-swal-title="¿Añadir alumno al grupo seleccionado?"
                                                          data-swal-ok="Sí, añadir"
                                                          data-swal-icon="question">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="accion" value="repetir">
                                                        <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                                        <select name="grupo_id" required aria-label="Repetir en grupo">
                                                            <option value=""><span aria-hidden="true">&#128257;</span> Repetir en…</option>
                                                            <?php foreach ($gruposRepetibles as $g): ?>
                                                                <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="badge badge-accion" title="Añadir al grupo seleccionado" aria-label="Confirmar">&#10003;</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="post" action="<?= BASE_URL ?>/profesor/alumnos"
                                                      data-swal-confirm="Esta acción no se puede deshacer."
                                                      data-swal-title="¿Eliminar este alumno?"
                                                      data-swal-ok="Sí, eliminar"
                                                      data-swal-icon="warning"
                                                      data-swal-danger>
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                                    <button type="submit" class="badge badge-anular" title="Eliminar" aria-label="Eliminar alumno"><span aria-hidden="true">&#128465;</span></button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

<script>
// Plegar / desplegar grupos del listado de alumnos.
(function () {
    var separadores = document.querySelectorAll('.fila-grupo-separador[data-toggle-grupo]');
    separadores.forEach(function (sep) {
        function alternar() {
            var clave = sep.getAttribute('data-toggle-grupo');
            var plegado = sep.classList.toggle('grupo-plegado');
            sep.setAttribute('aria-expanded', plegado ? 'false' : 'true');
            sep.setAttribute('title', plegado ? 'Pulsa para desplegar' : 'Pulsa para plegar');
            document.querySelectorAll('tr[data-grupo-clave="' + clave + '"]').forEach(function (fila) {
                fila.style.display = plegado ? 'none' : '';
            });
        }
        sep.addEventListener('click', alternar);
        sep.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); alternar(); }
        });
    });
})();

// Plantillas rápidas de observaciones: insertan el texto en el textarea más cercano.
(function () {
    document.querySelectorAll('.badge-plantilla').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = btn.closest('form');
            if (!form) return;
            var area = form.querySelector('textarea');
            if (!area) return;
            var texto = btn.getAttribute('data-plantilla') || '';
            area.value = area.value && area.value.trim() !== ''
                ? area.value.replace(/\s*$/, '') + ' · ' + texto
                : texto;
            area.focus();
        });
    });
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
