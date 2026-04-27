<?php
// /var/www/msg-sync/admin/ajax_sync.php
header('Content-Type: application/json');

// පද්ධතිය දැනටමත් රන් වෙනවාදැයි බැලීම
$check = shell_exec("ps aux | grep sync-entities.php | grep -v grep");
if (!empty($check)) {
    echo json_encode(['status' => 'running']);
    exit;
}

// පසුබිමින් රන් කරන කමාන්ඩ් එක (Path එක නිවැරදියි)
// මෙහිදී pm2 stop කිරීම අවශ්‍ය නැත, මන්ද Madeline v8+ බොහෝවිට Shared Session වලට ඉඩ දෙයි
$cmd = "php " . __DIR__ . "/sync-entities.php";

exec($cmd . " > /dev/null 2>&1 &");

echo json_encode(['status' => 'started']);
?>