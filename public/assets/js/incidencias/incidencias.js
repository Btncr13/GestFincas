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

    // LÓGICA DEL BOTÓN: Forzar la creación ("No, es diferente")
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
});
