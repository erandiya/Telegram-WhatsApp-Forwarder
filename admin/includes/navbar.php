<nav id="main-navbar">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3">
            <button id="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-800 text-gray-400 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <span class="text-xl font-bold text-white tracking-tight">
                <span class="hidden md:inline">🚀</span> WaNTg
            </span>
        </div>

        <div class="flex items-center gap-2 md:gap-3 font-bold text-[9px] md:text-[11px] uppercase">
            <div id="nav-sys-status" class="flex items-center gap-1 bg-gray-800 px-2 md:px-3 py-1.5 rounded-lg border border-gray-700">
                <span class="status-dot bg-offline"></span> 
                <span class="hidden md:inline">SYNC RUNNING</span><span class="md:hidden">SY</span>
            </div>
            <div id="nav-tg-status" class="flex items-center gap-1 bg-gray-800 px-2 md:px-3 py-1.5 rounded-lg border border-gray-700">
                <span class="status-dot bg-offline"></span> 
                <span class="hidden md:inline">TG ONLINE</span><span class="md:hidden">TG</span>
            </div>
            <div id="nav-wa-status" class="flex items-center gap-1 bg-gray-800 px-2 md:px-3 py-1.5 rounded-lg border border-gray-700">
                <span class="status-dot bg-offline"></span> 
                <span class="hidden md:inline">WA READY</span><span class="md:hidden">WA</span>
            </div>
            <div class="flex items-center gap-1 bg-gray-800 px-2 md:px-3 py-1.5 rounded-lg border border-gray-700 text-blue-400 font-mono">
                <span class="hidden md:inline">DISK:</span><span class="md:hidden">DK:</span>
                <span id="nav-disk-status">--%</span>
            </div>
        </div>
    </div>
</nav>