<?php $titulo_pagina = "Gestión EspaciosReservas"; ?>

<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="container py-4 py-md-5">

            <!-- Botón crear espacio -->

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">
                    Gestión Espacios / Reservas
                </h1>

                <button type="button" class="btn btn-brand fw-semibold shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalEspacio">
                    <i class="fa-solid fa-plus me-2"></i> Nuevo Espacio
                </button>
            </div>

            <!-- Contenedor de pestañas -->

            <ul class="nav nav-tabs mb-4" id="adminReservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="auditoria-tab" data-bs-toggle="tab" data-bs-target="#auditoria" type="button" role="tab" style="color: var(--bs-dark);">Reservas</button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link fw-semibold text-muted" id="espacios-tab" data-bs-toggle="tab" data-bs-target="#espacios" type="button" role="tab">Espacios</button>
                </li>
            </ul>

            <!-- Pestaña Reservas -->

            <div class="tab-content" id="adminReservasTabContent">

                <div class="tab-pane fade show active" id="auditoria" role="tabpanel">
                    <?php if (empty($todasLasReservas)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i>
                            <h5 class="fw-bold text-muted">No hay reservas esta semana</h5>
                            <p class="text-muted small">No se han registrado reservas recientes en la comunidad.</p>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm module-card border-0">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Vecino</th>
                                                <th>Espacio</th>
                                                <th>Fecha</th>
                                                <th>Horario</th>
                                                <th>Asistentes</th>
                                                <th>Estado</th>
                                                <th class="text-end pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todasLasReservas as $reserva): ?>
                                                <tr>
                                                    <td class="ps-4 fw-semibold">
                                                        <?= htmlspecialchars($reserva['vecino_nombre'] . ' ' . $reserva['apellidos']) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($reserva['nombre_espacio']) ?></td>
                                                    <td><i class="fa-regular fa-calendar text-muted me-2"></i><?= htmlspecialchars($reserva['fecha']) ?></td>
                                                    <td><i class="fa-regular fa-clock text-muted me-2"></i><?= htmlspecialchars($reserva['hora_inicio']) ?> - <?= htmlspecialchars($reserva['hora_fin']) ?></td>
                                                    <td><?= htmlspecialchars($reserva['asistentes']) ?></td>
                                                    <td>
                                                        <?php if (strtolower($reserva['estado_reserva']) === 'activo'): ?>
                                                            <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white fw-bold">Activa</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary px-2 py-1 rounded-2 shadow-sm text-white fw-bold">Inactiva</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <button class="btn btn-sm btn-outline-danger fw-semibold" title="Cancelar reserva" onclick="eliminarReservaAdmin(<?= $reserva['id_reserva'] ?>)">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pestaña Espacios -->

                <div class="tab-pane fade" id="espacios" role="tabpanel">
                    <?php if (empty($espacios)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-building-circle-xmark fs-1 text-muted mb-3"></i>
                            <h5 class="fw-bold text-muted">No hay espacios creados</h5>
                            <p class="text-muted small">Haz clic en "Nuevo Espacio" para añadir instalaciones a la comunidad.</p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($espacios as $espacio): ?>
                                <?php $isActivo = ($espacio['bloqueado'] == 0); ?>

                                <div class="col">
                                    <div class="card shadow-sm module-card h-100 border-0 border-start border-4 <?= $isActivo ? 'border-primary' : 'border-danger opacity-75' ?>">
                                        <div class="card-body p-4 d-flex flex-column text-center">

                                            <div class="d-flex justify-content-between align-items-start mb-3 w-100">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="fa-solid fa-building fs-5 text-secondary"></i>
                                                </div>
                                                <?php if ($isActivo): ?>
                                                    <span class="badge bg-primary px-2 py-1 rounded-2 shadow-sm text-white fw-bold">Operativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger px-2 py-1 rounded-2 shadow-sm text-white fw-bold">Bloqueado</span>
                                                <?php endif; ?>
                                            </div>

                                            <h3 class="fs-5 fw-bold text-dark mb-3" style="font-family: var(--fuente-titulos);">
                                                <?= htmlspecialchars($espacio['nombre_espacio']) ?>
                                            </h3>

                                            <div class="text-muted small mb-4 text-start bg-light p-3 rounded w-100">
                                                <div class="mb-2"><i class="fa-solid fa-users me-2 text-primary"></i><strong>Aforo:</strong> <?= htmlspecialchars($espacio['max_personas']) ?> personas</div>
                                                <div class="mb-2"><i class="fa-regular fa-clock me-2 text-primary"></i><strong>Horario:</strong> <?= htmlspecialchars($espacio['hora_apertura']) ?> a <?= htmlspecialchars($espacio['hora_cierre']) ?></div>
                                                <div><i class="fa-solid fa-stopwatch me-2 text-primary"></i><strong>Duración máx:</strong> <?= htmlspecialchars($espacio['duracion_uso']) ?> min</div>
                                            </div>

                                            <div class="mt-auto border-top w-100 pt-3 d-flex justify-content-between gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold flex-fill" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($espacio)) ?>)">
                                                    <i class="fa-solid fa-pen"></i> Editar
                                                </button>

                                                <button type="button" class="btn btn-sm <?= $isActivo ? 'btn-outline-warning' : 'btn-outline-success' ?> fw-semibold flex-fill" onclick="toggleEstadoEspacio(<?= $espacio['id_espacios_comunidad'] ?>, <?= $isActivo ? 1 : 0 ?>)">
                                                    <i class="fa-solid <?= $isActivo ? 'fa-lock' : 'fa-lock-open' ?>"></i> <?= $isActivo ? 'Bloquear' : 'Activar' ?>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" onclick="eliminarEspacio(<?= $espacio['id_espacios_comunidad'] ?>)" title="Eliminar Espacio">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php include 'src/views/components/reservas/modalEspacio.php'; ?>
    </div>
</div>
<script src="/public/js/reservas/modalEspacio.js"></script>