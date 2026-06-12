<?php
function dia_semana_corto_t($f) { $d=['dom','lun','mar','mié','jue','vie','sáb']; return $d[date('w',strtotime($f))]; }
function fecha_legible($f) { $m=['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']; return date('j',strtotime($f)).' '.$m[(int)date('n',strtotime($f))-1]; }
function tiempo_relativo($f) { $d=time()-strtotime($f); if($d<60)return'ahora'; if($d<3600)return'hace '.floor($d/60).' min'; if($d<86400)return'hace '.floor($d/3600).'h'; $dias=floor($d/86400); if($dias===1)return'ayer'; if($dias<30)return"hace $dias días"; return fecha_legible($f); }
function tipo_clase_t($t) { $m=['teatro'=>'Teatro','improvisacion'=>'Improvisación','voz'=>'Voz','expresion_corporal'=>'Expresión Corporal','intensivo'=>'Intensivo']; return $m[$t]??$t; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis tokens | Gestor Escuela</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
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
            <a href="<?= BASE_URL ?>/alumno/recuperar" class="enlace-nav">Recuperar</a>
            <span class="enlace-nav activo">Tokens</span>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido">
        <p class="etiqueta">MIS CRÉDITOS DE RECUPERACIÓN</p>
        <h1>Tokens</h1>

        <div class="tokens-panel">
            <p class="tokens-titulo">TU SALDO ACTUAL</p>
            <div class="tokens-slots">
                <?php for ($i = 1; $i <= $tokens_maximo; $i++): ?>
                    <div class="token-slot <?= $i <= $tokens_disponibles ? 'token-lleno' : 'token-vacio' ?>">
                        <?= $i <= $tokens_disponibles ? '★' : '○' ?>
                    </div>
                <?php endfor; ?>
            </div>
            <p class="tokens-info"><strong><?= $tokens_disponibles ?></strong> de <?= $tokens_maximo ?> disponibles</p>
            <p class="tokens-caducidad">Los tokens no utilizados caducan el <strong>30 de junio de 2026</strong></p>
        </div>

        <h2 class="titulo-seccion">¿CÓMO FUNCIONAN?</h2>
        <div class="pasos">
            <div class="paso">
                <div class="paso-numero">1</div>
                <div class="paso-contenido">
                    <p class="paso-titulo">Avisas de una ausencia</p>
                    <p class="paso-desc">Si avisas con más de 24 horas de antelación, ganas 1 token automáticamente.</p>
                </div>
            </div>
            <div class="paso">
                <div class="paso-numero">2</div>
                <div class="paso-contenido">
                    <p class="paso-titulo">Acumulas hasta 4 tokens</p>
                    <p class="paso-desc">Puedes tener un máximo de 4 tokens a la vez. Si ya tienes 4, los avisos no generarán más.</p>
                </div>
            </div>
            <div class="paso">
                <div class="paso-numero">3</div>
                <div class="paso-contenido">
                    <p class="paso-titulo">Recuperas una clase de improvisación</p>
                    <p class="paso-desc">Usa 1 token para apuntarte a cualquier clase de impro con plazas libres.</p>
                </div>
            </div>
        </div>

        <div class="info-extra">
            <strong>Ten en cuenta:</strong> si cancelas un aviso de ausencia que generó un token, el token se elimina. Si ese token ya se usó en una recuperación, la recuperación también se cancela automáticamente.
        </div>

        <h2 class="titulo-seccion" style="margin-top:36px;">HISTORIAL DE MOVIMIENTOS</h2>

        <?php if (empty($historial)): ?>
            <p class="vacio">Todavía no tienes movimientos de tokens.</p>
        <?php else: ?>
            <div class="lista-historial">
                <?php foreach ($historial as $mov): ?>
                    <div class="fila-historial">
                        <?php if (!$mov['usado'] && !$mov['recuperacion_id']): ?>
                            <div class="historial-icono icono-ganado">+</div>
                        <?php elseif ($mov['usado']): ?>
                            <div class="historial-icono icono-usado">−</div>
                        <?php else: ?>
                            <div class="historial-icono icono-ganado">+</div>
                        <?php endif; ?>

                        <div class="historial-info">
                            <p class="historial-titulo">
                                <?php if ($mov['usado']): ?>
                                    Token usado
                                <?php else: ?>
                                    Token disponible
                                <?php endif; ?>
                            </p>
                            <p class="historial-detalle">
                                Ausencia: <?= dia_semana_corto_t($mov['clase_fecha']) ?> <?= fecha_legible($mov['clase_fecha']) ?>, <?= substr($mov['clase_hora'], 0, 5) ?>
                                · <?= tipo_clase_t($mov['grupo_tipo']) ?>
                                <?php if ($mov['recuperacion_id'] && $mov['recup_fecha']): ?>
                                    <br>Recuperación: <?= dia_semana_corto_t($mov['recup_fecha']) ?> <?= fecha_legible($mov['recup_fecha']) ?>, <?= substr($mov['recup_hora'], 0, 5) ?>
                                    · <?= htmlspecialchars($mov['recup_grupo']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <p class="historial-tiempo"><?= tiempo_relativo($mov['fecha_generacion']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="volver">
            <a href="<?= BASE_URL ?>/alumno">← Volver a mis clases</a>
        </div>
    </main>

    <div class="franja-roja"></div>
</body>
</html>
