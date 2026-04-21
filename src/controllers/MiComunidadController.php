<?php
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

    // 🟢 ACCIÓN: CREAR VIVIENDA (Adaptado de tu compañero)
    public function crearViviendaAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=miComunidad/index");
            exit;
        }

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $nombre_vivienda = trim($_POST['vivienda'] ?? '');
        $codigo = trim($_POST['codigo_vivienda'] ?? '');

        // Validación de formato
        if (!preg_match('/^Planta \d+-[A-Z0-9]+$/i', $nombre_vivienda)) {
            $_SESSION['error_vivienda'] = "El formato de vivienda debe ser 'Planta X-PisoLetra' (ej: Planta 2-1B)";
            header("Location: index.php?route=miComunidad/index");
            exit;
        }

        if ($this->miComunidadModel->crearViviendaConCodigo($id_comunidad, $nombre_vivienda, $codigo)) {
            header("Location: index.php?route=miComunidad/index&status=success");
        } else {
            header("Location: index.php?route=miComunidad/index&status=error");
        }
        exit;
    }

    // 🟢 ACCIÓN: MODIFICAR VIVIENDA
    public function modificarViviendaAction()
    {
        // 1. Verificamos que sea POST y que el usuario sea el presidente
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=miComunidad/index");
            exit;
        }

        // 2. Recogemos los datos enviados desde el formulario modal
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $id_vivienda = $_POST['id_vivienda_modificar'] ?? null;
        $nombre_vivienda = trim($_POST['nombre_vivienda'] ?? '');

        // 3. Validación estricta del formato "Planta X-PisoY"
        if (!preg_match('/^Planta \d+-[A-Z0-9]+$/i', $nombre_vivienda)) {
            $_SESSION['error_vivienda'] = "El formato de la vivienda debe ser 'Planta X-PisoLetra' (ej: Planta 2-1B)";
            header("Location: index.php?route=miComunidad/index");
            exit;
        }

        // 4. Llamamos al modelo para ejecutar el UPDATE en la BBDD
        if ($id_vivienda && $this->miComunidadModel->modificarVivienda($id_vivienda, $id_comunidad, $nombre_vivienda)) {
            // Éxito: volvemos pasando 'edit=success' para que salte el Toast verde en JS
            header("Location: index.php?route=miComunidad/index&edit=success");
        } else {
            // Error: volvemos pasando 'edit=error'
            header("Location: index.php?route=miComunidad/index&edit=error");
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
            header("Location: index.php?route=miComunidad/index&delete=success");
        } else {
            header("Location: index.php?route=miComunidad/index&delete=error");
        }
        exit;
    }
}    
?>