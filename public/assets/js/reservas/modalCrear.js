// Este array se llenará cuando cargues la vista con los datos de PHP
let espaciosDisponibles = []; 

document.addEventListener('DOMContentLoaded', () => {
    const selectEspacio = document.getElementById('selectEspacio');
    const selectPersonas = document.getElementById('selectPersonas');
    const selectTramo = document.getElementById('selectTramo');

    selectEspacio.addEventListener('change', (e) => {
        const idEspacioSeleccionado = e.target.value;
        
        // 1. Limpiar y deshabilitar opciones
        selectPersonas.innerHTML = '<option value="">Selecciona cantidad...</option>';
        selectPersonas.disabled = true;

        if (!idEspacioSeleccionado) return;

        // 2. Buscar el espacio seleccionado usando id_espacios_comunidad
        const espacio = espaciosDisponibles.find(esp => esp.id_espacios_comunidad == idEspacioSeleccionado);
        
        // 3. Generar opciones basadas en max_personas
        if (espacio && espacio.max_personas > 0) {
            for (let i = 1; i <= espacio.max_personas; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i === 1 ? '1 persona' : `${i} personas`;
                selectPersonas.appendChild(option);
            }
            
            selectPersonas.disabled = false;
            // Aquí también habilitarías selectTramo si dependiera del espacio
        }
    });
});