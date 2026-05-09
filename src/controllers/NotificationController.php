<?php
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/ComunicacionesModel.php';
require_once __DIR__ . '/../models/IncidenciasModel.php';

class NotificationController
{
    private $reservaModel;
    private $comunicacionesModel;
    private $incidenciasModel;

    public function __construct($pdo)
    {
        $this->reservaModel = new ReservaModel($pdo);
        $this->comunicacionesModel = new ComunicacionesModel($pdo);
        $this->incidenciasModel = new IncidenciasModel($pdo);
    }

    /**
     * Prepara el array estructurado y limpio para alimentar la vista del Toast.
     */
    public function getNotificationsForView($id_usuario, $id_comunidad, $rol = 'vecino')
    {
        $notificaciones = [];

        try {
            // 1. Reservas
            $reservasHoy = $this->reservaModel->tieneReservaHoy($id_usuario);
            $reservasManana = $this->reservaModel->tieneReservaManana($id_usuario);
            
            if ($reservasHoy || $reservasManana) {
                $mensaje = $reservasHoy ? 'Tienes una reserva programada para hoy.' : 'Mañana tienes una reserva programada.';
                $notificaciones[] = [
                    'key'      => 'reservas',
                    'titulo'   => 'Recordatorio de Reserva',
                    'mensaje'  => $mensaje,
                    'color'    => 'bg-success',
                    'link'     => 'index.php?route=reserva/index',
                    'btn_text' => 'Ir a mis reservas'
                ];
            }

            // 2. Comunicaciones
            $comunicadosImportantes = $this->comunicacionesModel->contarNoLeidosImportantes($id_usuario);
            if ($comunicadosImportantes > 0) {
                $notificaciones[] = [
                    'key'      => 'comunicados',
                    'titulo'   => 'Avisos Importantes',
                    'mensaje'  => "Tienes $comunicadosImportantes comunicado(s) importante(s) pendiente(s).",
                    'color'    => 'bg-primary',
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
                    'mensaje'  => "Hay $incidenciasUrgentes incidencia(s) urgente(s) en la comunidad.",
                    'color'    => 'bg-danger',
                    'link'     => 'index.php?route=incidencias/index',
                    'btn_text' => 'Revisar'
                ];
            }
        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>