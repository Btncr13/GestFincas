<footer class="footer-custom">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> <span class="fw-bold text-primary">GestFincas</span> — Sistema de gestión de comunidades.</p>
    </div>
</footer>

<?php
// Invocamos de forma global el Sistema de Notificaciones si la sesión está activa y el PDO existe
if (isset($_SESSION['vivienda']['id_usuario']) && isset($pdo)) {
    require_once __DIR__ . '/../../controllers/NotificationController.php';
    $notifController = new NotificationController($pdo);
    $rol_actual = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
    $notificaciones = $notifController->getNotificationsForView($_SESSION['vivienda']['id_usuario'], $_SESSION['vivienda']['id_comunidad'], $rol_actual);
    include __DIR__ . '/toast_notification.php';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>