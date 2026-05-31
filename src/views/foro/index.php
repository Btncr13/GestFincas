<?php
/**
 * @var string $rol
 * @var array $temas
 */

// Helper para generar colores consistentes e iniciales
if (!function_exists('getAvatarForo')) {
    function getAvatarForo($id, $nombre, $apellidos) {
        $iniciales = mb_strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellidos, 0, 1));
        $hue = abs(crc32($id . 'gestfincas')) % 360;
        return ['color' => "hsl({$hue}, 70%, 45%)", 'iniciales' => $iniciales];
    }
}

$titulo_pagina = "Foro Vecinal";
include 'src/views/components/topbar.php';
?>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                <!-- Cabecera -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1 font-title">Foro Vecinal</h2>
                        <p class="text-muted small mb-0">Debate y propón ideas con tus vecinos</p>
                    </div>
                    <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoTema">
                        <i class="fa-solid fa-plus me-2"></i> Crear Nuevo Tema
                    </button>
                </div>

                <?php
                $categoria_actual = $_GET['cat'] ?? null;
                
                // BUENA PRÁCTICA: Array estructurado separando la lógica de la presentación
                $categorias_info = [
                    'general' => ['titulo' => 'General', 'icono' => 'bi-megaphone-fill', 'badge' => 'bg-primary', 'desc' => 'Avisos, debates sin clasificar, saludos o temas que no encajan en otras partes.'],
                    'propuestas' => ['titulo' => 'Mejoras y Propuestas', 'icono' => 'bi-lightbulb-fill', 'badge' => 'bg-warning', 'desc' => 'El lugar ideal para proponer instalaciones, mejoras estéticas o cambios.'],
                    'convivencia' => ['titulo' => 'Convivencia y Normas', 'icono' => 'bi-file-earmark-text-fill', 'badge' => 'bg-info', 'desc' => 'Consultas sobre horarios, zonas comunes, mascotas y convivencia diaria.'],
                    'mercadillo' => ['titulo' => 'Mercadillo y Ayuda', 'icono' => 'bi-shop', 'badge' => 'bg-success', 'desc' => 'Compra-venta entre vecinos, favores y recomendaciones de profesionales.']
                ];
                ?>

                <?php if (!$categoria_actual): ?>
                    <!-- Vista de Secciones Principales (Estilo panelpresi.js) -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 ms-1 text-dark"><i class="bi bi-ui-radios-grid me-2 text-primary"></i> Secciones de la Comunidad</h5>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($categorias_info as $clave => $info): ?>
                                <a href="index.php?route=foro/index&cat=<?= $clave ?>" class="text-decoration-none d-block">
                                    <div class="card shadow-sm border-0 module-card">
                                        <div class="card-body p-4 d-flex align-items-center gap-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 icon-box-lg bg-fondo">
                                                <i class="bi <?= $info['icono'] ?> fs-2 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title fw-bold text-dark mb-1 font-title"><?= $info['titulo'] ?></h5>
                                                <p class="card-text text-muted small mb-0"><?= $info['desc'] ?></p>
                                            </div>
                                            <i class="fa-solid fa-chevron-right text-muted opacity-50 d-none d-md-block"></i>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>

                    <!-- Navegación de vuelta a Secciones -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <a href="index.php?route=foro/index" class="btn btn-outline-secondary bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center icon-box-md border-custom" title="Volver a secciones">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0 fw-bold text-dark font-title"><?= htmlspecialchars($categorias_info[$categoria_actual]['titulo'] ?? 'Temas') ?></h4>
                    </div>

                    <!-- Lista de Temas -->
                    <div class="d-flex flex-column gap-3">
                        <?php if (empty($temas)): ?>
                            <div class="card border-0 shadow-sm module-card bg-light rounded-3">
                                <div class="card-body p-5 text-center text-muted">
                                    <i class="bi bi-chat-square-text fs-1 d-block mb-3"></i>
                                    <h5>No hay ningún tema de debate aún</h5>
                                    <p>¡Anímate y sé el primero en proponer algo a la comunidad!</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($temas as $t): ?>
                                <a href="index.php?route=foro/ver&id=<?= $t['id_tema'] ?>" class="text-decoration-none d-block">
                                    <div class="card shadow-sm border-0 module-card">
                                        <div class="card-body p-4">
                                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                                <h5 class="card-title mb-0 fw-bold text-dark font-title"><?= htmlspecialchars($t['titulo']) ?></h5>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php 
                                                    $cat_key = $t['categoria'] ?? 'general';
                                                    $cat_nombre = $categorias_info[$cat_key]['titulo'] ?? ucfirst($cat_key);
                                                    $cat_badge = $categorias_info[$cat_key]['badge'] ?? 'bg-primary';
                                                    ?>
                                                    <span class="badge text-white text-xs <?= $cat_badge ?>">
                                                        <?= htmlspecialchars($cat_nombre) ?>
                                                    </span>
                                                    <?php if (($t['estado'] ?? 'abierto') === 'cerrado'): ?>
                                                        <span class="badge bg-secondary">Cerrado</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <p class="card-text text-muted small mb-3 line-clamp-2"><?= htmlspecialchars($t['descripcion']) ?></p>
                                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-3 gap-2">
                                                <?php 
                                                $avatarList = getAvatarForo($t['id_usuario'], $t['nombre'], $t['apellidos']); 
                                                ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0 avatar-sm" style="background-color: <?= $avatarList['color'] ?>;">
                                                        <?= htmlspecialchars($avatarList['iniciales']) ?>
                                                    </div>
                                                    <small class="text-muted fw-semibold text-truncate"><?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?> <span class="fw-normal opacity-75">(<?= htmlspecialchars($t['nombre_vivienda']) ?>)</span>
                                                    <?php if (isset($t['rol']) && $t['rol'] === 'presidente'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1 badge-sm">Presidente</span>
                                                    <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-3 text-muted small">
                                                    <span><i class="bi bi-chat-dots me-1"></i> <?= $t['total_respuestas'] ?> respuestas</span>
                                                    <span><i class="bi bi-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($t['ultimo_mensaje'] ?? $t['fecha_creacion'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Modal Nuevo Tema -->
<div class="modal fade" id="modalNuevoTema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-fondo rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark font-title">Crear Nuevo Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/crear" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Título del debate</label>
                        <input type="text" name="titulo" class="form-control custom-input bg-light border-custom text-dark" required placeholder="Ej: Propuesta pintura garaje">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Categoría</label>
                        <select name="categoria" class="form-select custom-input bg-light border-custom text-dark" required>
                            <?php foreach ($categorias_info as $clave => $info): ?>
                                <option value="<?= $clave ?>" <?= ($_GET['cat'] ?? '') === $clave ? 'selected' : '' ?>><?= $info['titulo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Mensaje principal</label>
                        <textarea name="descripcion" class="form-control custom-input bg-light border-custom text-dark" rows="5" required placeholder="Explica tu propuesta o duda aquí..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Publicar Tema</button>
                </div>
            </form>
        </div>
    </div>
</div>