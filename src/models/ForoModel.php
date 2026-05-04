<?php
/**
 * Modelo ForoModel
 * Encargado de las operaciones de base de datos para el Foro Vecinal.
 * Implementa aislamiento por id_comunidad para garantizar la privacidad.
 */
require_once "config/BaseModel.php";

class ForoModel extends BaseModel
{
    /**
     * Obtiene todos los temas, ASEGURANDO que solo sean de la comunidad especificada.
     */
    public function getTemasByComunidad($id_comunidad, $categoria = null)
    {
        $params = ['id_comunidad' => $id_comunidad];
        $filtroCategoria = "";
        
        if ($categoria) {
            $filtroCategoria = " AND t.categoria = :categoria ";
            $params['categoria'] = $categoria;
        }

        $sql = "SELECT t.*, u.nombre, u.apellidos, u.rol, v.nombre as nombre_vivienda,
                       (SELECT COUNT(*) FROM foro_mensaje m WHERE m.id_tema = t.id_tema) as total_respuestas,
                       (SELECT MAX(fecha_creacion) FROM foro_mensaje m WHERE m.id_tema = t.id_tema) as ultimo_mensaje
                FROM foro_tema t
                JOIN usuario u ON t.id_usuario = u.id_usuario
                JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                WHERE t.id_comunidad = :id_comunidad" . $filtroCategoria . "
                ORDER BY COALESCE(ultimo_mensaje, t.fecha_creacion) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un tema específico, VALIDANDO que pertenezca a la comunidad del usuario que lo pide.
     * (Previene ataques IDOR).
     */
    public function getTemaById($id_tema, $id_comunidad)
    {
        $sql = "SELECT t.*, u.nombre, u.apellidos, u.rol, v.nombre as nombre_vivienda 
                FROM foro_tema t
                JOIN usuario u ON t.id_usuario = u.id_usuario
                JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                WHERE t.id_tema = :id_tema AND t.id_comunidad = :id_comunidad LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_tema' => $id_tema, 'id_comunidad' => $id_comunidad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearTema($id_comunidad, $id_usuario, $titulo, $descripcion, $categoria = 'general')
    {
        $sql = "INSERT INTO foro_tema (id_comunidad, id_usuario, titulo, descripcion, categoria, estado, fecha_creacion) 
                VALUES (:id_comunidad, :id_usuario, :titulo, :descripcion, :categoria, 'abierto', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_comunidad' => $id_comunidad,
            'id_usuario' => $id_usuario,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'categoria' => $categoria
        ]);
        return $this->db->lastInsertId();
    }

    public function getMensajesByTema($id_tema)
    {
        $sql = "SELECT m.*, u.nombre, u.apellidos, v.nombre as nombre_vivienda, u.rol 
                FROM foro_mensaje m
                JOIN usuario u ON m.id_usuario = u.id_usuario
                JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                WHERE m.id_tema = :id_tema
                ORDER BY m.fecha_creacion ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_tema' => $id_tema]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function añadirMensaje($id_tema, $id_usuario, $mensaje)
    {
        $sql = "INSERT INTO foro_mensaje (id_tema, id_usuario, mensaje, fecha_creacion) 
                VALUES (:id_tema, :id_usuario, :mensaje, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_tema' => $id_tema,
            'id_usuario' => $id_usuario,
            'mensaje' => $mensaje
        ]);
    }
    
    public function cambiarEstadoTema($id_tema, $estado)
    {
        $sql = "UPDATE foro_tema SET estado = :estado WHERE id_tema = :id_tema";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['estado' => $estado, 'id_tema' => $id_tema]);
    }

    // --- NUEVAS FUNCIONES DE EDICIÓN Y BORRADO ---

    public function getMensajeById($id_mensaje)
    {
        $sql = "SELECT * FROM foro_mensaje WHERE id_mensaje = :id_mensaje LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_mensaje' => $id_mensaje]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminarTema($id_tema)
    {
        // Gracias al ON DELETE CASCADE de la base de datos, borrar el tema borrará las respuestas automáticamente
        $sql = "DELETE FROM foro_tema WHERE id_tema = :id_tema";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_tema' => $id_tema]);
    }

    public function editarTema($id_tema, $titulo, $descripcion)
    {
        $sql = "UPDATE foro_tema SET titulo = :titulo, descripcion = :descripcion WHERE id_tema = :id_tema";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['titulo' => $titulo, 'descripcion' => $descripcion, 'id_tema' => $id_tema]);
    }

    public function eliminarMensaje($id_mensaje)
    {
        $sql = "DELETE FROM foro_mensaje WHERE id_mensaje = :id_mensaje";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_mensaje' => $id_mensaje]);
    }

    public function editarMensaje($id_mensaje, $mensaje)
    {
        $sql = "UPDATE foro_mensaje SET mensaje = :mensaje WHERE id_mensaje = :id_mensaje";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['mensaje' => $mensaje, 'id_mensaje' => $id_mensaje]);
    }
}