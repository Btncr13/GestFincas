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

    // Actualizar reservas vencidas antes de mostrarlas
    $this->reservaModel->actualizarReservasVencidas();

    $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
    $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

    require_once __DIR__ . '/../views/reservas/vecino.php';
  }


    // API: CREAR RESERVA
    public function store() {
    header('Content-Type: application/json');

    $id_usuario = $_SESSION['vivienda']['id_usuario'];

    // Datos recibidos del formulario
    $data = [
        'id_usuario'            => $id_usuario,
        'id_espacios_comunidad' => $_POST['id_espacio'] ?? null,
        'fecha_reserva'         => $_POST['fecha'] ?? null,
        'hora_inicio'           => $_POST['hora_inicio'] ?? null,
        'hora_fin'              => $_POST['hora_fin'] ?? null,
        'asistentes'            => isset($_POST['asistentes']) ? (int)$_POST['asistentes'] : 1
    ];

    // Validar que el espacio existe
    $espacioInfo = $this->reservaModel->getEspacioById($data['id_espacios_comunidad']);
    if (!$espacioInfo) {
        echo json_encode(['success' => false, 'message' => 'El espacio seleccionado no existe.']);
        return;
    }

    // -----------------------------------------
    // VALIDAR FECHA (hoy → hoy + 14 días)
    // -----------------------------------------
    $fecha = $data['fecha_reserva'];
    $hoy = date('Y-m-d');
    $max = date('Y-m-d', strtotime('+14 days'));

    if ($fecha < $hoy || $fecha > $max) {
        echo json_encode([
            'success' => false,
            'message' => 'Solo puedes reservar con un máximo de 14 días de antelación.'
        ]);
        return;
    }
    // -----------------------------------------

    // Validar aforo máximo
    if ($data['asistentes'] > $espacioInfo['max_personas']) {
        echo json_encode([
            'success' => false,
            'message' => 'Supera el máximo permitido (' . $espacioInfo['max_personas'] . ' personas).'
        ]);
        return;
    }

    // Validar cuotas (1 al día, 3 a la semana)
    $validacionCuota = $this->reservaModel->verificarCuotas($id_usuario, $data['fecha_reserva']);
    if (!$validacionCuota['status']) {
        echo json_encode(['success' => false, 'message' => $validacionCuota['msg']]);
        return;
    }

    // Crear reserva y obtener ID insertado
    $idReserva = $this->reservaModel->crearReserva($data);

    if (!$idReserva) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno al crear la reserva.']);
        return;
    }

    // Obtener datos completos de la reserva recién creada
    $reserva = $this->reservaModel->getReservaById($idReserva);

    // Obtener normas del espacio
    $normas = $this->reservaModel->getNormasByEspacio($data['id_espacios_comunidad']);

    // Respuesta final al frontend
    echo json_encode([
        'success' => true,
        'message' => 'Reserva creada con éxito.',
        'reserva' => [
            'id_reservas'    => $idReserva,
            'espacio'        => $reserva['nombre_espacio'],
            'fecha_reserva'  => $reserva['fecha_reserva'],
            'hora_inicio'    => $reserva['hora_inicio'],
            'hora_fin'       => $reserva['hora_fin'],
            'asistentes'     => $reserva['asistentes'],
            'normas'         => $normas
        ]
    ]);
    exit;
  }

   

    // API: ELIMINAR RESERVA
    public function destroy() {
        
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
          header('Content-Type: application/json');
       }
        // El frontend probablemente siga enviando 'id_reserva'
        $id_reservas = $_POST['id_reserva'] ?? null; 
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        if ($this->reservaModel->eliminarReserva($id_reservas, $id_usuario)) {
            echo json_encode(['success' => true, 'message' => 'Reserva cancelada con éxito.']);
            exit;
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado o reserva no encontrada.']);
            exit;
        }
    }

    // API: NORMAS
    public function getNormas($id_espacios_comunidad) {
        header('Content-Type: application/json');
        $normas = $this->reservaModel->getNormasByEspacio($id_espacios_comunidad);
        echo json_encode(['success' => true, 'data' => $normas]);
    }
}