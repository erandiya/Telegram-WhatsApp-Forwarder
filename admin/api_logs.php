<?php
// admin/api_logs.php
include_once 'includes/db.php';
include_once 'includes/functions.php';
checkAuth();

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$limit  = (int)($_GET['limit'] ?? 50);

$query = "SELECT * FROM sync_logs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (source_name LIKE ? OR target_name LIKE ? OR message_preview LIKE ? OR tg_msg_id LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY created_at DESC LIMIT $limit";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($logs);

?>