<?php
function inv_cal_mes_titulo($month) {
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fecha = DateTime::createFromFormat('Y-m-d', $month . '-01');
    if (!$fecha) {
        return '';
    }

    return ucfirst($meses[(int)$fecha->format('n') - 1]) . ' ' . $fecha->format('Y');
}

function inv_cal_dia_semana($index) {
    $dias = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
    return $dias[$index] ?? '';
}

function inv_cal_fecha_legible($fecha) {
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return date('j', strtotime($fecha)) . ' ' . $meses[(int)date('n', strtotime($fecha)) - 1] . ' ' . date('Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de prueba | Gestor Escuela</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="pagina-dashboard">

    <header class="cabecera-dashboard cabecera-invitado alumno-header">
        <div class="cabecera-bloque">
            <a href="<?= BASE_URL ?>/invitado" class="marca-enlace marca-bloque" aria-label="Ir a mi clase de prueba">
                <span class="marca">GESTOR ESCUELA</span>
                <span class="subtitulo">INVITADO</span>
            </a>
        </div>
        <nav>
            <a href="<?= BASE_URL ?>/invitado" class="enlace-nav">Mi clase de prueba</a>
            <span class="enlace-nav activo">Calendario</span>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido contenido-calendario">
        <p class="etiqueta">CLASES DE IMPROVISACIÓN</p>
        <h1>Calendario de prueba</h1>

        <?php if ($mensaje === 'reservado'): ?>
            <div class="aviso aviso-exito">✅ Clase de prueba reservada. Te esperamos.</div>
        <?php elseif ($mensaje === 'cancelado'): ?>
            <div class="aviso aviso-exito">✅ Reserva cancelada. Puedes elegir otra clase.</div>
        <?php elseif ($mensaje === 'sin_token'): ?>
            <div class="aviso aviso-warning">⚠️ Ya has utilizado tu clase de prueba.</div>
        <?php elseif ($mensaje === 'ya_reservado'): ?>
            <div class="aviso aviso-warning">ℹ️ Ya tienes una clase de prueba reservada.</div>
        <?php elseif ($mensaje === 'sin_plazas'): ?>
            <div class="aviso aviso-warning">⚠️ Esa clase ya no tiene plazas disponibles.</div>
        <?php elseif ($mensaje === 'error_datos'): ?>
            <div class="aviso aviso-warning">⚠️ No se pudo completar la acción. Vuelve a intentarlo.</div>
        <?php endif; ?>

        <section class="calendario-layout">
            <div class="calendario-panel">
                <div class="calendario-barra-superior">
                    <div class="calendario-barra-izquierda">
                        <a href="<?= BASE_URL ?>/invitado/calendario?month=<?= urlencode(date('Y-m')) ?>&fecha=<?= urlencode(date('Y-m-d')) ?>" class="calendario-boton-hoy">Hoy</a>
                        <div class="calendario-controles-mes">
                            <a href="<?= BASE_URL ?>/invitado/calendario?month=<?= urlencode($previousMonth) ?>" class="calendario-flecha" aria-label="Mes anterior">‹</a>
                            <a href="<?= BASE_URL ?>/invitado/calendario?month=<?= urlencode($nextMonth) ?>" class="calendario-flecha" aria-label="Mes siguiente">›</a>
                        </div>
                        <h2><?= htmlspecialchars(inv_cal_mes_titulo($monthStart->format('Y-m'))) ?></h2>
                    </div>

                    <div class="calendario-leyenda">
                        <span class="leyenda-chip leyenda-chip-verde"></span>
                        <span><?= $clase_reservada ? 'Tu reserva' : 'Clases disponibles' ?></span>
                    </div>
                </div>

                <div class="calendario-grid calendario-grid-cabecera">
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <div class="calendario-celda-head"><?= inv_cal_dia_semana($i) ?></div>
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
                                href="<?= BASE_URL ?>/invitado/calendario?month=<?= urlencode($monthStart->format('Y-m')) ?>&fecha=<?= urlencode($day['date']) ?>"
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
                                        <span class="calendario-evento-mini <?= !empty($miniEvento['esta_reservada']) ? 'evento-mini-avisado' : '' ?>">
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
                    <h2><?= htmlspecialchars(inv_cal_fecha_legible($selectedDate)) ?></h2>
                </div>

                <?php if (!$token_disponible && !$clase_reservada): ?>
                    <p class="vacio-calendario">Ya has utilizado tu clase de prueba.</p>
                <?php elseif (empty($selectedEvents)): ?>
                    <p class="vacio-calendario">No hay clases disponibles ese día.</p>
                <?php else: ?>
                    <div class="calendario-eventos">
                        <?php foreach ($selectedEvents as $evento): ?>
                            <?php
                            $ocupadas = (int)($evento['inscritos'] ?? 0) + (int)($evento['recuperaciones'] ?? 0);
                            $libres = isset($evento['cupo_maximo']) ? (int)$evento['cupo_maximo'] - $ocupadas : null;
                            $hay_plazas = $libres === null || $libres > 0;
                            $redirect_to = '/invitado/calendario?month=' . $monthStart->format('Y-m') . '&fecha=' . $selectedDate;
                            ?>
                            <article class="calendario-evento">
                                <div class="calendario-evento-banda"></div>
                                <div class="calendario-evento-contenido">
                                    <p class="calendario-evento-tipo"><?= !empty($evento['esta_reservada']) ? 'Clase reservada' : 'Clase disponible' ?></p>
                                    <h3><?= htmlspecialchars($evento['grupo_nombre']) ?></h3>
                                    <p class="calendario-evento-hora">
                                        <?= substr($evento['hora_inicio'], 0, 5) ?> - <?= substr($evento['hora_fin'], 0, 5) ?>
                                        <?php if (!empty($evento['profesor'])): ?>
                                            · <?= htmlspecialchars($evento['profesor']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($evento['sala'])): ?>
                                            · <?= htmlspecialchars($evento['sala']) ?>
                                        <?php endif; ?>
                                    </p>

                                    <div class="badge-zona badge-zona-izquierda">
                                        <?php if (!empty($evento['esta_reservada'])): ?>
                                            <span class="badge badge-recuperada">RESERVADA</span>
                                            <form action="<?= BASE_URL ?>/invitado/cancelar" method="POST" class="form-inline" id="form-inv-cal-cancelar-<?= (int)$evento['recuperacion_id'] ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="recuperacion_id" value="<?= (int)$evento['recuperacion_id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
                                                <button type="button" class="badge badge-cancelar" onclick="confirmarCambiarInvCal(<?= (int)$evento['recuperacion_id'] ?>)">Cambiar</button>
                                            </form>
                                        <?php elseif (!$hay_plazas): ?>
                                            <span class="badge badge-llena">COMPLETA</span>
                                        <?php else: ?>
                                            <span class="badge badge-manana"><?= $libres ?> plaza(s)</span>
                                            <form action="<?= BASE_URL ?>/invitado/reservar" method="POST" class="form-inline" id="form-inv-cal-reservar-<?= (int)$evento['clase_id'] ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="clase_id" value="<?= (int)$evento['clase_id'] ?>">
                                                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
                                                <button type="button" class="badge badge-recuperar" onclick="confirmarReservarInvCal(<?= (int)$evento['clase_id'] ?>)">PROBAR ESTA CLASE</button>
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
    <?php if ($mensaje === 'reservado'): ?>
    Swal.fire({ icon: 'success', title: '¡Clase reservada!', text: 'Te esperamos. Llega unos minutos antes para presentarte.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'cancelado'): ?>
    Swal.fire({ icon: 'success', title: 'Reserva cancelada', text: 'Puedes elegir otra clase del calendario.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'sin_token'): ?>
    Swal.fire({ icon: 'warning', title: 'Sin clase de prueba', text: 'Ya has utilizado tu clase de prueba.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'ya_reservado'): ?>
    Swal.fire({ icon: 'info', title: 'Ya reservado', text: 'Ya tienes una clase de prueba reservada.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'sin_plazas'): ?>
    Swal.fire({ icon: 'warning', title: 'Sin plazas', text: 'Esa clase ya no tiene plazas disponibles.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'error_datos'): ?>
    Swal.fire({ icon: 'warning', title: 'Acción no completada', text: 'Vuelve a intentarlo desde el formulario.', confirmButtonColor: '#DC2626' });
    <?php endif; ?>

    function confirmarReservarInvCal(claseId) {
        Swal.fire({
            icon: 'question',
            title: '¿Reservar esta clase?',
            text: 'Usarás tu clase de prueba gratuita.',
            showCancelButton: true,
            confirmButtonText: 'Sí, reservar',
            cancelButtonText: 'No',
            confirmButtonColor: '#0F6E56',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-inv-cal-reservar-' + claseId).submit();
        });
    }

    function confirmarCambiarInvCal(recuperacionId) {
        Swal.fire({
            icon: 'question',
            title: '¿Cambiar de clase?',
            text: 'Tu reserva actual se cancelará y podrás elegir otra del calendario.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-inv-cal-cancelar-' + recuperacionId).submit();
        });
    }
    </script>

</body>
</html>
