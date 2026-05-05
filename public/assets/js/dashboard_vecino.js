document.addEventListener('DOMContentLoaded', function() {
    // Obtener fecha local de forma segura para evitar desajustes de zona horaria (UTC)
    const getLocalYYYYMMDD = (d) => {
        return d.getFullYear() + '-' + 
               String(d.getMonth() + 1).padStart(2, '0') + '-' + 
               String(d.getDate()).padStart(2, '0');
    };
    const hoy = getLocalYYYYMMDD(new Date());
    
    const dManana = new Date();
    dManana.setDate(dManana.getDate() + 1);
    const mananaStr = getLocalYYYYMMDD(dManana);

    // 1. Lógica de la burbuja roja (Hoy)
    // Se oculta si el usuario ya confirmó asistencia hoy
    if (localStorage.getItem('reserva_confirmada_' + hoy)) {
        const burbuja = document.getElementById('burbuja-reservas-hoy');
        if (burbuja) burbuja.classList.add('d-none');
    }

    // 2. Lógica de Toasts (Hoy y Mañana)
    const toastHoy = document.getElementById('toast_reserva');
    const toastManana = document.getElementById('toast_reserva_manana');

    // Si ya se vio el toast de hoy, no lo mostramos (vía Bootstrap o eliminando el nodo)
    if (localStorage.getItem('reserva_hoy_' + hoy) && toastHoy) {
        toastHoy.remove();
    }

    // Si ya se vio el toast de mañana, no lo mostramos
    if (localStorage.getItem('reserva_manana_' + mananaStr) && toastManana) {
        toastManana.remove();
    }

    // 3. Capturar el cierre de los toasts para guardar en localStorage
    document.body.addEventListener('hidden.bs.toast', function(e) {
        const targetId = e.target.id;
        if (targetId === 'toast_reserva') {
            localStorage.setItem('reserva_hoy_' + hoy, 'true');
        } else if (targetId === 'toast_reserva_manana') {
            localStorage.setItem('reserva_manana_' + mananaStr, 'true');
        }
    });
});