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
            $sql = "SELECT u.*, v.nombre as nombre_vivienda, c.nombre as nombre_comunidad, c.id_comunidad,
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

    // ------------------------------------------- REFRESCAR SESIÓN DEL USUARIO

    public function refrescarSesion($id_usuario)
    {
        try {
            $sql = "SELECT u.*, v.nombre as nombre_vivienda, c.nombre as nombre_comunidad, 
                    c.id_comunidad, d.calle, d.numero
                    FROM usuario u
                    JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                    JOIN comunidad c ON v.id_comunidad = c.id_comunidad
                    JOIN direccion d ON c.id_direccion = d.id_direccion
                    WHERE u.id_usuario = :id_usuario LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_usuario' => $id_usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                unset($usuario['password']); // Limpiamos la contraseña por seguridad
                return $usuario;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }


    // ------------------------------------------- COMPROBAR SI EL MAIL EXISTE

    public function emailExiste($email)
    {
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

     // ------------------------------------------- OBTENER VECINOS POR COMUNIDAD
    /**
     * Recupera la lista de usuarios asociados a una comunidad específica.
     * Realiza un JOIN con la tabla vivienda para mostrar la ubicación de cada vecino.
     */
    public function getUsuariosByComunidad($id_comunidad)
    {
        try {
            // Usamos LEFT JOIN para que aparezcan las viviendas que aún no tienen un usuario registrado
            $sql = "SELECT u.nombre, u.apellidos, u.dni, u.email, u.rol, v.nombre as nombre_vivienda, u.id_usuario, v.id_vivienda 
                    FROM vivienda v
                    LEFT JOIN usuario u ON v.id_vivienda = u.id_vivienda
                    WHERE v.id_comunidad = :id_comunidad
                    ORDER BY v.nombre ASC, u.apellidos ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // ------------------------------------------- OBTENER DETALLES POR CÓDIGO
    public function getDetallesCodigo($codigo)
    {
        try {
            $sql = "SELECT v.nombre as nombre_vivienda, c.nombre as nombre_comunidad 
                    FROM codigo_validacion cv
                    JOIN vivienda v ON cv.id_vivienda = v.id_vivienda
                    JOIN comunidad c ON v.id_comunidad = c.id_comunidad
                    WHERE cv.codigo = :codigo AND cv.usado = 0 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['codigo' => trim($codigo)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene el hash de la contraseña de un usuario por su ID.
     */
    public function getPasswordHash($id_usuario)
    {
        $sql = "SELECT password FROM usuario WHERE id_usuario = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['password'] : null;
    }

    /**
     * Actualiza la contraseña de un usuario con un nuevo hash.
     */
    public function actualizarPassword($id_usuario, $nueva_password)
    {
        try {
            $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
            $sql = "UPDATE usuario SET password = :hash WHERE id_usuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['hash' => $hash, 'id' => $id_usuario]);
        } catch (PDOException $e) {
            error_log("Error al actualizar password: " . $e->getMessage());
            return false;
        }
    }

    // Generar un token único y guardarlo con 1 hora de validez
    public function generarTokenRecuperacion($email) {
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email = :email");
        $stmt->execute(['email' => trim($email)]);
        if(!$stmt->fetch()) return false; // El usuario no existe

        // Generar token seguro y fecha de expiración
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE usuario SET token_recuperacion = :token, expiracion_token = :expiracion WHERE email = :email";
        $this->db->prepare($sql)->execute([
            'token' => $token,
            'expiracion' => $expiracion,
            'email' => trim($email)
        ]);

        return $token;
    }

    // Comprobar si el token que viene por la URL es válido y no ha caducado
    public function validarTokenRecuperacion($token) {
        $sql = "SELECT id_usuario FROM usuario WHERE token_recuperacion = :token AND expiracion_token > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar la contraseña y limpiar el token para que no se pueda reusar
    public function cambiarPasswordConToken($id_usuario, $nuevaPassword) {
        $hash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE usuario SET password = :hash, token_recuperacion = NULL, expiracion_token = NULL WHERE id_usuario = :id";
        return $this->db->prepare($sql)->execute([
            'hash' => $hash,
            'id' => $id_usuario
        ]);
    }
}
