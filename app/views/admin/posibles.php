<?php $titulo = 'Posibles Alumnos'; $seccion = 'posibles'; require ROOT . '/app/views/admin/partials/header.php'; ?>

<style>

/* =========================================
   POSIBLES - ESTILO VISUAL
========================================= */

.admin-posibles-card{
    background:#f8fbff;
    border:1px solid #93c5fd;
    border-radius:10px;
    padding:22px;
    box-shadow:0 2px 6px rgba(0,0,0,.05);
}

/* =========================================
   TARJETAS RESUMEN
========================================= */

.admin-resumen-posibles{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:24px;
}

.admin-resumen-posibles .tarjeta{
    border-radius:10px;
    padding:22px;
    background:#fff;
    border:1px solid #e5e7eb;
    transition:all .2s ease;
}

.admin-resumen-posibles .tarjeta:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 18px rgba(0,0,0,.08);
}

.admin-resumen-posibles .tarjeta-total{
    border-left:4px solid #111827;
}

.admin-resumen-posibles .tarjeta-prueba{
    border-left:4px solid #dc2626;
}

.admin-resumen-posibles .tarjeta-intensivo{
    border-left:4px solid #2563eb;
}

.admin-resumen-posibles .tarjeta-ex{
    border-left:4px solid #7c3aed;
}

/* =========================================
   TABLA
========================================= */

.tabla-profesor{
    width:100%;
    border-collapse:collapse;
    margin-top:18px;
}

.tabla-profesor thead th{
    text-align:left;
    font-size:12px;
    letter-spacing:1px;
    text-transform:uppercase;
    color:#6b7280;
    padding:14px 16px;
    border-bottom:1px solid #e5e7eb;
}

.tabla-profesor tbody td{
    padding:16px;
    border-bottom:1px solid #f1f5f9;
    vertical-align:middle;
}

.tabla-profesor tbody tr{
    transition:background .2s ease;
}

.tabla-profesor tbody tr:hover{
    background:#f8fafc;
}

/* =========================================
   BOTONES ACCIONES
========================================= */

.admin-acciones-tabla{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.admin-btn-icono{
    width:34px;
    height:34px;
    border:1px solid #d1d5db;
    border-radius:6px;
    background:#fff;
    display:inline-flex;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    transition:all .2s ease;
}

.admin-btn-principal.admin-btn-icono{
    color:#374151;
}

.admin-btn-icono:hover{
    background:#f3f4f6;
}

.admin-btn-eliminar{
    color:#dc2626;
}

.admin-btn-eliminar:hover{
    background:#fff1f2;
    border-color:#fecdd3;
}

.admin-btn-matricular:hover{
    background:#ecfdf5;
    border-color:#bbf7d0;
}

/* =========================================
   FILTROS
========================================= */

#panel-filtros{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:10px;
    padding:18px;
    margin:18px 0;
}

#panel-filtros input,
#panel-filtros select{
    width:100%;
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:14px;
    background:#fff;
    box-sizing:border-box;
}

#panel-filtros input:focus,
#panel-filtros select:focus{
    outline:none;
    border-color:#e11d48;
    box-shadow:0 0 0 3px rgba(225,29,72,.12);
}

@media (max-width:900px){

    .admin-resumen-posibles{
        grid-template-columns:1fr 1fr;
    }

}
.admin-btn-detalle{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    padding:8px 14px;

    border:1px solid #d1d5db;
    border-radius:8px;

    background:#ffffff;
    color:#111827;

    font-size:13px;
    font-weight:700;

    text-decoration:none;

    transition:all .2s ease;
}

.admin-btn-detalle:hover{
    background:#fff1f2;
    border-color:#f5c2c7;
    color:#b42318;
}

/* =========================================
   BOTONES POSIBLES
========================================= */

.admin-btn-principal{
    background:#DC143C;
    color:white;

    border:none;
    border-radius:8px;

    padding:10px 16px;

    font-size:13px;
    font-weight:700;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    text-decoration:none;
    cursor:pointer;

    transition:all .2s ease;

    box-shadow:0 2px 8px rgba(220,20,60,.20);
}

.admin-btn-principal:hover{
    background:#b01030;
    transform:translateY(-1px);
}

