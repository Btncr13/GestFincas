<?php $titulo_pagina = "Gestión Espacios/Reservas"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark">Gestión Espacios / Reservas</h1>
                <button type="button" class="btn btn-primary fw-semibold shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalCrearEspacio">
                    <i class="fa-solid fa-plus me-2"></i> Nuevo Espacio
                </button>
            </div>

            <ul class="nav nav-tabs mb-4" id="adminReservasTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#auditoria" type="button">Reservas</button>
                </li>
                <li class="nav-item ms-2">
                    <button class="nav-link fw-semibold text-muted" data-bs-toggle="tab" data-bs-target="#espacios" type="button">Espacios</button>
                </li>
            </ul>

            <div class="tab-content" id="adminReservasTabContent">
                <div class="tab-pane fade show active" id="auditoria" role="tabpanel">
                    <?php if (empty($todasLasReservas)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No hay reservas esta semana</h5>
                        </div>
                    <?php else: ?>
                        <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="espacios" role="tabpanel">
                    <div class="row pt-3" id="contenedor-cards-espacios"> 
                        <?php if (empty($espacios)): ?>
                            <div class="text-center py-5" id="mensaje-vacio-espacios">
                                <i class="fa-solid fa-building-circle-xmark fs-1 text-muted mb-3"></i>
                                <h5 class="fw-bold text-muted">No hay espacios creados</h5>
                                <p class="text-muted small">Haz clic en "Nuevo Espacio" para añadir instalaciones a la comunidad.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($espacios as $espacio): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 shadow-sm border-0">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($espacio['nombre_espacio']); ?></h5>
                                            <p class="card-text text-muted small">
                                                Aforo: <?php echo $espacio['aforo']; ?> | Max: <?php echo $espacio['max_personas']; ?>
                                            </p>
                                            <p class="mb-0 small text-primary fw-bold">
                                                <?php echo $espacio['hora_apertura']; ?> - <?php echo $espacio['hora_cierre']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'src/views/components/reservas/modalCrearEspacio.php'; ?>

<script src="public/assets/js/reservas/modalEspacio.js"></script>
