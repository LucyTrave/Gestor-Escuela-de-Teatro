<?php $titulo = 'Dashboard Admin'; $seccion = 'dashboard'; require ROOT . '/app/views/admin/partials/header.php'; ?>
<div class="admin-dashboard-wrapper">

<div class="admin-dashboard-header">

    <div>
        <div class="etiqueta">PANEL DE ADMINISTRACIÓN</div>
        <h1>Gestor Escuela</h1>
        <div class="admin-dashboard-separador">
    <span></span>
    <div class="admin-dashboard-separador-icono">
    <img src="<?= BASE_URL ?>/public/img/mascaras-rojas.png" alt="Máscaras">
</div>
    <span></span>
</div>

<p class="admin-dashboard-subtitulo">
    Plataforma de gestión académica para alumnos, grupos y actividades.
</p>
    </div>
</div>

<section class="admin-dashboard-grid">

    <!-- Posibles alumnos -->
    <a class="admin-dashboard-link" href="<?= BASE_URL ?>/admin/posibles">
        <article class="admin-dashboard-card admin-dashboard-card-posibles">

            <div class="admin-dashboard-card-top">
                <div class="admin-dashboard-card-icono">🎓</div>

                <div class="admin-dashboard-card-textos">
                    <h2>Posibles alumnos</h2>
                    <p>Leads registrados</p>
                </div>

                <div class="admin-dashboard-contador admin-dashboard-contador-posibles">
                    <strong><?= $metricas['posibles'] ?></strong>
                    <span>registrados</span>
                </div>
            </div>

            <span class="admin-dashboard-boton">Acceder →</span>

        </article>
    </a>

    <!-- Matriculados -->
    <a class="admin-dashboard-link" href="<?= BASE_URL ?>/admin/matriculados">
        <article class="admin-dashboard-card admin-dashboard-card-matriculados">

            <div class="admin-dashboard-card-top">
                <div class="admin-dashboard-card-icono">✅</div>

                <div class="admin-dashboard-card-textos">
                    <h2>Alumnos matriculados</h2>
                    <p>Alumnos activos</p>
                </div>

                <div class="admin-dashboard-contador admin-dashboard-contador-matriculados">
                    <strong><?= $metricas['matriculados'] ?></strong>
                    <span>activos</span>
                </div>
            </div>

            <span class="admin-dashboard-boton">Acceder →</span>

        </article>
    </a>

    <!-- Grupos -->
    <a class="admin-dashboard-link" href="<?= BASE_URL ?>/admin/grupos">
        <article class="admin-dashboard-card admin-dashboard-card-grupos">

            <div class="admin-dashboard-card-top">
                <div class="admin-dashboard-card-icono">👥</div>

                <div class="admin-dashboard-card-textos">
                    <h2>Grupos / Clases</h2>
                    <p>Grupos activos</p>
                </div>

                <div class="admin-dashboard-contador admin-dashboard-contador-grupos">
                    <strong><?= $metricas['grupos'] ?></strong>
                    <span>activos</span>
                </div>
            </div>

            <span class="admin-dashboard-boton">Acceder →</span>

        </article>
    </a>

    <!-- Especiales -->
    <a class="admin-dashboard-link" href="<?= BASE_URL ?>/admin/especiales">
        <article class="admin-dashboard-card admin-dashboard-card-especiales">

            <div class="admin-dashboard-card-top">
                <div class="admin-dashboard-card-icono">🎭</div>

                <div class="admin-dashboard-card-textos">
                    <h2>Grupos especiales</h2>
                    <p>Eventos programados</p>
                </div>

                <div class="admin-dashboard-contador admin-dashboard-contador-especiales">
                    <strong><?= $metricas['eventos'] ?></strong>
                    <span>eventos</span>
                </div>
            </div>

            <span class="admin-dashboard-boton">Acceder →</span>

        </article>
    </a>

</section>

</div>
<?php require ROOT . '/app/views/admin/partials/footer.php'; ?>






<style>

/* ===== CONTENEDOR PRINCIPAL DEL DASHBOARD =====
   Controla el ancho máximo y centra el contenido.
*/
.admin-dashboard-wrapper {
    max-width: 1350px;
    margin: 0 auto;
    background: linear-gradient(
    180deg,
    #fafafa 0%,
    #f4f4f4 100%
);
}


