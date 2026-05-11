<?php
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/ComunicacionesModel.php';
require_once __DIR__ . '/../models/IncidenciasModel.php';
require_once __DIR__ . '/../models/VotacionModel.php';
require_once __DIR__ . '/../models/ReunionModel.php';

class NotificationController
{
    private $reservaModel;
    private $comunicacionesModel;
    private $incidenciasModel;
    private $votacionModel;
    private $reunionModel;

    public function __construct($pdo)
    {
        $this->reservaModel = new ReservaModel($pdo);
        $this->comunicacionesModel = new ComunicacionesModel($pdo);
        $this->incidenciasModel = new IncidenciasModel($pdo);
        $this->votacionModel = new VotacionModel($pdo);
        $this->reunionModel = new ReunionModel($pdo);
    }

    /**
     * Prepara el array estructurado y limpio para alimentar la vista del Toast y del Dashboard.
     */
    public function getNotificationsForView($id_usuario, $id_comunidad, $rol = 'vecino')
    {
        $notificaciones = [];

        try {
            // 1. Reservas
            $reservasHoy = $this->reservaModel->tieneReservaHoy($id_usuario);
            if ($reservasHoy) {
                $notificaciones[] = [
                    'key'      => 'reservas',
                    'titulo'   => 'Reserva para hoy',
                    'mensaje'  => 'Tienes una reserva programada para hoy.',
                    'color'    => 'bg-success',
                    'text_color' => 'text-success',
                    'border_color' => 'var(--bs-success)',
                    'icon'     => 'bi bi-calendar-check-fill',
                    'link'     => 'index.php?route=reserva/index',
                    'btn_text' => 'Ir a mis reservas'
                ];
            } else {
                $reservasManana = $this->reservaModel->tieneReservaManana($id_usuario);
                if ($reservasManana) {
                    $notificaciones[] = [
                        'key'      => 'reservas',
                        'titulo'   => 'Reserva para mañana',
                        'mensaje'  => 'Mañana tienes una reserva programada.',
                        'color'    => 'bg-success',
                        'text_color' => 'text-success',
                        'border_color' => 'var(--bs-success)',
                        'icon'     => 'bi bi-calendar-check-fill',
                        'link'     => 'index.php?route=reserva/index',
                        'btn_text' => 'Ir a mis reservas'
                    ];
                }
            }

            // 2. Comunicaciones
            $comunicadosNoLeidos = $this->comunicacionesModel->contarNoLeidos($id_comunidad, $id_usuario);
            if ($comunicadosNoLeidos > 0) {
                $notificaciones[] = [
                    'key'      => 'comunicados',
                    'titulo'   => 'Nuevos Comunicados',
                    'mensaje'  => "Tienes $comunicadosNoLeidos comunicado(s) sin leer.",
                    'color'    => 'bg-primary',
                    'text_color' => 'text-primary',
                    'border_color' => 'var(--bs-primary)',
                    'icon'     => 'bi bi-megaphone-fill',
                    'link'     => 'index.php?route=comunicaciones/index',
                    'btn_text' => 'Leer avisos'
                ];
            }

            // 3. Incidencias
            $incidenciasUrgentes = $this->incidenciasModel->contarUrgentes($id_comunidad, $id_usuario, $rol);
            if ($incidenciasUrgentes > 0) {
                $notificaciones[] = [
                    'key'      => 'incidencias',
                    'titulo'   => 'Incidencias Urgentes',
                    'mensaje'  => "Hay $incidenciasUrgentes incidencia(s) urgente(s) reportada(s).",
                    'color'    => 'bg-danger',
                    'text_color' => 'text-danger',
                    'border_color' => 'var(--bs-danger)',
                    'icon'     => 'fa-solid fa-triangle-exclamation',
                    'link'     => 'index.php?route=incidencias/index',
                    'btn_text' => 'Revisar'
                ];
            }

            // 4. Votaciones
            $votaciones = $this->votacionModel->getVotacionesActivas($id_comunidad);
            $votacionesPendientes = 0;
            if (is_array($votaciones)) {
                foreach ($votaciones as $v) {
                    $fecha_limite = !empty($v['fecha_limite']) ? strtotime($v['fecha_limite']) : null;
                    $esta_finalizada = $fecha_limite && $fecha_limite < time();
                    if (!$esta_finalizada && !$this->votacionModel->haVotado($v['id_votacion'], $id_usuario)) {
                        $votacionesPendientes++;
                    }
                }
            }
            if ($votacionesPendientes > 0) {
                $notificaciones[] = [
                    'key'      => 'votaciones',
                    'titulo'   => 'Votaciones Pendientes',
                    'mensaje'  => "Tienes $votacionesPendientes votación(es) pendiente(s) de respuesta.",
                    'color'    => 'bg-warning',
                    'text_color' => 'text-warning',
                    'border_color' => 'var(--bs-warning)',
                    'icon'     => 'fa-solid fa-check-to-slot',
                    'link'     => 'index.php?route=votacion/index',
                    'btn_text' => 'Votar'
                ];
            }

            // 5. Reuniones
            $reuniones = $this->reunionModel->getReunionesComunidad($id_comunidad);
            $reunionesPendientes = 0;
            if (is_array($reuniones)) {
                foreach ($reuniones as $r) {
                    if (isset($r['fecha']) && strtotime($r['fecha']) >= strtotime('today')) {
                        $reunionesPendientes++;
                    }
                }
            }
            if ($reunionesPendientes > 0) {
                $notificaciones[] = [
                    'key'      => 'reuniones',
                    'titulo'   => 'Reuniones Próximas',
                    'mensaje'  => "Hay $reunionesPendientes convocatoria(s) de reunión activa(s).",
                    'color'    => 'bg-warning',
                    'text_color' => 'text-warning',
                    'border_color' => 'var(--bs-warning)',
                    'icon'     => 'bi bi-calendar-event-fill',
                    'link'     => 'index.php?route=reunion/reuniones',
                    'btn_text' => 'Ver convocatorias'
                ];
            }

        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>