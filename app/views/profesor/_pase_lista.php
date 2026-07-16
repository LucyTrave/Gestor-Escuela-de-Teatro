<?php
// =============================================================================
// Parcial reutilizable: pase de lista para clases y eventos especiales.
//
// Variables necesarias:
//   $tipo                 'clase' o 'evento'.
//   $alumnos              Lista de alumnos.
//   $registros            Estado por alumno_id.
//   $avisos               Datos de aviso por alumno_id.
//   $resumen              Totales por estado.
//   $csrfToken            Token CSRF.
//   $detalleClase         Datos de la clase cuando $tipo === 'clase'.
//   $detalleEvento        Datos del evento cuando $tipo === 'evento'.
// =============================================================================

$esEvento = ($tipo === 'evento');
$idOrigen = $esEvento
    ? (int)($detalleEvento['id'] ?? 0)
    : (int)($detalleClase['id'] ?? 0);

$campoId = $esEvento ? 'evento_id' : 'clase_id';
$textoOrigen = $esEvento ? 'evento' : 'clase';
?>

<section class="asist-resumen">
    <div class="asist-resumen-item asist-resumen-presentes">
        <div class="asist-resumen-icono" aria-hidden="true">&#9989;</div>
        <div class="asist-resumen-numero" data-resumen="asiste"><?= (int)$resumen['asiste'] ?></div>
        <div class="asist-resumen-texto">Asisten</div>
    </div>

    <div class="asist-resumen-item asist-resumen-avisados">
        <div class="asist-resumen-icono" aria-hidden="true">&#128683;</div>
        <div class="asist-resumen-numero" data-resumen="avisado"><?= (int)$resumen['avisado'] ?></div>
        <div class="asist-resumen-texto">No vendrán</div>
    </div>

    <div class="asist-resumen-item asist-resumen-ausentes">
        <div class="asist-resumen-icono" aria-hidden="true">&#10060;</div>
        <div class="asist-resumen-numero" data-resumen="ausente"><?= (int)$resumen['ausente'] ?></div>
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
            No asistirán a este <?= htmlspecialchars($textoOrigen) ?> (<?= count($avisadosLista) ?>)
        </div>

        <div class="asist-chips">
            <?php foreach ($avisadosLista as $alumno): ?>
                <?php
                $aid = (int)$alumno['id'];
                $avisoInfo = $avisos[$aid] ?? null;
                $fechaAvisoTxt = $avisoInfo && !empty($avisoInfo['fecha_aviso'])
                    ? date('d/m H:i', strtotime($avisoInfo['fecha_aviso']))
                    : null;
                $conToken = !$esEvento && $avisoInfo && !empty($avisoInfo['aviso_valido']);
                ?>

                <div class="asist-chip asist-chip-avisado">
                    <div class="asist-chip-avatar" aria-hidden="true">&#128683;</div>

                    <div class="asist-chip-info">
                        <div class="asist-chip-nombre">
                            <?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>
                        </div>

                        <?php if ($fechaAvisoTxt): ?>
                            <div class="asist-chip-extra">
                                <span title="Fecha del aviso">
                                    <span aria-hidden="true">&#128340;</span>
                                    <?= htmlspecialchars($fechaAvisoTxt) ?>
                                </span>

                                <?php if ($conToken): ?>
                                    <span
                                        class="asist-chip-token"
                                        title="Aviso válido: generó token de recuperación"
                                        aria-label="Tiene token de recuperación"
                                    >&#127915;</span>
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
    <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
    <input type="hidden" name="<?= htmlspecialchars($campoId) ?>" value="<?= $idOrigen ?>">

    <section class="profesor-seccion">
        <div class="cabecera-pase-lista">
            <div class="titulo-seccion titulo-seccion-sin-margen">Pase de lista</div>

            <?php if (!empty($alumnos)): ?>
                <div class="atajos-pase-lista" role="group" aria-label="Marcar todos">
                    <button type="button" class="badge badge-secundario" data-marcar-todos="asiste">
                        <span aria-hidden="true">&#10003;</span>
                        Marcar todos como asisten
                    </button>

                    <button type="button" class="badge badge-cancelar" data-marcar-todos="ausente">
                        Limpiar marcas
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($alumnos)): ?>
            <p class="vacio">
                No hay alumnos inscritos en este <?= htmlspecialchars($textoOrigen) ?>.
            </p>
        <?php else: ?>
            <div class="asist-lista">
                <?php foreach ($alumnos as $alumno): ?>
                    <?php
                    $aid = (int)$alumno['id'];
                    $estadoActual = $registros[$aid] ?? 'asiste';
                    $avisoInfoFila = $avisos[$aid] ?? null;
                    $tieneTokenFila = !$esEvento
                        && $avisoInfoFila
                        && !empty($avisoInfoFila['aviso_valido']);

                    $tokDispon = !$esEvento
                        ? (int)($alumno['tokens_disponibles'] ?? 0)
                        : 0;
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

                            <span class="asist-fila-texto">
                                <?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>
                            </span>

                            <?php if ($tokDispon > 0): ?>
                                <span class="asist-token-badge" title="<?= $tokDispon ?> token(es) disponible(s) para recuperar clase">
                                    <span aria-hidden="true">&#127915;</span>
                                    <?= $tokDispon ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="asist-fila-botones" role="radiogroup" aria-label="Estado de <?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>">
                            <label class="asist-boton asist-boton-asiste<?= $estadoActual === 'asiste' ? ' activo' : '' ?>" title="Asiste">
                                <input type="radio" name="filas[<?= $aid ?>][estado]" value="asiste"<?= $estadoActual === 'asiste' ? ' checked' : '' ?>>
                                <span aria-hidden="true">&#10003;</span>
                                <span class="visualmente-oculto">Asiste</span>
                            </label>

                            <label class="asist-boton asist-boton-avisado<?= $estadoActual === 'avisado' ? ' activo' : '' ?>" title="No asistirá (avisó)">
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
                    <span aria-hidden="true">&#128190;</span>
                    Guardar
                </button>
            </div>
        <?php endif; ?>
    </section>
