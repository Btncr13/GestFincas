<?php
require_once __DIR__ . '/../models/ReservaModel.php';
// IMPORTANTE: Requerimos el EspacioModel para poder cargar la vista del presidente
require_once __DIR__ . '/../models/EspacioModel.php'; 

class ReservaController {
    private $reservaModel;
    private $espacioModel;

    public function __construct($pdo) {
        $this->reservaModel = new ReservaModel($pdo);
        $this->espacioModel = new EspacioModel($pdo);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit();
        }
    }

    // =========================================================================
    // ENRUTADOR PRINCIPAL DE VISTAS (Basado en Modo Vista)
    // =========================================================================
    public function index() {
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $id_comunidad = $_SESSION['vivienda']['id_comunidad']; 
        
        // 1. DUALIDAD DE ROLES: Soporte para "Modo Vista" 
        $rol = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');

        // 2. VARIABLES COMUNES PARA TOPBAR Y SIDEBAR (Igual que en Votaciones)
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim($calle . ' ' . $numero);
        $rolReal         = $_SESSION['vivienda']['rol'] ?? 'vecino';

        // 3. DECISIÓN DE VISTA SEGÚN EL ROL ACTIVO
        if ($rol === 'presidente') {
            
            // --- Carga de datos para PRESIDENTE ---
            $espacios = $this->espacioModel->getEspaciosByComunidad($id_comunidad);
            $todasLasReservas = $this->reservaModel->getTodasLasReservasComunidad($id_comunidad);
            
            require_once __DIR__ . '/../views/reservas/presidente.php';

        } else {
            
            // --- Carga de datos para VECINO ---
            $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
            $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

            require_once __DIR__ . '/../views/reservas/vecino.php';
            
        }
    }

    // =========================================================================
    // API: VECINOS (Crear, Eliminar, Ver Normas)
    // =========================================================================
    
    public function store() {
        header('Content-Type: application/json');
        
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $data = [
            'id_usuario'            => $id_usuario,
            'id_espacios_comunidad' => $_POST['id_espacio'] ?? null,
            'fecha_reserva'         => $_POST['fecha'] ?? null,
            'hora_inicio'           => $_POST['hora_inicio'] ?? null,
            'hora_fin'              => $_POST['hora_fin'] ?? null,
            'asistentes'            => isset($_POST['asistentes']) ? (int)$_POST['asistentes'] : 1
        ];

        // Validar Aforo
        $espacioInfo = $this->reservaModel->getEspacioById($data['id_espacios_comunidad']);
        if (!$espacioInfo || $data['asistentes'] > $espacioInfo['max_personas']) {
            echo json_encode(['success' => false, 'message' => 'Supera el aforo máximo.']);
            return;
        }

        // Validar Cuotas (1 al día, 3 a la semana)
        $validacionCuota = $this->reservaModel->verificarCuotas($id_usuario, $data['fecha_reserva']);
        if (!$validacionCuota['status']) {
            echo json_encode(['success' => false, 'message' => $validacionCuota['msg']]);
            return;
        }

        if ($this->reservaModel->crearReserva($data)) {
            echo json_encode(['success' => true, 'message' => 'Reserva confirmada.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al crear la reserva.']);
        }
    }

    public function destroy() {
        header('Content-Type: application/json');
        $id_reservas = $_POST['id_reserva'] ?? null; 
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        if ($this->reservaModel->eliminarReserva($id_reservas, $id_usuario)) {
            echo json_encode(['success' => true, 'message' => 'Reserva cancelada con éxito.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
        }
    }

    public function getNormas($id_espacios_comunidad) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->reservaModel->getNormasByEspacio($id_espacios_comunidad)]);
    }
}