<?php

/**
 * @var string $nombreComunidad
 * @var string $direccion
 * @var string $nombreVivienda
 * @var int $votacionesPendientes
 */
include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <div class="container-fluid p-0">

                <div class="card border-0 mb-4 overflow-hidden shadow-sm banner-presi-card">
                    <img src="public/assets/img/banner.jpeg" alt="Residencial" class="card-img w-100 h-100 object-fit-cover position-absolute banner-presi-img">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0 glass-icon-48">
                                <i class="bi bi-house-door-fill fs-4 text-white"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold text-white text-shadow-main font-titulos">Panel de Presidencia</h2>
                                <p class="mb-1 fw-semibold text-white text-shadow-sub fs-5">
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

                <div class="actions-container mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-bell-fill text-primary fs-5"></i>
                        <h5 class="mb-0 fs-6 font-titulos">Notificaciones</h5>
                    </div>

                    <div class="action-list">
                        <?php if (!empty($notificaciones)): ?>
                            <?php 
                            $visible_count = 4;
                            $total_notifs = count($notificaciones);
                            $visible_notifs = array_slice($notificaciones, 0, $visible_count);
                            $hidden_notifs = array_slice($notificaciones, $visible_count);
                            ?>

                            <?php foreach ($visible_notifs as $notif): ?>
                                <a href="<?= htmlspecialchars($notif['link']) ?>" class="text-decoration-none d-block mb-2">
                                    <div class="action-item" style="border-left-color: <?= htmlspecialchars($notif['border_color'] ?? 'var(--bs-primary)') ?>;">
                                        <div class="d-flex align-items-center gap-3 w-100">
                                            <i class="<?= htmlspecialchars($notif['icon'] ?? 'bi-bell-fill') ?> fs-5 <?= htmlspecialchars($notif['text_color'] ?? 'text-primary') ?>"></i>
                                            <span class="text-sm-custom text-dark fw-medium flex-grow-1"><?= htmlspecialchars($notif['mensaje']) ?></span>
                                            <?php if (!empty($notif['badge'])): ?>
                                                <span class="badge <?= htmlspecialchars($notif['badge']['class']) ?> ms-2"><?= htmlspecialchars($notif['badge']['text']) ?></span>
                                            <?php else: ?>
                                                <i class="bi bi-arrow-right text-muted ms-2"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>

                            <?php if ($total_notifs > $visible_count): ?>
                                <div class="collapse" id="collapseNotificaciones">
                                    <?php foreach ($hidden_notifs as $notif): ?>
                                        <a href="<?= htmlspecialchars($notif['link']) ?>" class="text-decoration-none d-block mb-2">
                                            <div class="action-item" style="border-left-color: <?= htmlspecialchars($notif['border_color'] ?? 'var(--bs-primary)') ?>;">
                                                <div class="d-flex align-items-center gap-3 w-100">
                                                    <i class="<?= htmlspecialchars($notif['icon'] ?? 'bi-bell-fill') ?> fs-5 <?= htmlspecialchars($notif['text_color'] ?? 'text-primary') ?>"></i>
                                                    <span class="text-sm-custom text-dark fw-medium flex-grow-1"><?= htmlspecialchars($notif['mensaje']) ?></span>
                                                    <?php if (!empty($notif['badge'])): ?>
                                                        <span class="badge <?= htmlspecialchars($notif['badge']['class']) ?> ms-2"><?= htmlspecialchars($notif['badge']['text']) ?></span>
                                                    <?php else: ?>
                                                        <i class="bi bi-arrow-right text-muted ms-2"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-2">
                                    <button class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotificaciones" aria-expanded="false" aria-controls="collapseNotificaciones" onclick="this.innerHTML = this.getAttribute('aria-expanded') === 'true' ? 'Ver más (<?= $total_notifs - $visible_count ?>)' : 'Ocultar';">
                                        Ver más (<?= $total_notifs - $visible_count ?>)
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="p-3 text-center text-muted border rounded bg-white shadow-sm">
                                <p class="mb-0 small">No hay notificaciones pendientes.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">

                    <div class="col">
                        <a href="index.php?route=micomunidad/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-green">
                                        <i class="bi bi-people-fill text-success fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Mi Comunidad</h5>
                                    <p class="card-text text-muted small mb-0">Gestión de usuarios</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=comunicaciones/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-purple">
                                        <i class="bi bi-megaphone-fill fs-2 txt-icon-purple"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Comunicaciones</h5>
                                    <p class="card-text text-muted small mb-0">Publicar y editar avisos</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=foro/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-dark">
                                        <i class="fa-solid fa-comments text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Foro Vecinal</h5>
                                    <p class="card-text text-muted small mb-0">Moderación y debate</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=reserva/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card position-relative">
                                <?php if (isset($tieneReservaHoy) && $tieneReservaHoy): ?>
                                    <span id="burbuja-reservas-hoy" class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle badge-reserva-hoy">
                                        <span class="visually-hidden">Reserva para hoy</span>
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-teal">
                                        <i class="bi bi-calendar-check-fill fs-2 txt-icon-teal"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Reservas</h5>
                                    <p class="card-text text-muted small mb-0">Administrar espacios</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col">
                        <a href="index.php?route=votacion/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card position-relative">
                                <?php if ($votacionesPendientes > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-xs">
                                        <?= $votacionesPendientes ?>
                                        <span class="visually-hidden">votaciones pendientes</span>
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-green">
                                        <i class="fa-solid fa-check-to-slot text-success fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Votaciones</h5>
                                    <p class="card-text text-muted small mb-0">Gestión de votos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col">
                        <a href="index.php?route=reunion/reuniones" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-warning">
                                        <i class="bi bi-easel-fill text-warning fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Reuniones</h5>
                                    <p class="card-text text-muted small mb-0">Convocar juntas</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=incidencias/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-red">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Incidencias</h5>
                                    <p class="card-text text-muted small mb-0">Gestión de averías</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=matricula/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-dark">
                                        <i class="fa-solid fa-car text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Parking</h5>
                                    <p class="card-text text-muted small mb-0">Registro matrículas</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col">
                        <a href="index.php?route=finanzas/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-wrap-64 bg-icon-dark">
                                        <i class="fa-solid fa-wallet text-primary fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1 font-titulos">Finanzas</h5>
                                    <p class="card-text text-muted small mb-0">Contabilidad y cuotas</p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<script src="public/assets/js/dashboard_vecino.js"></script>