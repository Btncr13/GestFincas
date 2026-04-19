document.addEventListener("DOMContentLoaded", () => {
  // =====================================================
  // 🔧 1. REFERENCIAS (UI)
  // =====================================================
  const selectEspacio = document.getElementById("id_espacio");
  const inputFecha = document.getElementById("inputFecha");
  const selectTramo = document.getElementById("selectTramo");
  const selectPersonas = document.getElementById("selectPersonas");
  const btnCrear = document.getElementById("btnCrearReserva");

  // =====================================================
  // 🧠 2. ESTADO CENTRAL (CLAVE)
  // =====================================================
  const state = {
    espacio: null,
    fecha: null,
    tramo: null,
    personas: null,
    espacioData: null,
  };

  // =====================================================
  // ⚙️ 3. INIT UI
  // =====================================================
  const hoy = new Date();
  const max = new Date();
  max.setDate(hoy.getDate() + 14);

  const formato = (d) => d.toISOString().split("T")[0];

  inputFecha.min = formato(hoy);
  inputFecha.max = formato(max);

  btnCrear.disabled = true;

  // =====================================================
  // 🎧 4. EVENTOS
  // =====================================================

  selectEspacio.addEventListener("change", (e) => {
    state.espacio = e.target.value;

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
    comprobarDisponibilidad();
    actualizarBoton();
  });

  selectTramo.addEventListener("change", (e) => {
    state.tramo = e.target.value;
    comprobarDisponibilidad();
    actualizarBoton();
  });

  selectPersonas.addEventListener("change", (e) => {
    state.personas = e.target.value;
    actualizarBoton();
  });

  btnCrear.addEventListener("click", crearReserva);

  // =====================================================
  // 🧠 5. CONTROL CENTRAL BOTÓN
  // =====================================================
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

    // Obtener hora actual en minutos si la fecha es hoy
    const hoyStr = formato(new Date());
    const esHoy = state.fecha === hoyStr;
    const ahora = new Date();
    // Calculamos el límite añadiendo 15 minutos de antelación mínima
    const minutosLimite = ahora.getHours() * 60 + ahora.getMinutes() + 15;

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
          alert(data.message);
          return;
        }

        const r = data.reserva;

        if (!r) {
          console.error("No viene reserva en respuesta", data);
          return;
        }

        const html = `
      <div class="col" id="reserva-${r.id_reservas}">
        <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-success">
          <div class="card-body p-4 d-flex flex-column">

            <div class="d-flex justify-content-between mb-3">
              <div class="text-muted small">
                <div>${r.fecha_reserva}</div>
                <div>${r.hora_inicio} - ${r.hora_fin}</div>
              </div>

              <span class="badge bg-success">Activa</span>
            </div>

            <h3 class="fs-5 fw-bold text-dark mb-2">
              ${r.nombre_espacio}
            </h3>

            <div class="small text-muted mb-3">
              <i class="fa-solid fa-users me-2"></i>
              Asistentes: ${r.asistentes}
            </div>

            <div class="mt-auto d-flex justify-content-end border-top pt-3">
              <button class="btn btn-sm btn-outline-danger"
                onclick="eliminarReserva(${r.id_reservas})">
                Cancelar
              </button>
            </div>

          </div>
        </div>
      </div>
    `;

        document
          .getElementById("contenedorReservas")
          .insertAdjacentHTML("afterbegin", html);

        bootstrap.Modal.getInstance(
          document.getElementById("modalReserva"),
        ).hide();
      })
      .catch((err) => console.error("Error en la petición:", err));
  }
});

// ------------------------- FUERA DEL DOMCONTENTLOADED

function cargarReservas() {
  fetch("index.php?route=reserva/getMisReservasAjax")
    .then((res) => res.json())
    .then((data) => {
      const contenedor = document.getElementById("contenedorReservas");
      contenedor.innerHTML = ""; // 👈 IMPORTANTE

      data.reservas.forEach(renderReservas);
    });
}

// ----------------------------------------------------------

function renderReservas(reservas) {
  const contenedor = document.getElementById("contenedorReservas");

  contenedor.innerHTML = "";

  reservas.forEach((r) => {
    const html = `
      <div class="col" id="reserva-${r.id_reserva}">
        <div class="card shadow-sm h-100 border-0 border-start border-4 border-success">
          <div class="card-body p-4 d-flex flex-column">

            <div class="text-muted small mb-2">
              <div>${r.fecha_reserva}</div>
              <div>${r.hora_inicio} - ${r.hora_fin}</div>
            </div>

            <h3 class="fs-5 fw-bold">
              ${r.nombre_espacio}
            </h3>

            <div class="small text-muted mb-3">
              Asistentes: ${r.asistentes}
            </div>

            <button class="btn btn-sm btn-outline-danger"
              onclick="eliminarReserva(${r.id_reserva})">
              Cancelar
            </button>

          </div>
        </div>
      </div>
    `;

    contenedor.insertAdjacentHTML("beforeend", html);
  });
}

// ----------------------------------------------------------------------------------

function eliminarReserva(idReserva) {
  if (!confirm("¿Seguro que deseas cancelar esta reserva?")) return;

  const formData = new FormData();
  formData.append("id_reserva", idReserva);

  fetch("index.php?route=reserva/destroy", {
    method: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        alert(data.message);
        return;
      }

      // 👇 REGENERA TODA LA LISTA DESDE BD
      cargarReservas();
    })
    .catch((err) => {
      console.error("Error eliminando reserva:", err);
    });
}
