<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

// --- 1. දත්ත මකා දැමීම ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM forward_mappings WHERE id = ?")->execute([$id]);
    header("Location: forwarding.php?status=deleted");
    exit;
}

// --- 2. SQL Query එක ---
$query = "
    SELECT 
        fm.*, 
        pe_src.title as source_name, 
        pe_src.image_path as source_img,
        pe_src.platform as source_platform,
        pe_target.title as target_name,
        pe_target.image_path as target_img
    FROM forward_mappings fm
    LEFT JOIN platform_entities pe_src ON fm.source_tg_id = pe_src.remote_id
    LEFT JOIN platform_entities pe_target ON fm.target_id = pe_target.remote_id
    ORDER BY fm.id DESC
";
$mappings = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// --- 3. දත්ත කාණ්ඩ කිරීම ---
$grouped_mappings = [
    'TG-WA' => [], 'TG-TG' => [], 'WA-TG' => [], 'WA-WA' => [], 'Other' => []
];

foreach ($mappings as $map) {
    $key = ($map['source_platform'] ?: 'Unknown') . '-' . ($map['target_platform'] ?: 'Unknown');
    if (isset($grouped_mappings[$key])) $grouped_mappings[$key][] = $map;
    else $grouped_mappings['Other'][] = $map;
}

$category_names = [
    'TG-WA' => 'Telegram ➔ WhatsApp',
    'TG-TG' => 'Telegram ➔ Telegram',
    'WA-TG' => 'WhatsApp ➔ Telegram',
    'WA-WA' => 'WhatsApp ➔ WhatsApp',
    'Other' => 'Uncategorized / Pending Sync'
];

/**
 * Avatar Helper with Click to Zoom
 */
