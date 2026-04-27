<?php
// admin/ajax_clear_logs.php
include_once 'includes/functions.php';
checkAuth();
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$file = '';

// Path එක නිවැරදි කරන ලදී (../)
if ($type === 'maintenance') {
    $file = __DIR__ . '/../maintenance.log';
} elseif ($type === 'watchdog') {
    $file = __DIR__ . '/../watchdog.log';
}

if ($file && file_exists($file)) {
    // ෆයිල් එකට ලිවීමට අවසර තිබේදැයි පරීක්ෂා කරයි
    if (is_writable($file)) {
        file_put_contents($file, "");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Permission Denied: Run chmod 666 on log files.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Log file not found at: ' . $file]);
}
?>