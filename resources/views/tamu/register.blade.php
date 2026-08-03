<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sebagai Tamu — Face Recognition</title>
    <meta name="description" content="Daftarkan wajah Anda untuk bisa melakukan check-in di kantor. Cukup ambil foto selfie dan isi nama.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #0f172a;
            min-height: 100vh;
        }

        /* HEADER */
        header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky; top: 0; z-index: 50;
        }
        .nav-inner {
            max-width: 1100px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 60px;
        }
        .nav-logo {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; font-size: 1rem; color: #0f172a; text-decoration: none;
        }
        .nav-logo-icon {
            width: 32px; height: 32px; background: #ecfdf5;
            border: 1.5px solid #6ee7b7; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn-outline {
            display: flex; align-items: center; gap: 7px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 7px 16px; font-size: 0.8rem; font-weight: 600;
            color: #374151; background: #fff; text-decoration: none;
            transition: all 0.15s;
        }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* PAGE */
        main { max-width: 1000px; margin: 0 auto; padding: 48px 24px 80px; }

        .page-header { margin-bottom: 32px; }
        .page-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: #ecfdf5; border: 1px solid #6ee7b7;
            color: #059669; border-radius: 20px; padding: 4px 12px;
            font-size: 0.75rem; font-weight: 600; margin-bottom: 14px;
        }
        h1 { font-size: 1.875rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .page-sub { color: #64748b; font-size: 0.9rem; line-height: 1.6; max-width: 540px; }

        /* MAIN CARD */
        .reg-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
            overflow: hidden; display: flex; box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) { .reg-card { flex-direction: column; } }

        /* LEFT - Camera */
        .camera-side {
            flex: 1; background: #f8fafc; position: relative;
            min-height: 420px; display: flex; flex-direction: column;
        }
        .camera-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; border-bottom: 1px solid #e2e8f0;
        }
        .camera-label {
            display: flex; align-items: center; gap: 7px;
            font-size: 0.8rem; font-weight: 600; color: #374151;
        }
        .detecting-badge {
            display: flex; align-items: center; gap: 5px;
            background: #ecfdf5; border: 1px solid #6ee7b7;
            color: #059669; padding: 4px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 600;
        }
        .detecting-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #10b981;
            animation: blink 1.2s ease-in-out infinite;
        }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

        .camera-wrapper {
            flex: 1; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: #0f172a; min-height: 300px;
        }
        #camera-stream {
            width: 100%; height: 100%; object-fit: cover;
            transform: scaleX(-1);
        }
        .face-guide {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 160px; height: 200px;
            border: 2px dashed rgba(16,185,129,0.6);
            border-radius: 50%;
            pointer-events: none;
        }

        .camera-bottombar {
            padding: 14px 18px;
            display: flex; align-items: center; justify-content: center;
            border-top: 1px solid #e2e8f0; gap: 12px;
        }
        .capture-btn {
            width: 56px; height: 56px; border-radius: 50%;
            background: #10b981; border: 4px solid #d1fae5; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; transition: all 0.15s; color: #fff;
            box-shadow: 0 4px 16px rgba(16,185,129,0.4);
        }
        .capture-btn:hover { background: #059669; transform: scale(1.05); }
        .retake-btn {
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 8px 16px;
            font-size: 0.78rem; font-weight: 600; color: #374151;
            cursor: pointer; display: none; transition: all 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .retake-btn.visible { display: block; }
        .retake-btn:hover { background: #e2e8f0; }

        /* RIGHT - Form */
        .form-side {
            width: 360px; flex-shrink: 0; padding: 36px 32px;
            border-left: 1px solid #e2e8f0;
        }
        @media (max-width: 768px) { .form-side { width: 100%; border-left: none; border-top: 1px solid #e2e8f0; padding: 24px 20px; } }

        .form-section-title { font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 6px; }
        .form-section-sub { font-size: 0.8rem; color: #94a3b8; margin-bottom: 28px; line-height: 1.5; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
        input[type="text"] {
            width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px;
            padding: 11px 14px; font-size: 0.875rem; color: #0f172a;
            outline: none; transition: all 0.15s; font-family: 'Inter', sans-serif;
        }
        input[type="text"]:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        input[type="text"]::placeholder { color: #94a3b8; }

        /* Preview */
        .preview-box {
            display: flex; align-items: center; gap: 14px;
            background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;
            padding: 14px; margin-bottom: 28px;
        }
        .preview-avatar {
            width: 56px; height: 56px; border-radius: 10px;
            background: #e2e8f0; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden;
        }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        .preview-text { font-size: 0.78rem; color: #64748b; line-height: 1.5; }
        .preview-text strong { color: #059669; display: block; margin-bottom: 2px; }

        /* Steps hint */
        .steps { margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .step-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: #0f172a; color: #fff;
            font-size: 0.65rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            margin-top: 1px;
        }
        .step-text { font-size: 0.78rem; color: #64748b; line-height: 1.5; }

        /* Buttons */
        .btn-save {
            width: 100%; padding: 13px; border-radius: 12px;
            border: none; background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            font-size: 0.9rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s; font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-save:disabled { background: #d1fae5; color: #6ee7b7; cursor: not-allowed; box-shadow: none; transform: none; }

        canvas { display: none; }

        /* Success state */
        .success-overlay {
            display: none; flex-direction: column; align-items: center; justify-content: center;
            padding: 48px 24px; text-align: center;
        }
        .success-overlay.show { display: flex; }
        .success-icon { font-size: 3rem; margin-bottom: 16px; }
        .success-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .success-sub { font-size: 0.875rem; color: #64748b; line-height: 1.6; }

        @media (max-width: 768px) {
            header { padding: 0; }
            main { padding: 24px 16px 60px; }
            h1 { font-size: 1.5rem; }
        }
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
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Kembali
            </a>
            <a href="{{ route('login') }}" class="btn-outline">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Login Admin
            </a>
        </div>
    </div>
</header>

<main>
    <div class="page-header">
        <div class="page-tag">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Registrasi Tamu Mandiri
        </div>
        <h1>Daftar Sebagai Tamu</h1>
        <p class="page-sub">Daftarkan wajah Anda dari mana saja — rumah, kantor, atau di perjalanan. Cukup izinkan kamera, ambil selfie, dan isi nama Anda.</p>
    </div>

    <div class="reg-card">

        <!-- Camera Side -->
        <div class="camera-side">
            <div class="camera-topbar">
                <div class="camera-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Kamera Selfie
                </div>
                <div class="detecting-badge" id="detecting-badge" style="display:none;">
                    <div class="detecting-dot"></div>
                    Kamera Aktif
                </div>
            </div>

            <div class="camera-wrapper">
                <video id="camera-stream" autoplay playsinline></video>
                <div class="face-guide" id="face-guide" style="display:none;"></div>
                <canvas id="canvas"></canvas>
            </div>

            <div class="camera-bottombar">
                <button class="capture-btn" id="capture-btn" title="Ambil Foto Selfie">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </button>
                <button class="retake-btn" id="retake-btn">🔄 Foto Ulang</button>
            </div>
        </div>

        <!-- Form Side -->
        <div class="form-side">
            <div class="form-section-title">Data Diri Anda</div>
            <p class="form-section-sub">Informasi ini akan digunakan untuk identifikasi saat Anda check-in di kantor.</p>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">Izinkan akses kamera di browser Anda</div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">Posisikan wajah di dalam panduan oval, lalu tekan tombol kamera</div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text">Isi nama lengkap dan klik <strong>Daftar Sekarang</strong></div>
                </div>
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" placeholder="Contoh: Budi Santoso" autocomplete="name">
            </div>

            <div class="preview-box" id="preview-box">
                <div class="preview-avatar" id="preview-avatar">
                    <span id="preview-placeholder">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <img id="preview-img" style="display:none;" alt="Preview foto">
                </div>
                <div class="preview-text" id="preview-text">
                    Ambil foto selfie terlebih dahulu
                </div>
            </div>

            <button class="btn-save" id="submit-btn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                Daftar Sekarang
            </button>
        </div>
    </div>
</main>

<script>
    const video    = document.getElementById('camera-stream');
    const canvas   = document.getElementById('canvas');
    const captureBtn = document.getElementById('capture-btn');
    const retakeBtn  = document.getElementById('retake-btn');
    const submitBtn  = document.getElementById('submit-btn');
    const namaInput  = document.getElementById('nama');
    const previewImg = document.getElementById('preview-img');
    const previewPlaceholder = document.getElementById('preview-placeholder');
    const previewText = document.getElementById('preview-text');
    const detectingBadge = document.getElementById('detecting-badge');
    const faceGuide = document.getElementById('face-guide');

    let capturedImage = null;

    // Start camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
        .then(stream => {
            video.srcObject = stream;
            detectingBadge.style.display = 'flex';
            faceGuide.style.display = 'block';
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Dapat Diakses',
                html: 'Pastikan Anda memberi izin akses kamera di browser.<br><small style="color:#94a3b8">Error: ' + err.message + '</small>',
                confirmButtonColor: '#10b981'
            });
        });

    // Capture
    captureBtn.addEventListener('click', () => {
        const maxWidth = 320;
        const scale = Math.min(1, maxWidth / video.videoWidth);
        canvas.width  = video.videoWidth  * scale;
        canvas.height = video.videoHeight * scale;
        const ctx = canvas.getContext('2d');
        // Mirror flip untuk konsistensi dengan tampilan kamera
        ctx.save();
        ctx.scale(-1, 1);
        ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
        ctx.restore();
        capturedImage = canvas.toDataURL('image/jpeg', 0.85);

        previewPlaceholder.style.display = 'none';
        previewImg.src = capturedImage;
        previewImg.style.display = 'block';
        previewText.innerHTML = '<strong>✅ Foto berhasil diambil</strong>Isi nama lengkap Anda dan klik Daftar.';

        retakeBtn.classList.add('visible');
        faceGuide.style.display = 'none';
        checkReady();
    });

    // Retake
    retakeBtn.addEventListener('click', () => {
        capturedImage = null;
        previewPlaceholder.style.display = 'flex';
        previewImg.style.display = 'none';
        previewText.innerHTML = 'Ambil foto selfie terlebih dahulu';
        retakeBtn.classList.remove('visible');
        faceGuide.style.display = 'block';
        checkReady();
    });

    namaInput.addEventListener('input', checkReady);

    function checkReady() {
        submitBtn.disabled = !(namaInput.value.trim() && capturedImage);
    }

    // Submit
    submitBtn.addEventListener('click', async () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2.5px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;"></span> Mendaftarkan...';

        try {
            const resp = await fetch('{{ route("tamu.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ nama: namaInput.value.trim(), image: capturedImage })
            });
            const result = await resp.json();

            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: '🎉 Pendaftaran Berhasil!',
                    html: `Hai, <strong>${namaInput.value}</strong>!<br>Wajah Anda sudah terdaftar di sistem.<br><br><small style="color:#64748b">Datang ke kantor dan lakukan check-in di scanner.</small>`,
                    confirmButtonText: 'Kembali ke Beranda',
                    confirmButtonColor: '#10b981',
                    allowOutsideClick: false
                });
                window.location.href = '{{ route("home") }}';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Pendaftaran Gagal',
                    text: result.message,
                    confirmButtonColor: '#10b981'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg> Daftar Sekarang';
            }
        } catch(e) {
            Swal.fire({ icon: 'error', title: 'Error Sistem', text: 'Terjadi kesalahan. Coba lagi.', confirmButtonColor: '#10b981' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg> Daftar Sekarang';
        }
    });
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