.admin-btn-secundario{
    background:#ffffff;
    color:#374151;

    border:1px solid #d1d5db;
    border-radius:8px;

    padding:10px 14px;

    font-size:13px;
    font-weight:600;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    text-decoration:none;

    transition:all .2s ease;
}

.admin-btn-secundario:hover{
    background:#fff1f2;
    border-color:#f5c2c7;
    color:#b42318;
}

.admin-btn-icono{
    width:40px;
    height:40px;

    padding:0;

    font-size:22px;
    line-height:1;
}
/* =========================================
   PANEL FILTROS POSIBLES
========================================= */

.admin-panel-filtros{
    margin-top:18px;

    padding:20px;

    border:1px solid #e5e7eb;
    border-radius:12px;

    background:#ffffff;
}

.admin-panel-filtros input,
.admin-panel-filtros select{
    width:100%;

    padding:11px 14px;

    border:1px solid #d1d5db;
    border-radius:8px;

    font-size:14px;

    background:#ffffff;

    box-sizing:border-box;

    transition:all .2s ease;
}

.admin-panel-filtros input:focus,
.admin-panel-filtros select:focus{
    outline:none;

    border-color:#e11d48;

    box-shadow:0 0 0 3px rgba(225,29,72,.12);
}

.admin-panel-filtros label{
    display:block;

    margin-bottom:6px;

    font-size:13px;
    font-weight:700;

    color:#374151;
}

.admin-grid-filtros{
    display:grid;

    grid-template-columns:repeat(4, 1fr);

    gap:14px;
}

.admin-grid-filtros-extra{
    display:grid;

    grid-template-columns:repeat(3, 1fr);

    gap:14px;

    margin-top:16px;
}

.acciones-filtros{
    display:flex;

    align-items:center;

    gap:10px;

    margin-top:20px;

    flex-wrap:wrap;
}
/* =========================================
   GRID MODAL CREAR ALUMNO
========================================= */

.admin-form-grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.admin-form-grid-2 input,
.admin-form-grid-2 select{
    width:100%;
    box-sizing:border-box;
}

.admin-form-grid-2 label{
    display:block;
    margin-bottom:6px;
    font-size:13px;
    font-weight:700;
    color:#111827;
}

</style>

<div class="etiqueta">ADMINISTRACION</div>
<h1>Posibles alumnos</h1>

<section class="admin-resumen-posibles">

    <article class="tarjeta tarjeta-total">
        <div class="tarjeta-etiqueta">Total leads</div>
        <div class="tarjeta-valor-grande"><?= $metricas['total'] ?></div>
    </article>

    <article class="tarjeta tarjeta-prueba">
        <div class="tarjeta-etiqueta">Clase de prueba</div>
        <div class="tarjeta-valor-grande"><?= $metricas['clase_prueba'] ?></div>
    </article>

    <article class="tarjeta tarjeta-intensivo">
        <div class="tarjeta-etiqueta">Interés intensivo</div>
        <div class="tarjeta-valor-grande"><?= $metricas['intensivo'] ?></div>
    </article>

    <article class="tarjeta tarjeta-ex">
        <div class="tarjeta-etiqueta">Ex alumnos</div>
        <div class="tarjeta-valor-grande"><?= $metricas['ex_alumno'] ?></div>
    </article>

</section>

<section class="admin-posibles-card">
    <div class="admin-topbar">
        <div class="titulo-seccion">Listado de leads</div>

        <div class="acciones-topbar">

            <button
                type="button"
                id="abrirModalCrearAlumno"
                class="admin-btn-secundario admin-btn-icono"
                title="Nuevo posible alumno"
            >
                +
            </button>

            <a href="#"
            onclick="toggleFiltros(); return false;"
            class="admin-btn-secundario admin-btn-icono"
            title="Filtrar">
                ☰
            </a>

        </div>

    </div>

    <div class="tabla-scroll">

<?php
$filtrosActivos =
    !empty($_GET['nombre']) ||
    !empty($_GET['email']) ||
    !empty($_GET['telefono']) ||
    !empty($_GET['nivel']) ||
    !empty($_GET['tipo_interes']) ||
    !empty($_GET['clase_prueba']) ||
    !empty($_GET['fecha_interes']) ||
    !empty($_GET['fecha_primera_clase']) ||
    !empty($_GET['fecha_registro']);
?>

