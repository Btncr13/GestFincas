<?php

$titulo_pagina = "Panel del Presidente";

include 'src/views/components/topbar.php';

// === MOCK DE DATOS PARA PRUEBAS ===
$rol = 'presidente'; // Cambiar a 'vecino' para probar permisos
$reunion = [
    'titulo' => 'Junta Ordinaria Anual',
    'estado' => 'Pendiente', // Cambiar a 'Finalizada' para ver el acta
    'fecha' => 'Jueves, 25 de Mayo de 2026',
    'hora' => '19:00',
    'lugar' => 'Sala Comunitaria',
    'enlace' => null,
    'orden_dia' => [
        'Lectura y aprobación del acta anterior.',
        'Aprobación de cuentas del ejercicio 2025.',
        'Renovación de cargos de la Junta Directiva.',
        'Ruegos y preguntas.'
    ],
    'acta_texto' => 'En Berchules, siendo las 19:00h del día 25 de Mayo, se reúne la junta... (Texto de prueba del acta descargable).'
];
// ===================================

$badgeClass = ($reunion['estado'] === 'Finalizada') ? 'bg-success text-white' : 'bg-warning text-dark';
?>

<main class="container py-4 py-md-5">
    
    <!-- Header -->
    <div class="mb-4 d-flex flex-column align-items-start">
        <a href="?route=reuniones" class="btn btn-link text-decoration-none text-muted p-0 mb-3 text-sm-custom hover:text-primary">
            <i class="fa-solid fa-arrow-left me-2"></i> Volver a Reuniones
        </a>
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center w-100 gap-3">
            <h1 class="fw-bold text-dark mb-0" style="font-family: var(--fuente-titulos);"><?= htmlspecialchars($reunion['titulo']) ?></h1>
            <span class="badge <?= $badgeClass ?> px-3 py-2 fs-6 rounded-2 shadow-sm"><?= $reunion['estado'] ?></span>
        </div>
    </div>

    <!-- Zona 1: Detalles principales -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-light border-0 shadow-sm h-100 p-3 d-flex flex-row align-items-center gap-3 rounded-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="fa-regular fa-clock fs-5 text-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 small text-uppercase">Cuándo</h6>
                    <p class="text-muted mb-0 fw-semibold text-sm-custom"><?= $reunion['fecha'] ?> - <?= $reunion['hora'] ?>h</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-0 shadow-sm h-100 p-3 d-flex flex-row align-items-center gap-3 rounded-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-location-dot fs-5 text-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 small text-uppercase">Dónde</h6>
                    <p class="text-muted mb-0 fw-semibold text-sm-custom">
                        <?= htmlspecialchars($reunion['lugar']) ?>
                        <?php if ($reunion['enlace']): ?>
                            <a href="<?= $reunion['enlace'] ?>" target="_blank" class="ms-2 text-decoration-none"><i class="fa-solid fa-link"></i> Unirse</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Zona 2: Orden del Día -->
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary" style="background-color: var(--bs-info);">
        <div class="card-body p-4 p-md-5">
            <h4 class="fw-bold text-dark mb-4" style="font-family: var(--fuente-titulos);">Orden del Día</h4>
            <ol class="text-muted mb-0 fw-medium" style="line-height: 1.8;">
                <?php foreach ($reunion['orden_dia'] as $punto): ?>
                    <li class="mb-2"><?= htmlspecialchars($punto) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>

    <!-- Zona 3: El Acta (Condicional) -->
    <?php if ($reunion['estado'] === 'Finalizada'): ?>
        <div class="card border-0 shadow-sm mb-4 bg-light rounded-3">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark mb-0" style="font-family: var(--fuente-titulos);">Acta de la Reunión</h4>
                    <button class="btn btn-primary btn-brand shadow-sm text-sm-custom fw-semibold">
                        <i class="fa-solid fa-file-pdf me-2"></i> Descargar Acta (PDF)
                    </button>
                </div>
                <div class="p-4 bg-white border rounded-3 text-muted" style="border-color: var(--color-borde);">
                    <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($reunion['acta_texto']) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Zona 4: Acciones del Presidente (Condicional) -->
    <?php if ($rol === 'presidente' && $reunion['estado'] === 'Pendiente'): ?>
        <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 mt-5 pt-4 border-top">
            <button class="btn btn-outline-danger fw-semibold px-4 py-2 rounded-2 text-sm-custom shadow-sm">
                <i class="fa-solid fa-xmark me-2"></i> Cancelar Reunión
            </button>
            <button class="btn btn-brand fw-semibold px-4 py-2 rounded-2 text-sm-custom shadow-sm">
                <i class="fa-solid fa-pen-to-square me-2"></i> Redactar Acta
            </button>
        </div>
    <?php endif; ?>

</main>