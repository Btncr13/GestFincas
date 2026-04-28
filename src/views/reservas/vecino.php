<?php $titulo_pagina = "Reservas"; ?>

<?php include 'src/views/components/topbarv.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="container py-4 py-md-5">

            <!-- Título + botón -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">
                    Reservas de Espacios
                </h1>

                <button type="button" class="btn btn-brand fw-semibold shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalReserva">
                    <i class="fa-solid fa-plus me-2"></i> Nueva Reserva
                </button>
            </div>

            <!-- Normas -->
            <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm mb-4"
                style="background-color: var(--bs-light);">
                <h5 class="fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);">
                    <i class="fa-solid fa-circle-info me-2 text-warning"></i>Normas y Recomendaciones
                </h5>
                <ul class="text-muted small mb-0 ps-3">
                    <li>Respeta los horarios establecidos.</li>
                    <li>Deja el espacio limpio y recogido.</li>
                    <li>Cancela tu reserva si no vas a asistir (máx. 1 al día y 3 a la semana).</li>
                    <li>Respeta el aforo máximo permitido en cada instalación.</li>
                </ul>
            </div>

            <?php
            // Procesar y dividir las reservas en activas e inactivas (últimas 2 semanas)
            $reservasActivas = [];
            $reservasInactivas = [];
            $limiteInactivas = strtotime('-14 days');

            if (!empty($misReservas)) {
                foreach ($misReservas as $reserva) {
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
                <button id="btn-sec-reservas" class="btn flex-fill text-center rounded-2 py-2 text-dark" style="font-size: 14px; font-weight: 500; transition: all 0.2s; background-color: var(--bs-light, #fff); box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onclick="switchMainTab('reservas')">Mis Reservas</button>
                <button id="btn-sec-espacios" class="btn flex-fill text-center rounded-2 py-2 text-muted" style="font-size: 14px; font-weight: 500; transition: all 0.2s; background-color: transparent; box-shadow: none;" onclick="switchMainTab('espacios')">Espacios Disponibles</button>
            </div>

            <!-- SECCIÓN 1: MIS RESERVAS -->
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
                    <div class="d-flex flex-column gap-3" id="contenedorReservas">
                        <?php if (!empty($reservasActivas)): ?>
                            <?php foreach ($reservasActivas as $reserva): ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-success) !important;" id="reserva-<?= $reserva['id_reserva'] ?>">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span style="font-size:15px; font-weight:700; color:var(--bs-dark); font-family: var(--fuente-titulos);"><?= htmlspecialchars($reserva['nombre_espacio']) ?></span>
                                                    <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Activa</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> <?= htmlspecialchars($reserva['fecha']) ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> <?= htmlspecialchars($reserva['hora_inicio']) ?> - <?= htmlspecialchars($reserva['hora_fin']) ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: <?= htmlspecialchars($reserva['asistentes']) ?></span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm" onclick="eliminarReserva(<?= $reserva['id_reserva'] ?>)">
                                                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 w-100" id="mensajeSinReservas">
                                <i class="fa-regular fa-calendar-xmark fs-1 text-muted mb-3"></i>
                                <h5 class="fw-bold text-muted">No tienes reservas activas</h5>
                                <p class="text-muted small">Haz clic en "Nueva Reserva" para empezar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contenedor Reservas Inactivas (Solo visualización) -->
                <div id="lista-inactivas" class="d-none">
                    <div class="d-flex flex-column gap-3">
                        <?php if (!empty($reservasInactivas)): ?>
                            <?php foreach ($reservasInactivas as $reserva): ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid #d1d5db !important; background-color: var(--bs-light); opacity: 0.75;" id="reserva-<?= $reserva['id_reserva'] ?>">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span style="font-size:15px; font-weight:700; color:var(--bs-gray-600); font-family: var(--fuente-titulos);"><?= htmlspecialchars($reserva['nombre_espacio']) ?></span>
                                                    <span class="badge bg-secondary px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Inactiva</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-secondary"></i> <?= htmlspecialchars($reserva['fecha']) ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-secondary"></i> <?= htmlspecialchars($reserva['hora_inicio']) ?> - <?= htmlspecialchars($reserva['hora_fin']) ?></span>
                                                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-secondary"></i> Asistentes: <?= htmlspecialchars($reserva['asistentes']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 w-100">
                                <i class="fa-regular fa-calendar-xmark fs-1 text-muted mb-3"></i>
                                <h5 class="fw-bold text-muted">No hay reservas inactivas recientes</h5>
                                <p class="text-muted small">Solo se muestran las reservas de las últimas 2 semanas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: ESPACIOS DISPONIBLES -->
            <div id="vista-espacios" class="d-none">
                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($espaciosDisponibles)): ?>
                        <?php foreach ($espaciosDisponibles as $espacio): ?>
                            <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-primary) !important;">
                                <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="fa-solid fa-building fs-5 text-primary"></i>
                                        </div>
                                        <div>
                                            <h3 class="fs-6 fw-bold text-dark mb-1" style="font-family: var(--fuente-titulos);">
                                                <?= htmlspecialchars($espacio['nombre_espacio']) ?>
                                            </h3>
                                            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-primary"></i> Aforo: <?= htmlspecialchars($espacio['aforo']) ?></span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-user-group text-primary"></i> Máx/reserva: <?= htmlspecialchars($espacio['max_personas']) ?></span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-primary"></i> <?= htmlspecialchars($espacio['hora_apertura']) ?> a <?= htmlspecialchars($espacio['hora_cierre']) ?></span>
                                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-stopwatch text-primary"></i> Máx: <?= htmlspecialchars($espacio['duracion_uso']) ?> min</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center ms-md-auto">
                                        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalReserva">
                                            <i class="fa-solid fa-calendar-check me-2"></i>Reservar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 w-100">
                            <i class="fa-solid fa-building-circle-xmark fs-1 text-muted mb-3"></i>
                            <h5 class="fw-bold text-muted">No hay espacios disponibles en este momento</h5>
                            <p class="text-muted small">Contacta con el presidente si crees que esto es un error.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>
</div>

<!-- Modal Crear Reserva -->
<?php include 'src/views/components/reservas/modalCrear.php'; ?>

<!-- Espacios disponibles para JS -->
<script>
    const espaciosDisponibles = <?= json_encode($espaciosDisponibles) ?>;
</script>

<!-- JS del modal -->
<script src="public/assets/js/reservas/modalCrear.js"></script>

<!-- JS de Pestañas tipo Switch -->
<script src="public/assets/js/reservas/panelvecino.js"></script>