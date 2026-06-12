<?php

/**
 * Variables auxiliares del modal de alumnos.
 * 
 * Inicializamos valores por defecto para evitar errores
 * de variables no definidas cuando el modal no está activo.
 * 
 * Esto también ayuda a Intelephense a interpretar correctamente
 * las variables utilizadas en la vista.
 */
/** @var array $grupos */
/** @var array $salas */
/** @var bool $modalAlumnosActivo */
/** @var array|null $grupoModal */
/** @var array $alumnos */
/** @var array $alumnosDisponibles */
/** @var array $profesores */

$modalAlumnosActivo = $modalAlumnosActivo ?? false;
$grupoModal = $grupoModal ?? null;
$alumnos = $alumnos ?? [];
$alumnosDisponibles = $alumnosDisponibles ?? [];
$profesores = $profesores ?? [];


/**
 * Nombre del usuario logueado para mostrarlo en la cabecera.
 * Si no existe en sesión, usamos "Lucía" como respaldo.
 */
$usuarioNombre = $_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? 'Lucía';

/**
 * Agrupamos las salas por espacio.
 * Ejemplo:
 * - Espacio en Blanco -> Sala Azul, Sala Blanca, etc.
 * - Sala ETC -> Sala ETC
 * - Sala Komodia -> Sala Komodia
 *
 * Esto nos permite pintar el bloque "Salas y espacios disponibles"
 * de una forma más limpia.
 */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos / Salas</title>

    <!--
        Cargamos el CSS oficial del equipo de desarrollo.
        Este CSS incluye estilos base, tipografías, colores y componentes comunes.
    -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css?v=grupos1">


    <!--
        Estilos específicos para la sección de grupos.
        Aquí añadimos personalizaciones y estilos propios para esta sección.-->

<style> 

/* Estilos para el botón de crear nuevo grupo */
.btn-crear-rojo{
    background:#c1121f;
    color:white;
    padding:8px 14px;
    border-radius:6px;
    font-weight:700;
    font-size:13px;
    text-decoration:none;
    transition:all .2s ease;
    box-shadow:0 2px 8px rgba(220,20,60,.25);

    display:inline-flex;
    align-items:center;
    gap:8px;

    border:none;
    cursor:pointer;
    font-family:inherit;
    line-height:1.2;
}

.btn-crear-rojo:hover{
    background:#9f0f1a;
    color:white;
    text-decoration:none;
    transform:translateY(-2px);
}


/* Estilo para el botón ver alumnos */

.admin-btn-ver-alumnos{
    transition:all .2s ease;
}

.admin-btn-ver-alumnos:hover{
    background:#fff1f2;
    color:#b42318;
    border-color:#f5c2c7;
}


/*Estilo para el botón eliminar grupo*/
.admin-grupo-btn-icono{
    transition:all .2s ease;
}

.admin-grupo-btn-icono:hover{
    background:#eef4ff;
    border-color:#bfd3ff;
    color:#1d4ed8;
}


/* Estilo para el boton aliminar grupo- utilizo !important para no sobreescribir
 ni romper estilos del esuipo, como excepción*/
.admin-grupo-btn-eliminar:hover{
    background:#fff1f2 !important;
    border-color:#f5c2c7 !important;
    color:#b42318 !important;
}

/*.Tarjetas Resumen mejorado*/
.admin-resumen-grupos-mejorado .tarjeta{
    border-radius:10px;
    border:1px solid #e5e7eb;
    padding:22px;
    transition:all .25s ease;
    background:white;
    cursor:default;
}

/* Hover */
.admin-resumen-grupos-mejorado .tarjeta:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 22px rgba(0,0,0,.08);
}

/* Grid para las tarjetas de resumen */

/* Más mantenible y flexible que usar flexbox, ya que nos permite controlar 
mejor el espacio entre tarjetas y la distribución en diferentes tamaños de pantalla. */
.admin-resumen-grupos-mejorado{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:16px;
}

/* Estilo común para el valor de las tarjetas*/

/* TARJETA TOTAL */
.admin-resumen-grupos-mejorado .tarjeta-total{
    border-left:4px solid #222;
}

/* TARJETA INICIACIÓN */
.admin-resumen-grupos-mejorado .tarjeta-iniciacion{
    border:1px solid #22c55e;
}

.admin-resumen-grupos-mejorado .tarjeta-iniciacion .tarjeta-valor-grande{
    color:#16a34a;
}

/* TARJETA INTERMEDIO */
.admin-resumen-grupos-mejorado .tarjeta-intermedio{
    border:1px solid #eab308;
}

.admin-resumen-grupos-mejorado .tarjeta-intermedio .tarjeta-valor-grande{
    color:#ca8a04;
}

/* TARJETA AVANZADO */
.admin-resumen-grupos-mejorado .tarjeta-avanzado{
    border:1px solid #f43f5e;
}

.admin-resumen-grupos-mejorado .tarjeta-avanzado .tarjeta-valor-grande{
    color:#e11d48;
}


/* BADGES DE NIVEL */

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    min-width:80px;

    padding:5px 16px;

    border-radius:8px;

    font-size:11px;
    font-weight:700;

    border:1px solid transparent;

}

/* INICIACIÓN */
.badge-iniciacion{
    background:#f0fdf4;
    border-color:#22c55e;
    color:#16a34a;
}

/* INTERMEDIO */
.badge-intermedio{
    background:#fefce8;
    border-color:#eab308;
    color:#ca8a04;
}

/* AVANZADO */
.badge-avanzado{
    background:#fff1f2;
    border-color:#f43f5e;
    color:#e11d48;
}

/* CURSO */
.badge-curso{
    background:#fff1f2;
    border-color:#f43f5e;
    color:#e11d48;
}

/* =====================================================
   TABS GRUPOS / SALAS
===================================================== */

