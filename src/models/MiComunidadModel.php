<?php
require_once "config/BaseModel.php";

class MiComunidadModel extends BaseModel
{
    public function getUsuariosPorComunidad($id_comunidad)
    {
        try {
            $sql = "SELECT u.id_usuario as id, v.nombre as vivienda, u.nombre, u.apellidos, u.email, u.rol
                    FROM usuario u
                    JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                    WHERE v.id_comunidad = :id_comunidad
                    ORDER BY v.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
}
?>