/* =========================================================
   SEPARADOR DECORATIVO DEL HEADER
========================================================= */

.admin-dashboard-separador {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;

    margin: 22px 0 18px;
}


.admin-dashboard-separador span {
    width: 180px;
    height: 2px;
    background: #d90429;
    opacity: 0.75;
}


.admin-dashboard-separador-icono {
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-dashboard-separador-icono img {
    width: 72px;
    height: auto;
    display: block;
}




/* =========================================================
   DASHBOARD ADMIN
   Estilos exclusivos de esta vista.
========================================================= */

/* ===== CONTENEDOR PRINCIPAL ===== */
.admin-dashboard-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}

/* ===== CABECERA PRINCIPAL ===== */


.admin-dashboard-header {
    text-align: center;
    padding-top: 40px;
    margin-bottom: 26px;
}

.admin-dashboard-icono {
    font-size: 62px;
    margin-bottom: 16px;
}

.admin-dashboard-subtitulo {
    font-size: 18px;
    color: #6b7280;
    font-weight: 400;
    text-align: center;
    margin-top: 0;
}

.admin-dashboard-header h1 {
    font-size: 46px;
    margin: 8px 0 12px;
    color: #111827;
}

.admin-dashboard-header .etiqueta {
    color: #b91c1c;
    letter-spacing: 6px;
    font-size: 13px;
    font-weight: 700;
}


/* ===== GRID PRINCIPAL ===== */
.admin-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(280px, 1fr));
    gap: 28px;
    margin-top: 24px;
}

.admin-dashboard-link {
    text-decoration: none;
    color: inherit;
}

.admin-dashboard-card {
    position: relative;
    min-height: 255px;
    padding: 22px;
    border-radius: 28px;
    background: #ffffff;
    border: 1px solid #eeeeee;
    box-shadow: 0 10px 26px rgba(0,0,0,0.06);

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    transition: all .25s ease;
    text-align: left;
}
/* =========================================================
   CABECERA INTERNA DE CADA TARJETA
========================================================= */

.admin-dashboard-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}


/* Bloque icono y textos */
.admin-dashboard-card-textos {
    flex: 1;
}
.admin-dashboard-card-textos h2 {
    font-size: 24px;
    font-weight: 600;
    line-height: 1.1;
    margin-bottom: 6px;
    color: #111827;
}

/* Iconos */
.admin-dashboard-card-icono {
    width: 82px;
    height: 82px;
    border-radius: 22px;
    background: #f8f8f8;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 42px;
}


/* Contador lateral */
.admin-dashboard-contador {
    min-width: 90px;
    min-height: 90px;

    border-radius: 18px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    font-size: 15px;
}


/* Número */
.admin-dashboard-contador strong {
    font-size: 42px;
    line-height: 1;
}


/* Texto inferior */
.admin-dashboard-contador span {
    margin-top: 6px;
    font-size: 14px;
}


/* Colores individuales */
.admin-dashboard-contador-posibles {
    background: #fff1f5;
    color: #e11d48;
}

.admin-dashboard-contador-matriculados {
    background: #f0fdf4;
    color: #22c55e;
}

.admin-dashboard-contador-grupos {
    background: #f5f3ff;
    color: #7c3aed;
}

.admin-dashboard-contador-especiales {
    background: #fff7ed;
    color: #f59e0b;
}
.admin-dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 34px rgba(0,0,0,0.10);
}


.admin-dashboard-boton {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 11px 20px;
    border-radius: 8px;
    background: #c1121f;
    color: #ffffff;
    font-weight: 700;
    font-size: 16px;
    text-align: center;
}

/* ===== COLORES POR TARJETA ===== */
.admin-dashboard-card-posibles {
    
}

.admin-dashboard-card-matriculados {
    
}

.admin-dashboard-card-grupos {
  
}

.admin-dashboard-card-especiales {
   
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .admin-dashboard-grid {
        grid-template-columns: repeat(2, minmax(520px, 1fr));
}

</style>
   

</style>