<?php

require_once "../config/Database.php";

function runRouter($config)
{
    $db = new Database($config);
    $pdo = $db->connect();

    // Ruta en formato /controlador/metodo
    $route = $_GET['route'] ?? 'home';

    // Dividir la ruta
    $parts = explode('/', trim($route, '/'));

    // Controlador y método por defecto
    $controllerName = ucfirst($parts[0] ?? 'home') . "Controller";
    $methodName = $parts[1] ?? "index";

    // Cargar controlador
    $controllerFile = "../src/controllers/$controllerName.php";

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
