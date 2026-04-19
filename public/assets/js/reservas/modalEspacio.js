/**
 * Gestión de Modal para Creación de Espacios Comunitarios
 * Archivo: public/assets/js/reservas/modalEspacio.js
 */

document.addEventListener("DOMContentLoaded", () => {
  // --- 1. ESTADO CENTRALIZADO ---
  const state = {
    form: {
      nombre_espacio: "",
      aforo: 0,
      max_personas: 0,
      hora_apertura: "",
      hora_cierre: "",
      duracion_uso: 30,
      bloqueado: 0,
      motivo: "",
    },
    isValid: false,
  };

  // --- 2. SELECTORES ---
  const modalEl = document.getElementById("modalCrearEspacio");
  const formEl = document.getElementById("formCrearEspacio");
  const btnGuardar = document.getElementById("btnGuardarEspacio");
  const contenedorEspacios = document.querySelector("#espacios .row"); // Ajusta  HTML

  // Si la modal no existe en el DOM, detenemos la ejecución para evitar errores
  if (!modalEl) return;

  const bootstrapModal = new bootstrap.Modal(modalEl);

  // --- 3. INICIALIZACIÓN Y RESET ---
  modalEl.addEventListener("show.bs.modal", () => {
    resetForm();
  });

  const resetForm = () => {
    formEl.reset();
    state.form = {
      nombre_espacio: "",
      aforo: 0,
      max_personas: 0,
      hora_apertura: "",
      hora_cierre: "",
      duracion_uso: 30,
      bloqueado: 0,
      motivo: "",
    };
    toggleMotivoField(false);
    validateForm();
  };

  // --- 4. ESCUCHA DE CAMBIOS (Update State) ---
  formEl.addEventListener("input", (e) => {
    const { name, value, type, checked } = e.target;

    // Actualizar el estado
    if (type === "checkbox") {
      state.form[name] = checked ? 1 : 0;
      if (name === "bloqueado") toggleMotivoField(checked);
    } else {
      state.form[name] = value;
      // Como 'bloqueado' en el modal de creación es un <select>,
      // debemos disparar la visibilidad del motivo aquí también.
      if (name === "bloqueado") toggleMotivoField(value == "1");
    }

    validateForm();
  });

  // --- 5. VALIDACIONES LÓGICAS ---
  const toggleMotivoField = (show) => {
    const motivoGroup = document.getElementById("grupoMotivo");
    if (motivoGroup) motivoGroup.style.display = show ? "block" : "none";
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
    const horarioValido =
      f.hora_apertura !== "" &&
      f.hora_cierre !== "" &&
      f.hora_cierre > f.hora_apertura;

    // Validación de bloqueo
    const motivoValido = f.bloqueado === 1 ? f.motivo.trim().length > 3 : true;

    state.isValid =
      hasNombre &&
      aforoValido &&
      asistentesValidos &&
      coherenciaAforo &&
      horarioValido &&
      duracionValida &&
      motivoValido;

    btnGuardar.disabled = !state.isValid;

    const inputMax = document.getElementById("max_personas");
    if (inputMax) {
      // Si max_personas es mayor al aforo, añade la clase 'is-invalid' de Bootstrap
      if (!coherenciaAforo && f.max_personas > 0) {
        inputMax.classList.add("is-invalid");
      } else {
        inputMax.classList.remove("is-invalid");
      }
    }
  };

  // --- 6. ENVÍO DE DATOS (FETCH) ---
  // --- 6. ENVÍO DE DATOS (FETCH) ---
  btnGuardar.addEventListener("click", async () => {
    if (!state.isValid) return;

    btnGuardar.innerHTML =
      '<span class="spinner-border spinner-border-sm"></span> Guardando...';
    btnGuardar.disabled = true;

    const formData = new FormData();
    // IMPORTANTE: Los nombres deben coincidir con lo que recibe el controlador en EspacioController.php
    formData.append("nombre_espacio", state.form.nombre_espacio);
    formData.append("aforo", state.form.aforo);
    formData.append("max_personas", state.form.max_personas);
    formData.append("hora_apertura", state.form.hora_apertura);
    formData.append("hora_cierre", state.form.hora_cierre);
    formData.append("duracion_uso", state.form.duracion_uso);
    formData.append("bloqueado", state.form.bloqueado);
    formData.append("motivo", state.form.motivo);

    // CORRECCIÓN: Usamos el ID único del textarea definido en el modal
    const normasInput = document.getElementById("normas_espacio");
    formData.append("normas", normasInput ? normasInput.value : "");

    try {
      // La ruta 'index.php?route=espacio/store' es correcta según tu router.php
      const response = await fetch("index.php?route=espacio/store", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.status === "success") {
        // Usamos el objeto 'espacio' que devuelve tu controlador tras el insert
        insertNewCard(result.espacio);
        bootstrapModal.hide();
        alert("Espacio creado correctamente");
      } else {
        throw new Error(result.message || "Error en el servidor");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("No se pudo crear el espacio: " + error.message);
    } finally {
      btnGuardar.innerHTML = "Crear Espacio";
      btnGuardar.disabled = false;
    }
  });

  // --- 7. ACTUALIZACIÓN DINÁMICA DE LA UI ---
  const insertNewCard = (data) => {
    if (!contenedorEspacios) return;

    // Definimos el color y el badge según el estado de bloqueo
    const isBloqueado = data.bloqueado == 1;
    const colorClase = isBloqueado ? "border-danger" : "border-primary";
    const badgeHTML = isBloqueado
      ? '<span class="badge bg-danger">Bloqueado</span>'
      : '<span class="badge bg-primary">Operativo</span>';

    const cardHTML = `
<div class="col-md-4 mb-3" id="espacio-${data.id_espacios_comunidad}">
  <div class="card shadow-sm module-card h-100 border-0 border-start border-4 ${colorClase}">
    <div class="card-body p-4 d-flex flex-column">

      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="text-muted small">
          <div>${data.hora_apertura.substring(0, 5)} - ${data.hora_cierre.substring(0, 5)}</div>
        </div>
        ${badgeHTML}
      </div>

      <h3 class="fs-5 fw-bold" style="font-family: var(--fuente-titulos);">
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

        <button class="btn btn-sm ${isBloqueado ? "btn-outline-success" : "btn-outline-warning"} flex-fill"
          onclick="toggleEstadoEspacio(${data.id_espacios_comunidad}, ${isBloqueado ? 0 : 1})">
          ${isBloqueado ? "Activar" : "Bloquear"}
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
    const emptyMsg = document.getElementById("mensaje-vacio-espacios");
    if (emptyMsg) emptyMsg.remove();

    // Insertamos la card al principio del contenedor
    contenedorEspacios.insertAdjacentHTML("afterbegin", cardHTML);
  };

  // --- 8. FUNCIONALIDADES DE ACCIÓN (EDITAR, BLOQUEAR, ELIMINAR) ---

  // Inicializamos las instancias de los modales una sola vez para evitar duplicados
  const modalEditEl = document.getElementById("modalEditarEspacio");
  const bootstrapModalEditar = modalEditEl
    ? new bootstrap.Modal(modalEditEl)
    : null;
  const modalBloqueoEl = document.getElementById("modalBloqueo");
  const bootstrapModalBloqueo = modalBloqueoEl
    ? new bootstrap.Modal(modalBloqueoEl)
    : null;

  window.abrirModalEditar = (espacio) => {
    document.getElementById("edit_id").value = espacio.id_espacios_comunidad;
    document.getElementById("edit_nombre").value = espacio.nombre_espacio;
    document.getElementById("edit_aforo").value = espacio.aforo;
    document.getElementById("edit_max").value = espacio.max_personas;
    document.getElementById("edit_apertura").value = espacio.hora_apertura;
    document.getElementById("edit_cierre").value = espacio.hora_cierre;
    document.getElementById("edit_duracion").value = espacio.duracion_uso;
    bootstrapModalEditar?.show();
  };

  document
    .getElementById("formEditarEspacio")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      const res = await fetch("index.php?route=espacio/update", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      if (data.success) {
        actualizarCardUI(data.espacio);
        bootstrapModalEditar?.hide();
      }
    });

  let idParaBloquear = null;
  window.toggleEstadoEspacio = (id, nuevoEstado) => {
    if (nuevoEstado === 1) {
      idParaBloquear = id;
      document.getElementById("motivoBloqueo").value = "";
      bootstrapModalBloqueo?.show();
    } else {
      peticionEstado(id, 0, null);
    }
  };

  document
    .getElementById("btnConfirmarBloqueo")
    ?.addEventListener("click", () => {
      const motivo = document.getElementById("motivoBloqueo").value;
      if (!motivo) return alert("Debes indicar un motivo");
      peticionEstado(idParaBloquear, 1, motivo);
      bootstrapModalBloqueo?.hide();
    });

  const peticionEstado = async (id, estado, motivo) => {
    const formData = new FormData();
    formData.append("id_espacios_comunidad", id);
    formData.append("bloqueado", estado);
    if (motivo) formData.append("motivo", motivo);

    const res = await fetch("index.php?route=espacio/toggleEstado", {
      method: "POST",
      body: formData,
    });
    const data = await res.json();
    if (data.success) actualizarCardUI(data.espacio);
  };

  window.eliminarEspacio = async (id) => {
    if (!confirm("¿Seguro que quieres eliminar el espacio?")) return;
    const formData = new FormData();
    formData.append("id_espacios_comunidad", id);
    const res = await fetch("index.php?route=espacio/destroy", {
      method: "POST",
      body: formData,
    });
    const data = await res.json();
    if (data.success) document.getElementById(`espacio-${id}`).remove();
  };

  const actualizarCardUI = (data) => {
    // Buscamos el contenedor de la columna por ID
    const colContainer = document.getElementById(
      `espacio-${data.id_espacios_comunidad}`,
    );
    if (!colContainer) {
      console.error(
        "No se encontró la card con ID:",
        data.id_espacios_comunidad,
      );
      return;
    }

    // Aseguramos que bloqueado sea tratado como número para la comparación
    const isBloqueado = parseInt(data.bloqueado) === 1;
    const colorClase = isBloqueado ? "border-danger" : "border-primary";

    // Escapamos el objeto para evitar errores de comillas en el atributo onclick
    const espacioJson = JSON.stringify(data).replace(/'/g, "&apos;");

    colContainer.innerHTML = `
      <div class="card shadow-sm module-card h-100 border-0 border-start border-4 ${colorClase}">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="text-muted small">
              <div>${data.hora_apertura.substring(0, 5)} - ${data.hora_cierre.substring(0, 5)}</div>
            </div>
            ${isBloqueado ? '<span class="badge bg-danger">Bloqueado</span>' : '<span class="badge bg-primary">Operativo</span>'}
          </div>
          <h3 class="fs-5 fw-bold" style="font-family: var(--fuente-titulos);">${data.nombre_espacio}</h3>
          <div class="small text-muted mb-3">
            Aforo: ${data.aforo} · Máx: ${data.duracion_uso} min
          </div>
          <div class="mt-auto d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm flex-fill" 
              onclick='abrirModalEditar(${espacioJson})'>
              Editar
            </button>
            <button class="btn btn-sm ${isBloqueado ? "btn-outline-success" : "btn-outline-warning"} flex-fill"
              onclick="toggleEstadoEspacio(${data.id_espacios_comunidad}, ${isBloqueado ? 0 : 1})">
              ${isBloqueado ? "Activar" : "Bloquear"}
            </button>
            <button class="btn btn-sm btn-outline-danger"
              onclick="eliminarEspacio(${data.id_espacios_comunidad})">
              🗑
            </button>
          </div>
        </div>
      </div>
    `;
  };
});
