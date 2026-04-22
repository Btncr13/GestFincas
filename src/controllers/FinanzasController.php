<?php
require_once "src/models/UsuarioModel.php";

class FinanzasController
{
    private $usuarioModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->usuarioModel = new UsuarioModel($pdo);
    }

    private function getViewData()
    {
        if (isset($_SESSION['vivienda']['id_usuario'])) {
            $sesionFresca = $this->usuarioModel->refrescarSesion($_SESSION['vivienda']['id_usuario']);
            if ($sesionFresca) $_SESSION['vivienda'] = $sesionFresca;
        }
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino')
        ];
    }

    public function index()
    {
        extract($this->getViewData());

        if ($rol === 'presidente') {
            // Datos de prueba (Mocks) para visualizar la maquetación del Frontend
            $gastosPendientes = [
                ['id' => 1, 'concepto' => 'Revisión completa ascensor - presupuesto TechLift', 'categoria' => 'Ascensores', 'fecha' => '20/3/2026', 'importe' => 650.00],
                ['id' => 2, 'concepto' => 'Sustitución luminarias portal', 'categoria' => 'Electricidad', 'fecha' => '16/3/2026', 'importe' => 387.20]
            ];

            $cuotasVecinos = [
                ['vivienda' => 'Planta 1-A', 'vecino' => 'Antonio Recio', 'estado' => 'Al corriente', 'deuda' => 0.00],
                ['vivienda' => 'Planta 2-B', 'vecino' => 'Enrique Pastor', 'estado' => 'Pendiente', 'deuda' => 150.50],
                ['vivienda' => 'Planta 3-C', 'vecino' => 'Amador Rivas', 'estado' => 'Moroso', 'deuda' => 450.00]
            ];

            require "src/views/finanzas/presidente.php";
        } else {
            // Si es vecino (lo haremos después), le muestra su vista de recibos
            // require "src/views/finanzas/vecino.php";
            echo "<h1>Vista del vecino en construcción...</h1>";
        }
    }
}
?>