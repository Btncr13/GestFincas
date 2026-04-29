<?php
require_once __DIR__ . '/../models/EspacioModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';

class EspacioController
{
    private $espacioModel;
    private $reservaModel;

    public function __construct($pdo)
    {
        $this->espacioModel = new EspacioModel($pdo);
        $this->reservaModel = new ReservaModel($pdo);

        if (session_status() === PHP_SESSION_NONE) session_start();

        // RBAC: Validar que sea Presidente
        if (
            !isset($_SESSION['vivienda']['id_usuario']) || !isset($_SESSION['vivienda']['rol']) ||
            $_SESSION['vivienda']['rol'] !== 'presidente'
        ) {
            header("HTTP/1.1 403 Forbidden");
            exit('Acceso denegado. Solo el Presidente puede acceder a esta sección.');
        }
    }


    // API: CREAR ESPACIO
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Limpiamos la salida para asegurar que solo enviamos JSON y evitar errores HTML
            ob_clean();
            header('Content-Type: application/json');

            try {
                $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;
                if (!$id_comunidad) {
                    throw new Exception('Sesión caducada. Vuelve a iniciar sesión.');
                }

                // Lógica de negocio para "bloqueado" y "motivo"
                $bloqueado = isset($_POST['bloqueado']) ? (int)$_POST['bloqueado'] : 0;
                $motivo = ($bloqueado === 1 && !empty($_POST['motivo'])) ? trim($_POST['motivo']) : null;

                // Empaquetamos los datos en un array para pasar al modelo
                $datos = [
                    'nombre_espacio' => $_POST['nombre_espacio'] ?? '',
                    'aforo'          => $_POST['aforo'] ?? 0,
                    'max_personas'   => $_POST['max_personas'] ?? 0,
                    'hora_apertura'  => $_POST['hora_apertura'] ?? '',
                    'hora_cierre'    => $_POST['hora_cierre'] ?? '',
                    'duracion_uso'   => $_POST['duracion_uso'] ?? 0,
                    'bloqueado'      => $bloqueado,
                    'motivo'         => $motivo,
                    'id_comunidad'   => $id_comunidad,
                    'normas'         => $_POST['normas'] ?? ''
                ];

                if (empty($datos['nombre_espacio'])) {
                    throw new Exception('El nombre del espacio es obligatorio.');
                }

                // 1. Llamada a la función devolver el ID insertado
                $idNuevoEspacio = $this->espacioModel->crearEspacioCompleto($datos);

                if ($idNuevoEspacio) {

                    // 2. Usamos tu función getEspacioById a través del reservaModel
                    $espacioCreado = $this->reservaModel->getEspacioById($idNuevoEspacio);
                    // Añadimos las normas para que el JS pueda renderizarlas en la card
                    $espacioCreado['normas'] = $this->espacioModel->getNormasByEspacio($idNuevoEspacio);

                    // 3. Devolvemos el éxito y pasamos los datos reales recién extraídos de la BD
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Espacio creado correctamente',
                        'espacio' => $espacioCreado
                    ]);
                } else {
                    throw new Exception('Error al guardar en la base de datos. Verifica la inserción.');
                }
            } catch (Exception $e) {
                // Si hay cualquier error de PHP o de negocio, devolvemos JSON limpio
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        }
    }
    // API: MODIFICAR ESPACIO
    /**
     * Actualiza los datos de un espacio comunitario y sus normas asociadas.
     * Requiere que el usuario sea presidente.
     */
    public function update()
    {
        // Limpiamos la salida para asegurar que solo enviamos JSON y evitar errores HTML
        ob_clean();
        header('Content-Type: application/json');

        // RBAC: Validar que sea Presidente (redundante pero buena práctica para APIs)
        if (
            !isset($_SESSION['vivienda']['id_usuario']) || !isset($_SESSION['vivienda']['rol']) ||
            $_SESSION['vivienda']['rol'] !== 'presidente'
        ) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo el Presidente puede realizar esta acción.']);
            exit;
        }

        // Aseguramos que id_comunidad esté presente en la sesión
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;

        $data = [
            'id_espacios_comunidad' => $_POST['id_espacios_comunidad'] ?? null,
            'id_comunidad'          => $_SESSION['vivienda']['id_comunidad'], // Seguridad
            'nombre_espacio'        => $_POST['nombre_espacio'] ?? '',
            'aforo'                 => (int)($_POST['aforo'] ?? 1),
            'max_personas'          => (int)($_POST['max_personas'] ?? 1),
            'hora_apertura'         => $_POST['hora_apertura'] ?? '08:00:00',
            'hora_cierre'           => $_POST['hora_cierre'] ?? '22:00:00',
            'duracion_uso'          => (int)($_POST['duracion_uso'] ?? 60),
            'normas'                => $_POST['normas'] ?? ''
        ];

        // Validaciones básicas
        if (empty($data['id_espacios_comunidad']) || empty($data['nombre_espacio']) || !$id_comunidad) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos para la actualización.']);
            exit;
        }

        if ($this->espacioModel->modificarEspacio($data)) { // El modelo ahora maneja las normas también
            $espacioActualizado = $this->reservaModel->getEspacioById($data['id_espacios_comunidad']);
            // Añadimos las normas actualizadas a la respuesta
            $espacioActualizado['normas'] = $this->espacioModel->getNormasByEspacio($data['id_espacios_comunidad']);
            echo json_encode(['success' => true, 'message' => 'Espacio actualizado.', 'espacio' => $espacioActualizado]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al modificar el espacio.']);
        }
        exit;
    }

    // API: Obtiene las normas de un espacio específico para ser mostradas en el frontend.
    public function getNormasForEspacio()
    {
        ob_clean();
        header('Content-Type: application/json');
        $id_espacios_comunidad = $_GET['id'] ?? null;

        if (!$id_espacios_comunidad) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de espacio no proporcionado.']);
            exit;
        }
        $normas = $this->espacioModel->getNormasByEspacio($id_espacios_comunidad);
        echo json_encode(['success' => true, 'normas' => $normas]);
        exit;
    }

    // API: BLOQUEAR/DESBLOQUEAR (Soft Delete / Inactivar)
    public function toggleEstado()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id_espacios_comunidad = $_POST['id_espacios_comunidad'] ?? null;

        // En frontend probablemente enviabas estado: 0 o 1, pero ahora nuestro campo 
        // en la BD se llama 'bloqueado' donde 1 es Bloqueado y 0 es Activo.
        $bloqueado = $_POST['bloqueado'] ?? 0;
        // Si estamos activando (bloqueado = 0), el motivo DEBE ser null
        $motivo = ($bloqueado == 1) ? (trim($_POST['motivo'] ?? '')) : null;

        if ($this->espacioModel->bloquearEspacio($id_espacios_comunidad, $bloqueado, $motivo)) {
            $espacio = $this->reservaModel->getEspacioById($id_espacios_comunidad);

            // Es vital incluir las normas aquí para que el JS pueda re-renderizar la card correctamente
            $espacio['normas'] = $this->espacioModel->getNormasByEspacio($id_espacios_comunidad);

            $msg = $bloqueado == 1 ? 'Espacio bloqueado.' : 'Espacio operativo.';
            echo json_encode(['success' => true, 'message' => $msg, 'espacio' => $espacio]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del espacio.']);
        }
        exit;
    }

    // API: ELIMINAR ESPACIO
    public function destroy()
    {
        ob_clean();
        header('Content-Type: application/json');
        $id = $_POST['id_espacios_comunidad'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de espacio no proporcionado.']);
            return;
            exit;
        }

        if ($this->espacioModel->eliminarEspacio($id)) {
            echo json_encode(['success' => true, 'message' => 'Espacio eliminado correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el espacio.']);
        }
        exit;
    }
}
