<?php
/** @var array $alumnos */
/** @var array $grupo */
/** @var array $alumnosDisponibles */
/** @var array $grupos */
/** @var array $salas */
?>



<!-- MODAL PARA GESTIONAR ALUMNOS DE UN GRUPO ESPECÍFICO -->

<div class="admin-alumnos-modal">

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px;">
        <div>
            <h2 style="margin:0 0 6px 0;">
                <?= htmlspecialchars($grupo['nombre'] ?? 'Grupo') ?>
            </h2>

            <p style="margin:0; color:#666;">
                Gestiona los alumnos asignados a este grupo
            </p>

<!-- Información del grupo -->
            <div style="
            margin-top:18px;
            border:1px solid #e5e7eb;
            border-radius:10px;
            padding:14px;
            background:#fafafa;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            ">

    <div style="display:flex; gap:14px; align-items:center; font-size:13px; color:#444;">

        <span>
            🕒 <?= htmlspecialchars(ucfirst($grupo['dia_semana'] ?? '') . ' - ' . substr($grupo['hora_inicio'] ?? '00:00:00', 0, 5)) ?>
        </span>

        <span>
            👨‍🏫 <?= htmlspecialchars($grupo['profesor_nombre'] ?? 'Profesor') ?>
        </span>

        <span>
            🏷 <?= htmlspecialchars(ucfirst($grupo['nivel'] ?? '')) ?>
        </span>

    </div>

    <div style="font-size:13px; font-weight:700;">
        <?= count($alumnos) ?>/16
    </div>

</div>
        </div>

        <!-- Botón para cerrar el modal y volver a la lista de grupos -->
        <a href="<?= BASE_URL ?>/admin/grupos"
           style="
        text-decoration:none;
        color:#333;
        font-weight:400;
        font-size:18px;
        line-height:1;
   ">
    ×
        </a>
    </div>

    

<!-- Lista de alumnos matriculados en el grupo -->

<h3 style="margin:22px 0 10px; font-size:15px;">
    👥 Alumnos del grupo (<?= count($alumnos) ?>)
</h3>

<!-- Lista de alumnos actuales del grupo -->
 <div style="
    margin-top:20px;
    max-height:220px;
    overflow-y:auto;
    padding-right:4px;
    ">

    <?php foreach ($alumnos as $alumno): ?>

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:14px;
            padding:14px;
            border:1px solid #86efac;
            border-radius:10px;
            margin-bottom:12px;
            background:#f0fdf4;
        ">

            <div>
                <div style="font-weight:700; color:#111827;">
                    <?= htmlspecialchars(($alumno['nombre'] ?? '') . ' ' . ($alumno['apellidos'] ?? '')) ?>
                </div>

                <div style="font-size:13px; color:#6b7280; margin-top:4px;">
                    <?= htmlspecialchars($alumno['email'] ?? '') ?>
                </div>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/admin/grupos/eliminar-alumno" class="form-eliminar-alumno-grupo">
                <?= Csrf::field() ?>
                <input type="hidden" name="grupo_id" value="<?= htmlspecialchars($grupo['id']) ?>">
                <input type="hidden" name="alumno_id" value="<?= htmlspecialchars($alumno['alumno_id']) ?>">
                <button type="submit"
                class="btn-eliminar-alumno" 
                data-alumno="<?= htmlspecialchars(($alumno['nombre'] ?? '') . ' ' . ($alumno['apellidos'] ?? '')) ?>"
               style="
                    background:#ffffff;
                    color:#dc2626;
                    border:1px solid #fca5a5;
                    padding:8px 14px;
                    border-radius:8px;
                    text-decoration:none;
                    font-size:13px;
                    font-weight:600;
                    display:flex;
                    align-items:center;
                    gap:6px;
                    transition:all .2s ease;
                    cursor:pointer;
               ">
                👤 Quitar
                </button>
            </form>

        </div>

    <?php endforeach; ?>

</div>

<!-- Lista de alumnos disponibles para agregar al grupo -->

<?php if (!empty($alumnosDisponibles)): ?>

<h3 style="margin:22px 0 10px; font-size:15px;">
    👥 Añadir alumnos
</h3>

<div style="margin-top:10px;">

    <?php foreach ($alumnosDisponibles as $alumnoDisponible): ?>

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:14px;
            padding:14px;
            border:1px solid #e5e7eb;
            border-radius:10px;
            margin-bottom:12px;
            background:#ffffff;
        ">

            <div>
    <div style="font-weight:700; color:#111827;">
        <?= htmlspecialchars(($alumnoDisponible['nombre'] ?? '') . ' ' . ($alumnoDisponible['apellidos'] ?? '')) ?>
    </div>

    <div style="font-size:12px; color:#666; margin-top:4px;">
        Nivel:
        <?= htmlspecialchars($alumnoDisponible['nivel'] ?? '') ?>
    </div>
</div>



            <form method="POST" action="<?= BASE_URL ?>/admin/grupos/agregar-alumno">
                <?= Csrf::field() ?>
                <input type="hidden" name="grupo_id" value="<?= htmlspecialchars($grupo['id']) ?>">
                <input type="hidden" name="alumno_id" value="<?= htmlspecialchars($alumnoDisponible['id']) ?>">

                <button type="submit" style="
                    background:#16a34a;
                    color:white;
                    border:none;
                    padding:8px 14px;
                    border-radius:8px;
                    font-size:13px;
                    font-weight:700;
                    cursor:pointer;
                ">
                    Añadir
                </button>
            </form>

        </div>

    <?php endforeach; ?>

<!--/* Botón para cerrar el modal y volver a la vista principal de grupos */-->


<?php endif; ?>
<div style="
    margin-top:22px;
    padding-top:16px;
    border-top:1px solid #e5e7eb;
    display:flex;
    justify-content:flex-end;
">
    <a href="<?= BASE_URL ?>/admin/grupos"
       style="
            background:#c1121f;
            color:white;
            padding:10px 18px;
            border-radius:8px;
            text-decoration:none;
            font-size:13px;
            font-weight:700;
            box-shadow:0 4px 12px rgba(193,18,31,.25);
       ">
        ✓ Finalizar
    </a>
</div>


</div>
