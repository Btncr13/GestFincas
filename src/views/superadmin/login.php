<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración Global - GestFincas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/variables.css">
    <style>
        body {
            background-color: var(--bs-secondary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card-custom {
            background: var(--bs-light);
            border: 1px solid var(--color-borde);
            border-top: 6px solid var(--bs-warning); /* Franja superior destacada */
            border-radius: var(--radio-lg);
            box-shadow: var(--sombra-media);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 450px;
        }
    </style>
    <script>
        const savedTheme = localStorage.getItem('gestfincas-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

<main class="main-wrapper">
<div class="login-card-custom text-center mx-3">
    <div class="mb-4 text-center">
        <div class="d-inline-block bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-3 shadow-sm" style="font-size: 0.75rem; letter-spacing: 1px;">
            <i class="fa-solid fa-shield-halved me-1"></i> Zona Superadmin
        </div>
        <h2 class="font-title text-primary fw-bold mb-1">GestFincas</h2>
        <p class="text-muted small">Acceso exclusivo a la administración global</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 text-sm-custom d-flex align-items-center text-start" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?route=superadmin/login" method="POST" class="text-start mt-4">
        <div class="mb-3">
            <label for="email" class="form-label fw-bold text-dark small">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-fondo border-custom text-muted"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" class="form-control bg-fondo border-custom" id="email" name="email" required autofocus placeholder="admin@gestfincas.com">
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label fw-bold text-dark small">Contraseña maestra</label>
            <div class="input-group">
                <span class="input-group-text bg-fondo border-custom text-muted"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control bg-fondo border-custom" id="password" name="password" required placeholder="••••••••">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Autenticarse
        </button>
    </form>
    
    <div class="mt-4 text-center">
        <a href="index.php?route=auth/login" class="text-muted small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al login de usuarios
        </a>
    </div>
</div>
</main>

</body>
</html>