<?php

/**
 * @var string $nombreComunidad
 * @var string $direccion
 * @var bool $tieneReservaHoy
 * @var int|null $votacionesPendientes
 * @var string|null $nombreVivienda
 */
include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <div class="container-fluid p-0">

                <!-- 1. BANNER PRINCIPAL "MI COMUNIDAD" -->
                <div class="card border-0 mb-4 overflow-hidden shadow-sm" style="min-height: 200px;">
                    <img src="public/assets/img/banner.jpeg" alt="Residencial" class="card-img w-100 h-100 object-fit-cover position-absolute" style="filter: brightness(0.6);">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                <i class="bi bi-house-door-fill fs-4 text-white"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: var(--fuente-titulos);"><?= htmlspecialchars($nombreVivienda) ?></h2>
                                <p class="mb-1 fw-semibold text-white" style="font-size: 1.1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                                    <?= htmlspecialchars($nombreComunidad) ?>
                                </p>
                                <div class="d-flex align-items-center gap-1 text-white opacity-75 small">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?= htmlspecialchars($direccion) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. NOTIFICACIONES RECIENTES -->
                <div class="actions-container mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-bell-fill text-primary fs-5"></i>
                        <h5 class="mb-0 fs-6" style="font-family: var(--fuente-titulos);">Notificaciones</h5>
                    </div>

                    <div class="action-list">
                        <?php if (!empty($notificaciones)): ?>
                            <?php foreach ($notificaciones as $notif): ?>
                                <a href="<?= htmlspecialchars($notif['link']) ?>" class="text-decoration-none d-block mb-2">
                                    <div class="action-item" style="border-left-color: <?= htmlspecialchars($notif['border_color'] ?? 'var(--bs-primary)') ?>;">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="<?= htmlspecialchars($notif['icon'] ?? 'bi-bell-fill') ?> fs-5 <?= htmlspecialchars($notif['text_color'] ?? 'text-primary') ?>"></i>
                                            <span class="text-sm-custom text-dark fw-medium"><?= htmlspecialchars($notif['mensaje']) ?></span>
                                        </div>
                                        <i class="bi bi-arrow-right text-muted"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3 text-center text-muted border rounded bg-white shadow-sm">
                                <p class="mb-0 small">No tienes notificaciones pendientes.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3. GRID DE ACCESO RÁPIDO -->
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">

                    <!-- Tarjeta 1 - Comunicaciones -->
                    <div class="col">
                        <a href="index.php?route=comunicaciones/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card position-relative">
                                <?php if (isset($comunicacionesPendientes) && $comunicacionesPendientes > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                        <?= $comunicacionesPendientes ?>
                                        <span class="visually-hidden">avisos nuevos</span>
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(34, 28, 53, 0.1);">
                                        <i class="bi bi-megaphone-fill text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Comunicaciones</h5>
                                    <p class="card-text text-muted small mb-0">Avisos de la comunidad</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta - Foro Vecinal -->
                    <div class="col">
                        <a href="index.php?route=foro/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(34, 28, 53, 0.1);">
                                        <i class="fa-solid fa-comments text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Foro Vecinal</h5>
                                    <p class="card-text text-muted small mb-0">Debate con tus vecinos</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 2 - Reservas -->
                    <div class="col">
                        <a href="index.php?route=reserva/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card position-relative">
                                <?php if (isset($tieneReservaHoy) && $tieneReservaHoy): ?>
                                    <span id="burbuja-reservas-hoy" class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle badge-reserva-hoy">
                                        <span class="visually-hidden">Reserva para hoy</span>
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(92, 178, 68, 0.1);">
                                        <i class="bi bi-calendar-check-fill text-success fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Reservas</h5>
                                    <p class="card-text text-muted small mb-0">Pistas y zonas comunes</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 3 - Reuniones -->
                    <div class="col">
                        <a href="index.php?route=reunion/reuniones" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(219, 145, 47, 0.1);">
                                        <i class="bi bi-people-fill text-warning fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Reuniones</h5>
                                    <p class="card-text text-muted small mb-0">Juntas y convocatorias</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Tarjeta 4 - Votaciones -->
                    <div class="col">
                        <a href="index.php?route=votacion/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card position-relative">
                                <?php if ($votacionesPendientes > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                        <?= $votacionesPendientes ?>
                                        <span class="visually-hidden">votaciones pendientes</span>
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(92, 178, 68, 0.1);">
                                        <i class="fa-solid fa-check-to-slot text-success fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Votaciones</h5>
                                    <p class="card-text text-muted small mb-0">Participa en decisiones</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 5 - Incidencias -->
                    <div class="col">
                        <a href="index.php?route=incidencias/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(164, 30, 52, 0.1);">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Incidencias</h5>
                                    <p class="card-text text-muted small mb-0">Reportar averías</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 6 - Parking -->
                    <div class="col">
                        <a href="index.php?route=matricula/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(34, 28, 53, 0.1);">
                                        <i class="fa-solid fa-car text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Parking</h5>
                                    <p class="card-text text-muted small mb-0">Registro matrículas</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 7 - Finanzas -->
                    <div class="col">
                        <a href="index.php?route=finanzas/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(34, 28, 53, 0.1);">
                                        <i class="fa-solid fa-wallet text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Finanzas</h5>
                                    <p class="card-text text-muted small mb-0">Recibos y cuentas</p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

                <!-- 4. PANELES INFERIORES -->
                <div class="row g-3 mb-4">

                    <!-- ÚLTIMO COMUNICADO -->
                    <div class="card module-card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom border-light d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-megaphone me-2 text-primary"></i> Comunicados Recientes
                            </h5>
                            <a href="index.php?route=comunicaciones/index" class="btn btn-sm btn-outline-primary">Ver todos</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($listaComs)): ?>
                                <!-- ACORDEÓN DE BOOTSTRAP -->
                                <div class="accordion accordion-flush" id="accordionComunicaciones">
                                    <!-- Mostramos solo los 5 comunicados más recientes para no saturar el dashboard -->
                                    <?php foreach (array_slice($listaComs, 0, 5) as $index => $com): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingCom-<?= $com['id_comunicado'] ?>">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCom-<?= $com['id_comunicado'] ?>" aria-expanded="false" aria-controls="collapseCom-<?= $com['id_comunicado'] ?>">
                                                    <div class="d-flex flex-column w-100 me-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <div class="d-flex align-items-center text-truncate" style="max-width: 80%;">
                                                                <span class="fw-bold text-truncate">
                                                                    <?= htmlspecialchars($com['titulo']) ?>
                                                                </span>
                                                                <?php if (($com['tipo'] ?? '') === 'urgente'): ?>
                                                                    <i class="bi bi-exclamation-triangle-fill text-danger ms-2" title="Urgente"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                            <small class="text-muted text-nowrap" style="font-size: 0.8rem;">
                                                                <?= date('d/m/Y', strtotime($com['fecha_creacion'] ?? $com['fecha_publicacion'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseCom-<?= $com['id_comunicado'] ?>" class="accordion-collapse collapse" aria-labelledby="headingCom-<?= $com['id_comunicado'] ?>" data-bs-parent="#accordionComunicaciones">
                                                <div class="accordion-body text-secondary" style="font-size: 0.95rem;">
                                                    <?= nl2br(htmlspecialchars($com['cuerpo'] ?? 'No hay descripción disponible.')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox fs-2 mb-2 d-block opacity-50"></i>
                                    <p class="mb-0">No hay comunicados recientes.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<script src="public/assets/js/dashboard_vecino.js"></script>