.admin-tabs-grupos{
    display:grid;
    grid-template-columns:1fr 1fr;
    width:340px;
    background:#e5e5e5;
    border-radius:8px;
    padding:3px;
    margin-bottom:16px;
    gap:0;
}

.admin-tab-btn{
    border:none;
    background:transparent;
    padding:8px 16px;
    border-radius:7px;
    font-weight:700;
    font-size:13px;
    cursor:pointer;
    transition:all .2s ease;
}

.admin-tab-btn:hover{
    background:#e5e7eb;
}

.admin-tab-btn.activo{
    background:white;
    color:#111827;
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}


/* =====================================================
   GRID DE TARJETAS DE GRUPOS
===================================================== */

.admin-grid-grupos{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:18px;
    align-items:start;

}


/* =====================================================
   MODAL ALUMNOS GRUPO
===================================================== */

.admin-alumnos-modal{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:10px;
    padding:22px;
    width:100%;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 900px){

    #seccion-grupos{
        grid-template-columns:1fr;
    }

    .admin-resumen-grupos-mejorado{
        grid-template-columns:repeat(2,1fr);
    }
}

/* =====================================================
   SWEETALERT SOBRE MODALES
===================================================== */

.swal2-container{
    z-index:999999 !important;
}

.swal-grupos-popup{
    z-index:999999 !important;
}


/* =====================================================
   MODAL EDITAR GRUPO - CAMPOS DEL FORMULARIO
===================================================== */

#modalEditarGrupo input,
#modalEditarGrupo select{
    width:100%;
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:14px;
    background:#ffffff;
    box-sizing:border-box;
    transition:border-color .2s ease, box-shadow .2s ease;
}

#modalEditarGrupo input:focus,
#modalEditarGrupo select:focus{
    outline:none;
    border-color:#e11d48;
    box-shadow:0 0 0 3px rgba(225,29,72,.12);
}

#modalEditarGrupo label{
    display:block;
    margin-bottom:6px;
    font-size:13px;
    font-weight:700;
    color:#111827;
}

/* =========================================
   MODAL CREAR GRUPO
========================================= */

.admin-form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.admin-form-grid-2 .admin-form-grupo {
    margin-bottom: 14px;
}

.admin-form-grupo select,
.admin-form-grupo input {
    width: 100%;
    box-sizing: border-box;
}




</style>

</head>



<body class="pagina-dashboard">

    <!-- CABECERA SUPERIOR -->
    <header class="cabecera-dashboard">
        <div>
            <div class="marca">GESTOR ESCUELA</div>
            <div class="subtitulo">ADMINISTRACIÓN</div>
        </div>

        <nav>
            <a href="<?= BASE_URL ?>/admin" class="enlace-nav">Inicio</a>
            <a href="<?= BASE_URL ?>/admin/posibles" class="enlace-nav">Posibles</a>
            <a href="<?= BASE_URL ?>/admin/matriculados" class="enlace-nav">Matriculados</a>
            <a href="<?= BASE_URL ?>/admin/grupos" class="enlace-nav activo">Grupos</a>
            <a href="<?= BASE_URL ?>/admin/especiales" class="enlace-nav">Especiales</a>
        </nav>

        <div class="admin-usuario">
            <span>Hola, <?= htmlspecialchars($usuarioNombre) ?></span>
            <a href="<?= BASE_URL ?>/logout" class="enlace-cerrar">Cerrar sesión</a>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido contenido-admin">
        <!-- Etiqueta pequeña superior -->
  <div class="admin-topbar" style="justify-content:flex-end; margin-bottom:24px;">
  </div>


       <!-- Título visual de la sección -->
<div class="titulo-bloque" style="margin-bottom: 16px;">
    <div style="display:flex; align-items:flex-start; gap:14px;">
        <span style="font-size: 28px;">👥</span>

        <div>
            <h1 style="margin-bottom: 4px;">Grupos / Clases</h1>
            <p style="color:#666; margin-bottom:4px;">
                Gestión de grupos y horarios de clases
            </p>

            <div class="volver">
                <a href="<?= BASE_URL ?>/admin">← Volver a inicio</a>
            </div>
        </div>
    </div>
</div>



    <!-- MENÚ INTERNO GRUPOS / SALAS -->
        <div class="admin-tabs-grupos">
            <button type="button" class="admin-tab-btn activo" data-tab="grupos">
                Grupos
            </button>

            <button type="button" class="admin-tab-btn" data-tab="salas">
                Salas
            </button>
        </div>

        <!-- BLOQUE DE SALAS Y ESPACIOS -->
        <section id="seccion-salas" class="admin-seccion-grupos admin-tab-contenido">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">

                <div class="titulo-seccion">
                    Salas y espacios disponibles
                </div>

                <button type="button" class="btn-crear btn-crear-rojo" id="abrirModalSala">
                    + Nueva sala
                </button>

            </div>

            <div class="admin-space-grid">

                <?php foreach ($salas as $sala): ?>

                    <article class="admin-space-card">

                        <strong><?= htmlspecialchars($sala['nombre']) ?></strong>

                        <p>📍 <?= htmlspecialchars($sala['direccion']) ?></p>

                        <span style="display:block; margin-bottom:14px;">
                            🏢 <?= htmlspecialchars($sala['espacio_nombre']) ?>
                        </span>

                        <div class="admin-grupo-acciones">

                            <button
                                type="button"
                                class="admin-grupo-btn-icono btn-editar-sala"
                                title="Editar sala"

                                data-id="<?= (int)$sala['id'] ?>"
                                data-nombre="<?= htmlspecialchars($sala['nombre'], ENT_QUOTES) ?>"
                                data-espacio="<?= htmlspecialchars($sala['espacio_nombre'], ENT_QUOTES) ?>"
                                data-direccion="<?= htmlspecialchars($sala['direccion'], ENT_QUOTES) ?>"
                            >
                                ✏️
                            </button>

                            <form action="<?= BASE_URL ?>/admin/salas/eliminar" method="POST" class="admin-sala-form-eliminar">
                                <?= Csrf::field() ?>

                                <input type="hidden" name="id" value="<?= (int)$sala['id'] ?>">

                                <button
                                    type="submit"
                                    class="admin-grupo-btn-icono admin-grupo-btn-eliminar"
                                >
                                    🗑️
                                </button>

                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    <!-- LISTADO DE GRUPOS -->
    <section id="seccion-grupos" class="admin-tab-contenido activo">

