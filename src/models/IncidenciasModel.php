<?php
require_once __DIR__ . '/../../config/database.php';

class IncidenciasModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

   // Obtener incidencias (Regla: Todas las abiertas + Resueltas de los últimos 3 meses)
    public function obtenerIncidenciasGlobales() {
        $sql = "SELECT i.*, v.nombre_vivienda 
                FROM incidencias i 
                LEFT JOIN vivienda v ON i.id_vivienda = v.id_vivienda 
                WHERE i.estado IN ('pendiente', 'abierta') 
                   OR (i.estado = 'resuelta' AND i.fecha_actualizacion >= DATE_SUB(NOW(), INTERVAL 3 MONTH))
                ORDER BY i.estado ASC, i.fecha_creacion DESC";
                // Ordenamos para que las pendientes/abiertas salgan primero
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar incidencias activas similares
    public function buscarSimilares($titulo_normalizado) {
        $sql = "SELECT * FROM incidencias 
                WHERE titulo_normalizado = :titulo_normalizado 
                AND estado IN ('pendiente', 'abierta') LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':titulo_normalizado', $titulo_normalizado, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear nueva incidencia
    public function crear($id_vivienda, $titulo, $titulo_norm, $descripcion, $foto = null) {
        $sql = "INSERT INTO incidencias (id_vivienda, titulo, titulo_normalizado, descripcion, foto, estado, numero_afectados) 
                VALUES (:id_vivienda, :titulo, :titulo_norm, :descripcion, :foto, 'pendiente', 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_vivienda' => $id_vivienda,
            ':titulo' => $titulo,
            ':titulo_norm' => $titulo_norm,
            ':descripcion' => $descripcion,
            ':foto' => $foto
        ]);
        return $this->db->lastInsertId();
    }

    // Unirse a una incidencia existente
    public function unirse($id_incidencia) {
        $sql = "UPDATE incidencias SET numero_afectados = numero_afectados + 1 WHERE id_incidencias = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id_incidencia]);
    }

    // Cambiar estado (Solo Presidente)
    public function actualizarEstado($id_incidencia, $estado) {
        $sql = "UPDATE incidencias SET estado = :estado WHERE id_incidencias = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id' => $id_incidencia]);
    }

    // Eliminar incidencia (Solo creador original)
    public function eliminar($id_incidencia, $id_vivienda, $rol) {
        if ($rol === 'PRESIDENTE' || $rol === 'SUPERADMIN') {
            $sql = "DELETE FROM incidencias WHERE id_incidencias = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id_incidencia]);
        } else {
            $sql = "DELETE FROM incidencias WHERE id_incidencias = :id AND id_vivienda = :id_vivienda";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id_incidencia, ':id_vivienda' => $id_vivienda]);
        }
    }
}