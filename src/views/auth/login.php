<main class="d-flex justify-content-center align-items-center flex-grow-1 bg-light">
    <div class="card shadow-sm login-card-custom">
        <div class="card-body p-4 p-md-5">

            <!-- logo y título -->
            <div class="text-center mb-4">
                <img src="assets/img/Logo.png" alt="Logo" class="rounded-circle shadow-sm mb-3" style="width: 72px; height: 72px; object-fit: cover;">
                <h1 class="fw-bold mb-1" style="font-size: 1.25rem;">GestFincas</h1>
                <p class="text-secondary small mb-0">Sistema de gestión de comunidades</p>
            </div>

            <!-- Formulario -->
            <form id="loginForm" action="index.php?route=auth/loginAction" method="POST">
                <div class="mb-3">
                    <label for="nombre_vivienda" class="form-label fw-medium text-dark text-sm-custom">Nombre de la Vivienda</label>
                    <input type="text" id="nombre_vivienda" name="nombre_vivienda" placeholder="Ej: Piso 1A" class="form-control custom-input" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium text-dark text-sm-custom">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" class="form-control custom-input" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-medium text-dark text-sm-custom">Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="••••••••" class="form-control custom-input border-right-0" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <div class="input-group-append">
                            <button class="btn bg-light custom-input-toggle shadow-none" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($mensajeExito)): ?>
                    <div class="alert alert-success py-2 text-center alert-custom" role="alert">
                        <?php echo htmlspecialchars($mensajeExito); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger py-2 text-center alert-custom" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-brand w-100 mt-2 mb-4">Iniciar Sesión</button>
            </form>

            <!-- Separador y extras -->
            <div class="text-center border-top pt-3">
                <p class="small text-muted mb-1">¿Es tu primera vez?</p>
                <a href="index.php?route=auth/register" class="btn btn-link text-primary text-decoration-none p-0 text-sm-custom fw-bold">Darme de alta</a>
            </div>

        </div>

    </div>
</main>

<script>
    // Envolvemos en DOMContentLoaded para mayor seguridad
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>