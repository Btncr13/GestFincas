/**
 * Gestión de Modal para Creación de Espacios Comunitarios
 * Archivo: public/assets/js/reservas/modalEspacio.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. ESTADO CENTRALIZADO ---
    const state = {
        form: {
            nombre_espacio: '',
            aforo: 0,
            max_personas: 0,
            hora_apertura: '',
            hora_cierre: '',
            duracion_uso: 30,
            bloqueado: 0,
            motivo: ''
        },
        isValid: false
    };

    // --- 2. SELECTORES ---
    const modalEl = document.getElementById('modalCrearEspacio');
    const formEl = document.getElementById('formCrearEspacio');
    const btnGuardar = document.getElementById('btnGuardarEspacio');
    const contenedorEspacios = document.querySelector('#espacios .row'); // Ajusta  HTML
    
    // Si la modal no existe en el DOM, detenemos la ejecución para evitar errores
    if (!modalEl) return;

    const bootstrapModal = new bootstrap.Modal(modalEl);

    // --- 3. INICIALIZACIÓN Y RESET ---
    modalEl.addEventListener('show.bs.modal', () => {
        resetForm();
    });

    const resetForm = () => {
        formEl.reset();
        state.form = {
            nombre_espacio: '',
            aforo: 0,
            max_personas: 0,
            hora_apertura: '',
            hora_cierre: '',
            duracion_uso: 30,
            bloqueado: 0,
            motivo: ''
        };
        toggleMotivoField(false);
        validateForm();
    };

    // --- 4. ESCUCHA DE CAMBIOS (Update State) ---
    formEl.addEventListener('input', (e) => {
        const { name, value, type, checked } = e.target;
        
        // Actualizar el estado
        if (type === 'checkbox') {
            state.form[name] = checked ? 1 : 0;
            if (name === 'bloqueado') toggleMotivoField(checked);
        } else {
            state.form[name] = value;
        }

        validateForm();
    });

    // --- 5. VALIDACIONES LÓGICAS ---
    const toggleMotivoField = (show) => {
        const motivoGroup = document.getElementById('grupoMotivo');
        if (motivoGroup) motivoGroup.style.display = show ? 'block' : 'none';
    };

    const validateForm = () => {
        const f = state.form;
        
        // Validaciones básicas
        const hasNombre = f.nombre_espacio.trim().length > 0;
        const aforoValido = parseInt(f.aforo) > 0;
        const asistentesValidos = parseInt(f.max_personas) > 0;
        const duracionValida = parseInt(f.duracion_uso) > 0;
        
        // Lógica de negocio: Max Personas <= Aforo
        const coherenciaAforo = parseInt(f.max_personas) <= parseInt(f.aforo);
        
        // Lógica horaria
        const horarioValido = f.hora_apertura !== '' && f.hora_cierre !== '' && f.hora_cierre > f.hora_apertura;
        
        // Validación de bloqueo
        const motivoValido = f.bloqueado === 1 ? f.motivo.trim().length > 3 : true;

        state.isValid = hasNombre && aforoValido && asistentesValidos && 
                        coherenciaAforo && horarioValido && duracionValida && motivoValido;

        btnGuardar.disabled = !state.isValid;

       const inputMax = document.getElementById('max_personas');
        if (inputMax) {
            // Si max_personas es mayor al aforo, añade la clase 'is-invalid' de Bootstrap
            if (!coherenciaAforo && f.max_personas > 0) {
                inputMax.classList.add('is-invalid');
            } else {
                inputMax.classList.remove('is-invalid');
            }
        }
    };

    // --- 6. ENVÍO DE DATOS (FETCH) ---
   // --- 6. ENVÍO DE DATOS (FETCH) ---
    btnGuardar.addEventListener('click', async () => {
        if (!state.isValid) return;

        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';
        btnGuardar.disabled = true;

        const formData = new FormData();
        // IMPORTANTE: Los nombres deben coincidir con lo que recibe el controlador en EspacioController.php
        formData.append('nombre_espacio', state.form.nombre_espacio);
        formData.append('aforo', state.form.aforo);
        formData.append('max_personas', state.form.max_personas);
        formData.append('hora_apertura', state.form.hora_apertura);
        formData.append('hora_cierre', state.form.hora_cierre);
        formData.append('duracion_uso', state.form.duracion_uso);
        formData.append('bloqueado', state.form.bloqueado);
        formData.append('motivo', state.form.motivo);
        
        // CORRECCIÓN: Usamos para acceder al primer elemento de la lista devuelta por name
        const normasInput = document.getElementsByName('normas');
        formData.append('normas', normasInput ? normasInput.value : '');

        try {
            // La ruta 'index.php?route=espacio/store' es correcta según tu router.php
            const response = await fetch('index.php?route=espacio/store', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Usamos el objeto 'espacio' que devuelve tu controlador tras el insert
                insertNewCard(result.espacio); 
                bootstrapModal.hide();
                alert('Espacio creado correctamente');
            } else {
                throw new Error(result.message || 'Error en el servidor');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('No se pudo crear el espacio: ' + error.message);
        } finally {
            btnGuardar.innerHTML = 'Crear Espacio';
            btnGuardar.disabled = false;
        }
    });

    // --- 7. ACTUALIZACIÓN DINÁMICA DE LA UI ---
    const insertNewCard = (data) => {
        if (!contenedorEspacios) return;

        const badge = data.bloqueado === 1 ? 
            '<span class="badge bg-danger">Bloqueado</span>' : 
            '<span class="badge bg-success">Activo</span>';

        const cardHTML = `
            <div class="col-md-4 mb-3 animate__animated animate__fadeIn">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title fw-bold">${data.nombre_espacio}</h5>
                            ${badge}
                        </div>
                        <p class="card-text text-muted small">
                            <i class="fa-solid fa-users me-1"></i> Aforo: ${data.aforo} | 
                            Max Asistentes: ${data.max_personas}
                        </p>
                        <hr>
                        <p class="mb-1"><i class="fa-regular fa-clock me-1"></i> ${data.hora_apertura} - ${data.hora_cierre}</p>
                        <p class="mb-0 small text-primary fw-bold">${data.duracion_uso} min por turno</p>
                    </div>
                </div>
            </div>
        `;
        
        // Si estaba el mensaje de "No hay espacios", lo quitamos
        const emptyMsg = contenedorEspacios.querySelector('.text-center');
        if (emptyMsg) emptyMsg.remove();

        contenedorEspacios.insertAdjacentHTML('afterbegin', cardHTML);
    };
});