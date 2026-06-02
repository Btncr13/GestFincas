<?php include 'src/views/components/topbar.php'; ?>

<?php
// =========================================================================
// PREPARACIÓN DE DATOS EN PHP (Evita tener que procesar todo en JavaScript)
// =========================================================================
$ahora = time();
$proximas = [];
$pasadas = [];
$reunionesData = $reunionesData ?? [];

foreach ($reunionesData as $r) {
    $fechaHoraStr = $r['fecha'] . ' ' . ($r['hora'] ?? '00:00:00');
    $fechaHora = strtotime($fechaHoraStr);
    if ($r['estado'] !== 'finalizada' && $fechaHora >= $ahora) {
        $proximas[] = $r;
    } else {
        $pasadas[] = $r;
    }
}

// Ordenar: Próximas de más cercana a más lejana, Pasadas de más reciente a más antigua
usort($proximas, function($a, $b) { return strtotime($a['fecha']) - strtotime($b['fecha']); });
usort($pasadas, function($a, $b) { return strtotime($b['fecha']) - strtotime($a['fecha']); });

$totalProximas = count($proximas);
$totalPasadas = count($pasadas);

$confGlobal = 0;
$pendGlobal = 0;
if ($totalProximas > 0) {
    foreach ($proximas[0]['asistencias'] as $a) {
        if ($a['confirmacion'] === 'confirmada') $confGlobal++;
        if ($a['confirmacion'] === 'pendiente') $pendGlobal++;
    }
}
?>

