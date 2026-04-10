<?php

require_once "../../config/BaseModel.php";

class ViviendaModel extends BaseModel
{
    /**
     * Obtener información de una vivienda y su comunidad
     */
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
}
?>