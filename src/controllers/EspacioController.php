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
        
        // CORRECCIÓN 1: Validar que sea Presidente leyendo del array 'vivienda'
       if (!isset($_SESSION['vivienda']['id_usuario']) || !isset($_SESSION['vivienda']['rol']) ||
            $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("HTTP/1.1 403 Forbidden");
            exit('Acceso denegado. Solo el Presidente puede acceder a esta sección.');
       }
    }

    // --------------------------------------------- VISTA GENERAL GESTION DE RESERVAS PRESIDENTE
    public function index() {
        // CORRECCIÓN 2: Extraer id_comunidad desde el array 'vivienda'
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        
        // CORRECCIÓN 3: Usar EspacioModel para traer los espacios (incluyendo el estado)
        $espacios = $this->espacioModel->getEspaciosByComunidad($id_comunidad);
        
        // La tabla de auditoría sí se trae desde ReservaModel
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