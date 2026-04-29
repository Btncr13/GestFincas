<!-- MODAL MODIFICAR VIVIENDA / VECINO -->
<div class="modal fade" id="modalModificarVivienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header border-bottom py-3" style="background-color: var(--bs-primary);">
                <h5 class="modal-title fw-bold text-white" style="font-family: var(--fuente-titulos);">
                    <i class="bi bi-pencil-square me-2"></i>Modificar Vivienda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Apuntamos a la nueva acción del controlador -->
            <form action="index.php?route=miComunidad/modificarViviendaAction" method="POST">
                <div class="modal-body p-4" style="background-color: var(--bs-light); color: var(--bs-dark);">
                    
                    <!-- ID Oculto de la vivienda seleccionada -->
                    <input type="hidden" name="id_vivienda_modificar" id="id_vivienda_modificar">

                    <p class="text-muted small mb-4">Modifica los datos de la vivienda seleccionada. Si deseas reasignar la vivienda, edita su identificador.</p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de la Vivienda</label>
                        <input type="text" name="nombre_vivienda" id="nombre_vivienda_modificar" class="form-control custom-input" required pattern="^Planta \d+-[A-Z0-9]+$" title="Formato: Planta X-PisoLetra">
                        <div class="form-text mt-1" style="color: var(--color-texto); font-size: 0.75rem;">Siga el formato: Planta [Número]-[Piso]</div>
                    </div>

                </div>
                <div class="modal-footer border-0 py-3" style="background-color: var(--color-fondo-formularios);">
                    <button type="button" class="btn btn-secondary fw-semibold text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-white">Guardar Cambios</button>
                </div>
            </form>

        </div>
    </div>
</div><!-- MODAL MODIFICAR VIVIENDA / VECINO -->
<div class="modal fade" id="modalModificarVivienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header border-bottom py-3" style="background-color: var(--bs-primary);">
                <h5 class="modal-title fw-bold text-white" style="font-family: var(--fuente-titulos);">
                    <i class="bi bi-pencil-square me-2"></i>Modificar Vivienda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Apuntamos a la nueva acción del controlador -->
            <form action="index.php?route=miComunidad/modificarViviendaAction" method="POST">
                <div class="modal-body p-4" style="background-color: var(--bs-light); color: var(--bs-dark);">
                    
                    <!-- ID Oculto de la vivienda seleccionada -->
                    <input type="hidden" name="id_vivienda_modificar" id="id_vivienda_modificar">

                    <p class="text-muted small mb-4">Modifica los datos de la vivienda seleccionada. Si deseas reasignar la vivienda, edita su identificador.</p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de la Vivienda</label>
                        <input type="text" name="nombre_vivienda" id="nombre_vivienda_modificar" class="form-control custom-input" required pattern="^Planta \d+-[A-Z0-9]+$" title="Formato: Planta X-PisoLetra">
                        <div class="form-text mt-1" style="color: var(--color-texto); font-size: 0.75rem;">Siga el formato: Planta [Número]-[Piso]</div>
                    </div>

                </div>
                <div class="modal-footer border-0 py-3" style="background-color: var(--color-fondo-formularios);">
                    <button type="button" class="btn btn-secondary fw-semibold text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-white">Guardar Cambios</button>
                </div>
            </form>

        </div>
    </div>
</div>