<?php
// =============================================================================
// Vista: Control de asistencia del profesor (página autónoma).
//
// Variables disponibles desde ProfesorController::asistencia():
//   $proximasClases    Listado paginado de próximas clases programadas (tarjetas).
//   $clasesPasadas     Últimas clases pasadas (acceso rápido a su pase de lista).
//   $detalleClase      Si se está editando una clase concreta, sus datos; null si no.
//   $alumnos           Alumnos del grupo de $detalleClase (para el pase de lista).
//   $registros         Estado actual de cada alumno (alumno_id => 'asiste'|'avisado'|'ausente').
//   $avisos            Información de aviso/recuperación por alumno_id.
//   $resumen           ['asiste' => n, 'avisado' => n, 'ausente' => n].
//   $mensaje           Aviso superior.
//   $pagina            Página actual de las tarjetas (1..n).
//   $porPagina         Nº de tarjetas por página.
//   $csrfToken         Token CSRF para el formulario de pase de lista.
// =============================================================================

$vista = 'asistencia';
require __DIR__ . '/_header.php';

// Iconos por tipo de grupo (valores reales del ENUM en BBDD).
$iconosGrupo = [
    'teatro'        => ['icono' => '&#127916;', 'etiqueta' => 'Teatro'],         // 🎬
    'improvisacion' => ['icono' => '&#9889;',   'etiqueta' => 'Improvisación'],  // ⚡
    'actuacion'     => ['icono' => '&#127917;', 'etiqueta' => 'Actuación'],      // 🎭
    'danza'         => ['icono' => '&#128131;', 'etiqueta' => 'Danza'],          // 💃
    'canto'         => ['icono' => '&#127908;', 'etiqueta' => 'Canto'],          // 🎤
];
$iconoPara = function (string $tipo) use ($iconosGrupo) {
    return $iconosGrupo[$tipo] ?? ['icono' => '&#127914;', 'etiqueta' => ucfirst($tipo)];
};
$diasCortos = ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'];

$pagina    = $pagina    ?? 1;
$porPagina = $porPagina ?? 4;
$csrfToken = $csrfToken ?? '';

// Tipo de asistencia que está mostrando la vista: clase o evento.
$tipo = $tipo ?? 'clase';

// Unifica el detalle para no depender del origen.
$detalleAsistencia = $detalleAsistencia ?? null;

