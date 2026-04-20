<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class EspacioModel extends BaseModel
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Crea un espacio y sus normas asociadas en una transacción
     */
    public function crearEspacioCompleto($datos)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en la tabla principal: espacios_comunidad
            $sql = "INSERT INTO espacios_comunidad (id_comunidad, nombre_espacio, aforo, max_personas, hora_apertura, hora_cierre, duracion_uso, bloqueado, motivo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_comunidad'],
                $datos['nombre_espacio'],
                $datos['aforo'],
                $datos['max_personas'],
                $datos['hora_apertura'],
                $datos['hora_cierre'],
                $datos['duracion_uso'],
                $datos['bloqueado'],
                $datos['motivo']
            ]);

            $idEspacio = $this->db->lastInsertId();

            // 2. Insertar en la tabla de detalle: espacios_normas
            // Ajustado a: id_espacios_comunidad y descripcion
            if (!empty($datos['normas'])) {
                // Dividimos el texto del textarea por saltos de línea (regex para soportar Windows/Linux)
                $normasArr = preg_split('/\r\n|\r|\n/', $datos['normas']);
                
                $sqlNorma = "INSERT INTO espacios_normas (id_espacios_comunidad, descripcion) VALUES (?, ?)";
                $stmtNorma = $this->db->prepare($sqlNorma);

                foreach ($normasArr as $linea) {
                    $linea = trim($linea); // Limpiamos espacios en blanco accidentales
                    if ($linea !== '') {   // Evitamos insertar líneas vacías
                        $stmtNorma->execute([$idEspacio, $linea]);
                    }
                }
            }

            $this->db->commit();
            return $idEspacio;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en crearEspacioCompleto: " . $e->getMessage());
            return false;
        }
    }

    public function modificarEspacio($data)
    {
        try {
            $sql = "UPDATE espacios_comunidad 
                    SET nombre_espacio = :nombre_espacio, aforo = :aforo, max_personas = :max_personas, 
                        hora_apertura = :hora_apertura, hora_cierre = :hora_cierre, duracion_uso = :duracion_uso 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad AND id_comunidad = :id_comunidad";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre_espacio', $data['nombre_espacio'], PDO::PARAM_STR);
            $stmt->bindParam(':aforo', $data['aforo'], PDO::PARAM_INT);
            $stmt->bindParam(':max_personas', $data['max_personas'], PDO::PARAM_INT);
            $stmt->bindParam(':hora_apertura', $data['hora_apertura'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_cierre', $data['hora_cierre'], PDO::PARAM_STR);
            $stmt->bindParam(':duracion_uso', $data['duracion_uso'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id_comunidad', $data['id_comunidad'], PDO::PARAM_INT);

            // Devolvemos simplemente el resultado de la ejecución. 
            // Si el usuario no cambia nada, rowCount es 0 pero la ejecución es exitosa.
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en modificarEspacio: " . $e->getMessage());
            return false;
        }
    }

    public function bloquearEspacio($id_espacios_comunidad, $bloqueado, $motivo = null)
    {
        try {
            $sql = "UPDATE espacios_comunidad 
                    SET bloqueado = :bloqueado, motivo = :motivo 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':bloqueado', $bloqueado, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':id_espacios_comunidad', $id_espacios_comunidad, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en bloquearEspacio: " . $e->getMessage());
            return false;
        }
    }

    public function getEspaciosByComunidad($id_comunidad)
    {
        try {
            $sql = "SELECT * FROM espacios_comunidad WHERE id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspaciosByComunidad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un espacio tiene reservas activas que aún no han concluido.
     * Esto protege la integridad del servicio para los vecinos.
     */
    public function tieneReservasPendientes($id_espacio)
    {
        try {
            $sql = "SELECT COUNT(*) FROM reservas 
                    WHERE id_espacios_comunidad = :id 
                    AND estado_reserva = 'activo' 
                    AND CONCAT(fecha_reserva, ' ', hora_fin) >= NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_espacio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en tieneReservasPendientes: " . $e->getMessage());
            return true; // Por seguridad, si falla la consulta impedimos el borrado
        }
    }

    public function eliminarEspacio($id_espacio)
    {
        try {
            $this->db->beginTransaction();
            // Primero eliminamos las normas asociadas por integridad referencial
            $this->db->prepare("DELETE FROM espacios_normas WHERE id_espacios_comunidad = ?")->execute([$id_espacio]);
            // Luego el espacio
            $stmt = $this->db->prepare("DELETE FROM espacios_comunidad WHERE id_espacios_comunidad = ?");
            $stmt->execute([$id_espacio]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en eliminarEspacio: " . $e->getMessage());
            return false;
        }
    }
}
