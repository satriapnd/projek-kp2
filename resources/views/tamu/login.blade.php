<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tamu — Face Recognition</title>
    <meta name="description" content="Login sebagai tamu menggunakan pengenalan wajah.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; color: #0f172a; min-height: 100vh; }

        header {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            position: sticky; top: 0; z-index: 50;
        }
        .nav-inner {
            max-width: 900px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 60px;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1rem; color: #0f172a; text-decoration: none; }
        .nav-logo-icon { width: 32px; height: 32px; background: #ecfdf5; border: 1.5px solid #6ee7b7; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn-outline { display: flex; align-items: center; gap: 7px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 16px; font-size: 0.8rem; font-weight: 600; color: #374151; background: #fff; text-decoration: none; transition: all 0.15s; }
        .btn-outline:hover { background: #f8fafc; }

        main { max-width: 900px; margin: 0 auto; padding: 48px 24px 80px; }

        .page-header { margin-bottom: 32px; }
        .page-tag { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; margin-bottom: 14px; }
        h1 { font-size: 1.875rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .page-sub { color: #64748b; font-size: 0.9rem; line-height: 1.6; max-width: 500px; }

        .login-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
            overflow: hidden; display: flex; box-shadow: 0 4px 24px rgba(0,0,0,0.05);
            max-width: 800px; margin: 0 auto;
        }
        @media (max-width: 640px) { .login-card { flex-direction: column; } }

        /* Camera side */
        .camera-side { flex: 1.4; position: relative; background: #1e293b; min-height: 360px; display: flex; flex-direction: column; }
        .camera-wrapper { flex: 1; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        #camera-stream { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        canvas { display: none; }

        .scan-guide {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -52%);
            width: 150px; height: 195px;
            border: 2px dashed rgba(59,130,246,0.7);
            border-radius: 50%;
            pointer-events: none;
        }
        .scan-guide::before, .scan-guide::after {
            content: ''; position: absolute;
            width: 20px; height: 20px; border-color: #3b82f6; border-style: solid;
        }
        .scan-guide::before { top: -2px; left: -2px; border-width: 2.5px 0 0 2.5px; border-radius: 2px; }
        .scan-guide::after  { bottom: -2px; right: -2px; border-width: 0 2.5px 2.5px 0; border-radius: 2px; }

        .camera-bottom { padding: 16px; display: flex; align-items: center; justify-content: center; gap: 12px; border-top: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.3); }
        .scan-btn { display: flex; align-items: center; gap: 8px; background: #3b82f6; border: none; border-radius: 10px; color: #fff; font-size: 0.875rem; font-weight: 700; padding: 11px 24px; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; box-shadow: 0 4px 12px rgba(59,130,246,0.4); }
        .scan-btn:hover { background: #2563eb; transform: translateY(-1px); }
        .scan-btn:disabled { background: #64748b; box-shadow: none; transform: none; cursor: not-allowed; }
        .retake-btn { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 9px 16px; font-size: 0.78rem; font-weight: 600; color: rgba(255,255,255,0.8); cursor: pointer; display: none; transition: all 0.15s; font-family: 'Inter', sans-serif; }
        .retake-btn.visible { display: block; }
        .retake-btn:hover { background: rgba(255,255,255,0.15); }

        /* Right panel */
        .info-side { width: 280px; flex-shrink: 0; padding: 32px 28px; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; }
        @media (max-width: 640px) { .info-side { width: 100%; border-left: none; border-top: 1px solid #e2e8f0; padding: 24px 20px; } }

        .info-title { font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 6px; }
        .info-sub { font-size: 0.8rem; color: #94a3b8; margin-bottom: 28px; line-height: 1.5; }

        .steps { margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
        .step-num { width: 22px; height: 22px; border-radius: 50%; background: #1d4ed8; color: #fff; font-size: 0.65rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
        .step-text { font-size: 0.78rem; color: #64748b; line-height: 1.5; }

        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 20px 0; }

        .register-hint { font-size: 0.78rem; color: #94a3b8; text-align: center; line-height: 1.6; }
        .register-hint a { color: #10b981; font-weight: 600; text-decoration: none; }
        .register-hint a:hover { text-decoration: underline; }

        /* Status box */
        .status-box { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center; }
        #status-msg { font-size: 0.82rem; font-weight: 600; color: #374151; }

        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 640px) { main { padding: 24px 16px 60px; } h1 { font-size: 1.5rem; } }
    </style>
</head>
<body>

<header>
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="nav-logo">
            <div class="nav-logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            Face Recognition
        </a>
        <div class="nav-actions">
            <a href="{{ route('home') }}" class="btn-outline">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Kembali
            </a>
            <a href="{{ route('tamu.register') }}" class="btn-outline">Daftar</a>
        </div>
    </div>
</header>

<main>
    <div class="page-header">
        <div class="page-tag">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Login Tamu
        </div>
        <h1>Masuk ke Akun Anda</h1>
        <p class="page-sub">Ambil foto selfie untuk dikenali oleh sistem. Tidak perlu password — wajah Anda adalah kunci akses.</p>
    </div>

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:12px 16px;border-radius:10px;font-size:0.875rem;margin-bottom:20px;max-width:800px;margin-left:auto;margin-right:auto;">
            ℹ️ {{ session('info') }}
        </div>
    @endif

    <div class="login-card">
        <!-- Camera -->
        <div class="camera-side">
            <div class="camera-wrapper">
                <video id="camera-stream" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <div class="scan-guide" id="scan-guide"></div>
            </div>
            <div class="camera-bottom">
                <button class="retake-btn" id="retake-btn">🔄 Foto Ulang</button>
                <button class="scan-btn" id="scan-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Scan & Masuk
                </button>
            </div>
        </div>

        <!-- Info -->
        <div class="info-side">
            <div class="info-title">Login dengan Wajah</div>
            <p class="info-sub">Sistem akan mengenali wajah Anda secara otomatis.</p>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">Izinkan akses kamera di browser Anda</div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">Posisikan wajah di dalam panduan oval</div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text">Klik <strong>Scan & Masuk</strong> untuk login</div>
                </div>
            </div>

            <div class="status-box">
                <span id="status-msg">Arahkan wajah ke kamera...</span>
            </div>

            <hr class="divider">

            <p class="register-hint">
                Belum punya akun?<br>
                <a href="{{ route('tamu.register') }}">Daftar wajah Anda sekarang →</a>
            </p>
        </div>
    </div>
</main>

<script>
    const video    = document.getElementById('camera-stream');
    const canvas   = document.getElementById('canvas');
    const scanBtn  = document.getElementById('scan-btn');
    const retakeBtn = document.getElementById('retake-btn');
    const statusMsg = document.getElementById('status-msg');
    const scanGuide = document.getElementById('scan-guide');

    let capturedImage = null;

    // Start camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 } } })
        .then(stream => { video.srcObject = stream; })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Kamera Error', text: err.message, confirmButtonColor: '#3b82f6' });
        });

    // Capture & scan
    scanBtn.addEventListener('click', async () => {
        if (capturedImage) {
            // Submit the captured image
            await doLogin(capturedImage);
            return;
        }

        // Capture frame
        const maxW = 320;
        const scale = Math.min(1, maxW / (video.videoWidth || 640));
        canvas.width  = video.videoWidth  * scale;
        canvas.height = video.videoHeight * scale;
        const ctx = canvas.getContext('2d');
        ctx.save(); ctx.scale(-1, 1);
        ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
        ctx.restore();
        capturedImage = canvas.toDataURL('image/jpeg', 0.85);

        scanGuide.style.display = 'none';
        retakeBtn.classList.add('visible');
        scanBtn.textContent = '✓ Login Sekarang';
        statusMsg.textContent = 'Foto diambil. Klik Login untuk masuk.';
        statusMsg.style.color = '#2563eb';
    });

    // Retake
    retakeBtn.addEventListener('click', () => {
        capturedImage = null;
        scanGuide.style.display = 'block';
        retakeBtn.classList.remove('visible');
        scanBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> Scan & Masuk';
        statusMsg.textContent = 'Arahkan wajah ke kamera...';
        statusMsg.style.color = '#374151';
    });

    async function doLogin(image) {
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;"></span> Memverifikasi...';
        statusMsg.textContent = 'Mengenali wajah...';
        statusMsg.style.color = '#d97706';

        try {
            const resp = await fetch('{{ route("tamu.login.process") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ image })
            });
            const result = await resp.json();

            if (result.success) {
                statusMsg.textContent = '✓ ' + result.message;
                statusMsg.style.color = '#16a34a';
                await Swal.fire({
                    icon: 'success',
                    title: '✅ Login Berhasil!',
                    html: `Selamat datang kembali, <strong>${result.nama}</strong>!<br><small style="color:#64748b">Akurasi: ${result.confidence}%</small>`,
                    confirmButtonColor: '#3b82f6',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
                window.location.href = result.redirect_url || '{{ route("tamu.profil") }}';
            } else {
                statusMsg.textContent = '✗ ' + result.message;
                statusMsg.style.color = '#dc2626';
                Swal.fire({ icon: 'error', title: 'Login Gagal', text: result.message, confirmButtonColor: '#3b82f6' });
                scanBtn.disabled = false;
                capturedImage = null;
                scanGuide.style.display = 'block';
                retakeBtn.classList.remove('visible');
                scanBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> Scan & Masuk';
            }
        } catch(e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.', confirmButtonColor: '#3b82f6' });
            scanBtn.disabled = false;
        }
    }
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</body>
</html>
