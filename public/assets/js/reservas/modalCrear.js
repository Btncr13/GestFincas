// ELIMINAR RESERVA

function eliminarReserva(idReserva) {
  if (!confirm("¿Seguro que deseas cancelar esta reserva?")) {
    return;
  }

  // --- ELIMINACIÓN OPTIMISTA (DE INMEDIATO) ---
  // Buscamos la tarjeta y la eliminamos de la vista antes de esperar al servidor
  const card = document.getElementById(`reserva-${idReserva}`);
  if (card) {
    card.remove();
  }

  // Si tras borrarla ya no quedan tarjetas, mostramos el mensaje de "No hay reservas"
  const contenedor = document.getElementById("contenedorReservas");
  if (contenedor && contenedor.querySelectorAll(".card").length === 0) {
    const mensaje = document.getElementById("mensajeSinReservas");
    if (mensaje) mensaje.style.display = "block";
  }

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
        // Si el servidor dice que NO se pudo borrar, avisamos y refrescamos
        // para que la reserva "reaparezca" y la vista sea veraz.
        alert("Error al cancelar: " + data.message);
        location.reload();
      }
    })
    .catch((err) => {
      console.error("Error en la red:", err);
      // En caso de error de conexión, también refrescamos para sincronizar
      location.reload();
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const selectEspacio = document.getElementById("selectEspacio");
  const inputFecha = document.getElementById("inputFecha");
  const selectTramo = document.getElementById("selectTramo");
  const selectPersonas = document.getElementById("selectPersonas");
  const btnCrear = document.getElementById("btnCrearReserva");

  // -----------------------------------
  // LIMITAR FECHA A HOY → HOY + 14 DÍAS
  // -----------------------------------
  const hoy = new Date();
  const max = new Date();
  max.setDate(hoy.getDate() + 14);

  const formato = (d) => d.toISOString().split("T")[0];

  inputFecha.min = formato(hoy);
  inputFecha.max = formato(max);

  // -----------------------------------
  // EVENTO: CAMBIO DE ESPACIO
  // -----------------------------------
  selectEspacio.addEventListener("change", (e) => {
    const idEspacio = e.target.value;

    resetSelect(selectPersonas, "Selecciona cantidad...");
    resetSelect(selectTramo, "Selecciona un tramo...");
    btnCrear.disabled = true;

    if (!idEspacio) return;

    const espacio = espaciosDisponibles.find(
      (esp) => esp.id_espacios_comunidad == idEspacio,
    );
    if (!espacio) return;

    generarPersonas(espacio.max_personas);
    generarTramos(
      espacio.hora_apertura,
      espacio.hora_cierre,
      espacio.duracion_uso,
    );

    btnCrear.disabled = false;
  });

  // -----------------------------------
  // EVENTO: CREAR RESERVA
  // -----------------------------------
  btnCrear.addEventListener("click", crearReserva);

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

    const [hora_inicio, hora_fin] = tramo.split("-");

    const formData = new FormData();
    formData.append("id_espacio", idEspacio);
    formData.append("fecha", fecha);
    formData.append("hora_inicio", hora_inicio);
    formData.append("hora_fin", hora_fin);
    formData.append("asistentes", asistentes);

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

        insertarReservaEnVista(data.reserva);
        const mensajeSinReservas =
          document.getElementById("mensajeSinReservas");
        if (mensajeSinReservas) mensajeSinReservas.style.display = "none";

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalReserva"),
        );
        modal.hide();
      })
      .catch((err) => console.error("Error en la petición:", err));
  }

  // -----------------------------------
  // FUNCIÓN: PINTAR TARJETA EN LA VISTA
  // -----------------------------------
  function insertarReservaEnVista(reserva) {
    const contenedor = document.getElementById("contenedorReservas");

    // Creamos el contenedor 'col' para que la rejilla no se rompa
    const col = document.createElement("div");
    col.className = "col";
    col.id = `reserva-${reserva.id_reservas}`; 

    col.innerHTML = `
        <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-success">
            <div class="card-body p-4 d-flex flex-column">

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="text-muted small">
                        <div class="mb-1">
                            <i class="fa-regular fa-calendar me-2 text-success"></i>
                            ${reserva.fecha_reserva}
                        </div>
                        <div>
                            <i class="fa-regular fa-clock me-2 text-success"></i>
                            ${reserva.hora_inicio} - ${reserva.hora_fin}
                        </div>
                    </div>
                    <span class="badge bg-success px-2 py-1 rounded-2 shadow-sm text-white fw-bold">
                        Activa
                    </span>
                </div>

                <h3 class="fs-5 fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);">
                    ${reserva.espacio}
                </h3>

                <div class="mb-3 small text-muted">
                    <i class="fa-solid fa-users me-2"></i>
                    Asistentes: ${reserva.asistentes}
                </div>

                <!-- Aquí insertamos las normas que vienen del JSON -->
                <div class="mb-3 pt-2 border-top">
                    <small class="text-dark fw-bold d-block mb-1">Normas del espacio:</small>
                    <ul class="text-muted small mb-0 ps-3">
                        ${reserva.normas.map(n => `<li>${n.descripcion}</li>`).join('')}
                    </ul>
                </div>

                <div class="mt-auto d-flex justify-content-end border-top pt-3">
                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" onclick="eliminarReserva(${reserva.id_reservas})">
                        <i class="fa-solid fa-trash me-2"></i>Cancelar
                    </button>
                </div>

            </div>
        </div>
    `;
    contenedor.prepend(col);
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
      const option = document.createElement("option");
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

      const option = document.createElement("option");
      option.value = `${horaInicio}-${horaFin}`;
      option.textContent = `${horaInicio} - ${horaFin}`;
      selectTramo.appendChild(option);
    }

    selectTramo.disabled = false;
  }

  function convertirHora(hora) {
    const [h, m] = hora.split(":").map(Number);
    return h * 60 + m;
  }

  function formatearHora(minutos) {
    const h = Math.floor(minutos / 60)
      .toString()
      .padStart(2, "0");
    const m = (minutos % 60).toString().padStart(2, "0");
    return `${h}:${m}`;
  }
});
