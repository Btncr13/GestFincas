<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración Global - GestFincas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/variables.css"> <!-- Ajusta esta ruta a 'public/assets/css/variables.css' si fuera necesario -->
    <style>
        body {
            background-color: var(--bs-primary);
            background-image: radial-gradient(circle at top right, #382f5c, transparent 40%), radial-gradient(circle at bottom left, #121111, transparent 40%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-login-card {
            background: var(--bs-light);
            border-radius: var(--radio-lg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }
        .admin-badge {
            background-color: var(--bs-warning);
            color: #fff;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="admin-login-card text-center">
    <div class="mb-4">
        <h2 class="font-title text-dark mb-1">GestFincas</h2>
        <span class="admin-badge">Zona Superadmin</span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 text-sm-custom" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?route=superadmin/login" method="POST" class="text-start mt-4">
        <div class="mb-3">
            <label for="email" class="form-label text-muted small fw-bold">CORREO ADMINISTRATIVO</label>
            <input type="email" class="form-control bg-fondo border-custom" id="email" name="email" required autofocus placeholder="admin@gestfincas.com">
        </div>
        <div class="mb-4">
            <label for="password" class="form-label text-muted small fw-bold">CONTRASEÑA MAESTRA</label>
            <input type="password" class="form-control bg-fondo border-custom" id="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Autenticarse</button>
    </form>
    
    <div class="mt-4">
        <a href="index.php?route=auth/login" class="text-muted small text-decoration-none hover-underline">← Volver al login normal</a>
    </div>
</div>

</body>
</html>