<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

$sources = $pdo->query("SELECT * FROM platform_entities ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
$targets = $pdo->query("SELECT * FROM platform_entities WHERE can_send = 1 ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

function renderAvatar($name, $img = null) {
    if ($img && file_exists(__DIR__ . '/../' . $img)) {
        return '<img src="../'.$img.'" class="w-8 h-8 rounded-lg object-cover">';
    }
    $initial = strtoupper(substr($name ?: '?', 0, 1));
    return '<div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center text-[10px] font-bold text-white">'.$initial.'</div>';
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Mapping - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .drag-item { cursor: grab; transition: all 0.2s; }
        .drop-zone { border: 2px dashed #1f2937; min-height: 120px; transition: all 0.3s; }
        .drop-zone.drag-over { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#060912] text-gray-200 font-sans">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            <header class="mb-8">
                <h2 class="text-3xl font-black text-white">New Forwarding Rule</h2>
                <p class="text-gray-500 text-sm">Search and drag entities to create a new sync link.</p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- 1. Source Selection with Search -->
                <div class="bg-[#0d111c] border border-gray-800 p-5 rounded-[2rem] shadow-xl flex flex-col">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <h3 class="text-gray-500 font-bold uppercase text-[10px] tracking-widest">1. Select Source</h3>
                    </div>
                    <!-- Search Box -->
                    <input type="text" id="source-search" placeholder="Search source..." 
                           class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2 text-xs mb-4 focus:border-blue-500 outline-none transition-all">
                    
                    <div id="source-list" class="space-y-2 max-h-[450px] overflow-y-auto custom-scrollbar pr-2">
                        <?php foreach($sources as $s): ?>
                        <div class="drag-item bg-gray-800/30 p-3 rounded-2xl border border-gray-800 flex items-center gap-3 hover:border-blue-500/50 transition-all" 
                             data-id="<?php echo $s['remote_id']; ?>" data-name="<?php echo strtolower($s['title']); ?>" data-type="source">
                            <?php echo renderAvatar($s['title'], $s['image_path']); ?>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-bold truncate text-gray-200"><?php echo $s['title']; ?></span>
                                <span class="text-[9px] text-gray-600 font-mono"><?php echo $s['platform']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. Drop Area & Config -->
                <div class="flex flex-col gap-6">
                    <div id="drop-area" class="drop-zone rounded-[2.5rem] flex flex-col items-center justify-center p-6 text-center">
                        <div id="placeholder-icon" class="text-gray-700 mb-2">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <p id="preview-text" class="text-xs font-bold text-gray-500 uppercase">Drag items here</p>
                        <div id="active-link-display" class="mt-4 flex flex-col items-center gap-2 hidden">
                            <span id="src-preview" class="text-blue-400 font-bold text-sm"></span>
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 14l-7 7m0 0l-7-7m7 7V3" stroke-width="3"/></svg>
                            <span id="dest-preview" class="text-green-400 font-bold text-sm"></span>
                        </div>
                    </div>

                    <form id="mapping-form" action="process_mapping.php" method="POST" class="hidden space-y-4 bg-[#0d111c] p-6 rounded-[2rem] border border-gray-800 shadow-xl">
                        <input type="hidden" name="source_id" id="form_source_id">
                        <input type="hidden" name="target_id" id="form_target_id">
                        <input type="hidden" name="target_platform" id="form_target_platform">

                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl cursor-pointer hover:bg-gray-800 transition-all border border-transparent hover:border-gray-700">
                                <input type="checkbox" name="replace_enabled" class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-blue-600 focus:ring-0">
                                <span class="text-xs font-medium">Enable Word Replacement</span>
                            </label>
                            <!-- අලුතින් එක් කළ Intelligent Replacement -->
                            <label class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl cursor-pointer hover:bg-gray-800 transition-all border border-transparent hover:border-gray-700">
                                <input type="checkbox" name="intelligent_replace" class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-purple-600 focus:ring-0">
                                <span class="text-xs font-medium">Enable Intelligent Replacement</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl cursor-pointer hover:bg-gray-800 transition-all border border-transparent hover:border-gray-700">
                                <input type="checkbox" name="block_service" class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-orange-600 focus:ring-0">
                                <span class="text-xs font-medium">Block Service Messages</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95">
                            CREATE SYNC LINK
                        </button>
                    </form>
                </div>

                <!-- 3. Target Selection with Search -->
                <div class="bg-[#0d111c] border border-gray-800 p-5 rounded-[2rem] shadow-xl flex flex-col">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <h3 class="text-gray-500 font-bold uppercase text-[10px] tracking-widest">2. Select Target</h3>
                    </div>
                    <!-- Search Box -->
                    <input type="text" id="target-search" placeholder="Search target..." 
                           class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2 text-xs mb-4 focus:border-green-500 outline-none transition-all">

                    <div id="target-list" class="space-y-2 max-h-[450px] overflow-y-auto custom-scrollbar pr-2">
                        <?php foreach($targets as $t): ?>
                        <div class="drag-item bg-gray-800/30 p-3 rounded-2xl border border-gray-800 flex items-center gap-3 hover:border-green-500/50 transition-all" 
                             data-id="<?php echo $t['remote_id']; ?>" data-name="<?php echo strtolower($t['title']); ?>" data-platform="<?php echo $t['platform']; ?>" data-type="target">
                            <?php echo renderAvatar($t['title'], $t['image_path']); ?>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-bold truncate text-gray-200"><?php echo $t['title']; ?></span>
                                <span class="text-[9px] text-gray-600 font-mono"><?php echo $t['platform']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- SEARCH LOGIC ---
        function setupSearch(inputId, listId) {
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);
            
            input.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                const items = list.getElementsByClassName('drag-item');
                
                Array.from(items).forEach(item => {
                    const name = item.getAttribute('data-name');
                    if (name.includes(term)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        setupSearch('source-search', 'source-list');
        setupSearch('target-search', 'target-list');

        // --- DRAG & DROP LOGIC ---
        let selectedSource = null;
        let selectedTarget = null;

        const setupSortable = (elId) => {
            new Sortable(document.getElementById(elId), {
                group: { name: 'shared', pull: 'clone', put: false },
                sort: false,
                animation: 150
            });
        };

        setupSortable('source-list');
        setupSortable('target-list');

        new Sortable(document.getElementById('drop-area'), {
            group: 'shared',
            onAdd: function (evt) {
                const item = evt.item;
                const type = item.getAttribute('data-type');
                const id = item.getAttribute('data-id');
                const name = item.querySelector('.truncate').innerText;
                const platform = item.getAttribute('data-platform');

                document.getElementById('placeholder-icon').classList.add('hidden');
                document.getElementById('preview-text').classList.add('hidden');
                document.getElementById('active-link-display').classList.remove('hidden');

                if (type === 'source') {
                    selectedSource = name;
                    document.getElementById('src-preview').innerText = "FROM: " + name;
                    document.getElementById('form_source_id').value = id;
                } else {
                    selectedTarget = name;
                    document.getElementById('dest-preview').innerText = "TO: " + name;
                    document.getElementById('form_target_id').value = id;
                    document.getElementById('form_target_platform').value = platform;
                }

                if (selectedSource || selectedTarget) {
                    document.getElementById('mapping-form').classList.remove('hidden');
                }
                item.remove();
            }
        });
    </script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>