<?php 
$titulo_pagina = "Panel del Presidente"; 
?>

<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            
            <div class="container-fluid p-0">
                
                <div class="mb-4">
                    <h2 class="h3 fw-bold mb-1" style="font-family: var(--fuente-titulos);">Panel del Presidente</h2>
                    <p class="text-muted">Métricas globales de la comunidad</p>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="metric-card p-3 h-100 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small mb-1">Incidencias abiertas</h6>
                                <div class="metric-value">5</div>
                            </div>
                            <div class="metric-icon-box" style="background-color: var(--bs-danger);">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="metric-card p-3 h-100 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small mb-1">Gastos totales</h6>
                                <div class="metric-value">3835€</div>
                            </div>
                            <div class="metric-icon-box" style="background-color: var(--bs-warning);">
                                <i class="fa-solid fa-piggy-bank"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="metric-card p-3 h-100 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small mb-1">Gastos pendientes</h6>
                                <div class="metric-value">2</div>
                            </div>
                            <div class="metric-icon-box" style="background-color: #221c35;">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="metric-card p-3 h-100 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small mb-1">Confirmar resolución</h6>
                                <div class="metric-value">1</div>
                            </div>
                            <div class="metric-icon-box" style="background-color: var(--bs-success);">
                                <i class="fa-solid fa-file-circle-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="actions-container mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-circle-exclamation text-danger"></i>
                        <h5 class="mb-0 fs-6" style="font-family: var(--fuente-titulos);">Acciones Pendientes</h5>
                    </div>
                    
                    <div class="action-list">
                        <div class="action-item warning">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-piggy-bank text-warning"></i>
                                <span class="text-sm-custom">Tienes 2 gastos pendientes de aprobar</span>
                            </div>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </div>
                        
                        <div class="action-item danger">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-circle-exclamation text-danger"></i>
                                <span class="text-sm-custom">1 incidencia resuelta esperando tu confirmación</span>
                            </div>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </div>
                        
                        <div class="action-item danger">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-circle-exclamation text-danger"></i>
                                <span class="text-sm-custom">1 incidencia urgente activa</span>
                            </div>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </div>
                    </div>
                </div>

                <div class="nav-card-container">
                    <a href="index.php?route=auth/incidencias" class="nav-card">
                        <div class="nav-card-icon" style="background-color: var(--bs-danger);">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <span>Incidencias</span>
                    </a>
                    <a href="index.php?route=auth/finanzas" class="nav-card">
                        <div class="nav-card-icon" style="background-color: #0d6efd;">
                            <i class="fa-solid fa-piggy-bank"></i>
                        </div>
                        <span>Finanzas</span>
                    </a>
                    <a href="index.php?route=auth/usuarios" class="nav-card">
                        <div class="nav-card-icon" style="background-color: var(--bs-success);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <span>Usuarios</span>
                    </a>
                    <a href="index.php?route=auth/comunicaciones" class="nav-card">
                        <div class="nav-card-icon" style="background-color: #6f42c1;">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <span>Comunicaciones</span>
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>