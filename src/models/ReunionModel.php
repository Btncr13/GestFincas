<?php

require_once "config/BaseModel.php";

class ReunionModel extends BaseModel
{
    // 🟢 [TÍTULO IMPORTANTE - NO BORRAR] OBTENER TODAS LAS REUNIONES DE LA COMUNIDAD 🟢
    public function getReunionesComunidad($id_comunidad)
    {
        // Obtenemos todas las reuniones de esta comunidad
        $sql = "SELECT * FROM reunion WHERE id_comunidad = :id_comunidad ORDER BY fecha DESC, hora DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_comunidad' => $id_comunidad]);
        $reuniones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reuniones as &$r) {
            $r['ordenDelDia'] = json_decode($r['orden_del_dia'], true) ?: [];
            $r['id'] = $r['id_reunion']; // Adaptamos el nombre del campo para el frontend JS

            $sqlAsist = "SELECT a.confirmacion, a.fecha_respuesta, v.nombre as piso, v.id_vivienda 
                         FROM asistencia_reunion a
                         JOIN vivienda v ON a.id_vivienda = v.id_vivienda
                         WHERE a.id_reunion = :id_reunion
                         ORDER BY v.nombre ASC";
            $stmtA = $this->db->prepare($sqlAsist);
            $stmtA->execute(['id_reunion' => $r['id_reunion']]);

            $r['asistencias'] = $stmtA->fetchAll(PDO::FETCH_ASSOC);
        }

        // Destruimos la variable por referencia por seguridad
        unset($r);

        return $reuniones;
    }

    // 🟢 ACTUALIZAR RUTA DEL PDF DEL ORDEN DEL DÍA 🟢
    public function updatePdfOrdenDiaPath($id_reunion, $pdf_path)
    {
        try {
            $sql = "UPDATE reunion SET pdf_orden_dia = :pdf_path WHERE id_reunion = :id_reunion";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'pdf_path' => $pdf_path,
                'id_reunion' => $id_reunion
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 🟢 CREAR UNA NUEVA REUNIÓN Y SUS ASISTENCIAS 🟢
    public function crearReunion($id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $ordenDelDiaJson)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO reunion (id_comunidad, titulo, descripcion, fecha, hora, lugar, orden_del_dia, estado) 
                    VALUES (:id_comunidad, :titulo, :descripcion, :fecha, :hora, :lugar, :orden_del_dia, 'convocada')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad, 'titulo' => $titulo, 'descripcion' => $descripcion, 'fecha' => $fecha, 'hora' => $hora, 'lugar' => $lugar, 'orden_del_dia' => $ordenDelDiaJson]);

            $id_reunion = $this->db->lastInsertId();

            $sqlViv = "SELECT id_vivienda FROM vivienda WHERE id_comunidad = :id_comunidad";
            $stmtViv = $this->db->prepare($sqlViv);
            $stmtViv->execute(['id_comunidad' => $id_comunidad]);
            $viviendas = $stmtViv->fetchAll(PDO::FETCH_ASSOC);

            foreach ($viviendas as $v) {
                $sqlAsist = "INSERT INTO asistencia_reunion (id_reunion, id_vivienda, confirmacion) VALUES (:id_r, :id_v, 'pendiente')";
                $stmtA = $this->db->prepare($sqlAsist);
                $stmtA->execute(['id_r' => $id_reunion, 'id_v' => $v['id_vivienda']]);
            }

            $this->db->commit();
            return $id_reunion; // Devolvemos el ID de la reunión creada
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // 🟢 GUARDAR EL VOTO DE ASISTENCIA 🟢
    public function confirmarAsistencia($id_reunion, $id_vivienda, $confirmacion)
    {
        try {
            $sql = "INSERT INTO asistencia_reunion (id_reunion, id_vivienda, confirmacion, fecha_respuesta) 
                    VALUES (:id_reunion, :id_vivienda, :confirmacion, CURRENT_DATE())
                    ON DUPLICATE KEY UPDATE confirmacion = :confirmacion2, fecha_respuesta = CURRENT_DATE()";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'confirmacion' => $confirmacion,
                'confirmacion2' => $confirmacion,
                'id_reunion' => $id_reunion,
                'id_vivienda' => $id_vivienda
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 🟢 ACTUALIZAR DATOS DE LA REUNIÓN 🟢
    public function actualizarReunion($id_reunion, $id_comunidad, $titulo, $descripcion, $fecha, $hora, $lugar, $ordenDelDiaJson)
    {
        try {
            $sql = "UPDATE reunion 
                    SET titulo = :titulo, descripcion = :descripcion, fecha = :fecha, hora = :hora, lugar = :lugar, orden_del_dia = :orden_del_dia 
                    WHERE id_reunion = :id_reunion AND id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'fecha' => $fecha,
                'hora' => $hora,
                'lugar' => $lugar,
                'orden_del_dia' => $ordenDelDiaJson,
                'id_reunion' => $id_reunion,
                'id_comunidad' => $id_comunidad
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 🟢 ELIMINAR REUNIÓN 🟢
    public function eliminarReunion($id_reunion, $id_comunidad)
    {
        try {
            $sql = "DELETE FROM reunion WHERE id_reunion = :id_reunion AND id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id_reunion' => $id_reunion, 'id_comunidad' => $id_comunidad]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
