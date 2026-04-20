<?php
require_once "src/models/MiComunidadModel.php";

class MiComunidadController
{
    private $miComunidadModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Comprobar si el usuario está logueado
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        
        $this->miComunidadModel = new MiComunidadModel($pdo);
    }

    public function index()
    {
        // Seguridad: Solo el presidente puede acceder
        if ($_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/panelvecino");
            exit;
        }

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        
        // Obtenemos los usuarios usando tu nuevo modelo
        $usuariosData = $this->miComunidadModel->getUsuariosPorComunidad($id_comunidad);

        // ======== VARIABLES NECESARIAS PARA TOPBAR Y SIDEBAR ========
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim($calle . ' ' . $numero);
        $rolReal         = $_SESSION['vivienda']['rol'] ?? 'vecino';
        $rol             = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        // ===========================================================

        // Cargar tu nueva vista (Ajusta el nombre de la carpeta si es distinto)
        require "src/views/micomunidad/micomunidad.php";
    }
}
?>