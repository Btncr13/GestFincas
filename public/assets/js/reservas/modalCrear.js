document.addEventListener("DOMContentLoaded", () => {
  // =====================================================
  // 🔧 1. REFERENCIAS (UI)
  // =====================================================
  const selectEspacio = document.getElementById("id_espacio");
  const inputFecha = document.getElementById("inputFecha");
  const selectTramo = document.getElementById("selectTramo");
  const selectPersonas = document.getElementById("selectPersonas");
  const btnCrear = document.getElementById("btnCrearReserva");
  const modalReservaEl = document.getElementById("modalReserva");
  const alertError = document.getElementById("modalErrorAlert");

  // =====================================================
  // 🧠 2. ESTADO CENTRAL (CLAVE)
  // =====================================================
  const state = {
    espacio: null,
    fecha: null,
    tramo: null,
    personas: null,
    espacioData: null,
    refreshInterval: null,
  };

  // =====================================================
  // ⚙️ 3. INIT UI
  // =====================================================
  const hoy = new Date();
  const max = new Date();
  max.setDate(hoy.getDate() + 14);

  // CORRECCIÓN: Usar fecha local para evitar desfases de zona horaria con toISOString()
  const formato = (d) => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  };

  const fechaHoyStr = formato(hoy);
  inputFecha.min = fechaHoyStr;
  inputFecha.value = fechaHoyStr; // Establecemos hoy por defecto en el UI
  inputFecha.max = formato(max);
  state.fecha = fechaHoyStr; // Sincronizamos el estado inicial

  btnCrear.disabled = true;

  // =====================================================
  // 🎧 4. EVENTOS
  // =====================================================

  selectEspacio.addEventListener("change", (e) => {
    state.espacio = e.target.value;
    limpiarErrorModal();

    resetSelect(selectTramo, "Selecciona un tramo...");
    resetSelect(selectPersonas, "Selecciona cantidad...");

    state.tramo = null;
    state.personas = null;

    if (!state.espacio) {
      actualizarBoton();
      return;
    }

    state.espacioData = espaciosDisponibles.find(
      (esp) => esp.id_espacios_comunidad == state.espacio,
    );

    if (!state.espacioData) return;

    generarTramos(
      state.espacioData.hora_apertura,
      state.espacioData.hora_cierre,
      state.espacioData.duracion_uso,
    );

    generarPersonas(state.espacioData.max_personas);

    actualizarBoton();
  });

  inputFecha.addEventListener("change", (e) => {
    state.fecha = e.target.value;
    limpiarErrorModal();

    // Al cambiar la fecha, los tramos disponibles pueden variar (especialmente si es hoy)
    // Si ya tenemos los datos del espacio, regeneramos el selector de tramos para aplicar el filtro de tiempo
    if (state.espacioData) {
      state.tramo = null; // Reset del tramo seleccionado al cambiar fecha
      generarTramos(
        state.espacioData.hora_apertura,
        state.espacioData.hora_cierre,
        state.espacioData.duracion_uso,
      );
    }

    comprobarDisponibilidad();
    actualizarBoton();
  });

  selectTramo.addEventListener("change", (e) => {
    state.tramo = e.target.value;
    limpiarErrorModal();
    comprobarDisponibilidad();
    actualizarBoton();
  });

  selectPersonas.addEventListener("change", (e) => {
    state.personas = e.target.value;
    actualizarBoton();
  });

  btnCrear.addEventListener("click", crearReserva);

  // Sincronización automática cada minuto mientras el modal está abierto
  modalReservaEl.addEventListener("shown.bs.modal", () => {
    state.refreshInterval = setInterval(() => {
      const { esHoy } = getFiltroTiempo();

      // Solo refrescamos si es "hoy" y hay un espacio seleccionado
      if (state.espacioData && esHoy) {
        const tramoSeleccionadoPrevio = state.tramo;

        generarTramos(
          state.espacioData.hora_apertura,
          state.espacioData.hora_cierre,
          state.espacioData.duracion_uso,
        );

        // Intentamos mantener la selección del usuario si el tramo sigue siendo válido
        selectTramo.value = tramoSeleccionadoPrevio;
        state.tramo = selectTramo.value || null;
        actualizarBoton();
      }
    }, 60000); // 60 segundos
  });

  modalReservaEl.addEventListener("hidden.bs.modal", () => {
    clearInterval(state.refreshInterval);
    limpiarErrorModal();
  });

  // =====================================================
  // 🧠 5. CONTROL CENTRAL BOTÓN
  // =====================================================
  function limpiarErrorModal() {
    if (alertError) {
      alertError.textContent = "";
      alertError.classList.add("d-none");
    }
  }

  function actualizarBoton() {
    const valido =
      state.espacio && state.fecha && state.tramo && state.personas;

    btnCrear.disabled = !valido;
  }

  // =====================================================
  // 🌐 6. DISPONIBILIDAD (BACKEND)
  // =====================================================
  function comprobarDisponibilidad() {
    if (!state.espacio || !state.fecha) return;

    let hora_inicio = null;
    let hora_fin = null;

    if (state.tramo) {
      [hora_inicio, hora_fin] = state.tramo.split("-");
    }

    const formData = new FormData();
    formData.append("id_espacios_comunidad", state.espacio);
    formData.append("fecha_reserva", state.fecha);

    if (hora_inicio && hora_fin) {
      formData.append("hora_inicio", hora_inicio);
      formData.append("hora_fin", hora_fin);
    }

    fetch("index.php?route=reserva/comprobarDisponibilidad", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => actualizarSelectEspacios(data));
  }

  function actualizarSelectEspacios(data) {
    Array.from(selectEspacio.options).forEach((option) => {
      const id = option.value;
      if (!id) return;

      const espacio = data.find((e) => e.id == id);
      if (!espacio) return;

      option.textContent = option.textContent.replace(" (Completo)", "");

      option.disabled = espacio.lleno;

      if (espacio.lleno) {
        option.textContent += " (Completo)";
      }
    });
  }

  // =====================================================
  // ⚙️ 7. GENERADORES
  // =====================================================

  function generarPersonas(max) {
    selectPersonas.innerHTML =
      '<option value="">Selecciona cantidad...</option>';

    for (let i = 1; i <= max; i++) {
      const option = document.createElement("option");
      option.value = i;
      option.textContent = i;
      selectPersonas.appendChild(option);
    }

    selectPersonas.disabled = false;
  }

  function generarTramos(horaApertura, horaCierre, duracion) {
    selectTramo.innerHTML = '<option value="">Selecciona un tramo...</option>';

    const inicio = convertirHora(horaApertura);
    const fin = convertirHora(horaCierre);

    // Obtenemos la lógica de tiempo centralizada
    const { esHoy, minutosLimite } = getFiltroTiempo();

    for (let h = inicio; h + duracion <= fin; h += duracion) {
      // Si es hoy, saltamos los tramos que comiencen antes del margen de antelación
      if (esHoy && h < minutosLimite) {
        continue;
      }

      const horaInicio = formatearHora(h);
      const horaFin = formatearHora(h + duracion);

      const option = document.createElement("option");
      option.value = `${horaInicio}-${horaFin}`;
      option.textContent = `${horaInicio} - ${horaFin}`;

      selectTramo.appendChild(option);
    }

    selectTramo.disabled = false;
  }

  // =====================================================
  // 🔧 8. HELPERS
  // =====================================================

  function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = false;
  }

  /**
   * Centraliza el cálculo del tiempo actual y la comparación con la fecha seleccionada.
   * @returns {Object} { esHoy: boolean, minutosLimite: number }
   */
  function getFiltroTiempo() {
    const ahora = new Date();
    return {
      // Compara la fecha del estado con la fecha de "ahora" formateada
      esHoy: state.fecha === formato(ahora),
      // Hora actual convertida a minutos + margen de cortesía (15 min)
      minutosLimite: ahora.getHours() * 60 + ahora.getMinutes() + 15,
    };
  }

  function convertirHora(hora) {
    const [h, m] = hora.split(":").map(Number);
    return h * 60 + m;
  }

  function formatearHora(min) {
    const h = String(Math.floor(min / 60)).padStart(2, "0");
    const m = String(min % 60).padStart(2, "0");
    return `${h}:${m}`;
  }

  // =====================================================
  // 📤 9. CREAR RESERVA
  // =====================================================

  function crearReserva() {
    const [hora_inicio, hora_fin] = state.tramo.split("-");

    const formData = new FormData();
    formData.append("id_espacios_comunidad", state.espacio);
    formData.append("fecha_reserva", state.fecha);
    formData.append("hora_inicio", hora_inicio);
    formData.append("hora_fin", hora_fin);
    formData.append("asistentes", state.personas);

    fetch("index.php?route=reserva/store", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          // Mostrar error específico dentro del modal
          if (alertError) {
            alertError.textContent = data.message;
            alertError.classList.remove("d-none");
          }
          btnCrear.disabled = false;
          return;
        }

        const r = data.reserva;

        if (!r) {
          console.error("No viene reserva en respuesta", data);
          return;
        }

        const html = `
<div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-success) !important;" id="reserva-${r.id_reserva}">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span style="font-size:15px; font-weight:700; color:var(--bs-dark); font-family: var(--fuente-titulos);">${r.nombre_espacio}</span>
                    <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Activa</span>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> ${r.fecha_reserva}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> ${r.hora_inicio} - ${r.hora_fin}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: ${r.asistentes}</span>
                </div>
                <div class="mt-3">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-1" style="font-size:12px; font-weight:500; color:var(--bs-primary);" onclick="toggleNormas('${r.id_reserva}')">
                        <i class="bi bi-chevron-down" id="icon-normas-${r.id_reserva}"></i> Normas de Uso
                    </button>
                    <div id="normas-${r.id_reserva}" class="d-none mt-2 ps-2" style="border-left: 2px solid rgba(34,28,53,0.2); font-size:12px; color:var(--color-texto);">
                        <ul class="list-unstyled mb-0">
                            ${r.normas && r.normas.length > 0 ?
                                r.normas.map(norma => `<li class="mb-1">${norma}</li>`).join('')
                                : '<li>No hay normas definidas.</li>'}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm" onclick="eliminarReserva(${r.id_reserva})">
                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
`;

        document
          .getElementById("contenedorReservas")
          .insertAdjacentHTML("afterbegin", html);

        // Eliminar el mensaje de "No hay reservas" si existe
        const mensajeSinReservas =
          document.getElementById("mensajeSinReservas");
        if (mensajeSinReservas) mensajeSinReservas.remove();

        bootstrap.Modal.getInstance(
          document.getElementById("modalReserva"),
        ).hide();

        showToast("Reserva confirmada con éxito.");
      })
      .catch((err) => console.error("Error en la petición:", err));
  }
});

