<?php
require_once "config/BaseModel.php";

class MiComunidadModel extends BaseModel
{
    // 🟢 OBTENER TODOS LOS VECINOS (Nuestra función de ayer mejorada)
    public function getUsuariosPorComunidad($id_comunidad)
    {
        try {
            // Hacemos un LEFT JOIN para traer también las viviendas vacías (sin usuario asignado)
            $sql = "SELECT v.id_vivienda, v.nombre as nombre_vivienda, u.id_usuario, u.nombre, u.apellidos, u.dni, u.email, u.rol
                    FROM vivienda v
                    LEFT JOIN usuario u ON v.id_vivienda = u.id_vivienda
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

// 🟢 ESTA ES LA FUNCIÓN QUE EL CONTROLADOR NO ENCUENTRA:
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
            $sqlC = "INSERT INTO codigo_validacion (id_vivienda, codigo, usado, fecha_creacion) VALUES (:id_v, :cod, 0, NOW())";
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

    // 🟢 MODIFICAR VIVIENDA EN LA BASE DE DATOS
    public function modificarVivienda($id_vivienda, $id_comunidad, $nombre_vivienda)
    {
        try {
            $sql = "UPDATE vivienda 
                    SET nombre = :nombre 
                    WHERE id_vivienda = :id_vivienda AND id_comunidad = :id_comunidad";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'nombre'       => $nombre_vivienda,
                'id_vivienda'  => $id_vivienda,
                'id_comunidad' => $id_comunidad
            ]);
        } catch (PDOException $e) {
            error_log("Error al modificar vivienda: " . $e->getMessage());
            return false;
        }
    }

    // 🟢 ELIMINAR VIVIENDA (Lógica del compañero)
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