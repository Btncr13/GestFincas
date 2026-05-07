<?php
// Extraemos los datos de la sesión (se guardaron en el AuthController al hacer login)
$rolReal = $_SESSION['vivienda']['rol'] ?? 'vecino';
$rol = $_SESSION['modo_vista'] ?? $rolReal;
$nombreComunidad = $_SESSION['vivienda']['nombre_comunidad'] ?? 'Comunidad';
$nombreVivienda = $_SESSION['vivienda']['nombre_vivienda'] ?? 'Vivienda';

// Si es presidente, la etiqueta será verde (success). Si no, gris.
$badgeClass = ($rol === 'presidente') ? 'bg-success' : 'bg-secondary';
?>

<nav class="navbar navbar-expand px-4 py-3" style="background-color: var(--bs-primary); color: white; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="container-fluid p-0 d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-light d-md-none border-0 p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <i class="fa-solid fa-bars fs-4"></i>
            </button>

            <div class="d-flex align-items-center gap-2">
                <img src="public/assets/img/logo2.png" alt="Logo" style="width:40px; height: 40px; object-fit: cover; filter: brightness(0) invert(1);">
                <span class="fs-5 fw-bold text-white" style="font-family: var(--fuente-titulos);">GestFincas</span>

                <span class="badge <?= $badgeClass ?> text-white text-uppercase ms-1 d-none d-sm-inline-block" style="font-size: 0.65rem; padding: 0.4em 0.8em; letter-spacing: 0.5px;">
                    <?= htmlspecialchars($rol) ?>
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">

            <div class="d-none d-lg-flex align-items-center gap-2 text-white-50" style="font-size: 0.9rem;">
                <i class="fa-regular fa-building"></i>
                <span><?= htmlspecialchars($nombreComunidad) ?></span>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                    <i class="fa-regular fa-user"></i>
                    <span><?= htmlspecialchars($nombreVivienda) ?></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3 py-2" style="min-width: 220px; border-radius: var(--radio-md); background-color: var(--bs-light);">
                    <li>
                        <div class="px-3 py-2 text-muted small d-lg-none border-bottom mb-2">
                            <i class="fa-regular fa-building me-1"></i> <?= htmlspecialchars($nombreComunidad) ?>
                        </div>
                    </li>

                    <li>
                        <div class="px-3 py-1">
                            <div class="fw-bold" style="font-family: var(--fuente-titulos); font-size: 0.95rem; color: var(--bs-dark);">
                                Vivienda: <?= htmlspecialchars($nombreVivienda) ?>
                            </div>
                            <div style="color: var(--bs-success); font-size: 0.85rem;">
                                <?= ucfirst(htmlspecialchars($rol)) ?>
                            </div>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider my-2" style="border-color: var(--color-borde);">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-3 text-sm-custom" href="index.php?route=usuario/perfil" style="color: var(--bs-dark);">
                            <div class="d-flex justify-content-center text-muted" style="width: 20px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="18" cy="15" r="3"></circle>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M10 15H6a4 4 0 0 0-4 4v2"></path>
                                    <path d="m21.7 16.4-.9-.3"></path>
                                    <path d="m15.2 13.9-.9-.3"></path>
                                    <path d="m16.6 18.7.3-.9"></path>
                                    <path d="m19.1 12.2.3-.9"></path>
                                    <path d="m19.6 18.7-.4-1"></path>
                                    <path d="m16.8 12.3-.4-1"></path>
                                    <path d="m14.3 16.6 1-.4"></path>
                                    <path d="m20.7 13.8 1-.4"></path>
                                </svg>
                            </div>
                            <span>Mi Perfil</span>
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-3 text-danger text-sm-custom" href="index.php?route=auth/logout">
                            <div class="d-flex justify-content-center" style="width: 20px;">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </div>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>