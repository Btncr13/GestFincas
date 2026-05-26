<?php $titulo_pagina = "Finanzas"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">

                <!-- TÍTULO Y BOTONES DE ACCIÓN -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
                    <div>
                        <h2 class="mb-1 fw-bold font-title text-dark">Finanzas</h2>
                        <p class="text-muted small mb-2">Control financiero de la comunidad y gestión de cuotas</p>
                        <?php $colorFondo = $saldoTotal >= 0 ? 'success' : 'danger'; ?>
                        <span class="badge bg-<?= $colorFondo ?> bg-opacity-10 text-<?= $colorFondo ?> border border-<?= $colorFondo ?> border-opacity-25 px-3 py-2 shadow-sm" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-vault me-1"></i> Fondo de la Comunidad: <?= number_format($saldoTotal, 2) ?> €
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-danger fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGasto">
                            <i class="fa-solid fa-arrow-trend-down me-1"></i> Registrar Gasto
                        </button>
                        <button class="btn btn-success fw-semibold shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalCuota">
                            <i class="fa-solid fa-arrow-trend-up me-1"></i> Emitir Cuota
                        </button>
                    </div>
                </div>

                <!-- ALERTAS DE ÉXITO Y ERROR -->
                <?php if (isset($_SESSION['finanzas_exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: var(--radio-md);">
                        <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['finanzas_exito'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['finanzas_exito']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['finanzas_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: var(--radio-md);">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <strong>Atención:</strong> <?= $_SESSION['finanzas_error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['finanzas_error']); ?>
                <?php endif; ?>

                <!-- ÚLTIMOS GASTOS REGISTRADOS -->
                <div class="card border-0 border-start border-4 border-primary shadow-sm mb-4 module-card bg-light rounded-3">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="fw-bold mb-0 text-primary font-title">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Últimos Gastos Registrados
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($ultimosGastos as $gasto): ?>
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-opacity-10 bg-secondary border-custom">
                                    <div class="d-flex align-items-start gap-3">
                                        <!-- FIX 1: Cambiado bg-white por background-color: var(--bs-light) -->
                                        <div class="rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0 bg-light" style="width: 40px; height: 40px;">
                                            <i class="fa-regular fa-file-lines text-muted fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($gasto['concepto']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($gasto['categoria']) ?> &bull; <?= date('d/m/Y', strtotime($gasto['fecha'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php $esIngreso = $gasto['importe'] < 0; ?>
                                        <span class="fs-5 fw-bold <?= $esIngreso ? 'text-success' : 'text-danger' ?>"><?= $esIngreso ? '+' : '-' ?> <?= number_format(abs($gasto['importe']), 2) ?> €</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- TABS (Mensual, Anual, Histórico) -->
                <?php $tabActiva = $tabActiva ?? 'mensual'; ?>
                <ul class="nav nav-pills d-flex mb-4 p-1 shadow-sm bg-fondo rounded-4" id="finanzasTab" role="tablist">
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'mensual' ? 'active text-dark shadow-sm bg-light' : 'text-muted bg-transparent' ?> w-100 fw-semibold rounded-2" data-bs-toggle="pill" data-bs-target="#mensual" type="button" style="transition: all 0.2s;">Mensual</button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'anual' ? 'active text-dark shadow-sm bg-light' : 'text-muted bg-transparent' ?> w-100 fw-semibold rounded-2" data-bs-toggle="pill" data-bs-target="#anual" type="button" style="transition: all 0.2s;">Anual</button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'historico' ? 'active text-dark shadow-sm bg-light' : 'text-muted bg-transparent' ?> w-100 fw-semibold rounded-2" data-bs-toggle="pill" data-bs-target="#historico" type="button" style="transition: all 0.2s;">Histórico</button>
                    </li>
                </ul>

                <div class="tab-content" id="finanzasTabContent">
                    <!-- PESTAÑA MENSUAL -->
                    <div class="tab-pane fade <?= $tabActiva === 'mensual' ? 'show active' : '' ?>" id="mensual" role="tabpanel">
                        <div class="row g-4 mb-4">
                            <!-- Gráfica Donut -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100 module-card bg-light rounded-3">
                                    <div class="card-body p-4">
                                        <?php
                                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        $mesAnio = $meses[date('n') - 1] . ' ' . date('Y');
                                        ?>
                                        <h6 class="fw-bold mb-4 text-capitalize font-title text-dark"><?= $mesAnio ?> - Ingresos vs Gastos</h6>
                                        <div style="position: relative; height: 250px; width: 100%;">
                                            <canvas id="chartMensual"></canvas>
                                        </div>
                                        <div class="d-flex justify-content-around mt-4 text-center">
                                            <div>
                                                <small class="text-muted d-block">Ingresos</small>
                                                <span class="fs-5 fw-bold text-success"><?= number_format($resumen['ingresos'], 2) ?> €</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Gastos</small>
                                                <span class="fs-5 fw-bold text-danger"><?= number_format($resumen['gastos'], 2) ?> €</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Balance</small>
                                                <?php $balanceMes = $resumen['ingresos'] - $resumen['gastos']; ?>
                                                <span class="fs-5 fw-bold <?= $balanceMes >= 0 ? 'text-primary' : 'text-danger' ?>"><?= $balanceMes > 0 ? '+' : '' ?><?= number_format($balanceMes, 2) ?> €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ESTADO DE CUOTAS DE VECINOS -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100 module-card bg-light rounded-3">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <!-- FIX: Usamos la clase text-dark nativa que tu CSS ya adapta al modo oscuro -->
                                            <h6 class="fw-bold mb-0 text-dark font-title">Estado de Cuotas</h6>
                                            <button class="btn btn-sm btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDirectorioVecinos"><i class="fa-solid fa-users me-1"></i> Ver todos los vecinos</button>
                                        </div>
                                        <div class="table-responsive flex-grow-1">
                                            <!-- FIX: Añadimos table-borderless para anular los bordes nativos rebeldes de Bootstrap -->
                                            <table class="table table-borderless align-middle mb-0 text-sm-custom">
                                                <tbody>
                                                    <?php if (empty($vecinosConDeuda)): ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center py-4 bg-transparent">
                                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success mb-3" style="width: 50px; height: 50px;">
                                                                        <i class="fa-solid fa-check-double fs-4"></i>
                                                                    </div>
                                                                    <h6 class="fw-bold text-success mb-1">¡Todo al día!</h6>
                                                                    <span class="text-muted small">Todas las cuotas de la comunidad están pagadas.</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($vecinosConDeuda as $cuota): ?>
                                                            <!-- FIX: Usamos nuestra variable dinámica --color-borde para la línea separadora -->
                                                            <tr style="border-bottom: 1px solid var(--color-borde);">

                                                                <!-- FIX: bg-transparent permite que el fondo oscuro de la tarjeta traspase a la celda -->
                                                                <td class="py-3 ps-0 bg-transparent">
                                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($cuota['vivienda']) ?></div>
                                                                    <small class="text-muted"><?= htmlspecialchars($cuota['vecino']) ?></small>
                                                                </td>
                                                                <td class="py-3 text-end pe-0 bg-transparent">
                                                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                                                        <?php if ($cuota['estado'] == 'Pendiente'): ?>
                                                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 border border-warning border-opacity-25">Pendiente (<?= $cuota['deuda'] ?>€)</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25">Moroso (<?= $cuota['deuda'] ?>€)</span>
                                                                        <?php endif; ?>

                                                                        <button class="btn btn-sm btn-outline-primary px-2 py-1 shadow-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalHistorial-<?= $cuota['id_vivienda'] ?>" style="font-size: 0.75rem;">
                                                                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Historial
                                                                        </button>
                                                                    </div>
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
                        </div>
                    </div>

                    <!-- PESTAÑA ANUAL -->
                    <div class="tab-pane fade <?= $tabActiva === 'anual' ? 'show active' : '' ?>" id="anual" role="tabpanel">
                        <div class="card shadow-sm border-0 module-card bg-light rounded-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4 text-center font-title text-dark">Evolución del Ejercicio <?= date('Y') ?></h6>
                                <div class="row g-3 mb-4 text-center">
                                    <div class="col-12 col-md-4">
                                        <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25">
                                            <small class="text-muted d-block mb-1">Total Ingresos Estimados</small>
                                            <span class="fs-4 fw-bold text-success">+ <?= number_format($resumenAnual['ingresos'], 2) ?> €</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                            <small class="text-muted d-block mb-1">Total Gastos Realizados</small>
                                            <span class="fs-4 fw-bold text-danger">- <?= number_format($resumenAnual['gastos'], 2) ?> €</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <?php $colorBalanceTexto = $resumenAnual['balance'] >= 0 ? 'success' : 'danger'; ?>
                                        <div class="p-3 rounded-3 bg-transparent border-custom">
                                            <small class="text-muted d-block mb-1">Balance Actual</small>
                                            <span class="fs-4 fw-bold text-<?= $colorBalanceTexto ?>"><?= number_format($resumenAnual['balance'], 2) ?> €</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- GRÁFICA DE BARRAS -->
                                <div style="position: relative; height: 300px; width: 100%;">
                                    <canvas id="chartAnual"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA HISTÓRICO -->
                    <div class="tab-pane fade <?= $tabActiva === 'historico' ? 'show active' : '' ?>" id="historico" role="tabpanel">

                        <!-- FILTROS HISTÓRICO -->
                        <div class="p-3 mb-4 rounded-3 shadow-sm module-card bg-light border-custom">
                            <form method="GET" action="index.php" class="row g-2 align-items-end m-0">
                                <input type="hidden" name="route" value="finanzas/index">
                                <input type="hidden" name="tab" value="historico">

                                <div class="col-12 col-md-4">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Buscar concepto</label>
                                    <input type="text" name="concepto" class="form-control form-control-sm bg-fondo border-custom" placeholder="Ej: Reparación..." value="<?= htmlspecialchars($filtrosHistorico['concepto']) ?>">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Categoría / Tipo</label>
                                    <select name="categoria" class="form-select form-select-sm bg-fondo border-custom">
                                        <option value="">Todas</option>
                                        <optgroup label="Ingresos">
                                            <option value="mensual" <?= ($filtrosHistorico['categoria'] == 'mensual') ? 'selected' : '' ?>>Cuota Mensual</option>
                                            <option value="derrama" <?= ($filtrosHistorico['categoria'] == 'derrama') ? 'selected' : '' ?>>Derrama</option>
                                        </optgroup>
                                        <optgroup label="Gastos">
                                            <option value="Mantenimiento y Reparaciones" <?= ($filtrosHistorico['categoria'] == 'Mantenimiento y Reparaciones') ? 'selected' : '' ?>>Mantenimiento y Reparaciones</option>
                                            <option value="Suministros" <?= ($filtrosHistorico['categoria'] == 'Suministros') ? 'selected' : '' ?>>Suministros (Luz, Agua, Gas)</option>
                                            <option value="Limpieza" <?= ($filtrosHistorico['categoria'] == 'Limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                            <option value="Seguros e Impuestos" <?= ($filtrosHistorico['categoria'] == 'Seguros e Impuestos') ? 'selected' : '' ?>>Seguros e Impuestos</option>
                                            <option value="Servicios Profesionales" <?= ($filtrosHistorico['categoria'] == 'Servicios Profesionales') ? 'selected' : '' ?>>Servicios Profesionales</option>
                                            <option value="Otros" <?= ($filtrosHistorico['categoria'] == 'Otros') ? 'selected' : '' ?>>Otros</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Año</label>
                                    <select name="anio" class="form-select form-select-sm bg-fondo border-custom">
                                        <option value="">Todos</option>
                                        <?php $anioActualF = date('Y');
                                        for ($i = $anioActualF; $i >= $anioActualF - 5; $i--): ?>
                                            <option value="<?= $i ?>" <?= ($filtrosHistorico['anio'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Mes</label>
                                    <select name="mes" class="form-select form-select-sm bg-fondo border-custom">
                                        <option value="">Todos</option>
                                        <?php foreach (['1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'] as $num => $nombreMes): ?>
                                            <option value="<?= $num ?>" <?= ($filtrosHistorico['mes'] == $num) ? 'selected' : '' ?>><?= $nombreMes ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Desde fecha</label>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm bg-fondo border-custom" value="<?= $filtrosHistorico['fecha_inicio'] ?>">
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small text-muted mb-1 fw-semibold">Hasta fecha</label>
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm bg-fondo border-custom" value="<?= $filtrosHistorico['fecha_fin'] ?>">
                                </div>
                                <div class="col-12 col-md-4 d-flex gap-2 mt-3 mt-md-0">
                                    <button type="submit" class="btn btn-sm btn-primary text-white flex-grow-1 shadow-sm"><i class="fa-solid fa-search me-1"></i> Buscar</button>
                                    <a href="index.php?route=finanzas/index&tab=historico" class="btn btn-sm btn-outline-secondary px-3" title="Limpiar filtros"><i class="fa-solid fa-eraser"></i></a>
                                </div>
                            </form>
                        </div>

                        <div class="card shadow-sm border-0 module-card bg-light rounded-3">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-hover align-middle mb-0 text-sm-custom">
                                        <thead style="background-color: var(--bs-secondary); border-bottom: 2px solid var(--color-borde);">
                                            <tr>
                                                <th class="py-3 ps-4 text-muted fw-semibold">Fecha</th>
                                                <th class="py-3 text-muted fw-semibold">Concepto</th>
                                                <th class="py-3 text-muted fw-semibold">Tipo</th>
                                                <th class="py-3 text-muted fw-semibold">Estado</th>
                                                <th class="py-3 pe-4 text-end text-muted fw-semibold">Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($historico)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        <i class="fa-solid fa-receipt fs-2 mb-2"></i><br>
                                                        No hay movimientos registrados.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($historico as $mov): ?>
                                                    <?php
                                                    $esIngresoReal = $mov['tipo_movimiento'] === 'Ingreso';
                                                    if ($mov['importe'] < 0) {
                                                        $esIngresoReal = !$esIngresoReal;
                                                    }
                                                    $importeAbs = abs($mov['importe']);
                                                    ?>
                                                    <tr style="border-bottom: 1px solid var(--color-borde);">
                                                        <td class="py-3 ps-4 bg-transparent text-muted"><?= date('d/m/Y', strtotime($mov['fecha'])) ?></td>
                                                        <td class="py-3 bg-transparent">
                                                            <span class="fw-bold text-dark"><?= htmlspecialchars($mov['concepto']) ?></span>
                                                            <?php if (!empty($mov['vivienda'])): ?>
                                                                <span class="d-block text-muted small fw-normal mt-1"><i class="fa-regular fa-building me-1"></i><?= htmlspecialchars($mov['vivienda']) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="py-3 bg-transparent">
                                                            <?php if ($esIngresoReal): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="fa-solid fa-arrow-down me-1"></i>Ingreso</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="fa-solid fa-arrow-up me-1"></i>Gasto</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="py-3 bg-transparent text-capitalize text-muted"><?= $mov['estado'] ?></td>
                                                        <td class="py-3 pe-4 bg-transparent text-end fw-bold <?= $esIngresoReal ? 'text-success' : 'text-danger' ?>">
                                                            <?= $esIngresoReal ? '+' : '-' ?> <?= number_format($importeAbs, 2) ?> €
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- PAGINACIÓN -->
                                <?php if ($totalPaginas > 1): ?>
                                    <div class="d-flex justify-content-between align-items-center p-3 border-top" style="border-color: var(--color-borde) !important;">
                                        <span class="small text-muted d-none d-sm-inline">Mostrando página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $totalHistorico ?> registros)</span>
                                        <nav>
                                            <ul class="pagination pagination-sm m-0">
                                                <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                                    <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=historico&page=<?= $paginaActual - 1 ?><?= $queryString ?>">&laquo;</a>
                                                </li>
                                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                                    <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                                                        <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=historico&page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                                    <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=historico&page=<?= $paginaActual + 1 ?><?= $queryString ?>">&raquo;</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<!-- MODAL REGISTRAR GASTO -->
<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-fondo rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold font-title text-dark">Registrar Nuevo Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=finanzas/nuevoGasto" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Concepto</label>
                        <input type="text" name="concepto" class="form-control bg-light border-custom" required placeholder="Ej: Reparación ascensor">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Categoría</label>
                            <select name="categoria" class="form-select bg-light border-custom" required>
                                <option value="Mantenimiento y Reparaciones">Mantenimiento y Reparaciones</option>
                                <option value="Suministros">Suministros (Luz, Agua, Gas)</option>
                                <option value="Limpieza">Limpieza</option>
                                <option value="Seguros e Impuestos">Seguros e Impuestos</option>
                                <option value="Servicios Profesionales">Servicios Profesionales</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Importe (€)</label>
                            <input type="number" name="importe" step="0.01" class="form-control bg-light border-custom" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Fecha del Gasto</label>
                        <input type="date" name="fecha" class="form-control bg-light border-custom" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Guardar Gasto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EMITIR CUOTA -->
<div class="modal fade" id="modalCuota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-fondo rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold font-title text-dark">Emitir Cuota o Derrama</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=finanzas/nuevaCuota" method="POST">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small border-0" role="alert" style="border-radius: var(--radio-md);">
                        <i class="fa-solid fa-circle-info me-1"></i> Esta cuota se emitirá para <strong>todas</strong> las viviendas de la comunidad.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Tipo de ingreso</label>
                            <select name="tipo" class="form-select bg-light border-custom" required>
                                <option value="mensual">Cuota Mensual</option>
                                <option value="derrama">Derrama Extraordinaria</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Importe <b>por vecino</b> (€)</label>
                            <input type="number" name="importe" step="0.01" min="0.01" class="form-control bg-light border-custom" required placeholder="50.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Concepto</label>
                        <input type="text" name="concepto" class="form-control bg-light border-custom" required placeholder="Ej: Cuota Abril 2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Fecha de Emisión</label>
                        <input type="date" name="fecha_emision" class="form-control bg-light border-custom" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success text-white">Emitir a todos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DIRECTORIO DE VECINOS -->
<div class="modal fade" id="modalDirectorioVecinos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-fondo rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold font-title text-dark">Directorio de Vecinos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($cuotasVecinos as $vecino): ?>
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border shadow-sm bg-light">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($vecino['vivienda']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($vecino['vecino']) ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($vecino['deuda'] == 0): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 d-none d-sm-inline-block">Al corriente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 d-none d-sm-inline-block">Deuda: <?= number_format($vecino['deuda'], 2) ?>€</span>
                                <?php endif; ?>
                                <!-- Usamos data-bs-dismiss="modal" para que cierre este antes de abrir el otro y evitar superposiciones raras en móviles -->
                                <button class="btn btn-sm btn-primary shadow-sm text-white" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalHistorial-<?= $vecino['id_vivienda'] ?>">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Historial
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALES DE HISTORIAL DE VECINOS -->
<?php foreach ($cuotasVecinos as $vecino): ?>
    <div class="modal fade" id="modalHistorial-<?= $vecino['id_vivienda'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-fondo rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold font-title text-dark">Historial de Pagos</h5>
                        <small class="text-muted"><?= htmlspecialchars($vecino['vivienda']) ?> - <?= htmlspecialchars($vecino['vecino']) ?></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?route=finanzas/modificarCuota" method="POST">
                    <div class="modal-body">
                        <?php if (empty($vecino['historial'])): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-receipt text-muted fs-1 mb-3"></i>
                                <h6 class="fw-bold text-muted">Sin movimientos</h6>
                                <p class="text-muted small mb-0">No se ha emitido ningún recibo para este vecino.</p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($vecino['historial'] as $recibo): ?>
                                    <div class="p-3 rounded-3 border shadow-sm bg-light">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-md-5">
                                                <label class="form-label small fw-semibold text-muted mb-1">Concepto</label>
                                                <input type="text" name="cuotas[<?= $recibo['id_cuota'] ?>][concepto]" class="form-control form-control-sm bg-secondary border-custom" value="<?= htmlspecialchars($recibo['concepto']) ?>" required>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-semibold text-muted mb-1">Importe (€)</label>
                                                <input type="number" step="0.01" min="0.01" name="cuotas[<?= $recibo['id_cuota'] ?>][importe]" class="form-control form-control-sm bg-secondary border-custom" value="<?= $recibo['importe'] ?>" required>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                                                <select name="cuotas[<?= $recibo['id_cuota'] ?>][estado]" class="form-select form-select-sm bg-secondary border-custom <?= $recibo['estado'] == 'pagada' ? 'text-success fw-bold' : 'text-warning fw-bold' ?>">
                                                    <option value="pagada" <?= $recibo['estado'] == 'pagada' ? 'selected' : '' ?>>Pagada</option>
                                                    <option value="pendiente" <?= $recibo['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-1 d-flex gap-2 mt-3 mt-md-0 justify-content-end">
                                                <button type="submit" name="eliminar_cuota_id" value="<?= $recibo['id_cuota'] ?>" class="btn btn-sm btn-outline-danger flex-grow-1" title="Eliminar recibo" onclick="return confirm('¿Seguro que deseas eliminar este recibo permanentemente?');"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                            <span class="text-capitalize"><?= $recibo['tipo'] ?></span> &bull; Emitido el <?= date('d/m/Y', strtotime($recibo['fecha_emision'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($vecino['historial'])): ?>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success text-white"><i class="fa-solid fa-floppy-disk me-2"></i> Guardar todos los cambios</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // FIX 2: Comportamiento JS para las Pestañas (Evitamos inyectar bg-white)
        const tabButtons = document.querySelectorAll('#finanzasTab button[data-bs-toggle="pill"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                tabButtons.forEach(b => {
                    b.classList.remove('text-dark', 'shadow-sm', 'bg-light');
                    b.classList.add('text-muted', 'bg-transparent');
                });
                e.target.classList.add('text-dark', 'shadow-sm', 'bg-light');
                e.target.classList.remove('text-muted', 'bg-transparent');
            });
        });

        // FIX 3: Inicializar Gráfica Chart.js y crear observador de tema
        const ctx = document.getElementById('chartMensual').getContext('2d');
        const isDarkInitial = document.documentElement.getAttribute('data-theme') === 'dark';

        const colorIngresos = '#5CB244';
        const colorGastos = '#A41E34';

        // Variables para los datos del gráfico
        const totalIngresos = <?= $resumen['ingresos'] ?? 0 ?>;
        const totalGastos = <?= $resumen['gastos'] ?? 0 ?>;

        // Configuración por defecto
        let valoresGrafico = [totalIngresos, totalGastos];
        let coloresGrafico = [colorIngresos, colorGastos];
        let etiquetasGrafico = ['Ingresos', 'Gastos'];
        let mostrarTooltips = true;

        // Estado vacío: Sin ingresos ni gastos
        if (totalIngresos === 0 && totalGastos === 0) {
            valoresGrafico = [1];
            coloresGrafico = [isDarkInitial ? '#313244' : '#ECECF0']; // Gris según el tema
            etiquetasGrafico = ['Sin movimientos'];
            mostrarTooltips = false;
        }

        let chartMensual = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: etiquetasGrafico,
                datasets: [{
                    data: valoresGrafico,
                    backgroundColor: coloresGrafico,
                    borderWidth: 4,
                    borderColor: isDarkInitial ? '#1E1E2E' : '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    tooltip: {
                        enabled: mostrarTooltips
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: isDarkInitial ? '#CDD6F4' : '#221C35',
                            font: {
                                family: 'Lato',
                                size: 13,
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    }
                }
            }
        });

        // NUEVA GRÁFICA ANUAL (BARRAS)
        const ctxAnual = document.getElementById('chartAnual').getContext('2d');
        const ingresosAnuales = <?= json_encode(array_column($evolucionAnual, 'ingresos')) ?>;
        const gastosAnuales = <?= json_encode(array_column($evolucionAnual, 'gastos')) ?>;

        let chartAnual = new Chart(ctxAnual, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                        label: 'Ingresos',
                        data: ingresosAnuales,
                        backgroundColor: colorIngresos,
                        borderRadius: 4
                    },
                    {
                        label: 'Gastos',
                        data: gastosAnuales,
                        backgroundColor: colorGastos,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDarkInitial ? '#CDD6F4' : '#221C35'
                        },
                        grid: {
                            color: isDarkInitial ? 'rgba(205, 214, 244, 0.1)' : 'rgba(34, 28, 53, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: isDarkInitial ? '#CDD6F4' : '#221C35'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: isDarkInitial ? '#CDD6F4' : '#221C35',
                            font: {
                                family: 'Lato',
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        // Observador para detectar clics en el botón de Modo Oscuro en tiempo real (Igual que en Votaciones)
        const themeObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-theme') {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    // Cambiamos el borde interior de la gráfica al color de la tarjeta
                    chartMensual.data.datasets[0].borderColor = isDark ? '#1E1E2E' : '#FFFFFF';
                    // Cambiamos el gris del estado vacío si cambia el tema
                    if (totalIngresos === 0 && totalGastos === 0) {
                        chartMensual.data.datasets[0].backgroundColor = [isDark ? '#313244' : '#ECECF0'];
                    }
                    // Cambiamos el color de las letras de la leyenda
                    chartMensual.options.plugins.legend.labels.color = isDark ? '#CDD6F4' : '#221C35';
                    chartMensual.update();

                    // Actualizamos colores de la gráfica anual
                    chartAnual.options.scales.y.ticks.color = isDark ? '#CDD6F4' : '#221C35';
                    chartAnual.options.scales.x.ticks.color = isDark ? '#CDD6F4' : '#221C35';
                    chartAnual.options.scales.y.grid.color = isDark ? 'rgba(205, 214, 244, 0.1)' : 'rgba(34, 28, 53, 0.05)';
                    chartAnual.options.plugins.legend.labels.color = isDark ? '#CDD6F4' : '#221C35';
                    chartAnual.update();
                }
            });
        });

        themeObserver.observe(document.documentElement, {
            attributes: true
        });
    });
</script>