// ------------------------- FUERA DEL DOMCONTENTLOADED

function cargarReservas() {
  fetch("index.php?route=reserva/getMisReservasAjax")
    .then((res) => res.json())
    .then((data) => {
      renderReservas(data.reservas);
    });
}

// ----------------------------------------------------------

function renderReservas(reservas) {
  const contenedor = document.getElementById("contenedorReservas");

  contenedor.innerHTML = "";

  // Si no hay reservas, inyectamos el mensaje de "Empty State"
  if (reservas.length === 0) {
    contenedor.innerHTML = `
      <div id="mensajeSinReservas" class="text-center py-5 w-100">
          <i class="fa-regular fa-calendar-xmark fs-1 text-muted mb-3"></i>
          <h5 class="fw-bold text-muted">No tienes reservas activas</h5>
          <p class="text-muted small">Haz clic en "Nueva Reserva" para empezar.</p>
      </div>`;
    return;
  }

  reservas.forEach((r) => {
    const html = `
<div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-success) !important;" id="reserva-${r.id_reserva}">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span style="font-size:15px; font-weight:700; color:var(--bs-dark); font-family: var(--fuente-titulos);">${r.nombre_espacio}</span>
                    <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">Activa</span>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> ${r.fecha_reserva}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> ${r.hora_inicio} - ${r.hora_fin}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: ${r.asistentes}</span>
                </div>
                <div class="mt-3">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-1" style="font-size:12px; font-weight:500; color:var(--bs-primary);" onclick="toggleNormas('${r.id_reserva}')">
                        <i class="bi bi-chevron-down" id="icon-normas-${r.id_reserva}"></i> Normas de Uso
                    </button>
                    <div id="normas-${r.id_reserva}" class="d-none mt-2 ps-2" style="border-left: 2px solid rgba(34,28,53,0.2); font-size:12px; color:var(--color-texto);">
                        <ul class="list-unstyled mb-0">
                            ${r.normas && r.normas.length > 0 ?
                                r.normas.map(norma => `<li class="mb-1">${norma}</li>`).join('')
                                : '<li>No hay normas definidas.</li>'}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm" onclick="eliminarReserva(${r.id_reserva})">
                    <i class="fa-solid fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
`;

    contenedor.insertAdjacentHTML("beforeend", html);
  });
}

