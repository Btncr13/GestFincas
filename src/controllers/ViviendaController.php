<?php

require_once "src/models/UsuarioModel.php";
require_once "src/models/ViviendaModel.php";

class ViviendaController
{
    private $usuarioModel;
    private $viviendaModel;

    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->viviendaModel = new ViviendaModel($pdo);
    }

    // 🟢 HELPER: DATOS COMUNES PARA LAS VISTAS 🟢
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

    // 🟢 VISTA: MI COMUNIDAD (GESTIÓN DE VECINOS) 🟢
    public function usuarios()
    {
        // Verificamos sesión y rol de presidente
        if (!isset($_SESSION['vivienda']) || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $_SESSION['modo_vista'] = 'presidente';

        // Obtenemos datos comunes para los componentes (topbar/sidebar)
        extract($this->getViewData());

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $vecinos = $this->usuarioModel->getUsuariosByComunidad($id_comunidad);

        require "src/views/auth/usuarios.php";
    }

    // 🟢 ACCIÓN: CREAR VIVIENDA 🟢
    public function crearViviendaAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['vivienda']['rol'] !== 'presidente') {
            header("Location: index.php?route=auth/usuarios");
            exit;
        }

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $nombre_vivienda = trim($_POST['vivienda'] ?? '');
        $codigo = trim($_POST['codigo_vivienda'] ?? '');
        // El email se podría usar para enviar una invitación automática en el futuro

        // Validación de formato "Planta X-PisoY"
        if (!preg_match('/^Planta \d+-[A-Z0-9]+$/i', $nombre_vivienda)) {
            $_SESSION['error_vivienda'] = "El formato de vivienda debe ser 'Planta X-PisoLetra' (ej: Planta 2-1B)";
            header("Location: index.php?route=auth/usuarios");
            exit;
        }

        if ($this->viviendaModel->crearViviendaConCodigo($id_comunidad, $nombre_vivienda, $codigo)) {
            header("Location: index.php?route=auth/usuarios&status=success");
        } else {
            header("Location: index.php?route=auth/usuarios&status=error");
        }
        exit;
    }

    // 🟢 AJAX: VERIFICAR CÓDIGO DE REGISTRO 🟢
    public function verificarCodigoAjax()
    {
        ob_clean(); // Limpia el buffer para asegurar un JSON válido sin basura
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Código vacío']);
            exit;
        }

        $detalles = $this->usuarioModel->getDetallesCodigo($codigo);
        
        header('Content-Type: application/json');
        echo json_encode($detalles ? ['success' => true, 'data' => $detalles] : ['success' => false, 'message' => 'Código no válido o ya usado']);
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

        if ($id_vivienda && $this->viviendaModel->eliminarViviendaCompleta($id_vivienda)) {
            header("Location: index.php?route=auth/usuarios&delete=success");
        } else {
            header("Location: index.php?route=auth/usuarios&delete=error");
        }
        exit;
    }
}