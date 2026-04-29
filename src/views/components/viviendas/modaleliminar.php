<!-- MODAL ELIMINAR VIVIENDA -->
<div class="modal fade" id="modalEliminarVivienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Vivienda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=micomunidad/eliminarViviendaAction" method="POST">
                <div class="modal-body px-4 pb-4">
                    <input type="hidden" name="id_vivienda_eliminar" id="id_vivienda_eliminar">
                    <p class="fs-6 mb-1">¿Estás seguro de que quieres eliminar la vivienda <strong id="nombre_vivienda_confirmar"></strong>?</p>
                    <p class="text-muted small">Esta acción eliminará definitivamente la vivienda y los datos del vecino asociado. No se puede deshacer.</p>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Confirmar Eliminación</button>
                </div>
            </form>
        </div>
    </div>
</div>
