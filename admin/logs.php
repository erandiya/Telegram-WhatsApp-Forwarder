<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Sync Logs - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .log-row { transition: all 0.3s ease; }
        .log-row:first-child { background: rgba(59, 130, 246, 0.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#060912] text-gray-200 font-sans">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">📜 Live Sync Logs</h2>
                    <p class="text-gray-500 text-sm mt-1">Real-time monitoring of every forwarded message.</p>
                </div>
            </header>

            <!-- 🔍 Filtering Toolbar -->
            <div class="bg-[#0d111c] border border-gray-800 p-4 rounded-3xl mb-6 shadow-xl flex flex-wrap items-center gap-4">
                <div class="flex-grow min-w-[200px]">
                    <input type="text" id="logSearch" placeholder="Search message, ID or channel..." 
                           class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-blue-500 transition-all">
                </div>
                
                <select id="statusFilter" class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-blue-500 text-gray-400">
                    <option value="">All Status</option>
                    <option value="SUCCESS">Success Only</option>
                    <option value="FAILED">Failed Only</option>
                </select>

                <select id="limitFilter" class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-blue-500 text-gray-400">
                    <option value="25">25 rows</option>
                    <option value="50" selected>50 rows</option>
                    <option value="100">100 rows</option>
                </select>

                <div class="flex items-center gap-2 px-3 py-2 bg-blue-500/5 rounded-xl border border-blue-500/10">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Live</span>
                </div>
            </div>

            <!-- 📋 Table Container -->
            <div class="bg-[#0d111c] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-800/30 text-gray-500 text-[10px] uppercase font-black tracking-widest border-b border-gray-800">
                                <th class="px-8 py-5">Timestamp</th>
                                <th class="px-8 py-5">Route</th>
                                <th class="px-8 py-5">Message Preview</th>
                                <th class="px-8 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="logs-tbody" class="divide-y divide-gray-800/40">
                            <!-- Data will be loaded here via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div id="sidebar-overlay"></div>

    <script>
    const logsTbody = document.getElementById('logs-tbody');
    const logSearch = document.getElementById('logSearch');
    const statusFilter = document.getElementById('statusFilter');
    const limitFilter = document.getElementById('limitFilter');

    let currentDataJson = ""; // පරණ දත්තම නැවත render කිරීම වැළැක්වීමට

    function fetchLogs() {
        const search = logSearch.value;
        const status = statusFilter.value;
        const limit = limitFilter.value;

        fetch(`api_logs.php?search=${encodeURIComponent(search)}&status=${status}&limit=${limit}`)
            .then(res => res.json())
            .then(data => {
                // දත්ත වෙනස් වී ඇත්නම් පමණක් Table එක Update කරයි (Performance)
                const dataString = JSON.stringify(data);
                if (currentDataJson === dataString) return;
                currentDataJson = dataString;

                renderLogs(data);
            })
            .catch(err => console.error('Log API Error:', err));
    }

    function renderLogs(data) {
        if (data.length === 0) {
            logsTbody.innerHTML = `<tr><td colspan="4" class="py-20 text-center text-gray-600 text-sm">No logs found matching your filters.</td></tr>`;
            return;
        }

        let html = '';
        data.forEach(log => {
            const date = new Date(log.created_at);
            const timeStr = date.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = date.toISOString().split('T')[0];

            const statusClass = log.status === 'SUCCESS' 
                ? 'bg-green-500/10 text-green-400 border-green-500/20' 
                : 'bg-red-500/10 text-red-400 border-red-500/20';

            html += `
                <tr class="log-row hover:bg-gray-800/20 transition-all">
                    <td class="px-8 py-5">
                        <div class="text-[11px] font-mono text-gray-500">${dateStr}</div>
                        <div class="text-xs font-bold text-white mt-0.5">${timeStr}</div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-xs font-bold text-blue-400 truncate max-w-[150px]">${log.source_name}</div>
                        <div class="text-[10px] text-gray-600 mt-1 flex items-center gap-1">
                            <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"/></svg>
                            ${log.target_name}
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-xs text-gray-400 max-w-xs md:max-w-md truncate" title="${log.message_preview}">
                            ${log.message_preview}
                        </div>
                        <div class="text-[9px] text-gray-700 mt-1">TG Message ID: ${log.tg_msg_id}</div>
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="border px-2 py-0.5 rounded-full text-[9px] font-black tracking-widest ${statusClass}">
                            ${log.status}
                        </span>
                        ${log.status === 'FAILED' ? `<div class="text-[8px] text-red-600 mt-1 uppercase font-bold">${log.error_details || 'Error'}</div>` : ''}
                    </td>
                </tr>
            `;
        });
        logsTbody.innerHTML = html;
    }

    // තත්පරයකට වරක් පරීක්ෂා කිරීම (1000ms)
    setInterval(fetchLogs, 1000);
    fetchLogs(); // Initial load

    // Filter වෙනස් කරන විට වහාම update කිරීම
    [logSearch, statusFilter, limitFilter].forEach(el => {
        el.addEventListener('change', fetchLogs);
        el.addEventListener('keyup', fetchLogs);
    });
    </script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>