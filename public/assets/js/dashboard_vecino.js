document.addEventListener('DOMContentLoaded', function() {
    // Obtener fecha local de forma segura para evitar desajustes de zona horaria (UTC)
    const getLocalYYYYMMDD = (d) => {
        return d.getFullYear() + '-' + 
               String(d.getMonth() + 1).padStart(2, '0') + '-' + 
               String(d.getDate()).padStart(2, '0');
    };
    const hoy = getLocalYYYYMMDD(new Date());
    
    // 1. Lógica de la burbuja roja (Hoy)
    // Se oculta SOLO si el usuario ya confirmó asistencia hoy (desde la vista de reservas)
    if (localStorage.getItem('reserva_confirmada_' + hoy)) {
        document.querySelectorAll('.badge-reserva-hoy').forEach(burbuja => burbuja.classList.add('d-none'));
    }
    
    // Nota: La gestión de los Toasts ahora la hace de forma independiente 
    // el componente toast_notification.php para evitar conflictos.
});