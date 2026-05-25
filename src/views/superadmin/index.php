<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel Superadmin - GestFincas</title>
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
$ruta_actual = $_GET['route'] ?? 'superadmin/index';
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

                    <!-- Cabecera y botón -->
                    <div id="comunidadesSection" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pt-2 gap-3">
                        <h2 class="font-title mb-0 text-dark">Gestión de Comunidades</h2>
                        <div class="d-flex gap-2 flex-column flex-sm-row w-100 justify-content-md-end">
                            <div class="input-group shadow-sm" style="max-width: 350px;">
                                <span class="input-group-text bg-fondo border-custom text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" id="buscadorComunidades" class="form-control bg-fondo border-custom" placeholder="Buscar comunidad, presidente..." onkeyup="filtrarComunidades()">
                            </div>
                            <button class="btn btn-primary fw-bold shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCrearComunidad">
                                <i class="fa-solid fa-plus me-1"></i> Nueva Comunidad
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Comunidades -->
                    <div class="card border-custom shadow-sm mb-5">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3 text-dark">ID</th>
                                            <th class="py-3 text-dark">Comunidad</th>
                                            <th class="py-3 text-dark">Dirección</th>
                                            <th class="py-3 text-dark">Presidente Asignado</th>
                                            <th class="py-3 text-dark">Email Presidente</th>
                                            <th class="py-3 text-center text-dark">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($comunidades)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No hay comunidades registradas.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($comunidades as $com): ?>
                                                <tr>
                                                    <td class="px-4 align-middle fw-bold text-muted">#<?= $com['id_comunidad'] ?></td>
                                                    <td class="align-middle fw-bold text-dark"><?= htmlspecialchars($com['nombre_comunidad']) ?></td>
                                                    <td class="align-middle text-dark"><?= htmlspecialchars($com['calle'] . ' ' . $com['numero']) ?></td>
                                                    <td class="align-middle">
                                                        <?php if ($com['presi_nombre']): ?>
                                                            <span class="text-dark"><?= htmlspecialchars($com['presi_nombre'] . ' ' . $com['presi_apellidos']) ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Sin Presidente</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle text-muted"><?= htmlspecialchars($com['presi_email'] ?? 'N/A') ?></td>
                                                    <td class="align-middle text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <?php if (!empty($com['presi_id'])): ?>
                                                                <form action="index.php?route=superadmin/impersonate" method="POST" target="_blank" class="m-0">
                                                                    <input type="hidden" name="id_usuario" value="<?= $com['presi_id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Entrar como este Presidente">
                                                                        <i class="fa-solid fa-user-secret"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="prepararBorradoComunidad(<?= $com['id_comunidad'] ?>, '<?= htmlspecialchars($com['nombre_comunidad'], ENT_QUOTES) ?>')" title="Eliminar Comunidad">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
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

<!-- MODAL: CREAR COMUNIDAD Y ASIGNAR PRESIDENTE -->
<div class="modal fade" id="modalCrearComunidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold font-title">Registrar Nueva Comunidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=superadmin/storeComunidad" method="POST">
                <div class="modal-body">
                    
                    <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold">1. Datos de la Comunidad</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Nombre de la Comunidad / Mancomunidad</label>
                            <input type="text" name="nombre_comunidad" class="form-control bg-fondo border-custom" required placeholder="Ej: Residencial Los Pinos">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Calle / Avenida</label>
                            <input type="text" name="calle" class="form-control bg-fondo border-custom" required placeholder="Calle Mayor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Número</label>
                            <input type="text" name="numero" class="form-control bg-fondo border-custom" required placeholder="12">
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold">2. Asignar Presidente Inicial</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" name="presi_nombre" class="form-control bg-fondo border-custom" required placeholder="Nombre del presidente">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Apellidos</label>
                            <input type="text" name="presi_apellidos" class="form-control bg-fondo border-custom" required placeholder="Apellidos">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">DNI / NIE</label>
                            <input type="text" name="presi_dni" class="form-control bg-fondo border-custom" required placeholder="DNI / NIE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Vivienda</label>
                            <input type="text" name="presi_vivienda" class="form-control bg-fondo border-custom" required placeholder="Ej: Planta 1-A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="presi_email" class="form-control bg-fondo border-custom" required placeholder="Correo Electrónico">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contraseña de acceso</label>
                            <input type="password" name="presi_password" class="form-control bg-fondo border-custom" required placeholder="Contraseña de acceso">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Comunidad y Presidente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ROJO: CONFIRMAR BORRADO DE COMUNIDAD -->
<div class="modal fade" id="modalEliminarComunidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Peligro: Borrar Comunidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=superadmin/deleteComunidad" method="POST">
                <div class="modal-body bg-light">
                    <p class="text-dark">Estás a punto de eliminar la comunidad <strong id="nombreComunidadBorrar" class="text-danger"></strong>.</p>
                    <p class="text-muted small fw-bold">Esta acción es irreversible y borrará a todos los vecinos, incidencias, foros y votaciones asociados (si la BD lo permite).</p>
                    <div class="mb-2 mt-4">
                        <label class="form-label small fw-bold text-dark">Para confirmar, escribe la palabra <span class="text-danger">ELIMINAR</span></label>
                        <input type="hidden" name="id_comunidad" id="inputIdComunidadBorrar" value="">
                        <input type="text" class="form-control border-danger" id="inputConfirmarBorrado" onkeyup="validarBorradoComunidad()" placeholder="ELIMINAR" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm" id="btnConfirmarBorrado" disabled>Eliminar Definitivamente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Theme Script -->
<script>
    function filtrarComunidades() {
        const input = document.getElementById("buscadorComunidades");
        const filter = input.value.toLowerCase();
        const trs = document.querySelectorAll("table tbody tr");

        trs.forEach(tr => {
            if (tr.children.length === 1) return; // Ignorar la fila de "No hay comunidades"
            
            let match = false;
            // Buscamos en las 5 primeras columnas (evitando la de Acciones)
            for (let i = 0; i < 5; i++) {
                if (tr.children[i]) {
                    const txt = tr.children[i].textContent || tr.children[i].innerText;
                    if (txt.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
            }
            tr.style.display = match ? "" : "none";
        });
    }

    function prepararBorradoComunidad(id, nombre) {
        document.getElementById('nombreComunidadBorrar').innerText = nombre;
        document.getElementById('inputIdComunidadBorrar').value = id;
        document.getElementById('inputConfirmarBorrado').value = '';
        document.getElementById('btnConfirmarBorrado').disabled = true;
        new bootstrap.Modal(document.getElementById('modalEliminarComunidad')).show();
    }

    function validarBorradoComunidad() {
        const input = document.getElementById('inputConfirmarBorrado').value;
        document.getElementById('btnConfirmarBorrado').disabled = (input !== 'ELIMINAR');
    }

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