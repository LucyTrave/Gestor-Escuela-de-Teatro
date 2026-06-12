<?php
// =============================================================================
// Vista: Calendario del profesor (pagina autonoma)
// =============================================================================
// Variables disponibles desde ProfesorController::calendario():
//   $monthStart, $monthEnd, $calendarWeeks, $eventsByDate,
//   $selectedDate, $selectedEvents, $previousMonth, $nextMonth, $mensaje
// =============================================================================

function prof_cal_mes_titulo($month) {
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fecha = DateTime::createFromFormat('Y-m-d', $month . '-01');
    return $fecha ? ucfirst($meses[(int)$fecha->format('n') - 1]) . ' ' . $fecha->format('Y') : '';
}

function prof_cal_dia_semana($index) {
    $dias = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
    return $dias[$index] ?? '';
}

function prof_cal_tipo_clase($tipo) {
    $mapa = ['teatro' => 'Teatro', 'actuacion' => 'Actuacion', 'danza' => 'Danza', 'canto' => 'Canto', 'improvisacion' => 'Improvisacion', 'voz' => 'Voz', 'expresion_corporal' => 'Expresion Corporal', 'intensivo' => 'Intensivo'];
    return $mapa[$tipo] ?? $tipo;
}

function prof_cal_fecha_legible($fecha) {
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return date('j', strtotime($fecha)) . ' ' . $meses[(int)date('n', strtotime($fecha)) - 1] . ' ' . date('Y', strtotime($fecha));
}

