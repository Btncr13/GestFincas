<?php
require_once "src/models/UsuarioModel.php";
require_once "src/models/FinanzasModel.php";

class FinanzasController
{
    private $usuarioModel;
    private $finanzasModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['vivienda'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->finanzasModel = new FinanzasModel($pdo);
    }

    private function getViewData()
    {
        if (isset($_SESSION['vivienda']['id_usuario'])) {
            $sesionFresca = $this->usuarioModel->refrescarSesion($_SESSION['vivienda']['id_usuario']);
            if ($sesionFresca) $_SESSION['vivienda'] = $sesionFresca;
        }
        $calle = $_SESSION['vivienda']['calle'] ?? 'Dirección desconocida';
        $numero = $_SESSION['vivienda']['numero'] ?? '';

        return [
            'nombreComunidad' => $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad',
            'nombreVivienda'  => $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda',
            'direccion'       => trim($calle . ' ' . $numero),
            'rolReal'         => $_SESSION['vivienda']['rol'] ?? 'vecino',
            'rol'             => $_SESSION['modo_vista'] ?? ($_SESSION['vivienda']['rol'] ?? 'vecino')
        ];
    }

    public function index()
    {
        extract($this->getViewData());

        if ($rol === 'presidente') {
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            // 1. Obtener últimos gastos
            $ultimosGastos = $this->finanzasModel->getUltimosGastos($id_comunidad);

            // 2. Obtener estado de vecinos
            $cuotasVecinos = $this->finanzasModel->getEstadoCuotasComunidad($id_comunidad);
            $vecinosConDeuda = array_filter($cuotasVecinos, function ($c) {
                return $c['deuda'] > 0;
            });

            // 3. Datos del mes actual para la gráfica
            $mesActual = date('m');
            $anioActual = date('Y');
            $resumen = $this->finanzasModel->getResumenMensual($id_comunidad, $mesActual, $anioActual);

            // 4. Datos para las pestañas Anual e Histórico
            $resumenAnual = $this->finanzasModel->getResumenAnual($id_comunidad, $anioActual);
            $evolucionAnual = $this->finanzasModel->getEvolucionAnual($id_comunidad, $anioActual);

            // === SISTEMA DE FILTRADO Y PAGINACIÓN DEL HISTÓRICO ===
            $filtrosHistorico = [
                'anio' => $_GET['anio'] ?? '',
                'mes' => $_GET['mes'] ?? '',
                'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
                'fecha_fin' => $_GET['fecha_fin'] ?? '',
                'categoria' => $_GET['categoria'] ?? '',
                'concepto' => trim($_GET['concepto'] ?? '')
            ];

            $paginaActual = max(1, (int)($_GET['page'] ?? 1));
            $limitePorPagina = 10;
            $totalHistorico = $this->finanzasModel->getTotalHistoricoMovimientos($id_comunidad, $filtrosHistorico);

            list($offset, $totalPaginas) = $this->calcularPaginacion($paginaActual, $limitePorPagina, $totalHistorico);
            $historico = $this->finanzasModel->getHistoricoMovimientos($id_comunidad, $limitePorPagina, $offset, $filtrosHistorico);

            $queryString = ""; // Para mantener los filtros al cambiar de página
            $hayFiltrosActivos = false;
            foreach ($filtrosHistorico as $k => $v) {
                if (trim((string)$v) !== '') {
                    $queryString .= "&{$k}=" . urlencode($v);
                    $hayFiltrosActivos = true;
                }
            }

            // Mantener pestaña activa al navegar
            $tabActiva = $_GET['tab'] ?? 'mensual';

            // Solo forzamos 'historico' si hay búsquedas activas o paginación en uso
            if ($hayFiltrosActivos || !empty($_GET['page'])) {
                $tabActiva = 'historico';
            }

            // 5. Saldo total histórico
            $saldoTotal = $this->finanzasModel->getSaldoTotalComunidad($id_comunidad);

            require "src/views/finanzas/presidente.php";
        } else {
            $id_vivienda = $_SESSION['vivienda']['id_vivienda'];
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            $tabActiva = $_GET['tab'] ?? 'mis-recibos';

            // 1. Datos personales del vecino (Pestaña Mis Recibos)
            $paginaRecibos = max(1, (int)($_GET['p_rec'] ?? 1));
            $limiteRecibos = 10;
            $totalCuotas = $this->finanzasModel->getTotalCuotasVecino($id_vivienda);

            list($offsetRecibos, $totalPaginasRecibos) = $this->calcularPaginacion($paginaRecibos, $limiteRecibos, $totalCuotas);
            $cuotas = $this->finanzasModel->getCuotasVecino($id_vivienda, $limiteRecibos, $offsetRecibos);

            $deudaTotal = $this->finanzasModel->getDeudaVecino($id_vivienda);

            // 2. Datos de transparencia de la comunidad (Pestaña Gastos Comunidad)
            $paginaGastos = max(1, (int)($_GET['p_gas'] ?? 1));
            $limiteGastos = 10;
            $totalGastos = $this->finanzasModel->getTotalGastosComunidad($id_comunidad);

            list($offsetGastos, $totalPaginasGastos) = $this->calcularPaginacion($paginaGastos, $limiteGastos, $totalGastos);
            $ultimosGastos = $this->finanzasModel->getUltimosGastos($id_comunidad, $limiteGastos, $offsetGastos);

            $anioActual = date('Y');
            $resumenAnual = $this->finanzasModel->getResumenAnual($id_comunidad, $anioActual);
            $evolucionAnual = $this->finanzasModel->getEvolucionAnual($id_comunidad, $anioActual);

            require "src/views/finanzas/vecino.php";
        }
    }

