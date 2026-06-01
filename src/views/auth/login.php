<main class="d-flex justify-content-center align-items-center flex-grow-1 bg-light min-vh-100">
    <div class="card shadow-sm login-card-custom border-0 position-relative">

        <button class="btn btn-link position-absolute top-0 end-0 m-3 text-muted shadow-none" id="themeToggleBtn">
            <i class="fa-solid fa-moon fs-5" id="themeIcon"></i>
        </button>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="public/assets/img/Logo.png" alt="Logo" class="rounded-circle shadow-sm mb-3 logo-adaptable">
                <h2 class="fw-bold mb-1">GestFincas</h2>
                <p class="text-secondary small mb-0">Sistema de gestión de comunidades</p>
            </div>

            <form id="loginForm" action="index.php?route=auth/loginAction" method="POST">
                <div class="mb-3">
                    <label for="nombre_vivienda" class="form-label fw-medium text-dark text-sm-custom">Nombre de la Vivienda</label>
                    <input type="text" id="nombre_vivienda" name="nombre_vivienda"
                        placeholder="Ej: Planta 1-C"
                        class="form-control custom-input"
                        required
                        pattern="Planta\s+(Bajo|bajo|[1-9]|[12][0-9]|30)-([1-9]|10|[A-Ma-m])">
                    <div class="invalid-feedback" style="font-size: 0.75rem;">
                        Ejemplo: Planta 1-C / Planta Bajo-D
                    </div>
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

                <button type="submit" class="btn btn-primary w-100 mt-3 mb-4">Iniciar Sesión</button>
            </form>
            <div class="text-end mb-4">
                <span class="text-primary small fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalRecuperarPassword" role="button">
                    ¿Has olvidado tu contraseña?
                </span>
            </div>
            <div class="text-center border-top pt-3">
                <p class="small text-muted mb-1">¿Es tu primera vez?</p>
                <a href="index.php?route=auth/register" class="btn btn-link text-primary text-decoration-none p-0 text-sm-custom fw-bold">Darme de alta</a>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalRecuperarPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-unlock-keyhole me-2 text-primary"></i>Recuperar Contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">Introduce tu correo electrónico. Te enviaremos un enlace seguro para que puedas crear una nueva contraseña.</p>

                <div id="recuperarError" class="alert alert-danger d-none py-2 text-center small"></div>
                <div id="recuperarExito" class="alert alert-success d-none py-2 text-center small"></div>

                <form id="formRecuperarPassword">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Correo electrónico</label>
                        <input type="email" id="email_recuperacion" class="form-control custom-input" placeholder="usuario@ejemplo.com" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 py-3">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnEnviarRecuperacion" class="btn btn-brand fw-semibold shadow-sm">
                    Enviar instrucciones
                </button>
            </div>
        </div>
    </div>
</div>

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

        // Validación en tiempo real para el nombre de la vivienda
        const inputVivienda = document.getElementById('nombre_vivienda');
        inputVivienda.addEventListener('input', function() {
            if (this.value !== "" && !this.checkValidity()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
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

    // Lógica para enviar el correo de recuperación mediante AJAX
    const btnEnviarRecuperacion = document.getElementById('btnEnviarRecuperacion');
    if (btnEnviarRecuperacion) {
        btnEnviarRecuperacion.addEventListener('click', async function() {
            const email = document.getElementById('email_recuperacion').value.trim();
            const errorAlert = document.getElementById('recuperarError');
            const exitoAlert = document.getElementById('recuperarExito');

            errorAlert.classList.add('d-none');
            exitoAlert.classList.add('d-none');

            if (!email) {
                errorAlert.textContent = 'Por favor, introduce tu correo electrónico.';
                errorAlert.classList.remove('d-none');
                return;
            }

            // Cambiamos el texto del botón mientras carga
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            this.disabled = true;

            const formData = new FormData();
            formData.append('email', email);

            try {
                const response = await fetch('index.php?route=auth/enviarRecuperacionAjax', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('formRecuperarPassword').reset();
                    exitoAlert.textContent = data.message;
                    exitoAlert.classList.remove('d-none');
                } else {
                    errorAlert.textContent = data.message; // "No existe ningún usuario con ese email"
                    errorAlert.classList.remove('d-none');
                }
            } catch (error) {
                errorAlert.textContent = 'Error de conexión con el servidor.';
                errorAlert.classList.remove('d-none');
            } finally {
                this.innerHTML = originalText;
                this.disabled = false;
            }
        });
    }
</script>