<div style="display:flex; justify-content:flex-end; align-items:center; margin-bottom:18px;">

    <button type="button" class="btn-crear btn-crear-rojo" id="abrirModalCrearGrupo">
        + Nuevo Grupo
    </button>

</div>


        <!-- TARJETAS DE RESUMEN -->
        <section class="tarjetas admin-resumen-grupos admin-resumen-grupos-mejorado">
            <article class="tarjeta tarjeta-total">
                <div class="tarjeta-etiqueta">Total grupos</div>
                <div class="tarjeta-valor-grande"><?= (int)($metricas['total'] ?? 0) ?></div>
            </article>

            <article class="tarjeta tarjeta-iniciacion">
                <div class="tarjeta-etiqueta">Iniciación</div>
                <div class="tarjeta-valor-grande"><?= (int)($metricas['iniciacion'] ?? 0) ?></div>
            </article>

            <article class="tarjeta tarjeta-intermedio">
                <div class="tarjeta-etiqueta">Intermedio</div>
                <div class="tarjeta-valor-grande"><?= (int)($metricas['intermedio'] ?? 0) ?></div>
            </article>

            <article class="tarjeta tarjeta-avanzado">
                <div class="tarjeta-etiqueta">Avanzado</div>
                <div class="tarjeta-valor-grande"><?= (int)($metricas['avanzado'] ?? 0) ?></div>
            </article>
        </section>

        <div class="admin-grid-grupos">

            <?php if (empty($grupos)): ?>
                <p class="vacio">No hay grupos creados.</p>
            <?php endif; ?>

            <?php foreach ($grupos as $grupo): ?>
                <?php
                /**
                 * Cálculos de ocupación.
                 * Asumimos 16 plazas por grupo como máximo, pero esto se podría adaptar 
                 * fácilmente si en el futuro queremos tener grupos con más o menos alumnos.
                 */
                $maxAlumnos = (int)($grupo['cupo_maximo'] ?? 16);
                $totalAlumnos = (int)($grupo['total_alumnos'] ?? 0);
                $plazasLibres = max(0, $maxAlumnos - $totalAlumnos);
                $porcentaje   = $maxAlumnos > 0 ? round(($totalAlumnos / $maxAlumnos) * 100) : 0;

                /**
                 * Nivel del grupo.
                 * Lo usamos para pintar el badge y el color de la barra.
                 */
                $nivel = strtolower(trim($grupo['nivel'] ?? ''));

                /**
                 * Título principal de la tarjeta.
                 * Ejemplo:
                 *  Iniciación Segundo Año
                 *  Lunes 17:30 
                 */
                $tituloGrupo = ucfirst($grupo['dia_semana'] ?? '') . ' ' .
                    substr($grupo['hora_inicio'] ?? '00:00:00', 0, 5) .
                    ' - ' . ($grupo['nombre'] ?? 'Grupo');

                /**
                 * Color de la barra según nivel.
                 */
                $colorBarra = '#DC2626'; // valor por defecto

                if ($nivel === 'iniciacion') {
                    $colorBarra = '#16A34A';
                } elseif ($nivel === 'intermedio') {
                    $colorBarra = '#BA7517';
                } elseif ($nivel === 'avanzado') {
                    $colorBarra = '#DC2626';
                }
                ?>
        
 <!-- Tarjeta individual de grupo -->
<article class="admin-group-card" style="margin-bottom: 18px;">

<div class="admin-group-head" style="display:block; min-height:auto;">



<!-- Nivel y contador alineados arriba -->
<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:8px; width:100%;">

    <div style="flex:1; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">

        <span class="badge badge-<?= $nivel ?>">
        <?= htmlspecialchars(ucfirst($grupo['nivel'] ?? 'Sin nivel')) ?>
        </span>

        <?php if (!empty($grupo['curso'])): ?>
            <span class="badge badge-curso">
               Curso <?= htmlspecialchars($grupo['curso']) ?> 
            </span>
        <?php endif; ?>

    </div>

    <div style="text-align:right; min-width:120px; margin-left:auto;">
        <div style="font-size:22px; font-weight:700; line-height:1;">
            <?= $totalAlumnos ?>/<?= $maxAlumnos ?>
        </div>

        <div style="font-size:11px; color:#333;">
            Alumnos
        </div>
    </div>

</div>

<!-- Título del grupo debajo -->
<h2 style="margin:0 0 12px 0; font-size:28px; line-height:1.1; font-weight:800;;">
    <?= htmlspecialchars($grupo['nombre'] ?? 'Grupo') ?>
</h2>

<?php if (!empty($grupo['clases'])): ?> 
    <?php foreach ($grupo['clases'] as $clase): ?> 
        <div style=" font-size:13px; color:#666; font-weight:600; margin-bottom:6px; display:flex; align-items:center; gap:6px; "> 
            <span>🕒</span> 
            <span> 
                <?= htmlspecialchars(ucfirst($grupo['dia_semana'])) ?>
                ·
                <?= htmlspecialchars(substr($clase['hora_inicio'], 0, 5)) ?> 
                - 
                <?= htmlspecialchars(substr($clase['hora_fin'], 0, 5)) ?> 
            </span> 
        </div> 
    <?php endforeach; ?> 
