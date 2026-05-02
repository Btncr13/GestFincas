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
                        <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos);">Foro Vecinal</h2>
                        <p class="text-muted small mb-0">Debate y propón ideas con tus vecinos</p>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoTema">
                        <i class="bi bi-plus-lg me-1"></i> Crear Nuevo Tema
                    </button>
                </div>

                <!-- Lista de Temas -->
                <div class="card border-0 shadow-sm overflow-hidden module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($temas)): ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="bi bi-chat-square-text fs-1 d-block mb-3"></i>
                                    <h5>No hay ningún tema de debate aún</h5>
                                    <p>¡Anímate y sé el primero en proponer algo a la comunidad!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($temas as $t): ?>
                                    <a href="index.php?route=foro/ver&id=<?= $t['id_tema'] ?>" class="list-group-item list-group-item-action p-4" style="background-color: transparent; border-color: var(--color-borde);">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                            <h5 class="mb-0 fw-bold" style="color: var(--bs-dark);"><?= htmlspecialchars($t['titulo']) ?></h5>
                                            <?php if (($t['estado'] ?? 'abierto') === 'cerrado'): ?>
                                                <span class="badge bg-secondary">Cerrado</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-2 text-muted text-truncate" style="max-width: 100%;"><?= htmlspecialchars($t['descripcion']) ?></p>
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-3 gap-2">
                                            <?php 
                                            $avatarList = getAvatarForo($t['id_usuario'], $t['nombre'], $t['apellidos']); 
                                            ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" style="width: 28px; height: 28px; background-color: <?= $avatarList['color'] ?>; font-size: 0.75rem;">
                                                    <?= htmlspecialchars($avatarList['iniciales']) ?>
                                                </div>
                                                <small class="text-muted fw-semibold text-truncate"><?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?> <span class="fw-normal opacity-75">(<?= htmlspecialchars($t['nombre_vivienda']) ?>)</span>
                                                <?php if (isset($t['rol']) && $t['rol'] === 'presidente'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1" style="font-size: 0.65rem; padding: 0.2em 0.4em;">Presidente</span>
                                                <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span><i class="bi bi-chat-dots me-1"></i> <?= $t['total_respuestas'] ?> respuestas</span>
                                                <span><i class="bi bi-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($t['ultimo_mensaje'] ?? $t['fecha_creacion'])) ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Nuevo Tema -->
<div class="modal fade" id="modalNuevoTema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Crear Nuevo Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/crear" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Título del debate</label>
                        <input type="text" name="titulo" class="form-control custom-input" required placeholder="Ej: Propuesta pintura garaje" style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Mensaje principal</label>
                        <textarea name="descripcion" class="form-control custom-input" rows="5" required placeholder="Explica tu propuesta o duda aquí..." style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);"></textarea>
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