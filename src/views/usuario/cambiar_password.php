<?php

/**
 * Vista para el cambio de contraseña.
 */
include 'src/views/components/topbar.php';
?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">

                <div class="mb-4">
                    <h2 class="fw-bold" style="font-family: var(--fuente-titulos);">Seguridad</h2>
                    <p class="text-muted small">Actualiza tus credenciales de acceso a la plataforma.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">

                        <?php if (isset($_SESSION['error_msg'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                <?= $_SESSION['error_msg'];
                                unset($_SESSION['error_msg']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="card shadow-sm border-0 module-card">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <div class="icon-box-lg bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                        <i class="fa-solid fa-shield-halved fs-2 text-primary"></i>
                                    </div>
                                    <h4 class="fw-bold">Cambiar Contraseña</h4>
                                </div>

                                <form action="index.php?route=usuario/updatePasswordAction" method="POST" id="formPassword">
                                    <div class="mb-3">
                                        <label for="password_actual" class="form-label fw-bold small text-uppercase">Contraseña Actual</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                            <input type="password" class="form-control bg-light border-start-0" id="password_actual" name="password_actual" required placeholder="Ingresa tu contraseña actual">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password_nueva" class="form-label fw-bold small text-uppercase">Nueva Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                            <input type="password" class="form-control bg-light border-start-0" id="password_nueva" name="password_nueva" required placeholder="Mínimo 6 caracteres">
                                        </div>
                                        <div class="form-text text-muted small">Al cambiarla, se cerrará tu sesión automáticamente por seguridad.</div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary py-2">
                                            <i class="fa-solid fa-rotate me-2"></i> Actualizar y Cerrar Sesión
                                        </button>
                                        <a href="index.php?route=usuario/perfil" class="btn btn-link text-muted btn-sm">Cancelar</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!--- Archivo a script para validación front de contraseña cambiada---->
<script src="public/assets/js/usuario/contrasena.js"></script>