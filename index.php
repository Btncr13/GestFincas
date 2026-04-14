<?php
// /index.php (EN LA RAÍZ DEL PROYECTO)
//http://localhost/proyectos/repogestfincas/JR_M26_ComunidadVecinos/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

ob_start();
session_start();

// 1. Cargamos la configuración (ahora sin el ../)
$configFile = __DIR__ . '/config/config.local.php';

if (!file_exists($configFile)) {
    $configFile = __DIR__ . '/config/config.php.example';
}

$config = require $configFile;

// 2. Requerimos el enrutador y el head (sin el ../)
require_once "config/router.php";
require_once "src/views/components/head.php";

// 3. Ejecutamos el router
runRouter($config);

// 4. Incluimos el footer
require_once "src/views/components/footer.php";

ob_end_flush();
?>