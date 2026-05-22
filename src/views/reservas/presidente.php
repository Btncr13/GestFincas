<?php $titulo_pagina = "Gestión Espacios/Reservas"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Gestión Espacios / Reservas</h2>
                    <p class="mb-0" style="color: var(--color-texto); font-size: 14px; margin-top: 0.25rem;">Gestión de reservas y espacios de la comunidad</p> 
                </div>
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
                        <option value="activas">Reservas Activas</option>
                        <option value="inactivas">Reservas Inactivas</option>
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
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reservasActivas as $res): ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-success) !important;" id="reserva-<?php echo $res['id_reserva']; ?>">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span style="font-size:15px; font-weight:700; color:var(--bs-dark); font-family: var(--fuente-titulos);"><?php echo htmlspecialchars($res['nombre_espacio']); ?></span>
                                                    <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Activa</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-house text-success"></i> <span class="fw-bold"><?php echo htmlspecialchars($res['nombre_vivienda'] ?? ''); ?></span></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> <?php echo date('d/m/Y', strtotime($res['fecha'])); ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> <span class="badge bg-light text-dark border"><?php echo substr($res['hora_inicio'], 0, 5); ?> - <?php echo substr($res['hora_fin'], 0, 5); ?></span></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: <?php echo $res['asistentes']; ?></span>
                                                </div>

                                                <div class="mt-3">
                                                    <details style="font-size:13px; color:var(--color-texto);">
                                                        <summary class="fw-semibold cursor-pointer text-success">
                                                            <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                                                        </summary>
                                                        <ul class="list-unstyled ps-3 pt-2 mb-0">
                                                            <?php foreach ($res['normas'] as $norma): ?>
                                                                <li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i><?php echo htmlspecialchars($norma); ?></li>
                                                            <?php endforeach;
                                                            if (empty($res['normas'])) echo '<li>No hay normas definidas.</li>'; ?>
                                                        </ul>
                                                    </details>
                                                </div>
                                            </div>
                                            <div class="ms-auto text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm px-3"
                                                    onclick="if(confirm('¿Seguro que deseas eliminar esta reserva?')) eliminarReservaGen(<?php echo $res['id_reserva']; ?>)">
                                                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reservasInactivas as $res): ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid #d1d5db !important; background-color: var(--bs-light); opacity: 0.75;" id="reserva-<?php echo $res['id_reserva']; ?>">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span style="font-size:15px; font-weight:700; color:var(--bs-gray-600); font-family: var(--fuente-titulos);"><?php echo htmlspecialchars($res['nombre_espacio']); ?></span>
                                                    <span class="badge bg-secondary px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Inactiva</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-house text-secondary"></i> <span class="fw-bold"><?php echo htmlspecialchars($res['nombre_vivienda'] ?? ''); ?></span></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-secondary"></i> <?php echo date('d/m/Y', strtotime($res['fecha'])); ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-secondary"></i> <span class="badge bg-light text-muted border"><?php echo substr($res['hora_inicio'], 0, 5); ?> - <?php echo substr($res['hora_fin'], 0, 5); ?></span></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-secondary"></i> Asistentes: <?php echo $res['asistentes']; ?></span>
                                                </div>

                                                <?php if ($res['estado_reserva'] === 'inactivo' && ($res['espacio_bloqueado'] ?? 0) == 1 && !empty($res['motivo_espacio'])): ?>
                                                    <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm mt-3 mb-0 py-2 px-3" style="font-size:12px;">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                        <strong>Cancelada por bloqueo:</strong> <?php echo htmlspecialchars($res['motivo_espacio']); ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mt-3">
                                                    <details style="font-size:13px; color:var(--color-texto);">
                                                        <summary class="fw-semibold cursor-pointer text-secondary">
                                                            <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                                                        </summary>
                                                        <ul class="list-unstyled ps-3 pt-2 mb-0">
                                                            <?php foreach ($res['normas'] as $norma): ?>
                                                                <li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-secondary"></i><?php echo htmlspecialchars($norma); ?></li>
                                                            <?php endforeach;
                                                            if (empty($res['normas'])) echo '<li>No hay normas definidas.</li>'; ?>
                                                        </ul>
                                                    </details>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                            $textClase = $isBloqueado ? 'text-danger' : 'text-primary';
                            ?>
                            <div class="card shadow-sm border module-card" style="border-left: 4px solid var(--color-borde) !important; border-color: var(--color-borde) !important;" id="espacio-<?php echo $espacio['id_espacios_comunidad']; ?>">
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
                                                    <span class="badge bg-success" style="font-size: 10px;">Operativo</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users <?php echo $textClase; ?>"></i> Aforo Total: <?php echo $espacio['aforo']; ?></span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-user-group <?php echo $textClase; ?>"></i> Máx. Personas/Reserva: <?php echo $espacio['max_personas']; ?></span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-stopwatch <?php echo $textClase; ?>"></i> Duración: <?php echo $espacio['duracion_uso']; ?> min</span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock <?php echo $textClase; ?>"></i> <?php echo substr($espacio['hora_apertura'], 0, 5); ?> a <?php echo substr($espacio['hora_cierre'], 0, 5); ?></span>
                                            </div>

                                            <div class="mt-3">
                                                <details style="font-size:13px; color:var(--color-texto);">
                                                    <summary class="fw-semibold cursor-pointer <?php echo $textClase; ?>">
                                                        <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                                                    </summary>
                                                    <ul class="list-unstyled ps-3 pt-2 mb-0">
                                                        <?php if (!empty($espacio['normas'])): ?>
                                                            <?php foreach ($espacio['normas'] as $norma): ?>
                                                                <li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i><?php echo htmlspecialchars($norma); ?></li>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <li>No hay normas definidas para este espacio.</li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </details>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 ms-md-auto">
                                        <button class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm" onclick='abrirModalEditar(<?php echo htmlspecialchars(json_encode($espacio), ENT_QUOTES, "UTF-8"); ?>)'>
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
<?php include 'src/views/components/reservas/modalEditarEspacio.php'; ?>
<?php include 'src/views/components/reservas/modalBloqueoEspacio.php'; ?>


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
<!-- Lógica de gestión de espacios y reservas -->
<script src="public/assets/js/reservas/modalEspacio.js"></script>
<script src="public/assets/js/reservas/modalReserva.js"></script>

<!-- Lógica de Pestañas tipo Switch -->
<script src="public/assets/js/reservas/panelpresi.js"></script>