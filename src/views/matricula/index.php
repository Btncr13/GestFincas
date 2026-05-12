<?php

/**
 * @var int $cantHabitual
 * @var int $cantInvitado
 * @var array $matriculas
 */
include 'src/views/components/topbar.php'; ?>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Gestión de Parking</h2>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalMatricula">
                    <i class="bi bi-plus-lg me-2"></i>Nueva Matrícula
                </button>
            </div>

            <?php if (isset($_SESSION['parking_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['parking_success'];
                    unset($_SESSION['parking_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['parking_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['parking_error'];
                    unset($_SESSION['parking_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- HABITUALES -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-primary">Uso Habitual (<?= $cantHabitual ?>/4)</h5>
                            <span class="badge bg-primary rounded-pill">Máx. 4</span>
                        </div>
                        <div class="card-body">
                            <?php if ($cantHabitual === 0): ?>
                                <p class="text-muted small">No hay matrículas habituales registradas.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($matriculas as $m): if ($m['uso_matricula'] === 'habitual'): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-md-4">
                                                    <div class="mb-1 mb-md-0">
                                                        <span class="text-muted small fw-bold text-uppercase">Matrícula:</span>
                                                        <span class="fw-bold fs-5 ms-1"><?= htmlspecialchars($m['matricula']) ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-muted small fw-bold text-uppercase">Modelo:</span>
                                                        <span class="text-dark ms-1"><?= htmlspecialchars($m['marca_vehículo']) ?></span>
                                                    </div>
                                                </div>
                                                <form action="index.php?route=matricula/delete" method="POST" onsubmit="return confirm('¿Eliminar esta matrícula?')">
                                                    <input type="hidden" name="id_matricula" value="<?= $m['id_matricula'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- INVITADOS -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-success">Invitados (<?= $cantInvitado ?>/2)</h5>
                            <span class="badge bg-success rounded-pill">Máx. 2</span>
                        </div>
                        <div class="card-body">
                            <?php if ($cantInvitado === 0): ?>
                                <p class="text-muted small">No hay matrículas de invitados registradas.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($matriculas as $m): if ($m['uso_matricula'] === 'invitado'): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-md-4">
                                                    <div class="mb-1 mb-md-0">
                                                        <span class="text-muted small fw-bold text-uppercase">Matrícula:</span>
                                                        <span class="fw-bold fs-5 ms-1"><?= htmlspecialchars($m['matricula']) ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-muted small fw-bold text-uppercase">Modelo:</span>
                                                        <span class="text-dark ms-1"><?= htmlspecialchars($m['marca_vehículo']) ?></span>
                                                        <span class="badge bg-light text-dark border ms-md-3 mt-1 mt-md-0">
                                                            Invitado: <?= htmlspecialchars($m['nombre_invitado'] ?? 'Invitado') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <form action="index.php?route=matricula/delete" method="POST" onsubmit="return confirm('¿Eliminar esta matrícula?')">
                                                    <input type="hidden" name="id_matricula" value="<?= $m['id_matricula'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../components/matricula/modalMatricula.php'; ?>
<script src="public/assets/js/matricula/modalMatricula.js"></script>