<?php
function dia_inv_upper($f) { $d=['DOM','LUN','MAR','MIE','JUE','VIE','SAB']; return $d[date('w',strtotime($f))]; }
function mes_inv_upper($f) { $m=['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC']; return $m[(int)date('n',strtotime($f))-1]; }
function dia_inv($f) { $d=['dom','lun','mar','mié','jue','vie','sáb']; return $d[date('w',strtotime($f))]; }
function fecha_inv($f) { $m=['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']; return date('j',strtotime($f)).' '.$m[(int)date('n',strtotime($f))-1]; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clase de prueba | Gestor Escuela</title>
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
            <span class="enlace-nav activo">Mi clase de prueba</span>
            <a href="<?= BASE_URL ?>/invitado/calendario" class="enlace-nav">Calendario</a>
        </nav>
        <div class="cabecera-bloque cabecera-bloque-derecha">
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido">

        <p class="etiqueta">HOLA DE NUEVO</p>
        <h1><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h1>

        <?php if ($mensaje === 'reservado'): ?>
            <div class="aviso aviso-exito">✅ ¡Clase de prueba reservada! Te esperamos.</div>
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

        <!-- CASO 1: Ya tiene una clase reservada -->
        <?php if ($clase_reservada): ?>

            <div class="invitado-reserva">
                <div class="invitado-icono">🎭</div>
                <h2>Tu clase de prueba</h2>
                <p class="invitado-fecha">
                    <?= dia_inv($clase_reservada['fecha']) ?>
                    <?= fecha_inv($clase_reservada['fecha']) ?>
                </p>
                <p class="invitado-grupo"><?= htmlspecialchars($clase_reservada['grupo_nombre']) ?></p>
                <p class="invitado-detalle">
                    <?= substr($clase_reservada['hora_inicio'], 0, 5) ?> – <?= substr($clase_reservada['hora_fin'], 0, 5) ?>
                    · <?= htmlspecialchars($clase_reservada['profesor'] ?? '') ?>
                    · <?= htmlspecialchars($clase_reservada['sala'] ?? '') ?>
                </p>

                <div class="invitado-info">
                    <p>Llega unos minutos antes para presentarte al profesor y al grupo. No necesitas traer nada especial, solo ganas de pasarlo bien.</p>
                </div>

                <form action="<?= BASE_URL ?>/invitado/cancelar" method="POST" id="form-cancelar-prueba">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="recuperacion_id" value="<?= $clase_reservada['recuperacion_id'] ?>">
                    <button type="button" class="invitado-btn-cancelar" onclick="confirmarCambiar()">Cambiar de clase</button>
                </form>
            </div>

        <!-- CASO 2: Tiene token pero no ha reservado -->
        <?php elseif ($token_disponible): ?>

            <div class="invitado-bienvenida">
                <div class="invitado-icono">🎭</div>
                <h2>Tienes 1 clase de prueba gratuita</h2>
                <p>Elige una clase de improvisación de la lista para reservar tu sitio. Puedes cambiar de clase en cualquier momento antes de que empiece.</p>
            </div>

            <h2 class="titulo-seccion">CLASES DISPONIBLES</h2>

            <?php if (empty($clases_disponibles)): ?>
                <p class="vacio">No hay clases disponibles en este momento.</p>
            <?php else: ?>
                <div class="lista-clases">
                    <?php foreach ($clases_disponibles as $clase): ?>
                        <?php
                        $ocupadas = (int)$clase['inscritos'] + (int)$clase['recuperaciones'];
                        $libres = (int)$clase['cupo_maximo'] - $ocupadas;
                        $hay_plazas = $libres > 0;
                        ?>
                        <div class="fila-clase">
                            <div class="caja-fecha">
                                <p class="dia-semana"><?= dia_inv_upper($clase['fecha']) ?></p>
                                <p class="dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></p>
                                <p class="mes"><?= mes_inv_upper($clase['fecha']) ?></p>
                            </div>
                         <div class="info-clase">
                                <p class="nombre-clase">
                                    <?= htmlspecialchars($clase['grupo_nombre']) ?>
                                    · <?= substr($clase['hora_inicio'], 0, 5) ?> – <?= substr($clase['hora_fin'], 0, 5) ?>
                                </p>
                                <p class="detalles-clase">
                                    <?= htmlspecialchars($clase['profesor'] ?? '') ?>
                                    · <?= htmlspecialchars($clase['sala'] ?? '') ?>
                                    · <span class="<?= $hay_plazas ? 'plazas-ok' : 'plazas-llenas' ?>"><?= $hay_plazas ? "$libres plaza(s)" : 'Completa' ?></span>
                                </p>
                            </div>
                            <div class="badge-zona">
                                <?php if (!$hay_plazas): ?>
                                    <span class="badge badge-llena">COMPLETA</span>
                                <?php else: ?>
                                    <form action="<?= BASE_URL ?>/invitado/reservar" method="POST" class="form-inline" id="form-probar-<?= $clase['id'] ?>">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                        <button type="button" class="badge badge-recuperar" onclick="confirmarProbar(<?= $clase['id'] ?>)">PROBAR ESTA CLASE</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <!-- CASO 3: Ya usó su token -->
        <?php else: ?>

            <div class="invitado-bienvenida">
                <div class="invitado-icono">✓</div>
                <h2>Ya has realizado tu clase de prueba</h2>
                <p>Si quieres seguir viniendo, contacta con la escuela para matricularte. Estaremos encantados de tenerte en el elenco.</p>
                <div class="invitado-info">
                    <p><strong>Contacto:</strong> info@puntodepartida.es</p>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <div class="franja-roja"></div>

    <script>
    <?php if ($mensaje === 'reservado'): ?>
    Swal.fire({ icon: 'success', title: '¡Clase reservada!', text: 'Te esperamos. Llega unos minutos antes para presentarte.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'cancelado'): ?>
    Swal.fire({ icon: 'success', title: 'Reserva cancelada', text: 'Puedes elegir otra clase de la lista.', confirmButtonColor: '#0F6E56' });
    <?php elseif ($mensaje === 'sin_plazas'): ?>
    Swal.fire({ icon: 'warning', title: 'Sin plazas', text: 'Esa clase ya no tiene plazas disponibles.', confirmButtonColor: '#DC2626' });
    <?php elseif ($mensaje === 'error_datos'): ?>
    Swal.fire({ icon: 'warning', title: 'Acción no completada', text: 'Vuelve a intentarlo desde el formulario.', confirmButtonColor: '#DC2626' });
    <?php endif; ?>

    function confirmarProbar(claseId) {
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
            if (result.isConfirmed) document.getElementById('form-probar-' + claseId).submit();
        });
    }

    function confirmarCambiar() {
        Swal.fire({
            icon: 'question',
            title: '¿Cambiar de clase?',
            text: 'Tu reserva actual se cancelará y podrás elegir otra.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'No',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#999'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-cancelar-prueba').submit();
        });
    }
    </script>
</body>
</html>
