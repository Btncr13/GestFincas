<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class EspacioModel extends BaseModel
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Crea un espacio y sus normas asociadas en una transacción
     */
    public function crearEspacioCompleto($datos)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en la tabla principal: espacios_comunidad
            $sql = "INSERT INTO espacios_comunidad (id_comunidad, nombre_espacio, aforo, max_personas, hora_apertura, hora_cierre, duracion_uso, bloqueado, motivo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_comunidad'],
                $datos['nombre_espacio'],
                $datos['aforo'],
                $datos['max_personas'],
                $datos['hora_apertura'],
                $datos['hora_cierre'],
                $datos['duracion_uso'],
                $datos['bloqueado'],
                $datos['motivo']
            ]);

            $idEspacio = $this->db->lastInsertId();

            // 2. Insertar en la tabla de detalle: espacios_normas
            if (!empty($datos['normas'])) {
                // Soportamos tanto array de inputs dinámicos como string de textarea
                $normasArr = is_array($datos['normas']) ? $datos['normas'] : preg_split('/\r\n|\r|\n/', $datos['normas']);

                $sqlNorma = "INSERT INTO espacios_normas (id_espacios_comunidad, descripcion) VALUES (?, ?)";
                $stmtNorma = $this->db->prepare($sqlNorma);

                foreach ($normasArr as $linea) {
                    $linea = trim($linea); // Limpiamos espacios en blanco accidentales
                    if ($linea !== '') {   // Evitamos insertar líneas vacías
                        $stmtNorma->execute([$idEspacio, $linea]);
                    }
                }
            }

            $this->db->commit();
            return $idEspacio;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en crearEspacioCompleto: " . $e->getMessage());
            return false;
        }
    }

    public function modificarEspacio($data)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE espacios_comunidad 
                    SET nombre_espacio = :nombre_espacio, aforo = :aforo, max_personas = :max_personas, 
                        hora_apertura = :hora_apertura, hora_cierre = :hora_cierre, duracion_uso = :duracion_uso 
                    WHERE id_espacios_comunidad = :id_espacios_comunidad AND id_comunidad = :id_comunidad";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre_espacio', $data['nombre_espacio'], PDO::PARAM_STR);
            $stmt->bindParam(':aforo', $data['aforo'], PDO::PARAM_INT);
            $stmt->bindParam(':max_personas', $data['max_personas'], PDO::PARAM_INT);
            $stmt->bindParam(':hora_apertura', $data['hora_apertura'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_cierre', $data['hora_cierre'], PDO::PARAM_STR);
            $stmt->bindParam(':duracion_uso', $data['duracion_uso'], PDO::PARAM_INT);
            $stmt->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id_comunidad', $data['id_comunidad'], PDO::PARAM_INT);

            $stmt->execute();

            // 2. Actualizar las normas asociadas
            // Primero, eliminar las normas existentes para este espacio
            $sqlDeleteNormas = "DELETE FROM espacios_normas WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmtDeleteNormas = $this->db->prepare($sqlDeleteNormas);
            $stmtDeleteNormas->bindParam(':id_espacios_comunidad', $data['id_espacios_comunidad'], PDO::PARAM_INT);
            $stmtDeleteNormas->execute();

            // Luego, insertar las nuevas normas si se proporcionaron
            if (!empty($data['normas'])) {
                // Soportamos tanto array de inputs dinámicos como string de textarea
                $normasArr = is_array($data['normas']) ? $data['normas'] : preg_split('/\r\n|\r|\n/', $data['normas']);

                $sqlInsertNorma = "INSERT INTO espacios_normas (id_espacios_comunidad, descripcion) VALUES (?, ?)";
                $stmtInsertNorma = $this->db->prepare($sqlInsertNorma);

                foreach ($normasArr as $linea) {
                    $linea = trim($linea);
                    if ($linea !== '') {
                        $stmtInsertNorma->execute([$data['id_espacios_comunidad'], $linea]);
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) { // Catch PDOException and other Exceptions
            error_log("Error en modificarEspacio: " . $e->getMessage());
            return false;
        }
    }

    public function bloquearEspacio($id_espacios_comunidad, $bloqueado, $motivo = null)
    {
        try {
            $this->db->beginTransaction();

            // 1. Actualizar el estado y motivo en el espacio
            $sql = "UPDATE espacios_comunidad 
                    SET bloqueado = :bloqueado, motivo = :motivo, fecha_actualizacion = NOW()
                    WHERE id_espacios_comunidad = :id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':bloqueado', $bloqueado, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', ($bloqueado == 1 ? $motivo : null), PDO::PARAM_STR);
            $stmt->bindParam(':id_espacios_comunidad', $id_espacios_comunidad, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Gestionar las reservas asociadas según el estado de bloqueo
            $sqlRes = "UPDATE reservas 
                       SET estado_reserva = :nuevo_estado 
                       WHERE id_espacios_comunidad = :id 
                       AND estado_reserva = :estado_actual 
                       AND CONCAT(fecha_reserva, ' ', hora_fin) >= NOW()";
            $stmtRes = $this->db->prepare($sqlRes);
            $stmtRes->bindParam(':id', $id_espacios_comunidad, PDO::PARAM_INT);

            if ($bloqueado == 1) {
                // Si se bloquea, inactivar reservas activas futuras
                $stmtRes->bindValue(':nuevo_estado', 'inactivo', PDO::PARAM_STR);
                $stmtRes->bindValue(':estado_actual', 'activo', PDO::PARAM_STR);
                $stmtRes->execute();
            } elseif ($bloqueado == 0) {
                // Si se desbloquea, reactivar reservas inactivas futuras (que fueron inactivadas por el bloqueo)
                $stmtRes->bindValue(':nuevo_estado', 'activo', PDO::PARAM_STR);
                $stmtRes->bindValue(':estado_actual', 'inactivo', PDO::PARAM_STR);
                $stmtRes->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error en bloquearEspacio: " . $e->getMessage());
            return false;
        }
    }

    public function getEspaciosByComunidad($id_comunidad)
    {
        try {
            $sql = "SELECT * FROM espacios_comunidad WHERE id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEspaciosByComunidad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los espacios de una comunidad, incluyendo sus normas asociadas.
     *
     * @param int $id_comunidad El ID de la comunidad.
     * @return array Un array de espacios, cada uno con un array de 'normas'.
     */
    public function getEspaciosByComunidadConNormas($id_comunidad)
    {
        try {
            $sql = "SELECT ec.*, GROUP_CONCAT(en.descripcion ORDER BY en.id_espacios_normas ASC SEPARATOR '|||') AS normas_str
                    FROM espacios_comunidad ec
                    LEFT JOIN espacios_normas en ON ec.id_espacios_comunidad = en.id_espacios_comunidad
                    WHERE ec.id_comunidad = :id_comunidad
                    GROUP BY ec.id_espacios_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_comunidad', $id_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            $espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Procesar la cadena de normas en un array
            foreach ($espacios as &$espacio) {
                $espacio['normas'] = !empty($espacio['normas_str']) ? explode('|||', $espacio['normas_str']) : [];
                unset($espacio['normas_str']); // Eliminar la cadena original
            }
            return $espacios;
        } catch (PDOException $e) {
            error_log("Error en getEspaciosByComunidadConNormas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las normas de uso para un espacio comunitario específico.
     * @param int $id_espacios_comunidad El ID del espacio.
     * @return array Un array de strings con las descripciones de las normas.
     */
    public function getNormasByEspacio($id_espacios_comunidad)
    {
        try {
            $sql = "SELECT descripcion FROM espacios_normas WHERE id_espacios_comunidad = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_espacios_comunidad, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch just the descriptions
        } catch (PDOException $e) {
            error_log("Error en getNormasByEspacio: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Verifica si un espacio tiene reservas activas que aún no han concluido.
     * Esto protege la integridad del servicio para los vecinos.
     */
    public function tieneReservasPendientes($id_espacio)
    {
        try {
            $sql = "SELECT COUNT(*) FROM reservas 
                    WHERE id_espacios_comunidad = :id 
                    AND estado_reserva = 'activo' 
                    AND CONCAT(fecha_reserva, ' ', hora_fin) >= NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_espacio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en tieneReservasPendientes: " . $e->getMessage());
            return true; // Por seguridad, si falla la consulta impedimos el borrado
        }
    }

    public function eliminarEspacio($id_espacio)
    {
        try {
            $this->db->beginTransaction();
            // 1. Eliminamos las reservas asociadas (necesario por integridad referencial)
            $this->db->prepare("DELETE FROM reservas WHERE id_espacios_comunidad = ?")->execute([$id_espacio]);
            // Primero eliminamos las normas asociadas por integridad referencial
            $this->db->prepare("DELETE FROM espacios_normas WHERE id_espacios_comunidad = ?")->execute([$id_espacio]);
            // Luego el espacio
            $stmt = $this->db->prepare("DELETE FROM espacios_comunidad WHERE id_espacios_comunidad = ?");
            $stmt->execute([$id_espacio]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en eliminarEspacio: " . $e->getMessage());
            return false;
        }
    }
}