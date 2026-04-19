<div class="modal fade" id="modalCrearEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa-solid fa-building-plus me-2"></i>Nueva Instalación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Nombre del Espacio</label>
                        <input type="text" id="nombre_espacio" class="form-control" placeholder="Ej: Piscina, Pista de Pádel...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Aforo Total</label>
                        <input type="number" id="aforo" class="form-control" placeholder="Capacidad máxima">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Max. Personas por Reserva</label>
                        <input type="number" id="max_personas" class="form-control" placeholder="Límite asistentes">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Hora Apertura</label>
                        <input type="time" id="hora_apertura" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Hora Cierre</label>
                        <input type="time" id="hora_cierre" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Duración Uso (min)</label>
                        <select id="duracion_uso" class="form-select">
                            <option value="30">30 min</option>
                            <option value="60" selected>60 min</option>
                            <option value="90">90 min</option>
                            <option value="120">120 min</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado Inicial</label>
                        <select id="bloqueado" class="form-select">
                            <option value="0">Operativo</option>
                            <option value="1">Bloqueado (Mantenimiento)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Normas del Espacio</label>
                        <textarea id="normas_espacio" class="form-control" rows="3" placeholder="Normas de convivencia..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarEspacio" class="btn btn-primary px-4">Guardar Instalación</button>
            </div>
        </div>
    </div>
</div>