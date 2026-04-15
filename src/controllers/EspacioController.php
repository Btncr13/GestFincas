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
        
        // RBAC: Validar que sea Presidente
        if (!isset($_SESSION['vivienda']['id_usuario']) || !isset($_SESSION['vivienda']['rol']) ||
            $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("HTTP/1.1 403 Forbidden");
            exit('Acceso denegado. Solo el Presidente puede acceder a esta sección.');
        }
    }

    public function index() {
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        
        $espacios = $this->espacioModel->getEspaciosByComunidad($id_comunidad);
        $todasLasReservas = $this->reservaModel->getTodasLasReservasComunidad($id_comunidad);
        
        require_once __DIR__ . '/../views/reservas/presidente.php';
    }

    // API: CREAR ESPACIO
    public function store() {
        header('Content-Type: application/json');
        
        $data = [
            'id_comunidad'   => $_SESSION['vivienda']['id_comunidad'],
            'nombre_espacio' => $_POST['nombre_espacio'] ?? '',
            'max_personas'   => (int)($_POST['max_personas'] ?? 1),
            'hora_apertura'  => $_POST['hora_apertura'] ?? '08:00:00',
            'hora_cierre'    => $_POST['hora_cierre'] ?? '22:00:00',
            'duracion_uso'   => (int)($_POST['duracion_uso'] ?? 60) // Ej: 60 minutos
        ];

        if ($this->espacioModel->crearEspacio($data)) {
            echo json_encode(['success' => true, 'message' => 'Espacio creado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al guardar el espacio.']);
        }
    }

    // API: MODIFICAR ESPACIO
    public function update() {
        header('Content-Type: application/json');
        
        $data = [
            'id_espacios_comunidad' => $_POST['id_espacios_comunidad'] ?? null,
            'id_comunidad'          => $_SESSION['vivienda']['id_comunidad'], // Seguridad
            'nombre_espacio'        => $_POST['nombre_espacio'] ?? '',
            'max_personas'          => (int)($_POST['max_personas'] ?? 1),
            'hora_apertura'         => $_POST['hora_apertura'] ?? '08:00:00',
            'hora_cierre'           => $_POST['hora_cierre'] ?? '22:00:00',
            'duracion_uso'          => (int)($_POST['duracion_uso'] ?? 60)
        ];

        if ($this->espacioModel->modificarEspacio($data)) {
            echo json_encode(['success' => true, 'message' => 'Espacio actualizado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al modificar el espacio.']);
        }
    }

    // API: BLOQUEAR/DESBLOQUEAR (Soft Delete / Inactivar)
    public function toggleEstado() {
        header('Content-Type: application/json');
        
        $id_espacios_comunidad = $_POST['id_espacios_comunidad'] ?? null;
        
        // En frontend probablemente enviabas estado: 0 o 1, pero ahora nuestro campo 
        // en la BD se llama 'bloqueado' donde 1 es Bloqueado y 0 es Activo.
        $bloqueado = $_POST['bloqueado'] ?? 0; 
        $motivo = $_POST['motivo'] ?? null;

        if ($this->espacioModel->bloquearEspacio($id_espacios_comunidad, $bloqueado, $motivo)) {
            $msg = $bloqueado == 1 ? 'Espacio bloqueado temporalmente.' : 'Espacio desbloqueado y operativo.';
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del espacio.']);
        }
    }
}