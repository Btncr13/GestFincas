<?php
require_once __DIR__ . '/../models/IncidenciasModel.php';

class IncidenciasController
{
    private $model;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new IncidenciasModel($pdo);
    }

    // Método principal de la vista
    public function index()
    {
        if (!isset($_SESSION['vivienda'])) {
            header('Location: index.php?route=auth/login');
            exit;
        }

        // ======== VARIABLES COMUNES PARA TOPBAR Y SIDEBAR ========
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim($calle . ' ' . $numero);
        $rolReal         = $_SESSION['vivienda']['rol'] ?? 'vecino';
        $rol             = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        // ===========================================================

        $id_vivienda = $_SESSION['vivienda']['id_vivienda'] ?? null;

        // todos ven el tablón global con las reglas de negocio
        $incidencias = $this->model->obtenerIncidenciasGlobales();
        $misUniones = $this->model->obtenerMisUniones($id_vivienda);

        require_once __DIR__ . '/../views/incidencias/index.php';
    }
    // API: Guardar o detectar similitud
    public function store()
    {
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
            $desc_norm   = $this->normalizarTitulo($descripcion);

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

            // --- LÓGICA DE SIMILITUD MEJORADA ---
            // Creamos un bloque único de búsqueda que combina título y descripción
            $bloqueBusquedaInput = $titulo_norm . ' ' . $desc_norm;

            $incidenciasActivas = $this->model->obtenerActivasParaComparar();
            $similarEncontrada = null;

            foreach ($incidenciasActivas as $inc) {
                // Comparamos el nuevo bloque contra lo que ya está en la BD
                // (que ahora guardará el bloque completo gracias al cambio en el store)
                similar_text($bloqueBusquedaInput, $inc['titulo_normalizado'], $porcentaje);

                if ($porcentaje >= 70) {
                    $similarEncontrada = $inc;
                    break;
                }
            }

            if ($similarEncontrada) {
                http_response_code(409); // Conflict: Avisamos al frontend
                echo json_encode([
                    'status' => 'similar_found',
                    'incidencia' => $similarEncontrada,
                    'message' => 'Ya existe una incidencia muy similar reportada. Por favor, únete a ella.'
                ]);
                exit;
            }

            // Guardamos el bloque combinado en el campo 'titulo_normalizado' de la BD
            $id = $this->model->crear($id_vivienda, $titulo, $bloqueBusquedaInput, $descripcion, $foto_ruta);

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
    public function join()
    {
        if (ob_get_length()) ob_clean(); // Fundamental para evitar Notice/Warning que rompan JSON en JS
        header('Content-Type: application/json');
        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        $id_vivienda = $_SESSION['vivienda']['id_vivienda'] ?? 0;

        $resultado = $this->model->unirse($id_incidencia, $id_vivienda);

        if ($resultado['success']) {
            echo json_encode(['status' => 'success', 'message' => 'Te has unido exitosamente a la incidencia.']);
        } else {
            http_response_code(400); // 400 (Bad Request) para que JS reciba JSON limpio del rechazo
            echo json_encode(['status' => 'error', 'message' => $resultado['message']]);
        }
        exit;
    }

    // API: Cambiar estado incidencia (Solo para el Presidente)
    public function updateEstado()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id_incidencia = $_POST['id_incidencia'] ?? 0;
        $nuevo_estado = $_POST['estado'] ?? '';

        // Verificación de rol por seguridad (RBAC)
        $rolActual = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
        if (strtolower($rolActual) !== 'presidente' && strtoupper($rolActual) !== 'SUPERADMIN') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para cambiar el estado.']);
            exit;
        }

        // Validación del estado a inyectar
        if (!in_array($nuevo_estado, ['abierta', 'resuelta'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Estado no válido.']);
            exit;
        }

        if ($this->model->actualizarEstado($id_incidencia, $nuevo_estado)) {
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado a ' . $nuevo_estado]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado de la incidencia.']);
        }
        exit;
    }

    // API: Eliminar incidencia (Propietario o Presidente)
    public function delete()
    {
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
    private function normalizarTitulo($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        // Eliminar acentos
        $string = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $string);
        // Eliminar caracteres especiales
        $string = preg_replace('/[^a-z0-9\s]/', '', $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));

        // Eliminar conectores y artículos (Stop words) para mejorar la densidad de palabras clave
        $stopWords = ['el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del', 'a', 'al', 'en', 'por', 'para', 'con', 'su', 'sus', 'se', 'ha', 'hay', 'que', 'mi', 'me', 'esta', 'este', 'lo', 'le'];

        // Diccionario de sinónimos: mapeamos variaciones a un concepto base
        $sinonimos = [
            // Estado de la avería
            'averiado'   => 'roto',
            'averia'     => 'roto',
            'estropeado' => 'roto',
            'estropeada' => 'roto',
            'dañado'     => 'roto',
            'dañada'     => 'roto',
            'fallo'      => 'roto',
            'falla'      => 'roto',
            'fundido'    => 'roto',
            'fundida'    => 'roto',
            // Elementos comunes
            'bombilla'   => 'luz',
            'foco'       => 'luz',
            'lampara'    => 'luz',
            'iluminacion' => 'luz',
            'elevador'   => 'ascensor',
            'garaje'     => 'cochera',
            'parking'    => 'cochera'
        ];

        $palabras = explode(' ', $string);
        $filtradas = array_diff($palabras, $stopWords);

        // Reemplazamos cada palabra por su sinónimo si existe en el diccionario
        $normalizadas = array_map(function ($palabra) use ($sinonimos) {
            return $sinonimos[$palabra] ?? $palabra;
        }, $filtradas);

        return implode(' ', $normalizadas);
    }
}
