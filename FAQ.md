# ❓ Setup FAQ & Troubleshooting Guide / නිතර අසන පැනයන් සහ විසඳුම් 🛠️

This document outlines the end-to-end challenges faced during the development of the **Telegram to WhatsApp Forwarder** on Ubuntu and how they were resolved.

මෙම ලේඛනය මගින් **Telegram to WhatsApp Forwarder** පද්ධතිය Ubuntu VPS එකක් මත ස්ථාපනය කිරීමේදී මුහුණ දුන් අභියෝග සහ ඒවා ජයගත් ආකාරය පියවරෙන් පියවර පැහැදිලි කරයි.

---

## 1. Initial Setup & PHP / ආරම්භක පියවර සහ PHP සැකසුම්

### Q: Why couldn't I install PHP 8.2 extensions on Ubuntu 24.04?
**Issue:** Default Ubuntu repositories might not contain the latest PHP versions or specific extensions like FFI.<br>
**Solution:** Added the Ondřej Surý PPA to access the latest PHP builds.<br><br>
**සිංහල:** Ubuntu 24.04 හි සාමාන්‍ය Repository එකේ PHP 8.2 හෝ FFI වැනි Extensions නොමැති වීම. මෙයට විසඳුම ලෙස Ondřej Surý PPA එක එකතු කර අලුත්ම PHP සංස්කරණය ස්ථාපනය කරන ලදී.

### Q: "FFI support is not enabled" error in MadelineProto?
**Issue:** Even if FFI is installed, it's disabled in the CLI by default for security.<br>
**Solution:** Edited `php.ini` to set `ffi.enable=true` and enabled it globally.<br><br>
**සිංහල:** FFI ඉන්ස්ටෝල් කර තිබුණත් MadelineProto එක වැඩ නොකිරීම. මෙයට විසඳුම ලෙස `php.ini` ෆයිල් එක තුළ `ffi.enable=true` ලෙස සකස් කර එය සක්‍රිය කරන ලදී.

---

## 2. Media & Sticker Processing / මීඩියා සහ ස්ටිකර් ගැටලු

### Q: Why were text messages working but images and stickers failing?
**Issue:** 1. Lack of folder permissions. 2. Missing FFmpeg for media handling.<br>
**Solution:** Granted full permissions (`777`) to the `downloads` folder and installed **FFmpeg**.<br><br>
**සිංහල:** ටෙක්ස්ට් ලැබුණත් පින්තූර නොලැබීම. මෙයට හේතුව වූයේ `downloads` ෆෝල්ඩරයට ලිවීමට අවසර නොමැති වීම සහ FFmpeg මෘදුකාංගය සර්වර් එකේ නොමැති වීමයි. එය ඉන්ස්ටෝල් කර අවසර ලබා දීමෙන් පසු නිවැරදි විය.

### Q: How did we solve the Animated Sticker (.tgs) conversion?
**Issue:** WhatsApp does not support Telegram's `.tgs` (Lottie JSON) format. It only supports Animated WebP.<br>
**Solution:** We built a custom conversion pipeline: `TGS` ➡️ `GIF` (using Python Lottie) ➡️ `WebP` (using FFmpeg).<br><br>
**සිංහල:** ටෙලිග්‍රෑම් ඇනිමේටඩ් ස්ටිකර් (.tgs) වට්ස්ඇප් එකේ වැඩ නොකිරීම. විසඳුම ලෙස Python සහ FFmpeg භාවිතා කර එම ස්ටිකර් මුලින්ම GIF එකක් බවටත්, පසුව වට්ස්ඇප් වලට ගැලපෙන WebP එකක් බවටත් පරිවර්තනය කරන ලදී.

---

## 3. Storage & Disk Management / ඩිස්ක් සහ මතකය කළමනාකරණය

### Q: "No space left on device" - Why did the 8GB disk fill up so fast?
**Issue:** Temporary media downloads and logs quickly consumed the limited 8GB VPS storage.<br>
**Solution:**<br>
1. Resized the VM partition from 8GB to 20GB using `growpart` and `resize2fs`.<br>
2. Created a `maintenance.sh` script to clear downloads and logs every 2 hours.<br><br>
**සිංහල:** සර්වර් එකේ ඉඩ (8GB) එකපාරටම පිරී යාම. මෙයට විසඳුම ලෙස VPS එකේ Partition එක 20GB දක්වා වැඩි කරන ලදී. එසේම සෑම පැය 2කට වරක් තාවකාලික ෆයිල් මකා දමන නඩත්තු ස්ක්‍රිප්ට් එකක් සකසන ලදී.

---

