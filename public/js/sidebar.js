// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {

    const btnToggle = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');

    if (btnToggle && sidebar) {
        btnToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            
            const icon = btnToggle.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.classList.replace('bi-list', 'bi-arrow-right-short');
            } else {
                icon.classList.replace('bi-arrow-right-short', 'bi-list');
            }
        });
    }

    
});