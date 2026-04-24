document.addEventListener('DOMContentLoaded', () => {
    
    // Helper para mostrar Toasts de Bootstrap 5
    const showToast = (message, type = 'success') => {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
        
        const toastHTML = `
            <div class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
    };

    const createToastContainer = () => {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
        return container;
    };

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
                showToast('Incidencia reportada correctamente.', 'success');
                setTimeout(() => location.reload(), 1500); 
                } else {
                showToast(data.message || 'Ocurrió un error al guardar.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            showToast('Error crítico de red.', 'error');
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
                showToast('Te has unido exitosamente a la incidencia comunitaria.', 'success');
                setTimeout(() => location.reload(), 1500); 
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Error al unirse.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            showToast('Error de conexión al intentar unirse.', 'error');
            }
        });
    }

    // ==========================================
    // 2. LÓGICA DEL TABLÓN: DELEGACIÓN DE EVENTOS 
    // ==========================================
    const contenedorIncidencias = document.getElementById('contenedor-incidencias');
    
    if (contenedorIncidencias) {
        contenedorIncidencias.addEventListener('click', async (e) => {
            
            // ACCIÓN: VER FOTO (LIGHTBOX)
            if (e.target.closest('.lightbox-trigger')) {
                e.preventDefault();
                const trigger = e.target.closest('.lightbox-trigger');
                const imgSrc = trigger.getAttribute('data-img');
                const lightboxImage = document.getElementById('lightboxImage');
                
                if (lightboxImage) {
                    lightboxImage.src = imgSrc;
                    const lightboxModal = new bootstrap.Modal(document.getElementById('lightboxModal'));
                    lightboxModal.show();
                }
            }

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
                        btn.classList.remove('btn-unirme-card'); // Quita el gatillo del evento
                        btn.disabled = true; // Deshabilita el botón físicamente
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Te has unido';
                    } else {
                    showToast(data.message || 'Error al unirse a la incidencia.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-hand-holding-hand"></i> Unirme';
                    }
                } catch (error) {
                    console.error('Error:', error);
                showToast('Error de conexión.', 'error');
                    btn.disabled = false;
                }
            }
            
            // ACCIÓN: RESOLVER (PRESIDENTE)
            if (e.target.closest('.btn-resolver')) {
                const btn = e.target.closest('.btn-resolver');
                const idIncidencia = btn.getAttribute('data-id');
                
                if (confirm('¿Estás seguro de que deseas marcar esta incidencia como resuelta?')) {
                    const formData = new FormData();
                    formData.append('id_incidencia', idIncidencia);
                    formData.append('estado', 'resuelta'); // Añadimos el estado esperado

                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Resolviendo...';

                        const response = await fetch('index.php?route=incidencias/updateEstado', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            showToast('La incidencia ha sido marcada como resuelta.', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(data.message || 'Error al resolver la incidencia.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-check"></i> Resolver';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('Error crítico de red.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Resolver';
                    }
                }
            }

            // ACCIÓN: ABRIR (PRESIDENTE)
            if (e.target.closest('.btn-abrir')) {
                const btn = e.target.closest('.btn-abrir');
                const idIncidencia = btn.getAttribute('data-id');
                
                if (confirm('¿Estás seguro de que deseas marcar esta incidencia como en curso (abierta)?')) {
                    const formData = new FormData();
                    formData.append('id_incidencia', idIncidencia);
                    formData.append('estado', 'abierta'); // Añadimos el estado esperado

                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Abriendo...';

                        const response = await fetch('index.php?route=incidencias/updateEstado', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            showToast('La incidencia ha sido abierta (en curso).', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(data.message || 'Error al abrir la incidencia.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-folder-open"></i> Abrir';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('Error crítico de red.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-folder-open"></i> Abrir';
                    }
                }
            }

            // ACCIÓN: ELIMINAR (CREADOR O PRESIDENTE)
            if (e.target.closest('.btn-delete')) {
                const btn = e.target.closest('.btn-delete');
                const idIncidencia = btn.getAttribute('data-id');
                
                if (confirm('¿Seguro que deseas eliminar esta incidencia permanentemente?')) {
                    const formData = new FormData();
                    formData.append('id_incidencia', idIncidencia);

                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Eliminando...';

                        const response = await fetch('index.php?route=incidencias/delete', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            // Optimistic UI: Borramos la tarjeta del DOM sin recargar la página entera
                            const tarjeta = document.getElementById(`incidencia-${idIncidencia}`);
                            if (tarjeta) tarjeta.remove();
                            
                            // Si era la única incidencia visible, recargamos para que aparezca el estado vacío
                            if (document.querySelectorAll('[id^="incidencia-"]').length === 0) location.reload();
                        } else {
                            alert(data.message || 'Error al eliminar la incidencia.');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-trash"></i> Eliminar';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error de conexión.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-trash"></i> Eliminar';
                    }
                }
            }
            
        });
    }
});