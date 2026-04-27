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
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;

        // Pasamos a JS la base de datos de incidencias de la comunidad entera
        $incidenciasData = [];
        if ($id_comunidad) {
            $incidenciasData = $this->model->obtenerIncidenciasPorComunidad($id_comunidad);
        }
        $misUniones = $this->model->obtenerMisUniones($id_vivienda);

        require_once __DIR__ . '/../views/incidencias/index.php';
    }
    // API: Guardar o detectar similitud
    public function store()
    {
        // 1. SOLUCIÓN AL ERROR DE BÚFER: Limpiar TODOS los niveles
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        try {
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'] ?? null;
            if (!$id_vivienda) {
                echo json_encode(['status' => 'error', 'message' => 'Sesión caducada.']);
                exit;
            }
        

            $titulo = trim(strip_tags($_POST['titulo'] ?? ''));
            $descripcion = trim(strip_tags($_POST['descripcion'] ?? ''));
        // 2. RECUPERAR EL FLAG QUE ENVÍA JS CUANDO PULSAMOS "NO, ES DIFERENTE"
            $forzar_creacion = isset($_POST['forzar_creacion']) && $_POST['forzar_creacion'] === 'true';
            if (empty($titulo) || empty($descripcion)) {
                echo json_encode(['status' => 'error', 'message' => 'El título y la descripción son obligatorios.']);
                exit;
            }

            
            $texto_normalizado = $this->normalizarTexto($titulo . ' ' . $descripcion);
            
            if (empty($texto_normalizado)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'El texto no contiene palabras clave válidas tras el filtro.']);
                exit;
            }

            // --- LÓGICA PARA SUBIR LA FOTO ---
            $foto_ruta = ''; // Carga string vacío en vez de null para evitar error en MySQL (NOT NULL)
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

           // 3. LÓGICA DE DETECCIÓN: Solo buscamos duplicados si NO estamos forzando la creación
            if (!$forzar_creacion) {
                
                $incidenciasSimilares = $this->model->buscarIncidenciasSimilares($texto_normalizado, 0.1);
                $similarEncontrada = null;
                $userWords = array_unique(explode(' ', $texto_normalizado));

                foreach ($incidenciasSimilares as $inc) {
                    $dbWords = array_unique(explode(' ', $inc['texto_normalizado']));
                    $coincidencias = count(array_intersect($userWords, $dbWords));
                    
                    
                    if ($coincidencias >= 2) {
                        $similarEncontrada = [
                            'id_incidencias' => $inc['id_incidencias'],
                            'titulo' => $inc['titulo'],
                            'fecha_creacion' => $inc['fecha_creacion']
                        ];
                        break; 
                    }
                }

                if ($similarEncontrada) {
                    http_response_code(409); // Conflict
                    echo json_encode([
                        'status' => 'similar_found',
                        'incidencia' => $similarEncontrada,
                        'message' => 'Se ha detectado una incidencia muy similar.'
                    ]);
                    exit;
                }
            }

            // Guardamos la nueva incidencia
            $id = $this->model->crear($id_vivienda, $titulo, $texto_normalizado, $descripcion, $foto_ruta);

            while (ob_get_level() > 0) ob_end_clean(); // Destrucción total de cualquier HTML previo
            header('Content-Type: application/json');
            if ($id) {
                echo json_encode(['status' => 'success', 'message' => 'Incidencia reportada correctamente.']);
            } else {
                throw new Exception('Error interno en la base de datos al guardar.');
            }
        } catch (Throwable $e) {
            while (ob_get_level() > 0) ob_end_clean(); // Limpieza profunda antes del error
            header('Content-Type: application/json');
            http_response_code(500);
            $msg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'auto');
            echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $msg]);
        }
        exit;
    }

    // API: Unirse a incidencia
    public function join()
    {
        while (ob_get_level() > 0) ob_end_clean();
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
        while (ob_get_level() > 0) ob_end_clean();
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
        while (ob_get_level() > 0) ob_end_clean();
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
    private function normalizarTexto($string)
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
            'rota'       => 'roto',
            'rotura'     => 'roto',
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
            'parpadeando'=> 'parpadea',
            'parpadeo'   => 'parpadea',
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
