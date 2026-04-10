<main class="d-flex justify-content-center align-items-center min-vh-100 bg-light px-3">
    <div class="card shadow-sm register-card-custom mx-auto">

        <div class="card-body p-4 p-md-5">

            <!-- Header -->
            <div class="text-center mb-4">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle mb-3 logo-bg-brand" style="width: 60px; height: 60px;">
                    <img src="assets/img/Logo.png" class="rounded-circle w-100 h-100 object-fit-cover">
                </div>
                <h1 class="fs-5 fw-bold mb-1">GestFincas</h1>
                <p class="text-muted small mb-0">Registro de nuevo vecino</p>
            </div>

            <!-- Form -->
            <form id="registerForm" action="index.php?route=auth/registerAction" method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nombre</label>
                        <input type="text" name="nombre" class="form-control custom-input" placeholder="Tu nombre" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control custom-input" placeholder="Tus apellidos" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">DNI</label>
                        <input type="text" name="dni" class="form-control custom-input" placeholder="12345678A" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Correo</label>
                        <input type="email" name="email" class="form-control custom-input" placeholder="usuario@ejemplo.com" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <input type="password" name="password" class="form-control custom-input" placeholder="••••••••" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Código vivienda</label>
                        <input type="text" name="codigo_vivienda" class="form-control custom-input" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Comunidad</label>
                        <input type="text" name="comunidad" class="form-control custom-input" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Vivienda</label>
                        <input type="text" name="vivienda" class="form-control custom-input" placeholder="Ej: Piso 1A" required>
                    </div>

                </div>

                <!-- Error -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-custom mt-3 text-center py-2 small">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Button -->
                <button type="submit" class="btn-brand w-100 mt-4 py-2 fw-semibold">
                    Registrarse
                </button>

            </form>

            <!-- Footer -->
            <div class="text-center border-top pt-3 mt-4">
                <p class="small text-muted mb-1">¿Ya tienes cuenta?</p>
                <a href="index.php?route=auth/login" class="fw-semibold text-decoration-none">
                    Iniciar sesión
                </a>
            </div>

        </div>
    </div>
</main>