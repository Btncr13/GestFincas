<?php

require_once "src/models/UsuarioModel.php";
// Incluir Dompdf (ajusta la ruta si es necesario)
require_once 'libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

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

        // --- LÓGICA PARA SUBIR EL PDF ---
        $pdf_ruta = null; // Por defecto es null

        // Verificamos si JavaScript nos ha mandado un archivo físico
        if (isset($_FILES['pdf_orden_dia'])) {
            
            // Si llegó y no hubo errores de subida (Código 0 = UPLOAD_ERR_OK)
            if ($_FILES['pdf_orden_dia']['error'] === UPLOAD_ERR_OK) {
                
                $uploadDir = dirname(__DIR__, 2) . '/public/uploads/reuniones/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['pdf_orden_dia']['name'], PATHINFO_EXTENSION));
                
                if ($fileExtension === 'pdf') {
                    $newFileName = uniqid('reunion_', true) . '.pdf';
                    
                    if (move_uploaded_file($_FILES['pdf_orden_dia']['tmp_name'], $uploadDir . $newFileName)) {
                        $pdf_ruta = 'public/uploads/reuniones/' . $newFileName;
                    } else {
                        $this->jsonResponse(false, 'Error al mover el PDF a su carpeta definitiva.');
                    }
                } else {
                    $this->jsonResponse(false, 'Formato no soportado. Por favor, sube un archivo .pdf');
                }
            } else {
                // Si PHP descarta el archivo (por peso o configuración), nos dirá el código exacto
                $this->jsonResponse(false, 'Error interno de PHP al subir: Código ' . $_FILES['pdf_orden_dia']['error']);
            }
        }

       // --- LÓGICA DE BASE DE DATOS ---
        $id_reunion_creada = $this->reunionModel->crearReunion($id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $orden_del_dia, $pdf_ruta);
        
        // --- RESPUESTA JSON FINAL QUE JS ESPERA ---
        // Si es 'true' o es un número (el ID de la nueva reunión), es un éxito absoluto
        if (is_numeric($id_reunion_creada)) {
            // Si no se subió un PDF manual, generamos el automático
            if ($pdf_ruta === null) {
                $this->generateAndSavePdf($id_reunion_creada, $titulo, $descripcion, $fecha, $hora, $lugar, json_decode($orden_del_dia, true));
            }
            $this->jsonResponse(true, 'Reunión convocada correctamente.');
        } else {
            // Si es un texto, es el mensaje de error del catch de PDO
            $this->jsonResponse(false, 'Error SQL: ' . $id_reunion_creada);
        }
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

        $pdf_ruta = null;

        // --- LÓGICA PARA GESTIONAR EL PDF EN LA EDICIÓN ---
        if (isset($_FILES['pdf_orden_dia']) && $_FILES['pdf_orden_dia']['error'] === UPLOAD_ERR_OK) {
            
            // 1. Necesitamos saber si ya existía un PDF previo para borrarlo físicamente
            $reunionActual = $this->reunionModel->getReunionById($id_reunion, $id_comunidad);
            
            if ($reunionActual && !empty($reunionActual['pdf_orden_dia'])) {
                // Convertimos ruta pública guardada en DB a ruta absoluta de servidor para unlink
                $oldFilePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $reunionActual['pdf_orden_dia']);
                
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // 2. Subimos el nuevo archivo
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/reuniones/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['pdf_orden_dia']['name'], PATHINFO_EXTENSION));
            if ($fileExtension === 'pdf') {
                $newFileName = uniqid('reunion_', true) . '.pdf';
                if (move_uploaded_file($_FILES['pdf_orden_dia']['tmp_name'], $uploadDir . $newFileName)) {
                    $pdf_ruta = 'public/uploads/reuniones/' . $newFileName;
                } else {
                    $this->jsonResponse(false, 'Error al mover el nuevo PDF a su ubicación.');
                }
            } else {
                $this->jsonResponse(false, 'Formato no soportado. Sube un PDF.');
            }
        }

        $success = $this->reunionModel->actualizarReunion($id_reunion, $id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $orden_del_dia, $pdf_ruta);

        if ($success) {
            // Solo regeneramos el PDF automático si NO se ha subido uno manual en esta edición
            if (!$pdf_ruta) {
                $this->generateAndSavePdf($id_reunion, $titulo, $descripcion, $fecha, $hora, $lugar, json_decode($orden_del_dia, true));
            }
            $this->jsonResponse(true, 'Reunión actualizada correctamente.');
        } else {
            $this->jsonResponse(false, 'Error al actualizar la reunión.');
        }
    }

    // 🟢 API ENDPOINT: LIMPIAR PDFS OBSOLETOS 🟢
    public function cleanupOldPdfsAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
        }

        $id_comunidad = $_SESSION['vivienda']['id_comunidad'] ?? null;
        if (!$id_comunidad || $_SESSION['vivienda']['rol'] !== 'presidente') {
            $this->jsonResponse(false, 'No autorizado.');
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/reuniones/';
        if (!is_dir($uploadDir)) {
            $this->jsonResponse(true, 'La carpeta de subidas no existe, no hay PDFs que limpiar.');
        }

        // 1. Obtener todas las rutas de PDF activas en la base de datos
        $activePdfPaths = $this->reunionModel->getAllPdfPaths();

        // Convertir las rutas de la DB a un formato comparable con las rutas del sistema de archivos
        // Las rutas en DB ya están en formato "public/uploads/reuniones/file.pdf"
        $dbRelativePaths = [];
        foreach ($activePdfPaths as $path) {
            $dbRelativePaths[] = $path;
        }

        // 2. Obtener todos los archivos PDF en la carpeta de subidas
        // glob() devuelve rutas absolutas
        $fileSystemPdfs = glob($uploadDir . '*.pdf');
        $deletedCount = 0;
        $errors = [];

        foreach ($fileSystemPdfs as $filePath) {
            // Convertir la ruta absoluta del sistema de archivos a una ruta relativa para la comparación
            $relativePath = str_replace(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR, '', $filePath);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath); // Normalizar barras para comparación

            if (!in_array($relativePath, $dbRelativePaths)) {
                // Este archivo existe en disco pero no en la base de datos, así que lo eliminamos
                if (unlink($filePath)) {
                    $deletedCount++;
                } else {
                    $errors[] = "Error al eliminar el archivo: " . basename($filePath);
                }
            }
        }

        if (empty($errors)) {
            $this->jsonResponse(true, "Limpieza completada. Se eliminaron $deletedCount archivos PDF obsoletos.");
        } else {
            $this->jsonResponse(false, "Limpieza completada con errores. Se eliminaron $deletedCount archivos. Errores: " . implode(', ', $errors));
        }
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

        // 1. Antes de borrar la reunión, buscamos si tiene un PDF asociado para eliminarlo del disco
        $reunion = $this->reunionModel->getReunionById($id_reunion, $id_comunidad);
        if ($reunion && !empty($reunion['pdf_orden_dia'])) {
            // Convertimos la ruta relativa de la BD a una ruta absoluta del servidor
            $filePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $reunion['pdf_orden_dia']);
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // 2. Procedemos a eliminar la reunión de la base de datos
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

    // 🟢 PRIVADA: GENERAR Y GUARDAR EL PDF 🟢
    private function generateAndSavePdf($id_reunion, $titulo, $descripcion, $fecha, $hora, $lugar, $ordenArray) {
        
         try {
            $html = $this->generateOrdenDelDiaHtml($titulo, $descripcion, $fecha, $hora, $lugar, $ordenArray);

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Usamos ruta absoluta para evitar problemas de permisos o ubicación
            $basePath = dirname(__DIR__, 2);
            $folderPath = $basePath . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "reuniones" . DIRECTORY_SEPARATOR;
            
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            $fileName = $id_reunion . ".pdf";
            $fullPath = $folderPath . $fileName;
            $publicPath = "public/uploads/reuniones/" . $fileName;

            if (file_put_contents($fullPath, $dompdf->output())) {
                $this->reunionModel->updatePdfOrdenDiaPath($id_reunion, $publicPath);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error generando PDF: " . $e->getMessage());
            return false;
        }
    }

     // 🟢 PRIVADA: GENERAR EL HTML PROFESIONAL PARA EL PDF 🟢
    private function generateOrdenDelDiaHtml($titulo, $descripcion, $fecha, $hora, $lugar, $ordenArray)
    {
        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Nuestra Comunidad';
        $fechaFormateada = date('d/m/Y', strtotime($fecha));
        $puntosHtml = "";

        if (is_array($ordenArray) && !empty($ordenArray)) {
            foreach ($ordenArray as $punto) {
                $puntosHtml .= "<li>" . htmlspecialchars($punto) . "</li>";
            }
        } else {
            $puntosHtml = "<li>No se han especificado puntos en el orden del día.</li>";
        }

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #221C35; padding-bottom: 15px; margin-bottom: 30px; }
                .comunidad { font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
                .titulo-doc { font-size: 26px; font-weight: bold; color: #221C35; margin: 10px 0; }
                .info-box { background: #F8F9FA; padding: 20px; border-radius: 8px; border: 1px solid #E9ECEF; margin-bottom: 25px; }
                .info-item { margin-bottom: 8px; font-size: 15px; }
                .label { font-weight: bold; color: #221C35; width: 100px; display: inline-block; }
                .descripcion-section { margin-bottom: 30px; }
                .descripcion-section h3 { font-size: 18px; color: #221C35; border-bottom: 1px solid #EEE; padding-bottom: 5px; }
                .orden-dia-section { margin-top: 20px; }
                .orden-dia-section h3 { font-size: 18px; color: #221C35; border-bottom: 1px solid #EEE; padding-bottom: 5px; }
                ul { padding-left: 25px; }
                li { margin-bottom: 12px; font-size: 14px; }
                .footer { margin-top: 50px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #EEE; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='comunidad'>$nombreComunidad</div>
                <div class='titulo-doc'>CONVOCATORIA DE JUNTA</div>
            </div>

            <div class='info-box'>
                <div class='info-item'><span class='label'>Asunto:</span> " . htmlspecialchars($titulo) . "</div>
                <div class='info-item'><span class='label'>Fecha:</span> $fechaFormateada</div>
                <div class='info-item'><span class='label'>Hora:</span> $hora h</div>
                <div class='info-item'><span class='label'>Lugar:</span> " . htmlspecialchars($lugar) . "</div>
            </div>

            <div class='descripcion-section'>
                <h3>Descripción / Objetivos</h3>
                <p>" . nl2br(htmlspecialchars($descripcion)) . "</p>
            </div>

            <div class='orden-dia-section'>
                <h3>Orden del Día</h3>
                <ul>$puntosHtml</ul>
            </div>

            <div class='footer'>
                Documento generado automáticamente por GestFincas - " . date('d/m/Y H:i') . "
            </div>
        </body>
        </html>";
    }

    // 🟢 API ENDPOINT: DESCARGAR EL PDF 🟢
    public function descargarPdf() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            die("ID de reunión no proporcionado");
        }

        $basePath = dirname(__DIR__, 2);
        $filePath = $basePath . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "reuniones" . DIRECTORY_SEPARATOR . $id . ".pdf";

        if (file_exists($filePath)) {
            ob_clean(); // Limpiamos el búfer para evitar PDF corrupto
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="OrdenDelDia_' . $id . '.pdf"');
            readfile($filePath);
            exit;
        } else {
            echo "El archivo PDF aún no ha sido generado o no existe.";
        }
    }
}