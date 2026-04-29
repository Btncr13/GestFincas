<?php
require_once "config/BaseModel.php";

class FinanzasModel extends BaseModel
{
    public $ultimoError = null;

    // 🟢 OBTENER EL ESTADO DE CUOTAS DE TODOS LOS VECINOS (Vista Presidente)
    public function getEstadoCuotasComunidad($id_comunidad)
    {
        try {
            // Usamos un LEFT JOIN para traer todas las viviendas, incluso si no tienen usuario o cuotas aún
            $sql = "SELECT 
                        v.id_vivienda, 
                        v.nombre as vivienda, 
                        COALESCE(CONCAT(u.nombre, ' ', u.apellidos), 'Sin asignar') as vecino,
                        SUM(CASE WHEN c.estado = 'pendiente' THEN c.importe ELSE 0 END) as deuda
                    FROM vivienda v
                    LEFT JOIN usuario u ON v.id_vivienda = u.id_vivienda
                    LEFT JOIN cuota c ON v.id_vivienda = c.id_vivienda
                    WHERE v.id_comunidad = :id_comunidad
                    GROUP BY v.id_vivienda
                    ORDER BY v.nombre ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formateamos el estado siguiendo la lógica actual de la vista
            foreach ($resultados as &$res) {
                $res['deuda'] = (float)$res['deuda'];

                if ($res['deuda'] == 0) {
                    $res['estado'] = 'Al corriente';
                } else {
                    if ($res['deuda'] > 0 && $res['deuda'] <= 200) {
                        $res['estado'] = 'Pendiente';
                    } else {
                        $res['estado'] = 'Moroso';
                    }
                }

                // Buscar TODO el historial de recibos para el modal de edición
                $sqlH = "SELECT id_cuota, concepto, importe, tipo, fecha_emision, estado FROM cuota WHERE id_vivienda = :id_v ORDER BY fecha_emision DESC";
                $stmtH = $this->db->prepare($sqlH);
                $stmtH->execute(['id_v' => $res['id_vivienda']]);
                $res['historial'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($res); // Romper referencia

            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en getEstadoCuotasComunidad: " . $e->getMessage());
            return [];
        }
    }

    // 🟢 OBTENER LOS ÚLTIMOS GASTOS REGISTRADOS
    public function getUltimosGastos($id_comunidad, $limit = 5, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM gasto 
                    WHERE id_comunidad = :id_comunidad 
                    ORDER BY fecha DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getUltimosGastos: " . $e->getMessage());
            return [];
        }
    }

    // 🟢 OBTENER TOTAL DE GASTOS PARA PAGINACIÓN
    public function getTotalGastosComunidad($id_comunidad)
    {
        try {
            $sql = "SELECT COUNT(*) FROM gasto WHERE id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_comunidad' => $id_comunidad]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 🟢 OBTENER RESUMEN FINANCIERO MENSUAL (Ingresos vs Gastos)
    public function getResumenMensual($id_comunidad, $mes, $anio)
    {
        try {
            // Ingresos: Suma de las cuotas y derramas de este mes
            $sqlIngresos = "SELECT COALESCE(SUM(c.importe), 0) as total FROM cuota c
                            JOIN vivienda v ON c.id_vivienda = v.id_vivienda
                            WHERE v.id_comunidad = :id_comunidad AND c.estado = 'pagada'
                            AND MONTH(c.fecha_emision) = :mes AND YEAR(c.fecha_emision) = :anio";
            $stmtI = $this->db->prepare($sqlIngresos);
            $stmtI->execute(['id_comunidad' => $id_comunidad, 'mes' => $mes, 'anio' => $anio]);
            $ingresos = $stmtI->fetchColumn();

            // Gastos: Suma de los gastos de este mes
            $sqlGastos = "SELECT COALESCE(SUM(importe), 0) as total FROM gasto 
                          WHERE id_comunidad = :id_comunidad 
                          AND MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
            $stmtG = $this->db->prepare($sqlGastos);
            $stmtG->execute(['id_comunidad' => $id_comunidad, 'mes' => $mes, 'anio' => $anio]);
            $gastos = $stmtG->fetchColumn();

            return ['ingresos' => $ingresos, 'gastos' => $gastos];
        } catch (PDOException $e) {
            return ['ingresos' => 0, 'gastos' => 0];
        }
    }

    // 🟢 REGISTRAR UN NUEVO GASTO
    public function crearGasto($id_comunidad, $concepto, $categoria, $fecha, $importe)
    {
        try {
            $sql = "INSERT INTO gasto (id_comunidad, concepto, categoria, fecha, importe, estado) 
                    VALUES (:id_comunidad, :concepto, :categoria, :fecha, :importe, 'aprobado')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id_comunidad' => $id_comunidad,
                'concepto' => $concepto,
                'categoria' => $categoria,
                'fecha' => $fecha,
                'importe' => $importe
            ]);
        } catch (PDOException $e) {
            $this->ultimoError = $e->getMessage();
            error_log("Error en crearGasto: " . $e->getMessage());
            return false;
        }
    }

    // 🟢 EMITIR NUEVA CUOTA/DERRAMA PARA TODOS LOS VECINOS
    public function emitirCuotas($id_comunidad, $tipo, $concepto, $importe, $fecha_emision)
    {
        try {
            $this->db->beginTransaction();

            // 1. Obtener todas las viviendas de la comunidad
            $sqlViv = "SELECT id_vivienda FROM vivienda WHERE id_comunidad = :id_comunidad";
            $stmtViv = $this->db->prepare($sqlViv);
            $stmtViv->execute(['id_comunidad' => $id_comunidad]);
            $viviendas = $stmtViv->fetchAll(PDO::FETCH_ASSOC);

            // 2. Insertar la cuota para cada vivienda
            $sql = "INSERT INTO cuota (id_vivienda, tipo, concepto, importe, fecha_emision, estado) 
                    VALUES (:id_vivienda, :tipo, :concepto, :importe, :fecha_emision, 'pendiente')";
            $stmt = $this->db->prepare($sql);

            foreach ($viviendas as $viv) {
                $stmt->execute([
                    'id_vivienda' => $viv['id_vivienda'],
                    'tipo' => $tipo,
                    'concepto' => $concepto,
                    'importe' => $importe,
                    'fecha_emision' => $fecha_emision
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->ultimoError = $e->getMessage();
            error_log("Error en emitirCuotas: " . $e->getMessage());
            return false;
        }
    }

    // 🟢 OBTENER TODAS LAS CUOTAS DE UN VECINO ESPECÍFICO (Vista Vecino)
    public function getCuotasVecino($id_vivienda, $limit = 10, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM cuota WHERE id_vivienda = :id_vivienda ORDER BY fecha_emision DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_vivienda' => $id_vivienda]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getCuotasVecino: " . $e->getMessage());
            return [];
        }
    }

    // 🟢 OBTENER TOTAL DE CUOTAS PARA PAGINACIÓN
    public function getTotalCuotasVecino($id_vivienda)
    {
        try {
            $sql = "SELECT COUNT(*) FROM cuota WHERE id_vivienda = :id_vivienda";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_vivienda' => $id_vivienda]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 🟢 OBTENER DEUDA TOTAL DE UN VECINO (Vista Vecino)
    public function getDeudaVecino($id_vivienda)
    {
        try {
            $sql = "SELECT COALESCE(SUM(importe), 0) FROM cuota WHERE id_vivienda = :id_vivienda AND estado = 'pendiente'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_vivienda' => $id_vivienda]);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0.00;
        }
    }

    // 🟢 MARCAR UNA CUOTA ESPECÍFICA COMO PAGADA
    public function saldarCuota($id_cuota, $id_comunidad)
    {
        try {
            // Usamos JOIN para asegurar que la vivienda pertenezca a esta comunidad
            $sql = "UPDATE cuota c
                    JOIN vivienda v ON c.id_vivienda = v.id_vivienda
                    SET c.estado = 'pagada'
                    WHERE c.id_cuota = :id_cuota 
                      AND v.id_comunidad = :id_comunidad 
                      AND c.estado = 'pendiente'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_cuota' => $id_cuota,
                'id_comunidad' => $id_comunidad
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->ultimoError = $e->getMessage();
            return false;
        }
    }

    // 🟢 MODIFICAR DATOS O ESTADO DE UNA CUOTA ESPECÍFICA
    public function modificarCuota($id_cuota, $concepto, $importe, $estado, $id_comunidad)
    {
        try {
            $sql = "UPDATE cuota c
                    JOIN vivienda v ON c.id_vivienda = v.id_vivienda
                    SET c.concepto = :concepto, c.importe = :importe, c.estado = :estado
                    WHERE c.id_cuota = :id_cuota AND v.id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'concepto' => $concepto,
                'importe' => $importe,
                'estado' => $estado,
                'id_cuota' => $id_cuota,
                'id_comunidad' => $id_comunidad
            ]);
        } catch (PDOException $e) {
            $this->ultimoError = $e->getMessage();
            return false;
        }
    }

    // 🟢 ELIMINAR UNA CUOTA (ÚTIL POR SI EL PRESIDENTE SE EQUIVOCA)
    public function eliminarCuota($id_cuota, $id_comunidad)
    {
        try {
            $sql = "DELETE c FROM cuota c JOIN vivienda v ON c.id_vivienda = v.id_vivienda WHERE c.id_cuota = :id_cuota AND v.id_comunidad = :id_comunidad";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id_cuota' => $id_cuota, 'id_comunidad' => $id_comunidad]);
        } catch (PDOException $e) {
            $this->ultimoError = $e->getMessage();
            return false;
        }
    }

    // 🟢 OBTENER EL BALANCE ANUAL (Ingresos vs Gastos del año en curso)
    public function getResumenAnual($id_comunidad, $anio)
    {
        try {
            $sqlG = "SELECT COALESCE(SUM(importe), 0) FROM gasto WHERE id_comunidad = :id_c AND YEAR(fecha) = :anio";
            $stmtG = $this->db->prepare($sqlG);
            $stmtG->execute(['id_c' => $id_comunidad, 'anio' => $anio]);
            $gastos = (float)$stmtG->fetchColumn();

            $sqlI = "SELECT COALESCE(SUM(c.importe), 0) FROM cuota c JOIN vivienda v ON c.id_vivienda = v.id_vivienda WHERE v.id_comunidad = :id_c AND c.estado = 'pagada' AND YEAR(c.fecha_emision) = :anio";
            $stmtI = $this->db->prepare($sqlI);
            $stmtI->execute(['id_c' => $id_comunidad, 'anio' => $anio]);
            $ingresos = (float)$stmtI->fetchColumn();

            return ['ingresos' => $ingresos, 'gastos' => $gastos, 'balance' => $ingresos - $gastos];
        } catch (PDOException $e) {
            return ['ingresos' => 0, 'gastos' => 0, 'balance' => 0];
        }
    }

    // 🟢 OBTENER LA EVOLUCIÓN MENSUAL DEL AÑO (Para gráfica de barras)
    public function getEvolucionAnual($id_comunidad, $anio)
    {
        $evolucion = [];
        for ($i = 1; $i <= 12; $i++) {
            $evolucion[$i] = ['ingresos' => 0, 'gastos' => 0];
        }

        try {
            $sqlI = "SELECT MONTH(c.fecha_emision) as mes, SUM(c.importe) as total 
                     FROM cuota c JOIN vivienda v ON c.id_vivienda = v.id_vivienda 
                     WHERE v.id_comunidad = :id_c AND c.estado = 'pagada' AND YEAR(c.fecha_emision) = :anio
                     GROUP BY MONTH(c.fecha_emision)";
            $stmtI = $this->db->prepare($sqlI);
            $stmtI->execute(['id_c' => $id_comunidad, 'anio' => $anio]);
            while ($row = $stmtI->fetch(PDO::FETCH_ASSOC)) {
                $evolucion[$row['mes']]['ingresos'] = (float)$row['total'];
            }

            $sqlG = "SELECT MONTH(fecha) as mes, SUM(importe) as total 
                     FROM gasto WHERE id_comunidad = :id_c AND YEAR(fecha) = :anio
                     GROUP BY MONTH(fecha)";
            $stmtG = $this->db->prepare($sqlG);
            $stmtG->execute(['id_c' => $id_comunidad, 'anio' => $anio]);
            while ($row = $stmtG->fetch(PDO::FETCH_ASSOC)) {
                $evolucion[$row['mes']]['gastos'] = (float)$row['total'];
            }

            return array_values($evolucion);
        } catch (PDOException $e) {
            return array_values($evolucion);
        }
    }

    // 🟢 FUNCIÓN PRIVADA PARA CONSTRUIR FILTROS DINÁMICOS
    private function construirFiltrosHistorico($id_comunidad, $filtros)
    {
        $whereGasto = "id_comunidad = :id_c1";
        $whereIngreso = "v.id_comunidad = :id_c2";
        $params = ['id_c1' => $id_comunidad, 'id_c2' => $id_comunidad];

        if (!empty($filtros['anio'])) {
            $whereGasto .= " AND YEAR(fecha) = :anio1";
            $whereIngreso .= " AND YEAR(c.fecha_emision) = :anio2";
            $params['anio1'] = $filtros['anio'];
            $params['anio2'] = $filtros['anio'];
        }
        if (!empty($filtros['mes'])) {
            $whereGasto .= " AND MONTH(fecha) = :mes1";
            $whereIngreso .= " AND MONTH(c.fecha_emision) = :mes2";
            $params['mes1'] = $filtros['mes'];
            $params['mes2'] = $filtros['mes'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $whereGasto .= " AND fecha >= :fini1";
            $whereIngreso .= " AND c.fecha_emision >= :fini2";
            $params['fini1'] = $filtros['fecha_inicio'];
            $params['fini2'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $whereGasto .= " AND fecha <= :ffin1";
            $whereIngreso .= " AND c.fecha_emision <= :ffin2";
            $params['ffin1'] = $filtros['fecha_fin'];
            $params['ffin2'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['categoria'])) {
            $whereGasto .= " AND categoria = :cat1";
            $whereIngreso .= " AND c.tipo = :cat2";
            $params['cat1'] = $filtros['categoria'];
            $params['cat2'] = $filtros['categoria'];
        }
        if (!empty($filtros['concepto'])) {
            $whereGasto .= " AND concepto LIKE :conc1";
            $whereIngreso .= " AND c.concepto LIKE :conc2";
            $params['conc1'] = "%" . $filtros['concepto'] . "%";
            $params['conc2'] = "%" . $filtros['concepto'] . "%";
        }

        return [$whereGasto, $whereIngreso, $params];
    }

    // 🟢 OBTENER EL HISTÓRICO COMBINADO CON FILTROS Y PAGINACIÓN
    public function getHistoricoMovimientos($id_comunidad, $limit = 10, $offset = 0, $filtros = [])
    {
        try {
            list($whereGasto, $whereIngreso, $params) = $this->construirFiltrosHistorico($id_comunidad, $filtros);

            $sql = "SELECT 'Gasto' as tipo_movimiento, concepto, NULL as vivienda, categoria, fecha, importe, estado 
                    FROM gasto WHERE $whereGasto
                    UNION ALL
                    SELECT 'Ingreso' as tipo_movimiento, c.concepto, v.nombre as vivienda, c.tipo as categoria, c.fecha_emision as fecha, c.importe, c.estado 
                    FROM cuota c 
                    JOIN vivienda v ON c.id_vivienda = v.id_vivienda 
                    WHERE $whereIngreso
                    ORDER BY fecha DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getHistoricoMovimientos: " . $e->getMessage());
            return [];
        }
    }

    // 🟢 OBTENER EL TOTAL DE REGISTROS PARA LA PAGINACIÓN
    public function getTotalHistoricoMovimientos($id_comunidad, $filtros = [])
    {
        try {
            list($whereGasto, $whereIngreso, $params) = $this->construirFiltrosHistorico($id_comunidad, $filtros);

            $sql = "SELECT SUM(conteo) as total FROM (
                        SELECT COUNT(*) as conteo FROM gasto WHERE $whereGasto
                        UNION ALL
                        SELECT COUNT(*) as conteo FROM cuota c JOIN vivienda v ON c.id_vivienda = v.id_vivienda WHERE $whereIngreso
                    ) as t";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 🟢 OBTENER EL SALDO TOTAL HISTÓRICO DE LA COMUNIDAD
    public function getSaldoTotalComunidad($id_comunidad)
    {
        try {
            $sqlI = "SELECT COALESCE(SUM(c.importe), 0) FROM cuota c JOIN vivienda v ON c.id_vivienda = v.id_vivienda WHERE v.id_comunidad = :id_c AND c.estado = 'pagada'";
            $stmtI = $this->db->prepare($sqlI);
            $stmtI->execute(['id_c' => $id_comunidad]);
            $ingresos = (float)$stmtI->fetchColumn();

            $sqlG = "SELECT COALESCE(SUM(importe), 0) FROM gasto WHERE id_comunidad = :id_c";
            $stmtG = $this->db->prepare($sqlG);
            $stmtG->execute(['id_c' => $id_comunidad]);
            $gastos = (float)$stmtG->fetchColumn();

            return $ingresos - $gastos;
        } catch (PDOException $e) {
            return 0.00;
        }
    }
}