// ----------------------------------------------------------------------------------

function eliminarReserva(idReserva) {
    // 1. Confirmación de seguridad (UX básica)
    if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
        return;
    }

    // 2. Preparamos los datos para el POST
    const formData = new FormData();
    formData.append('id_reserva', idReserva);
    // Idealmente aquí también añadiríamos: formData.append('csrf_token', tuTokenGlobal);

    // 3. Petición AJAX al controlador (ReservaController::destroy)
    fetch('index.php?route=reserva/destroy', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        // En lugar de hacer throw inmediato, parseamos la respuesta
        const data = await response.json().catch(() => null); 
        
        if (!response.ok) {
            // Si hay un error HTTP, lanzamos el mensaje del backend o uno por defecto
            throw new Error(data?.message || `Error del servidor HTTP ${response.status}`);
        }
        
        return data; // Si todo va bien (200 OK), pasamos la data al siguiente then
    })
    .then(data => {
        if (data && data.success) {
            const cardReserva = document.getElementById(`reserva-${idReserva}`);
            if (cardReserva) {
                cardReserva.style.transition = "opacity 0.3s ease";
                cardReserva.style.opacity = "0";
                setTimeout(() => { cardReserva.remove(); }, 300); 
            }
        } else {
            // Uso de Optional Chaining (?.) para evitar el crasheo si data es null
            alert(data?.message || 'No se pudo cancelar la reserva.');
        }
    })
    .catch(error => {
        console.error('Detalle del error:', error);
        // Ahora el alert mostrará el motivo real (ej: "No tienes permisos")
        alert(`Fallo en la operación: ${error.message}`);
    });
  }
// =====================================================
// 🍞 10. SISTEMA DE NOTIFICACIONES (TOAST)
// =====================================================
const toastEl = document.getElementById("liveToast");
const toastBody = document.getElementById("toastMessage");
const toastInstance = toastEl ? new bootstrap.Toast(toastEl) : null;

function showToast(message, type = "success") {
  if (!toastInstance) return;
  toastBody.textContent = message;
  toastEl.classList.remove("bg-success", "bg-danger", "text-white");
  const bgClass = type === "success" ? "bg-success" : "bg-danger";
  toastEl.classList.add(bgClass, "text-white");
  toastInstance.show();
}

// Lógica para alternar la visibilidad de las normas (estilo reuniones)
window.toggleNormas = (id) => {
  const el = document.getElementById(`normas-${id}`);
  const icon = document.getElementById(`icon-normas-${id}`);
  if (!el || !icon) return;
  
  if (el.classList.contains('d-none')) {
    el.classList.remove('d-none');
    icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
  } else {
    el.classList.add('d-none');
    icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
  }
};
