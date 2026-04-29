<?php $titulo_pagina = "Mi Comunidad"; ?>
<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0 position-relative">

                <!-- 1. BANNER PRINCIPAL (Diseño de ayer) -->
                <div class="card border-0 mb-4 overflow-hidden shadow-sm" style="min-height: 200px; border-radius: var(--radio-lg);">
                    <img src="public/assets/img/banner.jpeg" alt="Comunidad" class="card-img w-100 h-100 object-fit-cover position-absolute" style="filter: brightness(0.6);">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4 text-white">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                <i class="fa-solid fa-users fs-4 text-white"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: var(--fuente-titulos);">Gestión de Usuarios</h2>
                                <p class="mb-0 fw-semibold text-white" style="font-size: 1.1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Directorio de vecinos y gestión de roles</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. BOTONES DE ACCIÓN (Lógica del compañero) -->
                <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
                    <button class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrearVivienda">
                        <i class="bi bi-plus-lg"></i> Crear Vivienda
                    </button>
                    <button class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold shadow-sm text-white d-flex align-items-center gap-2" onclick="prepararModificacion()">
                        <i class="bi bi-pencil-square"></i>
                        <span>Modificar</span>
                    </button>
                    <!-- Llama a la función JS del compañero -->
                    <button class="btn btn-danger rounded-pill px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" onclick="prepararEliminacion()">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>

                <!-- 3. TABLA DE VECINOS (Adaptada al Modo Oscuro) -->
                <div class="card border-0 shadow-sm overflow-hidden module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-header py-3 border-bottom" style="background-color: var(--bs-secondary); border-color: var(--color-borde) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h5 class="mb-0 fs-6 fw-bold" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Lista de Vecinos</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="color: var(--color-texto);">
                                <thead style="background-color: var(--bs-secondary);">
                                    <tr>
                                        <th class="px-4 py-3 border-0 small fw-bold text-uppercase">Vivienda</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase">Nombre Completo</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase">DNI</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase">Email</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase">Rol</th>
                                        <th class="px-4 py-3 border-0 text-end small fw-bold text-uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($vecinos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-info-circle fs-4 d-block mb-2"></i> No se han encontrado vecinos registrados en la comunidad.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($vecinos as $vecino): ?>
                                        <tr style="border-bottom: 1px solid var(--color-borde);">
                                            <td class="px-4 py-3">
                                                <span class="badge bg-info text-primary fw-bold px-2 py-1 vivienda-badge"
                                                      style="cursor: pointer; border: 2px solid transparent;"
                                                      onclick="seleccionarVivienda(this)"
                                                      data-id="<?= $vecino['id_vivienda'] ?>"
                                                      data-nombre="<?= htmlspecialchars($vecino['nombre_vivienda']) ?>">
                                                    <?= htmlspecialchars($vecino['nombre_vivienda']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 fw-semibold" style="color: var(--bs-dark);">
                                                <?= !empty($vecino['id_usuario']) ? htmlspecialchars($vecino['nombre'] . ' ' . $vecino['apellidos']) : '<i class="text-muted small">Pendiente de registro</i>' ?>
                                            </td>
                                            <td class="py-3"><?= htmlspecialchars($vecino['dni'] ?? '-') ?></td>
                                            <td class="py-3"><?= htmlspecialchars($vecino['email'] ?? '-') ?></td>
                                            <td class="py-3">
                                                <?php if (empty($vecino['id_usuario'])): ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 fw-semibold">Sin asignar</span>
                                                <?php elseif ($vecino['rol'] === 'presidente'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 fw-semibold">Presidente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 fw-semibold">Vecino</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-end">
                                                <?php if (!empty($vecino['id_usuario'])): ?>
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success small px-3">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning small px-3">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. INCLUSIÓN DE LOS MODALES PHP -->
            <?php include 'src/views/components/viviendas/modalVivienda.php'; ?>
            <?php include 'src/views/components/viviendas/modalEliminar.php'; ?>
            <?php include 'src/views/components/viviendas/modalModificar.php'; ?>

            <!-- 2. CARGAR EL JAVASCRIPT CORREGIDO -->
            <script src="public/assets/js/viviendas/crearvivienda.js"></script>

            <!-- 3. GESTIONAR ERRORES DE SESIÓN (EL ÚNICO PHP QUE NECESITA JS) -->
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if (isset($_SESSION['error_vivienda'])): ?>
                    alert('<?= addslashes($_SESSION['error_vivienda']) ?>');
                    <?php unset($_SESSION['error_vivienda']); ?>
                <?php endif; ?>
            });
            </script>

        </main>
        </div>
    </div>

            </div>
        </main>
        
        <!-- CONTENEDOR TOAST (Debe estar fuera de la etiqueta <main>) -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
            <div id="comunidadToast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="comunidadToastMessage"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <!-- 3. PUENTE PHP -> JAVASCRIPT (Para las variables de sesión) -->
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_SESSION['error_vivienda'])): ?>
                // Inyectamos el error de la sesión hacia la función JS
                showComunidadToast('<?= addslashes($_SESSION['error_vivienda']) ?>', 'warning');
                <?php unset($_SESSION['error_vivienda']); ?>
            <?php endif; ?>
        });
        </script>

    </div>
</div>