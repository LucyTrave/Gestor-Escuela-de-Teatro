<?php
function calendario_mes_titulo($month) {
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fecha = DateTime::createFromFormat('Y-m-d', $month . '-01');
    if (!$fecha) {
        return '';
    }

    return ucfirst($meses[(int)$fecha->format('n') - 1]) . ' ' . $fecha->format('Y');
}

function calendario_dia_semana($index) {
    $dias = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
    return $dias[$index] ?? '';
}

function calendario_tipo_clase($tipo) {
    $mapa = ['teatro' => 'Teatro', 'improvisacion' => 'Improvisación', 'voz' => 'Voz', 'expresion_corporal' => 'Expresión Corporal', 'intensivo' => 'Intensivo'];
    return $mapa[$tipo] ?? $tipo;
}

function calendario_fecha_legible($fecha) {
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return date('j', strtotime($fecha)) . ' ' . $meses[(int)date('n', strtotime($fecha)) - 1] . ' ' . date('Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario | Gestor Escuela</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="pagina-dashboard">

    <header class="cabecera-dashboard alumno-header">
        <div class="cabecera-bloque">
            <a href="<?= BASE_URL ?>/alumno" class="marca-enlace marca-bloque" aria-label="Ir al inicio de alumnado">
                <span class="marca">GESTOR ESCUELA</span>
                <span class="subtitulo">ALUMNADO</span>
            </a>
        </div>
        <nav>
            <a href="<?= BASE_URL ?>/alumno" class="enlace-nav">Mis clases</a>
            <span class="enlace-nav activo">Calendario</span>
            <a href="<?= BASE_URL ?>/alumno/recuperar" class="enlace-nav">Recuperar</a>
            <a href="<?= BASE_URL ?>/alumno/tokens" class="enlace-nav">Tokens</a>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido contenido-calendario">
        <p class="etiqueta">PLANIFICACIÓN DE ACTIVIDADES</p>
        <h1>Calendario</h1>

        <?php if ($mensaje === 'avisado_token'): ?>
            <div class="aviso aviso-exito">✅ Ausencia registrada. Has ganado 1 token.</div>
        <?php elseif ($mensaje === 'avisado_sin_token'): ?>
            <div class="aviso aviso-warning">⚠️ Ausencia registrada, pero no genera token (menos de 24h).</div>
        <?php elseif ($mensaje === 'avisado_sin_token_maximo'): ?>
            <div class="aviso aviso-warning">⚠️ Ausencia registrada. No genera token porque ya tienes el máximo.</div>
        <?php elseif ($mensaje === 'ya_avisado'): ?>
            <div class="aviso aviso-warning">ℹ️ Ya habías avisado de esta ausencia.</div>
        <?php elseif ($mensaje === 'anulado'): ?>
            <div class="aviso aviso-exito">✅ Aviso cancelado correctamente.</div>
        <?php elseif ($mensaje === 'anulado_con_token'): ?>
            <div class="aviso aviso-warning">⚠️ Aviso cancelado. Se ha eliminado el token asociado.</div>
        <?php elseif ($mensaje === 'error_datos'): ?>
            <div class="aviso aviso-warning">⚠️ No se pudo completar la acción. Vuelve a intentarlo.</div>
        <?php endif; ?>

        <section class="calendario-layout">
            <div class="calendario-panel">
                <div class="calendario-barra-superior">
                    <div class="calendario-barra-izquierda">
                        <a href="<?= BASE_URL ?>/alumno/calendario?month=<?= urlencode(date('Y-m')) ?>&fecha=<?= urlencode(date('Y-m-d')) ?>" class="calendario-boton-hoy">Hoy</a>
                        <div class="calendario-controles-mes">
                            <a href="<?= BASE_URL ?>/alumno/calendario?month=<?= urlencode($previousMonth) ?>" class="calendario-flecha" aria-label="Mes anterior">‹</a>
                            <a href="<?= BASE_URL ?>/alumno/calendario?month=<?= urlencode($nextMonth) ?>" class="calendario-flecha" aria-label="Mes siguiente">›</a>
                        </div>
                        <h2><?= htmlspecialchars(calendario_mes_titulo($monthStart->format('Y-m'))) ?></h2>
                    </div>

                    <div class="calendario-leyenda">
                        <span class="leyenda-chip leyenda-chip-verde"></span>
                        <span>Mis actividades</span>
                    </div>
                </div>

                <div class="calendario-grid calendario-grid-cabecera">
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <div class="calendario-celda-head"><?= calendario_dia_semana($i) ?></div>
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
                                href="<?= BASE_URL ?>/alumno/calendario?month=<?= urlencode($monthStart->format('Y-m')) ?>&fecha=<?= urlencode($day['date']) ?>"
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
                                        <span class="calendario-evento-mini <?= $miniEvento['asistencia_id'] ? 'evento-mini-avisado' : '' ?>">
                                            <strong><?= substr($miniEvento['hora_inicio'], 0, 5) ?></strong>
                                            <?= htmlspecialchars($miniEvento['grupo_nombre']) ?>
                                        </span>
                                    <?php endforeach; ?>

                                    <?php if (count($dayEvents) > 3): ?>
                                        <span class="calendario-evento-mas">+<?= count($dayEvents) - 3 ?> más</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="calendario-detalle">
                <div class="calendario-detalle-head">
                    <p class="tarjeta-etiqueta">DÍA SELECCIONADO</p>
                    <h2><?= htmlspecialchars(calendario_fecha_legible($selectedDate)) ?></h2>
                </div>

                <?php if (empty($selectedEvents)): ?>
                    <p class="vacio-calendario">No tienes actividades ese día.</p>
                <?php else: ?>
                    <div class="calendario-eventos">
                        <?php foreach ($selectedEvents as $evento): ?>
                            <article class="calendario-evento">
                                <div class="calendario-evento-banda"></div>
                                <div class="calendario-evento-contenido">
                                    <p class="calendario-evento-tipo"><?= htmlspecialchars(calendario_tipo_clase($evento['grupo_tipo'])) ?></p>
                                    <h3><?= htmlspecialchars($evento['grupo_nombre']) ?></h3>
                                    <p class="calendario-evento-hora"><?= substr($evento['hora_inicio'], 0, 5) ?> - <?= substr($evento['hora_fin'], 0, 5) ?></p>

                                    <div class="badge-zona badge-zona-izquierda">
                                        <?php $redirect_to = '/alumno/calendario?month=' . $monthStart->format('Y-m') . '&fecha=' . $selectedDate; ?>
                                        <?php if ($evento['asistencia_id']): ?>
                                            <span class="badge badge-avisada">AUSENCIA AVISADA</span>
                                            <form action="<?= BASE_URL ?>/alumno/anular-ausencia" method="POST" class="form-inline" id="form-cal-anular-<?= $evento['asistencia_id'] ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="asistencia_id" value="<?= $evento['asistencia_id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
                                                <button type="button" class="badge badge-cancelar" onclick="confirmarAnularCal(<?= $evento['asistencia_id'] ?>)">Cancelar</button>
                                            </form>
                                        <?php elseif ($selectedDate === date('Y-m-d')): ?>
                                            <form action="<?= BASE_URL ?>/alumno/avisar-ausencia" method="POST" class="form-inline" id="form-cal-avisar-<?= $evento['id'] ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="clase_id" value="<?= $evento['id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
                                                <button type="button" class="badge badge-avisar" onclick="confirmarAvisarHoyCal(<?= $evento['id'] ?>)">AVISAR AUSENCIA</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>/alumno/avisar-ausencia" method="POST" class="form-inline" id="form-cal-avisar-<?= $evento['id'] ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="clase_id" value="<?= $evento['id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
                                                <button type="button" class="badge badge-avisar" onclick="confirmarAvisarCal(<?= $evento['id'] ?>)">NO PUEDO ASISTIR</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </section>
    </main>

    <div class="franja-roja"></div>

    <script>
    <?php if ($mensaje === 'avisado_token'): ?>
    Swal.fire({ icon: 'success', title: 'Ausencia registrada', text: 'Has ganado 1 token de recuperación.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'avisado_sin_token'): ?>
    Swal.fire({ icon: 'warning', title: 'Ausencia registrada', text: 'No genera token porque faltan menos de 24 horas.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'avisado_sin_token_maximo'): ?>
    Swal.fire({ icon: 'warning', title: 'Ausencia registrada', text: 'No genera token porque ya tienes el máximo.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'ya_avisado'): ?>
    Swal.fire({ icon: 'info', title: 'Ya avisado', text: 'Ya habías avisado de esta ausencia anteriormente.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'anulado'): ?>
    Swal.fire({ icon: 'success', title: 'Aviso cancelado', text: 'El aviso de ausencia se ha cancelado correctamente.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'anulado_con_token'): ?>
    Swal.fire({ icon: 'warning', title: 'Aviso cancelado', text: 'Se ha eliminado el token asociado. Si había una recuperación que dependía de ese token, también se ha cancelado.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'error_datos'): ?>
    Swal.fire({ icon: 'warning', title: 'Acción no completada', text: 'Vuelve a intentarlo desde el formulario.', confirmButtonColor: '#DC2626' });
    <?php endif; ?>

    function confirmarAvisarCal(claseId) {
        Swal.fire({
            icon: 'question',
            title: '¿No puedes asistir?',
            text: 'Se registrará tu ausencia para esta actividad.',
            showCancelButton: true,
            confirmButtonText: 'Sí, avisar',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-cal-avisar-' + claseId).submit();
        });
    }

    function confirmarAvisarHoyCal(claseId) {
        Swal.fire({
            icon: 'warning',
            title: 'Esta clase es hoy',
            text: 'El aviso no generará token porque faltan menos de 24 horas. ¿Continuar?',
            showCancelButton: true,
            confirmButtonText: 'Sí, avisar',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-cal-avisar-' + claseId).submit();
        });
    }

    function confirmarAnularCal(asistenciaId) {
        Swal.fire({
            icon: 'question',
            title: '¿Cancelar el aviso?',
            text: 'Si este aviso generó un token y lo has usado en una recuperación, ambos se cancelarán.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar aviso',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-cal-anular-' + asistenciaId).submit();
        });
    }
    </script>

</body>
</html>
