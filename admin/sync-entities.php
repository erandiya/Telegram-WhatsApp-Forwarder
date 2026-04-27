<?php
// /var/www/msg-sync/admin/sync-entities.php
date_default_timezone_set('Asia/Colombo');
ini_set('memory_limit', '1024M');
chdir(__DIR__);

include_once 'includes/db.php';
include_once '../madeline.php';

$avatar_dir = __DIR__ . '/assets/img/avatars';
if (!file_exists($avatar_dir)) mkdir($avatar_dir, 0777, true);

echo "[1/4] Loading Telegram Settings...\n";
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$api_id = (int)($config['API_ID'] ?? 0);
$api_hash = $config['API_HASH'] ?? "";

echo "[2/4] Starting MadelineProto...\n";
$settings = new \danog\MadelineProto\Settings();
$settings->getAppInfo()->setApiId($api_id);
$settings->getAppInfo()->setApiHash($api_hash);

try {
    $MadelineProto = new \danog\MadelineProto\API('../session.madeline', $settings);
    $MadelineProto->start();
    
    echo "[3/4] Fetching Telegram Dialogs...\n";
    $res = $MadelineProto->messages->getDialogs(['limit' => 100]);

    if (isset($res['chats'])) {
        foreach ($res['chats'] as $chat) {
            try {
                if (in_array($chat['_'], ['chat', 'channel'])) {
                    
                    // ID Prefix Fix
                    $raw_id = preg_replace('/^-100/', '', (string)$chat['id']);
                    $raw_id = ltrim($raw_id, '-');
                    $remote_id = ($chat['_'] === 'channel') ? '-100' . $raw_id : '-' . $raw_id;

                    $title = $chat['title'] ?? 'Unknown';
                    $image_rel_path = null;

                    // --- 1. පින්තූරය ඩවුන්ලෝඩ් කරන අලුත්ම ක්‍රමය (Direct Stream) ---
                    if (isset($chat['photo']) && $chat['photo']['_'] === 'chatPhoto') {
                        echo "      * Fetching TG DP for: $title\n";
                        try {
                            $file_name = 'tg_' . abs((int)$remote_id) . '.jpg';
                            $full_save_path = $avatar_dir . '/' . $file_name;
                            
                            // GetFullInfo හරහා ගෙන කෙලින්ම ගොනුවකට ලිවීම
                            $full_info = $MadelineProto->getFullInfo($remote_id);
                            if (isset($full_info['Chat']['photo']['photo_small'])) {
                                $info = $MadelineProto->downloadToFile($full_info['Chat']['photo']['photo_small'], $full_save_path);
                                if (file_exists($full_save_path) && filesize($full_save_path) > 0) {
                                    $image_rel_path = 'admin/assets/img/avatars/' . $file_name;
                                    echo "        [+] Success: $file_name\n";
                                }
                            }
                        } catch (\Exception $e) { 
                            echo "        [!] TG Photo Error: " . $e->getMessage() . "\n";
                        }
                        
                        // මතකය වහාම නිදහස් කිරීම
                        unset($full_info);
                    }

                    $stmt = $pdo->prepare("INSERT INTO platform_entities (platform, remote_id, title, image_path, can_send) 
                        VALUES ('TG', ?, ?, ?, 1) ON DUPLICATE KEY UPDATE title=VALUES(title), image_path=VALUES(image_path)");
                    $stmt->execute([$remote_id, $title, $image_rel_path]);
                    echo "   ✅ TG Synced: $title\n";
                    
                    // සෑම චැනල් එකකට පසුවම කුඩා විවේකයක් (RAM ඉතිරි කිරීමට)
                    usleep(100000); 
                }
            } catch (\Exception $e) { continue; }
        }
    }
    unset($res);

    echo "[4/4] Syncing WhatsApp Data...\n";
    $wa_url = 'http://127.0.0.1:3000/list-all-entities';
    $wa_json = @file_get_contents($wa_url);
    if ($wa_json) {
        $wa_data = json_decode($wa_json, true);
        foreach ($wa_data as $chat) {
            $wa_img_rel = null;
            if (!empty($chat['pic'])) {
                try {
                    $img_name = 'wa_' . md5($chat['id']) . '.jpg';
                    $full_save_path = $avatar_dir . '/' . $img_name;
                    
                    // WhatsApp පින්තූරය Curl හරහා ලබා ගැනීම (ස්ථායී ක්‍රමය)
                    $ch = curl_init($chat['pic']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                    $img_data = curl_exec($ch);
                    curl_close($ch);

                    if ($img_data) {
                        file_put_contents($full_save_path, $img_data);
                        $wa_img_rel = 'admin/assets/img/avatars/' . $img_name;
                    }
                } catch (\Exception $e) {}
            }

            $stmt = $pdo->prepare("INSERT INTO platform_entities (platform, remote_id, title, image_path, can_send) 
                VALUES ('WA', ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), image_path=VALUES(image_path), can_send=VALUES(can_send)");
            $stmt->execute([$chat['id'], $chat['name'], $wa_img_rel, $chat['canSend'] ? 1 : 0]);
            echo "   ✅ WA Synced: {$chat['name']}\n";
        }
    }

    echo "\n--- ALL DATA SYNCED SUCCESSFULLY ---\n";

} catch (\Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
}

// Memory Cleanup & Exit (Killed දෝෂය වැළැක්වීමට)
if (function_exists('gc_collect_cycles')) gc_collect_cycles();
exit(0);
?>