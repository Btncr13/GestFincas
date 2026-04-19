document.addEventListener('DOMContentLoaded', () => {
    const btnGuardarEspacio = document.getElementById('btnGuardarEspacio');

    if (btnGuardarEspacio) {
        btnGuardarEspacio.addEventListener('click', () => {
            
            // Recoger valores del formulario
            const formData = new FormData();
            formData.append('nombre_espacio', document.getElementById('nombre_espacio').value);
            formData.append('aforo', document.getElementById('aforo').value);
            formData.append('max_personas', document.getElementById('max_personas').value);
            formData.append('hora_apertura', document.getElementById('hora_apertura').value);
            formData.append('hora_cierre', document.getElementById('hora_cierre').value);
            formData.append('duracion_uso', document.getElementById('duracion_uso').value);
            formData.append('bloqueado', document.getElementById('bloqueado').value);
            formData.append('normas', document.getElementById('normas_espacio').value);

            // Deshabilitar botón para evitar doble clic
            btnGuardarEspacio.disabled = true;

            fetch('index.php?route=espacio/store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal y recargar la vista para ver el nuevo espacio
                    alert('Instalación añadida correctamente');
                    window.location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert('Ocurrió un error en el servidor.');
            })
            .finally(() => {
                btnGuardarEspacio.disabled = false;
            });
        });
    }
});