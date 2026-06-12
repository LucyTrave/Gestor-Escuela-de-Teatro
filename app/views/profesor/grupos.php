<?php
// =============================================================================
// Vista: Listado de grupos del profesor (página autónoma).
//
// Variables disponibles desde ProfesorController::grupos():
//   $grupos, $salas, $editarId, $observacionesPorGrupo, $mensaje,
//   $busqueda, $filtroTipo, $filtroEstado, $csrfToken
// =============================================================================
$vista = 'grupos';
require __DIR__ . '/_header.php';
$esAdminVista  = (($_SESSION['usuario_rol'] ?? '') === 'admin');
$usuarioId     = $_SESSION['usuario_id'] ?? '';
$busqueda      = $busqueda      ?? '';
$filtroTipo    = $filtroTipo    ?? '';
$filtroEstado  = $filtroEstado  ?? '';
$csrfToken     = $csrfToken     ?? '';

$plantillasObsGrupo = [
    'Grupo en buena progresión',
    'Falta motivación del grupo',
    'Atención: clima de tensión',
    'Buena dinámica esta semana',
    'Hablar con coordinación',
];
?>

            <div class="etiqueta">ÁREA PROFESORADO</div>
            <h1>Grupos</h1>

            <?php if (!$esAdminVista): ?>
                <p class="aviso aviso-info">Los grupos los crea y asigna el administrador. Como profesor puedes consultar tus grupos y añadir observaciones.</p>
            <?php endif; ?>

            <section class="profesor-seccion">
                <form method="get" action="<?= BASE_URL ?>/profesor/grupos" class="barra-filtros" role="search">
                    <label class="filtro-busqueda">
                        <span class="visualmente-oculto">Buscar grupo</span>
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="search" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre o curso…">
                    </label>
                    <label class="filtro-select">
                        <span class="visualmente-oculto">Filtrar por tipo</span>
                        <select name="tipo" onchange="this.form.submit()">
                            <option value="">Todos los tipos</option>
                            <option value="teatro"<?= $filtroTipo === 'teatro' ? ' selected' : '' ?>>Teatro</option>
                            <option value="improvisacion"<?= $filtroTipo === 'improvisacion' ? ' selected' : '' ?>>Improvisación</option>
                            <option value="actuacion"<?= $filtroTipo === 'actuacion' ? ' selected' : '' ?>>Actuación</option>
                            <option value="danza"<?= $filtroTipo === 'danza' ? ' selected' : '' ?>>Danza</option>
                            <option value="canto"<?= $filtroTipo === 'canto' ? ' selected' : '' ?>>Canto</option>
                        </select>
                    </label>
                    <label class="filtro-select">
                        <span class="visualmente-oculto">Filtrar por estado</span>
                        <select name="activo" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="1"<?= $filtroEstado === '1' ? ' selected' : '' ?>>Solo activos</option>
                            <option value="0"<?= $filtroEstado === '0' ? ' selected' : '' ?>>Solo baja</option>
                        </select>
                    </label>
                    <button type="submit" class="badge badge-accion">Filtrar</button>
                    <?php if ($busqueda !== '' || $filtroTipo !== '' || $filtroEstado !== ''): ?>
                        <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/grupos">Limpiar</a>
                    <?php endif; ?>
                </form>

                <?php
                    $tiposLegibles = [
                        'teatro' => 'Teatro', 'improvisacion' => 'Improvisación',
                        'actuacion' => 'Actuación', 'danza' => 'Danza', 'canto' => 'Canto',
                    ];
                    $etiquetaEstadoGrupo = ($filtroEstado === '1') ? 'activos'
                                         : (($filtroEstado === '0') ? 'en baja' : '');
                ?>
                <div class="resumen-filtros">
                    <strong><?= count($grupos) ?></strong>
                    grupo<?= count($grupos) === 1 ? '' : 's' ?>
                    <?= $busqueda !== '' ? ' coincidentes con «' . htmlspecialchars($busqueda) . '»' : '' ?>
                    <?= $filtroTipo !== '' ? ' del tipo ' . htmlspecialchars($tiposLegibles[$filtroTipo] ?? $filtroTipo) : '' ?>
                    <?= $etiquetaEstadoGrupo !== '' ? ' ' . $etiquetaEstadoGrupo : '' ?>
                </div>
            </section>

            <section class="profesor-seccion">
                <div class="titulo-seccion">Listado</div>
                <div class="tabla-scroll">
                    <table class="tabla-profesor">
                        <thead>
                            <tr>
                                <th scope="col">Grupo</th>
                                <th scope="col">Horario</th>
                                <th scope="col">Nivel</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Sala</th>
                                <th scope="col">Aforo</th>
                                <th scope="col">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grupos)): ?>
                                <tr><td colspan="7" class="vacio-tabla">
                                    <div class="estado-vacio">
                                        <i class="fas fa-users-slash" aria-hidden="true"></i>
                                        <p>No hay grupos que coincidan con los filtros.</p>
                                        <?php if ($busqueda !== '' || $filtroTipo !== '' || $filtroEstado !== ''): ?>
                                            <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/grupos">Quitar filtros</a>
                                        <?php endif; ?>
                                    </div>
                                </td></tr>
                            <?php endif; ?>
                            <?php foreach ($grupos as $grupo): ?>
                                <?php $editando = $esAdminVista && $editarId === (int)$grupo['id']; ?>
                                <tr>
                                    <?php if ($editando): ?>
                                        <?php $formId = 'editar-grupo-' . (int)$grupo['id']; ?>
                                        <td class="celda-doble">
                                            <form id="<?= $formId ?>" method="post" action="<?= BASE_URL ?>/profesor/grupos">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="accion" value="actualizar">
                                                <input type="hidden" name="id" value="<?= (int)$grupo['id'] ?>">
                                            </form>
                                            <input form="<?= $formId ?>" type="text" name="nombre" value="<?= htmlspecialchars($grupo['nombre']) ?>" required aria-label="Nombre del grupo">
                                            <input form="<?= $formId ?>" type="text" name="curso"  value="<?= htmlspecialchars($grupo['curso'] ?? '') ?>" placeholder="Curso" aria-label="Curso">
                                        </td>
                                        <td class="celda-doble">
                                            <select form="<?= $formId ?>" name="dia_semana" required aria-label="Día de la semana">
                                                <?php foreach (['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia): ?>
                                                    <option value="<?= $dia ?>"<?= $grupo['dia_semana'] === $dia ? ' selected' : '' ?>><?= ucfirst($dia) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="doble-input">
                                                <input form="<?= $formId ?>" type="time" name="hora_inicio" value="<?= htmlspecialchars(substr($grupo['hora_inicio'], 0, 5)) ?>" required aria-label="Hora de inicio">
                                                <input form="<?= $formId ?>" type="time" name="hora_fin"    value="<?= htmlspecialchars(substr($grupo['hora_fin'], 0, 5)) ?>" required aria-label="Hora de fin">
                                            </div>
                                        </td>
                                        <td>
                                            <select form="<?= $formId ?>" name="nivel" required aria-label="Nivel">
                                                <option value="iniciacion"<?= $grupo['nivel'] === 'iniciacion' ? ' selected' : '' ?>>Iniciación</option>
                                                <option value="intermedio"<?= $grupo['nivel'] === 'intermedio' ? ' selected' : '' ?>>Intermedio</option>
                                                <option value="avanzado"<?= $grupo['nivel'] === 'avanzado' ? ' selected' : '' ?>>Avanzado</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select form="<?= $formId ?>" name="tipo" required aria-label="Tipo">
                                                <option value="teatro"<?= $grupo['tipo'] === 'teatro' ? ' selected' : '' ?>>Teatro</option>
                                                <option value="improvisacion"<?= $grupo['tipo'] === 'improvisacion' ? ' selected' : '' ?>>Improvisación</option>
                                                <option value="actuacion"<?= $grupo['tipo'] === 'actuacion' ? ' selected' : '' ?>>Actuación</option>
                                                <option value="danza"<?= $grupo['tipo'] === 'danza' ? ' selected' : '' ?>>Danza</option>
                                                <option value="canto"<?= $grupo['tipo'] === 'canto' ? ' selected' : '' ?>>Canto</option>
                                            </select>
                                        </td>
                                        <td class="celda-doble">
                                            <select form="<?= $formId ?>" name="sala_id" required aria-label="Sala">
                                                <?php foreach ($salas as $sala): ?>
                                                    <option value="<?= (int)$sala['id'] ?>"<?= (int)$grupo['sala_id'] === (int)$sala['id'] ? ' selected' : '' ?>><?= htmlspecialchars($sala['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input form="<?= $formId ?>" type="date" name="fecha_inicio_curso" value="<?= htmlspecialchars($grupo['fecha_inicio_curso']) ?>" required aria-label="Fecha de inicio de curso">
                                            <label class="check-inline"><input form="<?= $formId ?>" type="checkbox" name="activo"<?= (int)$grupo['activo'] === 1 ? ' checked' : '' ?>> Activo</label>
                                        </td>
                                        <td><?= (int)$grupo['total_alumnos'] ?></td>
                                        <td class="celda-observaciones">
                                            <span class="tabla-subdato"><?= count($observacionesPorGrupo[(int)$grupo['id']] ?? []) ?> observ.</span>
                                            <div class="acciones-profesor" style="margin-top:6px;">
                                                <button form="<?= $formId ?>" type="submit" class="badge badge-accion">Guardar</button>
                                                <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/grupos">Cancelar</a>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td><strong><?= htmlspecialchars($grupo['nombre']) ?></strong><div class="tabla-subdato"><?= htmlspecialchars($grupo['curso'] ?: 'Sin curso') ?></div></td>
                                        <td><?= htmlspecialchars(ucfirst($grupo['dia_semana'])) ?><br><span class="tabla-subdato"><?= htmlspecialchars(substr($grupo['hora_inicio'], 0, 5) . ' - ' . substr($grupo['hora_fin'], 0, 5)) ?></span></td>
                                        <td><?= htmlspecialchars(ucfirst($grupo['nivel'])) ?></td>
                                        <td><span class="badge-tipo badge-tipo-<?= htmlspecialchars($grupo['tipo']) ?>"><?= htmlspecialchars(ucfirst($grupo['tipo'])) ?></span></td>
                                        <td><?= htmlspecialchars($grupo['sala_nombre']) ?><br><span class="tabla-subdato"><?= htmlspecialchars($grupo['espacio_nombre']) ?></span></td>
                                        <td class="celda-aforo">
                                            <?php
                                                $alumnosCount = (int)$grupo['total_alumnos'];
                                                $aforoMax = (int)($grupo['sala_aforo'] ?? 0);
                                                if ($aforoMax > 0) {
                                                    $porcentaje = min(100, round(($alumnosCount / $aforoMax) * 100));
                                                    if ($alumnosCount > $aforoMax) {
                                                        $aforoEstado = 'rojo';
                                                    } elseif ($porcentaje >= 85) {
                                                        $aforoEstado = 'amber';
                                                    } else {
                                                        $aforoEstado = 'verde';
                                                    }
                                                } else {
                                                    $porcentaje = 0;
                                                    $aforoEstado = 'sin-aforo';
                                                }
                                            ?>
                                            <div class="aforo-wrap aforo-<?= $aforoEstado ?>">
                                                <div class="aforo-num">
                                                    <strong><?= $alumnosCount ?></strong><span class="aforo-sep">/</span><span class="aforo-max"><?= $aforoMax > 0 ? $aforoMax : '?' ?></span>
                                                </div>
                                                <div class="aforo-barra" title="<?= $aforoMax > 0 ? $alumnosCount . ' de ' . $aforoMax . ' (' . $porcentaje . '%)' : 'Aforo no definido' ?>">
                                                    <div class="aforo-fill" style="width: <?= $porcentaje ?>%"></div>
                                                </div>
                                                <?php if ($aforoMax > 0 && $alumnosCount > $aforoMax): ?>
                                                    <div class="aforo-aviso"><span aria-hidden="true">&#9888;</span> Sobrecupo</div>
                                                <?php elseif ($aforoMax > 0 && $porcentaje >= 85): ?>
                                                    <div class="aforo-aviso"><span aria-hidden="true">&#9888;</span> Casi lleno</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="celda-observaciones">
                                            <?php $obsGrupo = $observacionesPorGrupo[(int)$grupo['id']] ?? []; ?>
                                            <?php if (empty($obsGrupo)): ?>
                                                <p class="obs-vacio">Sin observaciones</p>
                                            <?php else: ?>
                                                <ul class="obs-lista obs-lista-visible">
                                                    <?php foreach ($obsGrupo as $obs): ?>
                                                        <li class="obs-item">
                                                            <div class="obs-meta"><span aria-hidden="true">&#128197;</span> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($obs['fecha_creacion']))) ?></div>
                                                            <div class="obs-texto"><?= nl2br(htmlspecialchars($obs['texto'])) ?></div>
                                                            <?php if ($esAdminVista || $obs['profesor_id'] === $usuarioId): ?>
                                                                <form method="post" action="<?= BASE_URL ?>/profesor/grupos" class="obs-eliminar"
                                                                      data-swal-confirm="La observación se eliminará permanentemente."
                                                                      data-swal-title="¿Eliminar esta observación?"
                                                                      data-swal-ok="Sí, eliminar"
                                                                      data-swal-icon="question"
                                                                      data-swal-danger>
                                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                    <input type="hidden" name="accion" value="eliminar_observacion">
                                                                    <input type="hidden" name="observacion_id" value="<?= (int)$obs['id'] ?>">
                                                                    <button type="submit" class="badge badge-anular" title="Eliminar" aria-label="Eliminar observación"><span aria-hidden="true">&#128465;</span></button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>

                                            <details class="obs-anadir">
                                                <summary class="obs-boton-anadir"><span aria-hidden="true">&#43;</span> Añadir</summary>
                                                <form method="post" action="<?= BASE_URL ?>/profesor/grupos" class="form-observaciones">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="accion" value="observaciones">
                                                    <input type="hidden" name="destino_id" value="<?= (int)$grupo['id'] ?>">
                                                    <textarea name="texto" rows="2" required placeholder="Escribe tu observación…" aria-label="Texto de la observación"></textarea>
                                                    <div class="plantillas-obs" role="group" aria-label="Plantillas rápidas">
                                                        <?php foreach ($plantillasObsGrupo as $plantilla): ?>
                                                            <button type="button" class="badge badge-plantilla" data-plantilla="<?= htmlspecialchars($plantilla) ?>"><?= htmlspecialchars($plantilla) ?></button>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button type="submit" class="badge badge-accion"><span aria-hidden="true">&#128190;</span> Guardar</button>
                                                </form>
                                            </details>
                                            <?php if ($esAdminVista): ?>
                                                <div class="acciones-profesor" style="margin-top:8px;">
                                                    <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/grupos?editar=<?= (int)$grupo['id'] ?>">Editar</a>
                                                    <form method="post" action="<?= BASE_URL ?>/profesor/grupos"
                                                          data-swal-confirm="Si tiene clases asociadas no se podrá eliminar. Esta acción no se puede deshacer."
                                                          data-swal-title="¿Eliminar este grupo?"
                                                          data-swal-ok="Sí, eliminar"
                                                          data-swal-icon="warning"
                                                          data-swal-danger>
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?= (int)$grupo['id'] ?>">
                                                        <button type="submit" class="badge badge-anular">Eliminar</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

<script>
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
