<?php

require_once "config/BaseModel.php";

class UsuarioModel extends BaseModel
{
    
    // ------------------------------------------ VALIDA SI UN CODIGO EXISTE Y NO HA SIDO USADO
    public function validarCodigo($codigo)
    {
        $sql = "SELECT id_codigo, id_vivienda FROM codigo_validacion 
                WHERE codigo = :codigo AND usado = 0 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['codigo' => trim($codigo)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //  ------------------------------------------ REGISTRA NUEVO USUARIO Y MARCA CODIGO COMO USADO

    public function registrar($datos, $id_codigo)
    {
        try {
            $this->db->beginTransaction();

            // 1. Consulta para insertar el usuario
            $sqlUser = "INSERT INTO usuario (id_vivienda, nombre, apellidos, dni, email, password, fecha_registro, es_propietario, rol) 
                        VALUES (:id_vivienda, :nombre, :apellidos, :dni, :email, :password, NOW(), :es_propietario, :rol)";

            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([
                'id_vivienda'    => $datos['id_vivienda'],
                'nombre'         => trim($datos['nombre']),
                'apellidos'      => trim($datos['apellidos']),
                'dni'            => trim($datos['dni']),
                'email'          => trim($datos['email']),
                'password'       => password_hash($datos['password'], PASSWORD_BCRYPT),
                'es_propietario' => $datos['es_propietario'] ?? 1,
                'rol'            => $datos['rol'] ?? 'vecino'
            ]);

            // 2. Consulta para marcar código como usado
            $sqlUpdate = "UPDATE codigo_validacion SET usado = 1 WHERE id_codigo = :id_codigo";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute(['id_codigo' => $id_codigo]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Registro completado con éxito.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() == 23000) { // Error de duplicado (email/dni)
                return ['success' => false, 'message' => 'El email o DNI ya están registrados.'];
            }
            return ['success' => false, 'message' => 'Error en el registro: ' . $e->getMessage()];
        }
    }

//  -------------------------------------------------- LOGIN DE USUARIO POR MAIL

    public function login($nombreVivienda, $email, $password)
    {
        try {
            // Añadimos el JOIN con direccion para sacar la calle y el número
            $sql = "SELECT u.*, c.id_comunidad, v.nombre as nombre_vivienda, c.nombre as nombre_comunidad, 
                           d.calle, d.numero
                    FROM usuario u
                    JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                    JOIN comunidad c ON v.id_comunidad = c.id_comunidad
                    JOIN direccion d ON c.id_direccion = d.id_direccion
                    WHERE u.email = :email AND v.nombre = :nombre_vivienda LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => trim($email),
                'nombre_vivienda' => trim($nombreVivienda)
            ]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['success' => false, 'message' => 'Credenciales incorrectas.'];
            }

            if (!password_verify($password, $usuario['password'])) {
                return ['success' => false, 'message' => 'Credenciales incorrectas.'];
            }

            // Limpiar datos sensibles antes de devolver
            unset($usuario['password']);

            return [
                'success' => true,
                'message' => 'Login correcto.',
                'data' => $usuario
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en el servidor.'];
        }
    }


    // ------------------------------------------- COMPROBAR SI EL MAIL EXISTE

    public function emailExiste($email)
    {
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
}
?>