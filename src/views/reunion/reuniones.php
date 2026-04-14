<?php
// === MOCK DE DATOS PARA PRUEBAS ===
$rol = 'presidente'; // Cambiar a 'vecino' para probar la vista sin permisos de crear
$reuniones = [
    [
        'id' => 1,
        'titulo' => 'Junta Ordinaria Anual',
        'tipo' => 'Ordinaria',
        'fecha' => '25/05/2026',
        'hora' => '19:00',
        'lugar' => 'Sala Comunitaria',
        'estado' => 'Pendiente',
        'orden_dia' => '1. Lectura de cuentas.\n2. Renovación de cargos.\n3. Ruegos y preguntas.'
    ],
    [
        'id' => 2,
        'titulo' => 'Aprobación de Presupuestos',
        'tipo' => 'Extraordinaria',
        'fecha' => '10/03/2026',
        'hora' => '18:30',
        'lugar' => 'Videollamada',
        'estado' => 'Finalizada',
        'orden_dia' => '1. Votación de presupuesto para arreglo de fachada.'
    ]
];
// ===================================

include 'src/views/components/topbar.php';
?>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="container py-4 py-md-5">
            
            <!-- Header de la sección -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h1 class="fw-bold mb-0 text-dark" style="font-family: var(--fuente-titulos);">Reuniones de la Comunidad</h1>
                
                <?php if ($rol === 'presidente'): ?>
                    <!-- Botón que abre el Modal de Nueva Reunión -->
                    <button type="button" class="btn btn-brand fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaReunion">
                        <i class="fa-solid fa-plus me-2"></i> Convocar Reunión
                    </button>
                <?php endif; ?>
            </div>

            <!-- Filtros / Tabs (Corregido para Modo Oscuro) -->
            <ul class="nav nav-tabs mb-4" id="reunionesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="proximas-tab" data-bs-toggle="tab" data-bs-target="#proximas" type="button" role="tab" style="color: var(--bs-dark);">Próximas Reuniones</button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link fw-semibold text-muted" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">Historial / Actas</button>
                </li>
            </ul>

            <!-- Grid de Contenido -->
            <div class="tab-content" id="reunionesTabContent">
                
                <!-- Pestaña Próximas -->
                <div class="tab-pane fade show active" id="proximas" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($reuniones as $reunion): ?>
                            <?php if ($reunion['estado'] === 'Pendiente'): ?>
                                <div class="col">
                                    <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-warning">
                                        <div class="card-body p-4 d-flex flex-column">
                                            
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="text-muted small">
                                                    <div class="mb-1"><i class="fa-regular fa-calendar me-2 text-warning"></i><?= $reunion['fecha'] ?></div>
                                                    <div><i class="fa-regular fa-clock me-2 text-warning"></i><?= $reunion['hora'] ?> h</div>
                                                </div>
                                                <!-- FIX MODO OSCURO: style="color: #000 !important;" para forzar texto negro sobre fondo amarillo -->
                                                <span class="badge bg-warning px-2 py-1 rounded-2" style="color: #000 !important; font-weight: 700;"><?= $reunion['tipo'] ?></span>
                                            </div>
                                            
                                            <h3 class="fs-5 fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);"><?= $reunion['titulo'] ?></h3>
                                            <p class="text-muted small mb-4">
                                                <i class="fa-solid fa-location-dot me-2"></i><?= $reunion['lugar'] ?>
                                            </p>
                                            
                                            <div class="mt-auto d-flex justify-content-between align-items-center border-top pt-3">
                                                <!-- Botón que abre el Modal de Detalles -->
                                                <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold btn-ver-detalle" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalDetalleReunion"
                                                        data-titulo="<?= $reunion['titulo'] ?>"
                                                        data-fecha="<?= $reunion['fecha'] ?>"
                                                        data-hora="<?= $reunion['hora'] ?>"
                                                        data-lugar="<?= $reunion['lugar'] ?>"
                                                        data-estado="<?= $reunion['estado'] ?>"
                                                        data-orden="<?= htmlspecialchars($reunion['orden_dia']) ?>">
                                                    Ver Orden del Día
                                                </button>
                                                
                                                <?php if ($rol === 'presidente'): ?>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-link text-secondary p-0" title="Editar"><i class="fa-solid fa-pen"></i></button>
                                                        <button class="btn btn-sm btn-link text-danger p-0" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pestaña Historial -->
                <div class="tab-pane fade" id="historial" role="tabpanel">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($reuniones as $reunion): ?>
                            <?php if ($reunion['estado'] === 'Finalizada'): ?>
                                <div class="col">
                                    <div class="card shadow-sm module-card h-100 border-0 border-start border-4 border-success">
                                        <div class="card-body p-4 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="text-muted small">
                                                    <div class="mb-1"><i class="fa-regular fa-calendar-check me-2 text-success"></i><?= $reunion['fecha'] ?></div>
                                                    <div><i class="fa-regular fa-clock me-2 text-success"></i><?= $reunion['hora'] ?> h</div>
                                                </div>
                                                <span class="badge bg-secondary text-white px-2 py-1 rounded-2 fw-bold"><?= $reunion['tipo'] ?></span>
                                            </div>
                                            
                                            <h3 class="fs-5 fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);"><?= $reunion['titulo'] ?></h3>
                                            <p class="text-muted small mb-4">
                                                <i class="fa-solid fa-location-dot me-2"></i><?= $reunion['lugar'] ?>
                                            </p>
                                            
                                            <div class="mt-auto d-flex justify-content-start border-top pt-3">
                                                <button type="button" class="btn btn-outline-success btn-sm fw-semibold btn-ver-detalle" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalDetalleReunion"
                                                        data-titulo="<?= $reunion['titulo'] ?>"
                                                        data-fecha="<?= $reunion['fecha'] ?>"
                                                        data-hora="<?= $reunion['hora'] ?>"
                                                        data-lugar="<?= $reunion['lugar'] ?>"
                                                        data-estado="<?= $reunion['estado'] ?>"
                                                        data-orden="<?= htmlspecialchars($reunion['orden_dia']) ?>">
                                                    Ver Acta
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- ======================================================= -->
        <!-- MODAL: CONVOCAR NUEVA REUNIÓN (Solo Presidente)         -->
        <!-- ======================================================= -->
        <?php if ($rol === 'presidente'): ?>
        <div class="modal fade" id="modalNuevaReunion" tabindex="-1" aria-labelledby="modalNuevaReunionLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="background-color: var(--bs-light);">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h2 class="modal-title fw-bold text-dark" id="modalNuevaReunionLabel" style="font-family: var(--fuente-titulos);">Convocar Nueva Reunión</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="?route=reunion/crear" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Tipo de Reunión <span class="text-danger">*</span></label>
                                    <select name="tipo_reunion" class="form-select custom-input" required>
                                        <option value="Ordinaria" selected>Junta Ordinaria</option>
                                        <option value="Extraordinaria">Junta Extraordinaria</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Lugar <span class="text-danger">*</span></label>
                                    <input type="text" name="lugar" class="form-control custom-input" placeholder="Ej: Sala Comunitaria" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha" class="form-control custom-input" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Hora <span class="text-danger">*</span></label>
                                    <input type="time" name="hora" class="form-control custom-input" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Enlace Videollamada (Opcional)</label>
                                    <input type="url" name="enlace" class="form-control custom-input" placeholder="https://zoom.us/...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Orden del Día <span class="text-danger">*</span></label>
                                    <textarea name="orden_dia" class="form-control custom-input" rows="4" placeholder="Puntos a tratar..." required></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary fw-semibold px-4 py-2 rounded-2 text-sm-custom" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-brand fw-semibold px-4 py-2 rounded-2 text-sm-custom">Publicar Convocatoria</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ======================================================= -->
        <!-- MODAL: DETALLE / ORDEN DEL DÍA / ACTA                   -->
        <!-- ======================================================= -->
        <div class="modal fade" id="modalDetalleReunion" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="background-color: var(--bs-light);">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="modal-title fw-bold text-dark mb-2" id="detalleTitulo" style="font-family: var(--fuente-titulos);">Título de la Reunión</h2>
                            <span class="badge bg-warning px-3 py-1 rounded-2 shadow-sm text-dark fw-bold" id="detalleEstado">Estado</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        
                        <!-- Info Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3 rounded-3 h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fa-regular fa-clock fs-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small text-uppercase">Cuándo</h6>
                                        <p class="text-muted mb-0 fw-semibold text-sm-custom" id="detalleFechaHora">Fecha y Hora</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3 rounded-3 h-100">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-location-dot fs-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 small text-uppercase">Dónde</h6>
                                        <p class="text-muted mb-0 fw-semibold text-sm-custom" id="detalleLugar">Lugar</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Orden del Día -->
                        <div class="card border-0 shadow-sm border-start border-4 border-primary mb-0" style="background-color: var(--bs-info);">
                            <div class="card-body p-4">
                                <h5 class="fw-bold text-dark mb-3" style="font-family: var(--fuente-titulos);">Orden del Día / Temas</h5>
                                <p class="text-muted mb-0 fw-medium" id="detalleOrden" style="white-space: pre-wrap; line-height: 1.6;">Aquí va el orden del día...</p>
                            </div>
                        </div>

                    </div>
                    <!-- El footer del modal cambia según los permisos y el estado, en el futuro se manejará dinámicamente -->
                    <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary text-sm-custom fw-semibold" data-bs-dismiss="modal">Cerrar</button>
                        
                        <div id="accionesPresidente" class="d-none gap-2">
                            <button class="btn btn-brand text-sm-custom fw-semibold"><i class="fa-solid fa-pen-to-square me-2"></i> Redactar Acta</button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<!-- ======================================================= -->
