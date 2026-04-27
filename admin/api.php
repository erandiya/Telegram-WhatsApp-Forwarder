<?php
// admin/api.php
include_once 'includes/db.php';
include_once 'includes/functions.php';

header('Content-Type: application/json');

$tgReal = getRealTelegramStatus($pdo);
$wa = checkServiceStatus(3000);
$sys = getTelegramStatus(); // Cron/Loop check
$disk = getDiskUsage();

echo json_encode([
    'wa_status' => $wa,
    'sys_status' => $sys['status'],
    'tg_api_status' => $tgReal['status'],
    'disk_usage' => $disk
]);