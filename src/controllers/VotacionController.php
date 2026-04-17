<?php
/**
 * Controlador VotacionController
 * Gestiona el flujo de las votaciones: listado, emisión de votos y creación por parte del presidente.
 */
require_once "src/models/VotacionModel.php";

class VotacionController
{
    private $votacionModel;

    /**
     * Constructor: Valida que el usuario esté logueado antes de permitir
     * el acceso a cualquier método del controlador.
     */
    public function __construct($pdo)
    {
        if (!isset($_SESSION)) { session_start(); }
        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->votacionModel = new VotacionModel($pdo);
    }

    /**
     * Muestra la página principal de votaciones.
     * Enriquece la lista de votaciones con datos dinámicos (opciones, resultados y estado del voto).
     */
    public function index()
    {
        $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
        $id_usuario = $_SESSION['vivienda']['id_usuario'];
        
        // Corregimos la asignación del rol para que soporte el "Modo Vista" (Cambiar a vecino)
        $rol = $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');

        $votaciones = $this->votacionModel->getVotacionesActivas($id_comunidad);

        // Enriquecer datos con si el usuario ya votó y resultados
        foreach ($votaciones as &$v) {
            $v['ha_votado'] = false; // Valor por defecto
            $v['ha_votado'] = $this->votacionModel->haVotado($v['id_votacion'], $id_usuario);
            $v['resultados'] = $this->votacionModel->getResultados($v['id_votacion']);
            $v['opciones'] = $this->votacionModel->getOpciones($v['id_votacion']);
        }
        unset($v); // IMPORTANTE: Rompe la referencia para evitar duplicados en la vista

        // ======== VARIABLES FALTANTES PARA TOPBAR Y SIDEBAR ========
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        $nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
        $nombreVivienda  = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';
        $direccion       = trim($calle . ' ' . $numero);
        $rolReal         = $_SESSION['vivienda']['rol'] ?? 'vecino';
        // ===========================================================

        require "src/views/votaciones/index.php";
    }

    /**
     * Acción de registrar un voto.
     */
    public function votar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_votacion = $_POST['id_votacion'];
            $id_opcion = $_POST['id_opcion'];
            $id_usuario = $_SESSION['vivienda']['id_usuario'];

            if (!$this->votacionModel->haVotado($id_votacion, $id_usuario)) {
                $this->votacionModel->registrarVoto($id_votacion, $id_usuario, $id_opcion);
            }
        }
        header("Location: index.php?route=votacion/index");
    }

    /**
     * Acción de crear una nueva votación (Solo permitida para el rol 'presidente').
     */
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['vivienda']['rol'] === 'presidente') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
            $opciones = $_POST['opciones'] ?? [];
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            // Filtrar opciones vacías
            $opciones = array_filter($opciones, function($opc) {
                return !empty(trim($opc));
            });

            if (!empty($titulo) && count($opciones) >= 2) {
                $this->votacionModel->crearVotacion($id_comunidad, $titulo, $descripcion, $opciones, $fecha_limite);
            }
        }
        header("Location: index.php?route=votacion/index");
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['vivienda']['rol'] === 'presidente') {
            $id_votacion = $_POST['id_votacion'] ?? null;
            if ($id_votacion) {
                $this->votacionModel->eliminarVotacion($id_votacion);
            }
        }
        header("Location: index.php?route=votacion/index");
    }
}