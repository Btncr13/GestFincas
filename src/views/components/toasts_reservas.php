<!-- TOAST NOTIFICACIÓN DE RESERVAS -->
<?php if (isset($tieneReservaHoy) && $tieneReservaHoy): ?>
    <?php
    $toastKey     = 'reserva';
    $toastTitle   = '¡Tienes una reserva hoy!';
    $toastMsg     = 'Tienes una reserva programada para hoy.';
    $toastLink    = 'index.php?route=reserva/index';
    $toastBtnText = 'Ir a mis reservas';
    include 'src/views/components/toast_notification.php';
    ?>
<?php endif; ?>

<!-- TOAST NOTIFICACIÓN ANTICIPADA (MAÑANA) -->
<?php if (isset($tieneReservaManana) && $tieneReservaManana): ?>
    <?php
    $toastKey     = 'reserva_manana';
    $toastTitle   = 'Recordatorio de reserva';
    $toastMsg     = 'Mañana tienes una reserva programada.';
    $toastLink    = 'index.php?route=reserva/index';
    $toastBtnText = 'Ver detalles';
    include 'src/views/components/toast_notification.php';
    ?>
<?php endif; ?>