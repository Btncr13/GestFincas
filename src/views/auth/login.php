<main class="d-flex justify-content-center align-items-center flex-grow-1 bg-light min-vh-100">
    <div class="card shadow-sm login-card-custom position-relative">
        
        <button class="btn btn-link position-absolute top-0 end-0 m-3 text-muted shadow-none" id="themeToggleBtn" style="z-index: 10;">
            <i class="fa-solid fa-moon fs-5" id="themeIcon"></i>
        </button>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="public/assets/img/Logo.png" alt="Logo" class="rounded-circle shadow-sm mb-3 logo-adaptable" style="width: 72px; height: 72px; object-fit: cover;">                
                <h1 class="fw-bold mb-1" style="font-size: 1.25rem;">GestFincas</h1>
                <p class="text-secondary small mb-0">Sistema de gestión de comunidades</p>
            </div>

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
                        <input type="password" id="password" name="password" placeholder="••••••••" class="form-control custom-input border-end-0" required>
                        <button class="btn bg-light border border-start-0 custom-input-toggle shadow-none" type="button" id="togglePassword">
                            <i class="fa-solid fa-eye text-muted" id="toggleIconEye"></i>
                        </button>
                    </div>
                </div>

                <?php if (isset($mensajeExito)): ?>
                    <div class="alert alert-success py-2 text-center alert-custom" role="alert"><?= htmlspecialchars($mensajeExito) ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger py-2 text-center alert-custom" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100 mt-3 mb-4" style="background-color: var(--bs-primary); border: none;">Iniciar Sesión</button>
            </form>

            <div class="text-center border-top pt-3">
                <p class="small text-muted mb-1">¿Es tu primera vez?</p>
                <a href="index.php?route=auth/register" class="btn btn-link text-primary text-decoration-none p-0 text-sm-custom fw-bold">Darme de alta</a>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Lógica del Ojo de contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('toggleIconEye');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Lógica del Modo Oscuro
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        const updateUI = () => {
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            themeIcon.className = isDark ? 'fa-solid fa-sun fs-5' : 'fa-solid fa-moon fs-5';
        };

        updateUI();

        themeBtn.addEventListener('click', () => {
            const newTheme = htmlElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('gestfincas-theme', newTheme);
            updateUI();
        });
    });
</script>