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

            <?php
            // Procesar y dividir las reservas en activas e inactivas (últimas 2 semanas)
            $reservasActivas = [];
            $reservasInactivas = [];
            $limiteInactivas = strtotime('-14 days');

            if (!empty($todasLasReservas)) {
                foreach ($todasLasReservas as $reserva) {
                    if ($reserva['estado_reserva'] === 'inactivo') {
                        $fechaRes = strtotime($reserva['fecha']);
                        if ($fechaRes >= $limiteInactivas) {
                            $reservasInactivas[] = $reserva;
                        }
                    } else {
                        $reservasActivas[] = $reserva;
                    }
                }
            }
            ?>

            <!-- PESTAÑAS PRINCIPALES: Reservas vs Espacios -->
            <div class="d-flex mb-4 p-1" style="background-color: var(--color-fondo-formularios, #f8f9fa); border-radius: var(--radio-lg, 0.5rem);">
                <button id="btn-sec-reservas" class="btn flex-fill text-center rounded-2 py-2 text-dark" style="font-size: 14px; font-weight: 500; transition: all 0.2s; background-color: var(--bs-light, #fff); box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onclick="switchMainTab('reservas')">Reservas</button>
                <button id="btn-sec-espacios" class="btn flex-fill text-center rounded-2 py-2 text-muted" style="font-size: 14px; font-weight: 500; transition: all 0.2s; background-color: transparent; box-shadow: none;" onclick="switchMainTab('espacios')">Espacios de la Comunidad</button>
            </div>

            <!-- SECCIÓN 1: RESERVAS -->
            <div id="vista-reservas">
                <!-- FILTRO ESTADO RESERVAS (Desplegable a la derecha) -->
                <div class="d-flex justify-content-end mb-3">
                    <select class="form-select w-auto shadow-sm fw-semibold text-dark border-0" style="background-color: var(--color-fondo-formularios, #f8f9fa); border-radius: var(--radio-md, 0.375rem); font-size: 14px; cursor: pointer;" onchange="switchSubTab(this.value)">
                        <option value="activas">Reservas Activas (<?= count($reservasActivas) ?>)</option>
                        <option value="inactivas">Reservas Inactivas (Últ. 2 sem)</option>
                    </select>
                </div>

                <!-- Contenedor Reservas Activas -->
                <div id="lista-activas">
                    <?php if (empty($reservasActivas)): ?>
                        <div class="text-center py-5 w-100">
                            <i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No hay reservas activas</h5>
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
                                    <?php foreach ($reservasActivas as $res): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($res['vecino_nombre'] . ' ' . $res['apellidos']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($res['nombre_espacio']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo substr($res['hora_inicio'], 0, 5); ?> - <?php echo substr($res['hora_fin'], 0, 5); ?></span></td>
                                            <td class="text-center"><?php echo $res['asistentes']; ?></td>
                                            <td><span class="badge bg-success">Activa</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contenedor Reservas Inactivas -->
                <div id="lista-inactivas" class="d-none">
                    <?php if (empty($reservasInactivas)): ?>
                        <div class="text-center py-5 w-100">
                            <i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No hay reservas inactivas recientes</h5>
                            <p class="text-muted small">Solo se muestran las reservas de las últimas 2 semanas.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border shadow-sm rounded bg-light">
                                <thead class="table-secondary">
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
                                    <?php foreach ($reservasInactivas as $res): ?>
                                        <tr class="text-muted">
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($res['vecino_nombre'] . ' ' . $res['apellidos']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($res['nombre_espacio']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                                            <td><span class="badge bg-light text-muted border"><?php echo substr($res['hora_inicio'], 0, 5); ?> - <?php echo substr($res['hora_fin'], 0, 5); ?></span></td>
                                            <td class="text-center"><?php echo $res['asistentes']; ?></td>
                                            <td><span class="badge bg-secondary">Inactiva</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECCIÓN 2: ESPACIOS DE LA COMUNIDAD -->
            <div id="vista-espacios" class="d-none">
                    <div class="d-flex flex-column gap-3" id="contenedor-cards-espacios">
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
                                $textClase = $isBloqueado ? 'text-danger' : 'text-primary';
                                ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid <?php echo $colorClase; ?> !important;" id="espacio-<?php echo $espacio['id_espacios_comunidad']; ?>">
                                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                                                <i class="fa-solid fa-building fs-5 <?php echo $textClase; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <h3 class="fs-6 fw-bold text-dark mb-0" style="font-family: var(--fuente-titulos);">
                                                        <?php echo htmlspecialchars($espacio['nombre_espacio']); ?>
                                                    </h3>
                                                    <?php if ($isBloqueado): ?>
                                                        <span class="badge bg-danger" style="font-size: 10px;">Bloqueado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary" style="font-size: 10px;">Operativo</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users <?php echo $textClase; ?>"></i> Aforo: <?php echo $espacio['aforo']; ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-stopwatch <?php echo $textClase; ?>"></i> Máx: <?php echo $espacio['duracion_uso']; ?> min</span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock <?php echo $textClase; ?>"></i> <?php echo substr($espacio['hora_apertura'], 0, 5); ?> a <?php echo substr($espacio['hora_cierre'], 0, 5); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-md-auto">
                                            <button class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm" onclick='abrirModalEditar(<?php echo json_encode($espacio); ?>)'>
                                                <i class="fa-solid fa-pen-to-square me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-sm <?php echo $isBloqueado ? 'btn-outline-success' : 'btn-outline-warning'; ?> fw-semibold shadow-sm" onclick="toggleEstadoEspacio(<?php echo $espacio['id_espacios_comunidad']; ?>, <?php echo $isBloqueado ? 0 : 1; ?>)">
                                                <?php echo $isBloqueado ? '<i class="fa-solid fa-check me-1"></i>Activar' : '<i class="fa-solid fa-ban me-1"></i>Bloquear'; ?>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger fw-semibold shadow-sm" onclick="eliminarEspacio(<?php echo $espacio['id_espacios_comunidad']; ?>)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
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
<!-- Lógica de ventana modal -->
<script src="public/assets/js/reservas/modalEspacio.js"></script>

<!-- Lógica de Pestañas tipo Switch -->
<script src="public/assets/js/reservas/panelpresi.js"></script>