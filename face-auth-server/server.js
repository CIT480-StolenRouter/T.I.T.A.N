const express = require('express');
const bodyParser = require('body-parser');

const app = express();
app.use(bodyParser.json());

function decodeEmbedding(b64) {
  const s = Buffer.from(b64, 'base64').toString('utf8');
  return JSON.parse(s);
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

app.post('/verify', (req, res) => {
  try {
    const { userId, cardEmbedding_b64, probeEmbedding } = req.body;
    if (!cardEmbedding_b64 || !probeEmbedding) return res.status(400).json({ error: 'missing' });

    const cardEmbedding = decodeEmbedding(cardEmbedding_b64);
    const dist = euclidean(cardEmbedding, probeEmbedding);
    const threshold = 0.6;
    const match = dist <= threshold;

    return res.json({ match, score: dist });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: err.message });
  }
});

app.listen(3000, () => console.log('Server running on http://localhost:3000'));
