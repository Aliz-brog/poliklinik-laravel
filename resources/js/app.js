const sidebar = document.getElementById('appSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle = document.getElementById('sidebarToggle');

if (sidebar && sidebarOverlay && sidebarToggle) {
    const setSidebarOpen = (isOpen) => {
        sidebar.classList.toggle('open', isOpen);
        sidebarOverlay.classList.toggle('open', isOpen);
        sidebarToggle.setAttribute('aria-expanded', String(isOpen));
        sidebarToggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
    };

    sidebarToggle.addEventListener('click', () => {
        setSidebarOpen(!sidebar.classList.contains('open'));
    });

    sidebarOverlay.addEventListener('click', () => setSidebarOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });

    sidebar.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) {
            setSidebarOpen(false);
        }
    });
}