?>

            <?php if (!$detalleAsistencia): ?>
                <div class="etiqueta">CONTROL DE ASISTENCIA</div>
                <h1>Tus próximas clases</h1>

                <section class="profesor-seccion">
                    <div class="titulo-seccion">Próximas clases programadas</div>
                    <?php if (empty($proximasClases)): ?>
                        <p class="vacio">No tienes clases programadas.</p>
                    <?php else: ?>
                        <div class="asist-grid">
                            <?php foreach ($proximasClases as $clase): ?>
                                <?php $info = $iconoPara($clase['grupo_tipo']); ?>
                                <a class="asist-card" href="<?= BASE_URL ?>/profesor/asistencia?clase_id=<?= (int)$clase['id'] ?>">
                                    <div class="asist-card-icono">
                                        <span class="asist-emoji" aria-hidden="true"><?= $info['icono'] ?></span>
                                    </div>
                                    <div class="asist-card-fecha">
                                        <span class="asist-dia-texto"><?= $diasCortos[(int)date('N', strtotime($clase['fecha'])) - 1] ?? '' ?></span>
                                        <span class="asist-dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></span>
                                        <span class="asist-mes"><?= strtoupper(date('M', strtotime($clase['fecha']))) ?></span>
                                    </div>

                                    <div class="asist-card-grupo">
                                        <span class="asist-grupo-nombre"><?= htmlspecialchars($clase['grupo_nombre']) ?></span>
                                        <span class="asist-grupo-indicadores">
                                            <span class="asist-chip-alumnos" title="<?= (int)$clase['total_alumnos'] ?> alumnos en el grupo">
                                                <span aria-hidden="true">&#128101;</span> <?= (int)$clase['total_alumnos'] ?>
                                            </span>
                                            <?php if ((int)$clase['total_confirmados'] > 0): ?>
                                                <span class="asist-chip-confirmados" title="<?= (int)$clase['total_confirmados'] ?> confirman asistencia">
                                                    <span aria-hidden="true">&#10003;</span> <?= (int)$clase['total_confirmados'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ((int)$clase['total_avisos'] > 0): ?>
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
                                        <span title="Hora"><span aria-hidden="true">&#128336;</span> <?= substr($clase['hora_inicio'], 0, 5) ?></span>
                                        <span title="Sala"><span aria-hidden="true">&#128205;</span> <?= htmlspecialchars($clase['sala_nombre'] ?? '-') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <nav class="paginacion-clases-pro" aria-label="Paginación de próximas clases">
                            <?php if ($pagina > 1): ?>
                                <a class="pag-btn" href="<?= BASE_URL ?>/profesor/asistencia?pagina=<?= $pagina - 1 ?>">
                                    &larr; Anterior
                                </a>
                            <?php else: ?>
                                <span class="pag-btn pag-disabled" aria-disabled="true">&larr; Anterior</span>
                            <?php endif; ?>

                            <span class="pag-numero">
                                Página <?= (int)$pagina ?>
                            </span>

                            <?php if (count($proximasClases) === $porPagina): ?>
                                <a class="pag-btn" href="<?= BASE_URL ?>/profesor/asistencia?pagina=<?= $pagina + 1 ?>">
                                    Siguiente &rarr;
                                </a>
                            <?php else: ?>
                                <span class="pag-btn pag-disabled" aria-disabled="true">Siguiente &rarr;</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </section>

            <?php elseif ($tipo === 'clase' && $detalleClase): ?>
                <?php $info = $iconoPara($detalleClase['grupo_tipo']); ?>
                <section class="asist-hero">
                    <div class="asist-hero-icono" aria-hidden="true"><?= $info['icono'] ?></div>
                    <div class="asist-hero-info">
                        <div class="asist-hero-etiqueta"><?= htmlspecialchars($info['etiqueta']) ?></div>
                        <div class="asist-hero-titulo"><?= htmlspecialchars($detalleClase['grupo_nombre']) ?></div>
                        <div class="asist-hero-meta">
                            <span><span aria-hidden="true">&#128197;</span> <?= date('d/m/Y', strtotime($detalleClase['fecha'])) ?></span>
                            <span><span aria-hidden="true">&#128336;</span> <?= substr($detalleClase['hora_inicio'], 0, 5) ?> - <?= substr($detalleClase['hora_fin'], 0, 5) ?></span>
                            <span><span aria-hidden="true">&#128205;</span> <?= htmlspecialchars($detalleClase['sala_nombre'] ?? '-') ?></span>
                        </div>
                    </div>
                    <a class="asist-volver" href="<?= BASE_URL ?>/profesor">&larr; Volver</a>
                </section>

                <section class="asist-resumen">
                    <div class="asist-resumen-item asist-resumen-presentes">
                        <div class="asist-resumen-icono" aria-hidden="true">&#9989;</div>
                        <div class="asist-resumen-numero" data-resumen="asiste"><?= $resumen['asiste'] ?></div>
                        <div class="asist-resumen-texto">Asisten</div>
                    </div>
                    <div class="asist-resumen-item asist-resumen-avisados">
                        <div class="asist-resumen-icono" aria-hidden="true">&#128683;</div>
                        <div class="asist-resumen-numero" data-resumen="avisado"><?= $resumen['avisado'] ?></div>
                        <div class="asist-resumen-texto">No vendrán</div>
                    </div>
                    <div class="asist-resumen-item asist-resumen-ausentes">
                        <div class="asist-resumen-icono" aria-hidden="true">&#10060;</div>
                        <div class="asist-resumen-numero" data-resumen="ausente"><?= $resumen['ausente'] ?></div>
                        <div class="asist-resumen-texto">Ausentes</div>
                    </div>
                    <div class="asist-resumen-item asist-resumen-total">
                        <div class="asist-resumen-icono" aria-hidden="true">&#128101;</div>
                        <div class="asist-resumen-numero" data-resumen="total"><?= count($alumnos) ?></div>
                        <div class="asist-resumen-texto">Total</div>
                    </div>
                </section>

                <?php
                $avisadosLista = [];
                foreach ($alumnos as $alumno) {
                    if (($registros[(int)$alumno['id']] ?? '') === 'avisado') {
                        $avisadosLista[] = $alumno;
                    }
                }
                ?>

                <?php if (!empty($avisadosLista)): ?>
                    <section class="profesor-seccion asist-seccion-avisados">
                        <div class="titulo-seccion">
                            <span aria-hidden="true">&#128683;</span>
                            No asistirán a esta clase (<?= count($avisadosLista) ?>)
                        </div>
                        <div class="asist-chips">
                            <?php foreach ($avisadosLista as $alumno): ?>
                                <?php
                                    $aid = (int)$alumno['id'];
                                    $avisoInfo = $avisos[$aid] ?? null;
                                    $fechaAvisoTxt = $avisoInfo && $avisoInfo['fecha_aviso']
                                        ? date('d/m H:i', strtotime($avisoInfo['fecha_aviso']))
                                        : null;
                                    $conToken = $avisoInfo && !empty($avisoInfo['aviso_valido']);
                                ?>
                                <div class="asist-chip asist-chip-avisado">
                                    <div class="asist-chip-avatar" aria-hidden="true">&#128683;</div>
                                    <div class="asist-chip-info">
                                        <div class="asist-chip-nombre"><?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?></div>
                                        <?php if ($fechaAvisoTxt): ?>
                                            <div class="asist-chip-extra">
                                                <span title="Fecha del aviso"><span aria-hidden="true">&#128340;</span> <?= $fechaAvisoTxt ?></span>
                                                <?php if ($conToken): ?>
                                                    <span class="asist-chip-token" title="Aviso válido (+24h) - generó token de recuperación" aria-label="Tiene token de recuperación">&#127915;</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>/profesor/asistencia">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="clase_id" value="<?= (int)$detalleClase['id'] ?>">
                    <section class="profesor-seccion">
                        <div class="cabecera-pase-lista">
                            <div class="titulo-seccion titulo-seccion-sin-margen">Pase de lista</div>
                            <?php if (!empty($alumnos)): ?>
                                <div class="atajos-pase-lista" role="group" aria-label="Marcar todos">
                                    <button type="button" class="badge badge-secundario" data-marcar-todos="asiste">
                                        <span aria-hidden="true">&#10003;</span> Marcar todos como asisten
                                    </button>
                                    <button type="button" class="badge badge-cancelar" data-marcar-todos="ausente">
                                        Limpiar marcas
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($alumnos)): ?>
                            <p class="vacio">No hay alumnos activos en este grupo.</p>
                        <?php else: ?>
                            <div class="asist-lista">
                                <?php foreach ($alumnos as $alumno): ?>
                                    <?php
                                        $aid = (int)$alumno['id'];
                                        // Por defecto todos los alumnos del grupo asisten.
                                        // Si avisaron ('avisado') o el profesor ya marcó otro estado, prevalece.
                                        $estadoActual = $registros[$aid] ?? 'asiste';
                                        $avisoInfoFila = $avisos[$aid] ?? null;
                                        $tieneTokenFila = $avisoInfoFila && !empty($avisoInfoFila['aviso_valido']);
                                        $fechaAvisoFila = $avisoInfoFila && !empty($avisoInfoFila['fecha_aviso'])
                                            ? date('d/m H:i', strtotime($avisoInfoFila['fecha_aviso']))
                                            : null;
                                    ?>
                                    <div class="asist-fila asist-fila-<?= htmlspecialchars($estadoActual) ?><?= $tieneTokenFila ? ' asist-fila-con-token' : '' ?>">
                                        <div class="asist-fila-nombre">
                                            <?php if ($estadoActual === 'asiste'): ?>
                                                <span class="asist-indicador asist-indicador-ok" title="Confirma asistencia" aria-hidden="true">&#10003;</span>
                                            <?php elseif ($estadoActual === 'avisado'): ?>
                                                <span class="asist-indicador asist-indicador-no" title="No asistirá" aria-hidden="true">&#10007;</span>
                                            <?php else: ?>
                                                <span class="asist-indicador asist-indicador-pendiente" title="Sin confirmar" aria-hidden="true">&#9675;</span>
                                            <?php endif; ?>
                                            <span class="asist-fila-texto"><?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?></span>
                                            <?php $tokDispon = (int)($alumno['tokens_disponibles'] ?? 0); ?>
                                            <?php if ($tokDispon > 0): ?>
                                                <span class="asist-token-badge" title="<?= $tokDispon ?> token<?= $tokDispon === 1 ? '' : 's' ?> disponible<?= $tokDispon === 1 ? '' : 's' ?> para recuperar clase">
                                                    <span aria-hidden="true">&#127915;</span> <?= $tokDispon ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="asist-fila-botones" role="radiogroup" aria-label="Estado de <?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>">
                                            <label class="asist-boton asist-boton-asiste<?= $estadoActual === 'asiste' ? ' activo' : '' ?>" title="Asiste">
                                                <input type="radio" name="filas[<?= $aid ?>][estado]" value="asiste"<?= $estadoActual === 'asiste' ? ' checked' : '' ?>>
                                                <span aria-hidden="true">&#10003;</span>
                                                <span class="visualmente-oculto">Asiste</span>
                                            </label>
                                            <label class="asist-boton asist-boton-avisado<?= $estadoActual === 'avisado' ? ' activo' : '' ?>" title="Avisado (no vendrá)">
                                                <input type="radio" name="filas[<?= $aid ?>][estado]" value="avisado"<?= $estadoActual === 'avisado' ? ' checked' : '' ?>>
                                                <span aria-hidden="true">&#128683;</span>
                                                <span class="visualmente-oculto">Avisado, no vendrá</span>
                                            </label>
                                            <label class="asist-boton asist-boton-ausente<?= $estadoActual === 'ausente' ? ' activo' : '' ?>" title="Ausente sin avisar">
                                                <input type="radio" name="filas[<?= $aid ?>][estado]" value="ausente"<?= $estadoActual === 'ausente' ? ' checked' : '' ?>>
                                                <span aria-hidden="true">&#10007;</span>
                                                <span class="visualmente-oculto">Ausente sin avisar</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="acciones-barra">
                                <button type="submit" class="boton-principal boton-profesor">
                                    <span aria-hidden="true">&#128190;</span> Guardar
                                </button>
                            </div>
                        <?php endif; ?>
                    </section>
                </form>


