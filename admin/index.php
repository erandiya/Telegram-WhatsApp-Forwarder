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
    <title>WaNTg Control Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#060912] text-gray-200 font-sans">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            
            <header class="mb-10">
                <h2 class="text-3xl font-bold text-white">System Dashboard</h2>
                <p class="text-gray-500 mt-2">Real-time system monitoring is active and synced.</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Links Card -->
                <div class="bg-gray-800/40 p-6 rounded-3xl border border-gray-800 shadow-xl">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest">Forwarding Links</h3>
                    <p class="text-4xl font-black mt-3 text-blue-400">
                        <?php echo $pdo->query("SELECT COUNT(*) FROM forward_mappings")->fetchColumn(); ?>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay"></div>

    <script src="assets/js/scripts.js"></script>
</body>
</html>