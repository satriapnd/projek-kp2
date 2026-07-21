<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Wajah</title>
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

        /* TOPBAR */
        header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 32px; height: 56px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .header-left {
            display: flex; align-items: center; gap: 16px;
        }
        .menu-btn {
            font-size: 18px; cursor: pointer; color: #64748b; background: none;
            border: none; display: flex; align-items: center;
        }
        .header-brand { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
        .header-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: #0f172a; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
        }

        /* PAGE */
        main { max-width: 1000px; margin: 0 auto; padding: 40px 24px; }
        h1 { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .page-sub { color: #64748b; font-size: 0.875rem; margin-bottom: 32px; }

        /* MAIN CARD */
        .reg-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            overflow: hidden; display: flex;
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
            background: #000;
        }
        #camera-stream {
            /* object-fit: contain agar tampilan = yang diambil AI */
            width: 100%; height: 100%; object-fit: contain;
            transform: scaleX(-1);
        }

        .camera-bottombar {
            padding: 14px 18px;
            display: flex; align-items: center; justify-content: center;
            border-top: 1px solid #e2e8f0; gap: 12px;
        }
        .capture-btn {
            width: 48px; height: 48px; border-radius: 50%;
            background: #10b981; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; transition: background 0.15s; color: #fff;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .capture-btn:hover { background: #059669; }
        .retake-btn {
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 7px 14px;
            font-size: 0.78rem; font-weight: 600; color: #374151;
            cursor: pointer; display: none; transition: all 0.15s;
        }
        .retake-btn.visible { display: block; }

        /* RIGHT - Form */
        .form-side {
            width: 340px; flex-shrink: 0; padding: 32px 28px;
            border-left: 1px solid #e2e8f0;
        }
        @media (max-width: 768px) { .form-side { width: 100%; border-left: none; border-top: 1px solid #e2e8f0; } }

        .form-section-title { font-weight: 700; font-size: 1rem; color: #0f172a; margin-bottom: 24px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
        input[type="text"] {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 10px 14px; font-size: 0.875rem; color: #0f172a;
            outline: none; transition: border-color 0.15s; font-family: 'Inter', sans-serif;
        }
        input[type="text"]:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        input[type="text"]::placeholder { color: #94a3b8; }

        /* Preview box */
        .preview-box {
            display: flex; align-items: center; gap: 12px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 12px; margin-bottom: 24px;
        }
        .preview-avatar {
            width: 52px; height: 52px; border-radius: 8px;
            background: #e2e8f0; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden;
        }
        .preview-avatar img {
            width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);
        }
        .preview-placeholder { color: #94a3b8; font-size: 22px; }
        .preview-text { font-size: 0.8rem; color: #64748b; line-height: 1.5; }

        /* Buttons */
        .btn-row { display: flex; gap: 10px; }
        .btn-cancel {
            flex: 1; padding: 11px; border-radius: 10px;
            border: 1.5px solid #e2e8f0; background: #fff;
            font-size: 0.875rem; font-weight: 600; color: #374151;
            cursor: pointer; transition: all 0.15s; font-family: 'Inter', sans-serif;
        }
        .btn-cancel:hover { background: #f8fafc; }
        .btn-save {
            flex: 1.5; padding: 11px; border-radius: 10px;
            border: none; background: #16a34a; color: #fff;
            font-size: 0.875rem; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            transition: background 0.15s; font-family: 'Inter', sans-serif;
        }
        .btn-save:hover { background: #15803d; }
        .btn-save:disabled { background: #d1fae5; color: #6ee7b7; cursor: not-allowed; }

        canvas { display: none; }

        /* SIDEBAR (Offcanvas) */
        .sidebar-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4);
            z-index: 40; opacity: 0; pointer-events: none; transition: opacity 0.2s;
        }
        .sidebar-overlay.show { opacity: 1; pointer-events: auto; }
        
        .sidebar {
            width: 250px; flex-shrink: 0; background: #fff; border-right: 1px solid #e2e8f0;
            display: flex; flex-direction: column;
            min-height: 100vh; position: fixed; left: 0; top: 0; bottom: 0;
            z-index: 50; transform: translateX(-100%); transition: transform 0.2s ease-in-out;
        }
        .sidebar.open { transform: translateX(0); }
        .sidebar-profile { padding: 24px 20px 20px; border-bottom: 1px solid #f1f5f9; position: relative; }
        .sidebar-close {
            position: absolute; top: 16px; right: 16px;
            background: none; border: none; cursor: pointer; color: #94a3b8;
            padding: 4px; border-radius: 6px; transition: background 0.15s;
        }
        .sidebar-close:hover { background: #f1f5f9; color: #0f172a; }
        .profile-row { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
        .profile-avatar {
            width: 40px; height: 40px; border-radius: 50%; background: #0f172a; color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; flex-shrink: 0;
        }
        .profile-name { font-weight: 700; font-size: 0.875rem; color: #0f172a; }
        .profile-role { font-size: 0.75rem; color: #94a3b8; }
        .online-dot { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: #059669; font-weight: 500; margin-top: 4px; }
        .online-dot::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; margin-bottom: 2px;
            font-size: 0.875rem; font-weight: 500; color: #64748b; text-decoration: none; transition: all 0.15s; cursor: pointer;
        }
        .nav-item:hover { background: #f8fafc; color: #0f172a; }
        .nav-item.active { background: #16a34a; color: #fff; font-weight: 600; }
        .nav-item.active .nav-icon { color: #fff; }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid #f1f5f9; }

        /* Responsive */
        @media (max-width: 768px) {
            header { padding: 0 16px; height: auto; min-height: 56px; }
            main { padding: 24px 16px; }
            h1 { font-size: 1.5rem; }
            .page-sub { font-size: 0.8rem; margin-bottom: 24px; }
            .reg-card { flex-direction: column; border-radius: 12px; }
            .camera-side { min-height: 300px; }
            .form-side { padding: 24px 16px; border-left: none; border-top: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR OVERLAY & CONTAINER -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
        <button class="sidebar-close" id="sidebar-close" title="Tutup Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="profile-row">
            <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <div class="profile-name">{{ Auth::user()->name }}</div>
                <div class="profile-role">Administrator</div>
            </div>
        </div>
        <div class="online-dot">Online</div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Dashboard
        </a>
        <a href="{{ route('tamu.register') }}" class="nav-item active">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span> Daftar Tamu
        </a>
        <a href="{{ route('tamu.checkin') }}" class="nav-item">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span> Check-In
        </a>
        <a href="{{ route('tamu.checkout') }}" class="nav-item">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span> Check-Out
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="width:100%; border:none; background:none; text-align:left; cursor:pointer; color:#ef4444;">
                <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span> Logout
            </button>
        </form>
    </div>
</aside>

<header>
    <div class="header-left">
        <button class="menu-btn" id="menu-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="header-brand">Face Recognition</span>
    </div>
    <div class="header-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
</header>

<main>
    <h1>Registrasi Wajah</h1>
    <p class="page-sub">Arahkan wajah Anda ke kamera, lalu tekan tombol kamera untuk mengambil foto. Pastikan wajah terlihat jelas.</p>

    <div class="reg-card">

        <!-- Camera Side -->
        <div class="camera-side">
            <div class="camera-topbar">
                <div class="camera-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Kamera Aktif
                </div>
                <div class="detecting-badge" id="detecting-badge" style="display:none;">
                    <div class="detecting-dot"></div>
                    Mendeteksi
                </div>
            </div>

            <div class="camera-wrapper">
                <video id="camera-stream" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <!-- Tidak ada face box — tampilan kamera = frame yang dikirim ke AI -->
            </div>

            <div class="camera-bottombar">
                <button class="capture-btn" id="capture-btn" title="Ambil Foto">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </button>
                <button class="retake-btn" id="retake-btn">Mulai Ulang</button>
            </div>
        </div>

        <!-- Form Side -->
        <div class="form-side">
            <div class="form-section-title">Data Profil</div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" placeholder="Masukkan nama lengkap">
            </div>

            <div class="form-group">
                <label>Preview Foto</label>
                <div class="preview-box">
                    <div class="preview-avatar" id="preview-avatar">
                        <span class="preview-placeholder" id="preview-placeholder">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <img id="preview-img" style="display:none;" alt="Preview">
                    </div>
                    <span class="preview-text" id="preview-text">Pastikan wajah terlihat jelas dan tidak terhalang aksesori.</span>
                </div>
            </div>

            <div class="btn-row">
                <button class="btn-cancel" onclick="history.back()">Batal</button>
                <button class="btn-save" id="submit-btn" disabled>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Data
                </button>
            </div>
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

    let capturedImage = null;

    // Start camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(stream => {
            video.srcObject = stream;
            detectingBadge.style.display = 'flex';
        })
        .catch(err => {
            Swal.fire('Error Kamera', 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.', 'error');
        });

    // Camera ready — tidak ada face badge

    // Capture — gunakan max 320px sama seperti versi lama agar AI tidak timeout
    captureBtn.addEventListener('click', () => {
        const maxWidth = 320;
        const scale = Math.min(1, maxWidth / video.videoWidth);
        canvas.width  = video.videoWidth  * scale;
        canvas.height = video.videoHeight * scale;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        capturedImage = canvas.toDataURL('image/jpeg', 0.8);

        // Show preview
        previewPlaceholder.style.display = 'none';
        previewImg.src = capturedImage;
        previewImg.style.display = 'block';
        previewText.textContent = 'Foto berhasil diambil. Isi nama lalu simpan.';

        retakeBtn.classList.add('visible');
        checkReady();
    });

    // Retake
    retakeBtn.addEventListener('click', () => {
        capturedImage = null;
        previewPlaceholder.style.display = 'flex';
        previewImg.style.display = 'none';
        previewText.textContent = 'Pastikan wajah terlihat jelas dan tidak terhalang aksesori.';
        retakeBtn.classList.remove('visible');
        checkReady();
    });

    namaInput.addEventListener('input', checkReady);

    function checkReady() {
        submitBtn.disabled = !(namaInput.value.trim() && capturedImage);
    }

    // Submit
    submitBtn.addEventListener('click', async () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;"></span> Menyimpan...';

        try {
            const resp = await fetch('{{ route("tamu.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ nama: namaInput.value, image: capturedImage })
            });
            const result = await resp.json();

            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 2000, showConfirmButton: false })
                    .then(() => { window.location.reload(); });
            } else {
                Swal.fire('Gagal', result.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Data';
            }
        } catch {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Data';
        }
    });

    // Sidebar Toggle Logic
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarClose = document.getElementById('sidebar-close');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('show');
    }

    menuBtn.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);
    sidebarClose.addEventListener('click', toggleSidebar);
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
