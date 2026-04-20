<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0 position-relative" id="appComunidad">

                <!-- 1. BANNER PRINCIPAL -->
                <div class="card border-0 mb-4 overflow-hidden shadow-sm" style="min-height: 200px; border-radius: var(--radio-lg);">
                    <img src="public/assets/img/banner.jpeg" alt="Comunidad" class="card-img w-100 h-100 object-fit-cover position-absolute" style="filter: brightness(0.6);">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                <i class="fa-solid fa-users fs-4 text-white"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: var(--fuente-titulos);">Gestión de Usuarios</h2>
                                <p class="mb-0 fw-semibold text-white" style="font-size: 1.1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Directorio de vecinos y gestión de roles</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. BOTÓN DE ACCIÓN (Debajo del banner) -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn fw-semibold d-flex align-items-center gap-2 px-4 py-2 shadow-sm text-white" 
                            style="background-color: var(--bs-success); border-radius: var(--radio-md); border: none;"
                            onclick="appUsuarios.abrirModalAsignar()">
                        <i class="fa-solid fa-user-plus"></i> Asignar Vecino
                    </button>
                </div>

                <!-- 3. BUSCADOR Y FILTROS -->
                <div class="card shadow-sm border-0 mb-4" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body p-3 d-flex flex-column flex-md-row gap-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--color-borde);">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 custom-input ps-0 shadow-none" 
                                   id="buscarUsuario" placeholder="Buscar por vivienda o email..." 
                                   style="background-color: transparent; border-color: var(--color-borde);"
                                   onkeyup="appUsuarios.renderLista()">
                        </div>
                        <select class="form-select custom-input w-auto shadow-none" id="filtroRol" style="min-width: 150px; background-color: transparent; border-color: var(--color-borde);" onchange="appUsuarios.renderLista()">
                            <option value="todos">Todos</option>
                            <option value="vecino">Vecinos</option>
                            <option value="presidente">Presidentes</option>
                        </select>
                    </div>
                </div>

                <!-- 4. TARJETAS DE RESUMEN (KPIs) -->
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-4" id="kpi-cards">
                    <!-- Se llenan por JS -->
                </div>

                <!-- 5. LISTADO DE VECINOS -->
                <div class="d-flex flex-column gap-3" id="lista-usuarios">
                    <!-- Se llenan por JS -->
                </div>

            </div>
        </main>
    </div>
</div>

<!-- Modal Asignar Nuevo Vecino -->
<div class="modal fade" id="modalAsignarVecino" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <!-- Cabecera del Modal -->
            <div class="modal-header border-0" style="background-color: var(--bs-primary);">
                <h5 class="modal-title fw-bold text-white" id="modalAsignarLabel" style="font-family: var(--fuente-titulos);">
                    <i class="fa-solid fa-user-plus me-2"></i>Asignar Nuevo Vecino
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Formulario -->
            <form id="formAsignarVecino" onsubmit="appUsuarios.asignarVecino(event)">
                <div class="modal-body p-4" style="background-color: var(--bs-light);">
                    <p class="text-muted small mb-4">
                        Introduce los detalles de la vivienda y el correo del propietario o inquilino. El sistema registrará la vivienda y generará un código de validación único para su registro.
                    </p>

                    <!-- Campo Vivienda (Piso y Letra) -->
                    <div class="mb-3">
                        <label for="nombreVivienda" class="form-label fw-semibold" style="color: var(--bs-dark);">Vivienda (Piso y Letra) *</label>
                        <input type="text" class="form-control custom-input" id="nombreVivienda" name="nombre_vivienda" placeholder="Ej: Planta 3 - Letra A" required>
                    </div>

                    <!-- Campo Correo Electrónico -->
                    <div class="mb-4">
                        <label for="emailVecino" class="form-label fw-semibold" style="color: var(--bs-dark);">Correo Electrónico *</label>
                        <input type="email" class="form-control custom-input" id="emailVecino" name="email" placeholder="ejemplo@correo.com" required>
                    </div>
                    
                    <!-- Contenedor de errores (Oculto por defecto) -->
                    <div id="errorAsignar" class="alert alert-danger d-none py-2 small text-center"></div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer border-0" style="background-color: var(--color-fondo-formularios);">
                    <button type="button" class="btn btn-secondary fw-semibold text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnAsignarSubmit" class="btn fw-semibold text-white shadow-sm" style="background-color: var(--bs-success);">
                        <i class="fa-solid fa-paper-plane me-2"></i>Generar y Enviar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- SCRIPT DE RENDERIZADO Y LÓGICA -->
