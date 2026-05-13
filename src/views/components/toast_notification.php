<?php
// Si no hay notificaciones que mostrar, simplemente salimos del componente
if (empty($notificaciones) || !is_array($notificaciones)) {
    return;
}
?>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <?php foreach ($notificaciones as $notif): ?>
        <div id="toast_<?= htmlspecialchars($notif['key']) ?>" 
             class="toast shadow-lg border-0 mb-2 dynamic-toast" 
             data-toast-key="<?= htmlspecialchars($notif['key']) ?>"
             role="alert" aria-live="assertive" aria-atomic="true">
            
            <div class="toast-header text-dark border-0">
                <strong class="me-auto" style="font-family: var(--fuente-titulos);">
                    <span class="badge rounded-circle p-1 me-2 <?= htmlspecialchars($notif['color'] ?? 'bg-secondary') ?> <?= ($notif['color'] === 'bg-danger') ? 'badge-pulse' : '' ?>" 
                          style="width: 10px; height: 10px; display: inline-block;">&nbsp;</span>
                    <?= htmlspecialchars($notif['titulo']) ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close" 
                        onclick="marcarToastComoVisto('<?= htmlspecialchars($notif['key']) ?>', this)"></button>
            </div>
            
            <div class="toast-body bg-white text-dark rounded-bottom">
                <p class="mb-3"><?= htmlspecialchars($notif['mensaje']) ?></p>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-semibold" data-bs-dismiss="toast" 
                            onclick="marcarToastComoVisto('<?= htmlspecialchars($notif['key']) ?>', this)">Entendido</button>
                    <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-outline-primary btn-sm fw-semibold" 
                       onclick="marcarToastComoVisto('<?= htmlspecialchars($notif['key']) ?>', this)">
                        <?= htmlspecialchars($notif['btn_text']) ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Centralizamos la función pasándole la key en lugar de crear múltiples funciones en PHP
    function marcarToastComoVisto(toastKey, elementoClick) {
        const getLocalYYYYMMDD = (d) => {
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(d.getDate()).padStart(2, '0');
        };
        const hoy = getLocalYYYYMMDD(new Date());
        localStorage.setItem('toast_' + toastKey + '_' + hoy, 'true');

        // Ocultar el toast dinámicamente de la vista si se pasa el elemento que activó el evento
        if (elementoClick) {
            const toastEl = elementoClick.closest('.toast');
            if (toastEl) {
                const toastInstance = bootstrap.Toast.getOrCreateInstance(toastEl);
                toastInstance.hide();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const getLocalYYYYMMDD = (d) => {
            return d.getFullYear() + '-' + 
                   String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(d.getDate()).padStart(2, '0');
        };
        const hoy = getLocalYYYYMMDD(new Date());
        
        // Encontramos todos los toasts generados e iteramos sus estados
        const toastElements = document.querySelectorAll('.dynamic-toast');
        toastElements.forEach(function(toastEl) {
            const toastKey = toastEl.getAttribute('data-toast-key');
            if (!localStorage.getItem('toast_' + toastKey + '_' + hoy)) {
                const toast = bootstrap.Toast.getOrCreateInstance(toastEl, {
                    autohide: false // Mantenemos los avisos vitales visibles hasta interaccionar
                });
                toast.show();
            }
        });
    });
</script>