<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $src   = $_POST['source_id'];
        $dest  = $_POST['target_id'];
        $plat  = $_POST['target_platform'];
        $rep   = isset($_POST['replace_enabled']) ? 1 : 0;
        $int_rep = isset($_POST['intelligent_replace']) ? 1 : 0; // අලුත් අගය
        $block = isset($_POST['block_service']) ? 1 : 0;

        if (empty($src) || empty($dest)) {
            throw new Exception("Source and Target IDs are required.");
        }

        $stmt = $pdo->prepare("INSERT INTO forward_mappings (source_tg_id, target_id, target_platform, is_replace_enabled, is_intelligent_replace, block_service_msgs) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$src, $dest, $plat, $rep, $int_rep, $block])) {
            header("Location: forwarding.php?status=success");
        } else {
            header("Location: forwarding.php?status=error");
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
    exit;
}
?>