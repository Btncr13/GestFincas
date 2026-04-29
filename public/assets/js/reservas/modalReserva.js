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
  modalReservaEl.addEventListener("show.bs.modal", (event) => {
    const button = event.relatedTarget; // Botón que disparó el modal
    const idEspacio = button ? button.getAttribute('data-id-espacio') : null;
    
    if (idEspacio) {
      selectEspacio.value = idEspacio;
      // Disparar el evento change manualmente para que la lógica de state se actualice
      selectEspacio.dispatchEvent(new Event('change'));
    }
  });

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

        const hoyStr = new Date().toISOString().split('T')[0];
        const isHoy = r.fecha_reserva === hoyStr;
        const hInicio = r.hora_inicio.substring(0, 5);
        const hFin = r.hora_fin.substring(0, 5);

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
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> ${formatoFechaJS(r.fecha_reserva)}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> ${hInicio} - ${hFin}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: ${r.asistentes}</span>
                </div>
                <div class="mt-3">
                    <details style="font-size:13px; color:var(--color-texto);">
                        <summary class="fw-semibold cursor-pointer text-success">
                            <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                        </summary>
                        <ul class="list-unstyled ps-3 pt-2 mb-0">
                            ${r.normas && r.normas.length > 0
                                ? r.normas.map(norma => `<li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i>${norma}</li>`).join('')
                                : '<li>No hay normas definidas.</li>'}
                        </ul>
                    </details>
                </div>
            </div>
            <div class="ms-auto text-end">
                ${isHoy ? `
                    <button class="btn btn-sm btn-success btn-confirmar-reserva w-100 mb-2 shadow-sm fw-semibold" data-fecha="${r.fecha_reserva}">
                        <i class="bi bi-check-circle me-1"></i> Confirmar Asistencia
                    </button>
                ` : ''}
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm w-100 btn-eliminar-reserva" data-fecha="${r.fecha_reserva}" onclick="eliminarReserva(${r.id_reserva})">
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

// =====================================================
// 📋 11. GESTIÓN DE RESERVAS PARA PRESIDENTE (AJAX)
// =====================================================

window.cargarReservasPresi = function () {
  fetch("index.php?route=reserva/getTodasLasReservasComunidadAjax")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) renderReservasPresi(data.reservas);
    });
};

