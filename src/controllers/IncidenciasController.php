<?php
require_once __DIR__ . '/../models/IncidenciasModel.php';

class IncidenciasController {
    private $model;

    public function __construct($pdo) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new IncidenciasModel($pdo);
    }

    // Método principal de la vista
   public function index() {
        if (!isset($_SESSION['vivienda'])) { header('Location: index.php?route=auth/login'); exit; }
        
        // ======== VARIABLES COMUNES PARA TOPBAR Y SIDEBAR ========
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim($calle . ' ' . $numero);
        $rolReal         = $_SESSION['vivienda']['rol'] ?? 'vecino';
        $rol             = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        // ===========================================================

        // todos ven el tablón global con las reglas de negocio
        $incidencias = $this->model->obtenerIncidenciasGlobales();
        
        require_once __DIR__ . '/../views/incidencias/index.php';
    }
    // API: Guardar o detectar similitud
    public function store() {
        if (ob_get_length()) ob_clean(); // Evitamos un Notice si el buffer estaba vacío
        header('Content-Type: application/json');
        
        try {
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'] ?? null;
            if (!$id_vivienda) {
                echo json_encode(['status' => 'error', 'message' => 'Sesión caducada.']);
                exit;
            }
            
            $titulo = trim(strip_tags($_POST['titulo'] ?? ''));
            $descripcion = trim(strip_tags($_POST['descripcion'] ?? ''));

            if (empty($titulo) || empty($descripcion)) {
                echo json_encode(['status' => 'error', 'message' => 'El título y la descripción son obligatorios.']);
                exit;
            }

            // Normalización estricta del título
            $titulo_norm = $this->normalizarTitulo($titulo);

            // --- LÓGICA PARA SUBIR LA FOTO ---
            $foto_ruta = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                // Usamos dirname con nivel 2 para garantizar compatibilidad absoluta con Windows/XAMPP
                $uploadDir = dirname(__DIR__, 2) . '/public/uploads/incidencias/';
                
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) throw new Exception('No se pudo crear la carpeta para las imágenes.');
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = uniqid('inc_', true) . '.' . $fileExtension;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $newFileName)) {
                        $foto_ruta = 'public/uploads/incidencias/' . $newFileName;
                    } else {
                        throw new Exception('Error al mover la imagen a su carpeta final.');
                    }
                } else {
                    throw new Exception('Formato de imagen no soportado. Usa JPG, PNG, GIF o WEBP.');
                }
            } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception('Error de subida del archivo. Código de error PHP: ' . $_FILES['foto']['error']);
            }

            // Buscar si existe una similar en curso
            $similar = $this->model->buscarSimilares($titulo_norm);

            if ($similar) {
                http_response_code(409); // Conflict: Avisamos al frontend
                echo json_encode(['status' => 'similar_found', 'incidencia' => $similar, 'message' => 'Se ha detectado una incidencia similar.']);
                exit;
            }

            // Si no hay similar, crear (Si la tabla de BD no tiene el campo exacto, saltará un Exception que capturaremos)
            $id = $this->model->crear($id_vivienda, $titulo, $titulo_norm, $descripcion, $foto_ruta);
            
            if ($id) echo json_encode(['status' => 'success', 'message' => 'Incidencia reportada correctamente.']);
            else throw new Exception('Error interno en la base de datos al guardar.');
            
        } catch (Exception $e) {
            http_response_code(500);
            // Si falla la base de datos o la subida de la imagen, mostrará aquí el motivo exacto.
            echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
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

    // API: Abrir incidencia (Solo para el Presidente)
    public function open() {
        if (ob_get_length()) ob_clean(); // Asegurar JSON limpio
        header('Content-Type: application/json');
        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        
        // Verificación de rol por seguridad
        $rolActual = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        if (strtolower($rolActual) !== 'presidente' && strtoupper($rolActual) !== 'SUPERADMIN') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para abrir incidencias.']);
            exit;
        }

        if ($this->model->actualizarEstado($id_incidencia, 'abierta')) {
            echo json_encode(['status' => 'success', 'message' => 'La incidencia ha sido marcada como abierta.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al abrir la incidencia.']);
        }
        exit;
    }

    // API: Resolver incidencia (Solo para el Presidente)
    public function resolve() {
        if (ob_get_length()) ob_clean(); // Asegurar JSON limpio
        header('Content-Type: application/json');
        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        
        // Verificación de rol por seguridad
        $rolActual = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        if (strtolower($rolActual) !== 'presidente' && strtoupper($rolActual) !== 'SUPERADMIN') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para resolver incidencias.']);
            exit;
        }

        if ($this->model->actualizarEstado($id_incidencia, 'resuelta')) {
            echo json_encode(['status' => 'success', 'message' => 'La incidencia ha sido marcada como resuelta.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al resolver la incidencia.']);
        }
        exit;
    }

    // API: Eliminar incidencia (Propietario o Presidente)
    public function delete() {
        if (ob_get_length()) ob_clean(); // Evitamos que un notice rompa el JSON en el JS
        header('Content-Type: application/json');
        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        $id_vivienda = $_SESSION['vivienda']['id_vivienda'];
        $rolActual = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');

        if ($this->model->eliminar($id_incidencia, $id_vivienda, strtoupper($rolActual))) {
            echo json_encode(['status' => 'success', 'message' => 'Incidencia eliminada correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No tienes permisos o hubo un error al eliminar.']);
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