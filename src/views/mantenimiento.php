<?php
// Blindaje para el IDE: Aseguramos que la variable siempre esté definida
$mantenimiento = $mantenimiento ?? [
    'titulo' => 'Mantenimiento en curso',
    'mensaje' => 'La plataforma no está disponible en este momento.',
    'fecha_fin' => date('Y-m-d H:i:s', strtotime('+1 hour'))
];
?>
<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 100px); background-color: var(--bs-secondary);">
    <div class="text-center p-5 shadow-lg rounded-4 bg-white mx-3" style="max-width: 650px; border-top: 6px solid var(--bs-warning);">
        <div class="mb-4">
            <i class="fa-solid fa-person-digging text-warning" style="font-size: 5rem;"></i>
        </div>
        <h1 class="font-title text-dark mb-3 fw-bold"><?= htmlspecialchars($mantenimiento['titulo']) ?></h1>
        <p class="text-muted fs-5 mb-4" style="line-height: 1.6;">
            <?= nl2br(htmlspecialchars($mantenimiento['mensaje'])) ?>
        </p>
        <div class="alert alert-warning d-inline-block shadow-sm mb-0">
            <i class="fa-regular fa-clock me-2"></i>
            <strong>Tiempo estimado de finalización:</strong><br>
            <span class="fs-5"><?= date('d/m/Y \a \l\a\s H:i', strtotime($mantenimiento['fecha_fin'])) ?></span>
        </div>
    </div>
</div>