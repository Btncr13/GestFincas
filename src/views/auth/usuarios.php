<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <div class="container-fluid p-0">
                
                <!-- ENCABEZADO Y ACCIONES -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos);">Mi Comunidad</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item small"><a href="index.php?route=auth/panelpresi" class="text-decoration-none">Panel</a></li>
                                <li class="breadcrumb-item small active" aria-current="page">Gestión de Vecinos</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- BOTONES DE ACCIÓN (Estilo unificado) -->
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrearVivienda">
                            <i class="bi bi-plus-lg"></i>
                            <span>Crear Vivienda</span>
                        </button>
                        <button class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold shadow-sm text-white d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square"></i>
                            <span>Modificar</span>
                        </button>
                        <button class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm d-flex align-items-center gap-2" onclick="prepararEliminacion()">
                            <i class="bi bi-trash"></i>
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>

                <!-- TABLA DE VECINOS -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h5 class="mb-0 fs-6 fw-bold" style="font-family: var(--fuente-titulos);">Lista de Vecinos</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3 border-0 small fw-bold text-uppercase text-muted">Vivienda</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">Nombre Completo</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">DNI</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">Email</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">Rol</th>
                                        <th class="px-4 py-3 border-0 text-end small fw-bold text-uppercase text-muted">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($vecinos)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                                                No se han encontrado vecinos registrados en la comunidad.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($vecinos as $vecino): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="badge bg-info text-primary fw-bold px-2 py-1 vivienda-badge"
                                                          style="cursor: pointer; border: 2px solid transparent;"
                                                          onclick="seleccionarVivienda(this)"
                                                          data-id="<?= $vecino['id_vivienda'] ?>"
                                                          data-nombre="<?= htmlspecialchars($vecino['nombre_vivienda']) ?>">
                                                        <?= htmlspecialchars($vecino['nombre_vivienda']) ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 fw-semibold text-dark">
                                                    <?= !empty($vecino['id_usuario']) ? htmlspecialchars($vecino['nombre'] . ' ' . $vecino['apellidos']) : '<i class="text-muted small">Pendiente de registro</i>' ?>
                                                </td>
                                                <td class="py-3 text-muted"><?= htmlspecialchars($vecino['dni'] ?? '-') ?></td>
                                                <td class="py-3 text-muted"><?= htmlspecialchars($vecino['email'] ?? '-') ?></td>
                                                <td class="py-3">
                                                    <?php if (empty($vecino['id_usuario'])): ?>
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 fw-semibold">Sin asignar</span>
                                                    <?php elseif ($vecino['rol'] === 'presidente'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 fw-semibold">Presidente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 fw-semibold">Vecino</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-end">
                                                    <?php if (!empty($vecino['id_usuario'])): ?>
                                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success small px-3">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning small px-3">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>