<div class="container-fluid p-0">
    <div class="row flex-nowrap m-0">

        <?php include 'src/views/components/sidebar.php'; ?>

        <main class="col-12 col-md-9 col-lg-10 ms-auto px-2 px-md-4 pt-3 pt-md-4 pb-5 d-flex flex-column min-vh-100">

            <!-- Contenedor global de la mini-aplicación de Reuniones -->
            <div class="container-fluid p-0 position-relative" id="appReuniones">

                <!-- TOAST CONTAINER -->
                <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
                    <div id="reunionesToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i>
                                <span id="toastMensaje">Acción completada</span>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <!-- VISTA 1: LISTADO PRINCIPAL -->
                <div id="vista-lista">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h2 class="fw-bold mb-1 font-title">Reuniones</h2>
                            <p class="text-muted small mb-0">Juntas y reuniones de la comunidad</p>
                        </div>
                        <?php if ($rol === 'presidente'): ?>
                            <button type="button" class="btn btn-success text-white fw-semibold shadow-sm d-flex align-items-center gap-2" onclick="app.abrirFormularioCrear()">
                                <i class="fa-solid fa-plus"></i> Convocar Reunión
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Resumen (Cards) -->
                    <div class="row row-cols-2 g-2 mb-4" id="cards-resumen">
                        <div class="col"><div class="card shadow-sm border-0 h-100"><div class="card-body p-2 d-flex align-items-center gap-2">
                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px; background-color: rgba(92,178,68,0.1);"><i class="bi bi-check-circle" style="color: var(--bs-success); font-size:20px;"></i></div>
                            <div class="lh-1"><div style="font-family: var(--fuente-titulos); font-weight:700; font-size:20px;"><?= $confGlobal ?></div><small style="font-size:10px; color:var(--color-texto);">Confirmadas</small></div>
                        </div></div></div>
                        <div class="col"><div class="card shadow-sm border-0 h-100"><div class="card-body p-2 d-flex align-items-center gap-2">
                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px; background-color: rgba(164,30,52,0.1);"><i class="bi bi-question-circle" style="color: var(--bs-danger); font-size:20px;"></i></div>
                            <div class="lh-1"><div style="font-family: var(--fuente-titulos); font-weight:700; font-size:20px;"><?= $pendGlobal ?></div><small style="font-size:10px; color:var(--color-texto);">Pendientes</small></div>
                        </div></div></div>
                    </div>

                    <!-- Tabs -->
                    <div class="d-flex mb-3 p-1 nav-tabs-custom" style="background-color: var(--color-fondo-formularios); border-radius: var(--radio-lg);">
                        <button id="btn-tab-proximas" class="btn flex-fill text-center rounded-2 py-2 fw-semibold active" style="font-size: 14px; transition: all 0.2s;" onclick="app.switchTab('proximas')">Próximas (<?= $totalProximas ?>)</button>
                        <button id="btn-tab-pasadas" class="btn flex-fill text-center rounded-2 py-2 fw-semibold text-muted" style="font-size: 14px; transition: all 0.2s;" onclick="app.switchTab('pasadas')">Pasadas (<?= $totalPasadas ?>)</button>
                    </div>

                    <!-- Contenedor Listas -->
                    <div id="lista-proximas" class="d-flex flex-column gap-3">
                        <?php if (empty($proximas)): ?>
                            <div class="text-center py-5 rounded-3 shadow-sm" style="background-color: var(--bs-light);">
                                <i class="bi bi-calendar-check text-muted" style="font-size: 48px;"></i>
                                <p class="mt-2 mb-1 text-muted" style="font-size:14px;">No hay reuniones próximas convocadas</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($proximas as $r): ?>
                                <?php 
                                    $total = count($r['asistencias']);
                                    $confs = count(array_filter($r['asistencias'], fn($a) => $a['confirmacion'] === 'confirmada'));
                                    $pct = $total > 0 ? round(($confs / $total) * 100) : 0;
                                    $diasRestantes = ceil((strtotime($r['fecha']) - $ahora) / 86400);
                                    $fechaHora = strtotime($r['fecha'] . ' ' . ($r['hora'] ?? '00:00'));
                                    
                                    // Renderizador de Etiqueta (Badge)
                                    $badge = '<span class="badge" style="background-color: var(--bs-warning); font-size:12px;">Convocada</span>';
                                    if ($r['estado'] === 'en_curso') $badge = '<span class="badge" style="background-color: var(--bs-success); font-size:12px;">En Curso</span>';
                                ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid var(--bs-warning) !important;">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span style="font-size:14px; font-weight:600; color:var(--bs-dark);"><?= htmlspecialchars($r['titulo']) ?></span>
                                                    <?= $badge ?>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3" style="font-size:12px; color:var(--color-texto);">
                                                    <span><i class="bi bi-clock"></i> <?= date('d/m/Y', strtotime($r['fecha'])) ?> a las <?= $r['hora'] ?></span>
                                                    <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($r['lugar']) ?></span>
                                                </div>
                                            </div>
                                            <span style="font-size:12px; font-weight:600; color:var(--bs-primary); background-color: rgba(34,28,53,0.05); padding:2px 8px; border-radius:4px; height:fit-content;"><?= $diasRestantes ?> días</span>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center mt-3 mb-2" style="font-size:12px;">
                                            <i class="bi bi-people" style="color:var(--color-texto); font-size:16px;"></i>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between mb-1"><span style="color:var(--color-texto);">Asistencia: <?= $confs ?>/<?= $total ?></span><span style="font-weight:600;"><?= $pct ?>%</span></div>
                                                <div class="progress" style="height:6px; background-color:var(--color-fondo-formularios);"><div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div></div>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-1" style="font-size:12px; font-weight:500; color:var(--bs-primary);" onclick="app.toggleAgenda('<?= $r['id'] ?>')">
                                                <i class="bi bi-chevron-down" id="icon-agenda-<?= $r['id'] ?>"></i> Orden del día (<?= count($r['ordenDelDia']) ?> puntos)
                                            </button>
                                            <div id="agenda-<?= $r['id'] ?>" class="d-none mt-2 ps-2" style="border-left: 2px solid rgba(34,28,53,0.2); font-size:12px; color:var(--color-texto);">
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($r['ordenDelDia'] as $i => $o): ?>
                                                        <li><?= $i+1 ?>. <?= htmlspecialchars($o) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3 pt-2">
                                            <button class="btn btn-sm btn-outline-primary fw-semibold shadow-sm d-flex justify-content-center align-items-center gap-2" style="flex-grow:1; max-width: 200px;" onclick="app.renderDetalle('<?= $r['id'] ?>')"><i class="bi bi-eye"></i> Ver detalle</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div id="lista-pasadas" class="d-flex flex-column gap-3 d-none">
                        <?php if (empty($pasadas)): ?>
                            <div class="text-center py-5 rounded-3 shadow-sm" style="background-color: var(--bs-light);">
                                <i class="bi bi-file-earmark-text text-muted" style="font-size: 48px;"></i>
                                <p class="mt-2 text-muted" style="font-size:14px;">No hay reuniones pasadas registradas</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pasadas as $r): ?>
                                <?php 
                                    $confs = count(array_filter($r['asistencias'], fn($a) => $a['confirmacion'] === 'confirmada'));
                                    $badge = '<span class="badge border text-muted bg-transparent" style="font-size:12px;">Realizada</span>';
                                ?>
                                <div class="card shadow-sm border-0 module-card" style="border-left: 4px solid #d1d5db !important; cursor:pointer;" onclick="app.renderDetalle('<?= $r['id'] ?>')">
                                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                <span style="font-size:14px; font-weight:600; color:var(--bs-dark);"><?= htmlspecialchars($r['titulo']) ?></span>
                                                <?= $badge ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3" style="font-size:12px; color:var(--color-texto);">
                                                <span><i class="bi bi-clock"></i> <?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                                                <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($r['lugar']) ?></span>
                                                <span><i class="bi bi-people"></i> <?= $confs ?>/<?= count($r['asistencias']) ?> asistentes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VISTA 2: DETALLE -->
                <div id="vista-detalle" class="d-none">
                    <button class="btn btn-link text-muted text-decoration-none p-0 mb-3 d-flex align-items-center gap-1 fw-semibold" onclick="app.showView('vista-lista')">
                        <i class="bi bi-arrow-left"></i> Volver
                    </button>
                    <div id="detalle-content">
                        <!-- Generamos todos los detalles ocultos en PHP nativo -->
                        <?php foreach ($reunionesData as $r): ?>
                            <?php
                                $diasRestantes = ceil((strtotime($r['fecha']) - $ahora) / 86400);
                                $total = count($r['asistencias']);
                                $confs = count(array_filter($r['asistencias'], fn($a) => $a['confirmacion'] === 'confirmada'));
                                $rechs = count(array_filter($r['asistencias'], fn($a) => $a['confirmacion'] === 'rechazada'));
                                $pends = count(array_filter($r['asistencias'], fn($a) => $a['confirmacion'] === 'pendiente'));
                                
                                $ptConf = $total > 0 ? ($confs / $total) * 100 : 0;
                                $ptRech = $total > 0 ? ($rechs / $total) * 100 : 0;
                                $ptPend = $total > 0 ? ($pends / $total) * 100 : 0;
                                
                                $fechaHora = strtotime($r['fecha'] . ' ' . ($r['hora'] ?? '00:00'));
                                $isPasada = ($r['estado'] === 'finalizada' || $fechaHora < $ahora);
                                
                                $badge = '<span class="badge border text-muted bg-transparent" style="font-size:12px;">Realizada</span>';
                                if (!$isPasada) {
                                    $badge = ($r['estado'] === 'en_curso') 
                                        ? '<span class="badge" style="background-color: var(--bs-success); font-size:12px;">En Curso</span>' 
                                        : '<span class="badge" style="background-color: var(--bs-warning); font-size:12px;">Convocada</span>';
                                }

                                // Buscar mi respuesta
                                $miAsistencia = null;
                                foreach ($r['asistencias'] as $a) {
                                    if ($a['id_vivienda'] == $id_vivienda) {
                                        $miAsistencia = $a;
                                        break;
                                    }
                                }
                            ?>
                            <div id="detalle-reunion-<?= $r['id'] ?>" class="d-none detalle-reunion-container">
                                <div class="mb-4">
                                    <h1 class="mb-2" style="font-family: var(--fuente-titulos); font-size: 20px; font-weight: 700; color: var(--bs-dark);"><?= htmlspecialchars($r['titulo']) ?></h1>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <?= $badge ?>
                                        <?php if (!$isPasada): ?>
                                            <span style="font-size:12px; color:var(--color-texto);"><i class="bi bi-clock"></i> Faltan <?= $diasRestantes ?> días</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!$isPasada): ?>
                                    <?php
                                        $txtEstado = '<span class="text-muted fw-bold">Pendiente de respuesta</span>';
                                        if ($miAsistencia) {
                                            if ($miAsistencia['confirmacion'] === 'confirmada') $txtEstado = '<span class="text-success fw-bold">Sí, asistiré</span>';
                                            if ($miAsistencia['confirmacion'] === 'rechazada') $txtEstado = '<span class="text-danger fw-bold">No asistiré</span>';
                                        }
                                    ?>
                                    <div class="card shadow-sm border-0 mb-4" style="background-color: var(--bs-light); border-left: 4px solid var(--bs-primary) !important;">
                                        <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                                            <div>
                                                <h4 class="mb-1" style="font-family: var(--fuente-titulos); font-size: 16px; font-weight: 700;">Tu asistencia</h4>
                                                <p class="mb-0 text-muted" style="font-size: 14px;">Estado actual: <?= $txtEstado ?></p>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-outline-danger fw-semibold shadow-sm" onclick="app.enviarAsistencia('<?= $r['id'] ?>', 'rechazada')">No asistiré</button>
                                                <button class="btn btn-success text-white fw-semibold shadow-sm" onclick="app.enviarAsistencia('<?= $r['id'] ?>', 'confirmada')">Sí, asistiré</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- INFO -->
                                <div class="card shadow-sm border-0 mb-4" style="border-radius: var(--radio-lg);">
                                    <div class="card-body p-4">
                                        <h3 class="mb-3" style="font-family: var(--fuente-titulos); font-size: 16px; font-weight: 700; color: var(--bs-dark);">Información de la Reunión</h3>
                                        <p style="font-size: 14px; color: var(--color-texto); margin-bottom: 1rem; white-space: pre-wrap;"><?= htmlspecialchars($r['descripcion']) ?></p>
                                        
                                        <div class="row row-cols-1 row-cols-sm-2 g-3">
                                            <div class="col d-flex gap-2 align-items-center">
                                                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background-color:rgba(34,28,53,0.1);"><i class="bi bi-calendar3" style="color:var(--bs-primary);font-size:14px;"></i></div>
                                                <div class="lh-1"><small style="font-size:10px; color:var(--color-texto);">Fecha</small><div style="font-size:14px; font-weight:500; color:var(--bs-dark);"><?= date('d/m/Y', strtotime($r['fecha'])) ?></div></div>
                                            </div>
                                            <div class="col d-flex gap-2 align-items-center">
                                                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background-color:rgba(34,28,53,0.1);"><i class="bi bi-clock" style="color:var(--bs-primary);font-size:14px;"></i></div>
                                                <div class="lh-1"><small style="font-size:10px; color:var(--color-texto);">Hora</small><div style="font-size:14px; font-weight:500; color:var(--bs-dark);"><?= $r['hora'] ?>h</div></div>
                                            </div>
                                            <div class="col d-flex gap-2 align-items-center">
                                                <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background-color:rgba(34,28,53,0.1);"><i class="bi bi-geo-alt" style="color:var(--bs-primary);font-size:14px;"></i></div>
                                                <div class="lh-1"><small style="font-size:10px; color:var(--color-texto);">Lugar</small><div style="font-size:14px; font-weight:500; color:var(--bs-dark);"><?= htmlspecialchars($r['lugar']) ?></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ORDEN DEL DIA -->
                                <div class="card shadow-sm border-0 mb-4" style="border-radius: var(--radio-lg);">
                                    <div class="card-body p-4">
                                        <h3 class="mb-3 d-flex align-items-center gap-2" style="font-family: var(--fuente-titulos); font-size: 16px; font-weight: 700; color: var(--bs-dark);">Orden del Día <span style="font-size:12px; font-weight:400; color:var(--color-texto);"><?= count($r['ordenDelDia']) ?> puntos</span></h3>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <?php foreach ($r['ordenDelDia'] as $i => $o): ?>
                                                <li class="d-flex gap-2 align-items-start">
                                                    <div class="rounded-circle text-white d-flex justify-content-center align-items-center flex-shrink-0" style="width:24px; height:24px; background-color:var(--bs-primary); font-size:12px; font-family:var(--fuente-titulos); font-weight:700;"><?= $i+1 ?></div>
                                                    <div style="font-size:14px; color:var(--bs-dark); padding-top:2px;"><?= htmlspecialchars($o) ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3 pt-2">
                                    <?php if (!empty($r['pdf_orden_dia'])): ?>
                                    <a href="<?= htmlspecialchars($r['pdf_orden_dia']) ?>" target="_blank" class="btn d-flex align-items-center justify-content-center gap-2 flex-grow-1" style="background-color: var(--bs-primary); color: white; min-height: 44px; border-radius: var(--radio-lg); font-size: 14px; font-weight: 500;">
                                        <i class="bi bi-file-earmark-pdf fs-6"></i> Descargar Documento (PDF)
                                    </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- ASISTENCIAS -->
                                <div class="card shadow-sm border-0 mb-4" style="border-radius: var(--radio-lg);">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
                                            <div>
                                                <h3 class="mb-0" style="font-family: var(--fuente-titulos); font-size: 16px; font-weight: 700; color: var(--bs-dark);">Confirmación de Asistencia</h3>
                                                <span style="font-size:12px; color:var(--color-texto);"><?= $total ?> viviendas convocadas</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Barra tricolor -->
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between mb-1" style="font-size:12px; color:var(--color-texto);">
                                                <span><strong style="color:var(--bs-dark);"><?= round($ptConf) ?>%</strong> confirmado</span>
                                                <span><?= $confs ?>/<?= $total ?> viviendas</span>
                                            </div>
                                            <div class="progress" style="height:12px; border-radius:10px; background-color:var(--color-fondo-formularios);">
                                                <div class="progress-bar bg-success" style="width: <?= $ptConf ?>%"></div>
                                                <div class="progress-bar bg-danger" style="width: <?= $ptRech ?>%"></div>
                                                <div class="progress-bar bg-secondary" style="width: <?= $ptPend ?>%"></div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:12px; color:var(--color-texto);">
                                                <div class="d-flex align-items-center gap-1"><span class="rounded-circle bg-success" style="width:10px;height:10px;"></span> Confirmadas (<?= $confs ?>)</div>
                                                <div class="d-flex align-items-center gap-1"><span class="rounded-circle bg-danger" style="width:10px;height:10px;"></span> No asisten (<?= $rechs ?>)</div>
                                                <div class="d-flex align-items-center gap-1"><span class="rounded-circle bg-secondary" style="width:10px;height:10px;"></span> Pendientes (<?= $pends ?>)</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tabla Listado -->
                                        <div class="border rounded-2 overflow-hidden">
                                            <div class="d-flex p-2" style="background-color: var(--color-fondo-formularios); font-size:12px; font-weight:600; color:var(--color-texto);">
                                                <div class="flex-grow-1 px-2">Vivienda</div>
                                                <div class="text-center px-2" style="width:80px;">Estado</div>
                                                <div class="text-end px-2" style="width:90px;">Fecha</div>
                                            </div>
                                            <?php foreach ($r['asistencias'] as $i => $a): ?>
                                                <?php
                                                if($a['confirmacion'] === 'confirmada') { $icon='bi-person-check'; $bg='rgba(92,178,68,0.1)'; $txtC='var(--bs-success)'; $strEst='Asiste'; $bcolor='var(--bs-success)';}
                                                elseif($a['confirmacion'] === 'rechazada') { $icon='bi-person-x'; $bg='rgba(164,30,52,0.1)'; $txtC='var(--bs-danger)'; $strEst='No asiste'; $bcolor='var(--bs-danger)';}
                                                else { $icon='bi-person-dash'; $bg='var(--color-fondo-formularios)'; $txtC='var(--color-texto)'; $strEst='Pendiente'; $bcolor='var(--color-texto)';}
                                                
                                                $rowBg = $i % 2 !== 0 ? 'var(--bs-light)' : 'var(--bs-secondary)';
                                                $fresp = !empty($a['fecha_respuesta']) ? date('d/m/Y', strtotime($a['fecha_respuesta'])) : '-';
                                                ?>
                                                <div class="d-flex align-items-center p-2 border-top" style="background-color:<?= $rowBg ?>;">
                                                    <div class="flex-grow-1 px-2 d-flex align-items-center gap-2">
                                                        <div class="rounded-circle d-flex justify-content-center align-items-center flex-shrink-0" style="width:28px; height:28px; background-color:<?= $bg ?>; color:<?= $txtC ?>;">
                                                            <i class="bi <?= $icon ?>"></i>
                                                        </div>
                                                        <div class="lh-1">
                                                            <div style="font-size:14px; font-weight:500; color:var(--bs-dark);"><?= htmlspecialchars($a['piso']) ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center px-2" style="width:80px;">
                                                        <span style="background-color:<?= $bg ?>; color:<?= $bcolor ?>; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:500;"><?= $strEst ?></span>
                                                    </div>
                                                    <div class="text-end px-2" style="width:90px; font-size:12px; color:var(--color-texto);"><?= $fresp ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <?php if ($rol === 'presidente'): ?>
                                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                            <button class="btn btn-outline-secondary shadow-sm flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2" onclick="app.abrirFormularioEditar('<?= $r['id'] ?>')"><i class="bi bi-pencil"></i> Editar</button>
                                            <button class="btn btn-outline-danger shadow-sm flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2" onclick="app.eliminarReunion('<?= $r['id'] ?>')"><i class="bi bi-trash"></i> Eliminar</button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VISTA 3: FORMULARIO -->
                <?php if ($rol === 'presidente'): ?>
                    <div id="vista-formulario" class="d-none">
                        <button class="btn btn-link text-muted text-decoration-none p-0 mb-3 d-flex align-items-center gap-1 fw-semibold" onclick="app.showView('vista-lista')">
                            <i class="bi bi-arrow-left"></i> Volver
                        </button>

                        <h2 id="form-titulo-vista" class="fw-bold mb-1 font-title">Convocar Nueva Reunión</h2>
                        <p id="form-desc-vista" class="text-muted small mb-4">Completa los datos para convocar una junta o reunión</p>

                        <div class="card shadow-sm border-0" style="border-radius: var(--radio-lg);">
                            <div class="card-body p-4">
                                <form id="formReunion" onsubmit="app.crearReunion(event)">
                                    <input type="hidden" id="form-id">

                                    <div class="mb-3">
                                        <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Título de la Reunión *</label>
                                        <input type="text" class="form-control custom-input" id="form-titulo" placeholder="Ej: Junta General Ordinaria 2026" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Descripción</label>
                                        <textarea class="form-control custom-input" id="form-desc" rows="3" placeholder="Breve descripción del objetivo de la reunión..."></textarea>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Fecha *</label>
                                            <input type="date" class="form-control custom-input" id="form-fecha" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Hora *</label>
                                            <input type="time" class="form-control custom-input" id="form-hora" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Lugar *</label>
                                        <input type="text" class="form-control custom-input" id="form-lugar" placeholder="Ej: Salón de Actos" required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Orden del Día (una línea por punto)</label>
                                        <textarea class="form-control custom-input" id="form-orden" rows="5" placeholder="1. Lectura y aprobación del acta anterior&#10;2. Aprobación de cuentas&#10;3. Ruegos y preguntas"></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" style="font-size: 14px; font-weight: 500; color: var(--bs-dark);">Documento Adjunto (PDF Opcional)</label>
                                        <input type="file" class="form-control custom-input" id="form-pdf" accept=".pdf">
                                    </div>

                                    <button type="submit" id="form-btn-submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold shadow-sm" style="min-height: 44px; border-radius: var(--radio-lg);">
                                        <i class="bi bi-calendar-check"></i> Convocar Reunión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

