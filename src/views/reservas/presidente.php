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
                <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#auditoria" type="button">Reservas</button></li>
                <li class="nav-item ms-2"><button class="nav-link fw-semibold text-muted" data-bs-toggle="tab" data-bs-target="#espacios" type="button">Espacios</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="auditoria" role="tabpanel">
                    <?php if (empty($todasLasReservas)): ?>
                        <div class="text-center py-5"><h5 class="text-muted">No hay reservas esta semana</h5></div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="espacios" role="tabpanel">
                    <?php if (empty($espacios)): ?>
                        <div class="text-center py-5"><h5 class="text-muted">No hay espacios creados</h5></div>
                    <?php else: ?>
                        <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'src/views/components/reservas/modalEspacio.php'; ?>

<script src="public/assets/js/reservas/modalEspacio.js"></script>
