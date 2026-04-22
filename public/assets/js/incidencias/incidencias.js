document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // 1. LÓGICA DEL MODAL: CREAR INCIDENCIA
    // ==========================================
    const form = document.getElementById('form-incidencia');
    const inputTitulo = document.getElementById('titulo');
    const errorTitulo = document.getElementById('error-titulo');
    const alertaSimilar = document.getElementById('alerta-similar');
    const btnUnirmeModal = document.getElementById('btn-unirme');
    
    let idIncidenciaSimilar = null;

    if (form) {
        // Validación en vivo: Máximo 4 palabras
        inputTitulo.addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/).filter(word => word.length > 0);
            if (words.length > 4) {
                errorTitulo.classList.remove('d-none');
                this.value = words.slice(0, 4).join(' '); // Recortar
            } else {
                errorTitulo.classList.add('d-none');
            }
        });

        // Envío del formulario de creación
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch('index.php?route=incidencias/store', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (response.status === 409 && data.status === 'similar_found') {
                    // Mostrar alerta de similitud en el modal
                    document.getElementById('sim-titulo').innerText = `"${data.incidencia.titulo}" reportado el ${data.incidencia.fecha_creacion.split(' ')}`;
                    idIncidenciaSimilar = data.incidencia.id_incidencias;
                    alertaSimilar.classList.remove('d-none');
                    document.getElementById('btn-submit').disabled = true;
                } else if (response.ok && data.status === 'success') {
                    location.reload(); 
                } else {
                    alert(data.message || 'Ocurrió un error al guardar.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error crítico de red.');
            }
        });
    }

    // Lógica para Unirse desde el aviso de duplicado (dentro del Modal)
    if (btnUnirmeModal) {
        btnUnirmeModal.addEventListener('click', async () => {
            if (!idIncidenciaSimilar) return;
            
            const formData = new FormData();
            formData.append('id_incidencia', idIncidenciaSimilar);

            try {
                const response = await fetch('index.php?route=incidencias/join', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    alert('Te has unido exitosamente a la incidencia comunitaria.');
                    location.reload(); 
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    // ==========================================
    // 2. LÓGICA DEL TABLÓN: DELEGACIÓN DE EVENTOS 
    // ==========================================
    const contenedorIncidencias = document.getElementById('contenedor-incidencias');
    
    if (contenedorIncidencias) {
        contenedorIncidencias.addEventListener('click', async (e) => {
            
            // ACCIÓN: UNIRME DESDE LA TARJETA
            if (e.target.closest('.btn-unirme-card')) {
                const btn = e.target.closest('.btn-unirme-card');
                const idIncidencia = btn.getAttribute('data-id');
                
                const formData = new FormData();
                formData.append('id_incidencia', idIncidencia);

                try {
                    // Deshabilitar temporalmente para evitar doble clic
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uniéndose...';

                    const response = await fetch('index.php?route=incidencias/join', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        // Optimistic UI: Sumar 1 al contador sin recargar
                        const contadorDOM = document.getElementById(`afectados-${idIncidencia}`);
                        if (contadorDOM) {
                            contadorDOM.innerText = parseInt(contadorDOM.innerText) + 1;
                        }
                        // Cambiar botón para feedback visual
                        btn.classList.replace('btn-info', 'btn-secondary');
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Te has unido';
                    } else {
                        alert(data.message || 'Error al unirse a la incidencia.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-hand-holding-hand"></i> Unirme';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error de conexión.');
                    btn.disabled = false;
                }
            }
            
            // Aquí puedes añadir en el futuro la acción de ELIMINAR y RESOLVER
            // if (e.target.closest('.btn-delete')) { ... }
            // if (e.target.closest('.btn-resolver')) { ... }
            
        });
    }
});