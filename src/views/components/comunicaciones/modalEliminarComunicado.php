<!-- MODAL ELIMINAR COMUNICADO -->
<div class="modal fade" id="modalEliminarComunicado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Comunicado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=comunicaciones/eliminar" method="POST">
                <div class="modal-body px-4 pb-4">
                    <input type="hidden" name="id_comunicado" id="id_comunicado_eliminar">
                    <p class="fs-6 mb-1">¿Estás seguro de que quieres eliminar el comunicado <strong id="titulo_comunicado_confirmar"></strong>?</p>
                    <p class="text-muted small">Esta acción eliminará definitivamente el aviso del tablón de la comunidad. No se puede deshacer.</p>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Confirmar Eliminación</button>
                </div>
            </form>
        </div>
    </div>
</div>
