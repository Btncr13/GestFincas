<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Avisos Globales - GestFincas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/variables.css">
    <script>
        const savedTheme = localStorage.getItem('gestfincas-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="bg-light">
<?php
$ruta_actual = $_GET['route'] ?? 'superadmin/avisos';
?>

<div class="d-flex flex-column min-vh-100">
    <!-- Topbar Superadmin -->
    <nav class="navbar navbar-expand px-4 py-3 sticky-top" style="background-color: var(--bs-primary); color: white; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.1); z-index: 1050;">
        <div class="container-fluid p-0 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-light d-md-none border-0 p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarSuperadmin">
                    <i class="fa-solid fa-bars fs-4"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5 fw-bold text-white" style="font-family: var(--fuente-titulos);">GestFincas</span>
                    <span class="badge bg-warning text-dark text-uppercase ms-1 d-none d-sm-inline-block shadow-sm" style="font-size: 0.65rem; padding: 0.4em 0.8em; letter-spacing: 0.5px;">
                        SUPERADMIN
                    </span>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center gap-2 text-white" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span><?= htmlspecialchars($_SESSION['superadmin']['nombre']) ?></span>
                    </span>
                    <div class="vr text-white opacity-25" style="height: 24px;"></div>
                    <a href="index.php?route=superadmin/logout" class="btn btn-sm btn-danger fw-bold shadow-sm" title="Cerrar Sesión">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> <span class="d-none d-sm-inline">Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal (Sidebar + Contenido) -->
    <div class="container-fluid flex-grow-1 d-flex p-0">
        <div class="row w-100 m-0">
            
            <!-- Sidebar Superadmin -->
            <div class="sidebar offcanvas-md offcanvas-start col-md-3 col-lg-2 p-0 shadow-sm border-end" tabindex="-1" id="sidebarSuperadmin" style="background-color: var(--bs-light); min-height: calc(100vh - 76px);">
                <div class="offcanvas-header d-md-none border-bottom" style="min-height: 76px;">
                    <h5 class="offcanvas-title fw-bold" style="color: var(--bs-primary); font-family: var(--fuente-titulos);">Menú Superadmin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-md-flex flex-column p-0 pt-3 pt-lg-4 overflow-y-auto">
                    <ul class="nav flex-column px-2 mb-auto" style="font-family: var(--fuente-base);">
                        <li class="nav-item">
                            <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'superadmin/index') ? 'active fw-bold' : '' ?>" href="index.php?route=superadmin/index" style="background-color: <?= ($ruta_actual == 'superadmin/index') ? 'var(--bs-primary)' : 'transparent' ?>; color: <?= ($ruta_actual == 'superadmin/index') ? 'white' : 'var(--color-texto)' ?>;">
                                <i class="fa-solid fa-building-user me-2"></i> Comunidades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-link text-decoration-none py-2 px-3 rounded-2 mb-1 <?= ($ruta_actual == 'superadmin/avisos') ? 'active fw-bold' : '' ?>" href="index.php?route=superadmin/avisos" style="background-color: <?= ($ruta_actual == 'superadmin/avisos') ? 'var(--bs-primary)' : 'transparent' ?>; color: <?= ($ruta_actual == 'superadmin/avisos') ? 'white' : 'var(--color-texto)' ?>;">
                                <i class="fa-solid fa-bullhorn me-2"></i> Avisos Globales
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-3" style="border-color: var(--color-borde);"></li>
                        <li class="nav-item">
                            <a class="nav-link text-decoration-none py-2 px-3 rounded-2 mb-1" href="#" id="themeToggleBtn" style="color: var(--color-texto);">
                                <i class="fa-solid fa-moon me-2" id="themeIcon"></i> <span id="themeText">Modo Oscuro</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contenido Principal -->
            <main class="col-md-9 col-lg-10 p-4 mx-auto">
                <div class="container-fluid">
                    <!-- Alertas -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success d-flex align-items-center"><i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($exito) ?></div>
                    <?php endif; ?>

                    <!-- Cabecera -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
                        <h2 class="font-title mb-0 text-dark"><i class="fa-solid fa-bullhorn text-warning me-2"></i> Avisos Globales de Mantenimiento</h2>
                    </div>

                    <!-- Formulario de Creación Directo -->
                    <div class="card border-custom shadow-sm mb-5">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="mb-0 fw-bold font-title text-dark">Programar Nuevo Aviso</h5>
                        </div>
                        <div class="card-body">
                            <form action="index.php?route=superadmin/storeAviso" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Título del Aviso</label>
                                        <input type="text" name="titulo" class="form-control bg-fondo border-custom" required placeholder="Ej: Mantenimiento Programado">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Mensaje Corto</label>
                                        <textarea name="mensaje" class="form-control bg-fondo border-custom" rows="2" required placeholder="La plataforma no estará disponible de..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Fecha y Hora de Inicio</label>
                                        <input type="datetime-local" name="fecha_inicio" class="form-control bg-fondo border-custom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Fecha y Hora de Fin</label>
                                        <input type="datetime-local" name="fecha_fin" class="form-control bg-fondo border-custom" required>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="alert alert-warning small py-2 mb-3 border-0 shadow-sm d-flex align-items-center">
                                            <i class="fa-solid fa-circle-info me-2 fs-5"></i>
                                            El aviso bloqueará automáticamente la plataforma a los usuarios durante esta franja.
                                        </div>
                                        <button type="submit" class="btn btn-warning text-dark fw-bold shadow-sm">
                                            <i class="fa-solid fa-plus me-1"></i> Crear y Programar Aviso
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de Avisos Creados -->
                    <h5 class="font-title text-dark fw-bold mb-3">Historial de Avisos Programados</h5>
                    <div class="card border-custom shadow-sm mb-5">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3 text-dark">ID</th>
                                            <th class="py-3 text-dark">Título y Mensaje</th>
                                            <th class="py-3 text-dark">Inicio Programado</th>
                                            <th class="py-3 text-dark">Fin Programado</th>
                                            <th class="py-3 text-center text-dark">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($avisos)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No hay avisos programados actualmente.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($avisos as $aviso): ?>
                                                <tr>
                                                    <td class="px-4 align-middle fw-bold text-muted">#<?= $aviso['id_aviso'] ?></td>
                                                    <td class="align-middle text-dark">
                                                        <span class="fw-bold"><?= htmlspecialchars($aviso['titulo']) ?></span><br>
                                                        <small class="text-muted fw-normal line-clamp-2" style="max-width: 400px;"><?= htmlspecialchars($aviso['mensaje']) ?></small>
                                                    </td>
                                                    <td class="align-middle text-dark"><?= date('d/m/Y H:i', strtotime($aviso['fecha_inicio'])) ?></td>
                                                    <td class="align-middle text-dark"><?= date('d/m/Y H:i', strtotime($aviso['fecha_fin'])) ?></td>
                                                    <td class="align-middle text-center">
                                                        <form action="index.php?route=superadmin/deleteAviso" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas borrar este aviso?');">
                                                            <input type="hidden" name="id_aviso" value="<?= $aviso['id_aviso'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar aviso">
                                                                <i class="fa-solid fa-trash"></i> Eliminar
                                                            </button>
                                                        </form>
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
            </main>
        </div>
    </div>
</div>

<!-- Theme Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const htmlElement = document.documentElement;

        const updateIcon = () => {
            if (htmlElement.getAttribute('data-theme') === 'dark') {
                if (themeIcon) themeIcon.classList.replace('fa-moon', 'fa-sun');
                if (themeText) themeText.textContent = 'Modo Claro';
            } else {
                if (themeIcon) themeIcon.classList.replace('fa-sun', 'fa-moon');
                if (themeText) themeText.textContent = 'Modo Oscuro';
            }
        };
        updateIcon();

        if (themeBtn) {
            themeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const newTheme = htmlElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('gestfincas-theme', newTheme);
                updateIcon();
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>