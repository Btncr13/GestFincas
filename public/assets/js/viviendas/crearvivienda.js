<script>
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
    // Si ya está seleccionado, lo desmarcamos
    if (elemento.classList.contains('border-primary')) {
        elemento.classList.remove('border-primary', 'shadow-sm');
        elemento.style.borderColor = 'transparent';
        viviendaSeleccionada = null;
        return;
    }

    // Limpiar otras selecciones
    document.querySelectorAll('.vivienda-badge').forEach(el => {
        el.classList.remove('border-primary', 'shadow-sm');
        el.style.borderColor = 'transparent';
    });

    // Marcar selección actual
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
    // Resetear selección al cerrar el modal
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

    <?php if (isset($_GET['status'])): ?>
        const status = '<?= $_GET['status'] ?>';
        if (status === 'success') {
            alert('Vivienda creada y código generado con éxito.');
        } else {
            alert('Error al procesar el alta de la vivienda.');
        }
        // Limpiar URL
        window.history.replaceState({}, document.title, "index.php?route=auth/usuarios");
    <?php endif; ?>
    
    <?php if (isset($_GET['delete'])): ?>
        const delStatus = '<?= $_GET['delete'] ?>';
        if (delStatus === 'success') {
            alert('Vivienda eliminada correctamente.');
        } else {
            alert('Error al intentar eliminar la vivienda.');
        }
        window.history.replaceState({}, document.title, "index.php?route=auth/usuarios");
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_vivienda'])): ?>
        alert('<?= $_SESSION['error_vivienda'] ?>');
        <?php unset($_SESSION['error_vivienda']); ?>
    <?php endif; ?>
});