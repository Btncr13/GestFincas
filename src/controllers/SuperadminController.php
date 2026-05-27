<?php
require_once __DIR__ . '/../models/SuperAdminModel.php';

class SuperadminController
{
    private $model;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new SuperAdminModel($pdo);
    }

    public function login()
    {
        // Si ya está logueado como superadmin, redirigir directo al panel
        if (isset($_SESSION['superadmin'])) {
            header("Location: index.php?route=superadmin/index");
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Por favor, completa todos los campos.';
            } else {
                $result = $this->model->login($email, $password);

                if ($result['success']) {
                    // Usamos un array de sesión completamente aislado
                    $_SESSION['superadmin'] = $result['data'];
                    header("Location: index.php?route=superadmin/index");
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }

        // Cargar la vista de login exclusiva
        require_once __DIR__ . '/../views/superadmin/login.php';
    }

    public function logout()
    {
        unset($_SESSION['superadmin']);
        header("Location: index.php?route=superadmin/login");
        exit;
    }

    public function index()
    {
        if (!isset($_SESSION['superadmin'])) {
            header("Location: index.php?route=superadmin/login");
            exit;
        }

        $comunidades = $this->model->getComunidades();
        
        // Recoger mensajes de sesión para la vista
        $error = $_SESSION['sa_error'] ?? null;
        $exito = $_SESSION['sa_exito'] ?? null;
        unset($_SESSION['sa_error'], $_SESSION['sa_exito']);

        require_once __DIR__ . '/../views/superadmin/index.php';
    }

    public function avisos()
    {
        if (!isset($_SESSION['superadmin'])) {
            header("Location: index.php?route=superadmin/login");
            exit;
        }

        $avisos = $this->model->getAvisos();
        
        // Recoger mensajes de sesión para la vista
        $error = $_SESSION['sa_error'] ?? null;
        $exito = $_SESSION['sa_exito'] ?? null;
        unset($_SESSION['sa_error'], $_SESSION['sa_exito']);

        require_once __DIR__ . '/../views/superadmin/avisos.php';
    }

    public function storeComunidad()
    {
        if (!isset($_SESSION['superadmin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=superadmin/login");
            exit;
        }

        // Organizamos los arrays según los pide el modelo
        $datosComunidad = ['nombre' => trim($_POST['nombre_comunidad'] ?? '')];
        $datosDireccion = ['calle' => trim($_POST['calle'] ?? ''), 'numero' => trim($_POST['numero'] ?? '')];
        $datosPresidente = [
            'nombre' => trim($_POST['presi_nombre'] ?? ''), 'apellidos' => trim($_POST['presi_apellidos'] ?? ''),
            'dni' => trim($_POST['presi_dni'] ?? ''), 'email' => trim($_POST['presi_email'] ?? ''),
            'password' => trim($_POST['presi_password'] ?? ''), 'vivienda' => trim($_POST['presi_vivienda'] ?? 'Planta 1-A')
        ];

        $resultado = $this->model->crearComunidadYPresidente($datosComunidad, $datosDireccion, $datosPresidente);
        
        if ($resultado['success']) {
            $_SESSION['sa_exito'] = $resultado['message'];
        } else {
            $_SESSION['sa_error'] = $resultado['message'];
        }

        header("Location: index.php?route=superadmin/index");
        exit;
    }

    public function impersonate()
    {
        if (!isset($_SESSION['superadmin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $id_usuario = $_POST['id_usuario'] ?? 0;
        $usuario = $this->model->getUsuarioParaImpersonar($id_usuario);

        if ($usuario) {
            // Instanciamos la sesión de la App de Vecinos usando los datos extraídos
            $_SESSION['vivienda'] = $usuario;
            $_SESSION['modo_vista'] = $usuario['rol'];
            header("Location: index.php?route=auth/panelpresi");
            exit;
        } else {
            $_SESSION['sa_error'] = 'No se pudo iniciar sesión como este usuario.';
            header("Location: index.php?route=superadmin/index");
            exit;
        }
    }

    public function deleteComunidad()
    {
        if (!isset($_SESSION['superadmin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }
        $id = $_POST['id_comunidad'] ?? 0;
        
        if ($this->model->eliminarComunidad($id)) {
            $_SESSION['sa_exito'] = 'Comunidad eliminada correctamente del sistema.';
        } else {
            $_SESSION['sa_error'] = 'No se puede eliminar: la comunidad tiene datos asociados (vecinos, foros, incidencias). Debes vaciarla primero o configurar tu BD con ON DELETE CASCADE.';
        }
        header("Location: index.php?route=superadmin/index");
        exit;
    }

    public function storeAviso()
    {
        if (!isset($_SESSION['superadmin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=superadmin/login");
            exit;
        }

        $titulo = $_POST['titulo'] ?? '';
        $mensaje = $_POST['mensaje'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? '';

        if ($this->model->crearAviso($titulo, $mensaje, $fecha_inicio, $fecha_fin)) {
            $_SESSION['sa_exito'] = 'Aviso de mantenimiento programado correctamente.';
        } else {
            $_SESSION['sa_error'] = 'Hubo un error al guardar el aviso.';
        }

        header("Location: index.php?route=superadmin/avisos");
        exit;
    }

    public function deleteAviso()
    {
        if (!isset($_SESSION['superadmin']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }
        $id = $_POST['id_aviso'] ?? 0;
        $this->model->eliminarAviso($id);
        $_SESSION['sa_exito'] = 'Aviso eliminado del sistema.';
        header("Location: index.php?route=superadmin/avisos");
        exit;
    }

    // ENDPOINT PÚBLICO: Devuelve si hay un aviso activo en este mismo momento.
    public function getAvisoActivoAjax()
    {
        // Limpiamos cualquier salida previa y devolvemos JSON puro
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $aviso = $this->model->getAvisoActivoGlobal();
        echo json_encode(['success' => (bool)$aviso, 'aviso' => $aviso]);
        exit;
    }
}
?>