document.addEventListener("DOMContentLoaded", () => {
  // Helper para mostrar Toasts de Bootstrap 5
  const showToast = (message, type = "success") => {
    const toastContainer =
      document.getElementById("toast-container") || createToastContainer();
    const bgClass =
      type === "success"
        ? "bg-success"
        : type === "error"
          ? "bg-danger"
          : "bg-info";

    const toastHTML = `
            <div class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;

    toastContainer.insertAdjacentHTML("beforeend", toastHTML);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener("hidden.bs.toast", () =>
      toastElement.remove(),
    );
  };

  const createToastContainer = () => {
    const container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    container.style.zIndex = "1055";
    document.body.appendChild(container);
    return container;
  };

  // ==========================================
  // 0. LÓGICA DE RENDERIZADO Y FILTRADO APP
  // ==========================================
  window.appIncidencias = {
    tabVecino: "mis",
    tabPresi: "pendiente",
    filtroTiempo: "ultimos_3_meses",

    init: function () {
      if (isPresi) this.switchTabPresi("pendiente");
      else this.switchTabVecino("mis");
    },

    switchTabVecino: function (tab) {
      this.tabVecino = tab;
      const btnMis = document.getElementById("btn-tab-mis");
      const btnOtras = document.getElementById("btn-tab-otras");

      if (btnMis && btnOtras) {
        btnMis.className = tab === "mis" ? "btn flex-fill text-center rounded-2 py-2 text-dark" : "btn flex-fill text-center rounded-2 py-2 text-muted";
        btnMis.style.backgroundColor = tab === "mis" ? "var(--bs-light)" : "transparent";
        btnMis.style.boxShadow = tab === "mis" ? "0 1px 3px rgba(0,0,0,0.1)" : "none";

        btnOtras.className = tab === "otras" ? "btn flex-fill text-center rounded-2 py-2 text-dark" : "btn flex-fill text-center rounded-2 py-2 text-muted";
        btnOtras.style.backgroundColor = tab === "otras" ? "var(--bs-light)" : "transparent";
        btnOtras.style.boxShadow = tab === "otras" ? "0 1px 3px rgba(0,0,0,0.1)" : "none";
      }
      this.renderList();
    },

    switchTabPresi: function (tab) {
      this.tabPresi = tab;
      const tabs = ["pendiente", "abierta", "resuelta"];
      tabs.forEach((t) => {
        const btn = document.getElementById(`btn-tab-${t}`);
        if (btn) {
          const isActive = t === tab;
          btn.className = isActive ? "btn flex-fill text-center rounded-2 py-2 text-dark" : "btn flex-fill text-center rounded-2 py-2 text-muted";
          btn.style.backgroundColor = isActive ? "var(--bs-light)" : "transparent";
          btn.style.boxShadow = isActive ? "0 1px 3px rgba(0,0,0,0.1)" : "none";
        }
      });
      this.renderList();
    },

    changeFiltroTiempo: function (val) {
      this.filtroTiempo = val;
      this.renderList();
    },

    filterByTime: function (dateStr) {
      const date = new Date(dateStr.replace(/-/g, '/').replace(/T/, ' ')); 
      const now = new Date();
      if (this.filtroTiempo === "anio_actual") {
        return date.getFullYear() === now.getFullYear();
      } else if (this.filtroTiempo === "mensual") {
        return date.getFullYear() === now.getFullYear() && date.getMonth() === now.getMonth();
      } else if (this.filtroTiempo === "ultimos_3_meses") {
        const tresMesesAtras = new Date();
        tresMesesAtras.setMonth(now.getMonth() - 3);
        return date >= tresMesesAtras;
      }
      return true;
    },

    renderList: function () {
      const container = document.getElementById("contenedor-incidencias");
      if (!container) return;

      let filtradas = incidenciasDB.filter((inc) => {
        if (!this.filterByTime(inc.fecha_creacion)) return false;

        if (isPresi) return inc.estado === this.tabPresi;
        else return this.tabVecino === "mis" ? String(inc.id_vivienda) === String(miViviendaId) : String(inc.id_vivienda) !== String(miViviendaId);
      });

      if (filtradas.length === 0) {
        container.innerHTML = `
        <div class="card shadow-sm border-0 p-5 text-center mt-2" style="border-radius: var(--radio-lg); background-color: var(--bs-light);">
            <i class="fa-solid fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
            <h5 class="text-muted" style="font-family: var(--fuente-titulos); font-weight: 700;">No hay incidencias que mostrar</h5>
            <p class="text-muted small">No se encontraron resultados para los filtros seleccionados.</p>
        </div>`;
        return;
      }

      let html = "";
      filtradas.forEach((inc) => {
        const esMia = String(inc.id_vivienda) === String(miViviendaId);
        const estaAbierta = ["pendiente", "abierta"].includes(inc.estado);
        const yaUnido = misUnionesDB.includes(String(inc.id_incidencias)) || misUnionesDB.includes(Number(inc.id_incidencias));

        let badgeEstado = "";
        if (inc.estado === "pendiente") badgeEstado = '<span class="badge bg-secondary"><i class="fa-solid fa-clock"></i> Pendiente</span>';
        else if (inc.estado === "abierta") badgeEstado = '<span class="badge bg-warning text-dark"><i class="fa-solid fa-folder-open"></i> En curso</span>';
        else if (inc.estado === "resuelta") badgeEstado = '<span class="badge bg-success"><i class="fa-solid fa-check-circle"></i> Resuelta</span>';

        const d = new Date(inc.fecha_creacion.replace(/-/g, '/').replace(/T/, ' '));
        const fechaStr = d.toLocaleDateString("es-ES");

        let fotoHtml = "";
        if (inc.foto_incidencia) {
          fotoHtml = `
          <div class="mb-3 mt-2">
              <a href="javascript:void(0);" class="lightbox-trigger" data-img="${inc.foto_incidencia}" title="Ampliar imagen">
                  <img src="${inc.foto_incidencia}" alt="Evidencia" class="rounded shadow-sm border" style="max-height: 80px; object-fit: cover; cursor: zoom-in; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
              </a>
          </div>`;
        }

        let actionsHtml = "";
        if (isPresi && inc.estado === "abierta") actionsHtml += `<button class="btn btn-sm btn-success btn-resolver w-100 mb-2" data-id="${inc.id_incidencias}"><i class="fa-solid fa-check"></i> Resolver</button>`;
        if (isPresi && inc.estado === "pendiente") actionsHtml += `<button class="btn btn-sm btn-warning text-dark fw-bold btn-abrir w-100 mb-2" data-id="${inc.id_incidencias}"><i class="fa-solid fa-folder-open"></i> Abrir</button>`;
        if (esMia || isPresi) actionsHtml += `<button class="btn btn-sm btn-outline-danger btn-delete w-100 mb-2" data-id="${inc.id_incidencias}" title="Eliminar incidencia"><i class="fa-solid fa-trash"></i> Eliminar</button>`;
        if (!esMia && estaAbierta && !isPresi) {
          if (yaUnido) actionsHtml += `<button class="btn btn-sm btn-secondary text-white fw-bold w-100 mb-2" disabled><i class="fa-solid fa-check"></i> Te has unido</button>`;
          else actionsHtml += `<button class="btn btn-sm btn-info text-white fw-bold btn-unirme-card w-100 mb-2" data-id="${inc.id_incidencias}"><i class="fa-solid fa-hand-holding-hand"></i> Unirme</button>`;
        }

        html += `
        <div class="card shadow-sm border-0 module-card card-incidencia" id="incidencia-${inc.id_incidencias}" style="${esMia ? "border-left: 4px solid var(--bs-primary) !important;" : ""}">
            <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center">
                <div class="flex-grow-1 mb-3 mb-md-0">
                    <h5 class="mb-1 fw-bold" style="font-family: var(--fuente-titulos); font-size: 16px; color: var(--bs-dark);">${inc.titulo} ${esMia ? '<span class="badge bg-primary ms-2" style="font-size: 0.6em;">MI INCIDENCIA</span>' : ""}</h5>
                    <p class="mb-2 text-muted small">${inc.descripcion || ""}</p>
                    ${fotoHtml}
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-2" style="font-size: 12px;">${badgeEstado}<span class="text-muted"><i class="fa-solid fa-users text-info me-1"></i> Afectados: <strong id="afectados-${inc.id_incidencias}">${inc.numero_afectados}</strong></span><span class="text-muted"><i class="fa-solid fa-house me-1"></i> ${inc.nombre_vivienda || "Comunidad"}</span><span class="text-muted"><i class="fa-solid fa-calendar me-1"></i> ${fechaStr}</span></div>
                </div>
                <div class="ms-md-3 text-md-end d-flex flex-row flex-md-column gap-2" style="min-width: 120px;">${actionsHtml}</div>
            </div>
        </div>`;
      });
      container.innerHTML = html;
    },
  };

  // Lanzar la app inicial
  appIncidencias.init();

  // ==========================================
  // 1. LÓGICA DEL MODAL: CREAR INCIDENCIA
  // ==========================================
  const form = document.getElementById("form-incidencia");

  if (form) {
    const inputTitulo = document.getElementById("incidencia-titulo");
    const counterTitulo = document.getElementById("counter-titulo");
    const errorTitulo = document.getElementById("error-titulo");
    const inputDescripcion = document.getElementById("incidencia-descripcion");
    const counterDescripcion = document.getElementById("counter-descripcion");
    const alertaSimilar = document.getElementById("alerta-similar");
    const btnUnirmeModal = document.getElementById("btn-unirme");
    const btnForzarCrear = document.getElementById("btn-forzar-crear");
    const btnSubmit = document.getElementById("btn-submit");

    let idIncidenciaSimilar = null;

    // Función para validar el formulario y habilitar/deshabilitar el botón
    const checkFormValidity = () => {
      if (!inputTitulo || !inputDescripcion) return;

      const words = inputTitulo.value.trim().match(/\S+/g) || [];
      const isTituloValid = words.length > 0 && words.length <= 4;
      const isDescripcionValid = inputDescripcion.value.trim().length > 0;
      const isSimilarAlertActive =
        alertaSimilar && !alertaSimilar.classList.contains("d-none");

      const isValid =
        isTituloValid && isDescripcionValid && !isSimilarAlertActive;

      if (btnSubmit) {
        btnSubmit.disabled = !isValid;
        btnSubmit.classList.toggle("opacity-50", !isValid);
      }
    };

    // Escuchador global para cualquier cambio en el formulario (limpieza de alertas)
    form.addEventListener("input", () => {
      if (alertaSimilar && !alertaSimilar.classList.contains("d-none")) {
        alertaSimilar.classList.add("d-none");
        if (btnSubmit) btnSubmit.classList.remove("d-none");
        if (btnForzarCrear) {
            btnForzarCrear.disabled = false;
            btnForzarCrear.innerHTML = '<i class="fa-solid fa-file-circle-plus"></i> No, es diferente (Crear)';
        }
      }
    });

    // 1. Contador y validación de palabras para el Título
    if (inputTitulo) {
      inputTitulo.addEventListener("input", function () {
        // Contamos palabras ignorando espacios múltiples
        let words = this.value.match(/\S+/g) || [];

        if (words.length > 4) {
          if (errorTitulo) errorTitulo.classList.remove("d-none");
          // Bloqueamos la entrada a solo 4 palabras
          this.value = words.slice(0, 4).join(" ");
          words = words.slice(0, 4);
        } else {
          if (errorTitulo) errorTitulo.classList.add("d-none");
        }

        if (counterTitulo) {
          counterTitulo.innerText = `${words.length} / 4`;
          counterTitulo.classList.toggle("text-danger", words.length >= 4);
          counterTitulo.classList.toggle("text-muted", words.length < 4);
        }

        checkFormValidity();
      });
    }

    // 2. Contador de caracteres para la Descripción
    if (inputDescripcion) {
      inputDescripcion.addEventListener("input", function () {
        if (counterDescripcion) {
          const length = this.value.length;
          counterDescripcion.innerText = `${length} / 255`;
          counterDescripcion.classList.toggle("text-danger", length >= 255);
          counterDescripcion.classList.toggle("text-muted", length < 255);
        }

        checkFormValidity();
      });
    }

    const btnSubmitOriginal = document.getElementById("btn-submit");

    // Extraemos la lógica de fetch para poder reutilizarla
    const enviarFormulario = async (formData) => {
      try {
        const response = await fetch("index.php?route=incidencias/store", {
          method: "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest" // CRÍTICO: Para que PHP sepa que es AJAX y no envíe HTML
          }
        });

        const data = await response.json();

        if (response.status === 409 && data.status === "similar_found") {
          // Mostrar alerta amarilla de similitud en el modal
          const fecha = data.incidencia.fecha_creacion.split(" ");
          document.getElementById("sim-titulo").innerText =
            `"${data.incidencia.titulo}" (Reportada el ${fecha[0]})`;

          idIncidenciaSimilar = data.incidencia.id_incidencias;
          alertaSimilar.classList.remove("d-none");
          btnSubmitOriginal.classList.add("d-none"); // Ocultar el botón original "Guardar"
          
        } else if (response.ok && data.status === "success") {
          showToast("Incidencia reportada correctamente.", "success");
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(data.message || "Ocurrió un error al guardar.", "error");
        }
      } catch (error) {
        console.error("Error:", error);
        showToast("Error crítico de red. Revisa la consola.", "error");
      }
    };

    // Envío inicial del formulario
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      alertaSimilar.classList.add("d-none"); // Limpiar alertas previas
      const formData = new FormData(form);
      enviarFormulario(formData);
    });

    // Lógica para Unirse desde el aviso de duplicado (dentro del Modal)
    // Ahora está dentro de 'if (form)' para compartir el ámbito de 'idIncidenciaSimilar'
    if (btnUnirmeModal) {
      btnUnirmeModal.addEventListener("click", async () => {
        if (!idIncidenciaSimilar) return;

        const formData = new FormData();
        formData.append("id_incidencia", idIncidenciaSimilar);

        try {
          const response = await fetch("index.php?route=incidencias/join", {
            method: "POST",
            body: formData,
            headers: {
              "X-Requested-With": "XMLHttpRequest"
            }
          });
          if (response.ok) {
            showToast(
              "Te has unido exitosamente a la incidencia comunitaria.",
              "success",
            );
            setTimeout(() => location.reload(), 1500);
          } else {
            const data = await response.json();
            showToast(data.message || "Error al unirse.", "error");
          }
        } catch (error) {
          console.error("Error:", error);
          showToast("Error de conexión al intentar unirse.", "error");
        }
      });
    }

    // LÓGICA DEL NUEVO BOTÓN: Forzar la creación ("No, es diferente")
    if (btnForzarCrear) {
      btnForzarCrear.addEventListener("click", () => {
        const formData = new FormData(form);
        formData.append("forzar_creacion", "true"); // Añadimos el flag mágico
        
        btnForzarCrear.disabled = true;
        btnForzarCrear.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creando...';
        
        enviarFormulario(formData);
      });
    }

    // Sincronizar estado inicial del botón (debe nacer deshabilitado)
    checkFormValidity();
  }

  // ==========================================
  // 2. LÓGICA DEL TABLÓN: DELEGACIÓN DE EVENTOS
  // ==========================================
  const contenedorIncidencias = document.getElementById(
    "contenedor-incidencias",
  );

  if (contenedorIncidencias) {
    contenedorIncidencias.addEventListener("click", async (e) => {
      // ACCIÓN: VER FOTO (LIGHTBOX)
      if (e.target.closest(".lightbox-trigger")) {
        e.preventDefault();
        const trigger = e.target.closest(".lightbox-trigger");
        const imgSrc = trigger.getAttribute("data-img");
        const lightboxImage = document.getElementById("lightboxImage");

        if (lightboxImage) {
          lightboxImage.src = imgSrc;
          const lightboxModal = new bootstrap.Modal(
            document.getElementById("lightboxModal"),
          );
          lightboxModal.show();
        }
      }

      // ACCIÓN: UNIRME DESDE LA TARJETA
      if (e.target.closest(".btn-unirme-card")) {
        const btn = e.target.closest(".btn-unirme-card");
        const idIncidencia = btn.getAttribute("data-id");

        const formData = new FormData();
        formData.append("id_incidencia", idIncidencia);

        try {
          // Deshabilitar temporalmente para evitar doble clic
          btn.disabled = true;
          btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Uniéndose...';

          const response = await fetch("index.php?route=incidencias/join", {
            method: "POST",
            body: formData,
            headers: {
              "X-Requested-With": "XMLHttpRequest"
            }
          });

          const data = await response.json();

          if (response.ok) {
            // Optimistic UI: Sumar 1 al contador sin recargar
            const contadorDOM = document.getElementById(
              `afectados-${idIncidencia}`,
            );
            if (contadorDOM) {
              contadorDOM.innerText = parseInt(contadorDOM.innerText) + 1;
            }
            // Cambiar botón para feedback visual
            btn.classList.replace("btn-info", "btn-secondary");
            btn.classList.remove("btn-unirme-card"); // Quita el gatillo del evento
            btn.disabled = true; // Deshabilita el botón físicamente
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Te has unido';
          } else {
            showToast(
              data.message || "Error al unirse a la incidencia.",
              "error",
            );
            btn.disabled = false;
            btn.innerHTML =
              '<i class="fa-solid fa-hand-holding-hand"></i> Unirme';
          }
        } catch (error) {
          console.error("Error:", error);
          showToast("Error de conexión.", "error");
          btn.disabled = false;
        }
      }

      // ACCIÓN: RESOLVER (PRESIDENTE)
      if (e.target.closest(".btn-resolver")) {
        const btn = e.target.closest(".btn-resolver");
        const idIncidencia = btn.getAttribute("data-id");

        if (
          confirm(
            "¿Estás seguro de que deseas marcar esta incidencia como resuelta?",
          )
        ) {
          const formData = new FormData();
          formData.append("id_incidencia", idIncidencia);
          formData.append("estado", "resuelta"); // Añadimos el estado esperado

          try {
            btn.disabled = true;
            btn.innerHTML =
              '<i class="fa-solid fa-spinner fa-spin"></i> Resolviendo...';

            const response = await fetch(
              "index.php?route=incidencias/updateEstado",
              {
                method: "POST",
                body: formData,
                headers: {
                  "X-Requested-With": "XMLHttpRequest"
                }
              },
            );

            const data = await response.json();

            if (response.ok) {
              showToast(
                "La incidencia ha sido marcada como resuelta.",
                "success",
              );
              setTimeout(() => location.reload(), 1500);
            } else {
              showToast(
                data.message || "Error al resolver la incidencia.",
                "error",
              );
              btn.disabled = false;
              btn.innerHTML = '<i class="fa-solid fa-check"></i> Resolver';
            }
          } catch (error) {
            console.error("Error:", error);
            showToast("Error crítico de red.", "error");
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Resolver';
          }
        }
      }

      // ACCIÓN: ABRIR (PRESIDENTE)
      if (e.target.closest(".btn-abrir")) {
        const btn = e.target.closest(".btn-abrir");
        const idIncidencia = btn.getAttribute("data-id");

        if (
          confirm(
            "¿Estás seguro de que deseas marcar esta incidencia como en curso (abierta)?",
          )
        ) {
          const formData = new FormData();
          formData.append("id_incidencia", idIncidencia);
          formData.append("estado", "abierta"); // Añadimos el estado esperado

          try {
            btn.disabled = true;
            btn.innerHTML =
              '<i class="fa-solid fa-spinner fa-spin"></i> Abriendo...';

            const response = await fetch(
              "index.php?route=incidencias/updateEstado",
              {
                method: "POST",
                body: formData,
                headers: {
                  "X-Requested-With": "XMLHttpRequest"
                }
              },
            );

            const data = await response.json();

            if (response.ok) {
              showToast("La incidencia ha sido abierta (en curso).", "success");
              setTimeout(() => location.reload(), 1500);
            } else {
              showToast(
                data.message || "Error al abrir la incidencia.",
                "error",
              );
              btn.disabled = false;
              btn.innerHTML = '<i class="fa-solid fa-folder-open"></i> Abrir';
            }
          } catch (error) {
            console.error("Error:", error);
            showToast("Error crítico de red.", "error");
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-folder-open"></i> Abrir';
          }
        }
      }

      // ACCIÓN: ELIMINAR (CREADOR O PRESIDENTE)
      if (e.target.closest(".btn-delete")) {
        const btn = e.target.closest(".btn-delete");
        const idIncidencia = btn.getAttribute("data-id");

        if (
          confirm(
            "¿Seguro que deseas eliminar esta incidencia permanentemente?",
          )
        ) {
          const formData = new FormData();
          formData.append("id_incidencia", idIncidencia);

          try {
            btn.disabled = true;
            btn.innerHTML =
              '<i class="fa-solid fa-spinner fa-spin"></i> Eliminando...';

            const response = await fetch("index.php?route=incidencias/delete", {
              method: "POST",
              body: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest"
              }
            });

            const data = await response.json();

            if (response.ok) {
              showToast("Incidencia eliminada correctamente.", "success");
              // Optimistic UI: Borramos la tarjeta del DOM sin recargar la página entera
              const tarjeta = document.getElementById(
                `incidencia-${idIncidencia}`,
              );
              if (tarjeta) tarjeta.remove();

              // Si era la única incidencia visible, recargamos para que aparezca el estado vacío
              if (document.querySelectorAll('[id^="incidencia-"]').length === 0)
                location.reload();
            } else {
              showToast(
                data.message || "Error al eliminar la incidencia.",
                "error",
              );
              btn.disabled = false;
              btn.innerHTML = '<i class="fa-solid fa-trash"></i> Eliminar';
            }
          } catch (error) {
            console.error("Error:", error);
            showToast("Error de conexión.", "error");
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash"></i> Eliminar';
          }
        }
      }
    });
  }
});
