<?php $titulo_pagina = "Restablecer Contraseña - GestFincas"; ?>
<?php include 'src/views/components/head.php'; // Asegúrate de incluir tu head si usas uno global ?>

<main class="d-flex justify-content-center align-items-center flex-grow-1 min-vh-100 px-3" style="background-color: var(--bs-secondary);">
    <div class="card shadow-sm login-card-custom position-relative mx-auto border-0" style="background-color: var(--bs-light); width: 100%; max-width: 420px; border-radius: var(--radio-lg);">

        <!-- Botón Modo Oscuro -->
        <button class="btn btn-link position-absolute top-0 end-0 m-3 text-muted shadow-none" id="themeToggleBtn" style="z-index: 10;">
            <i class="fa-solid fa-moon fs-5" id="themeIcon"></i>
        </button>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex justify-content-center align-items-center rounded-circle mb-3 logo-bg-brand" style="width: 72px; height: 72px; background-color: var(--bs-primary);">
                    <i class="fa-solid fa-lock fs-1 text-white"></i>
                </div>
                <h1 class="fw-bold mb-1" style="font-size: 1.25rem; color: var(--bs-dark); font-family: var(--fuente-titulos);">Nueva Contraseña</h1>
                <p class="text-muted small mb-0">Crea una nueva contraseña para tu cuenta.</p>
            </div>

            <!-- FORMULARIO REAL CONECTADO AL BACKEND -->
            <form id="resetPasswordForm" onsubmit="event.preventDefault();">
                <!-- Capturamos el token de la URL de forma oculta -->
                <input type="hidden" id="token_secreto" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <!-- Contenedor para mensajes de error del servidor -->
                <div id="errorMensaje" class="alert alert-danger d-none py-2 text-center small mb-3" style="border-radius: var(--radio-md);"></div>

                <div class="mb-3">
                    <label class="form-label fw-medium text-dark text-sm-custom">Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="pass1" class="form-control custom-input border-end-0" placeholder="Escribe tu nueva contraseña" required>
                        <button class="btn border border-start-0 custom-input-toggle shadow-none" style="background-color: var(--color-fondo-formularios); border-color: var(--color-borde) !important;" type="button" onclick="toggleVisibilidad('pass1', 'icon1')">
                            <i class="fa-solid fa-eye text-muted" id="icon1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium text-dark text-sm-custom">Repite la Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="pass2" class="form-control custom-input border-end-0" placeholder="Repítela para confirmar" required>
                        <button class="btn border border-start-0 custom-input-toggle shadow-none" style="background-color: var(--color-fondo-formularios); border-color: var(--color-borde) !important;" type="button" onclick="toggleVisibilidad('pass2', 'icon2')">
                            <i class="fa-solid fa-eye text-muted" id="icon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btnGuardarPassword" class="btn btn-brand w-100 mb-2 fw-semibold border-0 shadow-sm" style="background-color: var(--bs-primary); color: white; padding: 10px; border-radius: var(--radio-md);">
                    Guardar nueva contraseña
                </button>
            </form>

            <!-- MENSAJE DE ÉXITO (Oculto por defecto) -->
            <div id="successBlock" class="text-center d-none mt-2">
                <div class="alert alert-success py-3 text-sm-custom" style="border-radius: var(--radio-md);">
                    <i class="fa-solid fa-circle-check fs-4 mb-2 d-block"></i>
                    ¡Tu contraseña ha sido actualizada correctamente!
                </div>
                <a href="index.php?route=auth/login" class="btn btn-brand w-100 mt-2 fw-semibold shadow-sm" style="background-color: var(--bs-primary); color: white; padding: 10px; border-radius: var(--radio-md); text-decoration: none;">
                    Volver a iniciar sesión
                </a>
            </div>

        </div>
    </div>
</main>

<!-- ==============================================
     AQUÍ EMPIEZA EL SCRIPT DE JAVASCRIPT Y AJAX
     Se ejecuta en el navegador del usuario 
=============================================== -->
<script>
// Función para el ojo (Mostrar/Ocultar contraseña)
function toggleVisibilidad(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Lógica AJAX real asegurada para enviar la nueva contraseña
document.addEventListener('DOMContentLoaded', function() {
    const formReset = document.getElementById('resetPasswordForm');
    
    if (formReset) {
        formReset.addEventListener('submit', async function(e) {
            e.preventDefault(); // Doble seguro para evitar que la página recargue
            
            const btn = document.getElementById('btnGuardarPassword');
            const errorMsg = document.getElementById('errorMensaje');
            
            const pass1 = document.getElementById('pass1').value;
            const pass2 = document.getElementById('pass2').value;
            const token = document.getElementById('token_secreto').value;

            // Reiniciamos el mensaje de error
            errorMsg.classList.add('d-none');

            // Validación front-end rápida
            if(pass1 !== pass2) {
                errorMsg.textContent = "Las contraseñas no coinciden.";
                errorMsg.classList.remove('d-none');
                return;
            }

            if(pass1.length < 6) {
                errorMsg.textContent = "La contraseña debe tener al menos 6 caracteres.";
                errorMsg.classList.remove('d-none');
                return;
            }

            // Efecto de carga en el botón
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';
            btn.disabled = true;

            // Preparamos los datos para enviar al servidor
            const formData = new FormData();
            formData.append('token', token);
            formData.append('pass1', pass1);
            formData.append('pass2', pass2);

            try {
                // Hacemos la petición POST a nuestro endpoint PHP sin recargar
                const response = await fetch('index.php?route=auth/actualizarPasswordAjax', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if(data.success) {
                    // ¡Éxito! Ocultamos formulario y mostramos mensaje verde
                    formReset.classList.add('d-none');
                    document.getElementById('successBlock').classList.remove('d-none');
                } else {
                    // El token caducó o error de DB
                    errorMsg.textContent = data.message;
                    errorMsg.classList.remove('d-none');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch(err) {
                errorMsg.textContent = "Error de conexión con el servidor.";
                errorMsg.classList.remove('d-none');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    // Lógica básica Modo Oscuro
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    const updateUI = () => {
        const isDark = htmlElement.getAttribute('data-theme') === 'dark';
        if (themeIcon) {
            themeIcon.className = isDark ? 'fa-solid fa-sun fs-5' : 'fa-solid fa-moon fs-5';
        }
    };
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
});
</script>