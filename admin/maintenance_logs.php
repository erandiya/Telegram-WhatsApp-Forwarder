<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Logs - WaNTg</title>
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
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">🛠️ Maintenance</h2>
                    <p class="text-gray-500 text-sm">Automated cleanup and reset history.</p>
                </div>
                <button onclick="clearLog('maintenance')" class="bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-500 hover:text-white transition-all">
                    Clear Logs
                </button>
            </header>

            <div class="bg-[#0d111c] border border-gray-800 p-4 rounded-3xl mb-6 flex gap-4">
                <input type="text" id="logSearch" placeholder="Filter maintenance events..." class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-orange-500 transition-all">
            </div>

            <div class="bg-[#0d111c] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <tbody id="maintenance-tbody" class="divide-y divide-gray-800/40"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function fetchLogs() {
        const search = document.getElementById('logSearch').value;
        fetch(`api_system_logs.php?type=maintenance&search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(log => {
                    html += `<tr class="hover:bg-gray-800/10"><td class="px-8 py-5 text-sm text-gray-300 font-mono">${log.event}</td></tr>`;
                });
                document.getElementById('maintenance-tbody').innerHTML = html || '<tr><td class="p-10 text-center text-gray-600">No logs found.</td></tr>';
            });
    }

    function clearLog(type) {
        if(confirm('Are you sure you want to clear ' + type + ' logs?')) {
            fetch(`ajax_clear_logs.php?type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        fetchLogs(); // ටේබල් එක refresh කරයි
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Failed to connect to server.'));
        }
    }

    setInterval(fetchLogs, 2000); fetchLogs();
    document.getElementById('logSearch').addEventListener('keyup', fetchLogs);
    </script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>