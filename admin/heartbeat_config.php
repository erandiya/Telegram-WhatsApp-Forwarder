<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

// --- 1. දත්ත මකා දැමීම ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM notification_targets WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: heartbeat_config.php"); exit;
}

// --- 2. අලුත් Target එකක් එක් කිරීම ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_id'])) {
    $tid = $_POST['target_id'];
    $plat = $_POST['platform'];
    // ටෙලිග්‍රෑම් නම් එය auto-test channel එකක් ලෙස සලකයි
    $is_test = ($plat === 'TG') ? 1 : 0;
    
    $pdo->prepare("INSERT INTO notification_targets (target_id, platform, is_test_channel) VALUES (?, ?, ?)")->execute([$tid, $plat, $is_test]);
    header("Location: heartbeat_config.php"); exit;
}

// දත්ත ලබා ගැනීම
$entities = $pdo->query("SELECT * FROM platform_entities ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
$targets = $pdo->query("SELECT nt.*, pe.title, pe.image_path FROM notification_targets nt LEFT JOIN platform_entities pe ON nt.target_id = pe.remote_id ORDER BY nt.id DESC")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Avatar Helper with Click to Zoom (Same as forwarding.php)
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
    <title>Heartbeat Config - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-[#060912] text-gray-200 font-sans">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            
            <header class="mb-10">
                <h2 class="text-3xl font-black text-white uppercase tracking-tight">💓 Heartbeat Management</h2>
                <p class="text-gray-500 text-sm mt-1">Configure status update destinations.</p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- 1. Drag Source -->
                <div class="lg:col-span-4 bg-[#0d111c] border border-gray-800 p-6 rounded-[2.5rem] shadow-xl">
                    <h3 class="text-blue-400 font-bold uppercase text-[10px] tracking-widest mb-4">Available Entities</h3>
                    <input type="text" id="entitySearch" placeholder="Search..." class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2 text-xs mb-4 outline-none focus:border-blue-500 transition-all">
                    
                    <div id="entity-list" class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach($entities as $en): ?>
                        <div class="drag-item bg-gray-800/30 p-3 rounded-2xl border border-gray-800 flex items-center gap-3 hover:border-blue-500/50 transition-all" 
                             data-id="<?php echo $en['remote_id']; ?>" data-name="<?php echo $en['title']; ?>" data-platform="<?php echo $en['platform']; ?>">
                            <?php echo getAvatar($en['title'], $en['image_path']); ?>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-bold truncate"><?php echo $en['title']; ?></span>
                                <span class="text-[9px] text-gray-600 font-mono"><?php echo $en['platform']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. Targets Table -->
                <div class="lg:col-span-8 space-y-6">
                    <div id="drop-area" class="border-2 border-dashed border-gray-800 rounded-[2.5rem] p-8 flex flex-col items-center justify-center text-center hover:border-blue-500/50 transition-all group">
                        <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Drag here to add target</p>
                    </div>

                    <div class="bg-[#0d111c] border border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-800/30 text-gray-500 text-[10px] uppercase font-black border-b border-gray-800">
                                    <th class="px-8 py-5">Target Destination</th>
                                    <th class="px-8 py-5 text-center">Status</th>
                                    <th class="px-8 py-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/40">
                                <?php foreach ($targets as $t): ?>
                                <tr class="hover:bg-gray-800/10 transition-all">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <?php echo getAvatar($t['title'], $t['image_path']); ?>
                                            <div>
                                                <div class="font-bold text-white text-sm"><?php echo $t['title'] ?: 'Unknown'; ?></div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-gray-800 text-gray-400 border border-gray-700"><?php echo $t['platform']; ?></span>
                                                    <span class="text-[10px] text-gray-600 font-mono"><?php echo $t['target_id']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" <?php echo $t['is_enabled'] ? 'checked' : ''; ?> onchange="toggleHeartbeat(<?php echo $t['id']; ?>)" class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                                        </label>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <a href="?delete=<?php echo $t['id']; ?>" onclick="return confirm('Remove this target?')" class="text-red-500/30 hover:text-red-500 transition-all inline-block p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 🖼️ Image Zoom Modal -->
    <div id="imageModal" onclick="closeImageModal()" class="hidden fixed inset-0 bg-black/95 z-[3000] flex items-center justify-center p-4 backdrop-blur-sm cursor-zoom-out">
        <img id="modalImg" src="" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl border border-white/10">
    </div>

    <div id="sidebar-overlay"></div>
    <form id="hidden-form" method="POST" class="hidden">
        <input type="hidden" name="target_id" id="h_id">
        <input type="hidden" name="platform" id="h_platform">
    </form>

    <script src="assets/js/scripts.js"></script>
    <script>
        // Image Popup
        function openImageModal(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Search
        document.getElementById('entitySearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#entity-list .drag-item').forEach(i => {
                i.style.display = i.innerText.toLowerCase().includes(term) ? 'flex' : 'none';
            });
        });

        // Toggle Status
        function toggleHeartbeat(id) {
            fetch(`ajax_toggle_heartbeat.php?id=${id}`).then(res => res.json());
        }

        // Drag & Drop
        new Sortable(document.getElementById('entity-list'), {
            group: { name: 'heartbeat', pull: 'clone', put: false },
            sort: false, animation: 150
        });

        new Sortable(document.getElementById('drop-area'), {
            group: 'heartbeat',
            onAdd: function (evt) {
                const item = evt.item;
                document.getElementById('h_id').value = item.getAttribute('data-id');
                document.getElementById('h_platform').value = item.getAttribute('data-platform');
                document.getElementById('hidden-form').submit();
            }
        });
    </script>
</body>
</html>