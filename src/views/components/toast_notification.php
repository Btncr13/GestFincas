<?php
// Valores por defecto de seguridad por si alguna variable no se define antes del include
$toastKey     = $toastKey ?? 'default';
$toastTitle   = $toastTitle ?? 'Notificación';
$toastMsg     = $toastMsg ?? 'Tienes un nuevo aviso.';
$toastLink    = $toastLink ?? '#';
$toastBtnText = $toastBtnText ?? 'Ver detalles';
?>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="toast_<?= $toastKey ?>" class="toast shadow-lg border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white border-0">
            <i class="bi bi-bell-fill me-2"></i>
            <strong class="me-auto" style="font-family: var(--fuente-titulos);"><?= htmlspecialchars($toastTitle) ?></strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close" onclick="marcarToastComoVisto_<?= $toastKey ?>()"></button>
        </div>
        <div class="toast-body bg-white text-dark">
            <p class="mb-3"><?= htmlspecialchars($toastMsg) ?></p>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm fw-semibold" data-bs-dismiss="toast" onclick="marcarToastComoVisto_<?= $toastKey ?>()">Entendido</button>
                <a href="<?= htmlspecialchars($toastLink) ?>" class="btn btn-success btn-sm fw-semibold" onclick="marcarToastComoVisto_<?= $toastKey ?>()"><?= htmlspecialchars($toastBtnText) ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    function marcarToastComoVisto_<?= $toastKey ?>() {
        const hoy = new Date().toISOString().split('T')[0];
        localStorage.setItem('<?= $toastKey ?>_' + hoy, 'true');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const hoy = new Date().toISOString().split('T')[0];
        if (!localStorage.getItem('<?= $toastKey ?>_' + hoy)) {
            const toastEl = document.getElementById('toast_<?= $toastKey ?>');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl, { autohide: false });
                toast.show();
            }
        }
    });
</script>