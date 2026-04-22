<?php $titulo_pagina = "Finanzas"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                
                <!-- TÍTULO -->
                <div class="mb-4">
                    <h2 class="mb-1 fw-bold" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Finanzas</h2>
                    <p class="text-muted small mb-0">Control financiero de la comunidad y gestión de cuotas</p>
                </div>

                <!-- GASTOS PENDIENTES DE APROBACIÓN -->
                <div class="card border-0 border-start border-4 border-warning shadow-sm mb-4 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="fw-bold mb-0" style="color: var(--bs-warning); font-family: var(--fuente-titulos);">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Gastos Pendientes de Aprobación (<?= count($gastosPendientes) ?>)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($gastosPendientes as $gasto): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-opacity-10" style="background-color: var(--bs-secondary); border-color: var(--color-borde) !important;">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- FIX 1: Cambiado bg-white por background-color: var(--bs-light) -->
                                    <div class="rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: var(--bs-light);">
                                        <i class="fa-regular fa-file-lines text-muted fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold" style="color: var(--bs-dark);"><?= htmlspecialchars($gasto['concepto']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($gasto['categoria']) ?> &bull; <?= htmlspecialchars($gasto['fecha']) ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fs-5 fw-bold" style="color: var(--bs-dark);"><?= number_format($gasto['importe'], 2) ?> €</span>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" style="width: 35px; height: 35px;" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>
                                        <button class="btn btn-success btn-sm rounded-circle shadow-sm text-white" style="width: 35px; height: 35px;" title="Aprobar"><i class="fa-solid fa-check"></i></button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- TABS (Mensual, Anual, Histórico) -->
                <ul class="nav nav-pills d-flex mb-4 p-1 shadow-sm" id="finanzasTab" role="tablist" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                    <!-- FIX 2: Añadido background-color: var(--bs-light) a la pestaña activa -->
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link active w-100 fw-semibold text-dark rounded-2 shadow-sm" data-bs-toggle="pill" data-bs-target="#mensual" type="button" style="transition: all 0.2s; background-color: var(--bs-light);">Mensual</button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link w-100 fw-semibold text-muted rounded-2" data-bs-toggle="pill" data-bs-target="#anual" type="button" style="transition: all 0.2s; background-color: transparent;">Anual</button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link w-100 fw-semibold text-muted rounded-2" data-bs-toggle="pill" data-bs-target="#historico" type="button" style="transition: all 0.2s; background-color: transparent;">Histórico</button>
                    </li>
                </ul>

                <div class="tab-content" id="finanzasTabContent">
                    <!-- PESTAÑA MENSUAL -->
                    <div class="tab-pane fade show active" id="mensual" role="tabpanel">
                        <div class="row g-4 mb-4">
                            <!-- Gráfica Donut -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-4" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Marzo 2026 - Ingresos vs Gastos</h6>
                                        <div style="position: relative; height: 250px; width: 100%;">
                                            <canvas id="chartMensual"></canvas>
                                        </div>
                                        <div class="d-flex justify-content-around mt-4 text-center">
                                            <div>
                                                <small class="text-muted d-block">Ingresos</small>
                                                <span class="fs-5 fw-bold text-success">4365.00 €</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Gastos</small>
                                                <span class="fs-5 fw-bold text-danger">1985.05 €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ESTADO DE CUOTAS DE VECINOS -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <!-- FIX: Usamos la clase text-dark nativa que tu CSS ya adapta al modo oscuro -->
                                            <h6 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">Estado de Cuotas</h6>
                                            <button class="btn btn-sm btn-outline-primary fw-semibold"><i class="fa-solid fa-bell me-1"></i> Reclamar impagos</button>
                                        </div>
                                        <div class="table-responsive flex-grow-1">
                                            <!-- FIX: Añadimos table-borderless para anular los bordes nativos rebeldes de Bootstrap -->
                                            <table class="table table-borderless align-middle mb-0 text-sm-custom">
                                                <tbody>
                                                    <?php foreach ($cuotasVecinos as $cuota): ?>
                                                    <!-- FIX: Usamos nuestra variable dinámica --color-borde para la línea separadora -->
                                                    <tr style="border-bottom: 1px solid var(--color-borde);">
                                                        
                                                        <!-- FIX: bg-transparent permite que el fondo oscuro de la tarjeta traspase a la celda -->
                                                        <td class="py-3 ps-0 bg-transparent">
                                                            <div class="fw-bold text-dark"><?= htmlspecialchars($cuota['vivienda']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($cuota['vecino']) ?></small>
                                                        </td>
                                                        <td class="py-3 text-end pe-0 bg-transparent">
                                                            <?php if($cuota['estado'] == 'Al corriente'): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25">Al corriente</span>
                                                            <?php elseif($cuota['estado'] == 'Pendiente'): ?>
                                                                <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 border border-warning border-opacity-25">Pendiente (<?= $cuota['deuda']?>€)</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25">Moroso (<?= $cuota['deuda']?>€)</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PESTAÑA ANUAL -->
                    <div class="tab-pane fade" id="anual" role="tabpanel">
                        <div class="card shadow-sm border-0 module-card text-center py-5" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                            <i class="fa-solid fa-chart-pie text-muted fs-1 mb-3"></i>
                            <h5 class="text-muted fw-bold">Resumen Anual</h5>
                            <p class="text-muted small">Selecciona un año para ver el ejercicio contable completo.</p>
                        </div>
                    </div>

                    <!-- PESTAÑA HISTÓRICO -->
                    <div class="tab-pane fade" id="historico" role="tabpanel">
                        <div class="card shadow-sm border-0 module-card text-center py-5" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                            <i class="fa-solid fa-list-check text-muted fs-1 mb-3"></i>
                            <h5 class="text-muted fw-bold">Histórico de Movimientos</h5>
                            <p class="text-muted small">Consulta el registro de todas las facturas, recibos y transferencias.</p>
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
    
    // FIX 2: Comportamiento JS para las Pestañas (Evitamos inyectar bg-white)
    const tabButtons = document.querySelectorAll('#finanzasTab button[data-bs-toggle="pill"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            tabButtons.forEach(b => {
                b.classList.remove('text-dark', 'shadow-sm');
                b.classList.add('text-muted');
                b.style.backgroundColor = 'transparent';
            });
            e.target.classList.add('text-dark', 'shadow-sm');
            e.target.classList.remove('text-muted');
            e.target.style.backgroundColor = 'var(--bs-light)'; // Usa el color del tema en lugar de blanco puro
        });
    });

    // FIX 3: Inicializar Gráfica Chart.js y crear observador de tema
    const ctx = document.getElementById('chartMensual').getContext('2d');
    const isDarkInitial = document.documentElement.getAttribute('data-theme') === 'dark';
    
    const colorIngresos = '#5CB244'; 
    const colorGastos = '#A41E34';   

    let chartMensual = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Ingresos', 'Gastos'],
            datasets: [{
                data: [4365.00, 1985.05],
                backgroundColor: [colorIngresos, colorGastos],
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
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDarkInitial ? '#CDD6F4' : '#221C35',
                        font: { family: 'Lato', size: 13, weight: 'bold' },
                        padding: 20
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
                chartMensual.data.datasets.borderColor = isDark ? '#1E1E2E' : '#FFFFFF';
                // Cambiamos el color de las letras de la leyenda
                chartMensual.options.plugins.legend.labels.color = isDark ? '#CDD6F4' : '#221C35';
                chartMensual.update();
            }
        });
    });
    
    themeObserver.observe(document.documentElement, { attributes: true });
});
</script>