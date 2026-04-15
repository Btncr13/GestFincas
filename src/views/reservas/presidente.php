<?php $titulo_pagina = "Gestión de Espacios y Reservas"; ?>

<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="container py-4 py-md-5">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">Gestión de Instalaciones</h1>
                
                <button type="button" class="btn btn-brand fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearEspacio">
                    <i class="fa-solid fa-plus me-2"></i> Nuevo Espacio
                </button>
            </div>

            <ul class="nav nav-tabs mb-4" id="adminReservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-dark" id="auditoria-tab" data-bs-toggle="tab" data-bs-target="#auditoria" type="button" role="tab">
                        <i class="fa-solid fa-clipboard-list me-2"></i>Auditoría (Semana actual)
                    </button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link fw-semibold text-muted" id="espacios-tab" data-bs-toggle="tab" data-bs-target="#espacios" type="button" role="tab">
                        <i class="fa-solid fa-building me-2"></i>Catálogo de Espacios
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="adminReservasTabContent">
                
                <div class="tab-pane fade show active" id="auditoria" role="tabpanel">
                    <div class="card border-0 shadow-sm module-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Vecino</th>
                                            <th>Espacio</th>
                                            <th>Fecha</th>
                                            <th>Horario</th>
                                            <th>Estado</th>
                                            <th class="text-end pe-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($todasLasReservas)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No hay reservas registradas para esta semana.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($todasLasReservas as $reserva): ?>
                                            <tr>
                                                <td class="ps-4 fw-semibold">
                                                    <?= htmlspecialchars($reserva['vecino_nombre'] . ' ' . $reserva['apellidos']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($reserva['espacio_nombre']) ?></td>
                                                <td><i class="fa-regular fa-calendar text-muted me-2"></i><?= htmlspecialchars($reserva['fecha']) ?></td>
                                                <td><i class="fa-regular fa-clock text-muted me-2"></i><?= htmlspecialchars($reserva['hora_inicio']) ?></td>
                                                <td>
                                                    <?php if($reserva['estado_reserva'] === 'Activa'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-2">Activa</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-2">Pasada/Inactiva</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-outline-danger" title="Cancelar reserva" onclick="cancelarReservaAdmin(<?= $reserva['id_reserva'] ?>)">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
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

                <div class="tab-pane fade" id="espacios" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($espacios as $espacio): ?>
                            <div class="col">
                                <?php $isActivo = ($espacio['estado'] ?? 1) == 1; ?>
                                <div class="card shadow-sm module-card h-100 border-0 border-start border-4 <?= $isActivo ? 'border-primary' : 'border-danger opacity-75' ?>">
                                    <div class="card-body p-4 d-flex flex-column">
                                        
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm" style="width: 48px; height: 48px;">
                                                <i class="fa-solid fa-building fs-5 text-secondary"></i>
                                            </div>
                                            <?php if ($isActivo): ?>
                                                <span class="badge bg-primary px-2 py-1 rounded-2 shadow-sm">Operativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger px-2 py-1 rounded-2 shadow-sm">Bloqueado</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h3 class="fs-5 fw-bold text-dark mb-1 mt-2" style="font-family: var(--fuente-titulos);">
                                            <?= htmlspecialchars($espacio['nombre']) ?>
                                        </h3>
                                        <p class="text-muted small mb-4 flex-grow-1"><?= htmlspecialchars($espacio['descripcion']) ?></p>
                                        
                                        <div class="mt-auto border-top pt-3 d-flex justify-content-between">
                                            <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($espacio)) ?>)">
                                                <i class="fa-solid fa-pen"></i> Editar
                                            </button>
                                            
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm <?= $isActivo ? 'btn-outline-warning' : 'btn-outline-success' ?> fw-semibold" onclick="toggleEstadoEspacio(<?= $espacio['id_espacio'] ?>, <?= $isActivo ? 0 : 1 ?>)">
                                                    <i class="fa-solid <?= $isActivo ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" onclick="eliminarEspacio(<?= $espacio['id_espacio'] ?>)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>