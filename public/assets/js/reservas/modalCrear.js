// ELIMINAR RESERVA

   function eliminarReserva(idReserva) {

    if (!confirm("¿Seguro que deseas cancelar esta reserva?")) {
        return;
    }

    const formData = new FormData();
    formData.append('id_reserva', idReserva);

    fetch('index.php?route=reserva/destroy', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {
            alert(data.message);
            return;
        }

        // Eliminar la tarjeta del DOM
        const card = document.getElementById(`reserva-${idReserva}`);
        if (card) {
            card.remove();
        }

        // Si ya no quedan reservas, mostrar mensaje
        const contenedor = document.getElementById('contenedorReservas');
        if (contenedor && contenedor.children.length === 0) {
            const mensaje = document.getElementById('mensajeSinReservas');
            if (mensaje) mensaje.style.display = 'block';
        }
    })
    .catch(err => console.error("Error al eliminar reserva:", err));
}


document.addEventListener('DOMContentLoaded', () => {

    const selectEspacio = document.getElementById('selectEspacio');
    const inputFecha = document.getElementById('inputFecha');
    const selectTramo = document.getElementById('selectTramo');
    const selectPersonas = document.getElementById('selectPersonas');
    const btnCrear = document.getElementById('btnCrearReserva');

    // -----------------------------------
    // LIMITAR FECHA A HOY → HOY + 14 DÍAS
    // -----------------------------------
    const hoy = new Date();
    const max = new Date();
    max.setDate(hoy.getDate() + 14);

    const formato = d => d.toISOString().split('T')[0];

    inputFecha.min = formato(hoy);
    inputFecha.max = formato(max);

    // -----------------------------------
    // EVENTO: CAMBIO DE ESPACIO
    // -----------------------------------
    selectEspacio.addEventListener('change', (e) => {

        const idEspacio = e.target.value;

        resetSelect(selectPersonas, "Selecciona cantidad...");
        resetSelect(selectTramo, "Selecciona un tramo...");
        btnCrear.disabled = true;

        if (!idEspacio) return;

        const espacio = espaciosDisponibles.find(esp => esp.id_espacios_comunidad == idEspacio);
        if (!espacio) return;

        generarPersonas(espacio.max_personas);
        generarTramos(espacio.hora_apertura, espacio.hora_cierre, espacio.duracion_uso);

        btnCrear.disabled = false;
    });

    // -----------------------------------
    // EVENTO: CREAR RESERVA
    // -----------------------------------
    btnCrear.addEventListener('click', crearReserva);

    // -----------------------------------
    // FUNCIÓN: ENVIAR RESERVA AL BACKEND
    // -----------------------------------
    function crearReserva() {

        const idEspacio = selectEspacio.value;
        const fecha = inputFecha.value;
        const tramo = selectTramo.value;
        const asistentes = selectPersonas.value;

        if (!idEspacio || !fecha || !tramo || !asistentes) {
            alert("Debes completar todos los campos.");
            return;
        }

        const [hora_inicio, hora_fin] = tramo.split('-');

        const formData = new FormData();
        formData.append('id_espacio', idEspacio);
        formData.append('fecha', fecha);
        formData.append('hora_inicio', hora_inicio);
        formData.append('hora_fin', hora_fin);
        formData.append('asistentes', asistentes);

        fetch('index.php?route=reserva/store', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (!data.success) {
                alert(data.message);
                return;
            }

            insertarReservaEnVista(data.reserva);
            const mensajeSinReservas = document.getElementById('mensajeSinReservas');
            if (mensajeSinReservas) mensajeSinReservas.style.display = 'none';

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReserva'));
            modal.hide();
        })
        .catch(err => console.error("Error en la petición:", err));
    }

    // -----------------------------------
    // FUNCIÓN: PINTAR TARJETA EN LA VISTA
    // -----------------------------------
    function insertarReservaEnVista(reserva) {

    const contenedor = document.getElementById('contenedorReservas');

    const card = document.createElement('div');
    card.className = "card shadow-sm mb-3 border-0";
    card.id = `reserva-${reserva.id_reservas}`; // ← NECESARIO PARA ELIMINAR

    card.innerHTML = `
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 fw-bold">${reserva.espacio}</h5>
            </div>

            <p class="mb-1 text-muted">
                <i class="fa-solid fa-calendar-day me-2"></i>
                ${reserva.fecha_reserva}
            </p>

            <p class="mb-1 text-muted">
                <i class="fa-solid fa-clock me-2"></i>
                ${reserva.hora_inicio} - ${reserva.hora_fin}
            </p>

            <p class="mb-3 text-muted">
                <i class="fa-solid fa-users me-2"></i>
                ${reserva.asistentes} asistentes
            </p>

            <div class="mb-3">
                <strong>Normas:</strong>
                <ul class="mt-2">
                    ${reserva.normas.map(n => `<li>${n}</li>`).join('')}
                </ul>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-outline-danger btn-sm" onclick="eliminarReserva(${reserva.id_reservas})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>

        </div>
    `;

    contenedor.prepend(card);
   }

   
    // -----------------------------------
    // FUNCIONES AUXILIARES 
    // -----------------------------------
    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    function generarPersonas(max) {
        for (let i = 1; i <= max; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i === 1 ? "1 persona" : `${i} personas`;
            selectPersonas.appendChild(option);
        }
        selectPersonas.disabled = false;
    }

    function generarTramos(horaApertura, horaCierre, duracion) {

        const inicio = convertirHora(horaApertura);
        const fin = convertirHora(horaCierre);

        for (let h = inicio; h + duracion <= fin; h += duracion) {
            const horaInicio = formatearHora(h);
            const horaFin = formatearHora(h + duracion);

            const option = document.createElement('option');
            option.value = `${horaInicio}-${horaFin}`;
            option.textContent = `${horaInicio} - ${horaFin}`;
            selectTramo.appendChild(option);
        }

        selectTramo.disabled = false;
    }

    function convertirHora(hora) {
        const [h, m] = hora.split(':').map(Number);
        return h * 60 + m;
    }

    function formatearHora(minutos) {
        const h = Math.floor(minutos / 60).toString().padStart(2, '0');
        const m = (minutos % 60).toString().padStart(2, '0');
        return `${h}:${m}`;
    }

});