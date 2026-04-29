<!-- Modal Editar Espacio -->
<div class="modal fade" id="modalEditarEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Instalación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarEspacio">
                <input type="hidden" name="id_espacios_comunidad" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" id="edit_nombre" name="nombre_espacio" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Aforo Total</label>
                            <input type="number" id="edit_aforo" name="aforo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max. Personas / Reserva</label>
                            <input type="number" id="edit_max" name="max_personas" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apertura</label>
                            <input type="time" id="edit_apertura" name="hora_apertura" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cierre</label>
                            <input type="time" id="edit_cierre" name="hora_cierre" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Duración (min)</label>
                            <input type="number" id="edit_duracion" name="duracion_uso" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center mb-2">
                                Normas de Uso
                                <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" onclick="crearInputNormaEdit()">
                                    <i class="fa-solid fa-plus me-1"></i>Añadir Norma
                                </button>
                            </label>
                            <div id="contenedor-normas-edit" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>