<script>
    // 🟢 INYECCIÓN DE DATOS PHP -> JAVASCRIPT 🟢
    let reunionesDB = <?= json_encode($reunionesData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const userRol = '<?= $rol ?>';
    const userIdVivienda = <?= $id_vivienda ?? 'null' ?>;
    const idComunidad = <?= json_encode($id_comunidad ?? null) ?>;

    // 🟢 LÓGICA PRINCIPAL (VANILLA JS) 🟢
    const app = {
        init: function() {
            const today = new Date().toISOString().split('T')[0];

            const dateInput = document.getElementById('form-fecha');
            if (dateInput) {
                dateInput.setAttribute('min', today);
            }
        },

        showView: function(viewId) {
            document.getElementById('vista-lista').classList.add('d-none');
            document.getElementById('vista-detalle').classList.add('d-none');

            const formView = document.getElementById('vista-formulario');
            if (formView) formView.classList.add('d-none');

            document.getElementById(viewId).classList.remove('d-none');
            window.scrollTo(0, 0);
        },

        abrirFormularioCrear: function() {
            document.getElementById('formReunion').reset();
            document.getElementById('form-id').value = '';
            document.getElementById('form-titulo-vista').innerText = 'Convocar Nueva Reunión';
            document.getElementById('form-desc-vista').innerText = 'Completa los datos para convocar una junta o reunión';
            document.getElementById('form-btn-submit').innerHTML = '<i class="bi bi-calendar-check"></i> Convocar Reunión';
            this.showView('vista-formulario');
        },

        abrirFormularioEditar: function(id) {
            const r = reunionesDB.find(x => x.id == id);
            if (!r) return;

            document.getElementById('form-id').value = r.id;
            document.getElementById('form-titulo').value = r.titulo;
            document.getElementById('form-desc').value = r.descripcion;
            document.getElementById('form-fecha').value = r.fecha;
            document.getElementById('form-hora').value = r.hora;
            document.getElementById('form-lugar').value = r.lugar;
            document.getElementById('form-orden').value = r.ordenDelDia.join('\n');

            document.getElementById('form-titulo-vista').innerText = 'Editar Reunión';
            document.getElementById('form-desc-vista').innerText = 'Modifica los datos de esta convocatoria';
            document.getElementById('form-btn-submit').innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
            this.showView('vista-formulario');
        },

        switchTab: function(tabName) {
            const isProx = tabName === 'proximas';
            const btnProx = document.getElementById('btn-tab-proximas');
            const btnPas = document.getElementById('btn-tab-pasadas');

            // Toggle de clases para los botones
            btnProx.classList.toggle('active', isProx);
            btnProx.classList.toggle('text-muted', !isProx);
            
            btnPas.classList.toggle('active', !isProx);
            btnPas.classList.toggle('text-muted', isProx);
            
            // Mostrar contenedores
            document.getElementById('lista-proximas').classList.toggle('d-none', !isProx);
            document.getElementById('lista-pasadas').classList.toggle('d-none', isProx);
        },

        showToast: function(msg, type = 'success') {
            const toastEl = document.getElementById('reunionesToast');
            document.getElementById('toastMensaje').innerText = msg;
            toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;
            const t = new bootstrap.Toast(toastEl, {
                delay: 3000
            });
            t.show();
        },

        toggleAgenda: function(id) {
            const el = document.getElementById(`agenda-${id}`);
            const icon = document.getElementById(`icon-agenda-${id}`);
            if (el.classList.contains('d-none')) {
                el.classList.remove('d-none');
                icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
            } else {
                el.classList.add('d-none');
                icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
            }
        },

        renderDetalle: function(id) {
            // Ocultamos todos los contenedores de detalles y mostramos solo el seleccionado
            document.querySelectorAll('.detalle-reunion-container').forEach(el => el.classList.add('d-none'));
            document.getElementById('detalle-reunion-' + id).classList.remove('d-none');
            this.showView('vista-detalle');
        },

        // 🟢 FUNCIONES AJAX (CONEXIÓN AL CONTROLADOR) 🟢
        crearReunion: async function(e) {
            e.preventDefault();

            const id_reunion = document.getElementById('form-id').value;
            const titulo = document.getElementById('form-titulo').value;
            const desc = document.getElementById('form-desc').value;
            const fecha = document.getElementById('form-fecha').value;
            const hora = document.getElementById('form-hora').value;
            const lugar = document.getElementById('form-lugar').value;
            const orden = document.getElementById('form-orden').value.split('\n').filter(o => o.trim() !== '');

            const formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('descripcion', desc);
            formData.append('fecha', fecha);
            formData.append('hora', hora);
            formData.append('lugar', lugar);
            formData.append('orden_del_dia', JSON.stringify(orden));

            // CAPTURAR EL PDF (AHORA SÍ, EL ARCHIVO FÍSICO)
            const pdfInput = document.getElementById('form-pdf');
            if (pdfInput && pdfInput.files.length > 0) {
                
                // Usamos .item(0) o  para extraer el archivo real de la lista
                const archivoReal = pdfInput.files.item(0);
                
                // Chivato para la consola
                console.log("Archivo capturado listo para enviar:", archivoReal);
                
                // Lo inyectamos en el FormData
                formData.append('pdf_orden_dia', archivoReal);
            }

            if (id_reunion) formData.append('id_reunion', id_reunion);

            const endPoint = id_reunion ? 'index.php?route=reunion/editarReunionAction' : 'index.php?route=reunion/crearReunionAction';

            try {
                const response = await fetch(endPoint, {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error("El servidor no devolvió JSON. Respuesta íntegra de PHP:", responseText);
                    this.showToast('Error del servidor. Pulsa F12 y revisa la pestaña Consola', 'danger');
                    return;
                }

                if (data.success) {
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Error en la operación', 'danger');
                }
            } catch (error) {
                this.showToast('Error de conexión con el servidor', 'danger');
            }
        },

        eliminarReunion: async function(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta reunión de forma permanente? Se borrarán también todas las asistencias registradas.')) return;

            const formData = new FormData();
            formData.append('id_reunion', id);

            try {
                const response = await fetch('index.php?route=reunion/eliminarReunionAction', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                const data = JSON.parse(responseText);

                if (data.success) {
                    this.showToast('Reunión eliminada', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('Error al eliminar', 'danger');
                }
            } catch (error) {
                this.showToast('Error de conexión', 'danger');
            }
        },

        enviarAsistencia: async function(idReunion, confirmacion) {
            const formData = new FormData();
            formData.append('id_reunion', idReunion);
            formData.append('confirmacion', confirmacion);

            try {
                const response = await fetch('index.php?route=reunion/confirmarAsistenciaAction', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                const data = JSON.parse(responseText);

                if (data.success) {
                    this.showToast('Asistencia actualizada', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast(data.message || 'Error al actualizar', 'danger');
                }
            } catch (error) {
                this.showToast('Error de conexión', 'danger');
            }
        }
    };

    // Arrancamos la lógica al cargar el DOM
    document.addEventListener('DOMContentLoaded', () => app.init());
</script>