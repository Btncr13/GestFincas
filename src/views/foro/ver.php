<?php
/**
 * @var string $rol
 * @var int    $id_usuario
 * @var array $tema
 * @var array $mensajes
 */
$titulo_pagina = "Foro - " . htmlspecialchars($tema['titulo']);
include 'src/views/components/topbar.php';

// Helper para generar colores consistentes e iniciales
if (!function_exists('getAvatarForo')) {
    function getAvatarForo($id, $nombre, $apellidos) {
        $iniciales = mb_strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellidos, 0, 1));
        // Generamos un color único (Hue) basado matemáticamente en el ID del usuario
        $hue = abs(crc32($id . 'gestfincas')) % 360;
        return ['color' => "hsl({$hue}, 70%, 45%)", 'iniciales' => $iniciales];
    }
}
?>
<style>
/* Animación para iluminar el mensaje cuando saltamos a él */
@keyframes flash-highlight {
    0% { box-shadow: 0 0 0 0 transparent; }
    15% { box-shadow: 0 0 0 4px var(--bs-primary); }
    100% { box-shadow: 0 0 0 0 transparent; }
}
.highlight-flash {
    animation: flash-highlight 2s ease-out;
}
</style>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                
                <!-- Botón Volver -->
                <div class="mb-3">
                    <a href="index.php?route=foro/index" class="text-decoration-none text-muted fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al Foro
                    </a>
                </div>

                <!-- Mensaje Original (Tema) -->
                <div class="card border-0 shadow-sm mb-4 module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md); border-left: 4px solid var(--bs-primary) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="fw-bold mb-0" style="color: var(--bs-dark); font-family: var(--fuente-titulos);"><?= htmlspecialchars($tema['titulo']) ?></h3>
                            <div class="d-flex gap-2 align-items-center">
                                <?php if (($tema['estado'] ?? 'abierto') === 'cerrado'): ?>
                                    <span class="badge bg-secondary px-3 py-2">Tema Cerrado</span>
                                <?php endif; ?>
                                
                                <?php if ($rol === 'presidente'): ?>
                                    <form action="index.php?route=foro/cambiarEstado" method="POST" class="m-0">
                                        <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                                        <?php if (($tema['estado'] ?? 'abierto') === 'abierto'): ?>
                                            <input type="hidden" name="estado" value="cerrado">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold shadow-sm"><i class="fa-solid fa-lock me-1"></i> Cerrar Tema</button>
                                        <?php else: ?>
                                            <input type="hidden" name="estado" value="abierto">
                                            <button type="submit" class="btn btn-sm btn-outline-success fw-semibold shadow-sm"><i class="fa-solid fa-lock-open me-1"></i> Abrir Tema</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                                
                                <?php $esAutorTema = ($tema['id_usuario'] == $id_usuario); ?>
                                <?php if ($esAutorTema): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditarTema" title="Editar Tema"><i class="fa-solid fa-pen"></i></button>
                                <?php endif; ?>
                                <?php if ($esAutorTema || $rol === 'presidente'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEliminarTema" title="Eliminar Tema"><i class="fa-solid fa-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php $avatarTema = getAvatarForo($tema['id_usuario'], $tema['nombre'], $tema['apellidos']); ?>
                        <div class="d-flex align-items-center gap-2 mb-4 text-muted small">
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                 style="width: 36px; height: 36px; background-color: <?= $avatarTema['color'] ?>; font-size: 0.9rem;">
                                <?= htmlspecialchars($avatarTema['iniciales']) ?>
                            </div>
                            <div>
                                <span class="fw-bold" style="color: var(--bs-dark);"><?= htmlspecialchars($tema['nombre'] . ' ' . $tema['apellidos']) ?></span>
                                <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($tema['nombre_vivienda']) ?></span>
                                <?php if (isset($tema['rol']) && $tema['rol'] === 'presidente'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">Presidente</span>
                                <?php endif; ?>
                                <br>
                                <span><?= date('d/m/Y H:i', strtotime($tema['fecha_creacion'])) ?></span>
                            </div>
                        </div>
                        <div class="fs-5" style="color: var(--color-texto); white-space: pre-wrap;"><?= htmlspecialchars($tema['descripcion']) ?></div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3" style="color: var(--bs-dark);">Respuestas (<?= count($mensajes) ?>)</h5>

                <!-- Lista de Respuestas -->
                <?php if (empty($mensajes)): ?>
                    <div class="text-center p-5 mb-4 rounded-3 border shadow-sm" style="background-color: var(--bs-light); border-color: var(--color-borde) !important;">
                        <i class="fa-regular fa-comments fs-1 text-muted mb-3 d-block opacity-50"></i>
                        <h6 class="fw-bold text-muted mb-1">Aún no hay respuestas</h6>
                        <p class="text-muted small mb-0">¡Sé el primero en dar tu opinión sobre este tema!</p>
                    </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($mensajes as $m): ?>
                        <?php $esAutor = ($m['id_usuario'] == $tema['id_usuario']); ?>
                        <?php $esPresidente = ($m['rol'] === 'presidente'); ?>
                        <?php $avatar = getAvatarForo($m['id_usuario'], $m['nombre'], $m['apellidos']); ?>
                        
                        <div id="mensaje-<?= $m['id_mensaje'] ?>" class="card border-0 shadow-sm module-card" style="background-color: var(--bs-light); border-radius: var(--radio-md);">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                             style="width: 36px; height: 36px; background-color: <?= $avatar['color'] ?>; font-size: 0.9rem;">
                                            <?= htmlspecialchars($avatar['iniciales']) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold" style="color: var(--bs-dark);"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellidos']) ?></span>
                                            <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($m['nombre_vivienda']) ?></span>
                                            <?php if ($esAutor): ?>
                                                <span class="badge bg-primary text-white ms-1">Autor</span>
                                            <?php endif; ?>
                                            <?php if ($esPresidente): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">Presidente</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($m['fecha_creacion'])) ?></small>
                                        
                                        <?php if (($tema['estado'] ?? 'abierto') === 'abierto'): ?>
                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold" onclick='citarMensaje(<?= $m['id_mensaje'] ?>, <?= htmlspecialchars(json_encode($m['nombre'] . " " . $m['apellidos']), ENT_QUOTES, "UTF-8") ?>, <?= htmlspecialchars(json_encode($m['mensaje']), ENT_QUOTES, "UTF-8") ?>)' title="Responder a este mensaje"><i class="fa-solid fa-reply"></i></button>
                                        <?php endif; ?>

                                        <?php $esAutorMensaje = ($m['id_usuario'] == $id_usuario); ?>
                                        <?php if ($esAutorMensaje || $rol === 'presidente'): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" title="Opciones"><i class="fa-solid fa-ellipsis-vertical px-2"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <?php if ($esAutorMensaje): ?>
                                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalEditarMensaje" data-id="<?= $m['id_mensaje'] ?>" data-cuerpo="<?= htmlspecialchars($m['mensaje']) ?>"><i class="fa-solid fa-pen me-2"></i> Editar</a></li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#modalEliminarMensaje" data-id="<?= $m['id_mensaje'] ?>"><i class="fa-solid fa-trash me-2"></i> Eliminar</a></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php 
                                // Parsear el formato [cita id="..." autor="..."]...[/cita] y convertirlo en cajita HTML
                                $textoHtml = htmlspecialchars($m['mensaje']);
                                $textoHtml = preg_replace(
                                    '/\[cita id=&quot;(\d+)&quot; autor=&quot;(.*?)&quot;\]\s*(.*?)\s*\[\/cita\]/s',
                                    '<div class="px-3 py-2 mb-2 rounded-2 shadow-sm" style="background-color: var(--color-fondo-formularios); border-left: 3px solid var(--bs-primary); cursor: pointer; transition: opacity 0.2s;" onclick="irAMensaje($1)" onmouseover="this.style.opacity=\'0.8\'" onmouseout="this.style.opacity=\'1\'" title="Ir al mensaje original"><div class="d-flex justify-content-between align-items-center mb-1"><div class="d-flex align-items-center gap-2"><i class="fa-solid fa-reply text-primary" style="font-size: 0.75rem;"></i><span class="fw-bold" style="color: var(--bs-primary); font-size: 0.85rem;">$2</span></div><i class="fa-solid fa-arrow-up text-muted" style="font-size: 0.75rem;"></i></div><div class="text-muted fst-italic" style="font-size: 0.85rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">$3</div></div>',
                                    $textoHtml
                                );
                                ?>
                                <div style="color: var(--color-texto); white-space: pre-wrap;"><?= $textoHtml ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Formulario de Respuesta -->
                <?php if (($tema['estado'] ?? 'abierto') === 'abierto'): ?>
                    <div class="card border-0 shadow-sm module-card mt-4" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3" style="color: var(--bs-dark);">Añadir respuesta</h6>
                            <form action="index.php?route=foro/responder" method="POST">
                                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                                <div class="mb-3">
                                    <textarea name="mensaje" id="mensaje-respuesta" class="form-control custom-input" rows="4" required placeholder="Escribe tu respuesta aquí..." style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-paper-plane me-1"></i> Enviar Respuesta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary border-0 text-center py-4 rounded-3" style="background-color: var(--color-fondo-formularios);">
                        <i class="fa-solid fa-lock fs-3 d-block mb-2 text-muted"></i>
                        <span class="fw-semibold text-muted">Este tema ha sido cerrado por administración y no admite más respuestas.</span>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

<!-- Modales de Edición y Eliminación -->
<div class="modal fade" id="modalEditarTema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Editar Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/editarTema" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Título</label>
                        <input type="text" name="titulo" class="form-control custom-input" required value="<?= htmlspecialchars($tema['titulo']) ?>" style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Mensaje principal</label>
                        <textarea name="descripcion" class="form-control custom-input" rows="5" required style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);"><?= htmlspecialchars($tema['descripcion']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminarTema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" style="font-family: var(--fuente-titulos);"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/eliminarTema" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <div class="modal-body py-1 mt-3">
                    <p class="mb-0 fs-5" style="color: var(--bs-dark);">¿Estás seguro de que deseas eliminar este tema?</p>
                    <p class="text-muted small mt-2 mb-0">Esta acción no se puede deshacer y se borrarán también todas las respuestas.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarMensaje" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--bs-dark); font-family: var(--fuente-titulos);">Editar Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/editarMensaje" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <input type="hidden" name="id_mensaje" id="edit-id-mensaje">
                <div class="modal-body">
                    <div class="mb-3">
                        <textarea name="mensaje" id="edit-cuerpo-mensaje" class="form-control custom-input" rows="4" required style="background-color: var(--bs-light); border-color: var(--color-borde); color: var(--bs-dark);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminarMensaje" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" style="font-family: var(--fuente-titulos);"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/eliminarMensaje" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <input type="hidden" name="id_mensaje" id="delete-id-mensaje">
                <div class="modal-body py-1 mt-3">
                    <p class="mb-0 fs-5" style="color: var(--bs-dark);">¿Estás seguro de que deseas eliminar esta respuesta?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEditarMensaje = document.getElementById('modalEditarMensaje');
        if (modalEditarMensaje) {
            modalEditarMensaje.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                this.querySelector('#edit-id-mensaje').value = button.getAttribute('data-id');
                this.querySelector('#edit-cuerpo-mensaje').value = button.getAttribute('data-cuerpo');
            });
        }

        const modalEliminarMensaje = document.getElementById('modalEliminarMensaje');
        if (modalEliminarMensaje) {
            modalEliminarMensaje.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                this.querySelector('#delete-id-mensaje').value = button.getAttribute('data-id');
            });
        }
    });

    function citarMensaje(id, autor, texto) {
        const textarea = document.getElementById('mensaje-respuesta');
        if (textarea) {
            // Quitamos citas anidadas previas para no ensuciar la nueva cita con cajitas infinitas
            let textoLimpio = texto.replace(/\[cita.*?\][\s\S]*?\[\/cita\]\n*/g, '').trim();
            const cita = `[cita id="${id}" autor="${autor}"]${textoLimpio}[/cita]\n`;
            
            textarea.value = cita + textarea.value;
            textarea.focus();
            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function irAMensaje(id) {
        const elemento = document.getElementById('mensaje-' + id);
        if (elemento) {
            elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });
            elemento.classList.remove('highlight-flash');
            void elemento.offsetWidth; // Forzamos al navegador a reiniciar la animación
            elemento.classList.add('highlight-flash');
        }
    }
</script>