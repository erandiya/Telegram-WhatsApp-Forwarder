<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE notification_targets SET is_enabled = NOT is_enabled WHERE id = ?");
    echo json_encode(['status' => $stmt->execute([$id]) ? 'success' : 'error']);
}
?>
