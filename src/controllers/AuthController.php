<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'libs/PHPMailer/src/Exception.php';
require_once 'libs/PHPMailer/src/PHPMailer.php';
require_once 'libs/PHPMailer/src/SMTP.php';

require_once "src/models/UsuarioModel.php";
require_once "src/models/EspacioModel.php";
require_once "src/models/VotacionModel.php";
require_once "src/models/ReservaModel.php";
require_once "src/models/ComunicacionesModel.php";
require_once "src/controllers/NotificationController.php";

class AuthController
{
    private $usuarioModel;
    private $votacionModel;
    private $reservaModel;
    private $espacioModel;
    private $comunicacionesModel;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->votacionModel = new VotacionModel($pdo);
        $this->reservaModel = new ReservaModel($pdo);
        $this->espacioModel = new EspacioModel($pdo);
        $this->comunicacionesModel = new ComunicacionesModel($pdo);
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
        unset($_SESSION['vivienda']);
        unset($_SESSION['modo_vista']);
        
        // Si no hay ninguna otra sesión activa (como la del superadmin), destruimos la sesión por completo
        if (!isset($_SESSION['superadmin'])) {
            session_destroy();
        }
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
        $tieneReservaManana = $this->reservaModel->tieneReservaManana($id_usuario);
        // Obtener comunicaciones pendientes
        $comunicacionesPendientes = $this->comunicacionesModel->contarNoLeidos($id_comunidad, $id_usuario);

        // Último comunicado real (No Mock)
        $listaComs = $this->comunicacionesModel->getComunicadosPorComunidad($id_comunidad);
        $ultimoComunicadoBD = !empty($listaComs) ? $listaComs[0] : null;

        // Instanciamos el controlador y preparamos las notificaciones
        $notificationController = new NotificationController($this->pdo);
        $notificaciones = $notificationController->getNotificationsForView($id_usuario, $id_comunidad, $_SESSION['modo_vista'] ?? 'vecino');

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
        $tieneReservaManana = $this->reservaModel->tieneReservaManana($id_usuario);

        // Obtener todos los espacios de la comunidad con sus normas
        $espacios = $this->espacioModel->getEspaciosByComunidadConNormas($id_comunidad);
        // Obtener comunicaciones reales de la BBDD para el panel del presidente
        $listaComs = $this->comunicacionesModel->getComunicadosPorComunidad($id_comunidad);

        // Instanciamos el controlador y preparamos las notificaciones
        $notificationController = new NotificationController($this->pdo);
        $notificaciones = $notificationController->getNotificationsForView($id_usuario, $id_comunidad, 'presidente');

        require "src/views/auth/panelpresi.php";
    }

    // 🟢 VISTA: PANTALLA RESTABLECER CONTRASEÑA (Solo visual) 🟢
    public function resetPassword()
    {
        require "src/views/auth/reset_password.php";
    }

    // 🟢 ENDPOINT AJAX: Enviar correo de recuperación
    public function enviarRecuperacionAjax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $email = $_POST['email'] ?? '';
        $token = $this->usuarioModel->generarTokenRecuperacion($email);

        if (!$token) {
            // Utilizamos el jsonResponse que ya tienes en el AuthController
            $this->jsonResponse(false, 'No existe ningún usuario registrado con ese correo.');
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'moisesmrobles@gmail.com'; // Tu cuenta real
            $mail->Password   = 'xonw eroz tnke xszg'; // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('moisesmrobles@gmail.com', 'GestFincas Seguridad');
            $mail->addAddress($email);

            // Ajusta "ComunidadVecinos/JR_M26..." si la ruta de tu proyecto cambia
            $enlaceReset = "http://localhost/ComunidadVecinos/JR_M26_ComunidadVecinos/index.php?route=auth/pantallaReset&token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = mb_encode_mimeheader('Recuperar contraseña - GestFincas', 'UTF-8');
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Recuperación de contraseña</h2>
                    <p>Has solicitado restablecer tu contraseña en GestFincas. Haz clic en el siguiente botón para crear una nueva (este enlace caducará en 1 hora):</p>
                    <br>
                    <a href='{$enlaceReset}' style='background-color: #221C35; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Restablecer Contraseña</a>
                    <br><br>
                    <p>Si no has solicitado este cambio, simplemente ignora este correo.</p>
                </div>
            ";
            $mail->send();
            
            $this->jsonResponse(true, 'Te hemos enviado un correo con las instrucciones.');
        } catch (Exception $e) {
            $this->jsonResponse(false, 'Error al enviar el correo. Inténtalo más tarde.');
        }
    }

    // 🟢 VISTA: Cargar la pantalla de Reset con validación
    public function pantallaReset() {
        $token = $_GET['token'] ?? '';
        $usuarioValido = $this->usuarioModel->validarTokenRecuperacion($token);
        
        if (!$usuarioValido) {
            // Variable que lee la vista reset_password.php que creamos ayer para mostrar el error
            $errorToken = "El enlace no es válido o ha caducado. Vuelve a solicitar la recuperación.";
        }
        require "src/views/auth/reset_password.php";
    }

    // 🟢 ENDPOINT AJAX: Guardar la nueva contraseña en Base de Datos
    public function actualizarPasswordAjax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $token = $_POST['token'] ?? '';
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        if ($pass1 !== $pass2) {
            $this->jsonResponse(false, 'Las contraseñas no coinciden.');
        }

        if (strlen($pass1) < 6) {
            $this->jsonResponse(false, 'La contraseña debe tener al menos 6 caracteres.');
        }

        $usuario = $this->usuarioModel->validarTokenRecuperacion($token);
        if (!$usuario) {
            $this->jsonResponse(false, 'El token ha caducado o no es válido.');
        }

        if ($this->usuarioModel->cambiarPasswordConToken($usuario['id_usuario'], $pass1)) {
            $this->jsonResponse(true, 'Contraseña actualizada correctamente.');
        } else {
            $this->jsonResponse(false, 'Error de base de datos al actualizar.');
        }
    }
}
