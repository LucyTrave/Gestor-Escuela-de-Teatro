<?php $titulo = 'Gestionar Evento'; $seccion = 'especiales'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Gestionar evento</h1>

<section class="profesor-seccion" id="lista-alumnos">

    <?php 
        $lleno = $total_apuntados >= $plazas_maximas;
        $porcentaje = $plazas_maximas > 0 ? ($total_apuntados / $plazas_maximas) * 100 : 0;
    ?>

    <div class="titulo-seccion"><?= htmlspecialchars($evento['nombre']) ?></div>
    <p class="detalle-evento">
        🎭 <strong>Tipo:</strong>
        <?= htmlspecialchars(str_replace('_', ' ', $evento['tipo'])) ?>
    </p>

    <p class="detalle-evento">
        📅 <strong>Fecha:</strong>
        <?= htmlspecialchars(date('d/m/Y', strtotime($evento['fecha']))) ?>
    </p>

    <?php if (!empty($evento['hora'])): ?>
        <p class="detalle-evento">
            🕒 <strong>Hora:</strong>
            <?= htmlspecialchars(substr($evento['hora'], 0, 5)) ?>
        </p>
    <?php endif; ?>

    <p class="detalle-evento">
        📝 <strong>Descripción:</strong>
        <?= htmlspecialchars($evento['descripcion'] ?: 'Sin descripción') ?>
    </p>

    <div class="aforo-info <?= $lleno ? 'aforo-lleno' : 'aforo-ok' ?>">
        <strong>Aforo:</strong> 
        <?= $total_apuntados ?> / <?= $plazas_maximas ?>

        <?php if ($lleno): ?>
            <span class="aforo-texto"> (Completo)</span>
        <?php else: ?>
            <span class="aforo-texto"> (<?= $plazas_libres ?> plazas libres)</span>
        <?php endif; ?>
    </div>

    <div class="barra-aforo">
        <div style="width: <?= $porcentaje ?>%"></div>
    </div>

</section>

<section class="profesor-seccion">
    <div class="admin-topbar-especiales">
        <div class="cabecera-alumnos">
            <div class="titulo-seccion">ALUMNOS INSCRITOS</div>

            <div class="acciones-alumnos">
                <input type="text"
                    id="buscador-alumnos"
                    placeholder="Buscar por nombre, email o teléfono..."
                    class="input-buscador">

                <a class="link-volver" href="<?= BASE_URL ?>/admin/especiales">
                    Volver
                </a>
            </div>
        </div>
    </div>
    <div class="tabla-scroll">
        <div class="lista-alumnos-evento">
            <?php foreach ($todosAlumnosOrdenados as $alumno): ?>
                <?php
                    $inscrito = false;
                    foreach ($alumnos as $a) {
                        if ($a['id'] == $alumno['id']) {
                            $inscrito = true;
                            break;
                        }
                    }
                ?>
                <div class="card-alumno-evento <?= $inscrito ? 'inscrito' : '' ?>"
                        data-nombre="<?= strtolower(
                            $alumno['nombre'] . ' ' . 
                            $alumno['apellidos'] . ' ' . 
                            ($alumno['email'] ?? '') . ' ' . 
                            ($alumno['telefono'] ?? '')
                        ) ?>">

                        <span class="nombre-alumno">
                            <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?>
                        </span>

                        <?php if ($inscrito): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/especiales/quitar-alumno">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                                <input type="hidden" name="alumno_id" value="<?= $alumno['id'] ?>">
                                <button class="badge badge-eliminar btn-alumno">Quitar</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/especiales/inscribir">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                                <input type="hidden" name="alumno_id" value="<?= $alumno['id'] ?>">
                                <button 
                                    class="badge badge-matricular btn-alumno <?= $lleno ? 'disabled' : '' ?>" 
                                    <?= $lleno ? 'disabled' : '' ?>>
                                    <?= $lleno ? 'Completo' : 'Apuntar' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

            <?php endforeach; ?>

        </div>
    </div>
</section>

<script>
    const buscador = document.getElementById('buscador-alumnos');

    buscador.addEventListener('input', function() {
        const filtro = this.value.toLowerCase().trim();
        const alumnos = document.querySelectorAll('.card-alumno-evento');

        alumnos.forEach(alumno => {
            const texto = alumno.dataset.nombre;

            alumno.style.display = texto.includes(filtro) ? 'flex' : 'none';
        });
    });
</script>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
