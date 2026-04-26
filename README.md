# 🚀 Telegram to WhatsApp Multi-Channel Forwarder 🤖

A robust, server-side automation system to forward messages from multiple Telegram channels/groups to specific WhatsApp numbers or groups. Supports Text, Images, Videos, Animated Stickers (TGS), and Service Messages.

ටෙලිග්‍රෑම් චැනල් හෝ ගෘප් කිහිපයක පණිවිඩ (Text, Images, Stickers, Service Messages) ස්වයංක්‍රීයව අදාළ වට්ස්ඇප් අංක හෝ ගෘප් වෙත යොමු කරන පද්ධතියකි. මෙහි ඇනිමේටඩ් ස්ටිකර් (TGS) වට්ස්ඇප් වලට ගැලපෙන සේ පරිවර්තනය කිරීමේ පහසුකමද ඇතුළත් වේ.

---

## ✨ Features / විශේෂාංග

*   **Multi-Channel Mapping:** Forward from multiple sources to different destinations via `channels.conf`.
    (චැනල් කිහිපයක පණිවිඩ ස්ථාන කිහිපයකට වෙන් වෙන්ව යැවීමේ හැකියාව).
*   **Animated Sticker Conversion:** Built-in TGS to WebP conversion using Python and FFmpeg.
    (ඇනිමේටඩ් ස්ටිකර් වට්ස්ඇප් වෙත ස්වයංක්‍රීයව පරිවර්තනය කර යැවීම).
*   **Service Message Support:** Forwards notifications about new members, title changes, etc.
    (නව සාමාජිකයින් එකතු වීම්, නම වෙනස් කිරීම් වැනි සියලු දැනුම්දීම් ලැබීම).
*   **Anti-Duplication & Locking:** Advanced system to prevent receiving the same message twice.
    (එකම පණිවිඩය දෙපාරක් ලැබීම වැළැක්වීමේ තාක්ෂණය).
*   **Self-Healing:** Automatic maintenance script to clear locks and restart services.
    (පද්ධතිය හිරවීම් වළක්වා ස්වයංක්‍රීයව නඩත්තු වීම).
*   **Text Replacement:** Supports automatic word replacement (e.g., changing 'Long' to 'Buy') via `replace-text.conf`.
    (පණිවිඩවල ඇති වචන ස්වයංක්‍රීයව වෙනස් කිරීමේ හැකියාව - උදා: 'Long' යන්න 'Buy' ලෙස මාරු කිරීම).

---

## 🛠 Prerequisites / අවශ්‍යතා

*   **OS:** Ubuntu VPS (22.04 or 24.04 recommended).
*   **Specs:** 1GB RAM / 20GB+ Disk Space.
*   **Accounts:** Telegram API ID/Hash and a WhatsApp account.

---

## 🚀 Installation Guide / ස්ථාපන පටිපාටිය

### 1. Update System & Install PHP (පද්ධතිය යාවත්කාලීන කර PHP ස්ථාපනය)
First, add the PHP repository and install PHP 8.2 with necessary extensions.
(පළමුව PHP Repository එක එකතු කර PHP 8.2 සහ අවශ්‍ය Extensions ස්ථාපනය කරන්න).

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-cli php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip php8.2-gmp php8.2-ffi php8.2-common
# Enable FFI in PHP
echo "ffi.enable=true" | sudo tee -a /etc/php/8.2/cli/php.ini 
```

---

### 2. Install Node.js & Media Tools (Node.js සහ මීඩියා මෙවලම් ස්ථාපනය)
Install Node.js for WhatsApp connection and FFmpeg/Lottie for sticker processing.
(වට්ස්ඇප් සම්බන්ධතාවය සහ ස්ටිකර් පරිවර්තනය සඳහා අවශ්‍ය මෘදුකාංග ස්ථාපනය කරන්න).

```Bash
# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs ffmpeg webp libcairo2-dev libpango1.0-dev

