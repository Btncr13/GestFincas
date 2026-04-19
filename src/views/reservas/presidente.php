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
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border shadow-sm rounded">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Vecino</th>
                                        <th>Instalación</th>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th class="text-center">Asist.</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todasLasReservas as $res): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($res['vecino_nombre'] . ' ' . $res['apellidos']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($res['nombre_espacio']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo substr($res['hora_inicio'], 0, 5); ?> - <?php echo substr($res['hora_fin'], 0, 5); ?></span></td>
                                            <td class="text-center"><?php echo $res['asistentes']; ?></td>
                                            <td>
                                                <?php if ($res['estado_reserva'] === 'activo'): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
                                <?php
                                $isBloqueado = $espacio['bloqueado'] == 1;
                                $colorClase = $isBloqueado ? 'border-danger' : 'border-primary';
                                ?>
                                <div class="col-md-4 mb-3" id="espacio-<?php echo $espacio['id_espacios_comunidad']; ?>">
                                    <div class="card shadow-sm module-card h-100 border-0 border-start border-4 <?php echo $colorClase; ?>">
                                        <div class="card-body p-4 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="text-muted small">
                                                    <div><?php echo substr($espacio['hora_apertura'], 0, 5); ?> - <?php echo substr($espacio['hora_cierre'], 0, 5); ?></div>
                                                </div>
                                                <?php if ($isBloqueado): ?>
                                                    <span class="badge bg-danger">Bloqueado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Operativo</span>
                                                <?php endif; ?>
                                            </div>

                                            <h3 class="fs-5 fw-bold" style="font-family: var(--fuente-titulos);"><?php echo htmlspecialchars($espacio['nombre_espacio']); ?></h3>

                                            <div class="small text-muted mb-3">
                                                Aforo: <?php echo $espacio['aforo']; ?> · Máx: <?php echo $espacio['duracion_uso']; ?> min
                                            </div>

                                            <div class="mt-auto d-flex gap-2">
                                                <button class="btn btn-outline-secondary btn-sm flex-fill"
                                                    onclick='abrirModalEditar(<?php echo json_encode($espacio); ?>)'>
                                                    Editar
                                                </button>

                                                <button class="btn btn-sm <?php echo $isBloqueado ? 'btn-outline-success' : 'btn-outline-warning'; ?> flex-fill"
                                                    onclick="toggleEstadoEspacio(<?php echo $espacio['id_espacios_comunidad']; ?>, <?php echo $isBloqueado ? 0 : 1; ?>)">
                                                    <?php echo $isBloqueado ? 'Activar' : 'Bloquear'; ?>
                                                </button>

                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="eliminarEspacio(<?php echo $espacio['id_espacios_comunidad']; ?>)">
                                                    🗑
                                                </button>
                                            </div>
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

<!-- Modal Editar Espacio -->
<div class="modal fade" id="modalEditarEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Instalación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarEspacio">
                <input type="hidden" name="id_espacios_comunidad" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" id="edit_nombre" name="nombre_espacio" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Aforo Total</label>
                            <input type="number" id="edit_aforo" name="aforo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max. Personas / Reserva</label>
                            <input type="number" id="edit_max" name="max_personas" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apertura</label>
                            <input type="time" id="edit_apertura" name="hora_apertura" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cierre</label>
                            <input type="time" id="edit_cierre" name="hora_cierre" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Duración (min)</label>
                            <input type="number" id="edit_duracion" name="duracion_uso" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bloqueo -->
<div class="modal fade" id="modalBloqueo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Bloquear Espacio</h5>
            </div>
            <div class="modal-body">
                <p>Indica el motivo por el cual los vecinos no podrán reservar esta instalación:</p>
                <input type="text" id="motivoBloqueo" class="form-control" placeholder="Ej: Limpieza de filtros">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarBloqueo" class="btn btn-warning fw-bold">Confirmar Bloqueo</button>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor Global para Toasts (Notificaciones) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                <!-- Mensaje dinámico -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="public/assets/js/reservas/modalEspacio.js"></script>