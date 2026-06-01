
document.addEventListener('DOMContentLoaded', function() {
    // 1. Al cargar la página, inicializar botones (Toggle State) de Confirmación
    document.querySelectorAll('.btn-confirmar-reserva').forEach(btn => {
        const fecha = btn.getAttribute('data-fecha');
        if (localStorage.getItem('reserva_confirmada_' + fecha)) {
            btn.classList.replace('btn-success', 'btn-warning');
            btn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Anular Confirmación';
            
            // Si al cargar la página ya está confirmada, ocultamos la burbuja roja del dashboard
            document.querySelectorAll('.badge-reserva-hoy').forEach(b => b.classList.add('d-none'));
        }
    });

    // 2. Delegación de eventos para Confirmar/Anular y Eliminar
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-confirmar-reserva');
        if (btn) {
            const fecha = btn.getAttribute('data-fecha');
            const isConfirmada = localStorage.getItem('reserva_confirmada_' + fecha);

            if (isConfirmada) {
                // Revertir a no confirmada (limpiar memoria)
                localStorage.removeItem('reserva_confirmada_' + fecha);
                document.querySelectorAll('.btn-confirmar-reserva[data-fecha="' + fecha + '"]').forEach(b => {
                    b.classList.replace('btn-warning', 'btn-success');
                    b.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirmar Asistencia';
                });
            
            // Volver a mostrar la burbuja roja
            document.querySelectorAll('.badge-reserva-hoy').forEach(b => b.classList.remove('d-none'));
            } else {
                // Marcar como confirmada (guardar memoria)
                localStorage.setItem('reserva_confirmada_' + fecha, 'true');
                document.querySelectorAll('.btn-confirmar-reserva[data-fecha="' + fecha + '"]').forEach(b => {
                    b.classList.replace('btn-success', 'btn-warning');
                    b.innerHTML = '<i class="bi bi-x-circle me-1"></i> Anular Confirmación';
                });
            
            // Ocultar la burbuja roja
            document.querySelectorAll('.badge-reserva-hoy').forEach(b => b.classList.add('d-none'));
            }
        }

        // 3. Limpiar caché automáticamente al pulsar Eliminar
        const btnEliminar = e.target.closest('.btn-eliminar-reserva');
        if (btnEliminar) {
            const fecha = btnEliminar.getAttribute('data-fecha');
            if (fecha) {
                localStorage.removeItem('reserva_confirmada_' + fecha);
            }
        }
    });
});

 function switchMainTab(seccion) {
    const isRes = seccion === 'reservas';
    const btnRes = document.getElementById('btn-sec-reservas');
    const btnEsp = document.getElementById('btn-sec-espacios');
    
    // Toggle de clases para los botones
    btnRes.classList.toggle('active', isRes);
    btnRes.classList.toggle('text-muted', !isRes);
    
    btnEsp.classList.toggle('active', !isRes);
    btnEsp.classList.toggle('text-muted', isRes);
    
    document.getElementById('vista-reservas').classList.toggle('d-none', !isRes);
    document.getElementById('vista-espacios').classList.toggle('d-none', isRes);
}

        function switchSubTab(estado) {
            const isAct = estado === 'activas';
            document.getElementById('lista-activas').classList.toggle('d-none', !isAct);
            document.getElementById('lista-inactivas').classList.toggle('d-none', isAct);
        }
      