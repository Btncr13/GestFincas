<?php

require_once "src/models/UsuarioModel.php";
require_once "src/models/ReunionModel.php";

class ReunionController
{
    private $usuarioModel;
    private $reunionModel;

    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->reunionModel = new ReunionModel($pdo);
    }

    // 🟢 HELPER: DATOS COMUNES PARA LAS VISTAS 🟢
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

    // 🟢 HELPER: RESPUESTAS JSON PARA AJAX 🟢
    private function jsonResponse($success, $message = null)
    {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    // 🟢 VISTA PRINCIPAL: LISTADO DE REUNIONES 🟢
    public function reuniones()
    {
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        extract($this->getViewData());

        $reunionesData = [];
        if ($id_comunidad) {
            $reunionesData = $this->reunionModel->getReunionesComunidad($id_comunidad);
        }

        require "src/views/reunion/reuniones.php";
    }

    // 🟢 API ENDPOINT: CREAR REUNIÓN 🟢
    public function crearReunionAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;
        if (!$id_comunidad || $_SESSION['vivienda']['rol'] !== 'presidente') {
            $this->jsonResponse(false, 'No autorizado');
        }

        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $lugar = $_POST['lugar'] ?? '';
        $orden_del_dia = $_POST['orden_del_dia'] ?? '[]'; 

        $success = $this->reunionModel->crearReunion($id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $orden_del_dia);
        $this->jsonResponse($success, $success ? null : 'Error al convocar la reunión');
    }

    // 🟢 API ENDPOINT: EDITAR REUNIÓN 🟢
    public function editarReunionAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;
        if (!$id_comunidad || $_SESSION['vivienda']['rol'] !== 'presidente') {
            $this->jsonResponse(false, 'No autorizado');
        }

        $id_reunion = $_POST['id_reunion'] ?? '';
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $lugar = $_POST['lugar'] ?? '';
        $orden_del_dia = $_POST['orden_del_dia'] ?? '[]'; 

        $success = $this->reunionModel->actualizarReunion($id_reunion, $id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $orden_del_dia);
        $this->jsonResponse($success, $success ? null : 'Error al actualizar la reunión');
    }

    // 🟢 API ENDPOINT: ELIMINAR REUNIÓN 🟢
    public function eliminarReunionAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;
        if (!$id_comunidad || $_SESSION['vivienda']['rol'] !== 'presidente') {
            $this->jsonResponse(false, 'No autorizado');
        }

        $id_reunion = $_POST['id_reunion'] ?? '';
        $success = $this->reunionModel->eliminarReunion($id_reunion, $id_comunidad);
        $this->jsonResponse($success, $success ? null : 'Error al eliminar la reunión');
    }

    // 🟢 API ENDPOINT: CONFIRMAR ASISTENCIA 🟢
    public function confirmarAsistenciaAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id_vivienda = $_SESSION['vivienda']['id_vivienda'] ?? null;
        if (!$id_vivienda) {
            $this->jsonResponse(false, 'Sesión no válida');
        }

        $id_reunion = $_POST['id_reunion'] ?? '';
        $confirmacion = $_POST['confirmacion'] ?? '';

        $success = $this->reunionModel->confirmarAsistencia($id_reunion, $id_vivienda, $confirmacion);
        $this->jsonResponse($success, $success ? null : 'Error al guardar asistencia');
    }
}