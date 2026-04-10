<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h4 mb-0">Bienvenido, <?php echo htmlspecialchars($_SESSION['vivienda']['nombre_vivienda']); ?></h2>
                        <p class="text-muted small">Panel de gestión de tu comunidad</p>
                    </div>
                    <a href="index.php?route=auth/logout" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-sm-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <span class="d-block h5 mb-0">0</span>
                            <span class="text-muted small">Incidencias</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>