<?php elseif ($tipo === 'evento' && $detalleEvento): ?>

    <section class="asist-hero">
        <div class="asist-hero-icono" aria-hidden="true">🎭</div>

        <div class="asist-hero-info">
            <div class="asist-hero-etiqueta">
                Evento especial
            </div>

            <div class="asist-hero-titulo">
                <?= htmlspecialchars($detalleEvento['nombre']) ?>
            </div>

            <div class="asist-hero-meta">
                <span>
                    📅 <?= date('d/m/Y', strtotime($detalleEvento['fecha'])) ?>
                </span>

                <?php if (!empty($detalleEvento['hora'])): ?>
                    <span>
                        🕒 <?= htmlspecialchars(substr($detalleEvento['hora'], 0, 5)) ?>
                    </span>
                <?php endif; ?>

                <span>
                    👥 <?= count($alumnos) ?> alumnos inscritos
                </span>
            </div>
        </div>

        <a class="asist-volver" href="<?= BASE_URL ?>/profesor">
    &larr; Volver
</a>
    </section>

   <?php require __DIR__ . '/_pase_lista.php'; ?>


            <?php else: ?>
                <section class="profesor-seccion">
                    <p class="vacio">No se ha podido cargar esa clase o no tienes permiso para verla.</p>
                    <div class="acciones-barra"><a class="boton-principal boton-profesor" href="<?= BASE_URL ?>/profesor/asistencia">&larr; Volver</a></div>
                </section>
            <?php endif; ?>

