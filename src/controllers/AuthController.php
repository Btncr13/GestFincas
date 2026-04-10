<?php

require_once "../src/models/UsuarioModel.php"; // Ahora usamos UsuarioModel

class AuthController
{
    private $usuarioModel; // Renombramos la propiedad a usuarioModel

    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo); // Instanciamos UsuarioModel
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
        require "../src/views/auth/login.php";
    }

    // --------------------------------------------------  MOSTRAR FORMULARIO REGISTRO/GET
    public function register()
    {
        require "../src/views/auth/register.php";
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
            require "../src/views/auth/register.php";
            return;
        }

        // 1. Validar si el código existe y es válido
        $codigoData = $this->usuarioModel->validarCodigo($codigo);
        if (!$codigoData) {
            $error = "El código de vivienda no es válido o ya ha sido utilizado.";
            require "../src/views/auth/register.php";
            return;
        }

        // 2. Preparar datos para el modelo (id_vivienda viene del código)
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
            require "../src/views/auth/register.php";
        }
    }

    // ---------------------------------------------------	PROCESAR LOGIN/POST
    public function loginAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo "Método no permitido";
            return;
        }

        $nombreVivienda = trim($_POST['nombre_vivienda'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación de campos vacíos
        if ($nombreVivienda === '' || $email === '' || $password === '') {
            http_response_code(400); // Bad Request
            $error = "Debes completar todos los campos.";
            require "../src/views/auth/login.php";
            return;
        }

        // Llamada al modelo
        $resultado = $this->usuarioModel->login($nombreVivienda, $email, $password);

        if (!$resultado['success']) {
            http_response_code(401); // Unauthorized
            $error = $resultado['message'];
            require "../src/views/auth/login.php";
            return;
        }

        // Login correcto
        $_SESSION['vivienda'] = $resultado['data'];
        header("Location: index.php?route=auth/panelvecino");
        exit;
    }


    // ------------------------------------------------------- FUNCION LOGOUT SALIR DEL PANEL DE LA VIVIENDA
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

        require "../src/views/auth/panelvecino.php";
    }
}
