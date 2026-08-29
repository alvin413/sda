function showHeaderAlert(type, message) {
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'custom-alert-overlay';

    // Contenido de la alerta
    const alertBox = document.createElement('div');
    alertBox.className = `custom-alert-box alert alert-${type} shadow`;
    alertBox.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
        ${message}
        <div class="alert-timer mt-2">
            <div class="alert-timer-bar"></div>
        </div>
    `;
    overlay.appendChild(alertBox);
    document.body.appendChild(overlay);

    // Animación barra de progreso
    const bar = alertBox.querySelector('.alert-timer-bar');
    bar.style.width = '100%';
    bar.style.height = '4px';
    bar.style.backgroundColor = type === 'success' ? '#1cc88a' : '#e74a3b';
    bar.style.borderRadius = '2px';
    bar.style.transition = 'width 2s linear';

    // Reducir ancho a 0 en 5 segundos
    setTimeout(() => {
        bar.style.width = '0%';
    }, 50); // pequeño delay para que la transición se aplique

    // Auto-cerrar después de 5s
    setTimeout(() => {
        if (overlay.parentNode) document.body.removeChild(overlay);
    }, 2000);
}
