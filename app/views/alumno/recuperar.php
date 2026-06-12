<?php
function dia_semana_corto_r($f) { $d=['DOM','LUN','MAR','MIE','JUE','VIE','SAB']; return $d[date('w',strtotime($f))]; }
function mes_corto_r($f) { $m=['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC']; return $m[(int)date('n',strtotime($f))-1]; }
function tipo_clase_r($t) { $m=['teatro'=>'Teatro','improvisacion'=>'Improvisación','voz'=>'Voz','expresion_corporal'=>'Expresión Corporal','intensivo'=>'Intensivo']; return $m[$t]??$t; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar clases | Gestor Escuela</title>
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
            <a href="<?= BASE_URL ?>/alumno/calendario" class="enlace-nav">Calendario</a>
            <span class="enlace-nav activo">Recuperar</span>
            <a href="<?= BASE_URL ?>/alumno/tokens" class="enlace-nav">Tokens</a>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido">
        <p class="etiqueta">CLASES DE IMPROVISACIÓN</p>
        <h1>Recuperar una clase</h1>

        <?php if ($mensaje === 'recuperado'): ?>
            <div class="aviso aviso-exito">✅ Recuperación reservada. Has gastado 1 token.</div>
        <?php elseif ($mensaje === 'recuperacion_anulada'): ?>
            <div class="aviso aviso-exito">✅ Recuperación cancelada. Te hemos devuelto 1 token.</div>
        <?php elseif ($mensaje === 'recuperacion_anulada_sin_token'): ?>
            <div class="aviso aviso-warning">⚠️ Recuperación cancelada. El token no se ha devuelto porque ya tienes el máximo (4).</div>
        <?php elseif ($mensaje === 'sin_tokens'): ?>
            <div class="aviso aviso-warning">⚠️ No tienes tokens disponibles.</div>
        <?php elseif ($mensaje === 'sin_plazas'): ?>
            <div class="aviso aviso-warning">⚠️ La clase ya no tiene plazas disponibles.</div>
        <?php elseif ($mensaje === 'error_datos'): ?>
            <div class="aviso aviso-warning">⚠️ No se pudo completar la acción. Vuelve a intentarlo.</div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/alumno/tokens" class="tokens-panel-link">
            <div class="tokens-panel">
                <div class="tokens-panel-cabecera">
                    <p class="tokens-titulo">TUS TOKENS</p>
                    <div class="tooltip-container">
                        <span class="tooltip-icono">?</span>
                        <div class="tooltip-contenido">
                            <p><strong>1.</strong> Avisa con +24h → ganas 1 token</p>
                            <p><strong>2.</strong> Acumula hasta 4 tokens</p>
                            <p><strong>3.</strong> Usa 1 token para recuperar clase de impro</p>
                        </div>
                    </div>
                </div>
                <div class="tokens-slots">
                    <?php for ($i = 1; $i <= $tokens_maximo; $i++): ?>
                        <div class="token-slot <?= $i <= $tokens_disponibles ? 'token-lleno' : 'token-vacio' ?>">
                            <?= $i <= $tokens_disponibles ? '★' : '○' ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <p class="tokens-info">
                    <strong><?= $tokens_disponibles ?></strong> de <?= $tokens_maximo ?> disponibles
                    <?php if ($tokens_disponibles === 0): ?>
                        · <span class="sin-tokens-aviso">Necesitas al menos 1 token para recuperar</span>
                    <?php endif; ?>
                </p>
            </div>
        </a>

        <h2 class="titulo-seccion">CLASES DISPONIBLES</h2>

        <?php if (empty($clases_recuperables)): ?>
            <p class="vacio">No hay clases de improvisación disponibles.</p>
        <?php else: ?>
            <div class="lista-clases">
                <?php foreach ($clases_recuperables as $clase): ?>
                    <?php
                    $ocupadas = (int)$clase['inscritos'] + (int)$clase['recuperaciones'];
                    $libres = (int)$clase['cupo_maximo'] - $ocupadas;
                    $hay_plazas = $libres > 0;
                    ?>
                    <div class="fila-clase">
                        <div class="caja-fecha">
                            <p class="dia-semana"><?= dia_semana_corto_r($clase['fecha']) ?></p>
                            <p class="dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></p>
                            <p class="mes"><?= mes_corto_r($clase['fecha']) ?></p>
                        </div>
                        <div class="info-clase">
                            <p class="nombre-clase"><?= tipo_clase_r($clase['grupo_tipo']) ?></p>
                            <p class="detalles-clase">
                                <?= substr($clase['hora_inicio'], 0, 5) ?> – <?= substr($clase['hora_fin'], 0, 5) ?>
                                · <span class="<?= $hay_plazas ? 'plazas-ok' : 'plazas-llenas' ?>"><?= $hay_plazas ? "$libres plaza(s)" : 'Completa' ?></span>
                            </p>
                        </div>
                        <div class="badge-zona">
                            <?php if ($clase['mi_recuperacion_id']): ?>
                                <span class="badge badge-recuperada">RESERVADA</span>
                                <form action="<?= BASE_URL ?>/alumno/anular-recuperacion" method="POST" class="form-inline" id="form-anular-recup-<?= $clase['mi_recuperacion_id'] ?>">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="recuperacion_id" value="<?= $clase['mi_recuperacion_id'] ?>">
                                    <button type="button" class="badge badge-cancelar" onclick="confirmarAnularRecup(<?= $clase['mi_recuperacion_id'] ?>)">Cancelar</button>
                                </form>
                            <?php elseif (!$hay_plazas): ?>
                                <span class="badge badge-llena">COMPLETA</span>
                            <?php elseif ($tokens_disponibles < 1): ?>
                                <span class="badge badge-manana">SIN TOKENS</span>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>/alumno/recuperar-clase" method="POST" class="form-inline" id="form-recuperar-<?= $clase['id'] ?>">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                    <button type="button" class="badge badge-recuperar" onclick="confirmarRecuperar(<?= $clase['id'] ?>)">RECUPERAR (1 token)</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="volver">
            <a href="<?= BASE_URL ?>/alumno">← Volver a mis clases</a>
        </div>
    </main>

    <div class="franja-roja"></div>

    <script>
    <?php if ($mensaje === 'recuperado'): ?>
    Swal.fire({ icon: 'success', title: 'Recuperación reservada', text: 'Se ha descontado 1 token.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'recuperacion_anulada'): ?>
    Swal.fire({ icon: 'success', title: 'Recuperación cancelada', text: 'Te hemos devuelto 1 token.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'recuperacion_anulada_sin_token'): ?>
    Swal.fire({ icon: 'warning', title: 'Recuperación cancelada', text: 'El token no se ha devuelto porque ya tienes el máximo de 4 tokens.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'sin_tokens'): ?>
    Swal.fire({ icon: 'warning', title: 'Sin tokens', text: 'No tienes tokens disponibles para recuperar.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'sin_plazas'): ?>
    Swal.fire({ icon: 'warning', title: 'Sin plazas', text: 'La clase ya no tiene plazas disponibles.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'error_datos'): ?>
    Swal.fire({ icon: 'warning', title: 'Acción no completada', text: 'Vuelve a intentarlo desde el formulario.', confirmButtonColor: '#DC2626' });
    <?php endif; ?>

    function confirmarRecuperar(claseId) {
        Swal.fire({
            icon: 'question',
            title: '¿Recuperar esta clase?',
            text: 'Se descontará 1 token de tu saldo.',
            showCancelButton: true,
            confirmButtonText: 'Sí, recuperar',
            cancelButtonText: 'No',
            confirmButtonColor: '#0F6E56',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-recuperar-' + claseId).submit();
        });
    }

    function confirmarAnularRecup(recuperacionId) {
        Swal.fire({
            icon: 'warning',
            title: '¿Cancelar recuperación?',
            text: 'Si ya tienes 4 tokens disponibles, el token de esta recuperación se perderá.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-anular-recup-' + recuperacionId).submit();
        });
    }
    </script>
</body>
</html>
