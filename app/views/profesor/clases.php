<?php
// =============================================================================
// Vista: Listado de clases del profesor (página autónoma).
//
// Variables disponibles desde ProfesorController::clases():
//   $clases                  Listado completo de clases (tabla "Listado").
//   $proximasClases          Tarjetas superiores (hoy/mañana o las 4 siguientes).
//   $modoTarjetas            'urgente' (hoy/mañana) | 'proximas' (4 siguientes).
//   $tarjetasPaginables      bool, true sólo en modo 'proximas'.
//   $gruposDisponibles       Grupos válidos para el selector de edición (solo admin).
//   $salas                   Salas disponibles para el selector de edición (solo admin).
//   $editarId                ID de la clase que se está editando (solo admin).
//   $mensaje                 ['tipo' => ..., 'texto' => ...] aviso superior.
//   $observacionesPorClase   Observaciones agrupadas por clase_id.
//   $pagina                  Página actual (sólo modo 'proximas').
//   $porPagina               Nº de tarjetas por página.
//   $csrfToken               Token CSRF que se inyecta en cada formulario.
// =============================================================================

$vista = 'clases';
require __DIR__ . '/_header.php';

$rolUsuario         = $_SESSION['usuario_rol'] ?? '';
$usuarioId          = $_SESSION['usuario_id']  ?? '';
$esAdminVista       = ($rolUsuario === 'admin');
// Admin → última columna = Acciones (Editar/Eliminar). Profesor → Observaciones del grupo.
$totalColumnas      = 7; // Grupo, Fecha, Horario, Sala, Cupo, Estado, [Acciones | Observaciones]
$pagina             = $pagina             ?? 1;
$porPagina          = $porPagina          ?? 4;
$proximasClases     = $proximasClases     ?? [];
$modoTarjetas       = $modoTarjetas       ?? 'proximas';
$tarjetasPaginables = $tarjetasPaginables ?? false;
$csrfToken          = $csrfToken          ?? '';
$filtroGrupo        = $filtroGrupo        ?? 0;
$filtroEstadoClase  = $filtroEstadoClase  ?? '';
$filtroDesde        = $filtroDesde        ?? '';
$filtroHasta        = $filtroHasta        ?? '';
$paginaListado       = $paginaListado       ?? 1;
$porPaginaListado    = $porPaginaListado    ?? 6;
$totalClases         = $totalClases         ?? count($clases);
$totalPaginasListado = $totalPaginasListado ?? max(1, (int)ceil($totalClases / $porPaginaListado));

$plantillasObsClase = [
    'Buena sesión',
    'Faltó material',
    'Hubo conflicto entre alumnos',
    'Repasar para la próxima',
    'Sesión cancelada por imprevisto',
];

// Iconos y etiquetas por tipo de grupo (ENUM real de BBDD).
$iconosGrupo = [
    'teatro'        => ['icono' => '&#127916;', 'etiqueta' => 'Teatro'],         // 🎬
    'improvisacion' => ['icono' => '&#9889;',   'etiqueta' => 'Improvisación'],  // ⚡
    'actuacion'     => ['icono' => '&#127917;', 'etiqueta' => 'Actuación'],      // 🎭
    'danza'         => ['icono' => '&#128131;', 'etiqueta' => 'Danza'],          // 💃
    'canto'         => ['icono' => '&#127908;', 'etiqueta' => 'Canto'],          // 🎤
];
$iconoPara = function (?string $tipo) use ($iconosGrupo): array {
    $clave = strtolower((string)$tipo);
    return $iconosGrupo[$clave] ?? ['icono' => '&#127914;', 'etiqueta' => $clave !== '' ? ucfirst($clave) : 'Sin tipo'];
};
$diasCortos = ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'];

