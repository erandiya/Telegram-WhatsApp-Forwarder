<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'sql/queries.php';
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
<!-- index.php හි head ඇතුළත දමන්න -->
<!--meta http-equiv="refresh" content="60"-->
    <title>MSG-Sync Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white font-sans">

    <nav class="p-6 bg-gray-800 border-b border-gray-700">
        <h1 class="text-2xl font-bold text-green-400">🚀 TG-WA Forwarder Dashboard</h1>
    </nav>

    <main class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    
    <!-- 1. Sync System Status (කලින් තිබූ Telegram Card එක) -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-semibold uppercase">Sync System Status</h3>
        <div class="mt-2">
            <p id="sys-status-text" class="text-2xl font-bold text-gray-500">Loading...</p>
            <p id="sys-last-sync" class="text-xs text-gray-500 mt-1">Calculating...</p>
        </div>
    </div>

    <!-- 2. Telegram API Status (අලුත් කාඩ් එක) -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-semibold uppercase">Telegram API Status</h3>
        <p id="tg-api-status-text" class="text-2xl font-bold mt-2 text-gray-500">Checking...</p>
    </div>

    <!-- 3. WhatsApp API Status -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-semibold uppercase">WhatsApp API Status</h3>
        <p id="wa-status-text" class="text-2xl font-bold mt-2 text-gray-500">Loading...</p>
    </div>

    <!-- 4. Disk Usage -->
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
        <h3 class="text-gray-400 text-sm font-semibold uppercase">Disk Usage</h3>
        <p class="text-2xl font-bold mt-2"><?php echo getDiskUsage(); ?>%</p>
        <div class="w-full bg-gray-700 rounded-full h-2 mt-4">
            <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo getDiskUsage(); ?>%"></div>
        </div>
    </div>

</main>

    <script src="assets/js/scripts.js"></script>

<script>
function updateDashboard() {
    fetch('api.php')
        .then(response => response.json())
        .then(data => {
            // 1. Sync System (Cron) Update
            const sysStatus = document.getElementById('sys-status-text');
            const sysTime = document.getElementById('sys-last-sync');
            if (data.sys_status === 'online') {
                sysStatus.innerHTML = '✅ Running';
                sysStatus.className = 'text-2xl font-bold text-green-400';
            } else {
                sysStatus.innerHTML = '❌ Stalled';
                sysStatus.className = 'text-2xl font-bold text-red-500';
            }
            sysTime.innerHTML = 'Last Cron Run: ' + data.sys_time;

            // 2. Telegram API Status Update
            const tgApiStatus = document.getElementById('tg-api-status-text');

            if (data.tg_api_status === 'online') {
                tgApiStatus.innerHTML = '✅ Online';
                tgApiStatus.className = 'text-2xl font-bold mt-2 text-blue-400';
            } else if (data.tg_api_status === 'stalled') {
                tgApiStatus.innerHTML = '⚠️ Stalled';
                tgApiStatus.className = 'text-2xl font-bold mt-2 text-yellow-500';
            } else {
                tgApiStatus.innerHTML = '❌ Offline';
                tgApiStatus.className = 'text-2xl font-bold mt-2 text-red-600';
            }

            // 3. WhatsApp Status Update
            const waStatus = document.getElementById('wa-status-text');
            if (data.wa_status === 'online') {
                waStatus.innerHTML = '✅ Online';
                waStatus.className = 'text-2xl font-bold mt-2 text-green-400';
            } else {
                waStatus.innerHTML = '❌ Offline';
                waStatus.className = 'text-2xl font-bold mt-2 text-red-500';
            }
        })
        .catch(e => console.error(e));
}

updateDashboard();
setInterval(updateDashboard, 1000);
</script>

</body>
</html>
