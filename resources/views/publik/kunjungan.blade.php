<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunjungan Hari Ini — Buku Tamu Digital</title>
    <meta name="description" content="Pantau aktivitas tamu secara real-time. Sistem manajemen kunjungan berbasis Face Recognition AI.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }

        /* NAV */
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
        .btn-login {
            display: flex; align-items: center; gap: 8px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 8px 18px; font-size: 0.875rem; font-weight: 600;
            color: #374151; background: #fff; text-decoration: none;
            transition: all 0.15s;
        }
        .btn-login:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* MAIN */
        main { max-width: 1100px; margin: 0 auto; padding: 48px 24px 80px; }

        h1 { font-size: 2rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .page-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 40px; max-width: 520px; line-height: 1.6; }

        /* STAT CARDS */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
            margin-bottom: 36px;
        }
        @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 24px 28px;
        }
        .stat-card-header {
            display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
        }
        .stat-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .stat-icon.green  { background: #ecfdf5; }
        .stat-icon.red    { background: #fef2f2; }
        .stat-icon.gray   { background: #f1f5f9; }
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; }
        .stat-number { font-size: 2.25rem; font-weight: 800; color: #0f172a; line-height: 1; }

        /* TABLE SECTION */
        .table-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            overflow: hidden;
        }
        .table-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
        }
        .table-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .search-box {
            display: flex; align-items: center; gap: 8px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 8px 14px; background: #f8fafc;
        }
        .search-box input {
            border: none; background: none; outline: none;
            font-size: 0.875rem; color: #374151; width: 180px;
        }
        .search-box input::placeholder { color: #94a3b8; }
        .search-icon { color: #94a3b8; font-size: 13px; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th {
            text-align: left; padding: 12px 20px;
            font-size: 0.75rem; font-weight: 600; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1px solid #f1f5f9;
        }
        td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }

        .guest-cell { display: flex; align-items: center; gap: 12px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
            object-fit: cover;
        }
        .avatar-img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .guest-name { font-weight: 600; color: #0f172a; }

        /* Avatar color variants based on first letter */
        .av-a { background: #dbeafe; color: #1d4ed8; }
        .av-b { background: #fce7f3; color: #be185d; }
        .av-c { background: #d1fae5; color: #065f46; }
        .av-d { background: #fef3c7; color: #92400e; }
        .av-e { background: #ede9fe; color: #5b21b6; }
        .av-f { background: #ffedd5; color: #9a3412; }
        .av-g { background: #cffafe; color: #0e7490; }
        .av-default { background: #f1f5f9; color: #475569; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }
        .status-active { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-done   { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        .time-text { color: #374151; font-variant-numeric: tabular-nums; }
        .time-dash { color: #cbd5e1; }

        /* Pagination */
        .pagination-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 24px; border-top: 1px solid #f1f5f9;
            font-size: 0.8rem; color: #64748b;
        }
        .page-btns { display: flex; align-items: center; gap: 4px; }
        .page-btn {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #e2e8f0; background: #fff;
            font-size: 0.8rem; font-weight: 600; color: #374151;
            cursor: pointer; text-decoration: none; transition: all 0.15s;
        }
        .page-btn:hover { background: #f8fafc; }
        .page-btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 24px; color: #94a3b8; }
        .empty-icon { font-size: 3rem; margin-bottom: 12px; }
        .empty-text { font-size: 0.9rem; }

        /* Footer */
        footer { text-align: center; padding: 32px; font-size: 0.8rem; color: #94a3b8; }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-inner { flex-direction: column; gap: 12px; }
            main { padding: 24px 16px; }
            .header-content { flex-direction: column; align-items: flex-start; gap: 16px; }
            .stats-grid { grid-template-columns: 1fr; gap: 12px; }
            .table-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .search-box { width: 100%; }
            .search-box input { width: 100%; }
            .table-card { overflow-x: auto; }
            th, td { white-space: nowrap; }
            .pagination-row { flex-direction: column; gap: 16px; }
        }
    </style>
</head>
<body>

<header>
    <div class="nav-inner">
        <a href="/" class="nav-logo">
            <div class="nav-logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            Buku Tamu Digital
        </a>
        <a href="{{ route('login') }}" class="btn-login">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Login Admin
        </a>
    </div>
</header>

<main>
    <h1>Kunjungan Hari Ini</h1>
    <p class="page-subtitle">Pantau aktivitas tamu secara real-time. Sistem ini menggunakan teknologi AI untuk pencatatan yang efisien dan akurat.</p>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon green">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <span class="stat-label">Total Tamu</span>
            </div>
            <div class="stat-number">{{ $totalTamu }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon red">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </div>
                <span class="stat-label">Tamu Aktif</span>
            </div>
            <div class="stat-number">{{ $sedangBerkunjung }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon gray">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </div>
                <span class="stat-label">Tamu Selesai</span>
            </div>
            <div class="stat-number">{{ $totalHariIni - $sedangBerkunjung }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <span class="table-title">Log Tamu Terkini</span>
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Cari nama tamu..." id="searchInput">
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nama Tamu</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($kunjungans as $i => $k)
                @php
                    $nama = $k->tamu->nama ?? 'Unknown';
                    $initials = implode('', array_map(fn($w) => strtoupper(substr($w, 0, 1)), array_slice(explode(' ', $nama), 0, 2)));
                    $colors = ['a','b','c','d','e','f','g'];
                    $colorClass = 'av-' . $colors[ord(strtolower($initials[0] ?? 'a')) % 7];
                @endphp
                <tr data-name="{{ strtolower($nama) }}">
                    <td>
                        <div class="guest-cell">
                            @if($k->tamu && $k->tamu->foto)
                                <img src="{{ asset('storage/' . $k->tamu->foto) }}" class="avatar-img" alt="{{ $nama }}">
                            @else
                                <div class="avatar {{ $colorClass }}">{{ $initials }}</div>
                            @endif
                            <span class="guest-name">{{ $nama }}</span>
                        </div>
                    </td>
                    <td class="time-text">{{ \Carbon\Carbon::parse($k->jam_masuk)->format('H:i') }} WIB</td>
                    <td class="time-text">
                        @if($k->jam_keluar)
                            {{ \Carbon\Carbon::parse($k->jam_keluar)->format('H:i') }} WIB
                        @else
                            <span class="time-dash">—</span>
                        @endif
                    </td>
                    <td>
                        @if($k->status == 'sedang berkunjung')
                            <span class="status-badge status-active">● Menunggu</span>
                        @else
                            <span class="status-badge status-done">✓ Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <div class="empty-text">Belum ada kunjungan hari ini.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-row">
            <span>Menampilkan {{ $kunjungans->count() }} dari {{ $totalHariIni }} kunjungan hari ini</span>
        </div>
    </div>
</main>

<footer>
    © {{ date('Y') }} Buku Tamu Digital. Selalu Terorganisir. Semua hak cipta dilindungi.
</footer>

<script>
    // Client-side search filter
    document.getElementById('searchInput').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr[data-name]').forEach(row => {
            row.style.display = row.dataset.name.includes(query) ? '' : 'none';
        });
    });
</script>
</body>
</html>
