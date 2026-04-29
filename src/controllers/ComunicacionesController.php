<?php
require_once "src/models/UsuarioModel.php";
require_once "src/models/ComunicacionesModel.php";

class ComunicacionesController
{
    private $usuarioModel;
    private $comunicacionesModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->comunicacionesModel = new ComunicacionesModel($pdo);
    }

    private function getViewData()
    {
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'id_comunidad'    => $_SESSION['vivienda']['id_comunidad'],
            'id_vivienda'     => $_SESSION['vivienda']['id_vivienda'], // Añadido para consistencia
            'id_usuario'      => $_SESSION['vivienda']['id_usuario'],
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino', // Añadido para solucionar el error del sidebar
            'rol'             => $_SESSION['modo_vista'] ?? $_SESSION['vivienda']['rol']
        ];
    }

    public function index()
    {
        extract($this->getViewData());
        $comunicados = $this->comunicacionesModel->getComunicadosPorComunidad($id_comunidad);
        
        require "src/views/comunicaciones/comunicaciones.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['modo_vista'] === 'presidente') {
            $titulo = trim($_POST['titulo'] ?? '');
            $cuerpo = trim($_POST['cuerpo'] ?? '');
            $tipo = isset($_POST['urgente']) ? 'urgente' : 'normal';
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            if (!empty($titulo) && !empty($cuerpo)) {
                $this->comunicacionesModel->crearComunicado($id_comunidad, $titulo, $cuerpo, $tipo);
            }
        }
        header("Location: index.php?route=comunicaciones/index");
    }

    public function leer()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $id_comunicado = $_GET['id'] ?? null;
        $id_usuario = $_SESSION['vivienda']['id_usuario'];

        if ($id_comunicado) {
            $comunicado = $this->comunicacionesModel->getComunicadoById($id_comunicado);
            if ($comunicado) {
                $this->comunicacionesModel->marcarComoLeido($id_comunicado, $id_usuario);
                echo json_encode(['success' => true, 'data' => $comunicado]);
                exit;
            }
            // Añadimos un log si el comunicado no se encuentra en la base de datos
            error_log("ComunicacionesController::leer - Comunicado con ID {$id_comunicado} no encontrado.");
        }
        echo json_encode(['success' => false, 'message' => 'Comunicado no encontrado o ID inválido.']);
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['modo_vista'] === 'presidente') {
            $id_comunicado = $_POST['id_comunicado'] ?? null;
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
            if ($id_comunicado) {
                $this->comunicacionesModel->eliminarComunicado($id_comunicado, $id_comunidad);
            }
        }
        header("Location: index.php?route=comunicaciones/index");
    }
}
