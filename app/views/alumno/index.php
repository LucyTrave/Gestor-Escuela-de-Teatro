<?php
function dia_semana_corto($f) { $d=['DOM','LUN','MAR','MIE','JUE','VIE','SAB']; return $d[date('w',strtotime($f))]; }
function mes_corto($f) { $m=['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC']; return $m[(int)date('n',strtotime($f))-1]; }
function es_hoy($f) { return date('Y-m-d') === $f; }
function tipo_clase($t) { $m=['teatro'=>'Teatro','improvisacion'=>'Improvisación','voz'=>'Voz','expresion_corporal'=>'Expresión Corporal','intensivo'=>'Intensivo']; return $m[$t]??$t; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Panel | Gestor Escuela</title>
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
            <span class="enlace-nav activo">Mis clases</span>
            <a href="<?= BASE_URL ?>/alumno/calendario" class="enlace-nav">Calendario</a>
            <a href="<?= BASE_URL ?>/alumno/recuperar" class="enlace-nav">Recuperar</a>
            <a href="<?= BASE_URL ?>/alumno/tokens" class="enlace-nav">Tokens</a>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido">
        <p class="etiqueta">HOLA DE NUEVO</p>
        <h1><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h1>

        <?php if ($mensaje === 'avisado_token'): ?>
            <div class="aviso aviso-exito">✅ Ausencia registrada. Has ganado 1 token.</div>
        <?php elseif ($mensaje === 'avisado_sin_token'): ?>
            <div class="aviso aviso-warning">⚠️ Ausencia registrada, pero no genera token (menos de 24h).</div>
        <?php elseif ($mensaje === 'avisado_sin_token_maximo'): ?>
            <div class="aviso aviso-warning">⚠️ Ausencia registrada. No genera token porque ya tienes el máximo (<?= $tokens_maximo ?>).</div>
        <?php elseif ($mensaje === 'ya_avisado'): ?>
            <div class="aviso aviso-warning">ℹ️ Ya habías avisado de esta ausencia.</div>
        <?php elseif ($mensaje === 'anulado'): ?>
            <div class="aviso aviso-exito">✅ Aviso cancelado correctamente.</div>
        <?php elseif ($mensaje === 'anulado_con_token'): ?>
            <div class="aviso aviso-warning">⚠️ Aviso cancelado. Se ha eliminado el token. Si había una recuperación asociada, también se ha cancelado.</div>
        <?php elseif ($mensaje === 'error_datos'): ?>
            <div class="aviso aviso-warning">⚠️ No se pudo completar la acción. Vuelve a intentarlo.</div>
        <?php endif; ?>

        <div class="resumen-dos">
            <div class="tarjeta tarjeta-roja">
                <p class="tarjeta-etiqueta">PRÓXIMA CLASE</p>
                <?php if ($proxima_clase): ?>
                    <p class="tarjeta-valor">
                        <?= dia_semana_corto($proxima_clase['fecha']) ?>
                        <?= date('d', strtotime($proxima_clase['fecha'])) ?>
                        <?= mes_corto($proxima_clase['fecha']) ?> ·
                        <?= substr($proxima_clase['hora_inicio'], 0, 5) ?>
                    </p>
                    <p class="tarjeta-sub"><?= tipo_clase($proxima_clase['grupo_tipo']) ?></p>
                <?php else: ?>
                    <p class="tarjeta-valor">Sin clases</p>
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/alumno/tokens" class="tokens-panel-link">
                <div class="tokens-panel-mini">
                    <div class="tokens-panel-cabecera">
                        <p class="tokens-titulo">TOKENS</p>
                        <div class="tooltip-container">
                            <span class="tooltip-icono">?</span>
                            <div class="tooltip-contenido">
                                <p><strong>1.</strong> Avisa con +24h → ganas 1 token</p>
                                <p><strong>2.</strong> Acumula hasta 4 tokens</p>
                                <p><strong>3.</strong> Usa 1 token para recuperar clase de impro</p>
                            </div>
                        </div>
                    </div>
                    <div class="tokens-slots-mini">
                        <?php for ($i = 1; $i <= $tokens_maximo; $i++): ?>
                            <div class="token-slot <?= $i <= $tokens_disponibles ? 'token-lleno' : 'token-vacio' ?>">
                                <?= $i <= $tokens_disponibles ? '★' : '○' ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <p class="tokens-info"><?= $tokens_disponibles ?> de <?= $tokens_maximo ?> disponibles</p>
                </div>
            </a>
        </div>

        <h2 class="titulo-seccion">PRÓXIMAS CLASES</h2>

        <?php if (empty($proximas_clases)): ?>
            <p class="vacio">No tienes clases programadas próximamente.</p>
        <?php else: ?>
            <div class="lista-clases">
                <?php foreach ($proximas_clases as $clase): ?>
                    <div class="fila-clase">
                        <div class="caja-fecha">
                            <p class="dia-semana <?= es_hoy($clase['fecha']) ? 'hoy' : '' ?>"><?= dia_semana_corto($clase['fecha']) ?></p>
                            <p class="dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></p>
                            <p class="mes"><?= mes_corto($clase['fecha']) ?></p>
                        </div>
                        <div class="info-clase">
                            <p class="nombre-clase"><?= tipo_clase($clase['grupo_tipo']) ?></p>
                            <p class="detalles-clase"><?= substr($clase['hora_inicio'], 0, 5) ?> – <?= substr($clase['hora_fin'], 0, 5) ?></p>
                        </div>
                        <div class="badge-zona">
                            <?php if ($clase['asistencia_id']): ?>
                                <span class="badge badge-avisada">AUSENCIA AVISADA</span>
                                <form action="<?= BASE_URL ?>/alumno/anular-ausencia" method="POST" class="form-inline" id="form-anular-<?= $clase['asistencia_id'] ?>">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="asistencia_id" value="<?= $clase['asistencia_id'] ?>">
                                    <button type="button" class="badge badge-cancelar" onclick="confirmarAnular(<?= $clase['asistencia_id'] ?>)">Cancelar</button>
                                </form>
                            <?php elseif (es_hoy($clase['fecha'])): ?>
                                <form action="<?= BASE_URL ?>/alumno/avisar-ausencia" method="POST" class="form-inline" id="form-avisar-<?= $clase['id'] ?>">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                    <button type="button" class="badge badge-avisar" onclick="confirmarAvisarHoy(<?= $clase['id'] ?>)">AVISAR AUSENCIA</button>
                                </form>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>/alumno/avisar-ausencia" method="POST" class="form-inline" id="form-avisar-<?= $clase['id'] ?>">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                    <button type="button" class="badge badge-avisar" onclick="confirmarAvisar(<?= $clase['id'] ?>)">AVISAR AUSENCIA</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="franja-roja"></div>

    <script>
// ─── Pop-ups de resultado ───────────────────────────────────────────────────
        <?php if ($mensaje === 'avisado_token'): ?>
        Swal.fire({ icon: 'success', title: 'Ausencia registrada', text: 'Has ganado 1 token de recuperación.', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'avisado_sin_token'): ?>
        Swal.fire({ icon: 'warning', title: 'Ausencia registrada', text: 'No genera token porque faltan menos de 24 horas.', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'avisado_sin_token_maximo'): ?>
        Swal.fire({ icon: 'warning', title: 'Ausencia registrada', text: 'No genera token porque ya tienes el máximo (4).', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'ya_avisado'): ?>
        Swal.fire({ icon: 'info', title: 'Ya avisado', text: 'Ya habías avisado de esta ausencia anteriormente.', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'anulado'): ?>
        Swal.fire({ icon: 'success', title: 'Aviso cancelado', text: 'El aviso de ausencia se ha cancelado correctamente.', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'anulado_con_token'): ?>
        Swal.fire({ icon: 'warning', title: 'Aviso cancelado', text: 'Se ha eliminado el token asociado. Si había una recuperación que dependía de ese token, también se ha cancelado.', confirmButtonColor: '#DC2626' });
        <?php elseif ($mensaje === 'error_datos'): ?>
        Swal.fire({ icon: 'warning', title: 'Acción no completada', text: 'Vuelve a intentarlo desde el formulario.', confirmButtonColor: '#DC2626' });
        <?php endif; ?>

// ─── Confirmaciones ─────────────────────────────────────────────────────────
        function confirmarAvisar(claseId) {
            Swal.fire({
                icon: 'question',
                title: '¿Avisar de ausencia?',
                showCancelButton: true,
                confirmButtonText: 'Sí, avisar',
                cancelButtonText: 'No',
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#999'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('form-avisar-' + claseId).submit();
            });
        }

        function confirmarAvisarHoy(claseId) {
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
                if (result.isConfirmed) document.getElementById('form-avisar-' + claseId).submit();
            });
        }

        function confirmarAnular(asistenciaId) {
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
                if (result.isConfirmed) document.getElementById('form-anular-' + asistenciaId).submit();
            });
        }
    </script>
</body>
</html>
