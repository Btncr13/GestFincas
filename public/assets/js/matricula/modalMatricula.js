function toggleInvitado(valor) {
  const divInvitado = document.getElementById("divInvitado");
  const inputNombreInvitado = document.getElementById("inputNombreInvitado");
  const inputFechaEntrada = document.getElementById("inputFechaEntrada");

  if (valor === "invitado") {
    divInvitado.style.display = "block";
    inputNombreInvitado.required = true;
    inputFechaEntrada.required = true;
    // Establecer la fecha mínima como hoy para evitar fechas pasadas
    const today = new Date().toISOString().split("T")[0];
    inputFechaEntrada.min = today;
  } else {
    divInvitado.style.display = "none";
    inputNombreInvitado.required = false;
    inputFechaEntrada.required = false;
    // Limpiar los valores cuando se ocultan los campos
    inputNombreInvitado.value = "";
    inputFechaEntrada.value = "";
  }
}
/**
 * Valida la matrícula en tiempo real.
 * Formato: 4 números seguidos de 3 letras mayúsculas.
 */
function validarMatricula(input) {
  // Convertimos a mayúsculas y eliminamos espacios
  let valor = input.value.toUpperCase().replace(/\s/g, "");

  // Limitamos a 7 caracteres (estándar español: 1234BBB)
  if (valor.length > 7) {
    valor = valor.substring(0, 7);
  }
  input.value = valor;

  // Patrón completo: 4 números y 3 letras
  const regexCompleta = /^[0-9]{4}[A-Z]{3}$/;
  // Patrón parcial: Permite validar mientras el usuario escribe (prefijo válido)
  const regexParcial = /^[0-9]{0,4}[A-Z]{0,3}$/;

  const esValida = regexCompleta.test(valor);
  const esPrefijoValido = regexParcial.test(valor);

  if (
    valor.length > 0 &&
    (!esPrefijoValido || (valor.length === 7 && !esValida))
  ) {
    input.classList.add("is-invalid");
  } else {
    input.classList.remove("is-invalid");
  }

  // Deshabilitar botón de envío si el valor no está completo o es inválido
  const btnSubmit = input
    .closest("form")
    .querySelector('button[type="submit"]');
  if (btnSubmit) {
    btnSubmit.disabled = !esValida && valor.length > 0;
  }
}

// Inicializar el estado del modal si el primer option habilitado es invitado
document.addEventListener("DOMContentLoaded", () => {
  const selectUso = document.getElementById("selectUso");
  if (selectUso) {
    toggleInvitado(selectUso.value);
  }
});
