document.addEventListener("DOMContentLoaded", function () {
  const inputBusqueda = document.getElementById("busquedaMatricula");
  const listaMatriculas = document.getElementById("listaMatriculasComunidad");
  const cardResultados = document.getElementById("cardResultadosComunidad");
  const btnLimpiar = document.getElementById("btnLimpiarBusqueda");
  const instrucciones = document.getElementById("instruccionesBusqueda");

  if (inputBusqueda && listaMatriculas && cardResultados) {
    inputBusqueda.addEventListener("input", function () {
      const filtro = this.value.toLowerCase();
      const items = listaMatriculas.querySelectorAll(
        ".item-matricula-comunidad",
      );
      let itemsVisibles = 0;

      // Mostrar/ocultar botón de limpiar si existe
      if (btnLimpiar) {
        btnLimpiar.classList.toggle("d-none", filtro.trim() === "");
      }

      // Si no hay texto, ocultamos la tarjeta de resultados y salimos
      if (filtro.trim() === "") {
        cardResultados.classList.add("d-none");
        if (instrucciones) instrucciones.classList.remove("d-none");
        return;
      }
      cardResultados.classList.remove("d-none");
      if (instrucciones) instrucciones.classList.add("d-none");

      items.forEach((item) => {
        // Ignorar el mensaje de lista vacía (si existe)
        if (item.classList.contains("p-4")) return;

        const textoVivienda =
          item.querySelector(".vivienda-info")?.textContent.toLowerCase() || "";
        const textoMatricula =
          item.querySelector(".matricula-info")?.textContent.toLowerCase() ||
          "";

        if (textoVivienda.includes(filtro) || textoMatricula.includes(filtro)) {
          item.style.display = "block";
          itemsVisibles++;
        } else {
          item.style.display = "none";
        }
      });

      // Mostrar u ocultar el mensaje de "no hay vehículos"
      const mensajeNoResultados = listaMatriculas.querySelector(".p-4");
      if (mensajeNoResultados) {
        mensajeNoResultados.style.display =
          itemsVisibles === 0 && filtro !== "" ? "" : "none";
      }
    });

    // Lógica para el botón de limpiar si existe
    if (btnLimpiar) {
      btnLimpiar.addEventListener("click", function () {
        inputBusqueda.value = "";
        inputBusqueda.dispatchEvent(new Event("input"));
        inputBusqueda.focus();
      });
    }
  }
});