function getAvatar($name, $img = null) {
    if ($img && file_exists(__DIR__ . '/../' . $img)) {
        $finalImgPath = '../' . $img;
        return '<img src="'.$finalImgPath.'" onclick="openImageModal(\''.$finalImgPath.'\')" class="w-10 h-10 rounded-xl object-cover shadow-md border border-gray-700 cursor-pointer hover:scale-110 transition-transform">';
    }
    
    $initial = strtoupper(substr($name ?: '?', 0, 1));
    $colors = ['bg-blue-600', 'bg-emerald-600', 'bg-orange-600', 'bg-rose-600', 'bg-indigo-600'];
    $color = $colors[ord($initial) % count($colors)];
    return '<div class="w-10 h-10 rounded-xl '.$color.' flex items-center justify-center text-white font-black shadow-md border border-white/10 text-lg">'.$initial.'</div>';
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forwarding Rules - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Collapsible Animation */
        .category-content { 
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .expanded .category-content { 
            max-height: 2000px; /* ලොකු අගයක් ලබා දීමෙන් සියල්ල පෙන්වයි */
        }
        .expanded .chevron-icon { transform: rotate(0deg); }
        .chevron-icon { transform: rotate(-90deg); transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-[#060912] text-gray-200 font-sans">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight">Forwarding Rules</h2>
                    <p class="text-gray-500 text-sm mt-1">Manage synchronization paths with easy grouping.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button onclick="startSync()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-lg active:scale-95">
                        <svg id="sync-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h5M20 20v-5h-5M15.24 6.37A8 8 0 1020.4 12" stroke-width="2" stroke-linecap="round"/></svg>
                        <span class="whitespace-nowrap">Sync Data</span>
                    </button>
                    <a href="add_mapping.php" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-lg active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2.5" stroke-linecap="round"/></svg>
                        <span>Add Rule</span>
                    </a>
                </div>
            </header>

            <?php foreach ($grouped_mappings as $category => $list): if (count($list) > 0): ?>
                <!-- Default state is collapsed (without .expanded class) -->
                <div class="category-block mb-4" id="cat-<?php echo $category; ?>">
                    <!-- Collapsible Header -->
                    <div onclick="toggleCategory('<?php echo $category; ?>')" class="flex items-center gap-4 px-5 py-4 bg-gray-800/20 rounded-2xl cursor-pointer hover:bg-gray-800/40 transition-all border border-gray-800/50 group">
                        <svg class="chevron-icon w-5 h-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="text-[12px] font-black text-gray-300 uppercase tracking-[0.2em] flex-grow select-none">
                            <?php echo $category_names[$category]; ?>
                        </span>
                        <span class="bg-blue-500/10 text-blue-400 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-blue-500/20">
                            <?php echo count($list); ?> Rules
                        </span>
                    </div>

                    <!-- Table Content (Starts Hidden) -->
                    <div class="category-content">
                        <div class="bg-[#0d111c] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl mt-4 mb-8">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-gray-800/30 text-gray-500 text-[10px] uppercase tracking-widest font-black border-b border-gray-800">
                                            <th class="px-8 py-5">Source</th>
                                            <th class="px-4 py-5 text-center"></th>
                                            <th class="px-8 py-5">Destination</th>
                                            <th class="px-8 py-5 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800/40">
                                        <?php foreach ($list as $map): ?>
                                        <tr class="hover:bg-gray-800/20 transition-all">
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-4">
                                                    <?php echo getAvatar($map['source_name'] ?: $map['source_tg_id'], $map['source_img']); ?>
                                                    <div>
                                                        <div class="font-bold text-white"><?php echo $map['source_name'] ?: 'Unknown'; ?></div>
                                                        <div class="text-[10px] text-gray-600 font-mono"><?php echo $map['source_tg_id']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-6 text-center">
                                                <svg class="w-4 h-4 text-gray-700 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-4">
                                                    <?php echo getAvatar($map['target_name'] ?: $map['target_id'], $map['target_img']); ?>
                                                    <div>
                                                        <div class="font-bold text-gray-200"><?php echo $map['target_name'] ?: 'Unknown'; ?></div>
                                                        <div class="flex flex-wrap gap-2 mt-1.5">
                                                            <span class="text-[9px] <?php echo $map['is_enabled'] ? 'text-green-400' : 'text-red-400'; ?> font-black uppercase tracking-tighter"><?php echo $map['is_enabled'] ? 'Active' : 'Paused'; ?></span>
                                                            <?php if($map['is_replace_enabled']): ?><span class="text-[9px] bg-purple-500/10 text-purple-400 border border-purple-500/20 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">Replace</span><?php endif; ?>
                                                            <?php if($map['is_intelligent_replace']): ?><span class="text-[9px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">AI</span><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <div class="flex items-center justify-end gap-4">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" <?php echo $map['is_enabled'] ? 'checked' : ''; ?> onchange="toggleRule(<?php echo $map['id']; ?>)" class="sr-only peer">
                                                        <div class="w-9 h-5 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                                    </label>
                                                    <a href="?delete=<?php echo $map['id']; ?>" onclick="return confirm('Delete mapping?')" class="text-red-500/40 hover:text-red-500 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>

    <!-- 🖼️ Image Preview Modal -->
    <div id="imageModal" onclick="closeImageModal()" class="hidden fixed inset-0 bg-black/95 z-[3000] flex items-center justify-center p-4 backdrop-blur-sm cursor-zoom-out">
        <img id="modalImg" src="" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl border border-white/10">
    </div>

    <!-- 🔄 Sync Modal -->
    <div id="syncModal" class="hidden fixed inset-0 bg-black/90 backdrop-blur-md z-[2500] flex items-center justify-center p-4 text-center">
        <div class="bg-[#0d111c] border border-gray-800 p-10 rounded-[3rem] max-w-sm w-full shadow-2xl">
            <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
            <h3 class="text-2xl font-bold text-white">Syncing Data</h3>
            <p class="text-gray-500 text-sm mt-2">Updating platform entities...</p>
        </div>
    </div>

    <div id="sidebar-overlay"></div>
    <script src="assets/js/scripts.js"></script>
    <script>
        // --- 📂 Category Toggle Logic ---
        function toggleCategory(catId) {
            const block = document.getElementById('cat-' + catId);
            block.classList.toggle('expanded');
        }

        // --- 🖼️ Image Modal Logic ---
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const img = document.getElementById('modalImg');
            img.src = src;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // --- Rules Toggle ---
        function toggleRule(id) {
            fetch(`ajax_toggle_rule.php?id=${id}`)
                .then(res => res.json())
                .then(data => { if(data.status !== 'success') alert('Sync Status Update Failed'); });
        }

        // --- Sync Trigger ---
        function startSync() {
            document.getElementById('syncModal').classList.remove('hidden');
            fetch('ajax_sync.php').then(() => { setTimeout(() => location.reload(), 12000); });
        }
    </script>
</body>
</html>