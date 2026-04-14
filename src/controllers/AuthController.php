<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController
{
    private $usuarioModel;

    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
    }

    // --------------------------------------------------- FUNCIÓN QUE LLEVA A LOGIN/GET
    public function index()
    {
        return $this->login();
    }

    // --------------------------------------------------  MOSTRAR FORMULARIO LOGIN/GET
    public function login()
    {
        $mensajeExito = null;
        if (isset($_GET['registrado']) && $_GET['registrado'] === 'success') {
            $mensajeExito = "Registro completado con éxito. Ya puedes iniciar sesión.";
        }
        require "src/views/auth/login.php";
    }

    // --------------------------------------------------  MOSTRAR FORMULARIO REGISTRO/GET
    public function register()
    {
        require "src/views/auth/register.php";
    }

    // --------------------------------------------------- PROCESAR REGISTRO/POST
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

        // 1. Validar si el código existe y es válido
        $codigoData = $this->usuarioModel->validarCodigo($codigo);
        if (!$codigoData) {
            $error = "El código de vivienda no es válido o ya ha sido utilizado.";
            require "src/views/auth/register.php";
            return;
        }

        // 2. Preparar datos para el modelo
        $datos = [
            'id_vivienda' => $codigoData['id_vivienda'],
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'dni'         => $dni,
            'email'       => $email,
            'password'    => $password
        ];

        // 3. Ejecutar el registro
        $resultado = $this->usuarioModel->registrar($datos, $codigoData['id_codigo']);

        if ($resultado['success']) {
            header("Location: index.php?route=auth/login&registrado=success");
            exit;
        } else {
            $error = $resultado['message'];
            require "src/views/auth/register.php";
        }
    }

    // ---------------------------------------------------	PROCESAR LOGIN/POST
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

        // --- AQUÍ EMPIEZA LA MAGIA DE LOS ROLES ---
        $_SESSION['vivienda'] = $resultado['data'];

        // Comprobamos el rol que viene de la base de datos
        if ($_SESSION['vivienda']['rol'] === 'presidente') {
            // Si es presidente, lo mandamos a su panel
            header("Location: index.php?route=auth/panelpresi");
        } else {
            // Si es vecino (o cualquier otro), lo mandamos al panel normal
            header("Location: index.php?route=auth/panelvecino");
        }
        exit;
    }

    // ------------------------------------------------------- FUNCION LOGOUT
    public function logout()
    {
        session_destroy();
        header("Location: index.php?route=auth/login");
        exit;
    }

    // ------------------------------------------------------- FUNCIÓN DIRIGE A VISTAS DEL PANEL DE LA VIVIENDA
    public function panelvecino()
    {
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        // DATOS MOCK DEL DISEÑO DE FIGMA (Listos para conectar con BD en el futuro)
        $nombreVivienda = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda 1º A';
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Residencial Los Olivos';
        $direccion = trim(($_SESSION['vivienda']['calle'] ?? 'Calle Mayor') . ' ' . ($_SESSION['vivienda']['numero'] ?? '45'));
        if (empty(trim($direccion))) {
            $direccion = 'Calle Mayor 45, 28001 Madrid';
        }

        $ultimoComunicado = [
            'titulo' => 'Corte de agua programado',
            'contenido' => 'Se informa que mañana día 22 de marzo habrá un corte de agua de 09:00 a 14:00 por trabajos de mantenimiento en la red general. Rogamos disculpen las molestias.',
            'fechaPublicacion' => '21/03/2026',
            'prioridad' => 'importante' // Posibles: 'normal', 'importante', 'urgente'
        ];

        require "src/views/auth/panelvecino.php";
    }

    // ------------------------------------------------------- FUNCIÓN DIRIGE A VISTAS DEL PANEL DEL PRESIDENTE
    public function panelpresi()
    {
        // 1. Verificamos que haya iniciado sesión
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        // 2. SEGURIDAD: Verificamos que sea realmente presidente
        // (Para evitar que un vecino listillo ponga "panelpresi" en la URL)
        if ($_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/panelvecino");
            exit;
        }

        // Preparamos los datos para la vista
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        $direccion = trim($calle . ' ' . $numero);

        require "src/views/auth/panelpresi.php";
    }
}
