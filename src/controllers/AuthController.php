<?php

require_once "src/models/UsuarioModel.php";
require_once "src/models/EspacioModel.php";
require_once "src/models/VotacionModel.php";
require_once "src/models/ReservaModel.php";

class AuthController
{
    private $usuarioModel;
    private $votacionModel;
    private $reservaModel;
    private $espacioModel;
    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->votacionModel = new VotacionModel($pdo);
        $this->reservaModel = new ReservaModel($pdo);
        $this->espacioModel = new EspacioModel($pdo);
    }

    // 🟢 ENRUTAMIENTO INICIAL 🟢
    public function index()
    {
        return $this->login();
    }

    // 🟢 VISTA: FORMULARIO DE LOGIN 🟢
    public function login()
    {
        $mensajeExito = null;
        if (isset($_GET['registrado']) && $_GET['registrado'] === 'success') {
            $mensajeExito = "Registro completado con éxito. Ya puedes iniciar sesión.";
        }
        require "src/views/auth/login.php";
    }

    // 🟢 VISTA: FORMULARIO DE REGISTRO 🟢
    public function register()
    {
        require "src/views/auth/register.php";
    }

    // 🟢  PROCESAR EL REGISTRO (POST) 🟢
    public function registerAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=auth/login");
            exit;
        }

        // Recoger datos del formulario
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $dni       = trim($_POST['dni'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $codigo    = trim($_POST['codigo_vivienda'] ?? '');

        // Validación simple de campos obligatorios
        if (!$nombre || !$apellidos || !$dni || !$email || !$password || !$codigo) {
            $error = "Todos los campos son obligatorios.";
            require "src/views/auth/register.php";
            return;
        }

        // Validar si el código existe y es válido
        $codigoData = $this->usuarioModel->validarCodigo($codigo);
        if (!$codigoData) {
            $error = "El código de vivienda no es válido o ya ha sido utilizado.";
            require "src/views/auth/register.php";
            return;
        }

        // Preparar datos para el modelo
        $datos = [
            'id_vivienda' => $codigoData['id_vivienda'],
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'dni'         => $dni,
            'email'       => $email,
            'password'    => $password
        ];

        // Ejecutar el registro
        $resultado = $this->usuarioModel->registrar($datos, $codigoData['id_codigo']);

        if ($resultado['success']) {
            header("Location: index.php?route=auth/login&registrado=success");
            exit;
        } else {
            $error = $resultado['message'];
            require "src/views/auth/register.php";
        }
    }

    // 🟢 PROCESAR EL INICIO DE SESIÓN (POST) 🟢
    public function loginAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Método no permitido";
            return;
        }

        $nombreVivienda = trim($_POST['nombre_vivienda'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($nombreVivienda === '' || $email === '' || $password === '') {
            http_response_code(400);
            $error = "Debes completar todos los campos.";
            require "src/views/auth/login.php";
            return;
        }

        $resultado = $this->usuarioModel->login($nombreVivienda, $email, $password);

        if (!$resultado['success']) {
            http_response_code(401);
            $error = $resultado['message'];
            require "src/views/auth/login.php";
            return;
        }

        // Seguridad: Prevenir ataques de fijación de sesión regenerando el ID
        session_regenerate_id(true);

        // Los roles
        $_SESSION['vivienda'] = $resultado['data'];
        $_SESSION['modo_vista'] = $_SESSION['vivienda']['rol'];

        // Comprobamos el rol que viene de la base de datos
        if ($_SESSION['vivienda']['rol'] === 'presidente') {
            // Si es presidente, lo mandamos a su panel
            header("Location: index.php?route=auth/panelpresi");
        } else {
            // Si es vecino lo mandamos al panel normal
            header("Location: index.php?route=auth/panelvecino");
        }
        exit;
    }

    // 🟢 CERRAR SESIÓN 🟢
    public function logout()
    {
        session_destroy();
        header("Location: index.php?route=auth/login");
        exit;
    }

    // 🟢 HELPER: DATOS COMUNES PARA VISTAS 🟢
    private function getViewData()
    {
        // Auto-reparación de sesión global para todas las pantallas
        if (isset($_SESSION['vivienda']['id_usuario'])) {
            $sesionFresca = $this->usuarioModel->refrescarSesion($_SESSION['vivienda']['id_usuario']);
            if ($sesionFresca) {
                $_SESSION['vivienda'] = $sesionFresca;
            }
        }

        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'id_comunidad'    => $_SESSION['vivienda']['id_comunidad'] ?? null,
            'id_vivienda'     => $_SESSION['vivienda']['id_vivienda'] ?? null,
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino')
        ];
    }

    // 🟢 HELPER: RESPUESTAS JSON PARA AJAX 🟢
    private function jsonResponse($success, $message = null)
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    // 🟢 VISTA: PANEL DE VECINO 🟢
    public function panelvecino()
    {
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $_SESSION['modo_vista'] = 'vecino';

        // Extraemos automáticamente todas las variables comunes ($nombreComunidad, $direccion, etc.)
        extract($this->getViewData());

        // DATOS MOCK DE COMUNICACIONES
        $ultimoComunicado = [
            'titulo' => 'Corte de agua programado',
            'contenido' => 'Se informa que mañana día 22 de marzo habrá un corte de agua de 09:00 a 14:00 por trabajos de mantenimiento en la red general. Rogamos disculpen las molestias.',
            'fechaPublicacion' => '21/03/2026',
            'prioridad' => 'importante' // Posibles: 'normal', 'importante', 'urgente'
        ];

        // Obtener votaciones pendientes para el vecino
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        $votaciones = $this->votacionModel->getVotacionesActivas($id_comunidad);
        $votacionesPendientes = 0;
        foreach ($votaciones as $v) {
            // Solo contamos como pendiente si la votación no ha finalizado y el usuario no ha votado
            $fecha_limite = !empty($v['fecha_limite']) ? strtotime($v['fecha_limite']) : null;
            $esta_finalizada = $fecha_limite && $fecha_limite < time();
            if (!$esta_finalizada && !$this->votacionModel->haVotado($v['id_votacion'], $id_usuario)) {
                $votacionesPendientes++;
            }
        }

        // Comprobar si tiene reserva hoy para mostrar la burbuja en la card
        $tieneReservaHoy = $this->reservaModel->tieneReservaHoy($id_usuario);

        require "src/views/auth/panelvecino.php";
    }

    // 🟢 VISTA: PANEL DE PRESIDENTE 🟢
    public function panelpresi()
    {
        // Verificamos que haya iniciado sesión
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        // Verificamos que sea realmente presidente
        if ($_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/panelvecino");
            exit;
        }

        $_SESSION['modo_vista'] = 'presidente';

        extract($this->getViewData());

        // Para el presidente, también calculamos las votaciones pendientes de su voto personal
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $id_usuario = $_SESSION['vivienda']['id_usuario']; // El presidente también es un usuario
        $votaciones = $this->votacionModel->getVotacionesActivas($id_comunidad);
        $votacionesPendientes = 0;
        foreach ($votaciones as $v) {
            // Solo contamos como pendiente si la votación no ha finalizado y el usuario no ha votado
            $fecha_limite = !empty($v['fecha_limite']) ? strtotime($v['fecha_limite']) : null;
            $esta_finalizada = $fecha_limite && $fecha_limite < time();
            if (!$esta_finalizada && !$this->votacionModel->haVotado($v['id_votacion'], $id_usuario)) {
                $votacionesPendientes++;
            }
        }

        // Comprobar si tiene reserva hoy para mostrar la burbuja en la card
        $tieneReservaHoy = $this->reservaModel->tieneReservaHoy($id_usuario);

        // Obtener todos los espacios de la comunidad con sus normas
        $espacios = $this->espacioModel->getEspaciosByComunidadConNormas($id_comunidad);

        require "src/views/auth/panelpresi.php";
    }
}
