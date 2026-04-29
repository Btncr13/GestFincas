<?php $titulo_pagina = "Mis Finanzas"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">

                <!-- TÍTULO -->
                <div class="mb-4">
                    <h2 class="mb-1 fw-bold" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Mis Finanzas</h2>
                    <p class="text-muted small mb-0">Consulta tus recibos y la transparencia financiera de la comunidad</p>
                </div>

                <!-- TABS -->
                <?php $tabActiva = $tabActiva ?? 'mis-recibos'; ?>
                <ul class="nav nav-pills d-flex mb-4 p-1 shadow-sm" id="finanzasVecinoTab" role="tablist" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'mis-recibos' ? 'active text-dark shadow-sm' : 'text-muted' ?> w-100 fw-semibold rounded-2" data-bs-toggle="pill" data-bs-target="#mis-recibos" type="button" style="transition: all 0.2s; background-color: <?= $tabActiva === 'mis-recibos' ? 'var(--bs-light)' : 'transparent' ?>;">Mis Recibos</button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'transparencia' ? 'active text-dark shadow-sm' : 'text-muted' ?> w-100 fw-semibold rounded-2" data-bs-toggle="pill" data-bs-target="#transparencia" type="button" style="transition: all 0.2s; background-color: <?= $tabActiva === 'transparencia' ? 'var(--bs-light)' : 'transparent' ?>;">Gastos Comunidad</button>
                    </li>
                </ul>

                <div class="tab-content" id="finanzasVecinoTabContent">
                    <!-- PESTAÑA: MIS RECIBOS -->
                    <div class="tab-pane fade <?= $tabActiva === 'mis-recibos' ? 'show active' : '' ?>" id="mis-recibos" role="tabpanel">

                        <!-- TARJETA DE RESUMEN DE ESTADO -->
                        <div class="card border-0 shadow-sm mb-4 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                            <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($deudaTotal == 0): ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0" style="width: 60px; height: 60px;">
                                            <i class="fa-solid fa-check fs-2"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-success">¡Estás al día!</h5>
                                            <p class="text-muted mb-0 small">No tienes ninguna deuda pendiente con la comunidad.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning flex-shrink-0" style="width: 60px; height: 60px;">
                                            <i class="fa-solid fa-triangle-exclamation fs-2"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-warning">Tienes pagos pendientes</h5>
                                            <p class="text-muted mb-0 small">Por favor, revisa tus cuotas y regulariza tu situación.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($deudaTotal > 0): ?>
                                    <div class="text-end">
                                        <p class="text-muted small mb-1">Deuda total acumulada</p>
                                        <h3 class="fw-bold text-danger mb-0"><?= number_format($deudaTotal, 2) ?> €</h3>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- LISTADO DE CUOTAS -->
                        <h5 class="fw-bold mb-3" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Historial de Recibos</h5>

                        <div class="card border-0 shadow-sm module-card mb-4" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                            <div class="card-body p-4">
                                <?php if (empty($cuotas)): ?>
                                    <div class="text-center py-5">
                                        <i class="fa-solid fa-file-invoice text-muted fs-1 mb-3"></i>
                                        <h6 class="fw-bold text-muted">No hay recibos</h6>
                                        <p class="text-muted small mb-0">Aún no se ha emitido ninguna cuota para tu vivienda.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($cuotas as $cuota): ?>
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-3 border rounded-3 bg-opacity-10 gap-3" style="background-color: var(--bs-secondary); border-color: var(--color-borde) !important;">

                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background-color: var(--bs-light);">
                                                        <i class="fa-solid <?= $cuota['tipo'] == 'derrama' ? 'fa-hammer text-warning' : 'fa-calendar-days text-primary' ?> fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($cuota['concepto']) ?></h6>
                                                        <small class="text-muted text-capitalize"><?= $cuota['tipo'] ?> &bull; Emitido el <?= date('d/m/Y', strtotime($cuota['fecha_emision'])) ?></small>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between gap-4">
                                                    <span class="fs-5 fw-bold text-dark"><?= number_format($cuota['importe'], 2) ?> €</span>
                                                    <?php if ($cuota['estado'] == 'pagada'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25 rounded-pill"><i class="fa-solid fa-check me-1"></i> Pagada</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 border border-warning border-opacity-25 rounded-pill"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- PAGINACIÓN MIS RECIBOS -->
                            <?php if ($totalPaginasRecibos > 1): ?>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top" style="border-color: var(--color-borde) !important;">
                                    <span class="small text-muted d-none d-sm-inline">Página <?= $paginaRecibos ?> de <?= $totalPaginasRecibos ?></span>
                                    <nav>
                                        <ul class="pagination pagination-sm m-0">
                                            <li class="page-item <?= ($paginaRecibos <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=mis-recibos&p_rec=<?= $paginaRecibos - 1 ?>&p_gas=<?= $paginaGastos ?>">&laquo;</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $totalPaginasRecibos; $i++): ?>
                                                <li class="page-item <?= ($i == $paginaRecibos) ? 'active' : '' ?>">
                                                    <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=mis-recibos&p_rec=<?= $i ?>&p_gas=<?= $paginaGastos ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= ($paginaRecibos >= $totalPaginasRecibos) ? 'disabled' : '' ?>">
                                                <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=mis-recibos&p_rec=<?= $paginaRecibos + 1 ?>&p_gas=<?= $paginaGastos ?>">&raquo;</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- PESTAÑA: TRANSPARENCIA (GASTOS COMUNIDAD) -->
                    <div class="tab-pane fade <?= $tabActiva === 'transparencia' ? 'show active' : '' ?>" id="transparencia" role="tabpanel">
                        <div class="row g-4">

                            <!-- GRÁFICA -->
                            <div class="col-md-5 col-lg-4">
                                <div class="card shadow-sm border-0 h-100 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-4 text-center" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Evolución del Ejercicio <?= date('Y') ?></h6>
                                        <div class="row g-3 mb-4 text-center">
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Ingresos</small>
                                                <span class="fs-5 fw-bold text-success">+ <?= number_format($resumenAnual['ingresos'], 2) ?> €</span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Gastos</small>
                                                <span class="fs-5 fw-bold text-danger">- <?= number_format($resumenAnual['gastos'], 2) ?> €</span>
                                            </div>
                                        </div>
                                        <div style="position: relative; height: 250px; width: 100%;">
                                            <canvas id="chartAnualVecino"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÚLTIMOS GASTOS -->
                            <div class="col-md-7 col-lg-8">
                                <div class="card shadow-sm border-0 h-100 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-4" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Últimos gastos aprobados</h6>

                                        <?php if (empty($ultimosGastos)): ?>
                                            <div class="text-center py-4">
                                                <i class="fa-solid fa-receipt text-muted fs-2 mb-2"></i>
                                                <p class="text-muted small mb-0">No se han registrado gastos recientemente.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column gap-3">
                                                <?php foreach ($ultimosGastos as $gasto): ?>
                                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-opacity-10" style="background-color: var(--bs-secondary); border-color: var(--color-borde) !important;">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div class="rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: var(--bs-light);">
                                                                <i class="fa-regular fa-file-lines text-muted fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1 fw-bold" style="color: var(--bs-dark); font-size: 0.95rem;"><?= htmlspecialchars($gasto['concepto']) ?></h6>
                                                                <small class="text-muted"><?= htmlspecialchars($gasto['categoria']) ?> &bull; <?= date('d/m/Y', strtotime($gasto['fecha'])) ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="ms-2">
                                                            <?php $esIngreso = $gasto['importe'] < 0; ?>
                                                            <span class="fs-6 fw-bold <?= $esIngreso ? 'text-success' : 'text-danger' ?>"><?= $esIngreso ? '+' : '-' ?> <?= number_format(abs($gasto['importe']), 2) ?> €</span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                    <!-- PAGINACIÓN GASTOS COMUNIDAD -->
                                    <?php if ($totalPaginasGastos > 1): ?>
                                        <div class="d-flex justify-content-between align-items-center p-3 border-top" style="border-color: var(--color-borde) !important;">
                                            <span class="small text-muted d-none d-sm-inline">Página <?= $paginaGastos ?> de <?= $totalPaginasGastos ?></span>
                                            <nav>
                                                <ul class="pagination pagination-sm m-0">
                                                    <li class="page-item <?= ($paginaGastos <= 1) ? 'disabled' : '' ?>">
                                                        <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=transparencia&p_rec=<?= $paginaRecibos ?>&p_gas=<?= $paginaGastos - 1 ?>">&laquo;</a>
                                                    </li>
                                                    <?php for ($i = 1; $i <= $totalPaginasGastos; $i++): ?>
                                                        <li class="page-item <?= ($i == $paginaGastos) ? 'active' : '' ?>">
                                                            <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=transparencia&p_rec=<?= $paginaRecibos ?>&p_gas=<?= $i ?>"><?= $i ?></a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    <li class="page-item <?= ($paginaGastos >= $totalPaginasGastos) ? 'disabled' : '' ?>">
                                                        <a class="page-link shadow-sm" href="index.php?route=finanzas/index&tab=transparencia&p_rec=<?= $paginaRecibos ?>&p_gas=<?= $paginaGastos + 1 ?>">&raquo;</a>
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

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Lógica visual de las pestañas
        const tabButtons = document.querySelectorAll('#finanzasVecinoTab button[data-bs-toggle="pill"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                tabButtons.forEach(b => {
                    b.classList.remove('text-dark', 'shadow-sm');
                    b.classList.add('text-muted');
                    b.style.backgroundColor = 'transparent';
                });
                e.target.classList.add('text-dark', 'shadow-sm');
                e.target.classList.remove('text-muted');
                e.target.style.backgroundColor = 'var(--bs-light)';
            });
        });

        // Inicializar Gráfica Chart.js
        const ctxAnual = document.getElementById('chartAnualVecino').getContext('2d');
        const isDarkInitial = document.documentElement.getAttribute('data-theme') === 'dark';

        const colorIngresos = '#5CB244';
        const colorGastos = '#A41E34';

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

        // Observador para detectar clics en el botón de Modo Oscuro en tiempo real
        const themeObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-theme') {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
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