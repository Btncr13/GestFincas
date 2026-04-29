<!-- MODAL PARA LEER COMUNICADO -->
<div class="modal fade" id="modalLectura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="modalTitulo" style="font-family: var(--fuente-titulos);"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalBadges" class="mb-3"></div>
                <p id="modalCuerpo" class="text-secondary" style="white-space: pre-wrap; line-height: 1.6;"></p>
                <hr class="my-4 opacity-50">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="modalFecha"></small>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>
</div>
