<?php 
// Pre-calculamos los permisos generales para usarlos en el HTML
$rolActual = $rol ?? ($_SESSION['vivienda']['rol'] ?? 'vecino');
$esPresidente = (strtolower($rolActual) === 'presidente' || strtoupper($rolActual) === 'SUPERADMIN');
$miViviendaId = $_SESSION['vivienda']['id_vivienda'] ?? null;
?>

<div class="d-flex" id="wrapper">
    
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 bg-light">
        
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <main class="container-fluid py-4 px-md-4">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="h3 fw-bold text-primary mb-0">Tablón de Incidencias</h1>
                
                <?php if (!$esPresidente): ?>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                        <i class="fa-solid fa-plus me-2"></i> Crear Incidencia
                    </button>
                <?php endif; ?>
            </div>

            <div class="row g-3" id="contenedor-incidencias">
                <?php if(!empty($incidencias)): ?>
                    <?php foreach ($incidencias as $inc): ?>
                        <?php 
                            // Lógica condicional por tarjeta
                            $esMia = ($inc['id_vivienda'] == $miViviendaId);
                            $estaAbierta = in_array($inc['estado'], ['pendiente', 'abierta']);
                            $yaUnido = in_array($inc['id_incidencias'], $misUniones ?? []);
                        ?>
                        <div class="col-12 card-incidencia" id="incidencia-<?= $inc['id_incidencias'] ?>">
                            <div class="card shadow-sm border-0 flex-column flex-md-row align-items-md-center p-3 <?= $esMia ? 'border-start border-primary border-4' : '' ?>">
                                <div class="flex-grow-1 mb-3 mb-md-0">
                                    <h5 class="mb-1 fw-bold">
                                        <?= htmlspecialchars($inc['titulo']) ?>
                                        <?php if($esMia): ?> <span class="badge bg-primary ms-2" style="font-size: 0.6em;">MI INCIDENCIA</span> <?php endif; ?>
                                    </h5>
                                    <p class="mb-2 text-muted small"><?= htmlspecialchars($inc['descripcion'] ?? '') ?></p>
                                    
                                    <!-- MOSTRAR FOTO SI EXISTE -->
                                    <?php if (!empty($inc['foto_incidencia'])): ?>
                                        <div class="mb-3">
                                            <a href="javascript:void(0);" class="lightbox-trigger" data-img="<?= htmlspecialchars($inc['foto_incidencia']) ?>" title="Ampliar imagen">
                                                <img src="<?= htmlspecialchars($inc['foto_incidencia']) ?>" alt="Evidencia de incidencia" class="rounded shadow-sm border" style="max-height: 80px; object-fit: cover; cursor: zoom-in; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <!-- ====== RENDERIZADO DEL BADGE SEGÚN ESTADO ====== -->
                                        <?php if ($inc['estado'] === 'pendiente'): ?>
                                            <span class="badge bg-secondary"><i class="fa-solid fa-clock"></i> Pendiente</span>
                                        <?php elseif ($inc['estado'] === 'abierta'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-folder-open"></i> En curso</span>
                                        <?php elseif ($inc['estado'] === 'resuelta'): ?>
                                            <span class="badge bg-success"><i class="fa-solid fa-check-circle"></i> Resuelta</span>
                                        <?php endif; ?>
                                        
                                        <span class="text-muted small"><i class="fa-solid fa-users text-info me-1"></i> Afectados: <strong id="afectados-<?= $inc['id_incidencias'] ?>"><?= $inc['numero_afectados'] ?></strong></span>
                                        <span class="text-muted small"><i class="fa-solid fa-house me-1"></i> <?= htmlspecialchars($inc['nombre_vivienda'] ?? 'Comunidad') ?></span>
                                        <span class="text-muted small"><i class="fa-solid fa-calendar me-1"></i> <?= date('d/m/Y', strtotime($inc['fecha_creacion'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="ms-md-3 text-md-end d-flex flex-row flex-md-column gap-2">
                                    <?php if ($esPresidente && $inc['estado'] === 'abierta'): ?>
                                        <button class="btn btn-sm btn-success btn-resolver w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                            <i class="fa-solid fa-check"></i> Resolver
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($esPresidente && $inc['estado'] === 'pendiente'): ?>
                                        <button class="btn btn-sm btn-warning text-dark fw-bold btn-abrir w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                            <i class="fa-solid fa-folder-open"></i> Abrir
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($esMia || $esPresidente): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-delete w-100" data-id="<?= $inc['id_incidencias'] ?>" title="Eliminar incidencia">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (!$esMia && $estaAbierta && !$esPresidente): ?>
                                        <?php if ($yaUnido): ?>
                                            <button class="btn btn-sm btn-secondary text-white fw-bold w-100" disabled>
                                                <i class="fa-solid fa-check"></i> Te has unido
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-info text-white fw-bold btn-unirme-card w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                                <i class="fa-solid fa-hand-holding-hand"></i> Unirme
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 p-5 text-center">
                            <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No hay incidencias en la comunidad</h5>
                            <p class="text-muted small">Todo funciona correctamente en este momento.</p>
                        </div>
                    </div>
                <?php endif; ?>
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

<?php include __DIR__ . '/../components/incidencias/modalIncidencias.php'; ?>
<script src="public/assets/js/incidencias/incidencias.js"></script>