<?php else: ?> 
    <div style="font-size:13px; color:#999;"> Sin horarios </div> 
<?php endif; ?>

    <!-- Datos secundarios debajo -->
    <div class="admin-group-meta">
        <span>
    👨‍🏫 Profesor:
    <?= htmlspecialchars(explode(' ', $grupo['profesor_nombre'])[0] ?? 'Sin profesor') ?>
</span>
    </div>

    <div class="admin-group-meta">
        <span>📍 <?= htmlspecialchars(($grupo['espacio_nombre'] ?? 'Sin espacio') . ' - ' . ($grupo['sala_nombre'] ?? 'Sin sala')) ?></span>
    </div>

</div>

    <p style="margin-top:10px; margin-bottom:4px; font-size:12px; color:#666;">
    Ocupación <?= $porcentaje ?>%
    </p>

    <div class="admin-progress">
    <div style="width: <?= $porcentaje ?>%; background: <?= $colorBarra ?>;"></div>
</div>

<div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center; gap:10px;">

<!-- Botón para ver alumnos del grupo -->
    <a href="<?= BASE_URL ?>/admin/grupos?modal=alumnos&id=<?= $grupo['id'] ?>"
   class="admin-btn-ver admin-btn-ver-alumnos"
   title="Alumnos">
    👥 Alumnos (<?= $totalAlumnos ?>)
</a>

    <div class="admin-grupo-acciones">
        <button type="button"
            class="admin-grupo-btn-icono"
            title="Editar grupo"
            data-id="<?= (int)$grupo['id'] ?>"
            data-nombre="<?= htmlspecialchars($grupo['nombre'] ?? '', ENT_QUOTES) ?>"
            data-dia="<?= htmlspecialchars($grupo['dia_semana'] ?? '', ENT_QUOTES) ?>"
            data-hora="<?= htmlspecialchars(substr($grupo['hora_inicio'] ?? '00:00:00', 0, 5), ENT_QUOTES) ?>"
            data-curso="<?= htmlspecialchars($grupo['curso'] ?? '', ENT_QUOTES) ?>"
            data-nivel="<?= htmlspecialchars($grupo['nivel'] ?? '', ENT_QUOTES) ?>"
            data-profesor-id="<?= htmlspecialchars($grupo['profesor_id'] ?? '', ENT_QUOTES) ?>"
            data-sala-id="<?= (int)($grupo['sala_id'] ?? 0) ?>"
            data-hora-fin="<?= htmlspecialchars(substr($grupo['hora_fin'] ?? '00:00:00', 0, 5), ENT_QUOTES) ?>"
            data-cupo="<?= (int)($grupo['cupo_maximo'] ?? 16) ?>"
            data-fecha-inicio="<?= htmlspecialchars($grupo['fecha_inicio_curso'] ?? '', ENT_QUOTES) ?>"
            data-fecha-fin="<?= htmlspecialchars($grupo['fecha_fin_curso'] ?? '', ENT_QUOTES) ?>">
            🖉
        </button>

        <form action="<?= BASE_URL ?>/admin/grupos/eliminar" method="POST" class="admin-grupo-form-eliminar">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int)$grupo['id'] ?>">

        <button type="submit"
        class="admin-grupo-btn-icono admin-grupo-btn-eliminar btn-eliminar-grupo"
        title="Eliminar grupo"
        data-nombre="<?= htmlspecialchars($grupo['nombre'] ?? 'Grupo', ENT_QUOTES) ?>"
        data-dia="<?= htmlspecialchars(ucfirst($grupo['dia_semana'] ?? ''), ENT_QUOTES) ?>"
        data-hora="<?= htmlspecialchars(substr($grupo['hora_inicio'] ?? '00:00:00', 0, 5), ENT_QUOTES) ?>"
        data-profesor="<?= htmlspecialchars($grupo['profesor_nombre'] ?? 'Sin profesor', ENT_QUOTES) ?>"
        data-nivel="<?= htmlspecialchars(ucfirst($grupo['nivel'] ?? ''), ENT_QUOTES) ?>"
        data-total="<?= (int)($grupo['total_alumnos'] ?? 0) ?>">
            🗑
        </button>
        </form>
    </div>

</div>

</article>

            <?php endforeach; ?>
        </section>
     
</div>

</section>        

<!-- MODAL ALUMNOS DEL GRUPO -->
<?php if ($modalAlumnosActivo && $grupoModal !== null): ?>

<div class="admin-modal activo">
    <div class="admin-modal-contenido" style="max-width: 520px; width:92%;">

        <?php
        $grupo = $grupoModal;
        require ROOT . '/app/views/admin/alumnos_grupo.php';
        ?>

    </div>
</div>

<?php endif; ?>  

</main>

<!-- MODAL EDITAR GRUPO -->
<div id="modalEditarGrupo" class="admin-modal">
    <div class="admin-modal-contenido" style="max-width: 520px; width:92%;">

        <div class="admin-modal-header">
            <h2>Editar Grupo de Clases</h2>
            <span class="admin-modal-cerrar" id="cerrarModal">&times;</span>
        </div>

        <p class="admin-modal-subtitulo">Modifica los datos del grupo</p>

        <form id="formEditarGrupo" method="POST" action="<?= BASE_URL ?>/admin/grupos/actualizar">
            <?= Csrf::field() ?>

            <input type="hidden" name="id" id="modal-id">

            <div class="admin-modal-grid">

                <div class="admin-modal-campo-full">
                    <label for="modal-nombre">Nombre del grupo *</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="modal-nombre" 
                        required 
                        readonly
