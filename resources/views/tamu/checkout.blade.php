<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-Out Tamu</title>
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
            display: flex;
            flex-direction: column;
        }

        /* TOPBAR */
        header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 32px; height: 56px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .header-titles { display: flex; flex-direction: column; }
        .header-brand { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
        .header-sub   { font-size: 0.72rem; color: #94a3b8; }
        .mode-badge {
            display: flex; align-items: center; gap: 7px;
            border: 1.5px solid #fca5a5; border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.78rem; font-weight: 700; color: #dc2626;
            background: #fff5f5;
        }
        .mode-icon { font-size: 14px; }

        /* MAIN */
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 24px; }

        .scanner-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            overflow: hidden; display: flex; width: 100%; max-width: 880px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        @media (max-width: 768px) { .scanner-card { flex-direction: column; } }

        /* LEFT — Camera */
        .camera-side {
            flex: 1.4; position: relative; background: #1e293b;
            min-height: 380px; display: flex; flex-direction: column;
        }
        .camera-wrapper {
            flex: 1; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        #camera-stream {
            width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);
        }
        canvas { display: none; }

        /* Scanning face box */
        .scan-box {
            position: absolute;
            width: 140px; height: 180px;
            border: 1.5px dashed #f87171;
            left: 50%; top: 50%;
            transform: translate(-50%, -55%);
            border-radius: 4px;
            pointer-events: none;
        }
        .scan-box::before, .scan-box::after {
            content: ''; position: absolute;
            width: 16px; height: 16px; border-color: #f87171; border-style: solid;
        }
        .scan-box::before { top: -2px; left: -2px; border-width: 2px 0 0 2px; }
        .scan-box::after  { bottom: -2px; right: -2px; border-width: 0 2px 2px 0; }
        .scan-corner-tr { position: absolute; top: -2px; right: -2px; width: 16px; height: 16px; border-top: 2px solid #f87171; border-right: 2px solid #f87171; }
        .scan-corner-bl { position: absolute; bottom: -2px; left: -2px; width: 16px; height: 16px; border-bottom: 2px solid #f87171; border-left: 2px solid #f87171; }

        /* Scan line animation */
        .scan-line {
            position: absolute;
            left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #f87171 50%, transparent 100%);
            animation: scan-move 2.5s ease-in-out infinite;
            pointer-events: none;
            opacity: 0.8;
        }
        @keyframes scan-move {
            0%   { top: 0%; }
            50%  { top: calc(100% - 2px); }
            100% { top: 0%; }
        }

        .camera-status-bar {
            position: absolute; bottom: 0; left: 0; right: 0;
            display: flex; align-items: center; justify-content: center;
            padding: 14px;
        }
        .active-pill {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.95); border-radius: 20px;
            padding: 8px 18px; font-size: 0.8rem; font-weight: 700;
            color: #0f172a; box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .active-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #ef4444;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }

        /* RIGHT — Status Panel */
        .status-side {
            width: 300px; flex-shrink: 0;
            padding: 32px 28px;
            border-left: 1px solid #e2e8f0;
            display: flex; flex-direction: column; align-items: center;
            justify-content: space-between;
        }
        @media (max-width: 768px) {
            .status-side { width: 100%; border-left: none; border-top: 1px solid #e2e8f0; }
        }

        .status-main {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
        }

        .status-icon {
            font-size: 3rem; margin-bottom: 16px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .status-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .status-desc  { font-size: 0.8rem; color: #64748b; line-height: 1.6; max-width: 200px; }

        /* Greeting (shown on success) */
        .greeting-card {
            background: #fff5f5; border: 1px solid #fca5a5; border-radius: 12px;
            padding: 18px; text-align: left; width: 100%;
            display: none;
        }
        .greeting-card.visible { display: block; }
        .greeting-top { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .greeting-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: #ef4444; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .greeting-name { font-weight: 700; color: #0f172a; font-size: 0.95rem; }
        .greeting-action { color: #64748b; font-size: 0.75rem; margin-top: 2px; }
        .success-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #ef4444; color: #fff;
            padding: 4px 12px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 700;
        }

        /* Status text area */
        .status-text-box {
            width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 14px; text-align: center; margin-top: 16px;
        }
        #status-text { font-size: 0.85rem; font-weight: 600; color: #374151; }

        /* Nav buttons at bottom */
        .side-actions {
            width: 100%; border-top: 1px solid #f1f5f9; padding-top: 16px;
            display: flex; gap: 8px; flex-direction: column;
        }
        .btn-switch, .btn-dashboard {
            width: 100%; padding: 10px; border-radius: 10px;
            border: 1.5px solid #e2e8f0; background: #fff;
            font-size: 0.8rem; font-weight: 600; color: #374151;
            cursor: pointer; text-decoration: none; text-align: center;
            transition: all 0.15s; display: flex; align-items: center; justify-content: center; gap: 7px;
        }
        .btn-switch:hover { border-color: #ef4444; color: #dc2626; background: #fff5f5; }
        .btn-dashboard:hover { border-color: #3b82f6; color: #2563eb; background: #eff6ff; }
        .btn-pause {
            width: 100%; padding: 10px; border-radius: 10px;
            border: none; background: #f1f5f9;
            font-size: 0.8rem; font-weight: 600; color: #374151;
            cursor: pointer; transition: all 0.15s;
        }
        .btn-pause:hover { background: #e2e8f0; }

        /* SIDEBAR (Offcanvas) */
        .sidebar-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); z-index: 40; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
        .sidebar-overlay.show { opacity: 1; pointer-events: auto; }
        .sidebar { width: 250px; flex-shrink: 0; background: #fff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; min-height: 100vh; position: fixed; left: 0; top: 0; bottom: 0; z-index: 50; transform: translateX(-100%); transition: transform 0.2s ease-in-out; }
        .sidebar.open { transform: translateX(0); }
        .sidebar-profile { padding: 24px 20px 20px; border-bottom: 1px solid #f1f5f9; position: relative; }
        .sidebar-close { position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; transition: background 0.15s; }
        .sidebar-close:hover { background: #f1f5f9; color: #0f172a; }
        .profile-row { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
        .profile-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; flex-shrink: 0; }
        .profile-name { font-weight: 700; font-size: 0.875rem; color: #0f172a; }
        .profile-role { font-size: 0.75rem; color: #94a3b8; }
        .online-dot { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: #059669; font-weight: 500; margin-top: 4px; }
        .online-dot::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; margin-bottom: 2px; font-size: 0.875rem; font-weight: 500; color: #64748b; text-decoration: none; transition: all 0.15s; cursor: pointer; }
        .nav-item:hover { background: #f8fafc; color: #0f172a; }
        .nav-item.active { background: #ef4444; color: #fff; font-weight: 600; }
        .nav-item.active .nav-icon { color: #fff; }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid #f1f5f9; }

        .menu-btn { font-size: 18px; cursor: pointer; color: #64748b; background: none; border: none; display: flex; align-items: center; }

        /* Responsive */
        @media (max-width: 768px) {
            header { padding: 0 16px; height: auto; min-height: 56px; flex-wrap: wrap; gap: 10px; padding-top: 10px; padding-bottom: 10px; }
            main { padding: 16px 12px; }
            .scanner-card { flex-direction: column; border-radius: 12px; }
            .camera-side { min-height: 280px; }
            .status-side { width: 100%; border-left: none; border-top: 1px solid #e2e8f0; padding: 24px 16px; }
            .status-icon { font-size: 2.5rem; margin-bottom: 12px; }
            .status-title { font-size: 1rem; }
            .mode-badge { font-size: 0.7rem; padding: 4px 10px; }
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
            <div class="profile-avatar">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'A' }}</div>
            <div>
                <div class="profile-name">{{ Auth::check() ? Auth::user()->name : 'Admin' }}</div>
                <div class="profile-role">Administrator</div>
            </div>
        </div>
        <div class="online-dot">Online</div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Dashboard
        </a>
        <a href="{{ route('tamu.checkin') }}" class="nav-item">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span> Check-In
        </a>
        <a href="{{ route('tamu.checkout') }}" class="nav-item active">
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
        <div class="header-titles">
            <span class="header-brand">Face Recognition</span>
           
        </div>
    </div>
    
</header>

<main>
    <div class="scanner-card">

        <!-- Camera -->
        <div class="camera-side">
            <div class="camera-wrapper">
                <video id="camera-stream" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <div class="scan-line" id="scan-line"></div>
                <div class="scan-box" id="face-box">
                    <div class="scan-corner-tr"></div>
                    <div class="scan-corner-bl"></div>
                </div>
            </div>
            <div class="camera-status-bar">
                <div class="active-pill" id="active-pill">
                    <div class="active-dot"></div>
                    AKTIF • MEMINDAI
                </div>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="status-side">
            <div class="status-main">
                <div id="idle-state">
                    <div class="status-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </div>
                    <div class="status-title" id="status-title">Menunggu Wajah...</div>
                    <div class="status-desc">Silakan berdiri di depan kamera untuk melakukan check-out otomatis.</div>
                </div>

                <div class="greeting-card" id="greeting-card">
                    <div class="greeting-top">
                        <div class="greeting-avatar">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <div class="greeting-name" id="greeting-name"></div>
                            <div class="greeting-action" id="greeting-action"></div>
                        </div>
                    </div>
                    <span class="success-badge">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Check-Out Berhasil
                    </span>
                </div>

                <div class="status-text-box">
                    <span id="status-text">Menunggu wajah...</span>
                </div>
            </div>

            <div class="side-actions">
                <a href="{{ route('dashboard') }}" class="btn-dashboard">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kembali ke Dashboard
                </a>
                <a href="{{ route('tamu.checkin') }}" class="btn-switch">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Pindah ke Check-In
                </a>
                <button class="btn-pause" id="toggle-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="display:inline;vertical-align:-2px;" id="toggle-icon"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    <span id="toggle-label">Jeda Scanner</span>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
    const video       = document.getElementById('camera-stream');
    const canvas      = document.getElementById('canvas');
    const scanLine    = document.getElementById('scan-line');
    const activePill  = document.getElementById('active-pill');
    const statusText  = document.getElementById('status-text');
    const statusTitle = document.getElementById('status-title');
    const greetingCard   = document.getElementById('greeting-card');
    const greetingName   = document.getElementById('greeting-name');
    const greetingAction = document.getElementById('greeting-action');
    const toggleBtn   = document.getElementById('toggle-btn');

    let isScanning = true;

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(s => { video.srcObject = s; })
        .catch(err => { Swal.fire('Error Kamera', err.message, 'error'); });

    function captureFrame() {
        const maxW = 320;
        const scale = Math.min(1, maxW / video.videoWidth);
        canvas.width  = video.videoWidth  * scale;
        canvas.height = video.videoHeight * scale;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        return canvas.toDataURL('image/jpeg', 0.7);
    }

    function setStatus(text, color) {
        statusText.textContent = text;
        statusText.style.color = color || '#374151';
    }

    function showGreeting(nama, confidence) {
        const idleState = document.getElementById('idle-state');
        if (idleState) idleState.style.display = 'none';
        greetingName.textContent   = `Sampai jumpa, ${nama}!`;
        greetingAction.textContent = `Check-out berhasil • Confidence: ${confidence}%`;
        greetingCard.classList.add('visible');
    }
    function hideGreeting() {
        greetingCard.classList.remove('visible');
        const idleState = document.getElementById('idle-state');
        if (idleState) idleState.style.display = 'block';
    }

    function resumeScanning() {
        scanLine.style.display = '';
        activePill.innerHTML = '<div class="active-dot"></div> AKTIF • MEMINDAI';
        setStatus('Menunggu wajah...', '#374151');
        statusTitle.textContent = 'Menunggu Wajah...';
        hideGreeting();
        isScanning = true;
        document.getElementById('toggle-icon').innerHTML = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
        document.getElementById('toggle-label').textContent = 'Jeda Scanner';
    }

    async function processScan() {
        if (!isScanning) return;
        isScanning = false;
        setStatus('Memproses wajah...', '#d97706');

        const img = captureFrame();
        try {
            const resp = await fetch('{{ route("tamu.checkout-process") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ image: img })
            });
            const result = await resp.json();

            if (result.success) {
                if (result.action === 'auto') {
                    setStatus(result.message, '#dc2626');
                    statusTitle.textContent = 'Wajah Dikenali!';
                    showGreeting(result.nama, result.confidence);
                    await doConfirm(result.tamu_id, result.confidence, result.nama);
                } else if (result.action === 'confirm') {
                    scanLine.style.display = 'none';
                    const cr = await Swal.fire({
                        title: 'Konfirmasi Check-Out',
                        html: `Apakah ini <strong>${result.nama}</strong>?<br><small style="color:#6b7280">Confidence: ${result.confidence}%</small>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: '✓ Ya, Check-Out',
                        cancelButtonText: 'Bukan'
                    });
                    if (cr.isConfirmed) {
                        showGreeting(result.nama, result.confidence);
                        await doConfirm(result.tamu_id, result.confidence, result.nama);
                    } else {
                        setStatus('Dibatalkan.', '#6b7280');
                        setTimeout(resumeScanning, 1500);
                    }
                }
            } else {
                const color = result.action === 'retry' ? '#dc2626' : result.action === 'not_checkedin' ? '#d97706' : '#6b7280';
                setStatus(result.message || 'Menunggu wajah...', color);
                setTimeout(resumeScanning, 2500);
            }
        } catch {
            setStatus('Terjadi kesalahan sistem.', '#dc2626');
            setTimeout(resumeScanning, 3000);
        }
    }

    async function doConfirm(tamu_id, confidence, nama) {
        try {
            const resp = await fetch('{{ route("tamu.checkout-confirm") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ tamu_id, confidence })
            });
            const result = await resp.json();

            if (result.success) {
                setStatus('✓ Check-Out Berhasil!', '#dc2626');
                await Swal.fire({
                    title: '✅ Check-Out Berhasil!',
                    html: `
                        <div style="font-size: 1.1rem; margin-top: 8px; font-weight: 600; color: #0f172a;">Sampai jumpa kembali, <strong>${nama}</strong>!</div>
                        <div style="color: #64748b; margin-top: 6px; font-size: 0.85rem;">Status kunjungan Anda telah diperbarui menjadi <strong style="color:#dc2626;">Selesai</strong>.</div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Selesai',
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Gagal Check-Out',
                    text: result.message || 'Data kunjungan tidak dapat diperbarui.',
                    confirmButtonColor: '#dc2626'
                });
            }
            setTimeout(() => { hideGreeting(); resumeScanning(); }, 500);
        } catch {
            Swal.fire('Error', 'Gagal menyimpan data check-out.', 'error');
            hideGreeting();
            resumeScanning();
        }
    }

    setInterval(() => {
        if (isScanning && video.readyState === 4) processScan();
    }, 2000);

    toggleBtn.addEventListener('click', () => {
        if (isScanning) {
            isScanning = false;
            scanLine.style.display = 'none';
            activePill.innerHTML = '<span style="color:#94a3b8">DIJEDA</span>';
            setStatus('Scanner dijeda.', '#94a3b8');
            document.getElementById('toggle-icon').innerHTML = '<polygon points="5 3 19 12 5 21 5 3"/>';
            document.getElementById('toggle-label').textContent = 'Mulai Scanner';
        } else {
            resumeScanning();
        }
    });

    // Sidebar Toggle Logic
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarClose = document.getElementById('sidebar-close');

    function toggleSidebar() {
        if(sidebar) sidebar.classList.toggle('open');
        if(sidebarOverlay) sidebarOverlay.classList.toggle('show');
    }

    if(menuBtn) menuBtn.addEventListener('click', toggleSidebar);
    if(sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
    if(sidebarClose) sidebarClose.addEventListener('click', toggleSidebar);
</script>
</body>
</html>
