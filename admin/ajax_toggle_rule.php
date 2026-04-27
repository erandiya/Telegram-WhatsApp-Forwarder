<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // දැනට පවතින තත්ත්වය මාරු කිරීම (0 -> 1 හෝ 1 -> 0)
    $stmt = $pdo->prepare("UPDATE forward_mappings SET is_enabled = NOT is_enabled WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>