    // 🟢 PROCESAR NUEVO GASTO
    public function nuevoGasto()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
            $concepto = trim($_POST['concepto'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $fecha = $_POST['fecha'] ?? date('Y-m-d');

            $importe = $this->parseImporte($_POST['importe'] ?? '0');

            if (!empty($concepto) && !empty($categoria) && $importe != 0) {
                if ($this->finanzasModel->crearGasto($id_comunidad, $concepto, $categoria, $fecha, $importe)) {
                    $_SESSION['finanzas_exito'] = "Gasto registrado correctamente.";
                } else {
                    $_SESSION['finanzas_error'] = "Error BD (Gasto): " . $this->finanzasModel->ultimoError;
                }
            } else {
                $_SESSION['finanzas_error'] = "Por favor, revisa que no haya campos vacíos y el importe sea distinto de cero.";
            }
        }
        header("Location: index.php?route=finanzas/index");
        exit;
    }

    // 🟢 PROCESAR NUEVA CUOTA/DERRAMA
    public function nuevaCuota()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
            $tipo = $_POST['tipo'] ?? 'mensual';
            $concepto = trim($_POST['concepto'] ?? '');

            $importe = $this->parseImporte($_POST['importe'] ?? '0');
            $fecha_emision = $_POST['fecha_emision'] ?? date('Y-m-d');

            if (!empty($concepto) && $importe > 0 && in_array($tipo, ['mensual', 'derrama'])) {
                if ($this->finanzasModel->emitirCuotas($id_comunidad, $tipo, $concepto, $importe, $fecha_emision)) {
                    $_SESSION['finanzas_exito'] = "Cuotas emitidas exitosamente a todos los vecinos.";
                } else {
                    $_SESSION['finanzas_error'] = "Error BD (Cuotas): " . $this->finanzasModel->ultimoError;
                }
            } else {
                $_SESSION['finanzas_error'] = "Datos incorrectos. Verifica el concepto y que el importe sea válido.";
            }
        }
        header("Location: index.php?route=finanzas/index");
        exit;
    }

    // 🟢 PROCESAR PAGO DE UN RECIBO ESPECÍFICO
    public function saldarDeuda()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];
            $id_cuota = $_POST['id_cuota'] ?? null;

            if ($id_cuota && $this->finanzasModel->saldarCuota($id_cuota, $id_comunidad)) {
                $_SESSION['finanzas_exito'] = "El recibo ha sido marcado como pagado correctamente.";
            } else {
                $_SESSION['finanzas_error'] = "No se pudo procesar el pago. Inténtalo de nuevo.";
            }
        }
        header("Location: index.php?route=finanzas/index");
        exit;
    }

    // 🟢 PROCESAR MODIFICACIÓN/ELIMINACIÓN DE UN RECIBO
    public function modificarCuota()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['modo_vista'] ?? '') === 'presidente') {
            $id_comunidad = $_SESSION['vivienda']['id_comunidad'];

            // Si se pulsó el botón de la papelera para una cuota en concreto
            if (!empty($_POST['eliminar_cuota_id'])) {
                $id_cuota = $_POST['eliminar_cuota_id'];
                if ($this->finanzasModel->eliminarCuota($id_cuota, $id_comunidad)) {
                    $_SESSION['finanzas_exito'] = "Recibo eliminado correctamente del sistema.";
                } else {
                    $_SESSION['finanzas_error'] = "Error BD (Eliminar): " . $this->finanzasModel->ultimoError;
                }
            }
            // Si se pulsó "Guardar todos los cambios"
            elseif (!empty($_POST['cuotas']) && is_array($_POST['cuotas'])) {
                $errores = 0;
                foreach ($_POST['cuotas'] as $id_cuota => $datos) {
                    $concepto = trim($datos['concepto'] ?? '');
                    $importe = $this->parseImporte($datos['importe'] ?? '0');
                    $estado = $datos['estado'] ?? 'pendiente';

                    if (!empty($concepto) && $importe > 0 && in_array($estado, ['pagada', 'pendiente'])) {
                        if (!$this->finanzasModel->modificarCuota($id_cuota, $concepto, $importe, $estado, $id_comunidad)) {
                            $errores++;
                        }
                    } else {
                        $errores++;
                    }
                }

                if ($errores === 0) {
                    $_SESSION['finanzas_exito'] = "Todos los recibos actualizados correctamente.";
                } else {
                    $_SESSION['finanzas_error'] = "Algunos recibos no se guardaron (revisa conceptos vacíos o importes en cero).";
                }
            }
        }
        header("Location: index.php?route=finanzas/index");
        exit;
    }

    // =========================================================
    // 🟢 HELPERS PRIVADOS (Código DRY / Limpio)
    // =========================================================

    private function parseImporte($valor_string)
    {
        // Convierte comas en puntos y asegura que sea un float válido
        return floatval(str_replace(',', '.', trim($valor_string)));
    }

    private function calcularPaginacion($paginaActual, $limitePorPagina, $totalRegistros)
    {
        $offset = ($paginaActual - 1) * $limitePorPagina;
        $totalPaginas = ceil($totalRegistros / $limitePorPagina);
        return [$offset, $totalPaginas];
    }
}
