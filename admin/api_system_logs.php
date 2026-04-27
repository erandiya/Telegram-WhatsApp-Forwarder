<?php
// admin/api_system_logs.php
include_once 'includes/functions.php';
checkAuth();
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'maintenance';
$search = $_GET['search'] ?? '';
$file_path = ($type === 'watchdog') ? __DIR__ . '/../watchdog.log' : __DIR__ . '/../maintenance.log';

if (!file_exists($file_path)) {
    echo json_encode([]); exit;
}

$lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$output = [];

foreach (array_reverse($lines) as $line) {
    // සර්ච් එකට ගැලපෙනවාදැයි බැලීම
    if (!empty($search) && stripos($line, $search) === false) continue;

    if ($type === 'watchdog' && preg_match('/^\[(.*?)\] (.*)$/', $line, $matches)) {
        $output[] = ['time' => $matches[1], 'event' => $matches[2]];
    } else {
        $output[] = ['time' => 'System Log', 'event' => $line];
    }
}
echo json_encode(array_slice($output, 0, 100)); // පේළි 100කට සීමා කරයි
?>