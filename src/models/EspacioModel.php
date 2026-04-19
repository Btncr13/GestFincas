<?php
// Asegúrate de que la ruta a BaseModel sea la correcta en tu proyecto
require_once __DIR__ . '/../../config/BaseModel.php'; 

class EspacioModel extends BaseModel {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    // El presidente crea un espacio directamente en su comunidad
    public function crearEspacioCompleto($datos) {
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO espacios_comunidad (id_comunidad, nombre_espacio, aforo, max_personas, hora_apertura, hora_cierre, duracion_uso, bloqueado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_comunidad'], $datos['nombre_espacio'], $datos['aforo'],
                $datos['max_personas'], $datos['hora_apertura'], $datos['hora_cierre'],
                $datos['duracion_uso'], $datos['bloqueado']
            ]);
            $idEspacio = $this->db->lastInsertId();

            if (!empty($datos['normas'])) {
                $sqlNorma = "INSERT INTO espacios_normas (id_espacios_comunidad, norma) VALUES (?, ?)";
                $this->db->prepare($sqlNorma)->execute([$idEspacio, $datos['normas']]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    public function modificarEspacio($data) {
        try {
            $sql = "UPDATE espacios_comunidad 
                    SET nombre_espacio = :nombre_espacio, max_personas = :max_personas, 
                        hora_apertura = :hora_apertura, hora_cierre = :hora_cierre, duracion_uso = :duracion_uso 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad AND id_comunidad = :id_comunidad";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre_espacio', $data['nombre_espacio'], PDO::PARAM_STR);
            $stmt->bindParam(':max_personas', $data['max_personas'], PDO::PARAM_INT);
            $stmt->bindParam(':hora_apertura', $data['hora_apertura'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_cierre', $data['hora_cierre'], PDO::PARAM_STR);
            $stmt->bindParam(':duracion_uso', $data['duracion_uso'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id_comunidad', $data['id_comunidad'], PDO::PARAM_INT); // Seguridad RBAC
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en modificarEspacio: " . $e->getMessage());
            return false;
        }
    }

    // 0 = Activo/Operativo, 1 = Bloqueado
    public function bloquearEspacio($id_espacios_comunidad, $bloqueado, $motivo = null) {
        try {
            $sql = "UPDATE espacios_comunidad 
                    SET bloqueado = :bloqueado, motivo = :motivo 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':bloqueado', $bloqueado, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':id_espacios_comunidad', $id_espacios_comunidad, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en bloquearEspacio: " . $e->getMessage());
            return false;
        }
    }

    public function getEspaciosByComunidad($id_comunidad) {
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
}