<!-- Añado filtro para tabla-->
    <div id="panel-filtros"class="admin-panel-filtros" style="display: <?= isset($_GET['mostrar_filtros']) || $filtrosActivos ? 'block' : 'none' ?>;">
        <form method="GET" action="<?= BASE_URL ?>/admin/posibles">
            <div class="admin-grid-filtros">
                    <div>
                        <label>Nombre</label>
                        <input
                            type="text"
                            name="nombre"
                            placeholder="Nombre"
                            value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Email</label>

                        <input
                            type="text"
                            name="email"
                            placeholder="Email"
                            value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Teléfono</label>

                        <input
                            type="text"
                            name="telefono"
                            placeholder="Teléfono"
                            value="<?= htmlspecialchars($_GET['telefono'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Nivel</label>

                        <select name="nivel">
                            <option value="">Nivel</option>
                            <option value="iniciacion">Iniciación</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>

                    <div>
                        <label>Tipo interés</label>

                        <select name="tipo_interes">
                            <option value="">Tipo interés</option>
                            <option value="intensivo">Intensivo</option>
                            <option value="ex_alumno">Ex alumno</option>
                        </select>
                    </div>

                    <div>
                        <label>Clase prueba</label>

                        <select name="clase_prueba">
                            <option value="">Clase prueba</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
            </div>

            <div id="filtros-extra"
                style="display: <?= (!empty($_GET['fecha_interes']) || !empty($_GET['fecha_primera_clase']) || !empty($_GET['fecha_registro'])) ? 'grid' : 'none' ?>;"
                class="admin-grid-filtros-extra">
                    <div>
                        <label>Fecha interés</label>
                        <input type="date" name="fecha_interes"
                                    value="<?= htmlspecialchars($_GET['fecha_interes'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Primera clase</label>
                        <input type="date" name="fecha_primera_clase"
                                    value="<?= htmlspecialchars($_GET['fecha_primera_clase'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Fecha registro</label>
                        <input type="date" name="fecha_registro"
                                    value="<?= htmlspecialchars($_GET['fecha_registro'] ?? '') ?>">
                    </div>
            </div>
              

            <div class="acciones-filtros">

                <button class="admin-btn-principal">
                    Filtrar
                </button>

                <a href="#"
                onclick="toggleMasFiltros(); return false;"
                class="admin-btn-secundario"
                title="Mostrar más filtros">
                    Mostrar más filtros
                </a>

                <a href="<?= BASE_URL ?>/admin/posibles?mostrar_filtros=1"
                class="admin-btn-secundario">
                    Limpiar
                </a>

            </div>
        </form>
    </div>

        <table class="tabla-profesor">
            <thead><tr><th>Nombre y apellidos</th><th>Email</th><th>Telefono</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($alumnos)): ?><tr><td colspan="4" class="vacio-tabla">No hay posibles alumnos.</td></tr><?php endif; ?>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></td>
                        <td><?= htmlspecialchars($alumno['email'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($alumno['telefono'] ?: '-') ?></td>
                        <td>
                            <div class="admin-acciones-tabla">

                                <a class="admin-btn-detalle"
                                href="<?= BASE_URL ?>/admin/alumnos/detalle?id=<?= (int)$alumno['id'] ?>&origen=posibles">
                                    Ver detalle
                                </a>

                                <a class="admin-btn-icono"
                                title="Editar alumno"
                                href="<?= BASE_URL ?>/admin/alumnos/editar?id=<?= (int)$alumno['id'] ?>">
                                    &#9998;
                                </a>

                                <form method="POST" action="<?= BASE_URL ?>/admin/alumnos/matricular">
                                    <?= Csrf::field() ?>

                                    <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">

                                    <button
                                        type="submit"
                                        class="admin-btn-icono admin-btn-matricular btn-matricular-alumno"
                                        title="Matricular alumno"

                                        data-nombre="<?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos'], ENT_QUOTES) ?>"
                                    >
                                        🎓
                                    </button>
                                </form>

                                <form method="POST" action="<?= BASE_URL ?>/admin/alumnos/eliminar">
                                    <?= Csrf::field() ?>

                                    <input type="hidden" name="id" value="<?= (int)$alumno['id'] ?>">
                                    <input type="hidden" name="origen" value="posibles">

                                    <button
                                        type="submit"
                                        class="admin-btn-icono admin-btn-eliminar btn-eliminar-alumno"
                                        data-nombre="<?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos'], ENT_QUOTES) ?>"
                                    >
                                        <span style="font-size:15px;">🗑</span>
                                    </button>
                                </form>

                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Script para boton de filtro -->
<script>
    function toggleFiltros() {
        const panel = document.getElementById('panel-filtros');
        if (!panel) return; // evita errores

        panel.style.display = (panel.style.display === 'none') ? 'block' : 'none';
    }
</script>
<!-- Script para filtros extra -->
<script>
    function toggleMasFiltros() {
        const panel = document.getElementById('filtros-extra');
        if (!panel) return;

        panel.style.display = (panel.style.display === 'none') ? 'grid' : 'none';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

/* =========================================
   SWEETALERT ELIMINAR POSIBLE ALUMNO
========================================= */

document.querySelectorAll('.btn-eliminar-alumno').forEach(function(boton){

    boton.addEventListener('click', function(e){

        e.preventDefault();

        const formulario = boton.closest('form');

        const nombre = boton.dataset.nombre;

        Swal.fire({
            title: 'Eliminar alumno',
            html: `
                <p style="margin-bottom:14px;">
                    ¿Seguro que quieres eliminar este posible alumno?
                </p>

                <div style="
                    text-align:left;
                    border:1px solid #e5e7eb;
                    border-radius:8px;
                    padding:16px;
                    background:#f9fafb;
                    margin-top:12px;
                ">
                    👤 <strong>${nombre}</strong>
                </div>

                <p style="
                    color:#e11d48;
                    font-size:13px;
                    margin-top:16px;
                ">
                    🗑 Esta acción no se puede deshacer.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if(result.isConfirmed){
                formulario.submit();
            }

        });

    });

});

/* =========================================
   SWEETALERT MATRICULAR ALUMNO
========================================= */

document.querySelectorAll('.btn-matricular-alumno').forEach(function(boton){

    boton.addEventListener('click', function(e){
         console.log('CLICK SWEET ALERT');

        e.preventDefault();

        const formulario = boton.closest('form');

        Swal.fire({
            title: 'Matricular alumno',
            html: `
                <p style="margin-bottom:14px;">
                    ¿Quieres matricular a este alumno?
                </p>

                <div style="
                    border:1px solid #e5e7eb;
                    border-radius:10px;
                    padding:16px;
                    background:#f9fafb;
                    text-align:left;
                ">
                    👤 <strong>${boton.dataset.nombre}</strong>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, matricular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if(result.isConfirmed){
                formulario.submit();
            }

        });

    });

});

/* =========================================
   MODAL CREAR ALUMNO
========================================= */

window.addEventListener('load', function () {

    const abrirModalCrearAlumno = document.getElementById('abrirModalCrearAlumno');

    const modalCrearAlumno = document.getElementById('modalCrearAlumno');

    const cerrarModalCrearAlumno = document.getElementById('cerrarModalCrearAlumno');

    const cancelarCrearAlumno = document.getElementById('cancelarCrearAlumno');

    console.log(abrirModalCrearAlumno);
    console.log(modalCrearAlumno);

    if (
        abrirModalCrearAlumno &&
        modalCrearAlumno &&
        cerrarModalCrearAlumno
    ) {

        abrirModalCrearAlumno.addEventListener('click', function () {

            modalCrearAlumno.classList.add('activo');

        });

        cerrarModalCrearAlumno.addEventListener('click', function () {

            modalCrearAlumno.classList.remove('activo');

        });

        cancelarCrearAlumno.addEventListener('click', function () {

            modalCrearAlumno.classList.remove('activo');

        });

        modalCrearAlumno.addEventListener('click', function (e) {

            if (e.target === modalCrearAlumno) {

                modalCrearAlumno.classList.remove('activo');

            }

        });

    }

});

const cerrarModalCrearAlumno = document.getElementById('cerrarModalCrearAlumno');

const cancelarCrearAlumno = document.getElementById('cancelarCrearAlumno');

if (
    abrirModalCrearAlumno &&
    modalCrearAlumno &&
    cerrarModalCrearAlumno
) {

    abrirModalCrearAlumno.addEventListener('click', function () {

        modalCrearAlumno.classList.add('activo');

    });

    cerrarModalCrearAlumno.addEventListener('click', function () {

        modalCrearAlumno.classList.remove('activo');

    });

    cancelarCrearAlumno.addEventListener('click', function () {

        modalCrearAlumno.classList.remove('activo');

    });

    modalCrearAlumno.addEventListener('click', function (e) {

        if (e.target === modalCrearAlumno) {

            modalCrearAlumno.classList.remove('activo');

        }

    });

}
</script>

<?php if (isset($_GET['ok']) && $_GET['ok'] === 'eliminado'): ?>

<script>

window.history.replaceState({}, document.title, window.location.pathname);

Swal.fire({
    icon: 'success',
    title: 'Alumno eliminado',
    text: 'El posible alumno se ha eliminado correctamente.',
    confirmButtonColor: '#e11d48',
    confirmButtonText: 'Aceptar'
});

</script>

<?php endif; ?>

<?php if (isset($_GET['ok']) && $_GET['ok'] === 'matriculado'): ?>

<script>

window.history.replaceState({}, document.title, window.location.pathname);

Swal.fire({
    icon: 'success',
    title: 'Alumno matriculado',
    text: 'El alumno se ha matriculado correctamente.',
    confirmButtonColor: '#16a34a',
    confirmButtonText: 'Aceptar'
});

</script>

<?php endif; ?>

<?php if (isset($_GET['ok']) && $_GET['ok'] === 'creado'): ?>

<script>

window.history.replaceState({}, document.title, window.location.pathname);

Swal.fire({
    icon: 'success',
    title: 'Alumno creado',
    text: 'El posible alumno se ha creado correctamente.',
    confirmButtonColor: '#16a34a',
    confirmButtonText: 'Aceptar'
});

</script>

<?php endif; ?>

<!-- MODAL CREAR POSIBLE ALUMNO -->

<div id="modalCrearAlumno" class="admin-modal">

    <div class="admin-modal-contenido" style="max-width:720px; width:92%;">

        <div class="admin-modal-header">
            <h2>Nuevo posible alumno</h2>

            <span
                class="admin-modal-cerrar"
                id="cerrarModalCrearAlumno"
            >
                &times;
            </span>
        </div>

        <p class="admin-modal-subtitulo">
            Completa los datos del alumno
        </p>

        <form 
            id="formCrearAlumno"
            action="<?= BASE_URL ?>/admin/alumnos/guardar"
            method="POST"
            class="admin-form-grid-2"
        >

            <?= Csrf::field() ?>

            <input
                type="hidden"
                name="origen"
                value="posibles"
            >

            <input
                type="text"
                name="nombre"
                placeholder="Nombre"
                required
            >

            <input
                type="text"
                name="apellidos"
                placeholder="Apellidos"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
            >

            <input
                type="text"
                name="telefono"
                placeholder="Teléfono"
            >

            <select name="nivel">

                <option value="">Nivel</option>

                <option value="iniciacion">
                    Iniciación
                </option>

                <option value="intermedio">
                    Intermedio
                </option>

                <option value="avanzado">
                    Avanzado
                </option>

            </select>

            <select name="tipo_interes">

                <option value="">
                    Tipo interés
                </option>

                <option value="intensivo">
                    Intensivo
                </option>

                <option value="ex_alumno">
                    Ex alumno
                </option>

                <option value="sin_horario">
                    Sin horario
                </option>

                <option value="no_insistir">
                    No insistir
                </option>

            </select>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; grid-column:1 / span 2; margin-top:10px;">

                <div>
                    <label>Fecha interés</label>

                    <input
                        type="date"
                        name="fecha_interes"
                    >
                </div>

                <div>
                    <label>Primera clase</label>

                    <input
                        type="date"
                        name="fecha_primera_clase"
                    >
                </div>

            </div>

            <div style="max-width:340px;">

                <div style="grid-column:1 / span 2; max-width:340px;">
                
                <select name="clase_prueba">

                        <option value="0">
                            Sin clase de prueba
                        </option>

                        <option value="1">
                            Con clase de prueba
                        </option>

                </select>

            </div>

        </form>
        <div class="admin-modal-footer">

            <button
                type="button"
                id="cancelarCrearAlumno"
                class="admin-btn-cancelar"
            >
                Cancelar
            </button>
            <button
                type="submit"
                class="admin-btn-guardar"
            >                    
                Crear alumno
           </button>

        </div>



    </div>

</div>


<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>