<script>
// Mock de datos: El back-end sustituirá esto por json_encode($usuariosData)
let usuariosDB = [
    { id: 1, vivienda: 'Vivienda 3ºA', nombre: 'Maria', apellidos: 'Garcia', email: 'maria.garcia@email.com', rol: 'vecino' },
    { id: 2, vivienda: 'Vivienda 1ºA', nombre: 'Pepe', apellidos: 'Perez', email: 'presidente@gestfincas.com', rol: 'presidente' },
    { id: 3, vivienda: 'Vivienda 5ºC', nombre: 'Ana', apellidos: 'Martin', email: 'ana.martin@email.com', rol: 'vecino' },
    { id: 4, vivienda: 'Vivienda 1ºB', nombre: 'Pedro', apellidos: 'Sanchez', email: 'pedro.sanchez@email.com', rol: 'vecino' },
    { id: 5, vivienda: 'Vivienda 2ºA', nombre: 'Laura', apellidos: 'Fdez', email: 'laura.fdez@email.com', rol: 'vecino' }
];

const appUsuarios = {
    init: function() {
        this.renderLista();
    },

abrirModalAsignar: function() {
        // Reseteamos el formulario y ocultamos errores previos
        document.getElementById('formAsignarVecino').reset();
        document.getElementById('errorAsignar').classList.add('d-none');
        
        // Abrimos el modal de Bootstrap 5
        const modal = new bootstrap.Modal(document.getElementById('modalAsignarVecino'));
        modal.show();
    },

    asignarVecino: async function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btnAsignarSubmit');
        const divError = document.getElementById('errorAsignar');
        
        // Estado de carga en el botón
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        divError.classList.add('d-none');

        // Capturar los datos
        const formData = new FormData();
        formData.append('nombre_vivienda', document.getElementById('nombreVivienda').value);
        formData.append('email', document.getElementById('emailVecino').value);

        try {
            // Petición AJAX (Fetch) al backend
            const response = await fetch('index.php?route=miComunidad/asignarVecinoAction', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Cerramos el modal
                const modalEl = document.getElementById('modalAsignarVecino');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();

                // Recargamos la vista dinámicamente tras 1 segundo
                setTimeout(() => window.location.reload(), 1000);
            } else {
                // Si el servidor devuelve error, lo mostramos en el modal
                divError.textContent = data.message || 'Error al asignar el vecino.';
                divError.classList.remove('d-none');
            }
        } catch (error) {
            divError.textContent = 'Error crítico de conexión con el servidor.';
            divError.classList.remove('d-none');
        } finally {
            // Restaurar el botón
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Generar y Enviar';
        }
    },

    cambiarRol: function(id_usuario, nuevoRol) {
        // Aquí irá la llamada AJAX (Fetch) al backend para cambiar el rol en BD
        alert("Llamada AJAX simulada: Cambiando usuario " + id_usuario + " a " + nuevoRol);
    },

    renderLista: function() {
        const busqueda = document.getElementById('buscarUsuario').value.toLowerCase();
        const filtro = document.getElementById('filtroRol').value;

        // Filtrar usuarios
        let usuariosFiltrados = usuariosDB.filter(u => {
            const texto = (u.vivienda + " " + u.email + " " + u.nombre + " " + u.apellidos).toLowerCase();
            const coincideTexto = texto.includes(busqueda);
            const coincideRol = (filtro === 'todos') || (u.rol === filtro);
            return coincideTexto && coincideRol;
        });

        // 1. Actualizar Tarjetas KPI
        const total = usuariosDB.length;
        const presidentes = usuariosDB.filter(u => u.rol === 'presidente').length;
        const vecinos = usuariosDB.filter(u => u.rol === 'vecino').length;

        document.getElementById('kpi-cards').innerHTML = `
            <div class="col">
                <div class="card shadow-sm border-0 h-100 py-3" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body text-center p-2 lh-1">
                        <div style="font-family: var(--fuente-titulos); font-weight:700; font-size:24px; color: var(--bs-dark);">${total}</div>
                        <small style="font-size:12px; color:var(--color-texto);">Usuarios Totales</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100 py-3" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body text-center p-2 lh-1">
                        <div style="font-family: var(--fuente-titulos); font-weight:700; font-size:24px; color: var(--bs-dark);">${presidentes}</div>
                        <small style="font-size:12px; color:var(--color-texto);">Presidentes / Admins</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow-sm border-0 h-100 py-3" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body text-center p-2 lh-1">
                        <div style="font-family: var(--fuente-titulos); font-weight:700; font-size:24px; color: var(--bs-dark);">${vecinos}</div>
                        <small style="font-size:12px; color:var(--color-texto);">Vecinos</small>
                    </div>
                </div>
            </div>
        `;

        // 2. Renderizar Lista
        const listaHtml = usuariosFiltrados.length === 0 ? 
            `<div class="text-center py-5 rounded-3 shadow-sm" style="background-color: var(--bs-light);">
                <i class="fa-solid fa-users-slash text-muted mb-3" style="font-size: 48px;"></i>
                <p class="text-muted mb-0">No se han encontrado usuarios con esos filtros.</p>
            </div>` 
            : 
            usuariosFiltrados.map(u => {
                // Configurar etiquetas de roles
                const isPresi = u.rol === 'presidente';
                const badgeHtml = isPresi 
                    ? `<span class="badge text-dark ms-2" style="background-color: #fbd38d; font-size: 10px; font-weight:600;">Presidente</span>`
                    : `<span class="badge bg-transparent border text-muted ms-2" style="font-size: 10px; font-weight:500;">Vecino</span>`;

                return `
                <div class="card shadow-sm border-0 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Icono Izquierda -->
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                 style="width: 48px; height: 48px; background-color: var(--bs-primary); color: white;">
                                <i class="fa-solid fa-house-chimney fs-5"></i>
                            </div>
                            <!-- Datos -->
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">${u.vivienda}</h6>
                                    ${badgeHtml}
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-1">
                                    <i class="fa-regular fa-envelope"></i> ${u.email}
                                </div>
                            </div>
                        </div>

                        <!-- Menú de Opciones (Tres puntos) -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted shadow-none px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="background-color: var(--bs-light);">
                                ${isPresi ? 
                                    `<li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)" onclick="appUsuarios.cambiarRol(${u.id}, 'vecino')"><i class="fa-solid fa-user text-muted" style="width: 16px;"></i> Revocar Presidente</a></li>` 
                                    : 
                                    `<li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)" onclick="appUsuarios.cambiarRol(${u.id}, 'presidente')"><i class="fa-solid fa-crown text-warning" style="width: 16px;"></i> Promover a Presidente</a></li>`
                                }
                                <li><hr class="dropdown-divider" style="border-color: var(--color-borde);"></li>
                                <li><a class="dropdown-item text-danger d-flex align-items-center gap-2" href="javascript:void(0)"><i class="fa-solid fa-trash-can" style="width: 16px;"></i> Eliminar Usuario</a></li>
                            </ul>
                        </div>
                    </div>
                </div>`;
            }).join('');

        document.getElementById('lista-usuarios').innerHTML = listaHtml;
    }
};

// Iniciar cuando cargue el DOM
document.addEventListener('DOMContentLoaded', () => {
    appUsuarios.init();
});
</script>