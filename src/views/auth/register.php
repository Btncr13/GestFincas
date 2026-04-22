<?php
// Capturamos el código si viene por la URL
$codigoPreevaluado = $_GET['codigo'] ?? '';
?>
<main class="d-flex justify-content-center align-items-center min-vh-100 bg-light px-3">
    <div class="card shadow-sm register-card-custom mx-auto position-relative">
        
        <button class="btn btn-link position-absolute top-0 end-0 m-3 text-muted shadow-none" id="themeToggleBtn" style="z-index: 10;">
            <i class="fa-solid fa-moon fs-5" id="themeIcon"></i>
        </button>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle mb-3 logo-bg-brand" style="width: 60px; height: 60px;">
                    <img src="public/assets/img/Logo.png" class="rounded-circle w-100 h-100 object-fit-cover logo-adaptable">
                </div>
                <h1 class="fs-5 fw-bold mb-1">GestFincas</h1>
                <p class="text-muted small mb-0">Registro de nuevo vecino</p>
            </div>

            <form id="registerForm" action="index.php?route=auth/registerAction" method="POST" class="needs-validation" novalidate>

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
                        <input type="text" name="dni" id="reg_dni" class="form-control custom-input" placeholder="12345678A" required pattern="[0-9]{8}[A-Z]" maxlength="9" title="Debe contener 8 números y una letra mayúscula">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Correo</label>
                        <input type="email" name="email" class="form-control custom-input" placeholder="usuario@ejemplo.com" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control custom-input" placeholder="••••••••" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Código vivienda</label>
                        <!-- Le inyectamos el valor de PHP y lo ponemos como readonly si ya trae código -->
                        <input type="text" name="codigo_vivienda" class="form-control custom-input" 
                            value="<?= htmlspecialchars($codigoPreevaluado) ?>" 
                            <?= !empty($codigoPreevaluado) ? 'readonly' : '' ?> 
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Comunidad</label>
                        <input type="text" name="comunidad" id="reg_comunidad" class="form-control custom-input bg-light" readonly placeholder="Se rellenará solo">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Vivienda</label>
                        <input type="text" name="vivienda" id="reg_vivienda" class="form-control custom-input bg-light" readonly placeholder="Se rellenará solo">
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-custom mt-3 text-center py-2 small">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-brand w-100 mt-4 py-2 fw-semibold">
                    Registrarse
                </button>
            </form>

            <div class="text-center border-top pt-3 mt-4">
                <p class="small text-muted mb-1">¿Ya tienes cuenta?</p>
                <a href="index.php?route=auth/login" class="fw-semibold text-decoration-none">
                    Iniciar sesión
                </a>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Lógica del Modo Oscuro
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        const updateUI = () => {
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            if (themeIcon) {
                themeIcon.className = isDark ? 'fa-solid fa-sun fs-5' : 'fa-solid fa-moon fs-5';
            }
        };

        // Inicializar icono según el tema actual cargado por el head.php
        updateUI();

        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('gestfincas-theme', newTheme);
                updateUI();
            });
        }

        // Lógica de mostrar contraseña (solo si decides añadir el botón más adelante)
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = document.getElementById('toggleIconEye');
                if (passwordInput && icon) {
                    passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            });
        }

        // Lógica de validación automática de código
        const dniInput = document.getElementById('reg_dni');
        if (dniInput) {
            dniInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        const codigoInput = document.getElementById('reg_codigo');
        const comunidadInput = document.getElementById('reg_comunidad');
        const viviendaInput = document.getElementById('reg_vivienda');

        codigoInput.addEventListener('input', function() {
            const codigo = this.value.trim().toUpperCase();
            this.value = codigo; // Forzar mayúsculas

            if (codigo.length === 8) {
                fetch(`index.php?route=vivienda/verificarCodigoAjax&codigo=${codigo}`)
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            comunidadInput.value = res.data.nombre_comunidad;
                            viviendaInput.value = res.data.nombre_vivienda;
                            codigoInput.classList.add('is-valid');
                            codigoInput.classList.remove('is-invalid');
                        } else {
                            comunidadInput.value = 'Código no válido';
                            viviendaInput.value = 'Código no válido';
                            codigoInput.classList.add('is-invalid');
                        }
                    })
                    .catch(err => {
                        console.error('Error en la petición AJAX:', err);
                        comunidadInput.value = 'Error de conexión';
                    });
            } else {
                comunidadInput.value = '';
                viviendaInput.value = '';
                codigoInput.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
</script>