## 4. Automation & Cron Jobs / ස්වයංක්‍රීයකරණය

### Q: Why did the script stop working automatically (Cron Job issues)?
**Issue:** Cron Jobs run in a different environment and didn't know the folder context.<br>
**Solution:** Used the `cd` command in crontab to enter the project folder before running PHP.<br><br>
**සිංහල:** මැනුවල් රන් කරන විට වැඩ කරන ස්ක්‍රිප්ට් එක Cron එකෙන් වැඩ නොකිරීම. මෙයට විසඳුම ලෙස Crontab එක ඇතුළත `cd` කමාන්ඩ් එක භාවිතා කර අදාළ ෆෝල්ඩරයට ඇතුළු වී පසුව ස්ක්‍රිප්ට් එක රන් කිරීමට උපදෙස් දෙන ලදී.

### Q: How to prevent multiple instances from locking each other?
**Issue:** If a previous sync was still running (e.g., converting a large sticker), the next minute's cron would start and crash.<br>
**Solution:** Implemented the Linux `flock` utility in Crontab to ensure only one instance of `sync.php` runs at a time.<br><br>
**සිංහල:** එකම ස්ක්‍රිප්ට් එක එකවර දෙපාරක් රන් වී හිරවීම. මෙයට විසඳුම ලෙස Linux `flock` භාවිතා කර එකක් වැඩ අවසන් වන තුරු තවත් එකක් රන් වීම වළක්වන ලදී.

---

## 5. WhatsApp API & Stability / වට්ස්ඇප් පද්ධතියේ ස්ථාවරත්වය

### Q: "Protocol error: Promise was collected" or "getChat undefined"?
**Issue:** Puppeteer browser instance crashed or became unresponsive.<br>
**Solution:** Switched the Node.js media sending logic to **Base64** and implemented a `pm2 restart` in the maintenance script to keep the browser fresh.<br><br>
**සිංහල:** වට්ස්ඇප් බ්‍රවුසරය (Puppeteer) හිරවීම හෝ දෝෂ පෙන්වීම. මෙයට විසඳුම ලෙස පින්තූර යැවීමේදී Base64 ක්‍රමය භාවිතා කළ අතර, සෑම පැය කිහිපයකට වරක් සේවාවන් Restart කිරීමට පියවර ගන්නා ලදී.

---

## 6. Security & GitHub / ආරක්ෂාව සහ GitHub

### Q: Why is it dangerous to upload `.wwebjs_auth` or `telegram.conf` to GitHub?
**Issue:** These files contain active session tokens. If public, anyone can hijack your WhatsApp or Telegram.<br>
**Solution:** Added these files to `.gitignore` and used a separate `telegram.conf` for sensitive credentials.<br><br>
**සිංහල:** වට්ස්ඇප් සෙෂන් ෆෝල්ඩර හෝ ටෙලිග්‍රෑම් API keys GitHub එකට දැමීමෙන් ඔබේ ගිණුම් වල ආරක්ෂාව නැති වීම. මෙය වැළැක්වීමට එම රහස්‍ය ෆයිල් සියල්ල `.gitignore` එකට ඇතුළත් කර අප්ලෝඩ් වීම වළක්වන ලදී.

---

## 7. Advanced Logic / වැඩිදියුණු කළ විශේෂාංග

### Q: How do we show the names of people joining groups?
**Issue:** Default service messages only say "Member joined".<br>
**Solution:** Integrated `$MadelineProto->getInfo()` to fetch the user's First and Last name dynamically and format it into the WhatsApp message.<br><br>
**සිංහල:** චැනල් එකට සම්බන්ධ වන අයගේ නම් නොපෙනීම. විසඳුම ලෙස ටෙලිග්‍රෑම් වෙතින් එම පුද්ගලයාගේ නම ලබාගෙන (getInfo) එය පණිවිඩයට එකතු කරන ලදී.

### Q: What is the "Heartbeat" notification?
**Feature:** A separate `heartbeat.php` included in `sync.php` that sends a "System Online" message to Admin groups at :00, :15, :30, and :45 minutes.<br><br>
**සිංහල:** පද්ධතිය වැඩ කරනවාදැයි බලා ගැනීමට සෑම විනාඩි 15කට වරක්ම ඇඩ්මින්ට "System Online" මැසේජ් එකක් එන ලෙස සකස් කරන ලද අමතර ස්ක්‍රිප්ට් එකකි.

---

**Developed & Documented by [Erandiya Sumanaweera.](https://fb.com/erandiya)**<br>
**සකස් කළේ : [Erandiya Sumanaweera.](https://fb.com/erandiya)**

