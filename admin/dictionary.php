<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

$status_msg = "";

// --- 1. වචනයක් ඇතුළත් කිරීම හෝ සංස්කරණය කිරීම ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_word'])) {
        $find = trim($_POST['find_text']);
        $replace = trim($_POST['replace_with']);
        if (!empty($find)) {
            $pdo->prepare("INSERT INTO word_replacements (find_text, replace_with) VALUES (?, ?)")->execute([$find, $replace]);
            $status_msg = "Word added successfully!";
        }
    } elseif (isset($_POST['update_word'])) {
        $id = (int)$_POST['word_id'];
        $find = trim($_POST['find_text']);
        $replace = trim($_POST['replace_with']);
        $pdo->prepare("UPDATE word_replacements SET find_text = ?, replace_with = ? WHERE id = ?")->execute([$find, $replace, $id]);
        $status_msg = "Word updated successfully!";
    }
}

// --- 2. මකා දැමීම ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM word_replacements WHERE id = ?")->execute([$id]);
    header("Location: dictionary.php?status=deleted");
    exit;
}

$words = $pdo->query("SELECT * FROM word_replacements ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary - WaNTg</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#060912] text-gray-200 font-sans">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div id="main-content" class="main-content">
        <div class="max-w-6xl mx-auto">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-3xl font-black text-white">Word Dictionary</h2>
                    <p class="text-gray-500 text-sm mt-1">Manage find & replace rules for your messages.</p>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Add Form -->
                <div class="lg:col-span-4">
                    <div class="bg-[#0d111c] border border-gray-800 p-8 rounded-[2.5rem] shadow-xl sticky top-24">
                        <h3 class="text-blue-400 font-bold uppercase text-[10px] tracking-widest mb-6">Add New Word</h3>
                        <form method="POST" class="space-y-5">
                            <input type="text" name="find_text" required placeholder="Find word..." class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-5 py-3 text-sm outline-none focus:border-blue-500 transition-all">
                            <input type="text" name="replace_with" placeholder="Replace with..." class="w-full bg-gray-900 border border-gray-800 rounded-2xl px-5 py-3 text-sm outline-none focus:border-blue-500 transition-all">
                            <button type="submit" name="add_word" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-2xl shadow-lg transition-all active:scale-95">Save Word</button>
                        </form>
                    </div>
                </div>

                <!-- Table Area -->
                <div class="lg:col-span-8">
                    <div class="bg-[#0d111c] border border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl">
                        <div class="p-6 border-b border-gray-800">
                            <input type="text" id="wordSearch" placeholder="Search in dictionary..." class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2 text-xs outline-none focus:border-blue-500 transition-all">
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left" id="wordsTable">
                                <thead>
                                    <tr class="bg-gray-800/30 text-gray-500 text-[10px] uppercase font-black border-b border-gray-800">
                                        <th class="px-6 py-5">Word Mapping</th>
                                        <th class="px-6 py-5">Status</th>
                                        <th class="px-6 py-5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800/40">
                                    <?php foreach ($words as $w): ?>
                                    <tr class="hover:bg-gray-800/10 transition-all">
                                        <td class="px-6 py-6">
                                            <div class="flex items-center gap-3">
                                                <span class="text-red-400 font-mono text-sm"><?php echo htmlspecialchars($w['find_text']); ?></span>
                                                <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <span class="text-green-400 font-mono text-sm"><?php echo htmlspecialchars($w['replace_with']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" <?php echo $w['is_enabled'] ? 'checked' : ''; ?> onchange="toggleWord(<?php echo $w['id']; ?>)" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button onclick="openEditModal(<?php echo $w['id']; ?>, '<?php echo addslashes($w['find_text']); ?>', '<?php echo addslashes($w['replace_with']); ?>')" class="text-gray-500 hover:text-blue-400 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2"/></svg>
                                                </button>
                                                <a href="?delete=<?php echo $w['id']; ?>" onclick="return confirm('Delete?')" class="text-red-900/50 hover:text-red-500 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
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
        </div>
    </div>

    <!-- ✏️ Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[2000] flex items-center justify-center p-4">
        <div class="bg-[#0d111c] border border-gray-800 p-8 rounded-[2.5rem] max-w-sm w-full shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-6">Edit Word Mapping</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="word_id" id="edit_word_id">
                <input type="text" name="find_text" id="edit_find" required class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-white outline-none">
                <input type="text" name="replace_with" id="edit_replace" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-white outline-none">
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="update_word" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-all">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="sidebar-overlay"></div>
    <script src="assets/js/scripts.js"></script>
    <script>
        function toggleWord(id) {
            fetch(`ajax_toggle_word.php?id=${id}`).then(res => res.json()).then(data => {
                if(data.status !== 'success') alert('Failed to update status');
            });
        }

        function openEditModal(id, find, replace) {
            document.getElementById('edit_word_id').value = id;
            document.getElementById('edit_find').value = find;
            document.getElementById('edit_replace').value = replace;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.getElementById('wordSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#wordsTable tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>
</body>
</html>