# Install Python Lottie Converter for Stickers
sudo apt install -y python3-pip python3-venv
pip install lottie cairosvg --break-system-packages
```

---

### 3. Setup Project Folder (ෆෝල්ඩරය සකස් කිරීම)
Create the folder, download MadelineProto, and install Node packages.
(ෆෝල්ඩරය සාදා MadelineProto බාගත කර අවශ්‍ය packages ස්ථාපනය කරන්න).

```Bash
mkdir ~/WaNTg && cd ~/WaNTg
wget https://phar.madelineproto.xyz/madeline.php
npm init -y
npm install whatsapp-web.js express qrcode-terminal
sudo npm install -g pm2
```

### ⚙️ Configuration Setup (සැකසුම් පටිපාටිය)

After cloning the repository, you need to create the configuration files by copying the examples:
රෙපොසිටරිය ක්ලෝන් කළ පසු, ලබා දී ඇති example ගොනු කොපි කර සැබෑ config ගොනු සාදා ගත යුතුය:

```bash
cp tg-api-hash-id.conf.example tg-api-hash-id.conf
cp tg-to-wa-forwarding-whatsapp-channels.conf.example tg-to-wa-forwarding-whatsapp-channels.conf
cp tg-to-wa-forwarding-whatsapp-channels-with-replace-text.conf.example tg-to-wa-forwarding-whatsapp-channels-with-replace-text.conf
cp tg-to-wa-heartbeat-whatsapp-channels.conf.example tg-to-wa-heartbeat-whatsapp-channels.conf
cp tg-to-wa-text-replace-dictionary.conf.example tg-to-wa-text-replace-dictionary.conf
cp tg-to-wa-test-telegrame-channels.conf.example tg-to-wa-test-telegrame-channels.conf
cp tg-to-tg-forwarding-telegrame-channels.conf.example tg-to-tg-forwarding-telegrame-channels.conf
cp tg-to-tg-forwarding-telegrame-channels-with-replace-text.conf.example tg-to-tg-forwarding-telegrame-channels-with-replace-text.conf
```

Then, edit each .conf file with your real IDs and credentials.
ඉන්පසු එක් එක් .conf ගොනුව විවෘත කර ඔබේ සැබෑ දත්ත ඇතුළත් කරන්න.

### Step 2: Start WhatsApp API (index.js)
Run the WhatsApp server and scan the QR code from your phone.
(වට්ස්ඇප් සර්වර් එක ක්‍රියාත්මක කර QR එක ස්කෑන් කරන්න).

```Bash
pm2 start index.js --name "whatsapp-api"
pm2 logs whatsapp-api
```

### Step 3: Configure Telegram (sync.php)
Update $api_id and $api_hash in sync.php, then run manually to login.
(ඔබේ ටෙලිග්‍රෑම් විස්තර ඇතුළත් කර පළමු වරට ලොග් වීමට මෙය රන් කරන්න).

``` Bash
php sync.php
```

### Step 4: Text Replacement (`replace-text.conf`)
Format: `'old_word' 'new_word'`
Example:
'Long' 'Buy'
'Short' 'Sell'

### ⏰ Automation / ස්වයංක්‍රීයකරණය
Step 4: Maintenance Script
Ensure maintenance.sh is executable to clear locks every 2 hours.
(පද්ධතිය නඩත්තු කිරීමේ ස්ක්‍රිප්ට් එකට අවසර ලබා දෙන්න).

``` Bash
chmod +x ~/WaNTg/maintenance.sh
```

### Step 5: Setup Cron Jobs
Add the following to crontab -e:
(ස්වයංක්‍රීයව පණිවිඩ යැවීමට සහ නඩත්තු වීමට මෙය Crontab එකට එක් කරන්න).

``` Bash
# Forward messages every minute
* * * * * cd /home/cito/WaNTg && flock -n /tmp/tg_sync.lock /usr/bin/php sync.php > /dev/null 2>&1

# Run maintenance every 2 hours
0 */2 * * * /bin/bash /home/cito/WaNTg/maintenance.sh > /dev/null 2>&1
```


---

### ⚠️ Troubleshooting / ගැටලු විසඳීම
Disk Space: Disk filling up can stop the bot. The maintenance script clears the downloads/ folder.
(ඩිස්ක් එක පිරීම වැළැක්වීමට නඩත්තු ස්ක්‍රිප්ට් එක ක්‍රියාත්මක විය යුතුය).
Session Lock: If you see "Script is already running", delete lock files: rm *.lock.
(පද්ධතිය හිරවී ඇත්නම් lock ෆයිල් මකා දමන්න).
Stickers Not Received: Ensure ffmpeg and lottie_convert.py are working correctly.
(ස්ටිකර් නොලැබේ නම් ffmpeg නිවැරදිව ස්ථාපනය වී ඇත්දැයි බලන්න).

---

### 👨‍💻 Author
Developed by **[Erandiya Sumanaweera.](https://fb.com/erandiya)**

---

### ⚖️ Disclaimer
This project is for educational and personal use only. Use it responsibly and comply with the Terms of Service of WhatsApp and Telegram.




---

## 📖 FAQ & Troubleshooting / ගැටලු සහ විසඳුම්
For a detailed guide on common challenges we faced and how to solve them, please refer to our FAQ:<br>
ස්ථාපනය කිරීමේදී අප මුහුණ දුන් අභියෝග සහ ඒවා ජයගත් ආකාරය පිළිබඳ විස්තරාත්මක මගපෙන්වීම සඳහා අපගේ FAQ ගොනුව බලන්න:

👉 **[Read the FAQ.md / FAQ කියවන්න](FAQ.md)**

---

## 🛡️ Long-term Stability  / දීර්ඝකාලීන ස්ථාවරත්වය
To ensure the system runs for a year without manual intervention:
පද්ධතිය මිනිස් මැදිහත්වීමකින් තොරව දිගුකාලීනව පවත්වා ගැනීමට පහත පියවර එක් කර ඇත:

1. **Swap Memory:** 2GB Virtual RAM added to prevent Out-of-Memory crashes.
2. **Watchdog Script:** A monitor script that detects system freezes and runs `maintenance.sh` automatically.
3. **Auto-Initialization:** Automatically syncs new channels from the current message ID.

