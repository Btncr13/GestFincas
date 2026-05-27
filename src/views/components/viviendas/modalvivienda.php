<!-- MODAL CREAR VIVIENDA -->
<div class="modal fade" id="modalCrearVivienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" style="font-family: var(--fuente-titulos);">Alta de Nueva Vivienda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=miComunidad/crearViviendaAction" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Comunidad</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($nombreComunidad) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vivienda</label>
                        <input type="text" id="vivienda" name="vivienda" class="form-control custom-input" placeholder="Ej: Planta 2-1 o Planta 2-B" required pattern="^Planta \d+-[0-9A-Z]$" title="Formato: Planta [Número]-[Número o LETRA MAYÚSCULA]">
                        <div id="viviendaFeedback" class="invalid-feedback">El formato debe ser 'Planta [Número]-[Número o LETRA MAYÚSCULA]' (ej: Planta 2-1 o Planta 2-B).</div>
                        <div class="form-text mt-1" style="font-size: 0.75rem;">Siga el formato: Planta [Número]-[Número o LETRA MAYÚSCULA]</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email del vecino</label>
                        <input type="email" name="email_vecino" class="form-control custom-input" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Código de Registro</label>
                        <div class="input-group">
                            <input type="text" id="codigo_vivienda" name="codigo_vivienda" class="form-control custom-input fw-mono" readonly required placeholder="Haz clic en generar">
                            <button class="btn btn-outline-primary" type="button" onclick="generarCodigoRegistro()">
                                <i class="bi bi-magic me-1"></i> Generar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Confirmar Vivienda</button>
                </div>
            </form>
        </div>
    </div>
</div>