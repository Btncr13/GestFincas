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
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                
                <?php 
                $nombres_categorias = [
                    'general' => 'General',
                    'propuestas' => 'Mejoras y Propuestas',
                    'convivencia' => 'Convivencia y Normas',
                    'mercadillo' => 'Mercadillo y Ayuda'
                ];
                $cat_key = $tema['categoria'] ?? 'general';
                $cat_nombre = $nombres_categorias[$cat_key] ?? ucfirst($cat_key);
                ?>

                <!-- Migas de pan (Breadcrumbs) -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php?route=foro/index" class="text-decoration-none fw-semibold text-primary">
                                <i class="fa-solid fa-comments me-1 opacity-75"></i>Foro
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?route=foro/index&cat=<?= htmlspecialchars($cat_key) ?>" class="text-decoration-none fw-semibold text-primary">
                                <?= htmlspecialchars($cat_nombre) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-muted fw-semibold text-truncate d-inline-block align-bottom max-w-200" aria-current="page">
                            <?= htmlspecialchars($tema['titulo']) ?>
                        </li>
                    </ol>
                </nav>

                <!-- Mensaje Original (Tema) -->
                <div class="card border-0 border-start border-4 border-danger shadow-sm mb-4 bg-light rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="fw-bold mb-0 text-dark font-title"><?= htmlspecialchars($tema['titulo']) ?></h3>
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
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm avatar-md" 
                                 style="background-color: <?= $avatarTema['color'] ?>;">
                                <?= htmlspecialchars($avatarTema['iniciales']) ?>
                            </div>
                            <div>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($tema['nombre'] . ' ' . $tema['apellidos']) ?></span>
                                <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($tema['nombre_vivienda']) ?></span>
                                <?php if (isset($tema['rol']) && $tema['rol'] === 'presidente'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1 badge-sm">Presidente</span>
                                <?php endif; ?>
                                <br>
                                <span><?= date('d/m/Y H:i', strtotime($tema['fecha_creacion'])) ?></span>
                            </div>
                        </div>
                        <div class="fs-5 text-muted ws-pre-wrap"><?= htmlspecialchars($tema['descripcion']) ?></div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-dark">Respuestas (<?= count($mensajes) ?>)</h5>

                <!-- Lista de Respuestas -->
                <?php if (empty($mensajes)): ?>
                    <div class="text-center p-5 mb-4 rounded-3 border shadow-sm bg-light">
                        <i class="fa-regular fa-comments fs-1 text-muted mb-3 d-block opacity-50"></i>
                        <h6 class="fw-bold text-muted mb-1">Aún no hay respuestas</h6>
                        <p class="text-muted small mb-0">¡Sé el primero en dar tu opinión sobre este tema!</p>
                    </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($mensajes as $m): ?>
                        <?php $esAutor = ($m['id_usuario'] == $tema['id_usuario']); ?>
                        <?php $esMio = ($m['id_usuario'] == $id_usuario); ?>
                        <?php $esPresidente = ($m['rol'] === 'presidente'); ?>
                        <?php $avatar = getAvatarForo($m['id_usuario'], $m['nombre'], $m['apellidos']); ?>
                        
                        <?php
                        $clasesBorde = 'border-0';
                        if ($esAutor) {
                            $clasesBorde = 'border-0 border-start border-4 border-danger';
                        } elseif ($esMio) {
                            $clasesBorde = 'border-0 border-start border-4 border-primary';
                        }
                        ?>
                        <div id="mensaje-<?= $m['id_mensaje'] ?>" class="card shadow-sm bg-light rounded-3 <?= $clasesBorde ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm avatar-md" 
                                             style="background-color: <?= $avatar['color'] ?>;">
                                            <?= htmlspecialchars($avatar['iniciales']) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellidos']) ?></span>
                                            <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($m['nombre_vivienda']) ?></span>
                                            <?php if ($esAutor): ?>
                                                <span class="badge bg-primary text-white ms-1">Autor</span>
                                            <?php endif; ?>
                                            <?php if ($esPresidente): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1 badge-sm">Presidente</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($m['fecha_creacion'])) ?></small>
                                        
                                        <?php if (($tema['estado'] ?? 'abierto') === 'abierto'): ?>
                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold" onclick='citarMensaje(<?= $m['id_mensaje'] ?>, <?= htmlspecialchars(json_encode($m['nombre'] . " " . $m['apellidos']), ENT_QUOTES, "UTF-8") ?>, <?= htmlspecialchars(json_encode($m['mensaje']), ENT_QUOTES, "UTF-8") ?>)' title="Responder a este mensaje"><i class="fa-solid fa-reply"></i></button>
                                        <?php endif; ?>

                                        <?php if ($esMio || $rol === 'presidente'): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0 text-decoration-none" data-bs-toggle="dropdown" title="Opciones"><i class="fa-solid fa-ellipsis-vertical px-2"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <?php if ($esMio): ?>
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
                                    '<div class="px-3 py-2 mb-2 rounded-2 shadow-sm border-start border-3 border-primary bg-soft" role="button" onclick="irAMensaje($1)" onmouseover="this.classList.add(\'opacity-75\')" onmouseout="this.classList.remove(\'opacity-75\')" title="Ir al mensaje original"><div class="d-flex justify-content-between align-items-center mb-1"><div class="d-flex align-items-center gap-2"><i class="fa-solid fa-reply text-primary text-xs"></i><span class="fw-bold text-primary text-sm-custom">$2</span></div><i class="fa-solid fa-arrow-up text-muted text-xs"></i></div><div class="text-muted fst-italic text-sm-custom lh-14 line-clamp-2">$3</div></div>',
                                    $textoHtml
                                );
                                ?>
                                <div class="text-muted ws-pre-wrap"><?= $textoHtml ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Formulario de Respuesta -->
                <?php if (($tema['estado'] ?? 'abierto') === 'abierto'): ?>
                    <div class="card border-0 shadow-sm module-card mt-4 rounded-4 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-dark">Añadir respuesta</h6>
                            <form action="index.php?route=foro/responder" method="POST">
                                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                                <div class="mb-3">
                                    <textarea name="mensaje" id="mensaje-respuesta" class="form-control custom-input text-dark" rows="4" required placeholder="Escribe tu respuesta aquí..."></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-paper-plane me-1"></i> Enviar Respuesta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary border-0 text-center py-4 rounded-3 bg-light">
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
        <div class="modal-content bg-light rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark font-title">Editar Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/editarTema" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Título</label>
                        <input type="text" name="titulo" class="form-control custom-input text-dark" required value="<?= htmlspecialchars($tema['titulo']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Mensaje principal</label>
                        <textarea name="descripcion" class="form-control custom-input text-dark" rows="5" required><?= htmlspecialchars($tema['descripcion']) ?></textarea>
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
        <div class="modal-content bg-light rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger font-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Tema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/eliminarTema" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <div class="modal-body py-1 mt-3">
                    <p class="mb-0 fs-5 text-dark">¿Estás seguro de que deseas eliminar este tema?</p>
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
        <div class="modal-content bg-light rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark font-title">Editar Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/editarMensaje" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <input type="hidden" name="id_mensaje" id="edit-id-mensaje">
                <div class="modal-body">
                    <div class="mb-3">
                        <textarea name="mensaje" id="edit-cuerpo-mensaje" class="form-control custom-input text-dark" rows="4" required></textarea>
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
        <div class="modal-content bg-light rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger font-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Eliminar Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=foro/eliminarMensaje" method="POST">
                <input type="hidden" name="id_tema" value="<?= $tema['id_tema'] ?>">
                <input type="hidden" name="id_mensaje" id="delete-id-mensaje">
                <div class="modal-body py-1 mt-3">
                    <p class="mb-0 fs-5 text-dark">¿Estás seguro de que deseas eliminar esta respuesta?</p>
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