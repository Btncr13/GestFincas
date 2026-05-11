<?php
require_once "src/models/UsuarioModel.php";
require_once "src/models/MatriculaModel.php";

class MatriculaController
{
    private $usuarioModel;
    private $matriculaModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->matriculaModel = new MatriculaModel($pdo);
    }

    private function getViewData()
    {
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'id_vivienda'     => $_SESSION['vivienda']['id_vivienda'],
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? $_SESSION['vivienda']['rol']
        ];
    }

    public function index()
    {
        extract($this->getViewData());
        $matriculas = $this->matriculaModel->getMatriculasPorVivienda($id_vivienda);

        // Contadores para deshabilitar botones en la vista si llegan al límite
        $cantHabitual = $this->matriculaModel->contarPorTipo($id_vivienda, 'habitual');
        $cantInvitado = $this->matriculaModel->contarPorTipo($id_vivienda, 'invitado');

        require "src/views/matricula/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'];
            $matricula = $_POST['matricula'] ?? '';
            $uso = $_POST['uso_matricula'] ?? 'habitual';
            $marca = $_POST['marca'] ?? '';
            $nombre_invitado = $_POST['nombre_invitado'] ?? null;

            // Validaciones de negocio
            if (empty($matricula) || empty($marca)) {
                $_SESSION['parking_error'] = "La matrícula y la marca son obligatorias.";
                header("Location: index.php?route=matricula/index");
                exit;
            }

            if ($this->matriculaModel->matriculaExisteEnVivienda($id_vivienda, $matricula)) {
                $_SESSION['parking_error'] = "Esta matrícula ya está registrada en tu vivienda.";
                header("Location: index.php?route=matricula/index");
                exit;
            }

            // Comprobar límites
            if ($uso === 'habitual') {
                if ($this->matriculaModel->contarPorTipo($id_vivienda, 'habitual') >= 4) {
                    $_SESSION['parking_error'] = "Has alcanzado el límite de 4 matrículas habituales.";
                    header("Location: index.php?route=matricula/index");
                    exit;
                }
            } else {
                if ($this->matriculaModel->contarPorTipo($id_vivienda, 'invitado') >= 2) {
                    $_SESSION['parking_error'] = "Has alcanzado el límite de 2 matrículas de invitados.";
                    header("Location: index.php?route=matricula/index");
                    exit;
                }
            }

            if ($this->matriculaModel->registrar($id_vivienda, $matricula, $uso, $marca, $nombre_invitado)) {
                $_SESSION['parking_success'] = "Matrícula registrada correctamente.";
            } else {
                $_SESSION['parking_error'] = "Error al registrar la matrícula.";
            }
        }
        header("Location: index.php?route=matricula/index");
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_matricula = $_POST['id_matricula'] ?? null;
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'];

            if ($id_matricula && $this->matriculaModel->eliminar($id_matricula, $id_vivienda)) {
                $_SESSION['parking_success'] = "Matrícula eliminada.";
            } else {
                $_SESSION['parking_error'] = "No se pudo eliminar la matrícula.";
            }
        }
        header("Location: index.php?route=matricula/index");
    }
}
