<?php

/**
 * @var int $cantHabitual
 * @var int $cantInvitado
 * @var array $matriculas
 * @var array $todasMatriculas
 * @var string $rol
 */
include 'src/views/components/topbar.php'; ?>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5">

            <!-- CABECERA DE SECCIÓN: Estilo limpio y alineado con la marca -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">

                    <div>
                        <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Parking</h2>
                        <p class="mb-0" style="color: var(--color-texto); font-size: 14px; margin-top: 0.25rem;">Control de vehículos de la comunidad</p>
                    </div>
                </div>
                <?php if ($rol !== 'presidente'): ?>
                    <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMatricula">
                        <i class="fa-solid fa-plus me-2"></i> Registrar Vehículo
                    </button>
                <?php endif; ?>
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


            <?php if ($rol === 'presidente'): ?>
                <!-- VISTA PRESIDENTE: Listado general de la comunidad -->
                <div class="row g-4">
                    <div class="col-12 mb-4">
                        <div class="bg-transparent border-0">
                            <div class="py-2 text-center d-flex flex-column align-items-center">
                                <div class="position-relative d-inline-block mb-2 w-100" style="max-width: 500px;">
                                    <i class="fa-solid fa-car" style="font-size: clamp(12rem, 80vw, 20rem); line-height: 1; color: #666; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));"></i>
                                    <div class="position-absolute" style="bottom: 21%; left: 50%; transform: translateX(-50%); width: 54%;">
                                        <div class="position-relative">
                                            <input type="text" id="busquedaMatricula" class="form-control text-center fw-bold shadow-sm"
                                                placeholder="Buscar..."
                                                style="font-family: var(--fuente-base); background-color: #fff; border: 1px solid #646464; border-radius: 4px; font-size: clamp(0.7rem, 4vw, 1.1rem); height: auto; padding: 4px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3) !important;">
                                            <button type="button" id="btnLimpiarBusqueda" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y border-0 d-none" style="padding: 0 10px; z-index: 5;">
                                                <i class="bi bi-x-lg text-dark fw-bold"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="instruccionesBusqueda">
                                    <h4 class="mt-4 fw-bold text-primary" style="font-family: var(--fuente-titulos);">Consultar Matrícula</h4>
                                    <p class="text-muted mb-0">Introduzca matrícula o nombre de vivienda</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="cardResultadosComunidad">
                        <h5 class="mb-3 fw-bold text-primary" style="font-family: var(--fuente-titulos);">Vehículos Registrados en la Comunidad</h5>
                        <div id="contenedorMatriculasComunidad" style="max-height: 550px; overflow-y: auto; overflow-x: hidden; padding: 5px;">
                            <div id="listaMatriculasComunidad">
                                <?php if (empty($todasMatriculas)): ?>
                                    <div class="p-4 text-center text-muted bg-white rounded shadow-sm border-start border-4" style="border-color: #6c757d !important;">
                                        <i class="bi bi-info-circle me-1"></i>No hay vehículos registrados en la comunidad.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($todasMatriculas as $m): ?>
                                        <div class="card shadow-sm border-0 mb-3 item-matricula-comunidad" style="border-left: 6px solid #495057 !important;">
                                            <div class="card-body py-3 px-4">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-md-4">
                                                        <div class="mb-1 mb-md-0">
                                                            <span class="text-muted small fw-bold text-uppercase d-md-none">Vivienda:</span>
                                                            <span class="fw-bold text-primary vivienda-info"><?= htmlspecialchars($m['vivienda']) ?></span>
                                                        </div>
                                                        <div class="mb-1 mb-md-0">
                                                            <span class="text-muted small fw-bold text-uppercase d-md-none">Matrícula:</span>
                                                            <span class="badge bg-light text-dark border px-2 py-1 matricula-info"><?= htmlspecialchars($m['matricula']) ?></span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted small fw-bold text-uppercase d-md-none">Vehículo:</span>
                                                            <span class="text-muted small me-2"><?= htmlspecialchars($m['marca_vehículo']) ?></span>
                                                            <span class="badge rounded-pill bg-<?= $m['uso_matricula'] === 'habitual' ? 'primary' : 'success' ?>" style="font-size: 0.7rem;">
                                                                <?= ucfirst($m['uso_matricula']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if ($m['uso_matricula'] === 'invitado'): ?>
                                                            <small class="text-muted">
                                                                <i class="bi bi-calendar3 me-1"></i>Fecha Entrada: <?= date('d/m/Y', strtotime($m['fecha_entrada'])) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>

                <!-- VISTA VECINO: Gestión propia -->
                <div class="row g-4">
                    <!-- HABITUALES -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0 module-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: rgba(34, 28, 53, 0.1);">
                                        <i class="fa-solid fa-house-user text-primary small"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold text-primary" style="font-family: var(--fuente-titulos);">Uso Habitual (<?= $cantHabitual ?>/4)</h5>
                                </div>
                                <span class="badge bg-primary rounded-pill">Máx. 4</span>
                            </div>
                            <div class="card-body">
                                <?php if ($cantHabitual === 0): ?>
                                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>No hay matrículas habituales registradas.</p>
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
                        <div class="card shadow-sm border-0 module-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: rgba(92, 178, 68, 0.1);">
                                        <i class="fa-solid fa-user-clock text-success small"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold text-success" style="font-family: var(--fuente-titulos);">Invitados (<?= $cantInvitado ?>/2)</h5>
                                </div>
                                <span class="badge bg-success rounded-pill">Máx. 2</span>
                            </div>
                            <div class="card-body">
                                <?php if ($cantInvitado === 0): ?>
                                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>No hay matrículas de invitados registradas.</p>
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
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../components/matricula/modalMatricula.php'; ?>
<script src="public/assets/js/matricula/modalMatricula.js"></script>
<script src="public/assets/js/matricula/gestionMatricula.js"></script>