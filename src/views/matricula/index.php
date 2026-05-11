<?php include 'src/views/components/topbar.php'; ?>
<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">
        <?php include 'src/views/components/sidebar.php'; ?>
        <main class="col-12 col-md-9 col-lg-10 ms-auto px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold"><i class="fa-solid fa-car-side me-2 text-primary"></i>Gestión de Parking</h2>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalMatricula">
                    <i class="bi bi-plus-lg me-2"></i>Nueva Matrícula
                </button>
            </div>

            <?php if (isset($_SESSION['parking_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['parking_success'];
                    unset($_SESSION['parking_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['parking_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['parking_error'];
                    unset($_SESSION['parking_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- HABITUALES -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-primary">Uso Habitual (<?= $cantHabitual ?>/4)</h5>
                            <span class="badge bg-primary rounded-pill">Máx. 4</span>
                        </div>
                        <div class="card-body">
                            <?php if ($cantHabitual === 0): ?>
                                <p class="text-muted small">No hay matrículas habituales registradas.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($matriculas as $m): if ($m['uso_matricula'] === 'habitual'): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <span class="fw-bold fs-5 d-block"><?= htmlspecialchars($m['matricula']) ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($m['marca_vehículo']) ?></small>
                                                </div>
                                                <form action="index.php?route=matricula/delete" method="POST" onsubmit="return confirm('¿Eliminar esta matrícula?')">
                                                    <input type="hidden" name="id_matricula" value="<?= $m['id_matricula'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- INVITADOS -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-success">Invitados (<?= $cantInvitado ?>/2)</h5>
                            <span class="badge bg-success rounded-pill">Máx. 2</span>
                        </div>
                        <div class="card-body">
                            <?php if ($cantInvitado === 0): ?>
                                <p class="text-muted small">No hay matrículas de invitados registradas.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($matriculas as $m): if ($m['uso_matricula'] === 'invitado'): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <span class="fw-bold fs-5 d-block"><?= htmlspecialchars($m['matricula']) ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($m['marca_vehículo']) ?> - <b><?= htmlspecialchars($m['nombre_invitado'] ?? 'Invitado') ?></b></small>
                                                </div>
                                                <form action="index.php?route=matricula/delete" method="POST" onsubmit="return confirm('¿Eliminar esta matrícula?')">
                                                    <input type="hidden" name="id_matricula" value="<?= $m['id_matricula'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL REGISTRO -->
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
                            <option value="habitual" <?= ($cantHabitual >= 4) ? 'disabled' : '' ?>>Habitual (<?= 4 - $cantHabitual ?> libres)</option>
                            <option value="invitado" <?= ($cantInvitado >= 2) ? 'disabled' : '' ?>>Invitado (<?= 2 - $cantInvitado ?> libres)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matrícula</label>
                        <input type="text" name="matricula" class="form-control" placeholder="1234BBB" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marca/Modelo</label>
                        <input type="text" name="marca" class="form-control" placeholder="Ej: Seat Ibiza Blanco" required>
                    </div>
                    <div id="divInvitado" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Invitado</label>
                            <input type="text" name="nombre_invitado" class="form-control" placeholder="Ej: Juan Pérez">
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

<script>
    function toggleInvitado(valor) {
        const div = document.getElementById('divInvitado');
        const input = div.querySelector('input');
        if (valor === 'invitado') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
        }
    }

    // Inicializar el estado del modal si el primer option habilitado es invitado
    document.addEventListener('DOMContentLoaded', () => toggleInvitado(document.getElementById('selectUso').value));
</script>