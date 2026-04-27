<?php
// පද්ධතියේ වත්මන් මිනිත්තුව ලබා ගැනීම
$current_minute = date('i');
$current_full_time = date('H:i');

// දැනුම්දීම යැවිය යුතු මිනිත්තු ලැයිස්තුව
$allowed_minutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

// පද්ධතියේ මේ මිනිත්තුවට අදාලව දැනටමත් යවා ඇත්දැයි පරීක්ෂා කිරීම
// $last_heartbeat_sent_min විචල්‍යය sync.php හි අර්ථ දක්වා ඇත
if (in_array($current_minute, $allowed_minutes) && $current_minute !== $last_heartbeat_sent_min) {
    
    echo "   [i] Heartbeat trigger detected at $current_full_time\n";

    $wa_logo = __DIR__ . '/whatsapp1.png';
    $tg_logo = __DIR__ . '/telegram1.png';

    // ඩේටාබේස් එකෙන් targets ලබා ගැනීම
    // heartbeat.php තුළ වෙනස් කරන්න
    $stmt = $pdo->query("SELECT * FROM notification_targets WHERE is_enabled = 1");
    $targets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($targets as $row) {
        $target_id = trim($row['target_id']);
        
        if ($row['platform'] === 'WA') {
            $wa_msg = "✅ *" . date('d') . "* -> " . $current_full_time;
            $img = file_exists($wa_logo) ? $wa_logo : null;
            $res = sendToWhatsAppAPI($target_id, $wa_msg, $img, false);
            echo "   [+] WhatsApp Heartbeat to $target_id: $res\n";
        } 
        elseif ($row['platform'] === 'TG' && $row['is_test_channel'] == 1) {
            try {
                $tg_msg = "🫵 *" . date('d') . "* -> " . $current_full_time;
                if (file_exists($tg_logo)) {
                    $MadelineProto->messages->sendMedia([
                        'peer'    => $target_id,
                        'media'   => ['_' => 'inputMediaUploadedPhoto', 'file' => $tg_logo],
                        'message' => $tg_msg
                    ]);
                } else {
                    $MadelineProto->messages->sendMessage(['peer' => $target_id, 'message' => $tg_msg]);
                }
                echo "   [+] Telegram Heartbeat sent to $target_id\n";
            } catch (\Exception $e) {
                echo "   [!] TG Heartbeat Error on $target_id: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // යැවූ මිනිත්තුව සටහන් කර ගැනීම
    $last_heartbeat_sent_min = $current_minute;
}
?>