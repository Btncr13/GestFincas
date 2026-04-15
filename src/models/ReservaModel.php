<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class ReservaModel extends BaseModel {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    // Para el Vecino: Solo se muestran los espacios que NO están bloqueados
    public function getEspaciosDisponibles($id_comunidad) {
        try {
            $sql = "SELECT * FROM espacios_comunidad 
                    WHERE id_comunidad = :id_comunidad AND bloqueado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspaciosDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function getEspacioById($id_espacios_comunidad) {
        try {
            $sql = "SELECT * FROM espacios_comunidad WHERE id_espacios_comunidad = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_espacios_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspacioById: " . $e->getMessage());
            return false;
        }
    }

    public function getNormasByEspacio($id_espacios_comunidad) {
        try {
            $sql = "SELECT descripcion FROM espacios_normas 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_espacios_comunidad', $id_espacios_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getNormasByEspacio: " . $e->getMessage());
            return [];
        }
    }

    public function verificarCuotas($id_usuario, $fecha_reserva) {
        try {
            // Regla: 1 al día
            $sqlDia = "SELECT COUNT(*) as total FROM reservas 
                       WHERE id_usuario = :id_usuario AND fecha_reserva = :fecha_reserva";
            $stmtDia = $this->db->prepare($sqlDia);
            $stmtDia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtDia->bindParam(':fecha_reserva', $fecha_reserva, PDO::PARAM_STR);
            $stmtDia->execute();
            $resDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
            
            if ($resDia['total'] >= 1) return ['status' => false, 'msg' => 'Ya tienes una reserva para este día.'];

            // Regla: 3 a la semana
            $sqlSemana = "SELECT COUNT(*) as total FROM reservas 
                          WHERE id_usuario = :id_usuario AND YEARWEEK(fecha_reserva, 1) = YEARWEEK(:fecha_reserva, 1)";
            $stmtSemana = $this->db->prepare($sqlSemana);
            $stmtSemana->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtSemana->bindParam(':fecha_reserva', $fecha_reserva, PDO::PARAM_STR);
            $stmtSemana->execute();
            $resSemana = $stmtSemana->fetch(PDO::FETCH_ASSOC);

            if ($resSemana['total'] >= 3) return ['status' => false, 'msg' => 'Cupo semanal agotado (máx 3).'];

            return ['status' => true];
        } catch (PDOException $e) {
            error_log("Error en verificarCuotas: " . $e->getMessage());
            return ['status' => false, 'msg' => 'Error interno al validar las cuotas.'];
        }
    }

    public function crearReserva($data) {
        try {
            // Asumimos que al crear, el estado_reserva es 'activo' (Enum)
            $sql = "INSERT INTO reservas (id_usuario, id_espacios_comunidad, fecha_reserva, hora_inicio, hora_fin, estado_reserva, asistentes) 
                    VALUES (:id_usuario, :id_espacios_comunidad, :fecha_reserva, :hora_inicio, :hora_fin, 'activo', :asistentes)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_reserva', $data['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':asistentes', $data['asistentes'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en crearReserva: " . $e->getMessage());
            return false;
        }
    }

    // Para la vista del vecino (Mis Reservas)
    public function getReservasUsuario($id_usuario) {
        try {
            $sql = "SELECT r.id_reservas as id_reserva, r.fecha_reserva as fecha, r.hora_inicio, r.hora_fin, r.estado_reserva, r.asistentes, 
                           ec.nombre_espacio 
                    FROM reservas r 
                    JOIN espacios_comunidad ec ON r.id_espacios_comunidad = ec.id_espacios_comunidad 
                    WHERE r.id_usuario = :id_usuario 
                    ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getReservasUsuario: " . $e->getMessage());
            return [];
        }
    }

    // Para la vista de auditoría del Presidente
    public function getTodasLasReservasComunidad($id_comunidad) {
        try {
            $sql = "SELECT r.id_reservas as id_reserva, r.fecha_reserva as fecha, r.hora_inicio, r.hora_fin, r.estado_reserva, r.asistentes,
                           ec.nombre_espacio, u.nombre as vecino_nombre, u.apellidos 
                    FROM reservas r
                    JOIN espacios_comunidad ec ON r.id_espacios_comunidad = ec.id_espacios_comunidad
                    JOIN usuario u ON r.id_usuario = u.id_usuario
                    WHERE ec.id_comunidad = :id_comunidad 
                      AND YEARWEEK(r.fecha_reserva, 1) = YEARWEEK(CURDATE(), 1)
                    ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTodasLasReservasComunidad: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarReserva($id_reservas, $id_usuario) {
        try {
            // Utilizamos id_reservas en lugar de id_reserva
            $sql = "DELETE FROM reservas WHERE id_reservas = :id_reservas AND id_usuario = :id_usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reservas', $id_reservas, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarReserva: " . $e->getMessage());
            return false;
        }
    }
}