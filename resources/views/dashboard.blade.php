<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */
        .sidebar {
            width: 220px; flex-shrink: 0;
            background: #fff; border-right: 1px solid #e2e8f0;
            display: flex; flex-direction: column;
            min-height: 100vh; position: fixed; left: 0; top: 0; bottom: 0;
            z-index: 40;
        }
        .sidebar-profile {
            padding: 24px 20px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .profile-row {
            display: flex; align-items: center; gap: 12px; margin-bottom: 6px;
        }
        .profile-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: #0f172a; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.875rem; font-weight: 700; flex-shrink: 0;
        }
        .profile-name { font-weight: 700; font-size: 0.875rem; color: #0f172a; }
        .profile-role { font-size: 0.75rem; color: #94a3b8; }
        .online-dot {
            display: flex; align-items: center; gap: 5px;
            font-size: 0.72rem; color: #059669; font-weight: 500;
            margin-top: 4px;
        }
        .online-dot::before {
            content: ''; width: 7px; height: 7px; border-radius: 50%;
            background: #10b981; display: inline-block;
        }

        .sidebar-nav {
            flex: 1; padding: 16px 12px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px; margin-bottom: 2px;
            font-size: 0.875rem; font-weight: 500; color: #64748b;
            text-decoration: none; transition: all 0.15s; cursor: pointer;
        }
        .nav-item:hover { background: #f8fafc; color: #0f172a; }
        .nav-item.active { background: #16a34a; color: #fff; font-weight: 600; }
        .nav-item.active .nav-icon { color: #fff; }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 16px 12px; border-top: 1px solid #f1f5f9;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 220px; flex: 1; display: flex; flex-direction: column;
        }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0 32px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 30;
        }
        .topbar-title { font-weight: 700; font-size: 0.95rem; color: #0f172a; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .notif-btn {
            width: 36px; height: 36px; border-radius: 8px;
            border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative; font-size: 16px;
        }
        .notif-dot {
            position: absolute; top: 7px; right: 7px;
            width: 7px; height: 7px; border-radius: 50%; background: #ef4444;
            border: 1.5px solid #fff;
        }

        .page-body { padding: 32px; }

        /* PAGE HEADER */
        .page-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 28px;
        }
        h1 { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .page-subtitle { color: #64748b; font-size: 0.875rem; }
        .btn-primary {
            display: flex; align-items: center; gap: 8px;
            background: #16a34a; color: #fff; border: none;
            padding: 10px 20px; border-radius: 10px;
            font-size: 0.875rem; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: background 0.15s;
        }
        .btn-primary:hover { background: #15803d; }

        /* STAT CARDS */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 22px 24px; position: relative; overflow: hidden;
        }
        .stat-card-top {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 12px;
        }
        .stat-label { font-size: 0.78rem; font-weight: 600; color: #64748b; }
        .stat-icon-box {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .icon-green  { background: #ecfdf5; }
        .icon-blue   { background: #eff6ff; }
        .icon-purple { background: #f5f3ff; }
        .stat-number { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: 8px; }
        .stat-trend {
            font-size: 0.75rem; font-weight: 500;
            display: flex; align-items: center; gap: 4px;
        }
        .trend-up   { color: #16a34a; }
        .trend-text { color: #94a3b8; }

        /* TABLE CARD */
        .table-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            overflow: hidden;
        }
        .table-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px;
        }
        .table-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
        .table-actions { display: flex; align-items: center; gap: 10px; }
        .search-box {
            display: flex; align-items: center; gap: 8px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 8px 14px; background: #f8fafc;
        }
        .search-box input {
            border: none; background: none; outline: none;
            font-size: 0.8rem; color: #374151; width: 170px;
        }
        .search-box input::placeholder { color: #94a3b8; }
        .btn-filter {
            display: flex; align-items: center; gap: 6px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 8px 14px; background: #fff;
            font-size: 0.8rem; font-weight: 500; color: #374151;
            cursor: pointer;
        }

        table { width: 100%; border-collapse: collapse; }
        thead { }
        th {
            text-align: left; padding: 10px 20px;
            font-size: 0.72rem; font-weight: 600; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
        }
        td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 0.85rem; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }

        .guest-cell { display: flex; align-items: center; gap: 12px; }
        .avatar-img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .avatar-initials {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
        }
        .guest-name { font-weight: 600; color: #0f172a; font-size: 0.875rem; }

        .confidence-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #ecfdf5; border: 1px solid #6ee7b7;
            color: #065f46; padding: 4px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 600;
        }
        .confidence-check { font-size: 12px; }

        .status-badge {
            display: inline-flex; align-items: center;
            padding: 5px 12px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }
        .status-active { background: #fef3c7; color: #92400e; }
        .status-done   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        .action-btn {
            width: 30px; height: 30px; border-radius: 6px;
            border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; font-size: 16px;
            transition: all 0.15s; position: relative;
        }
        .action-btn:hover { background: #f8fafc; color: #0f172a; }

        /* Alert */
        .alert-success {
            background: #ecfdf5; border: 1px solid #6ee7b7;
            color: #065f46; padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.875rem; font-weight: 500;
        }

        /* Pagination */
        .table-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 24px; border-top: 1px solid #f1f5f9;
            font-size: 0.8rem; color: #64748b;
        }
        .page-btns { display: flex; gap: 8px; }
        .page-btn-text {
            padding: 6px 14px; border-radius: 8px;
            border: 1px solid #e2e8f0; background: #fff;
            font-size: 0.8rem; font-weight: 500; color: #374151;
            cursor: pointer; text-decoration: none; transition: all 0.15s;
        }
        .page-btn-text:hover { background: #f8fafc; }
        .page-btn-text.disabled { opacity: 0.4; pointer-events: none; }

        /* Dropdown */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-menu {
            display: none; position: absolute; right: 0; top: 36px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08); min-width: 160px; z-index: 100;
            padding: 6px;
        }
        .dropdown:hover .dropdown-menu { display: block; }
        .dropdown-item {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px; border-radius: 7px;
            font-size: 0.8rem; color: #374151; cursor: pointer;
            transition: background 0.1s; text-decoration: none;
        }
        .dropdown-item:hover { background: #f8fafc; }
        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; }

        /* Delete form inline */
        .delete-form { display: inline; }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-profile">
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
        <a href="{{ route('dashboard') }}" class="nav-item active">
            <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Dashboard
        </a>
        <a href="{{ route('tamu.register') }}" class="nav-item">
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

<!-- MAIN -->
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Face Recognition</span>
        <div class="topbar-actions">
            <div class="notif-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <div class="notif-dot"></div>
            </div>
        </div>
    </div>

    <div class="page-body">

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Ringkasan Hari Ini</h1>
                <p class="page-subtitle">Pantau aktivitas tamu dan performa sistem AI secara real-time.</p>
            </div>
            <a href="{{ route('tamu.register') }}" class="btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Daftarkan Tamu
            </a>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-label">Kunjungan Aktif</span>
                    <div class="stat-icon-box icon-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div class="stat-number">{{ $sedangBerkunjung }}</div>
                <div class="stat-trend trend-up">
                    <span>Sedang di lokasi</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-label">Total Hari Ini</span>
                    <div class="stat-icon-box icon-blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <div class="stat-number">{{ $totalKunjunganHariIni }}</div>
                <div class="stat-trend">
                    <span class="trend-text">Total kunjungan hari ini</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-label">Tamu Terdaftar AI</span>
                    <div class="stat-icon-box icon-purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/><circle cx="8.5" cy="9" r="1.5"/><circle cx="15.5" cy="9" r="1.5"/><path d="M9 13h6"/></svg>
                    </div>
                </div>
                <div class="stat-number">{{ $totalTamuTerdaftar }}</div>
                <div class="stat-trend">
                    <span class="trend-text">Profil wajah di database</span>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">Riwayat Tamu Terbaru</span>
                <div class="table-actions">
                    <form method="GET" action="{{ route('dashboard') }}" style="display:flex; gap:10px;">
                        <div class="search-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="search" placeholder="Cari nama atau instansi..." value="{{ request('search') }}">
                        </div>
                        <select name="status" class="btn-filter" style="padding:8px 12px; font-size:0.8rem; border:1px solid #e2e8f0; border-radius:8px; outline:none; cursor:pointer;">
                            <option value="">Semua Status</option>
                            <option value="sedang berkunjung" {{ request('status') == 'sedang berkunjung' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </form>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Profil</th>
                        <th>Keperluan</th>
                        <th>Waktu Masuk</th>
                        <th>Status AI</th>
                        <th>Status Kunjungan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kunjungans as $kunjungan)
                    @php
                        $nama = $kunjungan->tamu->nama ?? 'Unknown';
                        $initials = implode('', array_map(fn($w) => strtoupper(substr($w, 0, 1)), array_slice(explode(' ', $nama), 0, 2)));
                        $palette = ['#dbeafe|#1d4ed8','#fce7f3|#be185d','#d1fae5|#065f46','#fef3c7|#92400e','#ede9fe|#5b21b6','#ffedd5|#9a3412'];
                        $colors = explode('|', $palette[ord(strtolower($initials[0] ?? 'a')) % 6]);
                    @endphp
                    <tr>
                        <td>
                            <div class="guest-cell">
                                @if($kunjungan->tamu && $kunjungan->tamu->foto)
                                    <img src="{{ asset('storage/' . $kunjungan->tamu->foto) }}" class="avatar-img" alt="{{ $nama }}">
                                @else
                                    <div class="avatar-initials" style="background:{{ $colors[0] }}; color:{{ $colors[1] }};">{{ $initials }}</div>
                                @endif
                                <div>
                                    <div class="guest-name">{{ $nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#374151;">{{ $kunjungan->keperluan ?: '—' }}</td>
                        <td style="color:#374151; font-variant-numeric: tabular-nums;">
                            {{ \Carbon\Carbon::parse($kunjungan->jam_masuk)->format('H:i') }} WIB
                        </td>
                        <td>
                            @if($kunjungan->confidence_score)
                                <span class="confidence-badge">
                                    <span class="confidence-check">✓</span>
                                    Dikenali ({{ number_format($kunjungan->confidence_score, 0) }}%)
                                </span>
                            @else
                                <span style="color:#94a3b8; font-size:0.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($kunjungan->status == 'sedang berkunjung')
                                <span class="status-badge status-active">Menunggu</span>
                            @else
                                <span class="status-badge status-done">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <div class="action-btn">⋮</div>
                                <div class="dropdown-menu">
                                    <form class="delete-form" action="{{ route('tamu.destroy', $kunjungan->tamu->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus seluruh data wajah & riwayat tamu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" style="width:100%; border:none; background:none; text-align:left; cursor:pointer;">
                                            🗑 Hapus Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:60px; color:#94a3b8;">
                            <div style="font-size:2.5rem; margin-bottom:10px;">📭</div>
                            <div>Belum ada data kunjungan.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="table-footer">
                <span>Menampilkan {{ $kunjungans->firstItem() ?? 0 }}–{{ $kunjungans->lastItem() ?? 0 }} dari {{ $kunjungans->total() }} tamu</span>
                <div class="page-btns">
                    @if($kunjungans->onFirstPage())
                        <span class="page-btn-text disabled">Sebelumnya</span>
                    @else
                        <a href="{{ $kunjungans->previousPageUrl() }}" class="page-btn-text">Sebelumnya</a>
                    @endif
                    @if($kunjungans->hasMorePages())
                        <a href="{{ $kunjungans->nextPageUrl() }}" class="page-btn-text">Selanjutnya</a>
                    @else
                        <span class="page-btn-text disabled">Selanjutnya</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
