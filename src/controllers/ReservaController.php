<?php
require_once __DIR__ . '/../models/ReservaModel.php';

class ReservaController {
    private $reservaModel;

    public function __construct($pdo) {

        $this->reservaModel = new ReservaModel($pdo);
        
        // Verificación estricta de sesión (Seguridad Básica)
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php?route=auth/login"); // Redirigir si no está autenticado
            exit();
        }
    }

    // --------------------------------------- VISTA GENERAL DE LAS RESERVAS VECINO
    public function index() {
        $id_usuario = $_SESSION['id_usuario'];
        $id_comunidad = $_SESSION['id_comunidad']; // Asumimos que al loguearse se guardó su comunidad
        
        $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
        $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

        require_once __DIR__ . '/../views/reservas/vecino.php';
    }

    // ----------------------------------------- API: CREAR RESERVA SE RECOGEN DATOS DE VENTANA MODAL
    public function store() {
        header('Content-Type: application/json');
        
        $id_usuario = $_SESSION['id_usuario'];
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

        // 2. Proactivo: Validación de solapamiento (Lógica a implementar en el modelo)
        // if ($this->reservaModel->existeSolapamiento($data['id_espacio'], $data['fecha'], $data['hora_inicio'], $data['hora_fin'])) {
        //     echo json_encode(['success' => false, 'message' => 'El espacio ya está reservado en ese horario.']);
        //     return;
        // }

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
        $id_usuario = $_SESSION['id_usuario'];

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