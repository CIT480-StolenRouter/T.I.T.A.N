// face-auth-server/server.js
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(bodyParser.json({ limit: '2mb' }));
app.use(cors({ origin: true })); // dev-friendly; tighten later

// super simple JSON "DB"
const DB_FILE = path.join(__dirname, 'db.json');
if (!fs.existsSync(DB_FILE)) fs.writeFileSync(DB_FILE, JSON.stringify({ users: {} }, null, 2));

function readDB() {
  return JSON.parse(fs.readFileSync(DB_FILE, 'utf8'));
}
function writeDB(db) {
  fs.writeFileSync(DB_FILE, JSON.stringify(db, null, 2));
}

function decodeEmbedding(b64) {
  const s = Buffer.from(b64, 'base64').toString('utf8');
  return JSON.parse(s); // array of floats
}
function euclidean(a, b) {
  if (a.length !== b.length) throw new Error('dim mismatch');
  let s = 0;
  for (let i = 0; i < a.length; i++) {
    const d = a[i] - b[i];
    s += d * d;
  }
  return Math.sqrt(s);
}

// ---- API ----

// List users
app.get('/users', (req, res) => {
  const db = readDB();
  res.json({ users: Object.keys(db.users) });
});

// Get a user (without embeddings content, just counts)
app.get('/users/:userId', (req, res) => {
  const db = readDB();
  const u = db.users[req.params.userId];
  if (!u) return res.status(404).json({ error: 'not found' });
  res.json({ userId: req.params.userId, templates: (u.templates || []).length });
});

// Enroll (create or append a template for user)
// body: { userId, embedding_b64 }
app.post('/enroll', (req, res) => {
  const { userId, embedding_b64 } = req.body || {};
  if (!userId || !embedding_b64) return res.status(400).json({ error: 'userId and embedding_b64 required' });

  const db = readDB();
  if (!db.users[userId]) db.users[userId] = { templates: [] };

  // store as base64 JSON to keep file small-ish
  db.users[userId].templates.push(embedding_b64);
  writeDB(db);

  res.json({ ok: true, userId, templateCount: db.users[userId].templates.length });
});

// Verify
// body: { userId, probeEmbedding }
// Compare probe against all templates; accept if any within threshold
app.post('/verify', (req, res) => {
  try {
    const { userId, probeEmbedding } = req.body || {};
    if (!userId || !probeEmbedding) return res.status(400).json({ error: 'userId and probeEmbedding required' });

    const db = readDB();
    const user = db.users[userId];
    if (!user || !user.templates || user.templates.length === 0) {
      return res.status(404).json({ error: 'user has no templates; enroll first' });
    }

    const threshold = Number(process.env.FACE_AUTH_THRESHOLD || 0.6);
    let best = { dist: Infinity, matched: false };

    for (const t of user.templates) {
      const enrolled = decodeEmbedding(t);
      const dist = euclidean(enrolled, probeEmbedding);
      if (dist < best.dist) best = { dist, matched: dist <= threshold };
      if (best.matched) break; // early exit if already good enough
    }

    res.json({ match: best.matched, bestDistance: best.dist, threshold, userId });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: e.message });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
