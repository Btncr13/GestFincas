<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class IncidenciasModel extends BaseModel
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Obtener absolutamente TODAS las incidencias de la comunidad para que JS las filtre
    public function obtenerIncidenciasPorComunidad($id_comunidad)
    {
        $sql = "SELECT i.*, v.nombre AS nombre_vivienda 
                FROM incidencias i 
                JOIN vivienda v ON i.id_vivienda = v.id_vivienda 
                WHERE v.id_comunidad = :id_comunidad
                ORDER BY i.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_comunidad' => $id_comunidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener incidencias (Regla: Todas las abiertas + Resueltas de los últimos 3 meses)
    public function obtenerIncidenciasGlobales()
    {
        $sql = "SELECT i.*, v.nombre AS nombre_vivienda 
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

    // Obtener incidencias con filtros dinámicos (Mis/Otras, Estado, Tiempo)
    public function obtenerIncidenciasFiltradas($id_comunidad, $id_vivienda, $rol, $tipo_vista, $estado, $tiempo)
    {
        $sql = "SELECT i.*, v.nombre AS nombre_vivienda 
                FROM incidencias i 
                JOIN vivienda v ON i.id_vivienda = v.id_vivienda 
                WHERE v.id_comunidad = :id_comunidad";
        
        $params = [':id_comunidad' => $id_comunidad];

        // Filtro por Estado (Pendiente, Abierta, Resuelta)
        if (in_array($estado, ['pendiente', 'abierta', 'resuelta'])) {
            $sql .= " AND i.estado = :estado";
            $params[':estado'] = $estado;
        }

        // Filtro por Propiedad (Solo afecta al rol vecino)
        if (strtolower($rol) === 'vecino') {
            if ($tipo_vista === 'mis') {
                $sql .= " AND i.id_vivienda = :id_vivienda";
                $params[':id_vivienda'] = $id_vivienda;
            } elseif ($tipo_vista === 'otras') {
                $sql .= " AND i.id_vivienda != :id_vivienda";
                $params[':id_vivienda'] = $id_vivienda;
            }
        }

        // Filtro por Tiempo
        if ($tiempo === 'anio_actual') {
            $sql .= " AND YEAR(i.fecha_creacion) = YEAR(CURDATE())";
        } elseif ($tiempo === 'ultimos_3_meses') {
            $sql .= " AND i.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        } elseif ($tiempo === 'mensual') {
            $sql .= " AND YEAR(i.fecha_creacion) = YEAR(CURDATE()) AND MONTH(i.fecha_creacion) = MONTH(CURDATE())";
        }

        $sql .= " ORDER BY i.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca incidencias similares utilizando un índice FULLTEXT y un umbral de relevancia.
     */
    public function buscarIncidenciasSimilares($texto_normalizado, $umbral = 2.5)
    {
        $sql = "SELECT id_incidencias, titulo, texto_normalizado, fecha_creacion,
                       MATCH(texto_normalizado) AGAINST(:texto1 IN NATURAL LANGUAGE MODE) AS score 
                FROM incidencias 
                WHERE estado IN ('pendiente', 'abierta') 
                  AND MATCH(texto_normalizado) AGAINST(:texto2 IN NATURAL LANGUAGE MODE) > :umbral
                ORDER BY score DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':texto1', $texto_normalizado, PDO::PARAM_STR);
        $stmt->bindValue(':texto2', $texto_normalizado, PDO::PARAM_STR);
        $stmt->bindValue(':umbral', $umbral, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear nueva incidencia
    public function crear($id_vivienda, $titulo, $texto_normalizado, $descripcion, $foto = null)
    {
        $sql = "INSERT INTO incidencias (id_vivienda, titulo, texto_normalizado, descripcion, foto_incidencia, estado, numero_afectados) 
                VALUES (:id_vivienda, :titulo, :texto_normalizado, :descripcion, :foto, 'pendiente', 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_vivienda' => $id_vivienda,
            ':titulo' => $titulo,
            ':texto_normalizado' => $texto_normalizado,
            ':descripcion' => $descripcion,
            ':foto' => $foto
        ]);
        return $this->db->lastInsertId();
    }

    // Obtener incidencias a las que el usuario se ha unido (para la UI)
    public function obtenerMisUniones($id_vivienda)
    {
        try {
            $sql = "SELECT id_incidencias FROM incidencias_uniones WHERE id_vivienda = :id_vivienda";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_vivienda', $id_vivienda, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve array: [1, 5, 8]
        } catch (PDOException $e) {
            return []; // Retorna vacío si la tabla no existe aún
        }
    }

    // Unirse a una incidencia existente
    public function unirse($id_incidencia, $id_vivienda)
    {
        try {
            $this->db->beginTransaction();

            // 1. Guardar la acción (evita duplicados si se hace doble clic rápido)
            $sqlUnion = "INSERT INTO incidencias_uniones (id_incidencias, id_vivienda) VALUES (:id_incidencia, :id_vivienda)";
            $stmtUnion = $this->db->prepare($sqlUnion);
            $stmtUnion->execute([':id_incidencia' => $id_incidencia, ':id_vivienda' => $id_vivienda]);

            // 2. Incrementar contador oficial
            $sql = "UPDATE incidencias SET numero_afectados = numero_afectados + 1 WHERE id_incidencias = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id_incidencia]);

            $this->db->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($e->getCode() == 23000) return ['success' => false, 'message' => 'Ya te has unido a esta incidencia.'];
            return ['success' => false, 'message' => 'Error de BD al intentar unirse.'];
        }
    }

    public function eliminar($id_incidencia, $id_vivienda, $rol)
    {
        try {
            if ($rol === 'PRESIDENTE' || $rol === 'SUPERADMIN') {
                // El presidente tiene permisos absolutos de borrado
                $sql = "DELETE FROM incidencias WHERE id_incidencias = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id_incidencia, PDO::PARAM_INT);
            } else {
                // El vecino/propietario solo puede borrar SUS incidencias
                $sql = "DELETE FROM incidencias WHERE id_incidencias = :id AND id_vivienda = :id_vivienda";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id_incidencia, PDO::PARAM_INT);
                $stmt->bindParam(':id_vivienda', $id_vivienda, PDO::PARAM_INT);
            }

            // Execute retorna TRUE si funciona, rowCount > 0 confirma si afectó filas (si existía)
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminar incidencia: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id_incidencia, $nuevo_estado)
    {
        try {
            // 'pendiente', 'abierta', 'resuelta'
            $sql = "UPDATE incidencias SET estado = :estado WHERE id_incidencias = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $nuevo_estado, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id_incidencia, PDO::PARAM_INT);

            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de la incidencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta las incidencias urgentes para la comunidad actual usando RBAC.
     */
    public function contarUrgentes($id_comunidad, $id_usuario, $rol)
    {
        try {
            $sql = "SELECT COUNT(*) FROM incidencias i 
                    LEFT JOIN vivienda v ON i.id_vivienda = v.id_vivienda 
                    WHERE v.id_comunidad = :id_comunidad AND i.estado = 'urgente'";

            // RBAC: Si es vecino, solo cuenta las de áreas comunes o las de su propia vivienda
            if (strtolower($rol) === 'vecino') {
                $sql .= " AND (i.id_vivienda IS NULL OR i.id_vivienda = (SELECT id_vivienda FROM usuario WHERE id_usuario = :id_usuario))";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            if (strtolower($rol) === 'vecino') {
                $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarUrgentes: " . $e->getMessage());
            return 0;
        }
    }
}
