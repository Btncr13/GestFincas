<div class="modal fade" id="modalIncidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Reportar Incidencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div id="alerta-similar" class="alert alert-warning d-none mb-3">
                    <h6 class="alert-heading fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Posible coincidencia</h6>
                    <p class="small mb-2">Ya existe una incidencia abierta que podría ser la misma:</p>
                    <p class="small mb-3 p-2 bg-white rounded border text-muted" id="sim-titulo" style="font-style: italic;"></p>
                    
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-warning fw-bold" id="btn-unirme">
                            <i class="fa-solid fa-hand-holding-hand"></i> Sí, unirme a esta
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-forzar-crear">
                            <i class="fa-solid fa-file-circle-plus"></i> No, es un problema diferente (Crear nueva)
                        </button>
                    </div>
                </div>

                <form id="form-incidencia">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título (Máx. 4 palabras)</label>
                        <input type="text" class="form-control" id="incidencia-titulo" name="titulo" placeholder="Ej: Bombilla fundida portal" required>
                        <div class="d-flex justify-content-between">
                            <small class="text-danger d-none" id="error-titulo">Máximo 4 palabras permitidas.</small>
                            <small class="text-muted ms-auto" id="counter-titulo">0 / 4</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción detallada</label>
                        <textarea class="form-control" name="descripcion" id="incidencia-descripcion" rows="3" maxlength="255" placeholder="Indica detalles para ayudar al presidente..." required></textarea>
                        <div class="text-end">
                            <small class="text-muted" id="counter-descripcion">0 / 255</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjuntar Fotografía (Opcional)</label>
                        <input type="file" class="form-control" name="foto" accept="image/*">
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary opacity-50" id="btn-submit" disabled>Guardar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>