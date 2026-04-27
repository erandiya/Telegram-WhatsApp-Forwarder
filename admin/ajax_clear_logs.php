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

// admin/ajax_clear_logs.php හි අවසානයට එක් කරන්න
if ($_GET['type'] === 'pm2') {
    // PM2 ලොග් සම්පූර්ණයෙන් පිරිසිදු කරයි
    shell_exec("pm2 flush");
    echo json_encode(['status' => 'success']);
    exit;
}
?>