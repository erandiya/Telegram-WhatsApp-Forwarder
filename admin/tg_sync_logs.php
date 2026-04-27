<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp API Logs - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#060912] text-gray-200 font-sans">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            <header class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">📱 WhatsApp API Logs</h2>
                    <p class="text-gray-500 text-sm">Real-time Node.js server logs (Port 3000).</p>
                </div>
                <button onclick="clearPM2('telegram-sync')" class="bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-500 hover:text-white transition-all">Flush Logs</button>
            </header>

            <div class="bg-[#0d111c] border border-gray-800 p-4 rounded-3xl mb-6">
                <input type="text" id="logSearch" placeholder="Filter WhatsApp logs..." class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-green-500 transition-all">
            </div>

            <div class="bg-[#0d111c] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <tbody id="log-tbody" class="divide-y divide-gray-800/40"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function fetchLogs() {
        const search = document.getElementById('logSearch').value;
        fetch(`api_pm2_logs.php?app=telegram-sync&search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(log => {
                    const color = log.type === 'ERR' ? 'text-red-400' : 'text-gray-300';
                    html += `<tr class="hover:bg-gray-800/10"><td class="px-8 py-4 text-xs font-mono ${color}"><span class="opacity-30 mr-2">[${log.type}]</span>${log.message}</td></tr>`;
                });
                document.getElementById('log-tbody').innerHTML = html || '<tr><td class="p-10 text-center text-gray-600">No logs available.</td></tr>';
            });
    }
    function clearPM2(app) {
        if(confirm('Flush all PM2 logs?')) {
            fetch(`ajax_clear_logs.php?type=pm2&app=${app}`).then(() => fetchLogs());
        }
    }
    setInterval(fetchLogs, 2000); fetchLogs();
    document.getElementById('logSearch').addEventListener('keyup', fetchLogs);
    </script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>