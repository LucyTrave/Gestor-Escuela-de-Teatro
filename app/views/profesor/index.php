<?php
$vista = $vista ?? 'inicio';

// Mapeo de títulos por vista
$titulos = [
    'inicio' => 'Profesor | Gestor Escuela',
    'alumnos' => 'Alumnos | Gestor Escuela',
    'grupos' => 'Grupos | Gestor Escuela',
    'clases' => 'Clases | Gestor Escuela',
    'asistencia' => 'Asistencia | Gestor Escuela',
    'calendario' => 'Calendario | Gestor Escuela'
];

// Si la vista no existe, fallback a inicio
$titulo = $titulos[$vista] ?? $titulos['inicio'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="pagina-dashboard">

<header class="cabecera-dashboard">
    <div>
        <div class="marca">GESTOR ESCUELA</div>
        <div class="subtitulo">AREA PROFESORADO</div>
    </div>

    <nav>
        <a class="enlace-nav<?= $vista === 'inicio' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor">Inicio</a>

        <a class="enlace-nav<?= $vista === 'alumnos' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/alumnos">Alumnos</a>

        <a class="enlace-nav<?= $vista === 'grupos' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/grupos">Grupos</a>

        <a class="enlace-nav<?= $vista === 'clases' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/clases">Clases</a>

        <a class="enlace-nav<?= $vista === 'asistencia' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/asistencia">Asistencia</a>

        <a class="enlace-nav<?= $vista === 'calendario' ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/calendario">Calendario</a>
    </nav>

    <div class="profesor-usuario">
        <span>Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></span>
        <a class="enlace-cerrar" href="<?= BASE_URL ?>/logout">Cerrar sesión</a>
    </div>
</header>

           
        </nav>
        <div class="profesor-usuario">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></span>
            <a class="enlace-cerrar" href="<?= BASE_URL ?>/logout">Cerrar sesion</a>
        </div>
    </header>

    <main class="contenido contenido-profesor">
        <?php if (!empty($mensaje['texto'])): ?>
            <div class="aviso aviso-<?= htmlspecialchars($mensaje['tipo']) ?>">
                <?= htmlspecialchars($mensaje['texto']) ?>
            </div>
        <?php endif; ?>

        <?php if ($vista === 'inicio'): ?>




            <div class="etiqueta">PANEL DE PROFESORADO</div>
            <h1>Gestion diaria del aula</h1>

            <section class="tarjetas tarjetas-profesor">
                <article class="tarjeta tarjeta-roja">
                    <div class="tarjeta-etiqueta">Grupos activos</div>
                    <div class="tarjeta-valor-grande"><?= (int)$gruposActivos ?></div>
                    <div class="tarjeta-sub">Sesiones activas en tu planificacion</div>
                </article>
                <article class="tarjeta tarjeta-negra">
                    <div class="tarjeta-etiqueta">Alumnado vinculado</div>
                    <div class="tarjeta-valor-grande"><?= (int)$alumnosActivos ?></div>
                    <div class="tarjeta-sub">Personas asignadas a tus grupos</div>
                </article>
                <article class="tarjeta tarjeta-roja">
                    <div class="tarjeta-etiqueta">Proximas clases</div>
                    <div class="tarjeta-valor-grande"><?= count($proximasClases) ?></div>
                    <div class="tarjeta-sub">Vista rapida de agenda inmediata</div>
                </article>
            </section>



<!-- *-------------------------------------------------------------------------------------------------------------
                                        TARJETAS DE INICIO
----------------------------------------------------------------------------------------------------------- -->





           <section class="profesor-grid">

  <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/asistencia">
        <article class="profesor-card">
            <i class="fas fa-check-circle icono-card"></i>
            <div class="titulo-seccion">Asistencia</div>
        </article>
    </a>


        <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/grupos">
        <article class="profesor-card">
            <i class="fas fa-users icono-card"></i>
            <div class="titulo-seccion">Grupos</div>
            <p>Gestion de horarios, grupos y salas.</p>
        </article>
    </a>


    <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/alumnos">
        <article class="profesor-card">
            <i class="fas fa-user-graduate icono-card"></i>
            <div class="titulo-seccion">Alumnos</div>
            <p>Alta, asignacion de grupo.</p>
        </article>
    </a>




    <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/clases">
        <article class="profesor-card">
            <i class="fas fa-chalkboard-teacher icono-card"></i>
            <div class="titulo-seccion">Clases</div>
            <p>Gestion de clases, sala y aforo.</p>
        </article>
    </a>

    <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/calendario">
        <article class="profesor-card">
            <i class="fas fa-calendar-alt icono-card"></i>
            <div class="titulo-seccion">Calendario</div>
            <p>Vista mensual de tus clases.</p>
        </article>
    </a>


</section>

      <section class="profesor-seccion">
    <div class="titulo-seccion">
        <i class="fas fa-calendar-alt icono-titulo"></i>
        Agenda inmediata
    </div>


    <?php if (empty($proximasClases)): ?>
        <p class="vacio">
            <i class="fas fa-calendar-times icono-vacio"></i>
            No hay clases programadas por ahora.
        </p>
    <?php else: ?>
        <div class="lista-clases">
            <?php foreach ($proximasClases as $clase): ?>
                <article class="fila-clase">
                    <div class="caja-fecha">
                        <i class="fas fa-calendar-day icono-fecha"></i>
                        <div class="dia-numero"><?= date('d', strtotime($clase['fecha'])) ?></div>
                        <div class="mes"><?= strtoupper(date('M', strtotime($clase['fecha']))) ?></div>
                    </div>

                    <div class="info-clase">
                        <div class="nombre-clase">
                            <i class="fas fa-chalkboard icono-clase"></i>
                            <?= htmlspecialchars($clase['nombre']) ?>
                        </div>

                        <div class="detalles-clase">
                            <i class="fas fa-info-circle icono-detalle"></i>
                            <?= htmlspecialchars(ucfirst($clase['tipo'])) ?> · <?= htmlspecialchars(substr($clase['hora_inicio'], 0, 5)) ?> · <?= htmlspecialchars($clase['sala'] ?? 'Sin sala') ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>






        <?php endif; ?>




<!----------------------------------------------------------------------------------------------------------------------------------------------------0
                                                               Gestion de alumnos
   ----------------------------------------------------------------------------------------------------------------------------------------------------         -->



        <?php if ($vista === 'alumnos'): ?>
            <?php $esAdminVista = (($_SESSION['usuario_rol'] ?? '') === 'admin'); ?>
            <div class="etiqueta">AREA PROFESORADO</div>
            <div class="alumnos-head">
                <h1>Alumnos</h1>
              <i class="fas fa-user-graduate icono-card"></i>
            </div>

            <?php if (!$esAdminVista): ?>
                <p class="aviso aviso-info">El alta y la edicion de alumnos los gestiona el administrador. Como profesor solo puedes consultar tu alumnado.</p>
            <?php endif; ?>







           
            <section class="profesor-seccion">




                <div class="titulo-seccion">Listado</div>
                <div class="tabla-scroll">
                    <table class="tabla-profesor">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Telefono</th><th>Estado</th><th>Grupo</th><?php if ($esAdminVista): ?><th>Acciones</th><?php endif; ?></tr></thead>
                        <tbody>
                            <?php if (empty($alumnos)): ?><tr><td colspan="<?= $esAdminVista ? 7 : 6 ?>" class="vacio-tabla">No hay alumnos disponibles.</td></tr><?php endif; ?>
                            <?php foreach ($alumnos as $alumno): ?>
                                <?php $editando = $esAdminVista && $editarId === (int)$alumno['id']; ?>
                                <tr>
                                    <?php if ($editando): ?>
                                        <?php $formId = 'editar-alumno-' . (int)$alumno['id']; ?>
                                        <td><?= (int)$alumno['id'] ?><form id="<?= $formId ?>" method="post" action="<?= BASE_URL ?>/profesor/alumnos"><input type="hidden" name="accion" value="actualizar"><input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>"></form></td>
                                        <td class="celda-doble"><input form="<?= $formId ?>" type="text" name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" required><input form="<?= $formId ?>" type="text" name="apellidos" value="<?= htmlspecialchars($alumno['apellidos']) ?>" required></td>
                                        <td><input form="<?= $formId ?>" type="email" name="email" value="<?= htmlspecialchars($alumno['email'] ?? '') ?>"></td>
                                        <td><input form="<?= $formId ?>" type="text" name="telefono" value="<?= htmlspecialchars($alumno['telefono'] ?? '') ?>"></td>
                                        <td><select form="<?= $formId ?>" name="estado"><option value="posible"<?= $alumno['estado'] === 'posible' ? ' selected' : '' ?>>Posible</option><option value="matriculado"<?= $alumno['estado'] === 'matriculado' ? ' selected' : '' ?>>Matriculado</option><option value="baja"<?= $alumno['estado'] === 'baja' ? ' selected' : '' ?>>Baja</option></select></td>
                                        <td><select form="<?= $formId ?>" name="grupo_id"><option value="0">Sin grupo</option><?php foreach ($gruposDisponibles as $grupo): ?><option value="<?= (int)$grupo['id'] ?>"<?= (int)($alumno['grupo_id'] ?? 0) === (int)$grupo['id'] ? ' selected' : '' ?>><?= htmlspecialchars($grupo['nombre']) ?></option><?php endforeach; ?></select></td>
                                        <td class="acciones-profesor"><button form="<?= $formId ?>" type="submit" class="badge badge-accion">Guardar</button><a class="badge badge-anular" href="<?= BASE_URL ?>/profesor/alumnos">Cancelar</a></td>
                                    <?php else: ?>
                                        <td><?= (int)$alumno['id'] ?></td>
                                        <td><?= htmlspecialchars(trim($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?></td>
                                        <td><?= htmlspecialchars($alumno['email'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($alumno['telefono'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($alumno['estado']) ?></td>
                                        <td><?= htmlspecialchars($alumno['grupo_nombre'] ?: 'Sin grupo') ?></td>
                                        <?php if ($esAdminVista): ?>
                                        <td class="acciones-profesor">
                                            <a class="badge badge-accion" href="<?= BASE_URL ?>/profesor/alumnos?editar=<?= (int)$alumno['id'] ?>" title="Editar">&#9998; Editar</a>
                                            <?php
                                                $gruposRepetibles = array_filter($gruposDisponibles, fn($g) => (int)$g['id'] !== (int)($alumno['grupo_id'] ?? 0));
                                            ?>
                                            <?php if (!empty($gruposRepetibles)): ?>
                                                <form method="post" action="<?= BASE_URL ?>/profesor/alumnos" class="form-repetir"
                                                      data-swal-confirm="El alumno quedará inscrito también en este grupo."
                                                      data-swal-title="¿Añadir alumno al grupo seleccionado?"
                                                      data-swal-ok="Sí, añadir"
                                                      data-swal-icon="question">
                                                    <input type="hidden" name="accion" value="repetir">
                                                    <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                                    <select name="grupo_id" required>
                                                        <option value="">&#128257; Repetir en...</option>
                                                        <?php foreach ($gruposRepetibles as $g): ?>
                                                            <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="badge badge-accion" title="Anadir al grupo seleccionado">&#10003;</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" action="<?= BASE_URL ?>/profesor/alumnos"
                                                  data-swal-confirm="Esta acción no se puede deshacer."
                                                  data-swal-title="¿Eliminar este alumno?"
                                                  data-swal-ok="Sí, eliminar"
                                                  data-swal-icon="warning"
                                                  data-swal-danger>
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                                <button type="submit" class="badge badge-anular" title="Eliminar">&#128465;</button>
                                            </form>
                                        </td>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>


