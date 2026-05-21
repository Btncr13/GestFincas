<?php
require_once __DIR__ . '/../../config/BaseModel.php';

class SuperAdminModel extends BaseModel
{
    public function login($email, $password)
    {
        try {
            $sql = "SELECT id_superadmin, nombre, email, password FROM superadmin WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => trim($email)]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                return ['success' => false, 'message' => 'Credenciales de administrador incorrectas.'];
            }

            if (!password_verify($password, $admin['password'])) {
                return ['success' => false, 'message' => 'Credenciales de administrador incorrectas.'];
            }

            unset($admin['password']); // Limpiamos la contraseña por seguridad
            return ['success' => true, 'data' => $admin];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en la base de datos al intentar acceder.'];
        }
    }

    // Obtener la lista de todas las comunidades y quién es su presidente actual
    public function getComunidades()
    {
        try {
            $sql = "SELECT c.id_comunidad, c.nombre as nombre_comunidad, d.calle, d.numero, u.id_usuario as presi_id,
                           u.nombre as presi_nombre, u.apellidos as presi_apellidos, u.email as presi_email
                    FROM comunidad c
                    JOIN direccion d ON c.id_direccion = d.id_direccion
                    LEFT JOIN vivienda v ON c.id_comunidad = v.id_comunidad
                    LEFT JOIN usuario u ON v.id_vivienda = u.id_vivienda AND u.rol = 'presidente'
                    GROUP BY c.id_comunidad";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Extrae los datos exactos que necesita la app para generar una sesión de usuario válida
    public function getUsuarioParaImpersonar($id_usuario)
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

    // Crea todo en cascada: Dirección -> Comunidad -> Vivienda -> Usuario(Presidente)
    public function crearComunidadYPresidente($datosComunidad, $datosDireccion, $datosPresidente)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar Dirección
            $stmtDir = $this->db->prepare("INSERT INTO direccion (calle, numero) VALUES (:calle, :numero)");
            $stmtDir->execute(['calle' => $datosDireccion['calle'], 'numero' => $datosDireccion['numero']]);
            $idDireccion = $this->db->lastInsertId();

            // 2. Insertar Comunidad
            $stmtCom = $this->db->prepare("INSERT INTO comunidad (nombre, id_direccion) VALUES (:nombre, :id_direccion)");
            $stmtCom->execute(['nombre' => $datosComunidad['nombre'], 'id_direccion' => $idDireccion]);
            $idComunidad = $this->db->lastInsertId();

            // 3. Insertar Vivienda del Presidente
            $stmtViv = $this->db->prepare("INSERT INTO vivienda (id_comunidad, nombre) VALUES (:id_comunidad, :nombre)");
            $stmtViv->execute(['id_comunidad' => $idComunidad, 'nombre' => $datosPresidente['vivienda']]);
            $idVivienda = $this->db->lastInsertId();

            // 4. Insertar Usuario Presidente
            $stmtUsu = $this->db->prepare("INSERT INTO usuario (id_vivienda, nombre, apellidos, dni, email, password, fecha_registro, es_propietario, rol) 
                                           VALUES (:id_vivienda, :nombre, :apellidos, :dni, :email, :password, NOW(), 1, 'presidente')");
            $stmtUsu->execute([
                'id_vivienda' => $idVivienda, 'nombre' => $datosPresidente['nombre'], 'apellidos' => $datosPresidente['apellidos'],
                'dni' => $datosPresidente['dni'], 'email' => $datosPresidente['email'], 'password' => password_hash($datosPresidente['password'], PASSWORD_BCRYPT)
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Comunidad y presidente registrados correctamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() == 23000) return ['success' => false, 'message' => 'El email o DNI del presidente ya están en uso.'];
            return ['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()];
        }
    }

    // Eliminar una comunidad
    public function eliminarComunidad($id_comunidad)
    {
        try {
            // Al borrar la comunidad, si la BD tiene ON DELETE CASCADE, se borrará todo lo asociado.
            $sql = "DELETE FROM comunidad WHERE id_comunidad = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_comunidad]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return false; // Error de restricción: Hay datos que dependen de esta comunidad
            }
            return false;
        }
    }

    // ==========================================
    // GESTIÓN DE AVISOS GLOBALES
    // ==========================================

    public function getAvisos()
    {
        try {
            $sql = "SELECT * FROM avisos_plataforma ORDER BY fecha_creacion DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crearAviso($titulo, $mensaje, $fecha_inicio, $fecha_fin)
    {
        try {
            $sql = "INSERT INTO avisos_plataforma (titulo, mensaje, fecha_inicio, fecha_fin, activo) VALUES (?, ?, ?, ?, 1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([trim($titulo), trim($mensaje), $fecha_inicio, $fecha_fin]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarAviso($id_aviso)
    {
        $sql = "DELETE FROM avisos_plataforma WHERE id_aviso = ?";
        return $this->db->prepare($sql)->execute([$id_aviso]);
    }

    public function getAvisoActivoGlobal()
    {
        // Busca un aviso que esté activo Y donde el momento actual (NOW) esté entre la fecha de inicio y la fecha de fin
        $sql = "SELECT titulo, mensaje FROM avisos_plataforma WHERE activo = 1 AND NOW() BETWEEN fecha_inicio AND fecha_fin ORDER BY id_aviso DESC LIMIT 1";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
}