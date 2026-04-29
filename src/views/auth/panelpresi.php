<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <div class="container-fluid p-0">

                <!-- 1. BANNER PRINCIPAL "ADMINISTRACIÓN" -->
                <div class="card border-0 mb-4 overflow-hidden shadow-sm" style="min-height: 200px;">
                    <img src="public/assets/img/banner.jpeg" alt="Residencial" class="card-img w-100 h-100 object-fit-cover position-absolute" style="filter: brightness(0.6);">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                <i class="bi bi-house-door-fill fs-4 text-white"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: var(--fuente-titulos);">Panel de Presidencia</h2>
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
                        <div class="action-item" style="border-left-color: var(--bs-success);">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-person-plus-fill text-success fs-5"></i>
                                <span class="text-sm-custom">Nuevo vecino registrado: María López (Planta 2-1B)</span>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                        </div>

                        <div class="action-item" style="border-left-color: #20c997;">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-calendar-check-fill fs-5" style="color: #20c997;"></i>
                                <span class="text-sm-custom">Nueva reserva: Pista de Pádel (Planta 1-2A)</span>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                        </div>

                        <div class="action-item" style="border-left-color: var(--bs-warning);">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-megaphone-fill text-warning fs-5"></i>
                                <span class="text-sm-custom">Recordatorio: Junta Ordinaria mañana a las 19:00h</span>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                        </div>
                    </div>
                </div>

                <!-- 3. GRID DE ACCESO RÁPIDO (Estilo unificado) -->
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">

                    <!-- Mi Comunidad (Usuarios) -->
                    <div class="col">
                        <a href="index.php?route=micomunidad/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(92, 178, 68, 0.1);">
                                        <i class="bi bi-people-fill text-success fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Mi Comunidad</h5>
                                    <p class="card-text text-muted small mb-0">Gestión de usuarios</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Comunicaciones -->
                    <div class="col">
                        <a href="index.php?route=comunicaciones/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(111, 66, 193, 0.1);">
                                        <i class="bi bi-megaphone-fill fs-2" style="color: #6f42c1;"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Comunicaciones</h5>
                                    <p class="card-text text-muted small mb-0">Publicar y editar avisos</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Reservas -->
                    <div class="col">
                        <a href="index.php?route=reserva/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(32, 201, 151, 0.1);">
                                        <i class="bi bi-calendar-check-fill fs-2" style="color: #20c997;"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Reservas</h5>
                                    <p class="card-text text-muted small mb-0">Administrar espacios</p>
                                </div>
                            </div>
                        </a>
                    </div>
                      <!-- Votaciones -->
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
                                    <p class="card-text text-muted small mb-0">Gestión de votos</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Reuniones -->
                    <div class="col">
                        <a href="index.php?route=reunion/reuniones" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(219, 145, 47, 0.1);">
                                        <i class="bi bi-easel-fill text-warning fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Reuniones</h5>
                                    <p class="card-text text-muted small mb-0">Convocar juntas</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Incidencias -->
                    <div class="col">
                        <a href="index.php?route=incidencias/index" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center module-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(164, 30, 52, 0.1);">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Incidencias</h5>
                                    <p class="card-text text-muted small mb-0">Gestión de averías</p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>


        </main>
    </div>
</div>