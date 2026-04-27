<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE word_replacements SET is_enabled = NOT is_enabled WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>