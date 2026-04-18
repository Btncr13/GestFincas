// ELIMINAR RESERVA
function eliminarReserva(idReserva) {
  if (!confirm("¿Seguro que deseas cancelar esta reserva?")) return;

  const card = document.getElementById(`reserva-${idReserva}`);
  if (card) card.remove();

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
        alert("Error al cancelar: " + data.message);
        location.reload();
      }
    })
    .catch(() => location.reload());
}

document.addEventListener("DOMContentLoaded", () => {
  const selectEspacio = document.getElementById("id_espacio");
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
  // RESETEAR MODAL AL ABRIRLO
  // -----------------------------------
  document.getElementById("modalReserva").addEventListener("show.bs.modal", () => {
    resetSelect(selectTramo, "Selecciona un tramo...");
    resetSelect(selectPersonas, "Selecciona cantidad...");
    selectEspacio.value = "";
    inputFecha.value = "";
    btnCrear.disabled = true;
  });

  // -----------------------------------
  // EVENTO: CAMBIO DE ESPACIO
  // -----------------------------------
  selectEspacio.addEventListener("change", (e) => {
    const idEspacio = e.target.value;

    [inputFecha, selectTramo, selectPersonas].forEach(el => {
      el.addEventListener("change", comprobarDisponibilidad);
    });

    resetSelect(selectPersonas, "Selecciona cantidad...");
    resetSelect(selectTramo, "Selecciona un tramo...");
    btnCrear.disabled = true;

    if (!idEspacio) return;

    const espacio = espaciosDisponibles.find(
      (esp) => esp.id_espacios_comunidad == idEspacio
    );

    if (!espacio) return;

    generarPersonas(espacio.max_personas);
    generarTramos(
      espacio.hora_apertura,
      espacio.hora_cierre,
      espacio.duracion_uso
    );

    btnCrear.disabled = false;
  });

  // -----------------------------------
  // EVENTO: CREAR RESERVA
  // -----------------------------------
  btnCrear.addEventListener("click", crearReserva);

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

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalReserva")
        );
        modal.hide();
      })
      .catch((err) => console.error("Error en la petición:", err));
  }

  function comprobarDisponibilidad() {
  const fecha = inputFecha.value;
  const tramo = selectTramo.value;

  if (!fecha || !tramo) return;

  const [hora_inicio, hora_fin] = tramo.split("-");

  const formData = new FormData();
  formData.append("fecha", fecha);
  formData.append("hora_inicio", hora_inicio);
  formData.append("hora_fin", hora_fin);

  fetch("index.php?route=reserva/comprobarDisponibilidad", {
    method: "POST",
    body: formData,
  })
    .then(res => res.json())
    .then(data => {
      actualizarSelectEspacios(data);
    })
    .catch(err => console.error("Error comprobando disponibilidad:", err));
}


function actualizarSelectEspacios(disponibilidad) {

  Array.from(selectEspacio.options).forEach(option => {
    const id = option.value;

    if (!id) return;

    const espacio = disponibilidad.find(e => e.id == id);

    if (!espacio) return;

    // Limpiar texto previo
    option.textContent = option.textContent.replace(" (Completo)", "");

    if (espacio.lleno) {
      option.disabled = true;
      option.textContent += " (Completo)";
    } else {
      option.disabled = false;
    }
  });
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
      option.textContent = i === 1 ? "1 persona" : `${i}`;
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
    const h = Math.floor(minutos / 60).toString().padStart(2, "0");
    const m = (minutos % 60).toString().padStart(2, "0");
    return `${h}:${m}`;
  }
});