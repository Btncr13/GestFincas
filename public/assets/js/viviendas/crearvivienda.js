function generarCodigoRegistro() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let resultado = '';
    for (let i = 0; i < 8; i++) {
        resultado += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('codigo_vivienda').value = resultado;
}

let viviendaSeleccionada = null;

function seleccionarVivienda(elemento) {
    if (elemento.classList.contains('border-primary')) {
        elemento.classList.remove('border-primary', 'shadow-sm');
        elemento.style.borderColor = 'transparent';
        viviendaSeleccionada = null;
        return;
    }
    document.querySelectorAll('.vivienda-badge').forEach(el => {
        el.classList.remove('border-primary', 'shadow-sm');
        el.style.borderColor = 'transparent';
    });
    elemento.classList.add('border-primary', 'shadow-sm');
    elemento.style.borderColor = 'var(--bs-primary)';
    viviendaSeleccionada = {
        id: elemento.getAttribute('data-id'),
        nombre: elemento.getAttribute('data-nombre')
    };
}

function prepararEliminacion() {
    if (!viviendaSeleccionada) {
        alert('Por favor, selecciona una vivienda pulsando sobre su nombre antes de eliminar.');
        return;
    }
    document.getElementById('id_vivienda_eliminar').value = viviendaSeleccionada.id;
    document.getElementById('nombre_vivienda_confirmar').textContent = viviendaSeleccionada.nombre;
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarVivienda'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const modalEliminar = document.getElementById('modalEliminarVivienda');
    if (modalEliminar) {
        modalEliminar.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.vivienda-badge').forEach(el => {
                el.classList.remove('border-primary', 'shadow-sm');
                el.style.borderColor = 'transparent';
            });
            viviendaSeleccionada = null;
        });
    }

    // Leemos la URL usando JS puro
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        if (status === 'success') {
            alert('Vivienda creada y código generado con éxito.');
        } else {
            alert('Error al procesar el alta de la vivienda.');
        }
        window.history.replaceState({}, document.title, "index.php?route=auth/usuarios");
    }

    if (urlParams.has('delete')) {
        const delStatus = urlParams.get('delete');
        if (delStatus === 'success') {
            alert('Vivienda eliminada correctamente.');
        } else {
            alert('Error al intentar eliminar la vivienda.');
        }
        window.history.replaceState({}, document.title, "index.php?route=auth/usuarios");
    }
});

// --- NUEVA LÓGICA PARA MODIFICAR ---
function prepararModificacion() {
    if (!viviendaSeleccionada) {
        showComunidadToast('Por favor, selecciona una vivienda pulsando sobre su nombre antes de modificar.', 'warning');
        return;
    }
    
    // Inyectamos los datos de la selección en los inputs del nuevo modal
    document.getElementById('id_vivienda_modificar').value = viviendaSeleccionada.id;
    document.getElementById('nombre_vivienda_modificar').value = viviendaSeleccionada.nombre;
    
    // Mostramos el modal
    const modal = new bootstrap.Modal(document.getElementById('modalModificarVivienda'));
    modal.show();
}
// Resetear selección al cerrar el modal de modificar
    const modalModificar = document.getElementById('modalModificarVivienda');
    if (modalModificar) {
        modalModificar.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.vivienda-badge').forEach(el => {
                el.classList.remove('border-primary', 'shadow-sm');
                el.style.borderColor = 'transparent';
            });
            viviendaSeleccionada = null;
        });
    }

