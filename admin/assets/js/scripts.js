document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main-content');
    const overlay = document.getElementById('sidebar-overlay');

    btn.addEventListener('click', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.toggle('sidebar-collapsed');
            main.classList.toggle('main-full');
        } else {
            sidebar.classList.toggle('mobile-active');
            overlay.classList.toggle('active');
        }
    });

    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
        });
    }

    function updateNavbar() {
        fetch('api.php').then(res => res.json()).then(data => {
            document.getElementById('nav-sys-status').querySelector('.status-dot').className = `status-dot ${data.sys_status === 'online' ? 'bg-online animate-pulse' : 'bg-offline'}`;
            let tgDot = data.tg_api_status === 'online' ? 'bg-online' : (data.tg_api_status === 'stalled' ? 'bg-warning' : 'bg-offline');
            document.getElementById('nav-tg-status').querySelector('.status-dot').className = `status-dot ${tgDot}`;
            document.getElementById('nav-wa-status').querySelector('.status-dot').className = `status-dot ${data.wa_status === 'online' ? 'bg-online' : 'bg-offline'}`;
            document.getElementById('nav-disk-status').innerText = data.disk_usage + '%';
        }).catch(() => {});
    }
    setInterval(updateNavbar, 2000);
    updateNavbar();
});