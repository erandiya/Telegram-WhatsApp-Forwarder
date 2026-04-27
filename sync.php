<?php
// 1. කාලය සහ මතක සැකසුම්
date_default_timezone_set('Asia/Colombo'); 
ini_set('memory_limit', '512M');
set_time_limit(0); 

// 2. පණිවිඩ යවන Function එක
function sendToWhatsAppAPI($number, $message, $filePath, $isSticker) {
    $url = 'http://127.0.0.1:3000/send-message';
    $data = ["number" => $number, "message" => $message, "filePath" => $filePath, "isSticker" => $isSticker];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40); 
    $res = curl_exec($ch);
    return $res;
}

include 'madeline.php';

// 3. Locking System
$lock_file = __DIR__ . '/sync_main.lock';
$lock_fp = fopen($lock_file, 'c+');
if (!flock($lock_fp, LOCK_EX | LOCK_NB)) {
    die("Main Sync is already running.\n");
}

// 4. Config කියවීම
include_once 'admin/includes/db.php'; 
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$api_id = (int)($config['API_ID'] ?? 0);
$api_hash = $config['API_HASH'] ?? "";

if (!$api_id || !$api_hash) {
    flock($lock_fp, LOCK_UN);
    die("Error: Telegram API credentials not found in database.\n");
}

// 5. MadelineProto ආරම්භ කිරීම
$settings = new \danog\MadelineProto\Settings\AppInfo();
$settings->setApiId($api_id);
$settings->setApiHash($api_hash);

$MadelineProto = new \danog\MadelineProto\API('session.madeline', $settings);
$MadelineProto->start();

// Heartbeat tracking variable
$last_heartbeat_sent_min = -1;

echo "System Started - Real-time monitoring active...\n";

// --- 6. අඛණ්ඩ ලූපය (Infinite Loop) ---
while (true) {
    $current_time = time();
    
    // පද්ධතිය සජීවී බව ඩේටාබේස් එකේ ලකුණු කිරීම
    $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('tg_last_heartbeat', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$current_time]);
    file_put_contents(__DIR__ . '/last_run_timestamp.txt', $current_time);

    // Heartbeat පරීක්ෂාව
    include 'heartbeat.php';

    // ඩේටාබේස් එකෙන් නීති ලබා ගැනීම
    $replacements = $pdo->query("SELECT find_text, replace_with FROM word_replacements WHERE `is_enabled`=1")->fetchAll(PDO::FETCH_KEY_PAIR);
    $mappings = $pdo->query("SELECT * FROM forward_mappings WHERE `is_enabled`=1")->fetchAll(PDO::FETCH_ASSOC);

    $download_dir = __DIR__ . '/downloads';
    if (!file_exists($download_dir)) mkdir($download_dir, 0777, true);

    foreach ($mappings as $map) {
        $tg_src = trim($map['source_tg_id']);
        $target_dest = trim($map['target_id']);
        $last_id = (int)$map['last_msg_id'];

        try {
            try { $MadelineProto->getInfo($tg_src); } catch (\Exception $e) { continue; }

            $messages = $MadelineProto->messages->getHistory([
                'peer' => $tg_src,
                'limit' => 10,
                'offset_id' => $last_id,
                'add_offset' => -10,
                'min_id' => $last_id
            ]);

            if (isset($messages['messages']) && count($messages['messages']) > 0) {
                foreach (array_reverse($messages['messages']) as $msg) {
                    if ($msg['id'] <= $last_id) continue;

                    echo "     * Forwarding ID: " . $msg['id'] . " from $tg_src\n";
                    $text = ""; $filePath = null; $isSticker = false;

                    if ($msg['_'] === 'messageService') {
                        if ($map['block_service_msgs']) {
                            $pdo->prepare("UPDATE forward_mappings SET last_msg_id = ? WHERE id = ?")->execute([$msg['id'], $map['id']]);
                            $last_id = $msg['id'];
                            continue;
                        }
                        try {
                            $user_info = $MadelineProto->getInfo($msg['from_id'] ?? $msg['peer_id']);
                            $from_name = trim(($user_info['User']['first_name'] ?? '') . ' ' . ($user_info['User']['last_name'] ?? ''));
                        } catch (\Exception $e) { $from_name = "Someone"; }
                        $text = "🔔 $from_name: " . ($msg['action']['_'] ?? 'Notification');
                    } else {
                        $text = $msg['message'] ?? '';
                        if ($map['is_replace_enabled'] && !empty($replacements)) {
                            foreach ($replacements as $old => $new) { $text = str_ireplace($old, $new, $text); }
                        }
                        if (isset($msg['media'])) {
                            try {
                                $filePath = $MadelineProto->downloadToDir($msg, $download_dir);
                                if (isset($msg['media']['document']['attributes'])) {
                                    foreach ($msg['media']['document']['attributes'] as $attr) {
                                        if ($attr['_'] === 'documentAttributeSticker') { $isSticker = true; break; }
                                    }
                                }
                            } catch (\Exception $e) {}
                        }
                    }

                    $isSent = false;
                    if ($map['target_platform'] === 'WA') {
                        $response = sendToWhatsAppAPI($target_dest, $text, $filePath, $isSticker);
                        $resData = json_decode($response, true);
                        if (isset($resData['status']) && $resData['status'] === 'success') $isSent = true;
                    } else {
                        try {
                            if ($isSticker && $filePath) {
                                $MadelineProto->messages->sendMedia(['peer' => $target_dest, 'media' => ['_' => 'inputMediaUploadedDocument', 'file' => $filePath, 'attributes' => [['_' => 'documentAttributeSticker']]]]);
                            } elseif ($filePath) {
                                $MadelineProto->messages->sendMedia(['peer' => $target_dest, 'media' => ['_' => 'inputMediaUploadedPhoto', 'file' => $filePath], 'message' => $text]);
                            } else {
                                $MadelineProto->messages->sendMessage(['peer' => $target_dest, 'message' => $text]);
                            }
                            if ($filePath && file_exists($filePath)) unlink($filePath);
                            $isSent = true;
                        } catch (\Exception $e) {}
                    }

                    if ($isSent) {
                        $pdo->prepare("UPDATE forward_mappings SET last_msg_id = ? WHERE id = ?")->execute([$msg['id'], $map['id']]);
                        $last_id = $msg['id'];
                        sleep(1); 
                    } else { break 2; }
                }
            }
        } catch (\Exception $e) { echo "Error on $tg_src\n"; }
    }

    if (function_exists('gc_collect_cycles')) gc_collect_cycles();
    sleep(3); 
}
?>