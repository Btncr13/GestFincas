<footer class="footer-custom">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> <span class="fw-bold text-primary">GestFincas</span> — Sistema de gestión de comunidades.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const themeSwitch = document.getElementById('themeSwitch');
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');

    // Al cargar la página, aplicar el tema guardado
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        if(themeSwitch) themeSwitch.checked = true;
        if(themeIcon) {
            themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
        }
    }

    // Escuchar cambios en el switch
    if (themeSwitch) {
        themeSwitch.addEventListener('change', () => {
            if (themeSwitch.checked) {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                if(themeIcon) themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                if(themeIcon) themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            }
        });
    }
</script>
</body>

</html>