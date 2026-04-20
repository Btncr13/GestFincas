<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class ReservaModel extends BaseModel
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Para el Vecino: Solo se muestran los espacios que NO están bloqueados
    public function getEspaciosDisponibles($id_comunidad)
    {
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

    public function hayCapacidad($id_espacios_comunidad, $fecha, $hora_inicio, $hora_fin, $nuevos_asistentes = 1)
    {

        // 1. Obtener aforo
        $sql = "SELECT aforo FROM espacios_comunidad WHERE id_espacios_comunidad = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_espacios_comunidad]);
        $aforo = $stmt->fetchColumn();

        // 2. Ocupación actual
        $sql = "SELECT COALESCE(SUM(asistentes), 0)
            FROM reservas
            WHERE id_espacios_comunidad = ?
            AND fecha_reserva = ?
            AND estado_reserva = 'activo'
            AND (hora_inicio < ? AND hora_fin > ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $id_espacios_comunidad,
            $fecha,
            $hora_fin,
            $hora_inicio
        ]);

        $ocupacion = $stmt->fetchColumn();

        // 3. Validación real
        return ($ocupacion + $nuevos_asistentes) <= $aforo;
    }


    public function getEspacioById($id_espacios_comunidad)
    {
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

    public function getNormasByEspacio($id_espacios_comunidad)
    {
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

    public function verificarCuotas($id_usuario, $fecha_reserva)
    {
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


    // ------------------------------------- -------------------------------------------------------GESTIÓN RESERVAS

    // --------------------------------------------------- CREAR RESERVA
    public function crearReserva($data)
    {

        try {
            // 1. INICIAR TRANSACCIÓN
            $this->db->beginTransaction();

            // 2. BLOQUEO/REVALIDACIÓN FINAL DE AFORO
            $sqlCheck = "SELECT SUM(asistentes) 
                    FROM reservas
                    WHERE id_espacios_comunidad = ?
                    AND fecha_reserva = ?
                    AND hora_inicio < ?
                    AND hora_fin > ?
                    AND estado_reserva = 'activo'
                    FOR UPDATE";

            $stmt = $this->db->prepare($sqlCheck);
            $stmt->execute([
                $data['id_espacios_comunidad'],
                $data['fecha_reserva'],
                $data['hora_fin'],
                $data['hora_inicio']
            ]);

            $ocupado = $stmt->fetchColumn() ?? 0;

            // 3. OBTENER AFORO DEL ESPACIO
            $sqlAforo = "SELECT aforo 
                     FROM espacios_comunidad 
                     WHERE id_espacios_comunidad = ? 
                     FOR UPDATE";

            $stmt = $this->db->prepare($sqlAforo);
            $stmt->execute([$data['id_espacios_comunidad']]);
            $aforo = $stmt->fetchColumn();

            // 4. VALIDACIÓN FINAL
            if (($ocupado + $data['asistentes']) > $aforo) {
                $this->db->rollBack();
                return false;
            }

            // 5. INSERT RESERVA
            $sql = "INSERT INTO reservas (
                    id_usuario,
                    id_espacios_comunidad,
                    fecha_reserva,
                    hora_inicio,
                    hora_fin,
                    estado_reserva,
                    asistentes
                ) VALUES (
                    :id_usuario,
                    :id_espacios_comunidad,
                    :fecha_reserva,
                    :hora_inicio,
                    :hora_fin,
                    'activo',
                    :asistentes
                )";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_reserva', $data['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':asistentes', $data['asistentes'], PDO::PARAM_INT);

            $stmt->execute();

            $idReserva = $this->db->lastInsertId();

            // 6. CONFIRMAR TRANSACCIÓN
            $this->db->commit();

            return $idReserva;
        } catch (PDOException $e) {

            // 7. ROLLBACK EN CASO DE ERROR
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log("Error en crearReserva: " . $e->getMessage());
            return false;
        }
    }

    // --------------------------------------------------- LEER RESERVAS POR ID_RESERVAS

    public function getReservaById($id)
    {
        $sql = "SELECT r.*, ec.nombre_espacio
            FROM reservas r
            JOIN espacios_comunidad ec 
                ON ec.id_espacios_comunidad = r.id_espacios_comunidad
            WHERE r.id_reservas = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // --------------------------------------------------- LEER RESERVAS POR ID_USUARIO
    public function getReservasUsuario($id_usuario)
    {
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

    // --------------------------------------------------- LEER TODAS LAS RESERVAS DE LA COMUNIDAD
    public function getTodasLasReservasComunidad($id_comunidad)
    {
        try {
            $sql = "SELECT r.id_reservas as id_reserva, r.fecha_reserva as fecha, r.hora_inicio, r.hora_fin, r.estado_reserva, r.asistentes,
                           ec.nombre_espacio, u.nombre as vecino_nombre, u.apellidos 
                    FROM reservas r
                    JOIN espacios_comunidad ec ON r.id_espacios_comunidad = ec.id_espacios_comunidad
                    JOIN usuario u ON r.id_usuario = u.id_usuario
                    WHERE ec.id_comunidad = :id_comunidad 
                      AND (
                        YEARWEEK(r.fecha_reserva, 1) = YEARWEEK(CURDATE(), 1)
                        OR (r.estado_reserva = 'inactivo' AND YEARWEEK(r.fecha_reserva, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1))
                      )
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

    // --------------------------------------------------- ACTUALIZAR RESERVAS VENCIDAS
    public function actualizarReservasVencidas()
    {
        try {
            $ahora = date('Y-m-d H:i:s');

            $sql = "UPDATE reservas 
                SET estado_reserva = 'inactivo'
                WHERE estado_reserva = 'activo'
                AND CONCAT(fecha_reserva, ' ', hora_fin) < ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ahora]);

            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizarReservasVencidas: " . $e->getMessage());
            return false;
        }
    }

    //  --------------------------------------------------- ELIMINAR RESERVA
   //  --------------------------------------------------- ELIMINAR RESERVA
    public function eliminarReserva($id_reservas, $id_usuario, $rol = 'VECINO')
    {
        try {
            // Normalizamos el rol a mayúsculas para evitar fallos por case-sensitivity
            $rol = strtoupper($rol);

            if ($rol === 'PRESIDENTE' || $rol === 'SUPERADMIN') {
                // El Presidente tiene poder de superusuario sobre las reservas: 
                // Ignoramos quién la creó, solo necesitamos el ID de la reserva.
                $sql = "DELETE FROM reservas WHERE id_reservas = :id_reservas";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_reservas', $id_reservas, PDO::PARAM_INT);
            } else {
                // Regla estricta para vecinos: Solo pueden borrar si la reserva es SUYA.
                $sql = "DELETE FROM reservas WHERE id_reservas = :id_reservas AND id_usuario = :id_usuario";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_reservas', $id_reservas, PDO::PARAM_INT);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }

            // Ejecutamos y verificamos si realmente se eliminó alguna fila
            return $stmt->execute() && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error en eliminarReserva: " . $e->getMessage());
            return false;
        }
    }

}
