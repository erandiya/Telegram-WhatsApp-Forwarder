<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Watchdog Logs - WaNTg</title>
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
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">🐕 Watchdog</h2>
                    <p class="text-gray-500 text-sm">Freeze detection and auto-recovery history.</p>
                </div>
                <button onclick="clearLog('watchdog')" class="bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-500 hover:text-white transition-all">
                    Clear Logs
                </button>
            </header>

            <div class="bg-[#0d111c] border border-gray-800 p-4 rounded-3xl mb-6">
                <input type="text" id="logSearch" placeholder="Filter watchdog events..." class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-red-500 transition-all">
            </div>

            <div class="bg-[#0d111c] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl">
                <table class="w-full text-left">
                    <tbody id="watchdog-tbody" class="divide-y divide-gray-800/40"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function fetchLogs() {
        const search = document.getElementById('logSearch').value;
        fetch(`api_system_logs.php?type=watchdog&search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(log => {
                    html += `<tr class="hover:bg-red-500/5 transition-all text-red-400">
                        <td class="px-8 py-5 text-xs font-bold font-mono w-1/4">${log.time}</td>
                        <td class="px-8 py-5 text-sm font-medium">${log.event}</td>
                    </tr>`;
                });
                document.getElementById('watchdog-tbody').innerHTML = html || '<tr><td colspan="2" class="p-10 text-center text-gray-600">System is stable. No freeze logs.</td></tr>';
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