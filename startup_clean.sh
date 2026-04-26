#!/bin/bash

# 1. සියලුම පරණ PHP සහ Chrome Processes නතර කිරීම
killall -9 php 2>/dev/null
pkill -9 -f chrome 2>/dev/null

# 2. MadelineProto හි හිරවුණු Socket සහ IPC ෆයිල් සහමුලින්ම මකා දැමීම
# (මෙය තමයි 'endpoint does not exist' සහ timeout දෝෂවලට ස්ථිර විසඳුම)
if [ -d "/var/www/msg-sync/session.madeline" ]; then
    find /var/www/msg-sync/session.madeline -name "*.ipc" -delete 2>/dev/null
    find /var/www/msg-sync/session.madeline -name "*.lock" -delete 2>/dev/null
fi

# 3. ප්‍රධාන ෆෝල්ඩරයේ ඇති Lock ෆයිල්ස් මැකීම
rm -f /var/www/msg-sync/*.lock 2>/dev/null
rm -f /tmp/tg_sync.lock 2>/dev/null
rm -f /tmp/sync.lock 2>/dev/null

# 4. Downloads පිරිසිදු කිරීම
rm -rf /var/www/msg-sync/downloads/*

# 5. PM2 නැවත ආරම්භ කිරීම
/usr/bin/pm2 restart all

echo "Deep cleaning completed at $(date)" >> /var/www/msg-sync/startup.log
