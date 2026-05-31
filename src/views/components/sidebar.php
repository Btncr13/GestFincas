<?php
// Blindaje de variables: si no existen, las sacamos de la sesión por defecto
$rolReal = $rolReal ?? $_SESSION['vivienda']['rol'] ?? 'vecino';
$rol = $rol ?? $rolReal;

// 🟢 LÓGICA DE RUTAS Y MENÚ ACTIVO 🟢
$dashboardUrl = ($rol === 'presidente' || $rol === 'PRESIDENTE') ? 'auth/panelpresi' : 'auth/panelvecino';
$ruta_actual = $_GET['route'] ?? $dashboardUrl;
$isDashboard = in_array($ruta_actual, ['auth/panelpresi', 'auth/panelvecino']);
?>

<div class="sidebar offcanvas-md offcanvas-start col-md-3 col-lg-2 p-0 shadow-sm border-end" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel" style="background-color: var(--bs-light); min-height: calc(100vh - 76px);">

    <div class="offcanvas-header d-md-none border-bottom" style="min-height: 76px;">
        <h5 class="offcanvas-title fw-bold" id="sidebarMenuLabel" style="color: var(--bs-primary); font-family: var(--fuente-titulos);">Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-md-flex flex-column p-0 pt-3 pt-lg-4 overflow-y-auto">

        <div class="px-3 mb-4">
            <div class="d-flex align-items-center p-2 rounded-3" style="background-color: var(--color-fondo-formularios); border: 1px solid var(--color-borde);">
                <div class="d-flex justify-content-center align-items-center rounded-2 me-2" style="width: 40px; height: 40px; background-color: var(--bs-primary); color: white; flex-shrink: 0;">
                    <i class="fa-regular fa-building fs-5"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.85rem; font-family: var(--fuente-titulos); color: var(--bs-dark);"><?= htmlspecialchars($nombreComunidad) ?></h6>
                    <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;"><?= htmlspecialchars($direccion) ?></small>
                </div>
            </div>
        </div>

        <hr class="dropdown-divider my-2" style="border-color: var(--color-borde);">

        <ul class="nav flex-column px-2 mb-auto" style="font-family: var(--fuente-base);">

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= $isDashboard ? 'active fw-bold' : '' ?>"
                    href="index.php?route=<?= $dashboardUrl ?>"
                    style="background-color: <?= $isDashboard ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= $isDashboard ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-table-cells-large me-2 fa-fw"></i> Dashboard
                </a>
            </li>

            <?php if ($rol === 'presidente'): ?>
                <li class="nav-item">
                    <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'micomunidad/index') ? 'active fw-bold' : '' ?>"
                        href="index.php?route=micomunidad/index"
                        style="background-color: <?= ($ruta_actual == 'micomunidad/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'micomunidad/index') ? 'white' : 'var(--color-texto)' ?>;">
                        <i class="fa-solid fa-users me-2 fa-fw"></i> Mi Comunidad
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'comunicaciones/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=comunicaciones/index"
                    style="background-color: <?= ($ruta_actual == 'comunicaciones/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'comunicaciones/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-bullhorn me-2 fa-fw"></i> Comunicaciones
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'foro/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=foro/index"
                    style="background-color: <?= ($ruta_actual == 'foro/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'foro/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-comments me-2 fa-fw"></i> Foro Vecinal
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'votacion/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=votacion/index"
                    style="background-color: <?= ($ruta_actual == 'votacion/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'votacion/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-check-to-slot me-2 fa-fw"></i> Votaciones
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'reserva/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=reserva/index"
                    style="background-color: <?= ($ruta_actual == 'reserva/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'reserva/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-calendar-check me-2 fa-fw"></i> Reservas
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'reunion/reuniones') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=reunion/reuniones"
                    style="background-color: <?= ($ruta_actual == 'reunion/reuniones') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'reunion/reuniones') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-people-group me-2 fa-fw"></i> Reuniones
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'incidencias/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=incidencias/index"
                    style="background-color: <?= ($ruta_actual == 'incidencias/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'incidencias/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-screwdriver-wrench me-2 fa-fw"></i> Incidencias
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'matricula/index') ? 'active fw-bold' : '' ?>"
                    href="index.php?route=matricula/index"
                    style="background-color: <?= ($ruta_actual == 'matricula/index') ? 'var(--bs-primary)' : 'transparent' ?>; 
                          color: <?= ($ruta_actual == 'matricula/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-car me-2 fa-fw"></i> Parking
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'finanzas/index') ? 'active fw-bold' : '' ?>"
                href="index.php?route=finanzas/index"
                style="background-color: <?= ($ruta_actual == 'finanzas/index') ? 'var(--bs-primary)' : 'transparent' ?>;
                        color: <?= ($ruta_actual == 'finanzas/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-wallet me-2 fa-fw"></i> Finanzas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'proveedor/index') ? 'active fw-bold' : '' ?>"
                href="index.php?route=proveedor/index"
                style="background-color: <?= ($ruta_actual == 'proveedor/index') ? 'var(--bs-primary)' : 'transparent' ?>;
                        color: <?= ($ruta_actual == 'proveedor/index') ? 'white' : 'var(--color-texto)' ?>;">
                    <i class="fa-solid fa-address-book me-2"></i> Proveedores
                </a>
            </li>

            <li>
                <hr class="dropdown-divider my-3" style="border-color: var(--color-borde);">
            </li>

            <li class="nav-item">
                <a class="nav-link text-decoration-none py-2 px-3 rounded-2 mb-1" href="#" id="themeToggleBtn" style="color: var(--color-texto);">
                    <i class="fa-solid fa-moon me-2" id="themeIcon"></i> <span id="themeText">Modo Oscuro</span>
                </a>
            </li>
        </ul>

        <!-- Botón de cambio de rol anclado al fondo -->
        <?php if ($rolReal === 'presidente'): ?>
            <div class="px-3 mt-auto mb-4">
                <hr class="dropdown-divider mb-3" style="border-color: var(--color-borde);">
                <?php if ($rol === 'vecino'): ?>
                    <a href="index.php?route=auth/panelpresi" class="btn w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold text-white shadow-sm" style="background-color: var(--bs-primary); font-size: 0.85rem;">
                        <i class="fa-solid fa-user-tie"></i> Cambiar a Presidente
                    </a>
                <?php else: ?>
                    <a href="index.php?route=auth/panelvecino" class="btn w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold text-dark shadow-sm" style="background-color: var(--bs-light); border: 1px solid var(--color-borde); font-size: 0.85rem;">
                        <i class="fa-solid fa-house"></i> Cambiar a Vecino
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const htmlElement = document.documentElement;

        // Función para cambiar el icono y el texto dependiendo del tema actual
        const updateIcon = () => {
            if (htmlElement.getAttribute('data-theme') === 'dark') {
                if (themeIcon) themeIcon.classList.replace('fa-moon', 'fa-sun');
                if (themeText) themeText.textContent = 'Modo Claro';
            } else {
                if (themeIcon) themeIcon.classList.replace('fa-sun', 'fa-moon');
                if (themeText) themeText.textContent = 'Modo Oscuro';
            }
        };

        // Configuramos la interfaz inicial
        updateIcon();

        // Evento al hacer click en el enlace del menú
        if (themeBtn) {
            themeBtn.addEventListener('click', (e) => {
                e.preventDefault(); // Evitamos que salte hacia arriba de la página
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('gestfincas-theme', newTheme);

                updateIcon();
            });
        }
    });
</script>