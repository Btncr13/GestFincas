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
                
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                    <i class="fa-solid fa-plus me-2"></i> Crear Incidencia
                </button>
            </div>

            <div class="row g-3" id="contenedor-incidencias">
                <?php if(!empty($incidencias)): ?>
                    <?php foreach ($incidencias as $inc): ?>
                        <?php 
                            // Lógica condicional por tarjeta
                            $esMia = ($inc['id_vivienda'] == $miViviendaId);
                            $estaAbierta = in_array($inc['estado'], ['pendiente', 'abierta']);
                        ?>
                        <div class="col-12" id="incidencia-<?= $inc['id_incidencias'] ?>">
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
                                            <a href="<?= htmlspecialchars($inc['foto_incidencia']) ?>" target="_blank" title="Ver imagen a tamaño completo">
                                                <img src="<?= htmlspecialchars($inc['foto_incidencia']) ?>" alt="Evidencia de incidencia" class="rounded shadow-sm border" style="max-height: 80px; object-fit: cover;">
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <span class="badge bg-<?= $inc['estado'] == 'resuelta' ? 'success' : ($inc['estado'] == 'abierta' ? 'primary' : 'warning text-dark') ?>">
                                            <?= strtoupper($inc['estado']) ?>
                                        </span>
                                        <span class="text-muted small"><i class="fa-solid fa-users text-info me-1"></i> Afectados: <strong id="afectados-<?= $inc['id_incidencias'] ?>"><?= $inc['numero_afectados'] ?></strong></span>
                                        <span class="text-muted small"><i class="fa-solid fa-house me-1"></i> <?= htmlspecialchars($inc['nombre_vivienda'] ?? 'Comunidad') ?></span>
                                        <span class="text-muted small"><i class="fa-solid fa-calendar me-1"></i> <?= date('d/m/Y', strtotime($inc['fecha_creacion'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="ms-md-3 text-md-end d-flex flex-row flex-md-column gap-2">
                                    <?php if ($esPresidente && $estaAbierta): ?>
                                        <button class="btn btn-sm btn-success btn-resolver w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                            <i class="fa-solid fa-check"></i> Resolver
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($esPresidente && $inc['estado'] === 'pendiente'): ?>
                                        <button class="btn btn-sm btn-warning text-dark fw-bold btn-abrir w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                            <i class="fa-solid fa-folder-open"></i> Abrir
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($esMia && $estaAbierta): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-delete w-100" data-id="<?= $inc['id_incidencias'] ?>" title="Eliminar mi incidencia">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    <?php elseif (!$esMia && $estaAbierta && !$esPresidente): ?>
                                        <button class="btn btn-sm btn-info text-white fw-bold btn-unirme-card w-100" data-id="<?= $inc['id_incidencias'] ?>">
                                            <i class="fa-solid fa-hand-holding-hand"></i> Unirme
                                        </button>
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

<?php include __DIR__ . '/../components/incidencias/modalIncidencias.php'; ?>
<script src="public/assets/js/incidencias/incidencias.js"></script>