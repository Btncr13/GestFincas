<?php
require_once "config/BaseModel.php";

class ProveedorModel extends BaseModel
{
    /**
     * Obtiene todos los proveedores de una comunidad específica.
     * @param int $id_comunidad
     * @return array
     */
    public function getProveedoresPorComunidad($id_comunidad)
    {
        $sql = "SELECT * FROM proveedores WHERE id_comunidad = :id_comunidad ORDER BY categoria, nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_comunidad' => $id_comunidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un proveedor específico por su ID, asegurando que pertenezca a la comunidad.
     * @param int $id_proveedor
     * @param int $id_comunidad
     * @return mixed
     */
    public function getProveedorById($id_proveedor, $id_comunidad)
    {
        $sql = "SELECT * FROM proveedores WHERE id_proveedor = :id_proveedor AND id_comunidad = :id_comunidad";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_proveedor' => $id_proveedor, 'id_comunidad' => $id_comunidad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo proveedor en la base de datos.
     * @param array $datos
     * @return bool
     */
    public function crearProveedor($datos)
    {
        $sql = "INSERT INTO proveedores (id_comunidad, nombre, categoria, telefono, email, horario, descripcion) 
                VALUES (:id_comunidad, :nombre, :categoria, :telefono, :email, :horario, :descripcion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_comunidad' => $datos['id_comunidad'],
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'telefono' => $datos['telefono'],
            'email' => $datos['email'],
            'horario' => $datos['horario'],
            'descripcion' => $datos['descripcion']
        ]);
    }

    /**
     * Actualiza los datos de un proveedor existente.
     * @param array $datos
     * @return bool
     */
    public function actualizarProveedor($datos)
    {
        $sql = "UPDATE proveedores SET 
                    nombre = :nombre, 
                    categoria = :categoria, 
                    telefono = :telefono, 
                    email = :email, 
                    horario = :horario, 
                    descripcion = :descripcion 
                WHERE id_proveedor = :id_proveedor AND id_comunidad = :id_comunidad";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nombre' => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'telefono' => $datos['telefono'],
            'email' => $datos['email'],
            'horario' => $datos['horario'],
            'descripcion' => $datos['descripcion'],
            'id_proveedor' => $datos['id_proveedor'],
            'id_comunidad' => $datos['id_comunidad']
        ]);
    }

    /**
     * Elimina un proveedor de la base de datos.
     * @param int $id_proveedor
     * @param int $id_comunidad
     * @return bool
     */
    public function eliminarProveedor($id_proveedor, $id_comunidad)
    {
        $sql = "DELETE FROM proveedores WHERE id_proveedor = :id_proveedor AND id_comunidad = :id_comunidad";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_proveedor' => $id_proveedor, 'id_comunidad' => $id_comunidad]);
    }
}