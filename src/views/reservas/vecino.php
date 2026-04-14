<?php include 'src/views/components/topbarv.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <!-- TÍTULO + BOTÓN -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <h3 class="fw-bold text-dark m-0">
                    Reservas de Espacios
                </h3>

                <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm w-100 w-sm-auto"
                        data-bs-toggle="modal" data-bs-target="#modalReserva">
                    <i class="bi bi-plus-circle me-2"></i> Haz una reserva
                </button>
            </div>

            <!-- CARD PRINCIPAL -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3 p-md-4">

                    <!-- ESPACIOS DISPONIBLES -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-grid-3x3-gap-fill me-2"></i>Espacios disponibles
                        </h5>

                        <div class="row g-3">
                            <?php foreach ($espacios as $espacio): ?>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="card h-100 border-0 shadow-sm module-card">
                                        <div class="card-body text-center p-4">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 icon-bg">
                                                <i class="bi bi-building fs-2 text-primary"></i>
                                            </div>
                                            <h6 class="fw-bold"><?= htmlspecialchars($espacio['nombre']) ?></h6>
                                            <p class="text-muted small mb-0"><?= htmlspecialchars($espacio['descripcion']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- MIS RESERVAS ACTIVAS -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-success mb-3">
                            <i class="bi bi-calendar-check-fill me-2"></i>Mis reservas activas
                        </h5>

                        <?php if (empty($misReservas)): ?>
                            <p class="text-muted">No tienes reservas activas actualmente.</p>
                        <?php else: ?>
                            <?php foreach ($misReservas as $reserva): ?>
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($reserva['espacio']) ?></h6>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= htmlspecialchars($reserva['fecha']) ?> - <?= htmlspecialchars($reserva['hora']) ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-success align-self-start align-self-md-center">Activa</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <!-- RECOMENDACIONES -->
                    <div>
                        <h5 class="fw-bold text-warning mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>Recomendaciones de uso
                        </h5>

                        <ul class="text-muted small mb-0">
                            <li>Respeta los horarios establecidos.</li>
                            <li>Deja el espacio limpio y recogido.</li>
                            <li>Cancela tu reserva si no vas a asistir.</li>
                            <li>Evita ruidos excesivos en zonas comunes.</li>
                        </ul>
                    </div>

                </div>
            </div>

        </main>

    </div>
</div>