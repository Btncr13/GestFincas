/**
 * Gestión de Modal para Creación de Espacios Comunitarios
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
  const contenedorEspacios = document.getElementById(
    "contenedor-cards-espacios",
  );

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
        showToast("Instalación creada correctamente");
      } else {
        throw new Error(result.message || "Error en el servidor");
      }
    } catch (error) {
      console.error("Error:", error);
      showToast("Error: " + error.message, "error");
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
    const colorClase = isBloqueado ? "var(--bs-danger)" : "var(--bs-primary)";
    const textClase = isBloqueado ? "text-danger" : "text-primary";
    const badgeHTML = isBloqueado
      ? '<span class="badge bg-danger" style="font-size: 10px;">Bloqueado</span>'
      : '<span class="badge bg-success" style="font-size: 10px;">Operativo</span>';
    const toggleBtnClass = isBloqueado
      ? "btn-outline-success"
      : "btn-outline-warning";
    const toggleBtnIcon = isBloqueado
      ? '<i class="fa-solid fa-check me-1"></i>Activar'
      : '<i class="fa-solid fa-ban me-1"></i>Bloquear';

    // Escapamos el objeto para evitar errores de comillas
    const espacioJson = JSON.stringify(data).replace(/'/g, "&apos;");

    const cardHTML = `
<div class="card shadow-sm border module-card" style="border-left: 4px solid var(--color-borde) !important; border-color: var(--color-borde) !important;" id="espacio-${data.id_espacios_comunidad}">
    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-building fs-5 ${textClase}"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="fs-6 fw-bold text-dark mb-0" style="font-family: var(--fuente-titulos);">
                        ${data.nombre_espacio}
                    </h3>
                    ${badgeHTML}
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users ${textClase}"></i> Aforo Total: ${data.aforo}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-user-group ${textClase}"></i> Máx. Personas/Reserva: ${data.max_personas}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-stopwatch ${textClase}"></i> Duración: ${data.duracion_uso} min</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock ${textClase}"></i> ${data.hora_apertura.substring(0, 5)} a ${data.hora_cierre.substring(0, 5)}</span>
                </div>
                <details class="mt-3" style="font-size:13px; color:var(--color-texto);">
                    <summary class="fw-semibold cursor-pointer ${textClase}">
                        <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                    </summary>
                    <ul class="list-unstyled ps-3 pt-2 mb-0">
                        ${
                          data.normas && data.normas.length > 0
                            ? data.normas
                                .map(
                                  (norma) =>
                                    `<li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i>${norma}</li>`,
                                )
                                .join("")
                            : "<li>No hay normas definidas para este espacio.</li>"
                        }
                    </ul>
                </details>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 ms-md-auto">
            <button class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm" onclick='abrirModalEditar(${espacioJson})'>
                <i class="fa-solid fa-pen-to-square me-1"></i>Editar
            </button>
            <button class="btn btn-sm ${toggleBtnClass} fw-semibold shadow-sm" onclick="toggleEstadoEspacio(${data.id_espacios_comunidad}, ${isBloqueado ? 0 : 1})">
                ${toggleBtnIcon}
            </button>
            <button class="btn btn-sm btn-outline-danger fw-semibold shadow-sm" onclick="eliminarEspacio(${data.id_espacios_comunidad})">
                <i class="fa-solid fa-trash"></i>
            </button>
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

  // Función global para añadir un nuevo campo de norma en el modal de edición
  window.crearInputNormaEdit = (valor = "") => {
    const contenedor = document.getElementById("contenedor-normas-edit");
    if (!contenedor) return;
    const div = document.createElement("div");
    div.className = "input-group input-group-sm mb-1";
    div.innerHTML = `
        <input type="text" name="normas[]" class="form-control border-end-0" value="${valor}" placeholder="Describa la norma...">
        <button class="btn btn-outline-danger border-start-0" type="button" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    contenedor.appendChild(div);
  };

  window.abrirModalEditar = async (espacio) => {
    document.getElementById("edit_id").value = espacio.id_espacios_comunidad;
    document.getElementById("edit_nombre").value = espacio.nombre_espacio;
    document.getElementById("edit_aforo").value = espacio.aforo;
    document.getElementById("edit_max").value = espacio.max_personas;
    document.getElementById("edit_apertura").value = espacio.hora_apertura;
    document.getElementById("edit_cierre").value = espacio.hora_cierre;
    document.getElementById("edit_duracion").value = espacio.duracion_uso;

    // Fetch norms for the space and populate the textarea
    try {
      const response = await fetch(
        `index.php?route=espacio/getNormasForEspacio&id=${espacio.id_espacios_comunidad}`,
      );
      const result = await response.json();
      const contenedor = document.getElementById("contenedor-normas-edit");
      if (contenedor) {
        contenedor.innerHTML = ""; // Limpiar normas previas
        if (result.success && result.normas.length > 0) {
          result.normas.forEach((n) => window.crearInputNormaEdit(n));
        } else {
          window.crearInputNormaEdit(); // Un campo vacío por defecto
        }
      }
    } catch (error) {
      console.error("Network error fetching norms:", error);
    }
    bootstrapModalEditar?.show();
  };

  document
    .getElementById("formEditarEspacio")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      // Deshabilitar el botón para evitar envíos múltiples
      const btnActualizar = e.submitter;
      if (btnActualizar) btnActualizar.disabled = true;

      const formData = new FormData(e.target);
      const res = await fetch("index.php?route=espacio/update", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      if (data.success) {
        actualizarCardUI(data.espacio);
        bootstrapModalEditar?.hide();
        showToast("Datos actualizados correctamente");
      } else {
        showToast(data.message || "Error al actualizar el espacio.", "error");
      }
      if (btnActualizar) btnActualizar.disabled = false; // Re-enable button
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

    try {
      const res = await fetch("index.php?route=espacio/toggleEstado", {
        method: "POST",
        body: formData,
      });
      
      const data = await res.json();
      
      if (data.success) {
        actualizarCardUI(data.espacio);
        showToast(data.message);
        // Refrescamos la pestaña de reservas para reflejar cancelaciones/reactivaciones
        if (typeof window.cargarReservasPresi === 'function') {
            window.cargarReservasPresi();
        }
      } else {
        showToast(data.message || "Error al cambiar el estado", "error");
      }
    } catch (error) {
      console.error("Error en toggleEstado:", error);
      showToast("Error de conexión con el servidor", "error");
    }
  };

  window.eliminarEspacio = async (id) => {
    if (!confirm("¿Seguro que quieres eliminar el espacio?")) return;

    try {
      const formData = new FormData();
      formData.append("id_espacios_comunidad", id);

      const res = await fetch("index.php?route=espacio/destroy", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      if (data.success) {
        const cardAEliminar = document.getElementById(`espacio-${id}`);
        if (cardAEliminar) {
          cardAEliminar.remove();

          // Si ya no quedan espacios, mostramos el mensaje de "vacío"
          if (
            contenedorEspacios &&
            contenedorEspacios.querySelectorAll(".card").length === 0
          ) {
            contenedorEspacios.innerHTML = `
              <div class="text-center py-5" id="mensaje-vacio-espacios">
                <i class="fa-solid fa-building-circle-xmark fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold text-muted">No hay espacios creados</h5>
                <p class="text-muted small">Haz clic en "Nuevo Espacio" para añadir instalaciones a la comunidad.</p>
              </div>`;
          }
          showToast("Espacio eliminado con éxito");
        }
      } else {
        showToast(data.message || "No se pudo eliminar el espacio.", "error");
      }
    } catch (error) {
      console.error("Error al eliminar:", error);
      showToast("Error crítico al procesar la eliminación.", "error");
    }
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
    const colorClase = isBloqueado ? "var(--bs-danger)" : "var(--bs-primary)";
    const textClase = isBloqueado ? "text-danger" : "text-primary";
    const badgeHTML = isBloqueado
      ? '<span class="badge bg-danger" style="font-size: 10px;">Bloqueado</span>'
      : '<span class="badge bg-success" style="font-size: 10px;">Operativo</span>';
    const toggleBtnClass = isBloqueado
      ? "btn-outline-success"
      : "btn-outline-warning";
    const toggleBtnIcon = isBloqueado
      ? '<i class="fa-solid fa-check me-1"></i>Activar'
      : '<i class="fa-solid fa-ban me-1"></i>Bloquear';

    // Escapamos el objeto para evitar errores de comillas en el atributo onclick
    const espacioJson = JSON.stringify(data).replace(/'/g, "&apos;");

    colContainer.classList.remove("border-0");
    colContainer.classList.add("border");
    colContainer.setAttribute(
      "style",
      `border-left: 4px solid var(--color-borde) !important; border-color: var(--color-borde) !important;`,
    );
    colContainer.innerHTML = `
    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                <i class="fa-solid fa-building fs-5 ${textClase}"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="fs-6 fw-bold text-dark mb-0" style="font-family: var(--fuente-titulos);">
                        ${data.nombre_espacio}
                    </h3>
                    ${badgeHTML}
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:13px; color:var(--color-texto);">
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-users ${textClase}"></i> Aforo Total: ${data.aforo}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-user-group ${textClase}"></i> Máx. Personas/Reserva: ${data.max_personas}</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-solid fa-stopwatch ${textClase}"></i> Duración: ${data.duracion_uso} min</span>
                    <span class="d-flex align-items-center gap-1"><i class="fa-regular fa-clock ${textClase}"></i> ${data.hora_apertura.substring(0, 5)} a ${data.hora_cierre.substring(0, 5)}</span>
                </div>
                <details class="mt-3" style="font-size:13px; color:var(--color-texto);">
                    <summary class="fw-semibold cursor-pointer ${textClase}">
                        <i class="fa-solid fa-circle-info me-1"></i> Ver Normas de Uso
                    </summary>
                    <ul class="list-unstyled ps-3 pt-2 mb-0">
                        ${
                          data.normas && data.normas.length > 0
                            ? data.normas
                                .map(
                                  (norma) =>
                                    `<li class="mb-1"><i class="fa-solid fa-check-circle me-2 text-success"></i>${norma}</li>`,
                                )
                                .join("")
                            : "<li>No hay normas definidas para este espacio.</li>"
                        }
                    </ul>
                </details>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 ms-md-auto">
            <button class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm" onclick='abrirModalEditar(${espacioJson})'>
                <i class="fa-solid fa-pen-to-square me-1"></i>Editar
            </button>
            <button class="btn btn-sm ${toggleBtnClass} fw-semibold shadow-sm" onclick="toggleEstadoEspacio(${data.id_espacios_comunidad}, ${isBloqueado ? 0 : 1})">
                ${toggleBtnIcon}
            </button>
            <button class="btn btn-sm btn-outline-danger fw-semibold shadow-sm" onclick="eliminarEspacio(${data.id_espacios_comunidad})">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
    `;
  };

  // --- 9. SISTEMA DE NOTIFICACIONES (TOAST) ---
  const toastEl = document.getElementById("liveToast");
  const toastBody = document.getElementById("toastMessage");
  const toastInstance = toastEl ? new bootstrap.Toast(toastEl) : null;

  /**
   * Muestra una notificación visual en pantalla
   */
  window.showToast = (message, type = "success") => {
    if (!toastInstance) return;
    toastBody.textContent = message;
    toastEl.classList.remove("bg-success", "bg-danger", "text-white");
    const bgClass = type === "success" ? "bg-success" : "bg-danger";
    toastEl.classList.add(bgClass, "text-white");
    toastInstance.show();
  };
});