// Título de la sección de tarjetas según el modo. Sin subtítulo, queda más limpio.
if ($modoTarjetas === 'urgente') {
    $tituloTarjetas    = $esAdminVista ? 'Hoy y mañana' : 'Tus clases de hoy y mañana';
    $subtituloTarjetas = '';
} else {
    $tituloTarjetas    = $esAdminVista ? 'Próximas clases programadas' : 'Tus próximas clases';
    $subtituloTarjetas = 'No tienes clases inmediatas; estas son las siguientes en agenda.';
}
?>

            <div class="etiqueta">ÁREA PROFESORADO</div>
            <h1>Clases</h1>


            <?php if (!empty($proximasClases)): ?>
                <section class="profesor-seccion seccion-tarjetas-clases<?= $modoTarjetas === 'urgente' ? ' seccion-urgente' : '' ?>">
                    <div class="cabecera-tarjetas-clases">
                        <div>
                            <div class="titulo-seccion titulo-seccion-sin-margen">
                                <?= $modoTarjetas === 'urgente'
                                    ? '<span class="badge-urgente" aria-hidden="true">&#128276;</span> '
                                    : '' ?>
                                <?= htmlspecialchars($tituloTarjetas) ?>
                            </div>
                            <?php if ($subtituloTarjetas !== ''): ?>
                                <p class="subtitulo-seccion"><?= htmlspecialchars($subtituloTarjetas) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($modoTarjetas === 'urgente'): ?>
                            <span class="chip-urgente">CONTROL DE ASISTENCIA</span>
                        <?php endif; ?>
                    </div>

                    <div class="asist-grid">
                        <?php
                        $hoyStr    = date('Y-m-d');
                        $mananaStr = date('Y-m-d', strtotime('+1 day'));
                        ?>
                        <?php foreach ($proximasClases as $clase): ?>
                            <?php
                                $esHoy       = ($clase['fecha'] === $hoyStr);
                                $esManana    = ($clase['fecha'] === $mananaStr);
                                $etiquetaDia = $esHoy ? 'HOY' : ($esManana ? 'MAÑANA' : '');
                                $extraClase  = $esHoy ? ' asist-card-hoy' : ($esManana ? ' asist-card-manana' : '');
                                $info        = $iconoPara($clase['grupo_tipo'] ?? null);
                                $diaCorto    = $diasCortos[(int)date('N', strtotime($clase['fecha'])) - 1] ?? '';
                            ?>
                            <a class="asist-card<?= $extraClase ?>" href="<?= BASE_URL ?>/profesor/asistencia?clase_id=<?= (int)$clase['id'] ?>">
                                <?php if ($etiquetaDia !== ''): ?>
                                    <span class="asist-card-badge"><?= $etiquetaDia ?></span>
                                <?php endif; ?>

                                <div class="asist-card-icono">
                                    <span class="asist-emoji" aria-hidden="true"><?= $info['icono'] ?></span>
                                </div>

                                <div class="asist-card-fecha">
                                    <span class="asist-dia-texto"><?= $diaCorto ?></span>
                                    <span class="asist-dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></span>
                                    <span class="asist-mes"><?= strtoupper(date('M', strtotime($clase['fecha']))) ?></span>
                                </div>

                                <div class="asist-card-grupo">
                                    <span class="asist-grupo-nombre"><?= htmlspecialchars($clase['grupo_nombre']) ?></span>
                                    <span class="asist-grupo-indicadores">
                                        <span class="asist-chip-alumnos" title="<?= (int)($clase['total_alumnos'] ?? 0) ?> alumnos en el grupo">
                                            <span aria-hidden="true">&#128101;</span>
                                            <?= (int)($clase['total_alumnos'] ?? 0) ?>
                                        </span>
                                        <?php if ((int)($clase['total_confirmados'] ?? 0) > 0): ?>
                                            <span class="asist-chip-confirmados" title="<?= (int)$clase['total_confirmados'] ?> confirman asistencia">
                                                <span aria-hidden="true">&#10003;</span> <?= (int)$clase['total_confirmados'] ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ((int)($clase['total_avisos'] ?? 0) > 0): ?>
                                            <span class="asist-chip-avisos" title="<?= (int)$clase['total_avisos'] ?> no asistirán">
                                                <span aria-hidden="true">&#10007;</span> <?= (int)$clase['total_avisos'] ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ((int)($clase['total_tokens'] ?? 0) > 0): ?>
                                            <span class="asist-chip-tokens" title="<?= (int)$clase['total_tokens'] ?> alumno(s) con token de recuperación">
                                                <span aria-hidden="true">&#127915;</span> <?= (int)$clase['total_tokens'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="asist-card-meta">
                                    <span><span aria-hidden="true">&#128336;</span> <?= substr($clase['hora_inicio'], 0, 5) ?></span>
                                    <span><span aria-hidden="true">&#128205;</span> <?= htmlspecialchars($clase['sala_nombre'] ?? '-') ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($tarjetasPaginables): ?>
                        <nav class="paginacion-clases-pro" aria-label="Paginación de próximas clases">
                            <?php if ($pagina > 1): ?>
                                <a class="pag-btn" href="<?= BASE_URL ?>/profesor/clases?pagina=<?= $pagina - 1 ?>">
                                    &larr; Anterior
                                </a>
                            <?php else: ?>
                                <span class="pag-btn pag-disabled" aria-disabled="true">&larr; Anterior</span>
                            <?php endif; ?>

                            <span class="pag-numero">
                                Página <?= (int)$pagina ?>
                            </span>

                            <?php if (count($proximasClases) === $porPagina): ?>
                                <a class="pag-btn" href="<?= BASE_URL ?>/profesor/clases?pagina=<?= $pagina + 1 ?>">
                                    Siguiente &rarr;
                                </a>
                            <?php else: ?>
                                <span class="pag-btn pag-disabled" aria-disabled="true">Siguiente &rarr;</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if (!$esAdminVista): ?>
                <p class="aviso aviso-info">
                    Las clases las programa el administrador. Aquí puedes consultarlas y dejar observaciones de cada sesión.
                </p>
            <?php endif; ?>

            <section class="profesor-seccion">
                <form method="get" action="<?= BASE_URL ?>/profesor/clases" class="barra-filtros" role="search">
                    <label class="filtro-select">
                        <span class="visualmente-oculto">Filtrar por grupo</span>
                        <select name="grupo" onchange="this.form.submit()">
                            <option value="0">Todos los grupos</option>
                            <?php foreach ($gruposDisponibles as $g): ?>
                                <option value="<?= (int)$g['id'] ?>"<?= (int)($filtroGrupo ?? 0) === (int)$g['id'] ? ' selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="filtro-select">
                        <span class="visualmente-oculto">Filtrar por estado</span>
                        <select name="estado_clase" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="programada"<?= ($filtroEstadoClase ?? '') === 'programada' ? ' selected' : '' ?>>Programada</option>
                            <option value="realizada"<?= ($filtroEstadoClase ?? '') === 'realizada' ? ' selected' : '' ?>>Realizada</option>
                            <?php if ($esAdminVista): ?>
                            <option value="cancelada"<?= ($filtroEstadoClase ?? '') === 'cancelada' ? ' selected' : '' ?>>Cancelada</option>
                            <?php endif; ?>
                        </select>
                    </label>
                    <label class="filtro-fecha">
                        <span class="visualmente-oculto">Desde</span>
                        <input type="date" name="desde" value="<?= htmlspecialchars($filtroDesde ?? '') ?>" title="Desde">
                    </label>
                    <label class="filtro-fecha">
                        <span class="visualmente-oculto">Hasta</span>
                        <input type="date" name="hasta" value="<?= htmlspecialchars($filtroHasta ?? '') ?>" title="Hasta">
                    </label>
                    <button type="submit" class="badge badge-accion">Filtrar</button>
                    <?php if (($filtroGrupo ?? 0) > 0 || ($filtroEstadoClase ?? '') !== '' || ($filtroDesde ?? '') !== '' || ($filtroHasta ?? '') !== ''): ?>
                        <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/clases">Limpiar</a>
                    <?php endif; ?>
                </form>
                <div class="resumen-filtros"><strong><?= count($clases) ?></strong> clase<?= count($clases) === 1 ? '' : 's' ?> en el listado.</div>
            </section>

            <section class="profesor-seccion bloque-listado-seccion" id="listado-completo">
                <details class="bloque-listado"<?= $paginaListado > 1 ? ' open' : '' ?>>
                    <summary class="bloque-listado-titulo">
                        <span class="bloque-listado-texto">
                            <span aria-hidden="true">&#128203;</span>
                            Ver listado completo de clases
                        </span>
                        <span class="bloque-listado-contador"><?= count($clases) ?></span>
                    </summary>
                <div class="tabla-scroll">
                    <table class="tabla-profesor">
                        <thead>
                            <tr>
                                <th scope="col">Grupo</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Horario</th>
                                <th scope="col">Sala</th>
                                <th scope="col">Cupo</th>
                                <th scope="col">Estado</th>
                                <?php if ($esAdminVista): ?>
                                    <th scope="col">Acciones</th>
                                <?php else: ?>
                                    <th scope="col">Observaciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clases)): ?>
                                <tr>
                                    <td colspan="<?= $totalColumnas ?>" class="vacio-tabla">No hay clases registradas.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($clases as $clase): ?>
                                <?php $editando = $esAdminVista && $editarId === (int)$clase['id']; ?>
                                <tr>
                                    <?php if ($editando): ?>
                                        <?php $formId = 'editar-clase-' . (int)$clase['id']; ?>
                                        <td>
                                            <form id="<?= $formId ?>" method="post" action="<?= BASE_URL ?>/profesor/clases">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="accion" value="actualizar">
                                                <input type="hidden" name="id" value="<?= (int)$clase['id'] ?>">
                                            </form>
                                            <label class="visualmente-oculto" for="grupo-<?= (int)$clase['id'] ?>">Grupo</label>
                                            <select id="grupo-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" name="grupo_id" required>
                                                <?php foreach ($gruposDisponibles as $grupo): ?>
                                                    <option value="<?= (int)$grupo['id'] ?>"<?= (int)$clase['grupo_id'] === (int)$grupo['id'] ? ' selected' : '' ?>>
                                                        <?= htmlspecialchars($grupo['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <label class="visualmente-oculto" for="fecha-<?= (int)$clase['id'] ?>">Fecha</label>
                                            <input id="fecha-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" type="date" name="fecha" value="<?= htmlspecialchars($clase['fecha']) ?>" required>
                                        </td>
                                        <td>
                                            <label class="visualmente-oculto" for="hora-<?= (int)$clase['id'] ?>">Hora de inicio</label>
                                            <input id="hora-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" type="time" name="hora_inicio" value="<?= htmlspecialchars(substr($clase['hora_inicio'], 0, 5)) ?>" required>
                                        </td>
                                        <td>
                                            <label class="visualmente-oculto" for="sala-<?= (int)$clase['id'] ?>">Sala</label>
                                            <select id="sala-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" name="sala_id" required>
                                                <?php foreach ($salas as $sala): ?>
                                                    <option value="<?= (int)$sala['id'] ?>"<?= (int)$clase['sala_id'] === (int)$sala['id'] ? ' selected' : '' ?>>
                                                        <?= htmlspecialchars($sala['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <label class="visualmente-oculto" for="cupo-<?= (int)$clase['id'] ?>">Cupo máximo</label>
                                            <input id="cupo-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" type="number" name="cupo_maximo" min="1" value="<?= (int)$clase['cupo_maximo'] ?>" required>
                                        </td>
                                        <td>
                                            <label class="visualmente-oculto" for="estado-<?= (int)$clase['id'] ?>">Estado</label>
                                            <select id="estado-<?= (int)$clase['id'] ?>" form="<?= $formId ?>" name="estado" required>
                                                <option value="programada"<?= $clase['estado'] === 'programada' ? ' selected' : '' ?>>Programada</option>
                                                <option value="cancelada"<?= $clase['estado'] === 'cancelada' ? ' selected' : '' ?>>Cancelada</option>
                                                <option value="realizada"<?= $clase['estado'] === 'realizada' ? ' selected' : '' ?>>Realizada</option>
                                            </select>
                                        </td>
                                        <td class="acciones-profesor">
                                            <button form="<?= $formId ?>" type="submit" class="badge badge-accion">Guardar</button>
                                            <a class="badge badge-cancelar" href="<?= BASE_URL ?>/profesor/clases">Cancelar</a>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <strong><?= htmlspecialchars($clase['grupo_nombre']) ?></strong>
                                            <div class="tabla-subdato"><?= htmlspecialchars($clase['grupo_tipo']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($clase['fecha']))) ?></td>
                                        <td><?= htmlspecialchars(substr($clase['hora_inicio'], 0, 5) . ' - ' . substr($clase['hora_fin'], 0, 5)) ?></td>
                                        <td><?= htmlspecialchars($clase['sala_nombre'] ?: '-') ?></td>
                                        <td><?= (int)$clase['cupo_maximo'] ?></td>
                                        <td><span class="badge-estado-clase badge-estado-clase-<?= htmlspecialchars($clase['estado']) ?>"><?= htmlspecialchars(ucfirst($clase['estado'])) ?></span></td>
                                        <?php if ($esAdminVista): ?>
                                            <td class="acciones-profesor">
                                                <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/clases?editar=<?= (int)$clase['id'] ?>">Editar</a>
                                                <form method="post" action="<?= BASE_URL ?>/profesor/clases"
                                                      data-swal-confirm="Esta acción no se puede deshacer."
                                                      data-swal-title="¿Eliminar esta clase?"
                                                      data-swal-ok="Sí, eliminar"
                                                      data-swal-icon="warning"
                                                      data-swal-danger>
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?= (int)$clase['id'] ?>">
                                                    <button type="submit" class="badge badge-anular">Eliminar</button>
                                                </form>
                                            </td>
                                        <?php else: ?>
                                            <td class="celda-observaciones">
                                                <?php $obsClase = $observacionesPorClase[(int)$clase['id']] ?? []; ?>
                                                <?php if (empty($obsClase)): ?>
                                                    <p class="obs-vacio">Sin observaciones</p>
                                                <?php else: ?>
                                                    <ul class="obs-lista obs-lista-visible">
                                                        <?php foreach ($obsClase as $obs): ?>
                                                            <li class="obs-item">
                                                                <div class="obs-meta">
                                                                    <span aria-hidden="true">&#128197;</span>
                                                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($obs['fecha_creacion']))) ?>
                                                                </div>
                                                                <div class="obs-texto"><?= nl2br(htmlspecialchars($obs['texto'])) ?></div>
                                                                <?php if ($esAdminVista || $obs['profesor_id'] === $usuarioId): ?>
                                                                    <form method="post" action="<?= BASE_URL ?>/profesor/clases" class="obs-eliminar"
                                                                          data-swal-confirm="La observación se eliminará permanentemente."
                                                                          data-swal-title="¿Eliminar esta observación?"
                                                                          data-swal-ok="Sí, eliminar"
                                                                          data-swal-icon="question"
                                                                          data-swal-danger>
                                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                        <input type="hidden" name="accion" value="eliminar_observacion">
                                                                        <input type="hidden" name="observacion_id" value="<?= (int)$obs['id'] ?>">
                                                                        <button type="submit" class="badge badge-anular" title="Eliminar observación" aria-label="Eliminar observación">
                                                                            <span aria-hidden="true">&#128465;</span>
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>

                                                <details class="obs-anadir">
                                                    <summary class="obs-boton-anadir">
                                                        <span aria-hidden="true">&#43;</span> Añadir
                                                    </summary>
                                                    <form method="post" action="<?= BASE_URL ?>/profesor/clases" class="form-observaciones">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="accion" value="observaciones">
                                                        <input type="hidden" name="destino_id" value="<?= (int)$clase['id'] ?>">
                                                        <label class="visualmente-oculto" for="obs-clase-<?= (int)$clase['id'] ?>">Texto de la observación</label>
                                                        <textarea id="obs-clase-<?= (int)$clase['id'] ?>" name="texto" rows="2" required placeholder="Escribe tu observación…"></textarea>
                                                        <div class="plantillas-obs" role="group" aria-label="Plantillas rápidas">
                                                            <?php foreach ($plantillasObsClase as $plantilla): ?>
                                                                <button type="button" class="badge badge-plantilla" data-plantilla="<?= htmlspecialchars($plantilla) ?>"><?= htmlspecialchars($plantilla) ?></button>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <button type="submit" class="badge badge-accion">
                                                            <span aria-hidden="true">&#128190;</span> Guardar
                                                        </button>
                                                    </form>
                                                </details>
                                            </td>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginasListado > 1): ?>
                    <?php
                        // Construir URL conservando los filtros activos.
                        $paramsListado = [];
                        if ($filtroGrupo > 0)         $paramsListado['grupo']        = $filtroGrupo;
                        if ($filtroEstadoClase !== '') $paramsListado['estado_clase'] = $filtroEstadoClase;
                        if ($filtroDesde !== '')      $paramsListado['desde']        = $filtroDesde;
                        if ($filtroHasta !== '')      $paramsListado['hasta']        = $filtroHasta;
                        $hacerUrlListado = function (int $p) use ($paramsListado): string {
                            $paramsListado['pl'] = $p;
                            return BASE_URL . '/profesor/clases?' . http_build_query($paramsListado) . '#listado-completo';
                        };
                    ?>
                    <nav class="paginacion-clases-pro" aria-label="Paginación del listado completo">
                        <?php if ($paginaListado > 1): ?>
                            <a class="pag-btn" href="<?= htmlspecialchars($hacerUrlListado($paginaListado - 1)) ?>">&larr; Anterior</a>
                        <?php else: ?>
                            <span class="pag-btn pag-disabled" aria-disabled="true">&larr; Anterior</span>
                        <?php endif; ?>

                        <span class="pag-numero">
                            Página <?= (int)$paginaListado ?> de <?= (int)$totalPaginasListado ?>
                        </span>

                        <?php if ($paginaListado < $totalPaginasListado): ?>
                            <a class="pag-btn" href="<?= htmlspecialchars($hacerUrlListado($paginaListado + 1)) ?>">Siguiente &rarr;</a>
                        <?php else: ?>
                            <span class="pag-btn pag-disabled" aria-disabled="true">Siguiente &rarr;</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
                </details>
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
