<?php
require_once 'BaseModel.php';

class ReservaModel extends BaseModel {

    public function getEspaciosDisponibles($id_comunidad) {
        try {
            $sql = "SELECT e.id_espacio, e.nombre, e.descripcion, ec.id_espacios_comunidad
                    FROM espacios e
                    JOIN espacios_comunidad ec ON e.id_espacio = ec.id_espacio
                    WHERE ec.id_comunidad = :id_comunidad";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspaciosDisponibles: " . $e->getMessage());
            return []; // Retornamos array vacío para no quebrar el frontend
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

    public function verificarCuotas($id_usuario, $fecha) {
        try {
            // Regla: 1 al día
            $sqlDia = "SELECT COUNT(*) as total FROM reservas 
                       WHERE id_usuario = :id_usuario AND fecha = :fecha";
            $stmtDia = $this->db->prepare($sqlDia);
            $stmtDia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtDia->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmtDia->execute();
            $resDia = $stmtDia->fetch(PDO::FETCH_ASSOC);
            
            if ($resDia['total'] >= 1) {
                return ['status' => false, 'msg' => 'Ya tienes una reserva para este día.'];
            }

            // Regla: 3 a la semana
            $sqlSemana = "SELECT COUNT(*) as total FROM reservas 
                          WHERE id_usuario = :id_usuario AND YEARWEEK(fecha, 1) = YEARWEEK(:fecha, 1)";
            $stmtSemana = $this->db->prepare($sqlSemana);
            $stmtSemana->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtSemana->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmtSemana->execute();
            $resSemana = $stmtSemana->fetch(PDO::FETCH_ASSOC);

            if ($resSemana['total'] >= 3) {
                return ['status' => false, 'msg' => 'Cupo semanal agotado (máx 3).'];
            }

            return ['status' => true];
        } catch (PDOException $e) {
            error_log("Error en verificarCuotas: " . $e->getMessage());
            return ['status' => false, 'msg' => 'Error interno al validar las cuotas. Inténtelo más tarde.'];
        }
    }

    public function crearReserva($data) {
        try {
            $sql = "INSERT INTO reservas (id_usuario, id_espacio, fecha, hora_inicio, hora_fin) 
                    VALUES (:id_usuario, :id_espacio, :fecha, :hora_inicio, :hora_fin)";
            
            $stmt = $this->db->prepare($sql);
            
            // Asignación de parámetros con su tipo de dato correspondiente
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacio', $data['id_espacio'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $data['fecha'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            
            return $stmt->execute(); // Retorna true si tiene éxito
        } catch (PDOException $e) {
            error_log("Error en crearReserva: " . $e->getMessage());
            return false;
        }
    }

    public function getReservasUsuario($id_usuario) {
        try {
            $sql = "SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin, e.nombre as espacio_nombre 
                    FROM reservas r 
                    JOIN espacios e ON r.id_espacio = e.id_espacio 
                    WHERE r.id_usuario = :id_usuario 
                    ORDER BY r.fecha DESC, r.hora_inicio DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getReservasUsuario: " . $e->getMessage());
            return [];
        }
    }
    public function eliminarReserva($id_reserva, $id_usuario) {
        try {
            // El WHERE id_usuario = :id_usuario es crucial para RBAC (el vecino solo borra lo suyo)
            $sql = "DELETE FROM reservas 
                    WHERE id_reserva = :id_reserva AND id_usuario = :id_usuario";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            // Execute devuelve true si se ejecutó sin errores sintácticos.
            // Para confirmar que realmente se borró, verificamos rowCount().
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return true;
            }
            return false; // No se borró nada (quizás la reserva no existía o no era del usuario)
            
        } catch (PDOException $e) {
            error_log("Error en eliminarReserva: " . $e->getMessage());
            return false;
        }
    }

    public function modificarReserva($data) {
        try {
            // Actualizamos los datos siempre y cuando la reserva pertenezca al usuario
            $sql = "UPDATE reservas 
                    SET id_espacio = :id_espacio, 
                        fecha = :fecha, 
                        hora_inicio = :hora_inicio, 
                        hora_fin = :hora_fin 
                    WHERE id_reserva = :id_reserva AND id_usuario = :id_usuario";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':id_espacio', $data['id_espacio'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $data['fecha'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            
            // Claves de identificación y seguridad
            $stmt->bindParam(':id_reserva', $data['id_reserva'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en modificarReserva: " . $e->getMessage());
            return false;
        }
    }
//___________ MÉTODOS PARA PANEL DE PRESIDENTE (GESTIÓN DE RESERVAS) __________

   public function getTodasLasReservasComunidad($id_comunidad) {
        try {
            // Obtenemos los datos del vecino y calculamos si es Activa/Inactiva dinámicamente
            $sql = "SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin, 
                           e.nombre as espacio_nombre, u.nombre as vecino_nombre, u.apellidos,
                           IF(r.fecha >= CURDATE(), 'Activa', 'Inactiva') as estado_reserva
                    FROM reservas r
                    JOIN espacios e ON r.id_espacio = e.id_espacio
                    JOIN espacios_comunidad ec ON e.id_espacio = ec.id_espacio
                    JOIN usuario u ON r.id_usuario = u.id_usuario
                    WHERE ec.id_comunidad = :id_comunidad
                    ORDER BY r.fecha DESC, r.hora_inicio DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTodasLasReservasComunidad: " . $e->getMessage());
            return [];
        }
    }
}
