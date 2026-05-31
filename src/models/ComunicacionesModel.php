<?php
require_once "config/BaseModel.php";

class ComunicacionesModel extends BaseModel
{
    public function getComunicadosPorComunidad($id_comunidad)
    {
        $sql = "SELECT * FROM comunicados WHERE id_comunidad = :id ORDER BY fecha_publicacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_comunidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearComunicado($id_comunidad, $titulo, $cuerpo, $tipo)
    {
        $sql = "INSERT INTO comunicados (id_comunidad, titulo, cuerpo, tipo) VALUES (:id, :t, :c, :tipo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id_comunidad,
            't' => $titulo,
            'c' => $cuerpo,
            'tipo' => $tipo
        ]);
    }

    public function getComunicadoById($id_comunicado)
    {
        $sql = "SELECT * FROM comunicados WHERE id_comunicado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_comunicado]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lógica para notificaciones
    public function contarNoLeidos($id_comunidad, $id_usuario)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM comunicados c 
                WHERE c.id_comunidad = :id_c 
                AND c.id_comunicado NOT IN (
                    SELECT id_comunicado FROM comunicado_lectura WHERE id_usuario = :id_u
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_c' => $id_comunidad, 'id_u' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    /**
     * Cuenta los comunicados no leídos que tienen un flag de importante o urgente.
     */
    public function contarNoLeidosImportantes($id_usuario)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM comunicados c 
                    JOIN comunidad com ON c.id_comunidad = com.id_comunidad
                    JOIN vivienda v ON com.id_comunidad = v.id_comunidad
                    JOIN usuario u ON v.id_vivienda = u.id_vivienda
                    WHERE u.id_usuario = :id_usuario AND c.tipo IN ('normal', 'importante', 'urgente') 
                    AND c.id_comunicado NOT IN (SELECT id_comunicado FROM comunicado_lectura WHERE id_usuario = :id_usuario_lectura)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_usuario' => $id_usuario, 'id_usuario_lectura' => $id_usuario]);
            return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarNoLeidosImportantes: " . $e->getMessage());
            return 0;
        }
    }

    public function marcarComoLeido($id_comunicado, $id_usuario)
    {
        $sql = "INSERT IGNORE INTO comunicado_lectura (id_comunicado, id_usuario) VALUES (:id_c, :id_u)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_c' => $id_comunicado, 'id_u' => $id_usuario]);
    }

    public function eliminarComunicado($id_comunicado, $id_comunidad)
    {
        $sql = "DELETE FROM comunicados WHERE id_comunicado = :id_com AND id_comunidad = :id_comu";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_com' => $id_comunicado,
            'id_comu' => $id_comunidad
        ]);
    }
}
