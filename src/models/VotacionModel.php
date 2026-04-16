<?php
/**
 * Modelo VotacionModel
 * Encargado de toda la lógica de persistencia de datos relacionada con las votaciones.
 * Sigue el patrón MVC interactuando directamente con la base de datos a través de PDO.
 */
require_once "config/BaseModel.php";

class VotacionModel extends BaseModel
{
    /**
     * Obtiene las votaciones que pertenecen a una comunidad y están marcadas como activas.
     * @param int $id_comunidad
     * @return array Lista de votaciones ordenadas por fecha de creación.
     */
    public function getVotacionesActivas($id_comunidad)
    {
        $sql = "SELECT * FROM votacion WHERE id_comunidad = :id AND activa = 1 ORDER BY fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_comunidad]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera las opciones de respuesta personalizadas asociadas a una votación específica.
     * @param int $id_votacion
     * @return array Opciones disponibles para votar.
     */
    public function getOpciones($id_votacion)
    {
        $sql = "SELECT * FROM votacion_opcion WHERE id_votacion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_votacion]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica si un usuario ya ha emitido un voto para una votación concreta (evita duplicados).
     * Esto garantiza la regla de "un usuario, un voto".
     * @param int $id_votacion
     * @param int $id_usuario
     * @return bool True si ya votó, False en caso contrario.
     */
    public function haVotado($id_votacion, $id_usuario)
    {
        $sql = "SELECT 1 FROM voto WHERE id_votacion = :v AND id_usuario = :u LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['v' => $id_votacion, 'u' => $id_usuario]);
        return (bool)$stmt->fetch();
    }

    /**
     * Guarda un nuevo voto en la base de datos.
     */
    public function registrarVoto($id_votacion, $id_usuario, $id_opcion)
    {
        $sql = "INSERT INTO voto (id_votacion, id_usuario, id_opcion) VALUES (:v, :u, :o)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'v' => $id_votacion,
            'u' => $id_usuario,
            'o' => $id_opcion
        ]);
    }

    /**
     * Crea una votación completa.
     * Implementa el concepto de ATOMICIDAD (ACID): O se hace todo (votación y opciones) 
     * o no se hace nada si ocurre un error, evitando datos "huérfanos".
     */
    public function crearVotacion($id_comunidad, $titulo, $descripcion, $opciones, $fecha_limite = null)
    {
        try {
            /**
             * ATOMICIDAD (ACID): Iniciamos una transacción.
             * Si algo falla al insertar las opciones, la votación no se crea.
             */
            $this->db->beginTransaction();

            $sql = "INSERT INTO votacion (id_comunidad, titulo, descripcion, fecha_limite, activa) VALUES (?, ?, ?, ?, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_comunidad, $titulo, $descripcion, $fecha_limite]);
            $id_votacion = $this->db->lastInsertId();

            // Insertar las opciones
            $sqlOp = "INSERT INTO votacion_opcion (id_votacion, texto) VALUES (?, ?)";
            $stmtOp = $this->db->prepare($sqlOp);
            foreach ($opciones as $texto) {
                $stmtOp->execute([$id_votacion, $texto]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Calcula los resultados en tiempo real:
     * 1. Total de votos.
     * 2. Censo total de la comunidad (usando un JOIN complejo).
     * 3. Desglose de votos por cada opción de respuesta.
     */
    public function getResultados($id_votacion)
    {
        // 1. Total de votos emitidos
        $stmtVotos = $this->db->prepare("SELECT COUNT(*) as total FROM voto WHERE id_votacion = :id");
        $stmtVotos->execute(['id' => $id_votacion]);
        $totalVotos = $stmtVotos->fetch()['total'];

        // 2. Obtener el Censo (Total usuarios de la comunidad a la que pertenece esta votación)
        $sqlCenso = "SELECT COUNT(u.id_usuario) as censo 
                     FROM usuario u 
                     JOIN vivienda v ON u.id_vivienda = v.id_vivienda 
                     WHERE v.id_comunidad = (SELECT id_comunidad FROM votacion WHERE id_votacion = :id)";
        $stmtCenso = $this->db->prepare($sqlCenso);
        $stmtCenso->execute(['id' => $id_votacion]);
        $censo = $stmtCenso->fetch()['censo'] ?? 0;

        // 3. Detalle por cada opción (usamos LEFT JOIN para ver incluso opciones con 0 votos)
        $sqlDetalle = "SELECT vo.texto, COUNT(v.id_voto) as total 
                       FROM votacion_opcion vo
                       LEFT JOIN voto v ON vo.id_opcion = v.id_opcion
                       WHERE vo.id_votacion = :id
                       GROUP BY vo.id_opcion";
        $stmtDetalle = $this->db->prepare($sqlDetalle);
        $stmtDetalle->execute(['id' => $id_votacion]);
        $detalle = $stmtDetalle->fetchAll();

        return [
            'total'      => $totalVotos,
            'censo'      => $censo,
            'pendientes' => max(0, $censo - $totalVotos),
            'detalle'    => $detalle
        ];
    }

    public function eliminarVotacion($id_votacion)
    {
        $sql = "DELETE FROM votacion WHERE id_votacion = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id_votacion]);
    }
}