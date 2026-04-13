<?php
$titulo_pagina = "Panel del Vecino";
?>

<?php include 'src/views/components/topbarv.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <div class="container-fluid p-0">

                <!-- 1. BANNER PRINCIPAL "MI COMUNIDAD" -->
                <div class="card border-0 mb-4 overflow-hidden shadow-sm" style="min-height: 200px;">
                    <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Fachada del edificio" class="card-img w-100 h-100 object-fit-cover position-absolute" style="filter: brightness(0.6);">
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

                <!-- 2. TARJETA "ÚLTIMO COMUNICADO" -->
                <div class="card shadow-sm mb-4 border-0" style="border-left: 4px solid var(--bs-primary) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-bell-fill text-primary fs-5"></i>
                                <span class="fw-bold text-primary" style="font-family: var(--fuente-titulos);">Último Comunicado</span>
                            </div>
                            <?php 
                                $badgeClass = 'bg-secondary';
                                if ($ultimoComunicado['prioridad'] === 'importante') $badgeClass = 'bg-warning text-dark';
                                if ($ultimoComunicado['prioridad'] === 'urgente') $badgeClass = 'bg-danger text-white';
                            ?>
                            <span class="badge <?= $badgeClass ?> px-2 py-1">
                                <?= ucfirst(htmlspecialchars($ultimoComunicado['prioridad'])) ?>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);"><?= htmlspecialchars($ultimoComunicado['titulo']) ?></h4>
                        <p class="card-text text-muted mb-3"><?= htmlspecialchars($ultimoComunicado['contenido']) ?></p>
                        <p class="card-text mb-0"><small class="text-muted"><?= htmlspecialchars($ultimoComunicado['fechaPublicacion']) ?></small></p>
                    </div>
                </div>

                <!-- 3. GRID DE ACCESO RÁPIDO -->
                <div class="row row-cols-1 row-cols-sm-3 g-3 mb-4">
                    
                    <!-- Tarjeta 1 - Reportar Avería -->
                    <div class="col">
                        <a href="index.php?route=auth/incidencias" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(164, 30, 52, 0.1);">
                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Reportar Avería</h5>
                                    <p class="card-text text-muted small mb-2">Informa de incidencias</p>
                                    <?php if ($incidenciasPendientes > 0): ?>
                                        <span class="badge bg-danger mt-auto"><?= $incidenciasPendientes ?> pendientes</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 2 - Mis Recibos -->
                    <div class="col">
                        <a href="#" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: <?= ($recibosPendientes > 0) ? 'rgba(164, 30, 52, 0.1)' : 'rgba(92, 178, 68, 0.1)' ?>;">
                                        <i class="bi bi-file-earmark-text-fill <?= ($recibosPendientes > 0) ? 'text-danger' : 'text-success' ?> fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Mis Recibos</h5>
                                    <p class="card-text text-muted small mb-2">Consulta tus pagos</p>
                                    <?php if ($recibosPendientes > 0): ?>
                                        <span class="badge bg-danger mt-auto"><?= $recibosPendientes ?> pendientes</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Tarjeta 3 - Documentación -->
                    <div class="col">
                        <a href="#" class="text-decoration-none h-100 d-block">
                            <div class="card shadow-sm h-100 border-0 text-center">
                                <div class="card-body p-4 d-flex flex-column align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: rgba(219, 145, 47, 0.1);">
                                        <i class="bi bi-folder-fill text-warning fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Documentación</h5>
                                    <p class="card-text text-muted small mb-0">Actas, estatutos y más</p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

                <!-- 4. TARJETA "MIS INCIDENCIAS" -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <h5 class="fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">Mis Incidencias</h5>
                                    <p class="text-muted small mb-0">Estado de tus reportes</p>
                                </div>
                                
                                <?php if (empty($incidencias)): ?>
                                    <div class="text-center py-4 text-muted">No has reportado ninguna incidencia</div>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach (array_slice($incidencias, 0, 3) as $inc): ?>
                                            <?php 
                                                // Asignamos iconos y colores según estado
                                                $icono = 'bi-exclamation-circle-fill text-danger'; 
                                                $badgeClass = 'border-danger text-danger';
                                                if ($inc['estado'] == 'en_curso') { 
                                                    $icono = 'bi-clock-fill text-warning'; 
                                                    $badgeClass = 'border-warning text-warning'; 
                                                }
                                                if ($inc['estado'] == 'resuelta') { 
                                                    $icono = 'bi-check-circle-fill text-success'; 
                                                    $badgeClass = 'border-success text-success'; 
                                                }
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                                    <i class="bi <?= $icono ?> fs-5"></i>
                                                    <div class="text-truncate">
                                                        <h6 class="mb-0 text-dark fw-semibold text-truncate"><?= htmlspecialchars($inc['titulo']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($inc['categoria']) ?></small>
                                                    </div>
                                                </div>
                                                <span class="badge border <?= $badgeClass ?> bg-transparent ms-2">
                                                    <?= ucfirst(str_replace('_', ' ', htmlspecialchars($inc['estado']))) ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    
                                    <?php if (count($incidencias) > 3): ?>
                                        <div class="text-center mt-3">
                                            <a href="index.php?route=auth/incidencias" class="text-decoration-none fw-semibold">
                                                Ver todas <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>