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
<body class="bg-[#060912] text-gray-200 font-sans overflow-x-hidden">

    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div id="main-content" class="main-content">
        <div class="max-w-7xl mx-auto">
            
            <header class="mb-10">
                <h2 class="text-3xl font-black text-white">System Dashboard</h2>
                <p class="text-gray-500 mt-2">Real-time system monitoring is active and synced.</p>
            </header>

            <!-- Grid Container for all cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                
                <!-- Card 1: Forwarding Links -->
                <div class="bg-gray-800/40 p-6 rounded-3xl border border-gray-800 shadow-xl backdrop-blur-sm">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest">Forwarding Links</h3>
                    <p class="text-4xl font-black mt-3 text-blue-400">
                        <?php echo $pdo->query("SELECT COUNT(*) FROM forward_mappings")->fetchColumn(); ?>
                    </p>
                    <p class="text-[10px] text-gray-600 mt-2 italic">Active synchronization paths</p>
                </div>

                <!-- Card 2: Success Messages Today -->
                <div class="bg-gray-800/40 p-6 rounded-3xl border border-gray-800 shadow-xl backdrop-blur-sm">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest">Messages Today</h3>
                    <p class="text-4xl font-black mt-3 text-green-400">
                        <?php 
                            echo $pdo->query("SELECT COUNT(*) FROM sync_logs WHERE status='SUCCESS' AND DATE(created_at) = CURDATE()")->fetchColumn(); 
                        ?>
                    </p>
                    <p class="text-[10px] text-gray-600 mt-2">Successfully delivered today</p>
                </div>

                <!-- Card 3: Failed Messages Today (අලුතින් එක් කළා) -->
                <div class="bg-gray-800/40 p-6 rounded-3xl border border-gray-800 shadow-xl backdrop-blur-sm">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest">Failed Attempts</h3>
                    <p class="text-4xl font-black mt-3 text-red-500">
                        <?php 
                            echo $pdo->query("SELECT COUNT(*) FROM sync_logs WHERE status='FAILED' AND DATE(created_at) = CURDATE()")->fetchColumn(); 
                        ?>
                    </p>
                    <p class="text-[10px] text-gray-600 mt-2">Issues detected today</p>
                </div>

                <!-- Card 4: Dictionary Words (අලුතින් එක් කළා) -->
                <div class="bg-gray-800/40 p-6 rounded-3xl border border-gray-800 shadow-xl backdrop-blur-sm">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-widest">Dictionary</h3>
                    <p class="text-4xl font-black mt-3 text-purple-400">
                        <?php echo $pdo->query("SELECT COUNT(*) FROM word_replacements")->fetchColumn(); ?>
                    </p>
                    <p class="text-[10px] text-gray-600 mt-2">Active word replacement rules</p>
                </div>

            </div>

        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay"></div>

    <script src="assets/js/scripts.js"></script>
</body>
</html>