const video = document.getElementById('video');
const enrollBtn = document.getElementById('enrollBtn');
const verifyBtn = document.getElementById('verifyBtn');
const listBtn = document.getElementById('listBtn');
const statusEl = document.getElementById('status');
const userIdEl = document.getElementById('userId');

const SERVER = 'http://localhost:3000'; // change later if deploying
const MATCH_THRESHOLD = 0.6; // should match server default

function logStatus(objOrMsg) {
  const msg = typeof objOrMsg === 'string' ? objOrMsg : JSON.stringify(objOrMsg, null, 2);
  statusEl.textContent = `Status: ${msg}`;
  console.log(objOrMsg);
}

async function loadModels() {
  const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
  await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
  await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
  await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
}

async function startCamera() {
  const stream = await navigator.mediaDevices.getUserMedia({ video: true });
  video.srcObject = stream;
  return new Promise(res => (video.onloadedmetadata = res));
}

async function getDescriptor() {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);

  const det = await faceapi
    .detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks()
    .withFaceDescriptor();

  return det ? Array.from(det.descriptor) : null;
}

async function enroll() {
  const userId = (userIdEl.value || '').trim();
  if (!userId) return logStatus('Enter a User ID before enrolling.');
  logStatus('Capturing for enrollment…');
  const desc = await getDescriptor();
  if (!desc) return logStatus('No face detected. Try again with good lighting.');

  const embedding_b64 = btoa(JSON.stringify(desc));
  const resp = await fetch(`${SERVER}/enroll`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userId, embedding_b64 })
  });
  const data = await resp.json();
  if (!resp.ok) return logStatus(data);
  logStatus({ message: 'Enrollment saved', userId, templates: data.templateCount });
}

async function verify() {
  const userId = (userIdEl.value || '').trim();
  if (!userId) return logStatus('Enter a User ID to verify against.');
  logStatus('Capturing for verification…');
  const probe = await getDescriptor();
  if (!probe) return logStatus('No face detected for verification.');

  const resp = await fetch(`${SERVER}/verify`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userId, probeEmbedding: probe })
  });
  const data = await resp.json();
  if (!resp.ok) return logStatus(data);
  if (data.match) {
    logStatus({ ok: true, message: '✅ MATCH', bestDistance: data.bestDistance, threshold: data.threshold, userId });
  } else {
    logStatus({ ok: false, message: '❌ NO MATCH', bestDistance: data.bestDistance, threshold: data.threshold, userId });
  }
}

async function listUsers() {
  const resp = await fetch(`${SERVER}/users`);
  const data = await resp.json();
  logStatus(data);
}

enrollBtn.addEventListener('click', enroll);
verifyBtn.addEventListener('click', verify);
listBtn.addEventListener('click', listUsers);

(async () => {
  try {
    await loadModels();
    await startCamera();
    logStatus('Camera ready. Enter a User ID, then Enroll or Verify.');
  } catch (e) {
    logStatus('Init error: ' + e.message);
  }
})();
