<?php
// admin/api_pm2_logs.php
include_once 'includes/functions.php';
checkAuth();
header('Content-Type: application/json');

$app = $_GET['app'] ?? 'whatsapp-api';
$search = $_GET['search'] ?? '';

// PM2 ලොග් පවතින නිවැරදි පාරවල්
$out_log = "/home/cito/.pm2/logs/{$app}-out.log";
$err_log = "/home/cito/.pm2/logs/{$app}-error.log";

$output = [];

function readLogFile($path, $type) {
    if (!file_exists($path)) return [];
    // අවසාන පේළි 100 ලබා ගැනීම
    $content = shell_exec("tail -n 100 " . escapeshellarg($path));
    $lines = array_filter(explode("\n", trim($content)));
    $res = [];
    foreach ($lines as $line) {
        $res[] = ['type' => $type, 'message' => $line];
    }
    return $res;
}

// ලොග් වර්ග දෙකම කියවීම
$logs = array_merge(readLogFile($out_log, 'OUT'), readLogFile($err_log, 'ERR'));

// පණිවිඩ පෙරීම (Search)
foreach (array_reverse($logs) as $entry) {
    if (!empty($search) && stripos($entry['message'], $search) === false) continue;
    $output[] = $entry;
}

echo json_encode(array_slice($output, 0, 100));
?>