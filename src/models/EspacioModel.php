<?php

require_once "config/BaseModel.php";

class EspacioModel extends BaseModel {

    public function crearEspacio($id_comunidad, $nombre, $descripcion) {
        try {
            // Iniciamos la transacción para asegurar consistencia
            $this->db->beginTransaction();

            // 1. Insertamos en el catálogo de espacios
            $sqlEspacio = "INSERT INTO espacios (nombre, descripcion) VALUES (:nombre, :descripcion)";
            $stmt = $this->db->prepare($sqlEspacio);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->execute();
            
            // Obtenemos el ID generado
            $id_espacio = $this->db->lastInsertId();

            // 2. Vinculamos el espacio a la comunidad específica del Presidente
            // Asumimos que por defecto se crea con estado activo (1)
            $sqlComunidad = "INSERT INTO espacios_comunidad (id_comunidad, id_espacio, estado) 
                             VALUES (:id_comunidad, :id_espacio, 1)";
            $stmtCom = $this->db->prepare($sqlComunidad);
            $stmtCom->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmtCom->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            $stmtCom->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en crearEspacio: " . $e->getMessage());
            return false;
        }
    }
    public function getEspaciosByComunidad($id_comunidad) {
        try {
            $sql = "SELECT e.id_espacio, e.nombre, e.descripcion, ec.id_espacios_comunidad, ec.estado
                    FROM espacios e
                    JOIN espacios_comunidad ec ON e.id_espacio = ec.id_espacio
                    WHERE ec.id_comunidad = :id_comunidad";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspaciosByComunidad: " . $e->getMessage());
            return [];
        }
    }

    public function modificarEspacio($id_espacio, $nombre, $descripcion) {
        try {
            $sql = "UPDATE espacios 
                    SET nombre = :nombre, descripcion = :descripcion 
                    WHERE id_espacio = :id_espacio";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en modificarEspacio: " . $e->getMessage());
            return false;
        }
    }

    // Borrado lógico o bloqueo de la instalación por mantenimiento o sanción
    public function bloquearEspacio($id_espacios_comunidad, $estado) {
        try {
            $sql = "UPDATE espacios_comunidad 
                    SET estado = :estado 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT); // 0 = Inactivo, 1 = Activo
            $stmt->bindParam(':id_espacios_comunidad', $id_espacios_comunidad, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en bloquearEspacio: " . $e->getMessage());
            return false;
        }
    }
}
?>