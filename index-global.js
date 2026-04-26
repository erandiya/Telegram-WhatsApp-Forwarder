const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

const app = express();
app.use(express.json());

const client = new Client({
    authStrategy: new LocalAuth({
        clientId: "global-session", // මෙය වෙනස් නමක් විය යුතුයි (උදා: global-session)
        dataPath: "./.wwebjs_auth_global" // මෙය වෙනම ෆෝල්ඩරයක් ලෙස සකසමු
}),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu', '--no-zygote']
    }
});

client.on('qr', (qr) => { qrcode.generate(qr, { small: true }); });
client.on('ready', () => { console.log('WhatsApp API Ready!'); });

client.on('disconnected', (reason) => {
    console.log('WhatsApp was logged out', reason);
    process.exit(1); 
});

client.initialize();

app.post('/send-message', async (req, res) => {
    let { number, message, filePath, isSticker } = req.body;
    if (!number) return res.status(400).json({ error: 'Number missing' });

    const cleanNumber = String(number).replace(/[^0-9@.a-zA-Z]/g, '');
    const chatId = cleanNumber.includes('@g.us') ? cleanNumber : `${cleanNumber}@c.us`;

    if (!client.info || !client.info.wid) {
        return res.status(503).json({ error: 'WhatsApp is not ready' });
    }

    try {
        if (filePath && fs.existsSync(filePath)) {
            const ext = path.extname(filePath).toLowerCase();
            let finalPath = filePath;

            // --- TGS to WebP Converter ---
            if (ext === '.tgs') {
                const gifPath = filePath.replace('.tgs', '.gif');
                const webpPath = filePath.replace('.tgs', '.webp');
                const cmd = `python3 /home/cito/.local/bin/lottie_convert.py "${filePath}" "${gifPath}" && ffmpeg -i "${gifPath}" -vcodec libwebp -vf "scale=512:512:force_original_aspect_ratio=decrease,pad=512:512:(ow-iw)/2:(oh-ih)/2:color=#00000000" -lossless 0 -q:v 50 -loop 0 -an "${webpPath}"`;

                await new Promise((resolve, reject) => {
                    exec(cmd, { timeout: 45000 }, (err) => {
                        if (!err && fs.existsSync(webpPath)) {
                            finalPath = webpPath;
                            isSticker = true;
                            if (fs.existsSync(filePath)) fs.unlinkSync(filePath);
                            if (fs.existsSync(gifPath)) fs.unlinkSync(gifPath);
                            resolve();
                        } else { reject(new Error("Sticker conversion failed")); }
                    });
                });
            }

            if (fs.existsSync(finalPath)) {
                const fileData = fs.readFileSync(finalPath, { encoding: 'base64' });
                const mimeType = isSticker || finalPath.endsWith('.webp') ? 'image/webp' : 'image/jpeg';
                const media = new MessageMedia(mimeType, fileData, path.basename(finalPath));

                await new Promise(r => setTimeout(r, 1500));

                await client.sendMessage(chatId, media, { 
                    sendMediaAsSticker: isSticker || finalPath.endsWith('.webp'),
                    caption: message || ''
                });
                
                // --- වෙනස් කළ කොටස ---
                // පින්තූරය downloads ෆෝල්ඩරය ඇතුළේ තිබේ නම් පමණක් මකා දමන්න
                if (finalPath.includes('/downloads/')) {
                    fs.unlinkSync(finalPath);
                    console.log(`Temporary file deleted: ${finalPath}`);
                } else {
                    console.log(`Permanent file kept: ${finalPath}`);
                }
            }
        } else {
            await client.sendMessage(chatId, message || '');
            console.log(`Text Sent to: ${chatId}`);
        }
        res.json({ status: 'success' });
    } catch (error) {
        console.error("Critical API Error:", error.message);
        res.status(500).json({ status: 'error', error: error.message });
    }
});

app.listen(3001, '0.0.0.0', () => console.log('API Running on 3001'));

