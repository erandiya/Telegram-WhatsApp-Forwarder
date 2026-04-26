<?php
include_once 'includes/db.php';
include_once 'includes/functions.php';

header('Content-Type: application/json');

$tgReal = getRealTelegramStatus($pdo);
$wa = checkServiceStatus(3000);
$sys = getTelegramStatus(); // Cron check

echo json_encode([
    'wa_status' => $wa,
    'sys_status' => $sys['status'],
    'sys_time'   => $sys['time'],
    'tg_api_status' => $tgReal['status'],
    'tg_api_time'   => $tgReal['time']
]);