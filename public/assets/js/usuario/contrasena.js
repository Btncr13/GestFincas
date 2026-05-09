document.getElementById('formPassword').addEventListener('submit', function(e) {
        const current = document.getElementById('password_actual').value.trim();
        const nueva = document.getElementById('password_nueva').value.trim();

        // 1. Validar campos vacíos
        if (!current || !nueva) {
            e.preventDefault();
            alert('Por favor, completa todos los campos.');
            return;
        }

        // 2. Validar longitud mínima
        if (nueva.length < 6) {
            e.preventDefault();
            alert('La nueva contraseña debe tener al menos 6 caracteres.');
            return;
        }

        // 3. Validar que no sea la misma
        if (current === nueva) {
            e.preventDefault();
            alert('La nueva contraseña no puede ser igual a la actual.');
            return;
        }

        if (!confirm('¿Estás seguro? Se cerrará tu sesión actual.')) {
            e.preventDefault();
        }
    });