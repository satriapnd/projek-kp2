<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Demo halaman uji Face Recognition - kirim foto wajah dan lihat hasil pengenalan secara langsung tanpa reload halaman.">
    <title>Face Recognition Demo — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            color: #e2e8f0;
            padding: 32px 16px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .page-header p {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            margin-bottom: 24px;
            transition: color 0.15s;
        }
        .back-link:hover { color: #94a3b8; }

        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            max-width: 960px;
            margin: 0 auto;
        }
        @media (max-width: 720px) { .demo-grid { grid-template-columns: 1fr; } }

        .card {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 28px;
            backdrop-filter: blur(12px);
        }
        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 24px;
        }
        .badge {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Camera */
        .camera-box {
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            aspect-ratio: 4/3;
            margin-bottom: 14px;
        }
        #video-recognize, #video-register {
            width: 100%; height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .camera-overlay-text {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            white-space: nowrap;
        }

        /* Controls */
        .controls { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }

        label.field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        input[type="text"] {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            color: #f1f5f9;
            outline: none;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.15s;
            margin-bottom: 14px;
        }
        input[type="text"]:focus { border-color: #10b981; }
        input[type="text"]::placeholder { color: #475569; }

        /* File upload drop zone */
        .dropzone {
            border: 2px dashed rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            color: #475569;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s;
            margin-bottom: 14px;
        }
        .dropzone:hover { border-color: #10b981; color: #94a3b8; }
        .dropzone input[type="file"] { display: none; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.825rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: 'Inter', sans-serif;
            transition: all 0.15s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35); }
        .btn-primary:disabled { background: #1e3a2f; color: #374151; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
        .btn-capture {
            background: #0ea5e9;
            color: #fff;
        }
        .btn-capture:hover { background: #0284c7; }

        /* Preview */
        .preview-img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 14px;
            display: none;
            border: 1px solid rgba(255,255,255,0.07);
        }
        canvas { display: none; }

        /* Result panel */
        .result-panel {
            border-radius: 12px;
            padding: 16px;
            margin-top: 8px;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        .result-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .result-fail {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .result-info {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        .result-icon { font-size: 1.8rem; margin-bottom: 6px; }
        .result-name { font-size: 1.2rem; font-weight: 700; color: #f1f5f9; }
        .result-conf {
            font-size: 0.78rem;
            color: #94a3b8;
            margin-top: 4px;
        }
        .result-msg {
            font-size: 0.82rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Confidence bar */
        .conf-bar-wrap {
            background: rgba(255,255,255,0.06);
            border-radius: 99px;
            height: 6px;
            margin-top: 8px;
            overflow: hidden;
        }
        .conf-bar {
            height: 100%;
            border-radius: 99px;
            transition: width 0.6s ease;
            background: linear-gradient(90deg, #10b981, #34d399);
        }
    </style>
</head>
<body>

<div style="max-width: 960px; margin: 0 auto;">

    <a href="{{ route('dashboard') }}" class="back-link">
        ← Kembali ke Dashboard
    </a>

    <div class="page-header">
        <h1>🎯 Face Recognition Demo</h1>
        <p>Test endpoint <code>/face/recognize</code> dan <code>/face/register</code> langsung dari browser, tanpa menyentuh Flask.</p>
    </div>

    <div class="demo-grid">

        {{-- ============================================================= --}}
        {{-- KARTU 1: RECOGNIZE                                            --}}
        {{-- ============================================================= --}}
        <div class="card">
            <div class="card-title">
                🔍 Kenali Wajah
                <span class="badge">POST /face/recognize</span>
            </div>
            <div class="card-subtitle">Upload foto atau ambil dari kamera, klik Kenali.</div>

            {{-- Camera live --}}
            <div class="camera-box">
                <video id="video-recognize" autoplay playsinline></video>
                <span class="camera-overlay-text">Kamera belum aktif — klik tombol di bawah</span>
            </div>
            <canvas id="canvas-recognize"></canvas>

            <div class="controls">
                <button class="btn btn-secondary" id="btn-cam-recognize">
                    📷 Aktifkan Kamera
                </button>
                <button class="btn btn-capture" id="btn-capture-recognize" style="display:none;">
                    📸 Ambil Foto
                </button>
            </div>

            {{-- Atau file upload --}}
            <label class="field-label">atau upload file gambar:</label>
            <div class="dropzone" id="dropzone-recognize" onclick="document.getElementById('file-recognize').click()">
                <input type="file" id="file-recognize" accept="image/*">
                <span id="dropzone-recognize-text">Klik untuk pilih file (JPG/PNG)</span>
            </div>

            {{-- Preview --}}
            <img id="preview-recognize" class="preview-img" alt="Preview">

            {{-- Action --}}
            <button class="btn btn-primary" id="btn-recognize" disabled>
                🎯 Kenali Wajah
            </button>

            {{-- Result --}}
            <div class="result-panel" id="result-recognize">
                <div class="result-icon" id="result-recognize-icon"></div>
                <div class="result-name" id="result-recognize-name"></div>
                <div class="result-conf" id="result-recognize-conf"></div>
                <div class="conf-bar-wrap" id="conf-bar-wrap" style="display:none;">
                    <div class="conf-bar" id="conf-bar" style="width:0%"></div>
                </div>
                <div class="result-msg" id="result-recognize-msg"></div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- KARTU 2: REGISTER                                             --}}
        {{-- ============================================================= --}}
        <div class="card">
            <div class="card-title">
                ➕ Daftarkan Wajah
                <span class="badge">POST /face/register</span>
            </div>
            <div class="card-subtitle">Masukkan nama lalu upload/ambil foto wajah baru.</div>

            {{-- Nama --}}
            <label class="field-label" for="input-nama">Nama Lengkap</label>
            <input type="text" id="input-nama" placeholder="Contoh: Budi Santoso">

            {{-- Camera live --}}
            <div class="camera-box">
                <video id="video-register" autoplay playsinline></video>
                <span class="camera-overlay-text" id="cam-reg-hint">Kamera belum aktif — klik tombol di bawah</span>
            </div>
            <canvas id="canvas-register"></canvas>

            <div class="controls">
                <button class="btn btn-secondary" id="btn-cam-register">
                    📷 Aktifkan Kamera
                </button>
                <button class="btn btn-capture" id="btn-capture-register" style="display:none;">
                    📸 Ambil Foto
                </button>
            </div>

            {{-- Atau file upload --}}
            <label class="field-label">atau upload file gambar:</label>
            <div class="dropzone" id="dropzone-register" onclick="document.getElementById('file-register').click()">
                <input type="file" id="file-register" accept="image/*">
                <span id="dropzone-register-text">Klik untuk pilih file (JPG/PNG)</span>
            </div>

            {{-- Preview --}}
            <img id="preview-register" class="preview-img" alt="Preview">

            {{-- Action --}}
            <button class="btn btn-primary" id="btn-register-face" disabled>
                💾 Daftarkan Wajah
            </button>

            {{-- Result --}}
            <div class="result-panel" id="result-register">
                <div class="result-icon" id="result-register-icon"></div>
                <div class="result-name" id="result-register-name"></div>
                <div class="result-msg"  id="result-register-msg"></div>
            </div>
        </div>

    </div>{{-- /demo-grid --}}
</div>{{-- /container --}}

<script>
// =============================================================================
// Konfigurasi route Laravel (dari server-side Blade, aman)
// Frontend tidak pernah tahu URL/key Flask secara langsung.
// =============================================================================
const ROUTES = {
    recognize : "{{ route('face.recognize') }}",
    register  : "{{ route('face.register') }}"
};
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// =============================================================================
// Utility helpers
// =============================================================================
function setLoading(btn, isLoading, originalLabel) {
    btn.disabled = isLoading;
    btn.innerHTML = isLoading
        ? `<span class="spinner"></span> Memproses...`
        : originalLabel;
}

function showResult(panelId, { icon, name, conf, msg, type }) {
    const panel = document.getElementById(panelId);
    panel.className = `result-panel result-${type}`;
    panel.style.display = 'block';
    document.getElementById(panelId + '-icon').textContent = icon;
    document.getElementById(panelId + '-name').textContent = name ?? '';
    if (document.getElementById(panelId + '-conf')) {
        document.getElementById(panelId + '-conf').textContent = conf ?? '';
    }
    document.getElementById(panelId + '-msg').textContent  = msg ?? '';
}

// =============================================================================
// Camera + Capture logic (generic, dipanggil untuk recognize & register)
// =============================================================================
function setupCamera(videoId, btnCamId, btnCaptureId, canvasId, previewId, dropzoneId, dropzoneTxtId, fileInputId, submitBtn) {
    const video       = document.getElementById(videoId);
    const btnCam      = document.getElementById(btnCamId);
    const btnCapture  = document.getElementById(btnCaptureId);
    const canvas      = document.getElementById(canvasId);
    const preview     = document.getElementById(previewId);
    const fileInput   = document.getElementById(fileInputId);
    const dropzoneTxt = document.getElementById(dropzoneTxtId);
    let capturedB64   = null;
    let stream        = null;

    function setImage(b64Src) {
        capturedB64 = b64Src;
        preview.src = b64Src;
        preview.style.display = 'block';
        submitBtn.disabled = false;
    }

    // Aktifkan kamera
    btnCam.addEventListener('click', () => {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(s => {
                stream = s;
                video.srcObject = s;
                btnCam.style.display    = 'none';
                btnCapture.style.display = 'inline-flex';
            })
            .catch(() => alert('Tidak dapat mengakses kamera. Pastikan izin diberikan.'));
    });

    // Ambil foto dari kamera
    btnCapture.addEventListener('click', () => {
        const maxW   = 320;
        const scale  = Math.min(1, maxW / video.videoWidth);
        canvas.width  = video.videoWidth  * scale;
        canvas.height = video.videoHeight * scale;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const b64 = canvas.toDataURL('image/jpeg', 0.8);
        setImage(b64);
        // Hentikan stream kamera untuk hemat resource
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        btnCapture.style.display = 'none';
        btnCam.style.display     = 'inline-flex';
        btnCam.textContent       = '📷 Kamera lagi';
    });

    // Upload file
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            setImage(e.target.result);
            dropzoneTxt.textContent = `✅ ${file.name}`;
        };
        reader.readAsDataURL(file);
    });

    // Expose captured data ke luar
    return { getImage: () => capturedB64, getFile: () => fileInput.files[0] };
}

// =============================================================================
// RECOGNIZE
// =============================================================================
const recognizeCtrl = setupCamera(
    'video-recognize', 'btn-cam-recognize', 'btn-capture-recognize',
    'canvas-recognize', 'preview-recognize',
    'dropzone-recognize', 'dropzone-recognize-text', 'file-recognize',
    document.getElementById('btn-recognize')
);

document.getElementById('btn-recognize').addEventListener('click', async () => {
    const btn   = document.getElementById('btn-recognize');
    const label = '🎯 Kenali Wajah';
    setLoading(btn, true, label);

    try {
        const b64  = recognizeCtrl.getImage();
        const file = recognizeCtrl.getFile();

        let res;

        // Jika ada file yang belum dipreview (langsung upload), gunakan FormData
        if (file && document.getElementById('preview-recognize').style.display === 'none') {
            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', CSRF_TOKEN);
            res = await fetch(ROUTES.recognize, { method: 'POST', body: fd });
        } else if (b64) {
            // Kirim base64 JSON
            res = await fetch(ROUTES.recognize, {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : CSRF_TOKEN
                },
                body: JSON.stringify({ image: b64 })
            });
        } else {
            alert('Pilih gambar terlebih dahulu.');
            setLoading(btn, false, label);
            return;
        }

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || `HTTP ${res.status}`);
        }

        const data = await res.json();

        // --- Tampilkan hasil ---
        const confBarWrap = document.getElementById('conf-bar-wrap');
        const confBar     = document.getElementById('conf-bar');

        if (data.recognized) {
            confBarWrap.style.display = 'block';
            confBar.style.width = (data.confidence ?? 0) + '%';
            showResult('result-recognize', {
                icon: '✅',
                name: `Selamat datang, ${data.nama}!`,
                conf: `Confidence: ${data.confidence?.toFixed(1)}%`,
                msg : '',
                type: 'success'
            });
        } else {
            confBarWrap.style.display = 'none';
            showResult('result-recognize', {
                icon: '❌',
                name: 'Tamu tidak dikenali',
                conf: data.confidence != null ? `Confidence tertinggi: ${data.confidence.toFixed(1)}%` : '',
                msg : data.message ?? 'Wajah tidak cocok dengan data terdaftar.',
                type: 'fail'
            });
        }

    } catch (err) {
        showResult('result-recognize', {
            icon: '⚠️',
            name: 'Error',
            conf: '',
            msg : err.message,
            type: 'fail'
        });
    } finally {
        setLoading(btn, false, label);
    }
});

// =============================================================================
// REGISTER
// =============================================================================
const registerCtrl = setupCamera(
    'video-register', 'btn-cam-register', 'btn-capture-register',
    'canvas-register', 'preview-register',
    'dropzone-register', 'dropzone-register-text', 'file-register',
    document.getElementById('btn-register-face')
);

// Nama input juga mempengaruhi disabled state
document.getElementById('input-nama').addEventListener('input', () => {
    const hasFoto = document.getElementById('preview-register').style.display !== 'none';
    const hasNama = document.getElementById('input-nama').value.trim().length > 0;
    document.getElementById('btn-register-face').disabled = !(hasFoto && hasNama);
});

document.getElementById('btn-register-face').addEventListener('click', async () => {
    const btn   = document.getElementById('btn-register-face');
    const label = '💾 Daftarkan Wajah';
    setLoading(btn, true, label);

    try {
        const nama = document.getElementById('input-nama').value.trim();
        const b64  = registerCtrl.getImage();

        if (!nama) { alert('Nama wajib diisi.'); setLoading(btn, false, label); return; }
        if (!b64)  { alert('Ambil atau upload foto terlebih dahulu.'); setLoading(btn, false, label); return; }

        // Kirim base64 JSON ke Laravel proxy
        const res = await fetch(ROUTES.register, {
            method : 'POST',
            headers: {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : CSRF_TOKEN
            },
            body: JSON.stringify({ nama, image: b64 })
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || `HTTP ${res.status}`);
        }

        const data = await res.json();

        if (data.success && !data.is_duplicate) {
            showResult('result-register', {
                icon: '✅',
                name: data.message ?? `Wajah '${nama}' berhasil didaftarkan!`,
                msg : 'Silakan gunakan halaman Kenali untuk menguji.',
                type: 'success'
            });
            document.getElementById('input-nama').value = '';
        } else if (data.is_duplicate) {
            showResult('result-register', {
                icon: '⚠️',
                name: 'Wajah sudah terdaftar',
                msg : data.message ?? '',
                type: 'info'
            });
        } else {
            showResult('result-register', {
                icon: '❌',
                name: 'Gagal mendaftarkan',
                msg : data.message ?? 'Terjadi kesalahan.',
                type: 'fail'
            });
        }

    } catch (err) {
        showResult('result-register', {
            icon: '⚠️',
            name: 'Error',
            msg : err.message,
            type: 'fail'
        });
    } finally {
        setLoading(btn, false, label);
    }
});
</script>

</body>
</html>
