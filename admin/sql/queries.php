<?php
// sql/queries.php
$sql_create_logs_table = "CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tg_id VARCHAR(50),
    wa_id VARCHAR(50),
    message_type VARCHAR(20),
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_get_recent_logs = "SELECT * FROM sync_logs ORDER BY created_at DESC LIMIT 10";
$sql_get_stats = "SELECT status, COUNT(*) as count FROM sync_logs GROUP BY status";

