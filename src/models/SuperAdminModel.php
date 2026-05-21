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
            $sql = "SELECT c.id_comunidad, c.nombre as nombre_comunidad, d.calle, d.numero, 
                           presi.id_usuario as presi_id, presi.nombre as presi_nombre, 
                           presi.apellidos as presi_apellidos, presi.email as presi_email
                    FROM comunidad c
                    JOIN direccion d ON c.id_direccion = d.id_direccion
                    LEFT JOIN (
                        SELECT u.id_usuario, u.nombre, u.apellidos, u.email, v.id_comunidad
                        FROM usuario u
                        JOIN vivienda v ON u.id_vivienda = v.id_vivienda
                        WHERE u.rol = 'presidente'
                    ) presi ON c.id_comunidad = presi.id_comunidad
                    ORDER BY c.id_comunidad DESC";
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

            // 1. Insertar Dirección con todos los campos obligatorios para no fallar en Strict Mode
            $stmtDir = $this->db->prepare("INSERT INTO direccion (tipo, calle, numero, ciudad, provincia, codigo_postal, pais) VALUES ('comunidad', :calle, :numero, 'Sin especificar', 'Sin especificar', 0, 'Sin especificar')");
            if (!$stmtDir->execute(['calle' => $datosDireccion['calle'], 'numero' => $datosDireccion['numero']])) throw new Exception("Fallo al insertar dirección.");
            $idDireccion = $this->db->lastInsertId();
            
            if (empty($idDireccion)) throw new Exception("Error crítico: Tu base de datos no tiene AUTO_INCREMENT en la tabla 'direccion'.");

            // 2. Insertar Comunidad asegurando fecha_creacion
            $stmtCom = $this->db->prepare("INSERT INTO comunidad (nombre, id_direccion, fecha_creacion) VALUES (:nombre, :id_direccion, NOW())");
            if (!$stmtCom->execute(['nombre' => $datosComunidad['nombre'], 'id_direccion' => $idDireccion])) throw new Exception("Fallo al insertar comunidad.");
            $idComunidad = $this->db->lastInsertId();
            
            if (empty($idComunidad)) throw new Exception("Error crítico: Tu base de datos no tiene AUTO_INCREMENT en la tabla 'comunidad'.");

            // 3. Insertar Vivienda del Presidente
            $stmtViv = $this->db->prepare("INSERT INTO vivienda (id_comunidad, nombre) VALUES (:id_comunidad, :nombre)");
            if (!$stmtViv->execute(['id_comunidad' => $idComunidad, 'nombre' => $datosPresidente['vivienda']])) throw new Exception("Fallo al insertar vivienda.");
            $idVivienda = $this->db->lastInsertId();
            
            if (empty($idVivienda)) throw new Exception("Error crítico: Tu base de datos no tiene AUTO_INCREMENT en la tabla 'vivienda'.");

            // 4. Insertar Usuario Presidente
            $stmtUsu = $this->db->prepare("INSERT INTO usuario (id_vivienda, nombre, apellidos, dni, email, password, fecha_registro, es_propietario, rol) 
                                           VALUES (:id_vivienda, :nombre, :apellidos, :dni, :email, :password, NOW(), 1, 'presidente')");
            $exitoUsuario = $stmtUsu->execute([
                'id_vivienda' => $idVivienda, 'nombre' => $datosPresidente['nombre'], 'apellidos' => $datosPresidente['apellidos'],
                'dni' => $datosPresidente['dni'], 'email' => $datosPresidente['email'], 'password' => password_hash($datosPresidente['password'], PASSWORD_BCRYPT)
            ]);
            
            if (!$exitoUsuario) {
                throw new Exception("El email o DNI introducidos para el presidente ya están en uso.");
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Comunidad y presidente registrados correctamente.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()];
        }
    }

    // Eliminar una comunidad
    public function eliminarComunidad($id_comunidad)
    {
        try {
            $this->db->beginTransaction();

            // 1. Borramos los usuarios y códigos asociados a las viviendas de esta comunidad
            $this->db->prepare("DELETE FROM usuario WHERE id_vivienda IN (SELECT id_vivienda FROM vivienda WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM codigo_validacion WHERE id_vivienda IN (SELECT id_vivienda FROM vivienda WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            
            // 2. Limpiamos foros, reuniones, gastos, espacios y votaciones de esta comunidad
            $this->db->prepare("DELETE FROM foro_mensaje WHERE id_tema IN (SELECT id_tema FROM foro_tema WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM foro_tema WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM gasto WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM comunicado_lectura WHERE id_comunicado IN (SELECT id_comunicado FROM comunicados WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM comunicados WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM espacios_normas WHERE id_espacios_comunidad IN (SELECT id_espacios_comunidad FROM espacios_comunidad WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM reservas WHERE id_espacios_comunidad IN (SELECT id_espacios_comunidad FROM espacios_comunidad WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM espacios_comunidad WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM voto WHERE id_votacion IN (SELECT id_votacion FROM votacion WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM votacion_opcion WHERE id_votacion IN (SELECT id_votacion FROM votacion WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM votacion WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM asistencia_reunion WHERE id_reunion IN (SELECT id_reunion FROM reunion WHERE id_comunidad = ?)")->execute([$id_comunidad]);
            $this->db->prepare("DELETE FROM reunion WHERE id_comunidad = ?")->execute([$id_comunidad]);

            // 3. Finalmente borramos las viviendas y la comunidad
            $this->db->prepare("DELETE FROM vivienda WHERE id_comunidad = ?")->execute([$id_comunidad]);
            $sql = "DELETE FROM comunidad WHERE id_comunidad = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$id_comunidad]);
            
            $this->db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $this->db->rollBack();
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