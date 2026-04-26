#!/bin/bash
FILE=/var/www/msg-sync/last_run_timestamp.txt
CURRENT_TIME=$(date +%s)
LOG_FILE=/var/www/msg-sync/watchdog.log

if [ -f "$FILE" ]; then
    LAST_RUN=$(cat "$FILE")
    DIFF=$((CURRENT_TIME - LAST_RUN))
    
    # මිනිත්තු 4කට වඩා පරණ නම් (240 seconds)
    if [ $DIFF -gt 240 ]; then
        # මිනිසාට කියවිය හැකි දිනය සහ වේලාව සැකසීම
        READABLE_TIME=$(date '+%Y-%m-%d %H:%M:%S')
        echo "[$READABLE_TIME] System freeze detected ($DIFF sec). Running Maintenance..." >> "$LOG_FILE"
        
        # නඩත්තු ස්ක්‍රිප්ට් එක රන් කිරීම
        /bin/bash /var/www/msg-sync/maintenance.sh
        
        echo "[$READABLE_TIME] System Maintenance Compleeted ($DIFF sec). Done, System Online Now..." >> "$LOG_FILE"
        echo "_________________________________________________________________________________________" >> "$LOG_FILE"
        # නැවත රන් වූ පසු ටයිම් එක අප්ඩේට් කරන්න
        echo $(date +%s) > "$FILE"
    fi
else
    # ෆයිල් එක නැතිනම් අලුතින් හදන්න
    echo $CURRENT_TIME > "$FILE"
fi

