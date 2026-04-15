<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> 
        <div class="modal-content border-0 shadow-lg">

            <!-- Header -->
            <div class="modal-header border-0 border-start border-4 border-primary">
                <h5 class="modal-title fw-bold text-dark" id="modalReservaLabel" style="font-family: var(--fuente-titulos);">
                    <i class="fa-solid fa-calendar-check me-2 text-primary"></i>
                    Crear Reserva
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Espacio -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Espacio</label>
                    <select id="selectEspacio" class="form-select shadow-sm">
                        <option value="">Selecciona un espacio...</option>
                    </select>
                </div>

                <!-- Tramo horario -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Tramo horario</label>
                    <select id="selectTramo" class="form-select shadow-sm" disabled>
                        <option value="">Selecciona un tramo...</option>
                    </select>
                </div>

                <!-- Número de personas -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Número de personas</label>
                    <select id="selectPersonas" class="form-select shadow-sm" disabled>
                        <option value="">Selecciona cantidad...</option>
                    </select>
                </div>

                <!-- Normas -->
                <div class="mt-4 p-3 rounded shadow-sm" style="background-color: var(--bs-light);">
                    <h6 class="fw-bold text-dark mb-2" style="font-family: var(--fuente-titulos);">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>Normas del espacio
                    </h6>
                    <div id="normas" class="text-muted small">
                        <p class="mb-0">Selecciona un espacio para ver sus normas.</p>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" id="btnCrearReserva" class="btn btn-brand fw-semibold shadow-sm" disabled>
                    <i class="fa-solid fa-check me-2"></i>Crear Reserva
                </button>
            </div>

        </div>
    </div>
</div>