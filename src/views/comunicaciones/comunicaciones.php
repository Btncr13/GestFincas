<?php include 'src/views/components/topbar.php'; ?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">
            <div class="container-fluid p-0">
                
                <!-- ENCABEZADO -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-1" style="font-family: var(--fuente-titulos);">Comunicaciones</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item small"><a href="index.php?route=auth/panel<?= $rol === 'presidente' ? 'presi' : 'vecino' ?>" class="text-decoration-none">Panel</a></li>
                                <li class="breadcrumb-item small active" aria-current="page">Tablón de Anuncios</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if ($rol === 'presidente'): ?>
                    <!-- VISTA PRESIDENTE: REDACCIÓN -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square text-primary"></i>
                                <h5 class="mb-0 fs-6 fw-bold" style="font-family: var(--fuente-titulos);">Redactar Nuevo Comunicado</h5>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="index.php?route=comunicaciones/store" method="POST" id="formComunicado">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Título del Aviso</label>
                                    <input type="text" name="titulo" class="form-control custom-input" placeholder="Ej: Corte de agua programado" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Cuerpo del Comunicado</label>
                                    <textarea name="cuerpo" class="form-control custom-input" rows="4" placeholder="Escribe aquí los detalles para los vecinos..." required></textarea>
                                </div>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="urgente" id="checkUrgente">
                                        <label class="form-check-label small fw-bold text-danger" for="checkUrgente">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Marcar como urgente
                                        </label>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-semibold border" onclick="document.getElementById('formComunicado').reset()">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm">
                                            <i class="bi bi-send-fill me-1"></i> Publicar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TABLA DE COMUNICADOS (Común para ambos, pero con acciones para presidente) -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-megaphone-fill text-primary"></i>
                            <h5 class="mb-0 fs-6 fw-bold" style="font-family: var(--fuente-titulos);">Historial de Avisos</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3 border-0 small fw-bold text-uppercase text-muted">Fecha</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">Título</th>
                                        <th class="py-3 border-0 small fw-bold text-uppercase text-muted">Tipo</th>
                                        <th class="px-4 py-3 border-0 text-end small fw-bold text-uppercase text-muted">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($comunicados)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                                                Aún no se han publicado comunicados en esta comunidad.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($comunicados as $com): ?>
                                            <tr style="cursor: pointer;" onclick="verComunicado(<?= $com['id_comunicado'] ?>)">
                                                <td class="px-4 py-3 small text-muted">
                                                    <?= date('d/m/Y', strtotime($com['fecha_publicacion'])) ?>
                                                </td>
                                                <td class="py-3 fw-semibold text-dark">
                                                    <?= htmlspecialchars($com['titulo']) ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($com['tipo'] === 'urgente'): ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 fw-semibold">Urgente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info bg-opacity-10 text-primary border border-info border-opacity-25 px-2 fw-semibold">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button class="btn btn-outline-primary btn-sm rounded-circle" title="Leer">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <?php if ($rol === 'presidente'): ?>
                                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" 
                                                                    title="Eliminar" 
                                                                    onclick="event.stopPropagation(); prepararEliminacion(<?= $com['id_comunicado'] ?>, '<?= htmlspecialchars($com['titulo'], ENT_QUOTES) ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
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

<?php include 'src/views/components/comunicaciones/modalLeerComunicado.php'; ?>
<?php include 'src/views/components/comunicaciones/modalEliminarComunicado.php'; ?>

<script>
function verComunicado(id) {
    console.log('Intentando ver comunicado con ID:', id); // Debug: Verifica el ID enviado desde la tabla
    fetch(`index.php?route=comunicaciones/leer&id=${id}`)
        .then(response => response.json())
        .then(res => {
            console.log('Respuesta del servidor:', res); // Debug: Verifica la respuesta completa del controlador
            if (res.success) {
                const com = res.data;
                document.getElementById('modalTitulo').textContent = com.titulo;
                document.getElementById('modalCuerpo').textContent = com.cuerpo;
                
                const fechaObj = new Date(com.fecha_publicacion.replace(/-/g, "/"));
                document.getElementById('modalFecha').textContent = 'Publicado el ' + (isNaN(fechaObj) ? com.fecha_publicacion : fechaObj.toLocaleDateString());
                
                const badgeContainer = document.getElementById('modalBadges');
                badgeContainer.innerHTML = '';
                if (com.tipo === 'urgente') {
                    badgeContainer.innerHTML = '<span class="badge bg-danger text-white px-2 py-1 small"><i class="bi bi-exclamation-triangle-fill me-1"></i>URGENTE</span>';
                }

                const modalEl = document.getElementById('modalLectura');
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.show();

                // Si hay un punto de notificación en el sidebar/panel, esto ayuda a refrescar el estado 
                // aunque para una actualización real sin recarga necesitaríamos más JS o recargar al cerrar.
                document.getElementById('modalLectura').addEventListener('hidden.bs.modal', function () {
                    // Opcional: recargar si queremos que el puntito de notificación desaparezca al instante
                    // location.reload(); 
                }, { once: true });
            }
        })
        .catch(error => console.error('Error:', error));
}

function prepararEliminacion(id, titulo) {
    document.getElementById('id_comunicado_eliminar').value = id;
    document.getElementById('titulo_comunicado_confirmar').textContent = titulo;
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminarComunicado'));
    modal.show();
}

// Evitar que el click en el botón de eliminar del presidente abra el modal de lectura
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('click', (e) => e.stopPropagation());
});
</script>
