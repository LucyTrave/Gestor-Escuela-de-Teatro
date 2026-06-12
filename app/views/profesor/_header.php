<?php
// =============================================================================
// Partial: cabecera comun a todas las vistas del profesor.
// Variable esperada: $vista (string) -> identifica la pestaña activa.
// Uso: $vista = 'alumnos'; require __DIR__ . '/_header.php';
// =============================================================================
$vista = $vista ?? '';
$titulosVista = [
    'inicio'      => 'Profesor | Gestor Escuela',
    'alumnos'     => 'Alumnos | Gestor Escuela',
    'grupos'      => 'Grupos | Gestor Escuela',
    'clases'      => 'Clases | Gestor Escuela',
    'asistencia'  => 'Asistencia | Gestor Escuela',
    'calendario'  => 'Calendario | Gestor Escuela',
];

$titulo = $titulosVista[$vista] ?? 'Profesor | Gestor Escuela';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="pagina-dashboard">
    <header class="cabecera-dashboard">
        <div>
            <div class="marca">GESTOR ESCUELA
                
            </div>
            <div class="subtitulo">ÁREA PROFESORADO</div>
        </div>
        <nav>
            <a class="enlace-nav<?= $vista === 'inicio'      ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor">Inicio</a>
            <a class="enlace-nav<?= $vista === 'alumnos'     ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/alumnos">Alumnos</a>
            <a class="enlace-nav<?= $vista === 'grupos'      ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/grupos">Grupos</a>
            <a class="enlace-nav<?= $vista === 'clases'      ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/clases">Clases</a>
            <a class="enlace-nav<?= $vista === 'calendario'  ? ' activo' : '' ?>" href="<?= BASE_URL ?>/profesor/calendario">Calendario</a>
        </nav>
        <div class="profesor-usuario">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></strong></span>
            <a class="enlace-cerrar" href="<?= BASE_URL ?>/logout">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenido contenido-profesor">
        <?php if (!empty($mensaje['texto'])): ?>
            <div id="aviso-flash"
                 class="aviso aviso-<?= htmlspecialchars($mensaje['tipo']) ?>"
                 data-tipo="<?= htmlspecialchars($mensaje['tipo']) ?>"
                 data-texto="<?= htmlspecialchars($mensaje['texto'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($mensaje['texto']) ?>
            </div>
        <?php endif; ?>

        <script>
        // ─── Helpers globales de SweetAlert2 para el área profesor ──────────────
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') return;

            // 1) Convertir mensaje flash (banner) en alerta centrada.
            var banner = document.getElementById('aviso-flash');
            if (banner) {
                var tipo  = banner.getAttribute('data-tipo')  || '';
                var texto = banner.getAttribute('data-texto') || '';
                var iconoAlerta = (tipo === 'exito')   ? 'success'
                               : (tipo === 'warning') ? 'warning'
                               : (tipo === 'error')   ? 'error'
                               : 'info';
                banner.style.display = 'none';
                Swal.fire({
                    position: 'center',
                    icon: iconoAlerta,
                    title: texto,
                    showConfirmButton: false,
                    timer: 2200,
                    timerProgressBar: true
                });
            }

            // 2) Confirmación bonita: cualquier <form data-swal-confirm="texto"> abre Swal antes de enviar.
            document.querySelectorAll('form[data-swal-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (ev) {
                    if (form.dataset.swalConfirmed === '1') return; // ya confirmado, dejar pasar
                    ev.preventDefault();
                    var titulo  = form.getAttribute('data-swal-title') || '¿Confirmar?';
                    var texto   = form.getAttribute('data-swal-confirm') || '';
                    var btnOk   = form.getAttribute('data-swal-ok')      || 'Sí, continuar';
                    var btnCan  = form.getAttribute('data-swal-cancel')  || 'Cancelar';
                    var icono   = form.getAttribute('data-swal-icon')    || 'warning';
                    var peligro = form.hasAttribute('data-swal-danger');
                    Swal.fire({
                        title: titulo,
                        text: texto,
                        icon: icono,
                        showCancelButton: true,
                        confirmButtonText: btnOk,
                        cancelButtonText: btnCan,
                        reverseButtons: true,
                        confirmButtonColor: peligro ? '#DC2626' : '#0F6E56',
                        cancelButtonColor: '#6B7280',
                        focusCancel: true
                    }).then(function (res) {
                        if (res.isConfirmed) {
                            form.dataset.swalConfirmed = '1';
                            form.submit();
                        }
                    });
                });
            });

            // 3) Helper expuesto por si una vista quiere lanzar una alerta centrada.
            window.toastProfesor = function (tipo, texto, timer) {
                Swal.fire({
                    position: 'center',
                    icon: tipo || 'info',
                    title: texto || '',
                    showConfirmButton: false,
                    timer: timer || 2200,
                    timerProgressBar: true
                });
            };
        });
        </script>
