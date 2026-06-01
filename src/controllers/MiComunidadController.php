<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Rutas directas partiendo desde la raíz del proyecto (donde está el index.php)
require_once 'libs/PHPMailer/src/Exception.php';
require_once 'libs/PHPMailer/src/PHPMailer.php';
require_once 'libs/PHPMailer/src/SMTP.php';

require_once "src/models/UsuarioModel.php";
require_once "src/models/MiComunidadModel.php";

class MiComunidadController
{
    private $usuarioModel;
    private $miComunidadModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $this->usuarioModel = new UsuarioModel($pdo);
        $this->miComunidadModel = new MiComunidadModel($pdo); // <-- 2. Comprueba que haces el "new"
    }

    // HELPER: Extraer datos comunes para Topbar y Sidebar
    private function getViewData()
    {
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

    // 🟢 VISTA PRINCIPAL
    public function index()
    {
        // Seguridad: Solo el presidente puede acceder
        if ($_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/panelvecino");
            exit;
        }

        $_SESSION['modo_vista'] = 'presidente';
        extract($this->getViewData());

        // Obtenemos los usuarios con el método fusionado
        $vecinos = $this->miComunidadModel->getUsuariosPorComunidad($id_comunidad);

        // Cargamos nuestra nueva vista
        require "src/views/micomunidad/micomunidad.php";
    }

    // 🟢 ACCIÓN: CREAR VIVIENDA Y ENVIAR EMAIL
    public function crearViviendaAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=micomunidad/index");
            exit;
        }

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $nombre_vivienda = trim($_POST['vivienda'] ?? '');
        $codigo = trim($_POST['codigo_vivienda'] ?? '');
        $email_vecino = trim($_POST['email_vecino'] ?? ''); // Capturamos el email del modal [4]

        // Validación de formato
        if (!preg_match('/^Planta\s+(Bajo|[1-9]|[12][0-9]|30)-([1-9]|10|[A-M])$/i', $nombre_vivienda)) {
            $_SESSION['error_vivienda'] = "Formato: Planta [1-30 o Bajo]-[1-10 o A-M]. Ej: Planta 1-C";
            header("Location: index.php?route=micomunidad/index");
            exit;
        }

        if ($this->miComunidadModel->crearViviendaConCodigo($id_comunidad, $nombre_vivienda, $codigo)) {

            // --- INICIO DE LÓGICA PHPMAILER ---
            if (!empty($email_vecino)) {
                $mail = new PHPMailer(true);

                try {
                    // Configuración del servidor SMTP (Ejemplo usando Mailtrap para pruebas locales o Gmail)
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; // Cambia esto por tu host SMTP (ej. sandbox.smtp.mailtrap.io) [3, 5]
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'moisesmrobles@gmail.com'; // Tu usuario SMTP [5]
                    $mail->Password   = 'xonw eroz tnke xszg'; // Tu contraseña SMTP 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Remitente y Destinatario
                    $mail->setFrom('moisesmrobles@gmail.com', 'GestFincas Administración');
                    $mail->addAddress($email_vecino);

                    // Generar enlace dinámico (OJO: Adapta "localhost/tu_carpeta" a la ruta de tu proyecto local)
                    $enlaceRegistro = "http://localhost/ComunidadVecinos/JR_M26_ComunidadVecinos/index.php?route=auth/register&codigo=" . urlencode($codigo);

                    // Contenido del Correo
                    $mail->isHTML(true);
                    $mail->Subject = mb_encode_mimeheader('Invitación a tu nueva comunidad - GestFincas', 'UTF-8');
                    $mail->Body    = "
                        <h2>¡Hola, nuevo vecino!</h2>
                        <p>Tu presidente te ha dado de alta en la plataforma <b>GestFincas</b> para la vivienda <b>{$nombre_vivienda}</b>.</p>
                        <p>Para completar tu registro y acceder a la plataforma, por favor haz clic en el siguiente enlace. Tu código de seguridad se rellenará automáticamente:</p>
                        <br>
                        <a href='{$enlaceRegistro}' style='background-color: #5CB244; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Completar Registro</a>
                        <br><br>
                        <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                        <p><a href='{$enlaceRegistro}'>{$enlaceRegistro}</a></p>
                        <p>Tu código de vivienda manual es: <b>{$codigo}</b></p>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("El correo no pudo ser enviado. Error de PHPMailer: {$mail->ErrorInfo}");
                }
            }
            // --- FIN DE LÓGICA PHPMAILER ---

            header("Location: index.php?route=micomunidad/index");
        } else {
            header("Location: index.php?route=micomunidad/index");
        }
        exit;
    }

    // 🟢 ACCIÓN: MODIFICAR VIVIENDA
    public function modificarViviendaAction()
    {
        // 1. Verificamos que sea POST y que el usuario sea el presidente
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=micomunidad/index");
            exit;
        }

        // 2. Recogemos los datos enviados desde el formulario modal
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $id_vivienda = $_POST['id_vivienda_modificar'] ?? null;
        $nombre_vivienda = trim($_POST['nombre_vivienda'] ?? '');

        // 3. Validación estricta del formato "Planta X-PisoY"
        if (!preg_match('/^Planta\s+(Bajo|[1-9]|[12][0-9]|30)-([1-9]|10|[A-M])$/i', $nombre_vivienda)) {
            $_SESSION['error_vivienda'] = "Formato: Planta [1-30 o Bajo]-[1-10 o A-M]. Ej: Planta 1-C";
            header("Location: index.php?route=micomunidad/index");
            exit;
        }

        // 4. Llamamos al modelo para ejecutar el UPDATE en la BBDD
        if ($id_vivienda && $this->miComunidadModel->modificarVivienda($id_vivienda, $id_comunidad, $nombre_vivienda)) {
            // Éxito: volvemos pasando 'edit=success' para que salte el Toast verde en JS
            header("Location: index.php?route=micomunidad/index");
        } else {
            // Error: volvemos pasando 'edit=error'
            header("Location: index.php?route=micomunidad/index");
        }
        exit;
    }

    // 🟢 ACCIÓN: ELIMINAR VIVIENDA (Y USUARIO) 🟢
    public function eliminarViviendaAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/usuarios");
            exit;
        }

        $id_vivienda = $_POST['id_vivienda_eliminar'] ?? null;

        if ($id_vivienda && $this->miComunidadModel->eliminarViviendaCompleta($id_vivienda)) {
            header("Location: index.php?route=micomunidad/index");
        } else {
            header("Location: index.php?route=micomunidad/index");
        }
        exit;
    }
}
