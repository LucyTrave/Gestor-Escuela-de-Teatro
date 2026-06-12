<?php $titulo = 'Grupos Especiales'; $seccion = 'especiales'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<div class="admin-topbar admin-topbar-especiales">
    <div>
        <div class="etiqueta">ADMINISTRACIÓN</div>
        <h1>Eventos especiales</h1>
    </div>
    <a class="boton-principal" href="<?= BASE_URL ?>/admin/especiales/crear">+ Nuevo evento</a>
</div>

<section class="tarjetas tarjetas-profesor">
    <article class="tarjeta tarjeta-roja"><div class="tarjeta-etiqueta">Total eventos</div><div class="tarjeta-valor-grande"><?= $metricas['total'] ?></div></article>
    <article class="tarjeta tarjeta-negra"><div class="tarjeta-etiqueta">Intensivos</div><div class="tarjeta-valor-grande"><?= $metricas['intensivos'] ?></div></article>
    <article class="tarjeta tarjeta-negra"><div class="tarjeta-etiqueta">Salidas</div><div class="tarjeta-valor-grande"><?= $metricas['salidas'] ?></div></article>
</section>

<section class="admin-group-grid">
    <?php if (empty($eventos)): ?><p class="vacio">No hay eventos especiales registrados.</p><?php endif; ?>
    <?php foreach ($eventos as $evento): ?>
        <?php $ocupacion = ((int)$evento['plazas_maximas'] > 0) ? min(100, (int)round(((int)$evento['apuntados'] / (int)$evento['plazas_maximas']) * 100)) : 0; ?>
        <article class="admin-event-card">
            <div class="admin-group-head">

                <div class="info-evento">

                    <h2 class="titulo-evento">
                        <?= htmlspecialchars($evento['nombre']) ?>
                    </h2>
                    <div class="evento-fecha">
                        📅 <?= htmlspecialchars(date('d/m/Y', strtotime($evento['fecha']))) ?>
                    </div>

                    <?php if (!empty($evento['hora'])): ?>
                        <div class="evento-hora">
                            🕒 <?= htmlspecialchars(substr($evento['hora'], 0, 5)) ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="tipo-evento">
                    Tipo: <?= htmlspecialchars(str_replace('_', ' ', $evento['tipo'])) ?>
                </div>

            </div>
            <p><?= htmlspecialchars($evento['descripcion'] ?: 'Sin descripcion') ?></p>
            <div class="admin-progress"><div style="width: <?= $ocupacion ?>%"></div></div>
            <div class="evento-info">
                <span class="evento-inscritos">
                    <?= (int)$evento['apuntados'] ?> inscritos
                </span>
                <span class="evento-separador">/</span>
                <span class="evento-plazas">
                    <?= (int)$evento['plazas_maximas'] ?> plazas
                </span>
            </div>
            
            <div class="acciones-evento">

                <a class="badge badge-accion btn-accion" href="<?= BASE_URL ?>/admin/especiales/gestionar?id=<?= (int)$evento['id'] ?>">Gestionar alumnos</a>

                <div class="acciones-derecha">
                    <a class="badge badge-anular icon-btn editar" href="<?= BASE_URL ?>/admin/especiales/editar?id=<?= (int)$evento['id'] ?>">✏️</a>

                    <form method="POST" action="<?= BASE_URL ?>/admin/especiales/eliminar" onsubmit="return confirm('¿Eliminar este evento?')">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int)$evento['id'] ?>">
                        <button class="badge badge-eliminar icon-btn eliminar">🗑</button>
                    </form>
                </div>

            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
