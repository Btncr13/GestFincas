<?php
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/EspacioModel.php';

class ReservaController
{
    private $reservaModel;
    private $espacioModel;

    public function __construct($pdo)
    {
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
    public function index()
    {
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
            $this->reservaModel->actualizarReservasVencidas();

            // Cambiamos a la función que recupera los espacios junto con sus normas
            $espacios = $this->espacioModel->getEspaciosByComunidadConNormas($id_comunidad);
            $todasLasReservas = $this->reservaModel->getTodasLasReservasComunidad($id_comunidad);

            require_once __DIR__ . '/../views/reservas/presidente.php';
        } else {

            // --- Carga de datos para VECINO ---
            $espaciosDisponibles = $this->reservaModel->getEspaciosDisponibles($id_comunidad);
            $misReservas = $this->reservaModel->getReservasUsuario($id_usuario);

            require_once __DIR__ . '/../views/reservas/vecino.php';
        }
    }

    // =========================================================================
    // ENDPOINTS Y LÓGICA DE API
    // =========================================================================
    public function comprobarDisponibilidad()
    {
        header('Content-Type: application/json');

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $fecha = $_POST['fecha_reserva'] ?? null;
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;

        // Validamos si el tiempo solicitado es válido (futuro + margen)
        $esTiempoValido = $this->validarMargenTiempo($fecha, $hora_inicio);

        $espacios = $this->reservaModel->getEspaciosDisponibles($id_comunidad);

        $resultado = [];

        foreach ($espacios as $espacio) {
            // Si el tiempo no es válido, marcamos como "lleno" para deshabilitar la opción en el UI
            $lleno = !$esTiempoValido || !$this->reservaModel->hayCapacidad(
                $espacio['id_espacios_comunidad'],
                $fecha,
                $hora_inicio,
                $hora_fin,
                1
            );

            $resultado[] = [
                'id' => $espacio['id_espacios_comunidad'],
                'lleno' => $lleno
            ];
        }

        echo json_encode($resultado);
    }

    public function store()
    {
        header('Content-Type: application/json');

        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        // Recoger datos
        $data = [
            'id_usuario'            => $id_usuario,
            'id_espacios_comunidad' => $_POST['id_espacios_comunidad'] ?? null,
            'fecha_reserva'         => $_POST['fecha_reserva'] ?? null,
            'hora_inicio'           => $_POST['hora_inicio'] ?? null,
            'hora_fin'              => $_POST['hora_fin'] ?? null,
            'asistentes'            => isset($_POST['asistentes']) ? (int)$_POST['asistentes'] : 1
        ];

        // 1. Validación de campos obligatorios
        if (
            empty($data['id_espacios_comunidad']) ||
            empty($data['fecha_reserva']) ||
            empty($data['hora_inicio']) ||
            empty($data['hora_fin']) ||
            empty($data['asistentes'])
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ]);
            exit;
        }

        // 1.1 Validación: No permitir reservas en el pasado para el día de hoy
        if (!$this->validarMargenTiempo($data['fecha_reserva'], $data['hora_inicio'])) {
            echo json_encode([
                'success' => false,
                'message' => 'No es posible reservar en el pasado. Las reservas para hoy requieren 15 min de antelación.'
            ]);
            exit;
        }

        // 2. Validación de aforo (ANTES de crear la reserva)
        if (!$this->reservaModel->hayCapacidad(
            $data['id_espacios_comunidad'],
            $data['fecha_reserva'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['asistentes']
        )) {
            echo json_encode([
                'success' => false,
                'message' => 'Aforo completo'
            ]);
            exit;
        }

        // 3. Validación de cuotas
        $validacionCuota = $this->reservaModel->verificarCuotas($id_usuario, $data['fecha_reserva'], $data['id_espacios_comunidad']);
        if (!$validacionCuota['status']) {
            echo json_encode(['success' => false, 'message' => $validacionCuota['msg']]);
            exit;
        }

        // 4. Crear reserva
        $id = $this->reservaModel->crearReserva($data);

        if ($id) {

            $reserva = $this->reservaModel->getReservaById($id);

            echo json_encode([
                'success' => true,
                'message' => 'Reserva confirmada.',
                'reserva' => $reserva
            ]);
            exit;
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la reserva.'
        ]);
        exit;
    }

    public function getMisReservasAjax()
    {

        header('Content-Type: application/json');

        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        $reservas = $this->reservaModel->getReservasUsuario($id_usuario);

        echo json_encode([
            'success' => true,
            'reservas' => $reservas
        ]);
        exit;
    }

    public function getTodasLasReservasComunidadAjax()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        
        $this->reservaModel->actualizarReservasVencidas();
        $reservas = $this->reservaModel->getTodasLasReservasComunidad($id_comunidad);
        
        echo json_encode(['success' => true, 'reservas' => $reservas]);
        exit;
    }

    public function destroy()
    {
        // ob_clean() asegura que ningún warning o espacio en blanco previo rompa el JSON devuelto
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');

        $id_reservas = $_POST['id_reserva'] ?? null;
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        // ARQUITECTURA: Pasamos el "Modo de Vista" actual, no el rol absoluto del usuario.
        $modo_vista = $_SESSION['modo_vista'] ?? 'vecino';

        if (!$id_reservas) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se ha enviado el ID de la reserva.']);
            exit;
        }

        $eliminado = $this->reservaModel->eliminarReserva($id_reservas, $id_usuario, $modo_vista);

        if ($eliminado) {
            echo json_encode(['success' => true, 'message' => 'Reserva cancelada con éxito.']);
        } else {
            // Ya no usamos 403 duro aquí para que JS lo pueda leer bien, usamos 400
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No tienes permisos o la reserva ya no existe.']);
        }
        exit; // Asegura que no se imprima nada más después
    }

    public function getNormas($id_espacios_comunidad)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->reservaModel->getNormasByEspacio($id_espacios_comunidad)]);
    }

    /**
     * Valida que la fecha y hora de la reserva no sean pasadas 
     * y respeten el margen de cortesía de 15 minutos.
     */
    private function validarMargenTiempo($fecha, $horaInicio)
    {
        $hoy = date('Y-m-d');

        // 1. Bloquear cualquier fecha anterior a hoy
        if ($fecha < $hoy) return false;

        // 2. Si es hoy, validar el margen de 15 minutos
        if ($fecha === $hoy && $horaInicio) {
            $horaLimite = date('H:i', strtotime('+15 minutes'));
            // Si la hora de inicio es menor a la hora actual + 15 min, es inválido
            return $horaInicio >= $horaLimite;
        }

        return true;
    }
}