function prof_cal_estado_label($estado) {
    $mapa = ['programada' => 'Programada', 'realizada' => 'Realizada', 'cancelada' => 'Cancelada'];
    return $mapa[$estado] ?? ucfirst((string)$estado);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario | Gestor Escuela</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="pagina-dashboard">

    <header class="cabecera-dashboard">
        <div>
            <div class="marca">GESTOR ESCUELA</div>
            <div class="subtitulo">AREA PROFESORADO</div>
        </div>
        <nav>
            <a class="enlace-nav" href="<?= BASE_URL ?>/profesor">Inicio</a>
            <a class="enlace-nav" href="<?= BASE_URL ?>/profesor/alumnos">Alumnos</a>
            <a class="enlace-nav" href="<?= BASE_URL ?>/profesor/grupos">Grupos</a>
            <a class="enlace-nav" href="<?= BASE_URL ?>/profesor/clases">Clases</a>
            <a class="enlace-nav activo" href="<?= BASE_URL ?>/profesor/calendario">Calendario</a>
        </nav>
        <div class="profesor-usuario">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></strong></span>
            <a class="enlace-cerrar" href="<?= BASE_URL ?>/logout">Cerrar sesion</a>
        </div>
    </header>

    <main class="contenido contenido-profesor">
        <?php if (!empty($mensaje['texto'])): ?>
            <div class="aviso aviso-<?= htmlspecialchars($mensaje['tipo']) ?>"><?= htmlspecialchars($mensaje['texto']) ?></div>
        <?php endif; ?>

        <div class="etiqueta">AREA PROFESORADO</div>
        <h1>Calendario</h1>

        <section class="calendario-layout">
            <div class="calendario-panel">
                <div class="calendario-barra-superior">
                    <div class="calendario-barra-izquierda">
                        <a href="<?= BASE_URL ?>/profesor/calendario?month=<?= urlencode(date('Y-m')) ?>&fecha=<?= urlencode(date('Y-m-d')) ?>" class="calendario-boton-hoy">Hoy</a>
                        <div class="calendario-controles-mes">
                            <a href="<?= BASE_URL ?>/profesor/calendario?month=<?= urlencode($previousMonth) ?>" class="calendario-flecha" aria-label="Mes anterior">&lsaquo;</a>
                            <a href="<?= BASE_URL ?>/profesor/calendario?month=<?= urlencode($nextMonth) ?>" class="calendario-flecha" aria-label="Mes siguiente">&rsaquo;</a>
                        </div>
                        <h2><?= htmlspecialchars(prof_cal_mes_titulo($monthStart->format('Y-m'))) ?></h2>
                    </div>

                    <div class="calendario-leyenda">
                        <span class="leyenda-chip leyenda-chip-programada"></span><span>Programada</span>
                        <span class="leyenda-chip leyenda-chip-realizada"></span><span>Realizada</span>
                        <span class="leyenda-chip leyenda-chip-cancelada"></span><span>Cancelada</span>
                    </div>
                </div>

                <div class="calendario-grid calendario-grid-cabecera">
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <div class="calendario-celda-head"><?= prof_cal_dia_semana($i) ?></div>
                    <?php endfor; ?>
                </div>

                <?php foreach ($calendarWeeks as $week): ?>
                    <div class="calendario-grid">
                        <?php foreach ($week as $day): ?>
                            <?php
                            $classes = ['calendario-celda'];
                            if (!$day['is_current_month']) $classes[] = 'fuera-mes';
                            if ($day['has_events']) $classes[] = 'con-actividad';
                            if ($day['is_selected']) $classes[] = 'seleccionado';
                            if ($day['is_today']) $classes[] = 'hoy';
                            $dayEvents = $eventsByDate[$day['date']] ?? [];
                            ?>
                            <a
                                href="<?= BASE_URL ?>/profesor/calendario?month=<?= urlencode($monthStart->format('Y-m')) ?>&fecha=<?= urlencode($day['date']) ?>"
                                class="<?= implode(' ', $classes) ?>"
                            >
                                <div class="calendario-celda-top">
                                    <span class="calendario-dia-numero"><?= $day['day'] ?></span>
                                    <?php if (count($dayEvents) > 0): ?>
                                        <span class="calendario-dia-total"><?= count($dayEvents) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="calendario-celda-eventos">
                                    <?php foreach (array_slice($dayEvents, 0, 3) as $miniEvento): ?>
                                        <span class="calendario-evento-mini evento-mini-estado-<?= htmlspecialchars($miniEvento['estado']) ?>">
                                            <strong><?= substr($miniEvento['hora_inicio'], 0, 5) ?></strong>
                                            <?= htmlspecialchars($miniEvento['grupo_nombre']) ?>
                                        </span>
                                    <?php endforeach; ?>

                                    <?php if (count($dayEvents) > 3): ?>
                                        <span class="calendario-evento-mas">+<?= count($dayEvents) - 3 ?> mas</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="calendario-detalle">
                <div class="calendario-detalle-head">
                    <p class="tarjeta-etiqueta">DIA SELECCIONADO</p>
                    <h2><?= htmlspecialchars(prof_cal_fecha_legible($selectedDate)) ?></h2>
                </div>

                <?php if (empty($selectedEvents)): ?>
                    <p class="vacio-calendario">No hay clases ese dia.</p>
                <?php else: ?>
                    <div class="calendario-eventos">
                        <?php foreach ($selectedEvents as $evento): ?>
                            <article class="calendario-evento calendario-evento-estado-<?= htmlspecialchars($evento['estado']) ?>">
                                <div class="calendario-evento-banda"></div>
                                <div class="calendario-evento-contenido">
                                    <p class="calendario-evento-tipo"><?= htmlspecialchars(prof_cal_tipo_clase($evento['grupo_tipo'])) ?> &middot; <?= htmlspecialchars(prof_cal_estado_label($evento['estado'])) ?></p>
                                    <h3><?= htmlspecialchars($evento['grupo_nombre']) ?></h3>
                                    <p class="calendario-evento-hora">
                                        <?= substr($evento['hora_inicio'], 0, 5) ?> - <?= substr($evento['hora_fin'], 0, 5) ?>
                                        <?php if (!empty($evento['sala_nombre'])): ?>
                                            &middot; <?= htmlspecialchars($evento['sala_nombre']) ?>
                                        <?php endif; ?>
                                    </p>

                                    <div class="calendario-resumen-asistencia">
                                        <span class="resumen-pill resumen-pill-total" title="Alumnos del grupo"><?= (int)$evento['total_alumnos'] ?> alumn.</span>
                                        <span class="resumen-pill resumen-pill-asisten" title="Asistieron"><?= (int)$evento['total_confirmados'] ?> asist.</span>
                                        <span class="resumen-pill resumen-pill-avisos" title="Avisaron"><?= (int)$evento['total_avisos'] ?> avisos</span>
                                        <span class="resumen-pill resumen-pill-ausentes" title="Ausentes"><?= (int)$evento['total_ausentes'] ?> ausen.</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </section>
    </main>

</body>
</html>