<!-- SCRIPT PARA PASAR DATOS DE LA TARJETA AL MODAL          -->
<!-- ======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const detalleModal = document.getElementById('modalDetalleReunion');
    if(detalleModal) {
        detalleModal.addEventListener('show.bs.modal', event => {
            // Botón que disparó el modal
            const button = event.relatedTarget;
            
            // Extraer info de los data-attributes
            const titulo = button.getAttribute('data-titulo');
            const fecha = button.getAttribute('data-fecha');
            const hora = button.getAttribute('data-hora');
            const lugar = button.getAttribute('data-lugar');
            const estado = button.getAttribute('data-estado');
            const orden = button.getAttribute('data-orden');
            
            // Actualizar el contenido del modal
            document.getElementById('detalleTitulo').textContent = titulo;
            document.getElementById('detalleFechaHora').textContent = fecha + ' a las ' + hora + 'h';
            document.getElementById('detalleLugar').textContent = lugar;
            document.getElementById('detalleOrden').textContent = orden;
            
            const badgeEstado = document.getElementById('detalleEstado');
            badgeEstado.textContent = estado;
            
            // Cambiar el color del badge del estado según si está finalizada o pendiente
            if(estado === 'Finalizada') {
                badgeEstado.className = 'badge bg-success px-3 py-1 rounded-2 shadow-sm text-white fw-bold';
            } else {
                badgeEstado.className = 'badge bg-warning px-3 py-1 rounded-2 shadow-sm fw-bold';
                badgeEstado.style.color = '#000'; // Fix para el texto negro en fondo amarillo
            }

            // Mostrar acciones de presidente solo si está pendiente (Opcional visual)
            const acciones = document.getElementById('accionesPresidente');
            <?php if ($rol === 'presidente'): ?>
                if(estado === 'Pendiente') {
                    acciones.classList.remove('d-none');
                    acciones.classList.add('d-flex');
                } else {
                    acciones.classList.add('d-none');
                    acciones.classList.remove('d-flex');
                }
            <?php endif; ?>
        });
    }
});
</script>