<?php if ($detalleClase && !empty($alumnos)): ?>
<script>
// Pase de lista interactivo:
//   - Cambia el color de la fila al instante (verde = asiste, rojo = avisado, gris tachado = ausente).
//   - Recalcula el resumen (Asisten / No vendrán / Ausentes / Total) en vivo.
(function () {
    var lista = document.querySelector('.asist-lista');
    if (!lista) return;
    var estados = ['asiste', 'avisado', 'ausente'];

    function recontarResumen() {
        var contador = { asiste: 0, avisado: 0, ausente: 0 };
        lista.querySelectorAll('.asist-fila').forEach(function (fila) {
            var marcado = fila.querySelector('input[type=radio]:checked');
            var valor = marcado ? marcado.value : 'asiste';
            if (contador.hasOwnProperty(valor)) contador[valor]++;
        });
        var total = contador.asiste + contador.avisado + contador.ausente;
        document.querySelectorAll('[data-resumen]').forEach(function (n) {
            var clave = n.getAttribute('data-resumen');
            if (clave === 'total') {
                n.textContent = total;
            } else if (contador.hasOwnProperty(clave)) {
                n.textContent = contador[clave];
            }
        });
    }

    function actualizarFila(input) {
        var fila = input.closest('.asist-fila');
        if (!fila) return;
        estados.forEach(function (e) { fila.classList.remove('asist-fila-' + e); });
        fila.classList.add('asist-fila-' + input.value);
        var ind = fila.querySelector('.asist-indicador');
        if (ind) {
            ind.classList.remove('asist-indicador-ok', 'asist-indicador-no', 'asist-indicador-pendiente');
            if (input.value === 'asiste') {
                ind.classList.add('asist-indicador-ok');
                ind.innerHTML = '&#10003;';
                ind.title = 'Confirma asistencia';
            } else if (input.value === 'avisado') {
                ind.classList.add('asist-indicador-no');
                ind.innerHTML = '&#10007;';
                ind.title = 'No asistirá';
            } else {
                ind.classList.add('asist-indicador-pendiente');
                ind.innerHTML = '&#9675;';
                ind.title = 'Ausente sin avisar';
            }
        }
        fila.querySelectorAll('.asist-boton').forEach(function (btn) {
            var radio = btn.querySelector('input[type=radio]');
            if (!radio) return;
            btn.classList.toggle('activo', radio.checked);
        });
    }

    // Guardamos el estado inicial de cada fila para poder revertir si se cancela la confirmación.
    var estadosPrevios = new WeakMap();
    lista.querySelectorAll('.asist-fila').forEach(function (fila) {
        var ch = fila.querySelector('input[type=radio]:checked');
        estadosPrevios.set(fila, ch ? ch.value : 'asiste');
    });

    function nombreDe(fila) {
        var n = fila.querySelector('.asist-fila-texto');
        return n ? (n.textContent || '').trim() : 'este alumno';
    }

    var textosEstado = {
        'asiste':  { titulo: '¿Marcar como asistente?', icon: 'question', ok: 'Sí, asistirá',  color: '#16A34A',
                     mensaje: function (n) { return n + ' aparecerá como que asiste a la clase.'; } },
        'avisado': { titulo: '¿Marcar como avisado?',   icon: 'info',     ok: 'Sí, avisó',     color: '#F59E0B',
                     mensaje: function (n) { return n + ' avisó de que no podrá asistir.'; } },
        'ausente': { titulo: '¿Marcar como ausente?',   icon: 'warning',  ok: 'Sí, ausente',   color: '#DC2626',
                     mensaje: function (n) { return n + ' no se presentó sin avisar.'; } }
    };

    lista.addEventListener('change', function (ev) {
        var input = ev.target;
        if (!input || input.type !== 'radio') return;
        var fila = input.closest('.asist-fila');
        if (!fila) return;

        // Si el cambio viene del atajo "Marcar todos", saltar confirmación.
        if (fila.dataset.skipConfirm === '1') {
            estadosPrevios.set(fila, input.value);
            actualizarFila(input);
            recontarResumen();
            return;
        }

        var cfg = textosEstado[input.value];
        if (!cfg || typeof Swal === 'undefined') {
            estadosPrevios.set(fila, input.value);
            actualizarFila(input);
            recontarResumen();
            return;
        }

        var nombre = nombreDe(fila);
        Swal.fire({
            title: cfg.titulo,
            text: cfg.mensaje(nombre),
            icon: cfg.icon,
            showCancelButton: true,
            confirmButtonText: cfg.ok,
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            confirmButtonColor: cfg.color,
            cancelButtonColor: '#6B7280',
            focusCancel: true
        }).then(function (res) {
            if (res.isConfirmed) {
                estadosPrevios.set(fila, input.value);
                actualizarFila(input);
                recontarResumen();
            } else {
                // Revertir al estado anterior.
                var previo = estadosPrevios.get(fila) || 'asiste';
                var radioPrevio = fila.querySelector('input[type=radio][value="' + previo + '"]');
                if (radioPrevio) {
                    radioPrevio.checked = true;
                    actualizarFila(radioPrevio);
                }
            }
        });
    });

    // Atajos: marcar todos como asisten / limpiar todas las marcas (sin preguntar uno a uno).
    document.querySelectorAll('[data-marcar-todos]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var valor = btn.getAttribute('data-marcar-todos');
            if (!valor) return;
            lista.querySelectorAll('.asist-fila').forEach(function (fila) {
                // No tocar alumnos que ya están como avisados (han avisado expresamente).
                var radioAvisado = fila.querySelector('input[type=radio][value="avisado"]');
                if (radioAvisado && radioAvisado.checked) return;
                var radio = fila.querySelector('input[type=radio][value="' + valor + '"]');
                if (radio) {
                    radio.checked = true;
                    estadosPrevios.set(fila, valor);
                    actualizarFila(radio);
                }
            });
            recontarResumen();
        });
    });

    recontarResumen();
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
