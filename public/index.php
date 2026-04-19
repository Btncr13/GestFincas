<?php

/**
 * Archivo index.php - Punto de entrada único de la aplicación.
 */

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 1. Iniciamos el almacenamiento en búfer de salida.
// Esto permite que los controladores ejecuten redirecciones (header) 
// incluso si ya se ha incluido HTML del archivo head.php.
ob_start();

// 2. Iniciamos la sesión para gestionar la autenticación de las viviendas.
session_start();

// 3. Cargamos la configuración (local o ejemplo)
$configFile = __DIR__ . '/../config/config.local.php';

if (!file_exists($configFile)) {
    $configFile = __DIR__ . '/../config/config.php.example';
}

$config = require $configFile;

// 4. Requerimos el enrutador y los componentes globales de la interfaz.
require_once "../config/router.php";
require_once "../src/views/components/head.php";

// 5. Ejecutamos el enrutador para cargar el controlador y método solicitados.
runRouter($config);

// 6. Incluimos el pie de página común.
require_once "../src/views/components/footer.php";

ob_end_flush();
?>