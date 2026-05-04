<?php
require_once __DIR__ . '/../models/ForoModel.php';

class ForoController
{
    private $model;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new ForoModel($pdo);
        
        // Seguridad: Si no hay sesión, al login
        if (!isset($_SESSION['vivienda'])) {
            header('Location: index.php?route=auth/login');
            exit;
        }
    }

    /**
     * Helper para extraer las variables comunes del topbar/sidebar.
     * @return array{nombreComunidad: string, nombreVivienda: string, direccion: string, rolReal: string, rol: string, id_comunidad: int, id_usuario: int}
     */
    private function getViewData(): array
    {
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';
        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino'),
            'id_comunidad'    => $_SESSION['vivienda']['id_comunidad'],
            'id_usuario'      => $_SESSION['vivienda']['id_usuario']
        ];
    }

    // 1. Mostrar la lista de temas (Página principal del foro)
    public function index()
    {
        extract($this->getViewData());
        $categoria = $_GET['cat'] ?? null;
        $temas = $this->model->getTemasByComunidad($id_comunidad, $categoria);
        
        require_once __DIR__ . '/../views/foro/index.php';
    }

    // 2. Ver un tema específico y sus mensajes
    public function ver()
    {
        extract($this->getViewData());
        $id_tema = $_GET['id'] ?? 0;
        
        $tema = $this->model->getTemaById($id_tema, $id_comunidad);
        
        // Si el tema no existe o es de otra comunidad, lo echamos
        if (!$tema) {
            header('Location: index.php?route=foro/index');
            exit;
        }

        $mensajes = $this->model->getMensajesByTema($id_tema);
        
        require_once __DIR__ . '/../views/foro/ver.php';
    }

    // 3. Crear un nuevo tema
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria = $_POST['categoria'] ?? 'general';

            if (!empty($titulo) && !empty($descripcion)) {
                $this->model->crearTema($id_comunidad, $id_usuario, $titulo, $descripcion, $categoria);
            }
            header('Location: index.php?route=foro/index');
            exit;
        }
    }

    // 4. Responder a un tema
    public function responder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $id_tema = $_POST['id_tema'] ?? 0;
            $mensaje = trim($_POST['mensaje'] ?? '');

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            
            // Solo guardamos si el tema existe, es de su comunidad, está abierto y el mensaje no está vacío
            if ($tema && $tema['estado'] === 'abierto' && !empty($mensaje)) {
                $this->model->añadirMensaje($id_tema, $id_usuario, $mensaje);
            }
            header('Location: index.php?route=foro/ver&id=' . $id_tema);
            exit;
        }
    }

    // 5. Cambiar estado del tema (Solo presidente)
    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            
            // Seguridad: Solo el presidente puede hacer esto
            if ($rol !== 'presidente') {
                header('Location: index.php?route=foro/index');
                exit;
            }

            $id_tema = $_POST['id_tema'] ?? 0;
            $estado = $_POST['estado'] ?? 'abierto';

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            
            if ($tema) {
                $this->model->cambiarEstadoTema($id_tema, $estado);
            }
            header('Location: index.php?route=foro/ver&id=' . $id_tema);
            exit;
        }
    }

    // 6. Editar un tema
    public function editarTema()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $id_tema = $_POST['id_tema'] ?? 0;
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            
            // Permiso: SOLO el autor puede editar su propio tema
            if ($tema && ($tema['id_usuario'] == $id_usuario) && !empty($titulo) && !empty($descripcion)) {
                $this->model->editarTema($id_tema, $titulo, $descripcion);
            }
            header('Location: index.php?route=foro/ver&id=' . $id_tema);
            exit;
        }
    }

    // 7. Eliminar un tema
    public function eliminarTema()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $id_tema = $_POST['id_tema'] ?? 0;

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            
            // Permiso: es autor o presidente
            if ($tema && ($tema['id_usuario'] == $id_usuario || $rol === 'presidente')) {
                $this->model->eliminarTema($id_tema);
            }
            header('Location: index.php?route=foro/index');
            exit;
        }
    }

    // 8. Editar un mensaje
    public function editarMensaje()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $id_tema = $_POST['id_tema'] ?? 0;
            $id_mensaje = $_POST['id_mensaje'] ?? 0;
            $mensajeTexto = trim($_POST['mensaje'] ?? '');

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            $mensajeBD = $this->model->getMensajeById($id_mensaje);
            
            // Permiso: SOLO el autor puede editar su propio mensaje, y este debe pertenecer al tema
            if ($tema && $mensajeBD && $mensajeBD['id_tema'] == $id_tema && ($mensajeBD['id_usuario'] == $id_usuario) && !empty($mensajeTexto)) {
                $this->model->editarMensaje($id_mensaje, $mensajeTexto);
            }
            header('Location: index.php?route=foro/ver&id=' . $id_tema);
            exit;
        }
    }

    // 9. Eliminar un mensaje
    public function eliminarMensaje()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            extract($this->getViewData());
            $id_tema = $_POST['id_tema'] ?? 0;
            $id_mensaje = $_POST['id_mensaje'] ?? 0;

            $tema = $this->model->getTemaById($id_tema, $id_comunidad);
            $mensajeBD = $this->model->getMensajeById($id_mensaje);
            
            // Permiso: es autor o presidente
            if ($tema && $mensajeBD && $mensajeBD['id_tema'] == $id_tema && ($mensajeBD['id_usuario'] == $id_usuario || $rol === 'presidente')) {
                $this->model->eliminarMensaje($id_mensaje);
            }
            header('Location: index.php?route=foro/ver&id=' . $id_tema);
            exit;
        }
    }
}