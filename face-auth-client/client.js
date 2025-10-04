const video = document.getElementById('video');
const captureBtn = document.getElementById('captureBtn');

async function loadModels() {
  const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
  await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
  await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
  await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
}

async function startCamera() {
  const stream = await navigator.mediaDevices.getUserMedia({ video: true });
  video.srcObject = stream;
  return new Promise(res => video.onloadedmetadata = res);
}

captureBtn.addEventListener('click', async () => {
  const raw = prompt('Paste NFC JSON (or cancel for demo)');
  let nfcData;
  if (!raw) {
    const demoEmbedding = new Array(128).fill(0).map((_,i)=>Math.sin(i*0.1)*0.01);
    nfcData = { userId: 'demo', embedding_b64: btoa(JSON.stringify(demoEmbedding)) };
  } else {
    nfcData = JSON.parse(raw);
  }

  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);

  const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks()
    .withFaceDescriptor();

  if (!detection) {
    alert('No face detected!');
    return;
  }

  const descriptor = Array.from(detection.descriptor);

  const resp = await fetch('http://localhost:3000/verify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      userId: nfcData.userId,
      cardEmbedding_b64: nfcData.embedding_b64,
      probeEmbedding: descriptor
    })
  });

  const result = await resp.json();
  if (result.match) alert('✅ ACCESS GRANTED');
  else alert('❌ ACCESS DENIED (score: ' + result.score + ')');
});

(async () => {
  await loadModels();
  await startCamera();
})();
