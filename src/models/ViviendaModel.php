<?php

require_once "config/BaseModel.php";

class ViviendaModel extends BaseModel
{
    // ----------------------------------------------- OBTENER DETALLE DE LAS VIVIENDAS
    public function getViviendaDetalle($id_vivienda)
    {
        try {
            $sql = "SELECT v.*, c.nombre as nombre_comunidad 
                    FROM vivienda v
                    JOIN comunidad c ON v.id_comunidad = c.id_comunidad
                    WHERE v.id_vivienda = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id_vivienda]);
            $vivienda = $stmt->fetch();

            if (!$vivienda) {
                return ['success' => false, 'message' => 'Vivienda no encontrada.'];
            }

            return ['success' => true, 'data' => $vivienda];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al obtener vivienda: ' . $e->getMessage()];
        }
    }

    // ----------------------------------------------- CREAR VIVIENDA Y CÓDIGO (TRANSACCIÓN)
    public function crearViviendaConCodigo($id_comunidad, $nombre_vivienda, $codigo)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar la vivienda
            $sqlV = "INSERT INTO vivienda (id_comunidad, nombre) VALUES (:id_c, :nom)";
            $stmtV = $this->db->prepare($sqlV);
            $stmtV->execute(['id_c' => $id_comunidad, 'nom' => $nombre_vivienda]);
            $id_vivienda = $this->db->lastInsertId();

            // 2. Insertar el código de validación asociado
            $sqlC = "INSERT INTO codigo_validacion (id_vivienda, codigo, usado) VALUES (:id_v, :cod, 0)";
            $stmtC = $this->db->prepare($sqlC);
            $stmtC->execute(['id_v' => $id_vivienda, 'cod' => $codigo]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al crear vivienda/código: " . $e->getMessage());
            return false;
        }
    }

    // ----------------------------------------------- ELIMINACIÓN DEFINITIVA DE VIVIENDA Y USUARIO
    public function eliminarViviendaCompleta($id_vivienda)
    {
        try {
            $this->db->beginTransaction();

            // 1. Borrar usuario asociado (si existe)
            $sqlU = "DELETE FROM usuario WHERE id_vivienda = :id";
            $this->db->prepare($sqlU)->execute(['id' => $id_vivienda]);

            // 2. Borrar códigos de validación
            $sqlC = "DELETE FROM codigo_validacion WHERE id_vivienda = :id";
            $this->db->prepare($sqlC)->execute(['id' => $id_vivienda]);

            // 3. Borrar la vivienda
            $sqlV = "DELETE FROM vivienda WHERE id_vivienda = :id";
            $this->db->prepare($sqlV)->execute(['id' => $id_vivienda]);

            return $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }
}
?>