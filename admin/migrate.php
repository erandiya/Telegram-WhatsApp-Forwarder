<?php
include_once 'includes/db.php';

echo "--- Starting Migration ---\n";

// 1. Telegram API Config Migration (tg-api-hash-id.conf)
$tg_conf_path = __DIR__ . '/../tg-api-hash-id.conf';
if (file_exists($tg_conf_path)) {
    $tg_conf = parse_ini_file($tg_conf_path);
    foreach ($tg_conf as $k => $v) {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $stmt->execute([$k, $v]);
    }
    echo "[+] API Settings migrated.\n";
}

// 2. Word Replacements Migration (tg-to-wa-text-replace-dictionary.conf)
$replace_conf_path = __DIR__ . '/../tg-to-wa-text-replace-dictionary.conf';
if (file_exists($replace_conf_path)) {
    $replace_conf = file($replace_conf_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($replace_conf as $line) {
        $line = trim(explode('//', $line)[0]);
        if (preg_match_all("/'([^']+)'/", $line, $matches) && count($matches[1]) >= 2) {
            $stmt = $pdo->prepare("INSERT INTO word_replacements (find_text, replace_with) VALUES (?, ?)");
            $stmt->execute([$matches[1][0], $matches[1][1]]); // මෙතැන තිබූ දෝෂය නිවැරදි කරන ලදී
        }
    }
    echo "[+] Word dictionary migrated.\n";
}

// 3. Mapping Migration (Conf files + JSON Last IDs)
$json_path = __DIR__ . '/../last_sync_data.json';
$last_sync = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];

$mapping_files = [
    ['file' => 'tg-to-wa-forwarding-whatsapp-channels.conf', 'platform' => 'WA', 'replace' => 0, 'block' => 0, 'prefix' => 'wa_'],
    ['file' => 'tg-to-wa-forwarding-whatsapp-channels-with-replace-text.conf', 'platform' => 'WA', 'replace' => 1, 'block' => 1, 'prefix' => 'wa_rep_'],
    ['file' => 'tg-to-tg-forwarding-telegrame-channels.conf', 'platform' => 'TG', 'replace' => 0, 'block' => 0, 'prefix' => 'tg_'],
    ['file' => 'tg-to-tg-forwarding-telegrame-channels-with-replace-text.conf', 'platform' => 'TG', 'replace' => 1, 'block' => 1, 'prefix' => 'tg_rep_']
];

foreach ($mapping_files as $m) {
    $path = __DIR__ . '/../' . $m['file'];
    if (file_exists($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim(explode('//', $line)[0]);
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $tg_src = trim($parts[0]);
                $target = trim($parts[1]);
                
                // පරණ JSON එකෙන් අදාළ ID එක සොයා ගැනීම
                $map_key = $m['prefix'] . str_replace(['-', ' ', '@', '.'], '', $tg_src) . "_to_" . str_replace(['-', ' ', '@', '.'], '', $target);
                $last_id = isset($last_sync[$map_key]) ? (int)$last_sync[$map_key] : 0;

                $stmt = $pdo->prepare("INSERT INTO forward_mappings (source_tg_id, target_id, target_platform, is_replace_enabled, block_service_msgs, last_msg_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tg_src, $target, $m['platform'], $m['replace'], $m['block'], $last_id]);
            }
        }
    }
}

echo "[+] Channel mappings and Last IDs migrated successfully.\n";
echo "--- Migration Completed ---\n";
?>
