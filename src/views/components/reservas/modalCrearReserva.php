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

                <!-- Contenedor de errores específico del modal -->
                <div id="modalErrorAlert" class="alert alert-danger d-none mb-3 py-2 small" role="alert"></div>

                <!-- Espacio -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Espacio</label>
                    <!-- Añadido custom-input aquí -->
                    <select name="id_espacio" id="id_espacio" class="form-select custom-input">
                        <option value="">Selecciona un espacio</option>
                        <?php foreach ($espaciosDisponibles as $espacio): ?>
                            <option value="<?= $espacio['id_espacios_comunidad'] ?>">
                                <?= $espacio['nombre_espacio'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fecha de la reserva -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Fecha</label>
                    <!-- Añadido custom-input aquí -->
                    <input type="date" id="inputFecha" class="form-control custom-input shadow-sm">
                </div>


                <!-- Tramo horario -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Tramo horario</label>
                    <!-- Añadido custom-input aquí -->
                    <select id="selectTramo" class="form-select custom-input shadow-sm">
                        <option value="">Selecciona un tramo...</option>
                    </select>
                </div>

                <!-- Número de personas -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Asistentes</label>
                    <!-- Añadido custom-input aquí -->
                    <select id="selectPersonas" class="form-select custom-input shadow-sm">
                        <option value="">Máximo asistentes...</option>
                    </select>
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