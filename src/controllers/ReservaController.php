<?php
require_once __DIR__ . '/../models/ReservaModel.php';

class ReservaController {
    private $reservaModel;

 public function __construct($pdo) {
        $this->reservaModel = new ReservaModel($pdo);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // CORRECCIÓN: Comprobamos el array correcto
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit();
        }
    }

    public function index() {
        // CORRECCIÓN: Extraemos los IDs desde dentro de 'vivienda'
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $id_comunidad = $_SESSION['vivienda']['id_comunidad']; 
        
        $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
        $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

        require_once __DIR__ . '/../views/reservas/vecino.php';
    }
    // ----------------------------------------- API: CREAR RESERVA SE RECOGEN DATOS DE VENTANA MODAL
    public function store() {
        header('Content-Type: application/json');
        
        
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $data = [
            'id_usuario'  => $id_usuario,
            'id_espacio'  => $_POST['id_espacio'] ?? null,
            'fecha'       => $_POST['fecha'] ?? null,
            'hora_inicio' => $_POST['hora_inicio'] ?? null,
            'hora_fin'    => $_POST['hora_fin'] ?? null
        ];

        // 1. Validar Cuotas (1 al día, 3 a la semana)
        $validacionCuota = $this->reservaModel->verificarCuotas($id_usuario, $data['fecha']);
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

    // -------------------------------------------------------- API: ELIMINAR LA RESERVA
    public function destroy() {
        header('Content-Type: application/json');
        $id_reserva = $_POST['id_reserva'] ?? null;
        
        // CORRECCIÓN: Extraer el ID de usuario desde el array 'vivienda'
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        if ($this->reservaModel->eliminarReserva($id_reserva, $id_usuario)) {
            echo json_encode(['success' => true, 'message' => 'Reserva eliminada con éxito.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado o reserva no encontrada.']);
        }
    }

    // --------------------------------------------------------- API: NORMAS PARA CARD DINÁMICO
    public function getNormas($id_espacios_comunidad) {
        header('Content-Type: application/json');
        $normas = $this->reservaModel->getNormasByEspacio($id_espacios_comunidad);
        echo json_encode(['success' => true, 'data' => $normas]);
    }
}