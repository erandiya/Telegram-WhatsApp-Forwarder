const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

const app = express();
app.use(express.json());

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox', 
            '--disable-setuid-sandbox', 
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-zygote'
        ]
    }
});

client.on('qr', (qr) => { qrcode.generate(qr, { small: true }); });
client.on('ready', () => { console.log('WhatsApp API Ready!'); });

// --- 1. ඔබ පණිවිඩයක් යැවූ සැණින් Group ID එක පෙන්වන කොටස ---
client.on('message_create', msg => {
    if (msg.fromMe && msg.body.toLowerCase().includes('test id')) {
        console.log('\n---------------- පණිවිඩයක් හඳුනාගත්තා ----------------');
        console.log('Chat Name:', msg.to); 
        console.log('Group ID:', msg.to); // මෙය කොපි කරගන්න
        console.log('Message:', msg.body);
        console.log('----------------------------------------------------\n');
    }
});

client.initialize();

// --- 2. සියලුම ගෘප් ලැයිස්තුව සහ ID ලබා ගන්නා Endpoint එක ---
app.get('/list-groups', async (req, res) => {
    try {
        const chats = await client.getChats();
        const groups = chats
            .filter(chat => chat.isGroup)
            .map(group => ({
                name: group.name,
                id: group.id._serialized
            }));
        res.json(groups);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.post('/send-message', async (req, res) => {
    let { number, message, filePath, isSticker } = req.body;
    if (!number) return res.status(400).json({ error: 'Number missing' });

    const cleanNumber = String(number).replace(/[^0-9@.a-zA-Z]/g, '');
    const chatId = cleanNumber.includes('@g.us') ? cleanNumber : `${cleanNumber}@c.us`;

    if (!client.info || !client.info.wid) return res.status(503).json({ error: 'WhatsApp not ready' });

    try {
        if (filePath && fs.existsSync(filePath)) {
            const ext = path.extname(filePath).toLowerCase();
            let finalPath = filePath;

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

                await new Promise(r => setTimeout(r, 1000));

                await client.sendMessage(chatId, media, { 
                    sendMediaAsSticker: isSticker || finalPath.endsWith('.webp'),
                    caption: message || ''
                });

                if (finalPath.includes('/downloads/')) fs.unlinkSync(finalPath);
            }
        } else {
            await client.sendMessage(chatId, message || '');
        }
        res.json({ status: 'success' });
    } catch (error) {
        console.error("API Error:", error.message);
        if (error.message.includes('detached Frame') || error.message.includes('Protocol error')) {
            process.exit(1);
        }
        res.status(500).json({ status: 'error', error: error.message });
    }
});

// පද්ධතියේ තත්ත්වය බැලීමට අලුතින් එක් කළ කොටස
app.get('/status', (req, res) => {
    res.json({ status: 'online' });
});

app.get('/list-all-entities', async (req, res) => {
    try {
        const chats = await client.getChats();
        const data = await Promise.all(chats.map(async chat => {
            let picUrl = "";
            try { picUrl = await chat.getProfilePicUrl(); } catch(e) {}
            return {
                id: chat.id._serialized,
                name: chat.name,
                canSend: !chat.isReadOnly,
                pic: picUrl
            };
        }));
        res.json(data);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.listen(3000, '0.0.0.0', () => console.log('API Running on 3000'));

