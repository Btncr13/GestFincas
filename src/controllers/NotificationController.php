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
            // 1. Reservas (Específicas del usuario para cumplir con Privacidad)
            $reservas = $this->reservaModel->getReservasUsuario($id_usuario);
            if (is_array($reservas)) {
                foreach ($reservas as $res) {
                    $fecha_reserva = strtotime($res['fecha_reserva']);
                    $hoy = strtotime('today');
                    $manana = strtotime('+1 day', $hoy);
                    
                    if ($res['estado_reserva'] === 'activo' && ($fecha_reserva == $hoy || $fecha_reserva == $manana)) {
                        $dia_texto = ($fecha_reserva == $hoy) ? 'hoy' : 'mañana';
                        $hora = date('H:i', strtotime($res['hora_inicio']));
                        $notificaciones[] = [
                            'key'      => 'reserva_' . $res['id_reserva'],
                            'titulo'   => "Reserva para $dia_texto",
                            'mensaje'  => "Reserva en " . htmlspecialchars($res['nombre_espacio']) . " a las $hora.",
                            'color'    => 'bg-success',
                            'text_color' => 'text_success',
                            'border_color' => 'var(--bs-success)',
                            'icon'     => 'bi bi-calendar-check-fill',
                            'link'     => 'index.php?route=reserva/index',
                            'btn_text' => 'Ir a mis reservas',
                            'badge'    => null
                        ];
                    }
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
                    'btn_text' => 'Leer avisos',
                    'badge'    => null
                ];
            }

            // 3. Incidencias (Ahora entidades individuales y muestran estado actual en tiempo real)
            $incidencias = $this->incidenciasModel->obtenerIncidenciasPorComunidad($id_comunidad);
            if (is_array($incidencias)) {
                foreach ($incidencias as $inc) {
                    // Evitamos cargar las resueltas para no llenar el panel
                    if (in_array(strtolower($inc['estado']), ['pendiente', 'abierta', 'urgente'])) {
                        
                        $estado = strtolower($inc['estado']);
                        $badgeClass = 'bg-secondary';
                        if ($estado === 'pendiente') $badgeClass = 'bg-warning text-dark';
                        if ($estado === 'abierta') $badgeClass = 'bg-primary';
                        if ($estado === 'urgente') $badgeClass = 'bg-danger';

                        $notificaciones[] = [
                            'key'      => 'incidencia_' . $inc['id_incidencias'],
                            'titulo'   => 'Incidencia de Comunidad',
                            'mensaje'  => 'Incidencia: ' . htmlspecialchars($inc['titulo']),
                            'color'    => 'bg-danger',
                            'text_color' => 'text-danger',
                            'border_color' => 'var(--bs-danger)',
                            'icon'     => 'fa-solid fa-triangle-exclamation',
                            'link'     => 'index.php?route=incidencias/index',
                            'btn_text' => 'Revisar',
                            'badge'    => [
                                'text'  => ucfirst($estado),
                                'class' => $badgeClass
                            ]
                        ];
                    }
                }
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
                    'btn_text' => 'Votar',
                    'badge'    => null
                ];
            }

            // 5. Reuniones
            $reuniones = $this->reunionModel->getReunionesComunidad($id_comunidad);
            if (is_array($reuniones)) {
                foreach ($reuniones as $r) {
                    if (isset($r['fecha']) && strtotime($r['fecha']) >= strtotime('today')) {
                        $fecha_formateada = date('d/m/Y', strtotime($r['fecha']));
                        $notificaciones[] = [
                            'key'      => 'reunion_' . $r['id_reunion'],
                            'titulo'   => 'Próxima Reunión',
                            'mensaje'  => htmlspecialchars($r['titulo']) . " el $fecha_formateada.",
                            'color'    => 'bg-warning',
                            'text_color' => 'text-warning',
                            'border_color' => 'var(--bs-warning)',
                            'icon'     => 'bi bi-calendar-event-fill',
                            'link'     => 'index.php?route=reunion/reuniones',
                            'btn_text' => 'Ver convocatorias',
                            'badge'    => null
                        ];
                    }
                }
            }

        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>