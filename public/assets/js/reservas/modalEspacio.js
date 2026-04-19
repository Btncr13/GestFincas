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

        // Definimos el color y el badge según el estado de bloqueo
        const isBloqueado = data.bloqueado == 1;
        const colorClase = isBloqueado ? 'border-danger' : 'border-primary';
        const badgeHTML = isBloqueado 
            ? '<span class="badge bg-danger">Bloqueado</span>' 
            : '<span class="badge bg-primary">Operativo</span>';

        const cardHTML = `
<div class="col" id="espacio-${data.id_espacios_comunidad}">
  <div class="card shadow-sm h-100 border-0 border-start border-4 ${colorClase}">
    <div class="card-body p-4 d-flex flex-column">

      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="text-muted small">
          <div>${data.hora_apertura} - ${data.hora_cierre}</div>
        </div>
        ${badgeHTML}
      </div>

      <h3 class="fs-5 fw-bold">
        ${data.nombre_espacio}
      </h3>

      <div class="small text-muted mb-3">
        Aforo: ${data.aforo} · Máx: ${data.duracion_uso} min
      </div>

      <div class="mt-auto d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm flex-fill"
          onclick='abrirModalEditar(${JSON.stringify(data)})'>
          Editar
        </button>

        <button class="btn btn-sm ${isBloqueado ? 'btn-outline-success' : 'btn-outline-warning'} flex-fill"
          onclick="toggleEstadoEspacio(${data.id_espacios_comunidad}, ${isBloqueado ? 0 : 1})">
          ${isBloqueado ? 'Activar' : 'Bloquear'}
        </button>

        <button class="btn btn-sm btn-outline-danger"
          onclick="eliminarEspacio(${data.id_espacios_comunidad})">
          🗑
        </button>
      </div>

    </div>
  </div>
</div>
`;
        // Eliminamos el mensaje de "No hay espacios" si existe
        const emptyMsg = document.getElementById('mensaje-vacio-espacios');
        if (emptyMsg) emptyMsg.remove();

        // Insertamos la card al principio del contenedor
        contenedorEspacios.insertAdjacentHTML('afterbegin', cardHTML);
    };
});