<?php
require_once __DIR__ . '/../models/IncidenciasModel.php';

class IncidenciasController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new IncidenciasModel();
    }

    // Método principal de la vista
   public function index() {
        if (!isset($_SESSION['vivienda'])) { header('Location: index.php?route=auth/login'); exit; }
        
        // todos ven el tablón global con las reglas de negocio
        $incidencias = $this->model->obtenerIncidenciasGlobales();
        
        require_once __DIR__ . '/../views/incidencias/index.php';
    }
    // API: Guardar o detectar similitud
    public function store() {
        header('Content-Type: application/json');
        $id_vivienda = $_SESSION['vivienda']['id_vivienda'];
        
        $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING));
        $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
        
        // Normalización estricta del título
        $titulo_norm = $this->normalizarTitulo($titulo);

        // Buscar si existe una similar en curso
        $similar = $this->model->buscarSimilares($titulo_norm);

        if ($similar) {
            http_response_code(409); // Conflict: Avisamos al frontend
            echo json_encode([
                'status' => 'similar_found',
                'incidencia' => $similar,
                'message' => 'Se ha detectado una incidencia similar.'
            ]);
            exit;
        }

        // Si no hay similar, crear
        $id = $this->model->crear($id_vivienda, $titulo, $titulo_norm, $descripcion);
        if ($id) {
            echo json_encode(['status' => 'success', 'message' => 'Incidencia reportada correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar.']);
        }
        exit;
    }

    // API: Unirse a incidencia
    public function join() {
        header('Content-Type: application/json');
        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        
        if ($this->model->unirse($id_incidencia)) {
            echo json_encode(['status' => 'success', 'message' => 'Te has unido a la incidencia.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al unirse.']);
        }
        exit;
    }

    // Helpers
    private function normalizarTitulo($string) {
        $string = mb_strtolower($string, 'UTF-8');
        $string = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $string);
        $string = preg_replace('/[^a-z0-9\s]/', '', $string);
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}