function renderReservasPresi(reservas) {
  const containerActivas = document.getElementById("lista-activas");
  const containerInactivas = document.getElementById("lista-inactivas");
  if (!containerActivas || !containerInactivas) return;

  // Filtro de 14 días para inactivas (mismo que en PHP)
  const limiteInactivas = new Date();
  limiteInactivas.setDate(limiteInactivas.getDate() - 14);
  limiteInactivas.setHours(0, 0, 0, 0);

  let htmlActivas = "";
  let htmlInactivas = "";

  reservas.forEach((res) => {
    const isActiva = res.estado_reserva === "activo";
    const fechaRes = new Date(res.fecha);

    if (!isActiva && fechaRes < limiteInactivas) return;

    const color = isActiva ? "success" : "secondary";
    const borderLeft = isActiva ? "var(--bs-success)" : "#d1d5db";
    const bgCard = isActiva
      ? ""
      : "background-color: var(--bs-light); opacity: 0.75;";
    const badgeText = isActiva ? "Activa" : "Inactiva";

    const cardHTML = `
            <div class="card shadow-sm border-0 module-card mb-3" style="border-left: 4px solid ${borderLeft} !important; ${bgCard}" id="reserva-${res.id_reserva}">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span style="font-size:15px; font-weight:700; color:var(--bs-dark); font-family: var(--fuente-titulos);">${res.nombre_espacio}</span>
                                <span class="badge bg-${color} px-2 py-1 rounded-2 shadow-sm text-white" style="font-size:11px;">${badgeText}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-house text-${color}"></i> <span class="fw-bold">${res.nombre_vivienda || ""}</span></span>
                                <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-${color}"></i> ${formatoFechaJS(res.fecha)}</span>
                                <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-${color}"></i> <span class="badge bg-light text-dark border">${res.hora_inicio.substring(0, 5)} - ${res.hora_fin.substring(0, 5)}</span></span>
                                <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-${color}"></i> Asistentes: ${res.asistentes}</span>
                            </div>
                            ${
                              !isActiva &&
                              res.espacio_bloqueado == 1 &&
                              res.motivo_espacio
                                ? `
                                <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm mt-3 mb-0 py-2 px-3" style="font-size:12px;">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    <strong>Cancelada por bloqueo:</strong> ${res.motivo_espacio}
                                </div>`
                                : ""
                            }
                            <div class="mt-3">
                                <details style="font-size:13px; color:var(--color-texto);">
                                    <summary class="fw-semibold cursor-pointer text-${color}">
                                        <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                                    </summary>
                                    <ul class="list-unstyled ps-3 pt-2 mb-0">
                                        ${
                                          res.normas && res.normas.length > 0
                                            ? res.normas
                                                .map(
                                                  (n) =>
                                                    `<li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-${color}"></i>${n}</li>`,
                                                )
                                                .join("")
                                            : "<li>No hay normas definidas.</li>"
                                        }
                                    </ul>
                                </details>
                            </div>
                        </div>
                        ${
                          isActiva
                            ? `
                        <div class="ms-auto text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm px-3" onclick="if(confirm('¿Seguro que deseas eliminar esta reserva?')) eliminarReservaGen(${res.id_reserva})">
                                <i class="fa-solid fa-trash me-2"></i>Eliminar
                            </button>
                        </div>`
                            : ""
                        }
                    </div>
                </div>
            </div>`;

    if (isActiva) htmlActivas += cardHTML;
    else htmlInactivas += cardHTML;
  });

  containerActivas.innerHTML =
    htmlActivas ||
    '<div class="text-center py-5 w-100"><i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i><h5 class="text-muted">No hay reservas activas</h5></div>';
  containerInactivas.innerHTML =
    htmlInactivas ||
    '<div class="text-center py-5 w-100"><i class="fa-solid fa-clipboard-check fs-1 text-muted mb-3"></i><h5 class="text-muted">No hay reservas inactivas recientes</h5></div>';
}

function formatoFechaJS(fechaStr) {
  const d = new Date(fechaStr);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
}

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
    const hoyStr = new Date().toISOString().split('T')[0];
    const isHoy = r.fecha_reserva === hoyStr;
    const hInicio = r.hora_inicio.substring(0, 5);
    const hFin = r.hora_fin.substring(0, 5);

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
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-calendar text-success"></i> ${formatoFechaJS(r.fecha_reserva)}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock text-success"></i> ${hInicio} - ${hFin}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users text-success"></i> Asistentes: ${r.asistentes}</span>
                </div>
                <div class="mt-3">
                    <details style="font-size:13px; color:var(--color-texto);">
                        <summary class="fw-semibold cursor-pointer text-success">
                            <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                        </summary>
                        <ul class="list-unstyled ps-3 pt-2 mb-0">
                            ${r.normas && r.normas.length > 0
                                ? r.normas.map(norma => `<li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i>${norma}</li>`).join('')
                                : '<li>No hay normas definidas.</li>'}
                        </ul>
                    </details>
                </div>
            </div>
            <div class="ms-auto text-end">
                ${isHoy ? `
                    <button class="btn btn-sm btn-success btn-confirmar-reserva w-100 mb-2 shadow-sm fw-semibold" data-fecha="${r.fecha_reserva}">
                        <i class="bi bi-check-circle me-1"></i> Confirmar Asistencia
                    </button>
                ` : ''}
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold shadow-sm w-100 btn-eliminar-reserva" data-fecha="${r.fecha_reserva}" onclick="eliminarReserva(${r.id_reserva})">
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
  if (!confirm("¿Estás seguro de que deseas cancelar esta reserva?")) {
    return;
  }

  // 2. Preparamos los datos para el POST
  const formData = new FormData();
  formData.append("id_reserva", idReserva);
  // Idealmente aquí también añadiríamos: formData.append('csrf_token', tuTokenGlobal);

  // 3. Petición AJAX al controlador (ReservaController::destroy)
  fetch("index.php?route=reserva/destroy", {
    method: "POST",
    body: formData,
  })
    .then(async (response) => {
      // En lugar de hacer throw inmediato, parseamos la respuesta
      const data = await response.json().catch(() => null);

      if (!response.ok) {
        // Si hay un error HTTP, lanzamos el mensaje del backend o uno por defecto
        throw new Error(
          data?.message || `Error del servidor HTTP ${response.status}`,
        );
      }

      return data; // Si todo va bien (200 OK), pasamos la data al siguiente then
    })
    .then((data) => {
      if (data && data.success) {
        const cardReserva = document.getElementById(`reserva-${idReserva}`);
        if (cardReserva) {
          cardReserva.style.transition = "opacity 0.3s ease";
          cardReserva.style.opacity = "0";
          setTimeout(() => {
            cardReserva.remove();
          }, 300);
          
          // Limpiar claves de localStorage asociadas a alertas
          const fechaReserva = cardReserva.getAttribute('data-fecha') || new Date().toISOString().split('T')[0];
          limpiarKeysReserva(fechaReserva);
        }
      } else {
        // Uso de Optional Chaining (?.) para evitar el crasheo si data es null
        alert(data?.message || "No se pudo cancelar la reserva.");
      }
    })
    .catch((error) => {
      console.error("Detalle del error:", error);
      // Ahora el alert mostrará el motivo real (ej: "No tienes permisos")
      alert(`Fallo en la operación: ${error.message}`);
    });
}

function limpiarKeysReserva(fecha) {
    localStorage.removeItem('reserva_confirmada_' + fecha);
    localStorage.removeItem('reserva_toast_hoy_' + fecha);
    
    // También intentamos limpiar la de mañana por si acaso
    localStorage.removeItem('reserva_toast_manana_' + fecha);
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

  if (el.classList.contains("d-none")) {
    el.classList.remove("d-none");
    icon.classList.replace("bi-chevron-down", "bi-chevron-up");
  } else {
    el.classList.add("d-none");
    icon.classList.replace("bi-chevron-up", "bi-chevron-down");
  }
};
