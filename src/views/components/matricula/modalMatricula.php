<?php

/**
 * @var int $cantHabitual
 * @var int $cantInvitado
 */
?>
<div class="modal fade" id="modalMatricula" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?route=matricula/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Registrar Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Uso</label>
                        <select name="uso_matricula" class="form-select" id="selectUso" required onchange="toggleInvitado(this.value)">
                            <option value="habitual" <?= ($cantHabitual >= 4) ? 'disabled' : '' ?>>Habitual</option>
                            <option value="invitado" <?= ($cantInvitado >= 2) ? 'disabled' : '' ?>>Invitado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matrícula</label>
                        <input type="text" name="matricula" id="inputMatricula" class="form-control"
                            placeholder="1234BBB" required oninput="validarMatricula(this)">
                        <div class="invalid-feedback">
                            La matrícula debe tener 4 números y 3 letras mayúsculas (ej: 1234BBB).
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marca/Modelo</label>
                        <input type="text" name="marca" class="form-control" placeholder="Ej: Seat Ibiza Blanco" required>
                    </div>
                    <div id="divInvitado" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Invitado</label>
                            <input type="text" name="nombre_invitado" id="inputNombreInvitado" class="form-control" placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Ingreso al Parking</label>
                            <input type="date" name="fecha_entrada" id="inputFechaEntrada" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Matrícula</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script> </script>