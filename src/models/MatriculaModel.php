<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class MatriculaModel extends BaseModel
{
    public function getMatriculasPorVivienda($id_vivienda)
    {
        $sql = "SELECT * FROM matriculas WHERE id_vivienda = :id ORDER BY uso_matricula ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_vivienda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatriculasComunidad($id_comunidad)
    {
        $sql = "SELECT m.*, v.nombre as vivienda 
                FROM matriculas m 
                JOIN vivienda v ON m.id_vivienda = v.id_vivienda 
                WHERE v.id_comunidad = :id_comunidad 
                ORDER BY v.nombre ASC, m.uso_matricula ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_comunidad' => $id_comunidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarPorTipo($id_vivienda, $tipo)
    {
        $sql = "SELECT COUNT(*) FROM matriculas WHERE id_vivienda = :id AND uso_matricula = :tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_vivienda, 'tipo' => $tipo]);
        return (int)$stmt->fetchColumn();
    }

    public function registrar($id_vivienda, $matricula, $uso, $marca, $nombre_invitado = null, $fecha_entrada = null)
    {
        try {
            $sql = "INSERT INTO matriculas (id_vivienda, matricula, uso_matricula, marca_vehículo, nombre_invitado, fecha_entrada) 
                    VALUES (:id_v, :mat, :uso, :marca, :nom_inv, :f_ent)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id_v'  => $id_vivienda,
                'mat'   => strtoupper(trim($matricula)),
                'uso'   => $uso,
                'marca' => trim($marca),
                'nom_inv' => ($uso === 'invitado') ? trim($nombre_invitado) : null,
                'f_ent' => ($uso === 'invitado') ? $fecha_entrada : date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("Error en MatriculaModel::registrar: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id_matricula, $id_vivienda)
    {
        $sql = "DELETE FROM matriculas WHERE id_matricula = :id_m AND id_vivienda = :id_v";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_m' => $id_matricula,
            'id_v' => $id_vivienda
        ]);
    }

    public function matriculaExisteEnVivienda($id_vivienda, $matricula)
    {
        $sql = "SELECT COUNT(*) FROM matriculas WHERE id_vivienda = :id AND matricula = :mat";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id_vivienda,
            'mat' => strtoupper(trim($matricula))
        ]);
        return $stmt->fetchColumn() > 0;
    }
}