>
                </div>

                <div>
                    <label for="modal-dia">Día de la semana *</label>
                    <select name="dia_semana" id="modal-dia" required>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sábado</option>
                        <option value="domingo">Domingo</option>
                    </select>
                </div>

                <div>
                    <label for="modal-hora">Hora de inicio *</label>
                    <input type="time" name="hora_inicio" id="modal-hora" required>
                </div>

                <div>
                    <label for="modal-nivel">Nivel *</label>
                    <select name="nivel" id="modal-nivel" required>
                        <option value="iniciacion">Iniciación</option>
                        <option value="intermedio">Intermedio</option>
                        <option value="avanzado">Avanzado</option>
                    </select>
                </div>

                <div>
                    <label for="modal-curso">Curso *</label>

                    <select name="curso" id="modal-curso" required>
                    <option value="2025-2026">2025-2026</option>
                    <option value="2026-2027">2026-2027</option>
                    </select>
                </div>

                <div>
                    <label for="modal-profesor">Profesor *</label>
                    <select name="profesor_id" id="modal-profesor" required>
                        <?php foreach (($profesores ?? []) as $profesor): ?>
                            <option value="<?= htmlspecialchars($profesor['id'], ENT_QUOTES) ?>">
                                <?= htmlspecialchars($profesor['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-modal-campo-full">
                    <label for="modal-sala">Sala *</label>
                    <select name="sala_id" id="modal-sala" required>
                        <?php foreach ($salas as $sala): ?>
                            <option value="<?= (int)$sala['id'] ?>">
                                <?= htmlspecialchars(($sala['espacio_nombre'] ?? 'Sin espacio') . ' - ' . ($sala['nombre'] ?? 'Sala')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="admin-modal-grid" style="margin-top:16px;">

                <div>
                    <label for="modal-hora-fin">Hora fin *</label>

                    <input
                        type="time"
                        name="hora_fin"
                        id="modal-hora-fin"
                        required
                    >
                </div>

                <div>
                    <label for="modal-cupo">Aforo máximo *</label>

                    <input
                        type="number"
                        name="aforo_maximo"
                        id="modal-cupo"
                        min="1"
                        required
                    >
                </div>

            </div>

            <div class="admin-modal-grid" style="margin-top:16px;">

                <div>
                    <label for="modal-fecha-inicio">Fecha inicio curso *</label>

                    <input
                        type="date"
                        name="fecha_inicio_curso"
                        id="modal-fecha-inicio"
                        required
                    >
                </div>

                <div>
                    <label for="modal-fecha-fin">Fecha fin curso *</label>

                    <input
                        type="date"
                        name="fecha_fin_curso"
                        id="modal-fecha-fin"
                        required
                    >
                </div>

            </div>



            <div class="admin-modal-footer">
                <button type="button" id="cancelarModal" class="admin-btn-cancelar">Cancelar</button>
                <button type="submit" class="admin-btn-guardar">Guardar Cambios</button>
            </div>

        </form>
    </div>
</div>


<!-- SweetAlert2 utilizado para confirmaciones y mensajes de gestión de alumnos en Grupos -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- Scripts de la vista Grupos -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       TABS GRUPOS / SALAS
    ========================================= */

    const botones = document.querySelectorAll('.admin-tab-btn');
    const seccionGrupos = document.getElementById('seccion-grupos');
    const seccionSalas = document.getElementById('seccion-salas');

    function activarTab(tab) {

        botones.forEach(btn => btn.classList.remove('activo'));

        const botonActivo = document.querySelector(`[data-tab="${tab}"]`);

        if (botonActivo) {
            botonActivo.classList.add('activo');
        }

        if (tab === 'salas') {
            seccionGrupos.style.display = 'none';
            seccionSalas.style.display = 'block';
        } else {
            seccionGrupos.style.display = 'grid';
            seccionSalas.style.display = 'none';
        }
    }

    botones.forEach(function (boton) {

        boton.addEventListener('click', function () {

            const tab = boton.dataset.tab;

            activarTab(tab);

            const url = new URL(window.location);

            url.searchParams.set('tab', tab);

            window.history.replaceState({}, '', url);
        });

    });

    const tabActual = new URLSearchParams(window.location.search).get('tab') || 'grupos';

    activarTab(tabActual);


    /* =========================================
       MODAL EDITAR GRUPO
    ========================================= */

    const modal = document.getElementById('modalEditarGrupo');
    const cerrarModal = document.getElementById('cerrarModal');
    const cancelarModal = document.getElementById('cancelarModal');

    const inputId = document.getElementById('modal-id');
    const inputNombre = document.getElementById('modal-nombre');
    const inputDia = document.getElementById('modal-dia');
    const inputHora = document.getElementById('modal-hora');
    const inputNivel = document.getElementById('modal-nivel');
    const inputCurso = document.getElementById('modal-curso');
    const inputProfesor = document.getElementById('modal-profesor');
    const inputSala = document.getElementById('modal-sala');
    const inputHoraFin = document.getElementById('modal-hora-fin');
    const inputCupo = document.getElementById('modal-cupo');
    const inputFechaInicio = document.getElementById('modal-fecha-inicio');
    const inputFechaFin = document.getElementById('modal-fecha-fin');

    document.querySelectorAll('.admin-grupo-btn-icono[title="Editar grupo"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            inputId.value = boton.dataset.id;
            inputNombre.value = boton.dataset.nombre;
            inputDia.value = boton.dataset.dia;
            inputHora.value = boton.dataset.hora;
            inputNivel.value = boton.dataset.nivel;
            inputCurso.value = boton.dataset.curso;
            inputProfesor.value = boton.dataset.profesorId;
            inputSala.value = boton.dataset.salaId;
            inputHoraFin.value = boton.dataset.horaFin;
            inputCupo.value = boton.dataset.cupo;
            inputFechaInicio.value = boton.dataset.fechaInicio;
            inputFechaFin.value = boton.dataset.fechaFin;

            modal.classList.add('activo');
        });
    });

    function cerrar() {
        modal.classList.remove('activo');
    }

    cerrarModal.addEventListener('click', cerrar);
    cancelarModal.addEventListener('click', cerrar);

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            cerrar();
        }
    });

