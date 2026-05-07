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
}
