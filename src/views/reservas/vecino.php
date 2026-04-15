<?php $titulo_pagina = "Reservas";?>

<?php include 'src/views/components/topbarv.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="container py-4 py-md-5">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">Reservas de Espacios</h1>
                
                <button type="button" class="btn btn-brand fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalReserva">
                    <i class="fa-solid fa-plus me-2"></i> Nueva Reserva
                </button>
            </div>

            <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm mb-4" style="background-color: var(--bs-light);">
                <h5 class="fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);">
                    <i class="fa-solid fa-circle-info me-2 text-warning"></i>Normas y Recomendaciones
                </h5>
                <ul class="text-muted small mb-0 ps-3">
                    <li>Respeta los horarios establecidos.</li>
                    <li>Deja el espacio limpio y recogido.</li>
                    <li>Cancela tu reserva si no vas a asistir (recuerda: máx. 1 al día y 3 a la semana).</li>
                    <li>Respeta el aforo máximo permitido en cada instalación.</li>
                </ul>
            </div>

            <ul class="nav nav-tabs mb-4" id="reservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="mis-reservas-tab" data-bs-toggle="tab" data-bs-target="#mis-reservas" type="button" role="tab" style="color: var(--bs-dark);">Mis Reservas</button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link fw-semibold text-muted" id="espacios-tab" data-bs-toggle="tab" data-bs-target="#espacios" type="button" role="tab">Espacios Disponibles</button>
                </li>
            </ul>

            <div class="tab-content" id="reservasTabContent">
                
                <div class="tab-pane fade show active" id="mis-reservas" role="tabpanel">
                    <?php if (empty($misReservas)): ?>
                        <div class="text-center py-5">
                            <i class="fa-regular fa-calendar-xmark fs-1 text-muted mb-3"></i>
                            <h5 class="fw-bold text-muted">No tienes reservas activas</h5>
                            <p class="text-muted small">Haz clic en "Nueva Reserva" para empezar.</p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($misReservas as $reserva): ?>
                                <div class="col">
                                    <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-success">
                                        <div class="card-body p-4 d-flex flex-column">
                                            
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="text-muted small">
                                                    <div class="mb-1"><i class="fa-regular fa-calendar me-2 text-success"></i><?= htmlspecialchars($reserva['fecha']) ?></div>
                                                    <div>
                                                        <i class="fa-regular fa-clock me-2 text-success"></i>
                                                        <?= htmlspecialchars($reserva['hora_inicio']) ?> - <?= htmlspecialchars($reserva['hora_fin']) ?>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white fw-bold">Activa</span>
                                            </div>
                                            
                                            <h3 class="fs-5 fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);">
                                                <?= htmlspecialchars($reserva['nombre_espacio']) ?>
                                            </h3>
                                            
                                            <div class="mb-3 small text-muted">
                                                <i class="fa-solid fa-users me-2"></i>Asistentes: <?= htmlspecialchars($reserva['asistentes']) ?>
                                            </div>
                                            
                                            <div class="mt-auto d-flex justify-content-end border-top pt-3">
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" onclick="eliminarReserva(<?= $reserva['id_reserva'] ?>)">
                                                    <i class="fa-solid fa-trash me-2"></i>Cancelar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="espacios" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($espacios as $espacio): ?>
                            <div class="col">
                                <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-primary">
                                    <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                                        
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm mb-3" style="width: 64px; height: 64px;">
                                            <i class="fa-solid fa-building fs-4 text-primary"></i>
                                        </div>
                                        
                                        <h3 class="fs-5 fw-bold text-dark mb-3" style="font-family: var(--fuente-titulos);">
                                            <?= htmlspecialchars($espacio['nombre_espacio']) ?>
                                        </h3>
                                        
                                        <div class="text-muted small mb-4 text-start bg-light p-3 rounded w-100">
                                            <div class="mb-2"><i class="fa-solid fa-users me-2 text-primary"></i><strong>Aforo:</strong> <?= htmlspecialchars($espacio['max_personas']) ?> personas</div>
                                            <div class="mb-2"><i class="fa-regular fa-clock me-2 text-primary"></i><strong>Horario:</strong> <?= htmlspecialchars($espacio['hora_apertura']) ?> a <?= htmlspecialchars($espacio['hora_cierre']) ?></div>
                                            <div><i class="fa-solid fa-stopwatch me-2 text-primary"></i><strong>Duración máx:</strong> <?= htmlspecialchars($espacio['duracion_uso']) ?> min</div>
                                        </div>
                                        
                                        <div class="mt-auto border-top w-100 pt-3">
                                            <button type="button" class="btn btn-outline-primary btn-sm fw-semibold w-100" data-bs-toggle="modal" data-bs-target="#modalReserva">
                                                <i class="fa-solid fa-calendar-check me-2"></i>Reservar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </main>
        
        <?php include 'src/views/components/reservas/modalCrear.php'; ?>
    </div>
</div>
