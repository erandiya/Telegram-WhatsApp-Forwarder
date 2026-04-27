<!-- admin/includes/sidebar.php -->
<div id="sidebar-overlay" class="md:hidden"></div>

<aside id="sidebar" class="fixed left-0 top-[70px] h-[calc(100vh-70px)] bg-[#0d111c] border-r border-gray-800 z-[1000] transition-transform duration-300">
    <div class="flex flex-col h-full py-4">
        
        <div class="px-4 space-y-2 mt-4 flex-grow overflow-y-auto custom-scrollbar">
            
            <!-- Dashboard -->
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Forwarding Rules -->
            <a href="forwarding.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                <span class="font-medium">Forwarding Rules</span>
            </a>

            <!-- Word Dictionary -->
            <a href="dictionary.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="font-medium">Word Dictionary</span>
            </a>

            <!-- Heartbeating -->
            <a href="heartbeat_config.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                <span class="font-medium">Heartbeating</span>
            </a>

            <!-- ✅ SYNC LOGS (අලුතින් එක් කළ ලින්ක් එක) -->
            <a href="logs.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="font-medium">Sync Logs</span>
            </a>
            
            <!-- Sidebar ඇතුළත ලොග් සෙක්ෂන් එකට පසුව මෙය එක් කරන්න -->
            <div class="px-4 py-2 text-[10px] font-black text-gray-600 uppercase tracking-widest mt-4">System Health</div>

            <a href="maintenance_logs.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/></svg>
                <span class="font-medium text-sm">Maintenance</span>
            </a>

            <a href="watchdog_logs.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-800 transition text-gray-400 hover:text-white group">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-width="2"/></svg>
                <span class="font-medium text-sm">Watchdog Monitor</span>
            </a>

        </div>

        <!-- Logout Section -->
        <div class="px-4 pb-4 border-t border-gray-800 pt-4">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-500/10 transition text-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="font-medium">Logout</span>
            </a>
        </div>

    </div>
</aside>