</form>

<?php if (!empty($alumnos)): ?>
<script>
(function () {
    var lista = document.querySelector('.asist-lista');
    if (!lista) return;

    var estados = ['asiste', 'avisado', 'ausente'];

    function recontarResumen() {
        var contador = { asiste: 0, avisado: 0, ausente: 0 };

        lista.querySelectorAll('.asist-fila').forEach(function (fila) {
            var marcado = fila.querySelector('input[type=radio]:checked');
            var valor = marcado ? marcado.value : 'asiste';

            if (Object.prototype.hasOwnProperty.call(contador, valor)) {
                contador[valor]++;
            }
        });

        var total = contador.asiste + contador.avisado + contador.ausente;

        document.querySelectorAll('[data-resumen]').forEach(function (n) {
            var clave = n.getAttribute('data-resumen');

            if (clave === 'total') {
                n.textContent = total;
            } else if (Object.prototype.hasOwnProperty.call(contador, clave)) {
                n.textContent = contador[clave];
            }
        });
    }

    function actualizarFila(input) {
        var fila = input.closest('.asist-fila');
        if (!fila) return;

        estados.forEach(function (estado) {
            fila.classList.remove('asist-fila-' + estado);
        });

        fila.classList.add('asist-fila-' + input.value);

        var indicador = fila.querySelector('.asist-indicador');

        if (indicador) {
            indicador.classList.remove(
                'asist-indicador-ok',
                'asist-indicador-no',
                'asist-indicador-pendiente'
            );

            if (input.value === 'asiste') {
                indicador.classList.add('asist-indicador-ok');
                indicador.innerHTML = '&#10003;';
                indicador.title = 'Confirma asistencia';
            } else if (input.value === 'avisado') {
                indicador.classList.add('asist-indicador-no');
                indicador.innerHTML = '&#10007;';
                indicador.title = 'No asistirá';
            } else {
                indicador.classList.add('asist-indicador-pendiente');
                indicador.innerHTML = '&#9675;';
                indicador.title = 'Ausente sin avisar';
            }
        }

        fila.querySelectorAll('.asist-boton').forEach(function (boton) {
            var radio = boton.querySelector('input[type=radio]');
            if (radio) {
                boton.classList.toggle('activo', radio.checked);
            }
        });
    }

    var estadosPrevios = new WeakMap();

    lista.querySelectorAll('.asist-fila').forEach(function (fila) {
        var seleccionado = fila.querySelector('input[type=radio]:checked');
        estadosPrevios.set(fila, seleccionado ? seleccionado.value : 'asiste');
    });

    function nombreDe(fila) {
        var nodo = fila.querySelector('.asist-fila-texto');
        return nodo ? (nodo.textContent || '').trim() : 'este alumno';
    }

    var textosEstado = {
        asiste: {
            titulo: '¿Marcar como asistente?',
            icon: 'question',
            ok: 'Sí, asistirá',
            color: '#16A34A',
            mensaje: function (nombre) {
                return nombre + ' aparecerá como que asiste.';
            }
        },
        avisado: {
            titulo: '¿Marcar como avisado?',
            icon: 'info',
            ok: 'Sí, avisó',
            color: '#F59E0B',
            mensaje: function (nombre) {
                return nombre + ' avisó de que no podrá asistir.';
            }
        },
        ausente: {
            titulo: '¿Marcar como ausente?',
            icon: 'warning',
            ok: 'Sí, ausente',
            color: '#DC2626',
            mensaje: function (nombre) {
                return nombre + ' no se presentó sin avisar.';
            }
        }
    };

    lista.addEventListener('change', function (evento) {
        var input = evento.target;

        if (!input || input.type !== 'radio') return;

        var fila = input.closest('.asist-fila');
        if (!fila) return;

        if (fila.dataset.skipConfirm === '1') {
            estadosPrevios.set(fila, input.value);
            actualizarFila(input);
            recontarResumen();
            return;
        }

        var configuracion = textosEstado[input.value];

        if (!configuracion || typeof Swal === 'undefined') {
            estadosPrevios.set(fila, input.value);
            actualizarFila(input);
            recontarResumen();
            return;
        }

        Swal.fire({
            title: configuracion.titulo,
            text: configuracion.mensaje(nombreDe(fila)),
            icon: configuracion.icon,
            showCancelButton: true,
            confirmButtonText: configuracion.ok,
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            confirmButtonColor: configuracion.color,
            cancelButtonColor: '#6B7280',
            focusCancel: true
        }).then(function (resultado) {
            if (resultado.isConfirmed) {
                estadosPrevios.set(fila, input.value);
                actualizarFila(input);
                recontarResumen();
                return;
            }

            var previo = estadosPrevios.get(fila) || 'asiste';
            var radioPrevio = fila.querySelector(
                'input[type=radio][value="' + previo + '"]'
            );

            if (radioPrevio) {
                radioPrevio.checked = true;
                actualizarFila(radioPrevio);
            }
        });
    });

    document.querySelectorAll('[data-marcar-todos]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var valor = boton.getAttribute('data-marcar-todos');
            if (!valor) return;

            lista.querySelectorAll('.asist-fila').forEach(function (fila) {
                var radioAvisado = fila.querySelector(
                    'input[type=radio][value="avisado"]'
                );

                if (radioAvisado && radioAvisado.checked) return;

                var radio = fila.querySelector(
                    'input[type=radio][value="' + valor + '"]'
                );

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
