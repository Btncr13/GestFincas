<?php
require_once __DIR__ . '/../models/EspacioModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';

class EspacioController {
    private $espacioModel;
    private $reservaModel;

    public function __construct($pdo) {
        $this->espacioModel = new EspacioModel($pdo);
        $this->reservaModel = new ReservaModel($pdo);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        //Validar que sea Presidente 
       if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) ||
            $_SESSION['rol'] !== 'presidente') {
            header("HTTP/1.1 403 Forbidden");
            exit('Acceso denegado. Solo el Presidente puede acceder a esta sección.');}
    }

    // --------------------------------------------- VISTA GENERAL GESTION DE RESERVAS PRESIDENTE
    public function index() {
        $id_comunidad = $_SESSION['id_comunidad'];
        
        // Obtenemos los datos para nutrir la vista
        $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
        $todasLasReservas = $this->reservaModel->getTodasLasReservasComunidad($id_comunidad);
        
        require_once __DIR__ . '/../views/reservas/presidente.php';
    }

    // ---------------------------------------------- API: BLOQUEAR/DESBLOQUEAR UN ESPACIO
    public function toggleEstado() {
        header('Content-Type: application/json');
        
        $id_espacios_comunidad = $_POST['id_espacios_comunidad'] ?? null;
        $nuevo_estado = $_POST['estado'] ?? null; // 0 = Inactivo, 1 = Activo

        if ($this->espacioModel->bloquearEspacio($id_espacios_comunidad, $nuevo_estado)) {
            $msg = $nuevo_estado == 1 ? 'Espacio activado.' : 'Espacio bloqueado.';
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del espacio.']);
        }
    }
}
