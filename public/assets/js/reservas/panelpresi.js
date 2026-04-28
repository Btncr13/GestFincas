function switchMainTab(seccion) {
    const isRes = seccion === 'reservas';
    const btnRes = document.getElementById('btn-sec-reservas');
    const btnEsp = document.getElementById('btn-sec-espacios');
    
    btnRes.style.backgroundColor = isRes ? 'var(--bs-light, #fff)' : 'transparent';
    btnRes.style.boxShadow = isRes ? '0 1px 3px rgba(0,0,0,0.1)' : 'none';
    btnRes.className = isRes ? 'btn flex-fill text-center rounded-2 py-2 text-dark' : 'btn flex-fill text-center rounded-2 py-2 text-muted';
    
    btnEsp.style.backgroundColor = !isRes ? 'var(--bs-light, #fff)' : 'transparent';
    btnEsp.style.boxShadow = !isRes ? '0 1px 3px rgba(0,0,0,0.1)' : 'none';
    btnEsp.className = !isRes ? 'btn flex-fill text-center rounded-2 py-2 text-dark' : 'btn flex-fill text-center rounded-2 py-2 text-muted';
    
    document.getElementById('vista-reservas').classList.toggle('d-none', !isRes);
    document.getElementById('vista-espacios').classList.toggle('d-none', isRes);
}

function switchSubTab(estado) {
    const isAct = estado === 'activas';
    const btnActivas = document.getElementById('btn-sub-activas');
    const btnInactivas = document.getElementById('btn-sub-inactivas');
    
    btnActivas.style.backgroundColor = isAct ? 'var(--bs-light, #fff)' : 'transparent';
    btnActivas.style.boxShadow = isAct ? '0 1px 3px rgba(0,0,0,0.1)' : 'none';
    btnActivas.className = isAct ? 'btn flex-fill text-center rounded-2 py-1 text-dark' : 'btn flex-fill text-center rounded-2 py-1 text-muted';
    
    btnInactivas.style.backgroundColor = !isAct ? 'var(--bs-light, #fff)' : 'transparent';
    btnInactivas.style.boxShadow = !isAct ? '0 1px 3px rgba(0,0,0,0.1)' : 'none';
    btnInactivas.className = !isAct ? 'btn flex-fill text-center rounded-2 py-1 text-dark' : 'btn flex-fill text-center rounded-2 py-1 text-muted';
    
    document.getElementById('lista-activas').classList.toggle('d-none', !isAct);
    document.getElementById('lista-inactivas').classList.toggle('d-none', isAct);
}