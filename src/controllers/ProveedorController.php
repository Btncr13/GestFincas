<?php
require_once "src/models/ProveedorModel.php";
class ProveedorController
{
    private $proveedorModel;

    // En el constructor, verificamos la sesión y creamos una instancia del modelo.
    // Si no hay sesión, redirigimos al login.
    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->proveedorModel = new ProveedorModel($pdo);
    }

    // Un helper para obtener datos comunes de la sesión para las vistas.
    private function getViewData()
    {
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'id_comunidad'    => $_SESSION['vivienda']['id_comunidad'],
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? $_SESSION['vivienda']['rol']
        ];
    }

    // Método principal: obtiene los proveedores, los agrupa y carga la vista.
    public function index()
    {
        extract($this->getViewData());
 
        $proveedores = $this->proveedorModel->getProveedoresPorComunidad($id_comunidad);

        // Agrupar por categoría para la vista
        $proveedoresAgrupados = [];
        foreach ($proveedores as $p) {
            $proveedoresAgrupados[$p['categoria']][] = $p;
        }

        require "src/views/proveedor/index.php"; // Carga la vista correcta
    }

    // Procesa la creación de un nuevo proveedor.
    // Solo permite la acción si el método es POST y el rol es 'presidente'.
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $datos = [
                'id_comunidad' => $_SESSION['vivienda']['id_comunidad'],
                'nombre' => trim($_POST['nombre'] ?? ''),
                'categoria' => $_POST['categoria'] ?? 'Otros',
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'horario' => trim($_POST['horario'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            if (!empty($datos['nombre']) && !empty($datos['categoria'])) {
                $this->proveedorModel->crearProveedor($datos);
            }
        }
        header("Location: index.php?route=proveedor/index");
        exit;
    }

    // Procesa la actualización de un proveedor existente.
    // Misma lógica de seguridad que store().
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $datos = [
                'id_proveedor' => $_POST['id_proveedor'] ?? null,
                'id_comunidad' => $_SESSION['vivienda']['id_comunidad'],
                'nombre' => trim($_POST['nombre'] ?? ''),
                'categoria' => $_POST['categoria'] ?? 'Otros',
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'horario' => trim($_POST['horario'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            if (!empty($datos['id_proveedor']) && !empty($datos['nombre'])) {
                $this->proveedorModel->actualizarProveedor($datos);
            }
        }
        header("Location: index.php?route=proveedor/index");
        exit;
    }

    // Procesa la eliminación de un proveedor.
    // Misma lógica de seguridad.
    public function destroy()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $id_proveedor = $_POST['id_proveedor_eliminar'] ?? null;
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            if ($id_proveedor) {
                $this->proveedorModel->eliminarProveedor($id_proveedor, $id_comunidad);
            }
        }
        header("Location: index.php?route=proveedor/index");
        exit;
    }
}