<?php

require_once "config/database.php";

function runRouter($config)
{
    $db = new Database($config);
    $pdo = $db->connect();

    /*
    // Ruta en formato /controlador/metodo
    $route = $_GET['route'] ?? 'home';

    // Dividir la ruta
    $parts = explode('/', trim($route, '/'));

    // Controlador y método por defecto
    $controllerName = ucfirst($parts[0] ?? 'home') . "Controller";
    $methodName = $parts[1] ?? "index";

    // Cargar controlador
    $controllerFile = "../src/controllers/$controllerName.php";
    */

    // 1. Ponemos el login por defecto si no hay ruta:
    $route = $_GET['route'] ?? 'auth/login';
    $parts = explode('/', trim($route, '/'));

    // Normalizamos el nombre del controlador (Ej: miComunidad -> MiComunidadController)
    $controllerName = ucfirst($parts[0]) . "Controller";
    
    // Si no se especifica método, usamos 'login' para Auth y 'index' para el resto de módulos (como Comunicaciones)
    $methodName = $parts[1] ?? ($controllerName === 'AuthController' ? 'login' : 'index');

    // 2. Quitamos el "../" de la ruta del controlador porque ya estamos en la raíz:
    $controllerFile = "src/controllers/$controllerName.php";

    if (!file_exists($controllerFile)) {
        echo "Controlador no encontrado";
        return;
    }

    require_once $controllerFile;

    if (!class_exists($controllerName)) {
        echo "Clase del controlador no encontrada";
        return;
    }

    $controller = new $controllerName($pdo);

    // Verificar método
    if (!method_exists($controller, $methodName)) {
        echo "Método no encontrado";
        return;
    }

    // Ejecutar método dinámicamente
    $controller->$methodName();
}
?>