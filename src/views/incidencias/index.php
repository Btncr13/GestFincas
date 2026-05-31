<?php 
// Pre-calculamos los permisos generales para usarlos en el HTML
$rolActual = $rol ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
$esPresidente = (strtolower($rolActual) === 'presidente' || strtoupper($rolActual) === 'SUPERADMIN');
$miViviendaId = $_SESSION['vivienda']['id_vivienda'] ?? null;
?>

<?php include __DIR__ . '/../components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
    
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            
            <div class="container-fluid p-0 position-relative" id="appIncidencias">

                <div class="d-flex justify-content-between flex-wrap gap-2 mb-4 align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos); color: var(--bs-dark);">Tablón de Incidencias</h2>
                        <p class="mb-0" style="color: var(--color-texto); font-size: 14px; margin-top: 0.25rem;">Gestiona las averías de la comunidad</p>
                    </div>
                    <?php if (!$esPresidente): ?>
                    <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                        <i class="fa-solid fa-plus me-2"></i> Crear Incidencia
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Filtro Desplegable de Tiempo (Global) -->
                <div class="d-flex justify-content-end mb-3">
                    <select id="filtro-tiempo" class="form-select w-auto shadow-sm" style="border-radius: var(--radio-md); font-size: 14px; border: 1px solid var(--color-borde); background-color: var(--bs-light); color: var(--color-texto);" onchange="appIncidencias.changeFiltroTiempo(this.value)">
                        <option value="anio_actual">Año en curso</option>
                        <option value="ultimos_3_meses" selected>Últimos 3 meses</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>

                <!-- Controles Tabulados -->
                <?php if (!$esPresidente): ?>
                <!-- TABS VECINO -->
                <div class="d-flex mb-3 p-1" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                    <button id="btn-tab-mis" class="btn flex-fill text-center rounded-2 py-2" style="font-size: 14px; font-weight: 500; transition: all 0.2s;" onclick="appIncidencias.switchTabVecino('mis')">Mis incidencias</button>
                    <button id="btn-tab-otras" class="btn flex-fill text-center rounded-2 py-2 text-muted" style="font-size: 14px; font-weight: 500; transition: all 0.2s;" onclick="appIncidencias.switchTabVecino('otras')">Otras incidencias</button>
                </div>
                <?php else: ?>
                <!-- TABS PRESIDENTE -->
                <div class="d-flex mb-3 p-1 flex-wrap" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                    <button id="btn-tab-pendiente" class="btn flex-fill text-center rounded-2 py-2" style="font-size: 14px; font-weight: 500; transition: all 0.2s;" onclick="appIncidencias.switchTabPresi('pendiente')">Pendientes</button>
                    <button id="btn-tab-abierta" class="btn flex-fill text-center rounded-2 py-2 text-muted" style="font-size: 14px; font-weight: 500; transition: all 0.2s;" onclick="appIncidencias.switchTabPresi('abierta')">Abiertas</button>
                    <button id="btn-tab-resuelta" class="btn flex-fill text-center rounded-2 py-2 text-muted" style="font-size: 14px; font-weight: 500; transition: all 0.2s;" onclick="appIncidencias.switchTabPresi('resuelta')">Resueltas</button>
                </div>
                <?php endif; ?>

                <!-- Contenedor donde JS escupirá las tarjetas filtradas -->
                <div id="contenedor-incidencias" class="d-flex flex-column gap-3"></div>
                
            </div>
        </main>
    </div>
</div>

<!-- Modal Lightbox para Imágenes -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 justify-content-end p-0 mb-3">
                <button type="button" class="btn-close btn-close-white fs-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="lightboxImage" src="" alt="Imagen ampliada" class="img-fluid rounded shadow-lg" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- 🟢 INYECCIÓN DE DATOS PHP -> JAVASCRIPT 🟢 -->
<script>
    const incidenciasDB = <?= json_encode($incidenciasData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const misUnionesDB = <?= json_encode($misUniones ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const userRolActual = <?= json_encode($rolActual) ?>;
    const isPresi = <?= $esPresidente ? 'true' : 'false' ?>;
    const miViviendaId = <?= json_encode($miViviendaId) ?>;
</script>

<?php include __DIR__ . '/../components/incidencias/modalIncidencias.php'; ?>
<script src="public/assets/js/incidencias/incidencias-board.js"></script>
<script src="public/assets/js/incidencias/incidencias.js"></script>