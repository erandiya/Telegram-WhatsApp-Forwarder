#!/bin/bash
cd /var/www/msg-sync

# 1. සියල්ල නැවැත්වීම
/usr/bin/pm2 stop all
pkill -9 -f php
pkill -9 -f madeline-ipc
pkill -9 -f chrome
pkill -9 -f chromium
pkill -9 -f node

# 2. පිරිසිදු කිරීම (Lock, Temp files, IPC files)
rm -f /var/www/msg-sync/*.lock
rm -f /tmp/tg_sync.lock
rm -f /tmp/replace_sync.lock
rm -rf /var/www/msg-sync/downloads/*
rm -rf /var/www/msg-sync/session.madeline/*.ipc
rm -f /var/www/msg-sync/MadelineProto.log

# 3. පද්ධතිය නැවත පණ ගැන්වීම
/usr/bin/pm2 restart all

mysql -u root -e "DELETE FROM sync_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)" msg_sync_db

echo "Full Maintenance & Restart completed at $(date)" >> /var/www/msg-sync/maintenance.log

timedatectl set-timezone Asia/Colombo

