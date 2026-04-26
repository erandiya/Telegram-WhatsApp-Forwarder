<?php
// includes/functions.php

/**
 * Port එකක් හරහා සේවාව සජීවීදැයි පරීක්ෂා කිරීම
 * (මෙය PM2 පර්මිෂන් ගැටළු මඟහරවයි)
 */
function checkServiceStatus($port) {
    $url = "http://127.0.0.1:$port/status";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); // තත්පර 2ක් ඇතුළත පිළිතුරක් ලැබිය යුතුයි
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return "online";
    }
    return "offline";
}

// Disk Usage ලබා ගැනීම
function getDiskUsage() {
    $free = disk_free_space("/");
    $total = disk_total_space("/");
    $used = $total - $free;
    return round(($used / $total) * 100, 1);
}


/**
 * Telegram පද්ධතියේ තත්ත්වය පරීක්ෂා කිරීම
 * last_run_timestamp.txt පරීක්ෂා කරයි
 */
function getTelegramStatus() {
    $file = __DIR__ . '/../../last_run_timestamp.txt';
    
    if (!file_exists($file)) {
        return ['status' => 'unknown', 'time' => 'Never'];
    }

    $lastRun = (int)file_get_contents($file);
    $diff = time() - $lastRun;

    if ($diff < 120) { // විනාඩි 2කට වඩා අඩු නම්
        return ['status' => 'online', 'time' => $diff . 's ago'];
    } elseif ($diff < 300) { // විනාඩි 5කට වඩා අඩු නම්
        return ['status' => 'warning', 'time' => round($diff/60) . 'm ago'];
    } else {
        return ['status' => 'offline', 'time' => round($diff/60) . 'm ago'];
    }
}

/**
 * ටෙලිග්‍රෑම් සැබෑ තත්ත්වය පරීක්ෂා කිරීම
 * (DB Heartbeat + Linux Process පරීක්ෂාව)
 */
function getRealTelegramStatus($pdo) {
    // 1. ඩේටාබේස් එකෙන් අවසාන Heartbeat එක ලබා ගැනීම
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'tg_last_heartbeat'");
    $stmt->execute();
    $lastHeartbeat = (int)$stmt->fetchColumn();

    // 2. පද්ධතියේ Madeline process එකක් රන් වෙනවාදැයි බැලීම
    $processCheck = shell_exec("pgrep -f madeline-ipc");
    
    $diff = time() - $lastHeartbeat;

    // තත්පර 90කට වඩා පරණ නම් හෝ process එක නැතිනම් Offline ලෙස සලකයි
    if ($diff < 90 && !empty($processCheck)) {
        return ['status' => 'online', 'time' => $diff . 's ago'];
    } elseif (!empty($processCheck)) {
        return ['status' => 'stalled', 'time' => $diff . 's ago'];
    } else {
        return ['status' => 'offline', 'time' => $diff . 's ago'];
    }
}

?>
