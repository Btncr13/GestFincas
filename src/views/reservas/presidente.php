<?php include 'src/views/components/topbar.php'; ?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-0 text-primary fw-bold">Gestión de Reservas</h2>
                    <p class="text-muted small">Panel de Presidente - <?php echo htmlspecialchars($_SESSION['vivienda']['nombre_vivienda'] ?? 'Comunidad'); ?></p>
                </div>
                <a href="index.php?route=auth/panelpresi" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Panel
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <h5 class="h6 fw-bold mb-0">Espacios Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa-solid fa-table-tennis-paddle-ball text-primary me-2"></i> Pista de Pádel
                                    </div>
                                    <span class="badge bg-success rounded-pill">Activa</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa-solid fa-people-roof text-primary me-2"></i> Sala Multiusos
                                    </div>
                                    <span class="badge bg-success rounded-pill">Activa</span>
                                </li>
                            </ul>
                            <button class="btn btn-brand w-100 mt-3 btn-sm">
                                <i class="fa-solid fa-plus"></i> Nuevo Espacio
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="h6 fw-bold mb-0">Próximas Reservas</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Hoy
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Esta semana</a></li>
                                    <li><a class="dropdown-item" href="#">Este mes</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light text-muted text-sm-custom">
                                        <tr>
                                            <th>Vecino / Vivienda</th>
                                            <th>Espacio</th>
                                            <th>Fecha y Hora</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm-custom">
                                        <tr>
                                            <td>
                                                <span class="d-block fw-bold">Juan Pérez</span>
                                                <span class="text-muted small">Piso 4B</span>
                                            </td>
                                            <td>Pista de Pádel</td>
                                            <td>
                                                <span class="d-block">Hoy</span>
                                                <span class="text-muted">18:00 - 19:30</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-danger" title="Cancelar Reserva">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="d-block fw-bold">María López</span>
                                                <span class="text-muted small">Piso 1A</span>
                                            </td>
                                            <td>Sala Multiusos</td>
                                            <td>
                                                <span class="d-block">Mañana</span>
                                                <span class="text-muted">10:00 - 14:00</span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-danger" title="Cancelar Reserva">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>