/* =========================================
   MODAL CREAR GRUPO
========================================= */

const abrirModalCrearGrupo = document.getElementById('abrirModalCrearGrupo');
const modalCrearGrupo = document.getElementById('modalCrearGrupo');
const cerrarModalCrearGrupo = document.getElementById('cerrarModalCrearGrupo');
const cancelarCrearGrupo = document.getElementById('cancelarCrearGrupo');

if (abrirModalCrearGrupo && modalCrearGrupo && cerrarModalCrearGrupo) {
    abrirModalCrearGrupo.addEventListener('click', function () {
        modalCrearGrupo.classList.add('activo');
    });

    cerrarModalCrearGrupo.addEventListener('click', function () {
        modalCrearGrupo.classList.remove('activo');
    });

cancelarCrearGrupo.addEventListener('click', function () {
    modalCrearGrupo.classList.remove('activo');
});

    modalCrearGrupo.addEventListener('click', function (e) {
        if (e.target === modalCrearGrupo) {
            modalCrearGrupo.classList.remove('activo');
        }
    });
}


});

/* =========================================
   SWEETALERT ELIMINAR ALUMNO
========================================= */

document.querySelectorAll('.btn-eliminar-alumno').forEach(function(boton){

    boton.addEventListener('click', function(e){

        e.preventDefault();

        const form = this.closest('form');
        const alumno = this.dataset.alumno;

        Swal.fire({
            title: '¿Eliminar alumno?',
            text: `¿Está seguro de eliminar a ${alumno} de este grupo?`,
            icon: 'warning',
            customClass: {
            popup: 'swal-grupos-popup'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if(result.isConfirmed){
                form.submit();
            }

        });

    });

});

/* =========================================
   SWEETALERT ALUMNO AÑADIDO
========================================= */

console.log('SweetAlert OK');


const parametrosUrl = new URLSearchParams(window.location.search);

if (parametrosUrl.get('ok') === 'alumno_agregado') {
    Swal.fire({
        title: 'Alumno añadido',
        text: 'Alumno añadido correctamente a este grupo.',
        icon: 'success',
        confirmButtonColor: '#16a34a'
    });
}

/* =========================================
   SWEETALERT ELIMINAR GRUPO
========================================= */

document.querySelectorAll('.admin-grupo-form-eliminar').forEach(function(formulario){

    formulario.addEventListener('submit', function(e){

        e.preventDefault();

        const boton = formulario.querySelector('.btn-eliminar-grupo');

        Swal.fire({
            title: 'Eliminar grupo',
            html: `
                <p style="margin-bottom:14px;">¿Estás seguro de que quieres eliminar este grupo?</p>

                <div style="
                    text-align:left;
                    border:1px solid #e5e7eb;
                    border-radius:8px;
                    padding:16px;
                    background:#f9fafb;
                    margin-top:12px;
                ">
                    <strong>${boton.dataset.dia} ${boton.dataset.hora} - ${boton.dataset.nombre}</strong><br><br>
                    🕒 ${boton.dataset.dia} - ${boton.dataset.hora}<br>
                    👨‍🏫 ${boton.dataset.profesor}<br>
                    🏷 ${boton.dataset.nivel}<br>
                    👥 ${boton.dataset.total} alumnos matriculados
                </div>

                <p style="color:#e11d48; font-size:13px; margin-top:16px;">
                    🗑 Esta acción no se puede deshacer.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar grupo',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-grupos-popup'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                formulario.submit();
            }
        });

    });

});

/* =========================================
   SWEETALERT ELIMINAR SALA
========================================= */

document.querySelectorAll('.admin-sala-form-eliminar').forEach(function(formulario){

    formulario.addEventListener('submit', function(e){

        e.preventDefault();

        Swal.fire({
            title: 'Eliminar sala',
            text: '¿Estás seguro de que quieres eliminar esta sala?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar sala',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-grupos-popup'
            }
        }).then((result) => {

            if (result.isConfirmed) {
                formulario.submit();
            }

        });

    });

});

</script>

<?php if (isset($_GET['editado'])): ?>

    <script>
    window.history.replaceState({}, document.title, window.location.pathname);

    Swal.fire({
        icon: 'success',
        title: 'Grupo editado',
        text: 'Los cambios se han guardado correctamente.',
        confirmButtonColor: '#c1121f',
        confirmButtonText: 'Aceptar'
    });
    </script>
<?php endif; ?>    

<!--Sala creada correctamente-->
<?php if (isset($_GET['creada_sala'])): ?>

<script>
window.history.replaceState({}, document.title, window.location.pathname + '?tab=salas');

Swal.fire({
    icon: 'success',
    title: 'Sala creada',
    text: 'La sala se ha creado correctamente.',
    confirmButtonColor: '#c1121f',
    confirmButtonText: 'Aceptar'
});
</script>

<?php endif; ?>

<!--Sala editada correctamente-->
<?php if (isset($_GET['editada_sala'])): ?>

<script>
window.history.replaceState({}, document.title, window.location.pathname + '?tab=salas');

Swal.fire({
    icon: 'success',
    title: 'Sala editada',
    text: 'Los cambios se han guardado correctamente.',
    confirmButtonColor: '#c1121f',
    confirmButtonText: 'Aceptar'
});
</script>

<?php endif; ?>

<!--Sala eliminada correctamente-->
<?php if (isset($_GET['eliminada_sala'])): ?>

<script>
window.history.replaceState({}, document.title, window.location.pathname + '?tab=salas');

Swal.fire({
    icon: 'success',
    title: 'Sala eliminada',
    text: 'La sala se ha eliminado correctamente.',
    confirmButtonColor: '#c1121f',
    confirmButtonText: 'Aceptar'
});
</script>

<?php endif; ?>

<!-- Alert de que no es posible eliminar grupo -->
 <?php if (isset($_GET['error']) && $_GET['error'] === 'sala_con_grupos'): ?>

<script>
Swal.fire({
    icon: 'error',
    title: 'No se puede eliminar',
    text: 'Esta sala tiene grupos asignados actualmente.',
    confirmButtonColor: '#e11d48',
    confirmButtonText: 'Aceptar'
});
</script>

<?php endif; ?>



<!-- MODAL EDITAR SALA -->
<div id="modalEditarSala" class="admin-modal">

    <div class="admin-modal-contenido">

        <div class="admin-modal-header">
            <h2>Editar sala</h2>
            <span class="admin-modal-cerrar" id="cerrarModalSala">&times;</span>
        </div>

        <p class="admin-modal-subtitulo">
            Modifica los datos de la sala
        </p>

        <form method="POST" action="<?= BASE_URL ?>/admin/salas/actualizar?tab=salas">
            <?= Csrf::field() ?>

            <input type="hidden" name="id" id="sala-id">

            <div class="admin-modal-grid">

                <div class="admin-modal-campo-full">
                    <label>Nombre sala</label>

                    <input
                        type="text"
                        name="nombre"
                        id="sala-nombre"
                        required
                    >
                </div>

                <div class="admin-modal-campo-full">
                    <label>Espacio</label>

                    <input
                        type="text"
                        name="espacio_nombre"
                        id="sala-espacio"
                        required
                    >
                </div>

                <div class="admin-modal-campo-full">
                    <label>Dirección</label>

                    <input
                        type="text"
                        name="direccion"
                        id="sala-direccion"
                        required
                    >
                </div>

            </div>

            <div class="admin-modal-footer">
                <button type="button" id="cancelarModalSala" class="admin-btn-cancelar">
                    Cancelar
                </button>

                <button type="submit" class="admin-btn-guardar">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('modalEditarSala');

    const cerrar = document.getElementById('cerrarModalSala');
    const cancelar = document.getElementById('cancelarModalSala');

    const inputId = document.getElementById('sala-id');
    const inputNombre = document.getElementById('sala-nombre');
    const inputEspacio = document.getElementById('sala-espacio');
    const inputDireccion = document.getElementById('sala-direccion');

    const modalNueva = document.getElementById('modalNuevaSala');

    const abrirNueva = document.getElementById('abrirModalSala');

    const cerrarNueva = document.getElementById('cerrarNuevaSala');

    const cancelarNueva = document.getElementById('cancelarNuevaSala');

    document.querySelectorAll('.btn-editar-sala').forEach(function (boton) {

        boton.addEventListener('click', function () {

            inputId.value = boton.dataset.id;
            inputNombre.value = boton.dataset.nombre;
            inputEspacio.value = boton.dataset.espacio;
            inputDireccion.value = boton.dataset.direccion;

            modal.classList.add('activo');
        });

    });

    function cerrarModal() {
        modal.classList.remove('activo');
    }

    cerrar.addEventListener('click', cerrarModal);
    cancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            cerrarModal();
        }
    });

    abrirNueva.addEventListener('click', function () {
    modalNueva.classList.add('activo');
});

    function cerrarModalNueva() {
        modalNueva.classList.remove('activo');
    }

    cerrarNueva.addEventListener('click', cerrarModalNueva);

    cancelarNueva.addEventListener('click', cerrarModalNueva);

    modalNueva.addEventListener('click', function (e) {
        if (e.target === modalNueva) {
            cerrarModalNueva();
        }
    });

    });
</script>

<!-- MODAL NUEVA SALA -->
<div id="modalNuevaSala" class="admin-modal">

    <div class="admin-modal-contenido">

        <div class="admin-modal-header">
            <h2>Nueva sala</h2>
            <span class="admin-modal-cerrar" id="cerrarNuevaSala">&times;</span>
        </div>

        <p class="admin-modal-subtitulo">
            Añade una nueva sala
        </p>

        <form method="POST" action="<?= BASE_URL ?>/admin/salas/crear?tab=salas">
            <?= Csrf::field() ?>

            <div class="admin-modal-grid">

                <div class="admin-modal-campo-full">
                    <label>Nombre sala</label>

                    <input
                        type="text"
                        name="nombre"
                        required
                    >
                </div>

                <div class="admin-modal-campo-full">
                    <label>Espacio</label>

                    <input
                        type="text"
                        name="espacio_nombre"
                        value="Espacio en Blanco"
                        required
                    >
                </div>

                <div class="admin-modal-campo-full">
                    <label>Dirección</label>

                    <input
                        type="text"
                        name="direccion"
                        required
                    >
                </div>

            </div>

            <div class="admin-modal-footer">

                <button
                    type="button"
                    id="cancelarNuevaSala"
                    class="admin-btn-cancelar"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="admin-btn-guardar"
                >
                    Crear sala
                </button>

            </div>

        </form>
    </div>
</div>    


<!-- SWEETALERT GRUPO CREADO -->

<?php if (isset($_GET['creado'])): ?>

<script>
window.history.replaceState({}, document.title, window.location.pathname);

Swal.fire({
    icon: 'success',
    title: 'Grupo creado',
    text: 'El grupo se ha creado correctamente.',
    confirmButtonColor: '#c1121f',
    confirmButtonText: 'Aceptar'
});
</script>

<?php endif; ?>



<!-- MODAL CREAR GRUPO -->
<div id="modalCrearGrupo" class="admin-modal">
    <div class="admin-modal-contenido" style="max-width: 520px; width:92%;">

        <div class="admin-modal-header">
            <h2>Crear Nuevo Grupo de Clases</h2>
            <span class="admin-modal-cerrar" id="cerrarModalCrearGrupo">&times;</span>
        </div>

        <p class="admin-modal-subtitulo">Completa los datos para crear un nuevo grupo</p>
<form method="POST" action="<?= BASE_URL ?>/admin/grupos/crear">
    <?= Csrf::field() ?>

    <div class="admin-form-grupo">
    <label>Nombre del grupo *</label>

    <select name="nombre" id="nombreGrupoSelect" required>
        <option value="">Selecciona un grupo</option>
        <option value="Iniciación">Iniciación</option>
        <option value="Iniciación 2º Año">Iniciación 2º Año</option>
        <option value="Avanzado">Avanzado</option>
        <option value="otro">Otro nombre</option>
    </select>

    <div id="bloqueNombrePersonalizado" style="
    display:none;
    margin-top:24px;
    margin-bottom:34px;
    padding-bottom:22px;
    border-bottom:1px dashed #cbd5e1;
">

    <div style="
        display:flex;
        align-items:center;
        gap:10px;
        color:#2563eb;
        font-weight:700;
        font-size:16px;
        border-bottom:2px solid #2563eb;
        padding-bottom:8px;
        margin-bottom:10px;
    ">
        <span style="font-size:18px;">✎</span>
        <span>Escribe un nombre</span>
    </div>

    <p style="
        margin:0 0 14px 0;
        color:#64748b;
        font-size:13px;
    ">
        Puedes escribir el nombre que desees para este grupo.
    </p>

    <input
        type="text"
        name="nombre_personalizado"
        id="nombreGrupoPersonalizado"
        placeholder="Escribe el nombre del grupo aquí..."
        style="
            width:100%;
            border:1px solid #2563eb;
            border-radius:8px;
            padding:11px 12px;
            font-size:14px;
            box-sizing:border-box;
        "
    >
</div>


<div class="admin-modal-grid">

    <div>
        <label>Día de la semana *</label>
        <select name="dia_semana" required>
            <option value="">Selecciona un día</option>
            <option value="lunes">Lunes</option>
            <option value="martes">Martes</option>
            <option value="miercoles">Miércoles</option>
            <option value="jueves">Jueves</option>
            <option value="viernes">Viernes</option>
            <option value="sabado">Sábado</option>
            <option value="domingo">Domingo</option>
        </select>
    </div>

    <div>
        <label>Hora de inicio *</label>
        <input type="time" name="hora_inicio" required>
    </div>

</div>

<div class="admin-modal-grid" style="margin-top:16px;">

    <div>
        <label>Hora fin *</label>

        <input
            type="time"
            name="hora_fin"
            required
        >
    </div>

    <div>
        <label>Aforo máximo *</label>

        <input
            type="number"
            name="aforo_maximo"
            min="1"
            value="16"
            required
        >
    </div>

</div>


<!-- NIVEL Y PROFESOR -->

<div class="admin-modal-grid">

    <div>
        <label>Curso *</label>

        <select name="curso" required>
            <option value="">Selecciona Curso</option>
            <option value="1º">2025-2026</option>
            <option value="2º">2026-2027</option>
        </select>
    </div>

    <div>
        <label>Nivel *</label>

        <select name="nivel" required>
            <option value="iniciacion">Iniciación</option>
            <option value="intermedio">Intermedio</option>
            <option value="avanzado">Avanzado</option>
        </select>
    </div>

</div>

<div class="admin-modal-grid" style="margin-top:16px;">

    <div>
        <label>Profesor *</label>

        <select name="profesor_id" required>
            <option value="">Selecciona un profesor</option>

            <?php foreach (($profesores ?? []) as $profesor): ?>
                <option value="<?= htmlspecialchars($profesor['id'], ENT_QUOTES) ?>">
                    <?= htmlspecialchars($profesor['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>    

<!-- SALA -->

<div class="admin-modal-campo-full">

    <label>Sala *</label>

    <select name="sala_id" required>

        <option value="">Selecciona una sala</option>

        <?php foreach ($salas as $sala): ?>
            <option value="<?= (int)$sala['id'] ?>">
                <?= htmlspecialchars(
                    ($sala['espacio_nombre'] ?? 'Sin espacio')
                    . ' - ' .
                    ($sala['nombre'] ?? 'Sala')
                ) ?>
            </option>
        <?php endforeach; ?>

    </select>

</div>


<div>
    <label>Fecha inicio curso *</label>

    <input
        type="date"
        name="fecha_inicio_curso"
        required
    >
</div>

<div>
    <label>Fecha fin curso *</label>

    <input
        type="date"
        name="fecha_fin_curso"
        required
    >
</div>




    <div class="admin-modal-footer">
        <button type="button" class="admin-btn-cancelar" id="cancelarCrearGrupo">
            Cancelar
        </button>

        <button type="submit" class="admin-btn-guardar">
            Crear Grupo
        </button>
    </div>

</form>


    </div>
</div>

<style>
/* Sobrescribe solo los botones del modal de grupos */
.admin-btn-guardar{
    background:#c1121f !important;
    color:#ffffff !important;
}

.admin-btn-guardar:hover{
    background:#9f0f1a !important;
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* -- Mostrar u ocultar bloque para nombre personalizado del grupo -- */

    const selectNombreGrupo = document.getElementById('nombreGrupoSelect');
    const inputNombrePersonalizado = document.getElementById('nombreGrupoPersonalizado');
    const bloqueNombrePersonalizado = document.getElementById('bloqueNombrePersonalizado');

    if (selectNombreGrupo && inputNombrePersonalizado && bloqueNombrePersonalizado) {

        selectNombreGrupo.addEventListener('change', function () {

            if (this.value === 'otro') {
                bloqueNombrePersonalizado.style.display = 'block';
                inputNombrePersonalizado.required = true;
            } else {
                bloqueNombrePersonalizado.style.display = 'none';
                inputNombrePersonalizado.required = false;
                inputNombrePersonalizado.value = '';
            }

        });

    }

});
</script>