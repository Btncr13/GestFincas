<?php

/**
 * @var array $usuario          Datos del usuario (nombre, apellidos, email, vivienda, etc.)
 * @var string $nombreComunidad Nombre de la comunidad para el topbar/sidebar
 * @var string $nombreVivienda  Nombre de la vivienda
 * @var string $direccion       Dirección completa
 * @var string $rol             Rol actual (vecino o presidente)
 * @var string $rolReal         Rol original del usuario
 */
include 'src/views/components/topbar.php';
?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">

                <!-- Título de sección -->
                <div class="mb-4">
                    <h2 class="fw-bold" style="font-family: var(--fuente-titulos);">Mi Perfil</h2>
                    <p class="text-muted small">Consulta y gestiona tu información personal y de acceso.</p>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-12">
                        <!-- Tarjeta de Datos (Estilo module-card) -->
                        <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-primary) !important;">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm flex-shrink-0" style="width: 56px; height: 56px;">
                                        <i class="fa-solid fa-circle-user fs-2 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1" style="font-family: var(--fuente-titulos);">Datos Personales</h5>
                                        <p class="text-muted small mb-0">Información básica de tu cuenta</p>
                                    </div>
                                </div>

                                <div class="row g-4 mb-2">
                                    <!-- Nombre -->
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Nombre</label>
                                        <div class="fs-6 fw-bold text-dark"><?= htmlspecialchars($usuario['nombre']) ?></div>
                                    </div>
                                    <!-- Apellidos -->
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Apellidos</label>
                                        <div class="fs-6 fw-bold text-dark"><?= htmlspecialchars($usuario['apellidos']) ?></div>
                                    </div>
                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Correo Electrónico</label>
                                        <div class="fs-6 fw-bold text-dark"><?= htmlspecialchars($usuario['email']) ?></div>
                                    </div>

                                    <!-- Vivienda -->
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Vivienda Asignada</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-2">
                                                <i class="fa-solid fa-house-user"></i>
                                            </span>
                                            <div class="fs-6 fw-bold text-dark"><?= htmlspecialchars($usuario['nombre_vivienda']) ?></div>
                                        </div>
                                    </div>
                                    <!-- Comunidad -->
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Comunidad</label>
                                        <div class="fs-6 fw-bold text-dark">
                                            <i class="fa-regular fa-building me-1 text-primary"></i> <?= htmlspecialchars($usuario['nombre_comunidad']) ?>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4" style="opacity: 0.1;">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?route=usuario/cambiarPassword" class="btn btn-outline-primary btn-sm px-4">
                                        <i class="fa-solid fa-key me-2"></i> Cambiar Contraseña
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>