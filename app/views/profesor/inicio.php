<?php
// =============================================================================
// Vista: Panel de inicio del profesor (pagina autonoma)
// Variables disponibles desde ProfesorController::showPanel():
//   $gruposActivos, $alumnosActivos, $proximasClases, $mensaje
// =============================================================================
$vista = 'inicio';
require __DIR__ . '/_header.php';
?>

            <div class="etiqueta">PANEL DE PROFESORADO</div>
            <h1>Gestión diaria del aula</h1>

            <section class="tarjetas tarjetas-profesor">
                <a class="tarjeta-enlace" href="<?= BASE_URL ?>/profesor/grupos" title="Ir a Grupos">
                    <article class="tarjeta tarjeta-roja">
                        <div class="tarjeta-etiqueta">Grupos activos</div>
                        <div class="tarjeta-valor-grande"><?= (int)$gruposActivos ?></div>
                        <div class="tarjeta-sub">Sesiones activas en tu planificación</div>
                        <span class="tarjeta-flecha" aria-hidden="true">&rarr;</span>
                    </article>
                </a>
                <a class="tarjeta-enlace" href="<?= BASE_URL ?>/profesor/alumnos" title="Ir a Alumnos">
                    <article class="tarjeta tarjeta-negra">
                        <div class="tarjeta-etiqueta">Alumnado vinculado</div>
                        <div class="tarjeta-valor-grande"><?= (int)$alumnosActivos ?></div>
                        <div class="tarjeta-sub">Personas asignadas a tus grupos</div>
                        <span class="tarjeta-flecha" aria-hidden="true">&rarr;</span>
                    </article>
                </a>
                <a class="tarjeta-enlace" href="#agenda-inmediata" title="Ir a la agenda inmediata">
                    <article class="tarjeta tarjeta-roja">
                        <div class="tarjeta-etiqueta">Próximas clases</div>
                        <div class="tarjeta-valor-grande"><?= count($proximasClases) ?></div>
                        <div class="tarjeta-sub">Vista rápida de agenda inmediata</div>
                        <span class="tarjeta-flecha" aria-hidden="true">&darr;</span>
                    </article>
                </a>
            </section>

            <!-- Tarjetas de inicio -->
            <section class="profesor-grid">

                <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/asistencia">
                    <article class="profesor-card">
                        <i class="fas fa-check-circle icono-card" aria-hidden="true"></i>
                        <div class="titulo-seccion">Asistencia</div>
                        <p>Pase de lista y avisos por clase.</p>
                    </article>
                </a>

                <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/grupos">
                    <article class="profesor-card">
                        <i class="fas fa-users icono-card" aria-hidden="true"></i>
                        <div class="titulo-seccion">Grupos</div>
                        <p>Gestión de horarios, grupos y salas.</p>
                    </article>
                </a>

                <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/alumnos">
                    <article class="profesor-card">
                        <i class="fas fa-user-graduate icono-card" aria-hidden="true"></i>
                        <div class="titulo-seccion">Alumnos</div>
                        <p>Alta y asignación de grupo.</p>
                    </article>
                </a>

                <a class="profesor-card-link" href="<?= BASE_URL ?>/profesor/calendario">
                    <article class="profesor-card">
                        <i class="fas fa-calendar-alt icono-card" aria-hidden="true"></i>
                        <div class="titulo-seccion">Calendario</div>
                        <p>Vista mensual de tus clases.</p>
                    </article>
                </a>

            </section>

            <?php
            // Iconos y etiquetas por tipo de grupo (valores reales del ENUM en BBDD: grupo.tipo).
            // Mantener sincronizado con la tabla `grupo` y con asistencia.php.
            $tiposGrupo = [
                'teatro'        => ['icono' => '🎬', 'etiqueta' => 'Teatro'],
                'improvisacion' => ['icono' => '⚡', 'etiqueta' => 'Improvisación'],
                'actuacion'     => ['icono' => '🎭', 'etiqueta' => 'Actuación'],
                'danza'         => ['icono' => '💃', 'etiqueta' => 'Danza'],
                'canto'         => ['icono' => '🎤', 'etiqueta' => 'Canto'],
            ];
            $tipoInfoPara = function (?string $tipo) use ($tiposGrupo): array {
                $clave = strtolower((string)$tipo);
                return $tiposGrupo[$clave]
                    ?? ['icono' => '🎓', 'etiqueta' => $clave !== '' ? ucfirst($clave) : 'Sin tipo'];
            };
            $diasSemana = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
            $hoy        = new DateTime('today');
            ?>

            <section class="profesor-seccion" id="agenda-inmediata">
                <div class="agenda-cabecera">
                    <div class="titulo-seccion titulo-seccion-sin-margen">
                        <i class="fas fa-calendar-alt icono-titulo" aria-hidden="true"></i>
                        Agenda inmediata
                    </div>
                    <div class="agenda-leyenda" aria-label="Leyenda de colores">
                        <span class="agenda-leyenda-item"><span class="agenda-leyenda-punto agenda-leyenda-naranja" aria-hidden="true"></span> Siguiente</span>
                        <span class="agenda-leyenda-item"><span class="agenda-leyenda-punto agenda-leyenda-verde" aria-hidden="true"></span> Próxima</span>
                        <span class="agenda-leyenda-item"><span class="agenda-leyenda-punto agenda-leyenda-azul" aria-hidden="true"></span> Programada</span>
                    </div>
                </div>

                <?php if (empty($proximasClases)): ?>
                    <div class="agenda-vacio">
                        <i class="fas fa-calendar-times icono-vacio" aria-hidden="true"></i>
                        <p>No hay clases programadas por ahora.</p>
                        <p class="agenda-vacio-sub">Cuando se asignen clases a tus grupos aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <?php $clasesAgenda = $proximasClases; ?>
                    <div class="agenda-lista">
                        <?php foreach ($clasesAgenda as $idx => $clase): ?>
                            <?php
                                $fechaClase = new DateTime($clase['fecha']);
                                $diff       = (int)$hoy->diff($fechaClase)->format('%r%a');
                                if ($diff === 0)      { $proxLabel = 'HOY';     $proxClase = 'agenda-hoy'; }
                                elseif ($diff === 1)  { $proxLabel = 'MAÑANA';  $proxClase = 'agenda-manana'; }
                                else                  { $proxLabel = $diasSemana[(int)$fechaClase->format('w')]; $proxClase = 'agenda-futuro'; }
                                $infoTipo      = $tipoInfoPara($clase['tipo'] ?? null);
                                $emoji         = $infoTipo['icono'];
                                $etiquetaTipo  = $infoTipo['etiqueta'];
                                $confirm  = (int)($clase['confirmados'] ?? 0);
                                $avisos   = (int)($clase['avisados']   ?? 0);
                                $total    = (int)($clase['total_alumnos'] ?? 0);
                                // Prioridad por posicion: 0=siguiente(naranja), 1=proxima(azul), 2=programada(verde)
                                $prioClase = ['agenda-prio-siguiente','agenda-prio-proxima','agenda-prio-programada'][$idx] ?? 'agenda-prio-programada';
                                $esSiguiente = ($idx === 0);
                            ?>
                            <a class="agenda-card <?= $proxClase ?> <?= $prioClase ?>" href="<?= BASE_URL ?>/profesor/asistencia?clase_id=<?= (int)$clase['id'] ?>">
                                <?php if ($esSiguiente): ?>
                                    <span class="agenda-badge-siguiente"><span aria-hidden="true">&#128276;</span> CLASE PRÓXIMA</span>
                                <?php endif; ?>
                                <div class="agenda-emoji" aria-hidden="true"><?= $emoji ?></div>

                                <div class="agenda-fecha">
                                    <span class="agenda-prox-badge"><?= $proxLabel ?></span>
                                    <span class="agenda-dia"><?= $fechaClase->format('d') ?></span>
                                    <span class="agenda-mes"><?= strtoupper($fechaClase->format('M')) ?></span>
                                </div>

                                <div class="agenda-info">
                                    <div class="agenda-nombre"><?= htmlspecialchars($clase['nombre']) ?></div>
                                    <div class="agenda-meta">
                                        <span class="agenda-meta-item"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?= htmlspecialchars($clase['sala'] ?? 'Sin sala') ?></span>
                                        <span class="agenda-meta-item"><i class="fas fa-tag" aria-hidden="true"></i> <?= htmlspecialchars($etiquetaTipo) ?></span>
                                    </div>
                                </div>

                                <div class="agenda-hora">
                                    <i class="far fa-clock" aria-hidden="true"></i>
                                    <?= htmlspecialchars(substr($clase['hora_inicio'], 0, 5)) ?>
                                </div>

                                <div class="agenda-contadores">
                                    <?php if ($total > 0): ?>
                                        <span class="agenda-cont agenda-cont-ok" title="Confirmados">
                                            <i class="fas fa-check" aria-hidden="true"></i> <?= $confirm ?>
                                        </span>
                                        <?php if ($avisos > 0): ?>
                                            <span class="agenda-cont agenda-cont-aviso" title="Avisados">
                                                <i class="fas fa-times" aria-hidden="true"></i> <?= $avisos ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="agenda-cont-total" title="Total alumnos">/ <?= $total ?></span>
                                    <?php else: ?>
                                        <span class="agenda-cont agenda-cont-sin">Sin alumnos</span>
                                    <?php endif; ?>
                                </div>

                                <div class="agenda-flecha" aria-hidden="true">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

<?php require __DIR__ . '/_footer.php'; ?>
