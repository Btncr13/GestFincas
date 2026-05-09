<?php
require_once "src/models/UsuarioModel.php";

class UsuarioController
{
    private $usuarioModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Verificación de sesión
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $this->usuarioModel = new UsuarioModel($pdo);
    }

    /**
     * Muestra la vista de perfil con los datos del usuario actual.
     */
    public function perfil()
    {
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        // Utilizamos refrescarSesion para obtener todos los datos actualizados (incluyendo vivienda y comunidad)
        $userData = $this->usuarioModel->refrescarSesion($id_usuario);

        if (!$userData) {
            header("Location: index.php?route=auth/logout");
            exit;
        }

        // Variables requeridas por topbar.php y sidebar.php
        $nombreComunidad = $userData['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $userData['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim(($userData['calle'] ?? '') . ' ' . ($userData['numero'] ?? ''));
        $rolReal         = $userData['rol'] ?? 'vecino';
        $rol             = $_SESSION['modo_vista'] ?? $rolReal;
        $usuario         = $userData;

        require_once "src/views/usuario/perfil.php";
    }

    /**
     * Muestra la vista para cambiar la contraseña.
     */
    public function cambiarPassword()
    {
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $userData = $this->usuarioModel->refrescarSesion($id_usuario);

        // Datos comunes para el layout (topbar/sidebar)
        $nombreComunidad = $userData['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $userData['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim(($userData['calle'] ?? '') . ' ' . ($userData['numero'] ?? ''));
        $rolReal         = $userData['rol'] ?? 'vecino';
        $rol             = $_SESSION['modo_vista'] ?? $rolReal;

        require_once "src/views/usuario/cambiar_password.php";
    }

    /**
     * Procesa la petición de cambio de contraseña con validación en Back-end.
     */
    public function updatePasswordAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=usuario/perfil");
            exit;
        }

        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $passActual = $_POST['password_actual'] ?? '';
        $passNueva  = $_POST['password_nueva'] ?? '';

        // Validación de campos vacíos
        if (empty($passActual) || empty($passNueva)) {
            $_SESSION['error_msg'] = "Todos los campos son obligatorios.";
            header("Location: index.php?route=usuario/cambiarPassword");
            exit;
        }

        // Seguridad: Verificar que la contraseña actual sea correcta
        $hashActual = $this->usuarioModel->getPasswordHash($id_usuario);
        if (!$hashActual || !password_verify($passActual, $hashActual)) {
            $_SESSION['error_msg'] = "La contraseña actual introducida no es correcta.";
            header("Location: index.php?route=usuario/cambiarPassword");
            exit;
        }

        // Ejecutar actualización
        if ($this->usuarioModel->actualizarPassword($id_usuario, $passNueva)) {
            // Éxito: Redirigimos al logout para forzar nuevo inicio de sesión
            header("Location: index.php?route=auth/logout");
            exit;
        } else {
            $_SESSION['error_msg'] = "Error interno al intentar actualizar la contraseña.";
            header("Location: index.php?route=usuario/cambiarPassword");
            exit;
        }
    }
}
