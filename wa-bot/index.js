import express from "express";
import bodyParser from "body-parser";
import cors from "cors";
import fs from "fs";
import qrcode from "qrcode-terminal";

import makeWASocket, {
  useMultiFileAuthState,
  fetchLatestBaileysVersion,
  DisconnectReason
} from "@whiskeysockets/baileys";

/* ======================
   CONFIG
====================== */
const PORT = 2026;
const API_KEY = "123456";

/* ======================
   APP
====================== */
const app = express();
app.use(cors());
app.use(bodyParser.json());

function auth(req, res, next) {
  if (req.headers["x-api-key"] !== API_KEY) {
    return res.status(401).json({ error: "Unauthorized" });
  }
  next();
}

let sock;

/* ======================
   START WHATSAPP
====================== */
async function startWA() {

  if (!fs.existsSync("./session")) fs.mkdirSync("./session");

  const { state, saveCreds } = await useMultiFileAuthState("./session");
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    auth: state,
    version,
    syncFullHistory: false,
    connectTimeoutMs: 60000,
    keepAliveIntervalMs: 30000,
    markOnlineOnConnect: true,
    browser: ["CRM WA", "Windows", "1.0"]
  });

  sock.ev.on("creds.update", saveCreds);

  /* ======================
     CONNECTION UPDATE
  ====================== */
  sock.ev.on("connection.update", (update) => {

    const { qr, connection, lastDisconnect } = update;

    if (qr) {
      console.clear();
      console.log("📱 SCAN QR DI BAWAH INI:");
      qrcode.generate(qr, { small: true });
    }

    if (connection === "open") {
      console.log("✅ WhatsApp Connected");
    }

    if (connection === "close") {
      const reason = lastDisconnect?.error?.output?.statusCode;
      console.log("❌ WA disconnected:", reason);

      if (reason !== DisconnectReason.loggedOut) {
        console.log("🔄 Reconnecting in 5 seconds...");
        setTimeout(startWA, 5000);
      } else {
        console.log("⚠️ Logged out. Hapus folder session dan scan ulang.");
      }
    }

  });
}

startWA();

/* ======================
   API SEND MESSAGE (UNTUK CRM)
====================== */
app.post("/send", auth, async (req, res) => {

  try {
    let { nomor, pesan } = req.body;

    if (!nomor || !pesan) {
      return res.status(400).json({ error: "Nomor & pesan wajib diisi" });
    }

    // format 08 -> 628
    if (nomor.startsWith("0")) {
      nomor = "62" + nomor.slice(1);
    }

    const jid = nomor + "@s.whatsapp.net";

    await sock.sendMessage(jid, { text: pesan });

    console.log("📤 Pesan terkirim ke:", nomor);

    res.json({ status: "success" });

  } catch (e) {
    console.error("❌ Gagal kirim:", e.message);
    res.status(500).json({ error: e.message });
  }
});

app.listen(PORT, () =>
  console.log(`🚀 WA API aktif di http://localhost:${PORT}`)
);