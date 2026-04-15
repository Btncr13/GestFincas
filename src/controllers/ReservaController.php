<?php
require_once __DIR__ . '/../models/ReservaModel.php';

class ReservaController {
    private $reservaModel;

    public function __construct($pdo) {
        $this->reservaModel = new ReservaModel($pdo);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificación de sesión
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit();
        }
    }

    public function index() {
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $id_comunidad = $_SESSION['vivienda']['id_comunidad']; 
        
        $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
        $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

        require_once __DIR__ . '/../views/reservas/vecino.php';
    }

    // API: CREAR RESERVA
    public function store() {
        header('Content-Type: application/json');
        
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        
        // Mapeamos lo que llega por POST a las nuevas columnas de la BD
        $data = [
            'id_usuario'            => $id_usuario,
            'id_espacios_comunidad' => $_POST['id_espacio'] ?? null,
            'fecha_reserva'         => $_POST['fecha'] ?? null,
            'hora_inicio'           => $_POST['hora_inicio'] ?? null,
            'hora_fin'              => $_POST['hora_fin'] ?? null,
            'asistentes'            => isset($_POST['asistentes']) ? (int)$_POST['asistentes'] : 1
        ];

        // Validar Aforo Máximo
        $espacioInfo = $this->reservaModel->getEspacioById($data['id_espacios_comunidad']);
        if (!$espacioInfo) {
            echo json_encode(['success' => false, 'message' => 'El espacio seleccionado no existe.']);
            return;
        }

        if ($data['asistentes'] > $espacioInfo['max_personas']) {
            echo json_encode(['success' => false, 'message' => 'Supera el aforo máximo permitido (' . $espacioInfo['max_personas'] . ' personas).']);
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
            echo json_encode(['success' => false, 'message' => 'Error interno al crear la reserva.']);
        }
    }

    // API: ELIMINAR RESERVA
    public function destroy() {
        header('Content-Type: application/json');
        // El frontend probablemente siga enviando 'id_reserva'
        $id_reservas = $_POST['id_reserva'] ?? null; 
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        if ($this->reservaModel->eliminarReserva($id_reservas, $id_usuario)) {
            echo json_encode(['success' => true, 'message' => 'Reserva cancelada con éxito.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado o reserva no encontrada.']);
        }
    }

    // API: NORMAS
    public function getNormas($id_espacios_comunidad) {
        header('Content-Type: application/json');
        $normas = $this->reservaModel->getNormasByEspacio($id_espacios_comunidad);
        echo json_encode(['success' => true, 'data' => $normas]);
    }
}