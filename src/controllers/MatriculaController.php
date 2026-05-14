<?php
require_once "src/models/UsuarioModel.php";
require_once "src/models/MatriculaModel.php";
/**
 * Controlador para la gestión de matrículas de vehículos en la comunidad.
 * Permite a los vecinos registrar y eliminar sus matrículas, y a los presidentes
 * visualizar todas las matrículas de la comunidad.
 */
class MatriculaController
{
    /** @var UsuarioModel */
    private $usuarioModel;

    /** @var MatriculaModel */
    private $matriculaModel;

    /**
     * Constructor de la clase MatriculaController.
     * @param PDO $pdo Objeto PDO para la conexión a la base de datos.
     */
    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->matriculaModel = new MatriculaModel($pdo);
    }

    /**
     * Prepara los datos comunes necesarios para las vistas (topbar, sidebar, etc.).
     * 
     * @return array{nombreComunidad: string, nombreVivienda: string, direccion: string, id_vivienda: int, id_comunidad: int|null, rolReal: string, rol: string}
     */
    private function getViewData(): array
    {
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'id_vivienda'     => $_SESSION['vivienda']['id_vivienda'] ?? 0,
            'id_comunidad'    => $_SESSION['vivienda']['id_comunidad'] ?? null,
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? $_SESSION['vivienda']['rol']
        ];
    }

    /**
     * Muestra la página principal de gestión de parking.
     * Adapta la vista y los datos según el rol del usuario (vecino o presidente).
     */
    public function index()
    {
        // Desestructuración de array: asignación explícita que el IDE reconoce perfectamente
        [
            'nombreComunidad' => $nombreComunidad,
            'nombreVivienda'  => $nombreVivienda,
            'direccion'       => $direccion,
            'id_vivienda'     => $id_vivienda,
            'id_comunidad'    => $id_comunidad,
            'rolReal'         => $rolReal,
            'rol'             => $rol
        ] = $this->getViewData();

        $todasMatriculas = [];
        if ($rol === 'presidente') {
            $rawMatriculas = $this->matriculaModel->getMatriculasComunidad($id_comunidad);
            $hoy = date('Y-m-d');

            $todasMatriculas = array_filter($rawMatriculas, function ($m) use ($hoy) {
                if ($m['uso_matricula'] === 'invitado') {
                    return date('Y-m-d', strtotime($m['fecha_entrada'])) >= $hoy;
                }
                return true;
            });
        }

        $matriculas = $this->matriculaModel->getMatriculasPorVivienda($id_vivienda);
        $cantHabitual = $this->matriculaModel->contarPorTipo($id_vivienda, 'habitual');
        $cantInvitado = $this->matriculaModel->contarPorTipo($id_vivienda, 'invitado');

        require "src/views/matricula/index.php";
    }

    /**
     * Procesa la solicitud para registrar una nueva matrícula.
     * Realiza validaciones de negocio (campos obligatorios, límites, duplicados)
     * y redirige con mensajes de éxito o error.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /** @var string $matricula, $uso, $marca, $nombre_invitado, $fecha_entrada */
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'];
            $matricula = $_POST['matricula'] ?? '';
            $uso = $_POST['uso_matricula'] ?? 'habitual';
            $marca = $_POST['marca'] ?? '';
            $nombre_invitado = $_POST['nombre_invitado'] ?? null;
            $fecha_entrada = $_POST['fecha_entrada'] ?? null;

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

                if (empty($fecha_entrada)) {
                    $_SESSION['parking_error'] = "La fecha de entrada es obligatoria para invitados.";
                    header("Location: index.php?route=matricula/index");
                    exit;
                }
            }

            if ($this->matriculaModel->registrar($id_vivienda, $matricula, $uso, $marca, $nombre_invitado, $fecha_entrada)) {
                $_SESSION['parking_success'] = "Matrícula registrada correctamente.";
            } else {
                $_SESSION['parking_error'] = "Error al registrar la matrícula.";
            }
        }
        header("Location: index.php?route=matricula/index");
    }

    /**
     * Procesa la solicitud para eliminar una matrícula.
     * Solo permite eliminar matrículas que pertenecen a la vivienda del usuario.
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /** @var int|null $id_matricula ID de la matrícula a eliminar. */
            $id_matricula = $_POST['id_matricula'] ?? null;
            /** @var int $id_vivienda ID de la vivienda del usuario que intenta eliminar la matrícula. */
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
