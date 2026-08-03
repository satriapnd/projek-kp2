<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Tamu — {{ $tamu->nama }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; color: #0f172a; min-height: 100vh; }

        /* NAV */
        header { background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 50; }
        .nav-inner { max-width: 1000px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; height: 60px; }
        .nav-logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1rem; color: #0f172a; text-decoration: none; }
        .nav-logo-icon { width: 32px; height: 32px; background: #ecfdf5; border: 1.5px solid #6ee7b7; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .nav-user { display: flex; align-items: center; gap: 10px; }
        .nav-avatar { width: 34px; height: 34px; border-radius: 50%; background: #0f172a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 700; overflow: hidden; }
        .nav-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .nav-name { font-weight: 600; font-size: 0.875rem; color: #0f172a; }
        .btn-logout { display: flex; align-items: center; gap: 6px; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 6px 14px; font-size: 0.78rem; font-weight: 600; color: #dc2626; background: #fff; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.15s; }
        .btn-logout:hover { background: #fef2f2; }

        /* MAIN */
        main { max-width: 1000px; margin: 0 auto; padding: 40px 24px 80px; }

        /* PROFILE CARD */
        .profile-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
            padding: 32px; display: flex; align-items: center; gap: 28px;
            margin-bottom: 24px; box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        }
        @media (max-width: 600px) { .profile-card { flex-direction: column; align-items: flex-start; } }
        .profile-photo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; flex-shrink: 0; }
        .profile-initials { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #0f172a, #334155); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 800; flex-shrink: 0; }
        .profile-info { flex: 1; }
        .profile-name { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .profile-id { font-size: 0.78rem; color: #94a3b8; margin-bottom: 12px; }
        .profile-badges { display: flex; flex-wrap: wrap; gap: 8px; }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }
        .badge-yellow { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-gray  { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* STATUS CARD */
        .status-card {
            border-radius: 16px; padding: 20px 24px; margin-bottom: 24px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
        }
        .status-card.active { background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #f59e0b; }
        .status-card.idle   { background: #f8fafc; border: 1px solid #e2e8f0; }
        .status-left { display: flex; align-items: center; gap: 14px; }
        .status-icon-box { font-size: 2rem; }
        .status-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #92400e; margin-bottom: 2px; }
        .status-label.idle { color: #64748b; }
        .status-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .status-time  { font-size: 0.78rem; color: #64748b; margin-top: 2px; }

        /* TABLE CARD */
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        .table-hdr { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
        .table-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .table-count { font-size: 0.78rem; color: #94a3b8; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 3px 10px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 20px; font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #f1f5f9; }
        td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 0.85rem; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }

        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.73rem; font-weight: 600; }
        .status-active { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-done   { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }

        .time-val { font-variant-numeric: tabular-nums; color: #374151; }
        .time-dash { color: #cbd5e1; }

        .empty-state { text-align: center; padding: 48px; color: #94a3b8; }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .empty-state p { font-size: 0.875rem; }

        @media (max-width: 640px) {
            main { padding: 24px 16px 60px; }
            .profile-name { font-size: 1.25rem; }
            .status-card { flex-direction: column; align-items: flex-start; }
            .table-card { overflow-x: auto; }
            th, td { white-space: nowrap; }
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
        <div class="nav-user">
            <div class="nav-avatar">
                @if($tamu->foto)
                    <img src="{{ asset('storage/' . $tamu->foto) }}" alt="{{ $tamu->nama }}">
                @else
                    {{ strtoupper(substr($tamu->nama, 0, 1)) }}
                @endif
            </div>
            <span class="nav-name">{{ $tamu->nama }}</span>
            <form method="POST" action="{{ route('tamu.logout.tamu') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</header>

<main>

    {{-- Profile Card --}}
    <div class="profile-card">
        @if($tamu->foto)
            <img src="{{ asset('storage/' . $tamu->foto) }}" class="profile-photo" alt="{{ $tamu->nama }}">
        @else
            <div class="profile-initials">{{ strtoupper(substr($tamu->nama, 0, 1)) }}</div>
        @endif
        <div class="profile-info">
            <div class="profile-name">{{ $tamu->nama }}</div>
            <div class="profile-id">ID Tamu #{{ $tamu->id }} · Bergabung {{ \Carbon\Carbon::parse($tamu->created_at)->translatedFormat('d F Y') }}</div>
            <div class="profile-badges">
                @if($kunjunganAktif)
                    <span class="badge badge-yellow">
                        <span class="badge-dot"></span> Sedang Berkunjung
                    </span>
                @else
                    <span class="badge badge-gray">Tidak Sedang Berkunjung</span>
                @endif
                <span class="badge badge-green">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    Tamu Terdaftar
                </span>
                <span class="badge badge-gray">{{ $kunjungans->count() }} kunjungan</span>
            </div>
        </div>
    </div>

    {{-- Status saat ini --}}
    @if($kunjunganAktif)
        <div class="status-card active">
            <div class="status-left">
                <div class="status-icon-box">🏢</div>
                <div>
                    <div class="status-label">Status Kunjungan Aktif</div>
                    <div class="status-title">Sedang Berkunjung</div>
                    <div class="status-time">Check-in sejak {{ \Carbon\Carbon::parse($kunjunganAktif->jam_masuk)->format('H:i') }} WIB</div>
                </div>
            </div>
        </div>
    @else
        <div class="status-card idle">
            <div class="status-left">
                <div class="status-icon-box">🏠</div>
                <div>
                    <div class="status-label idle">Status</div>
                    <div class="status-title">Tidak Sedang Berkunjung</div>
                    <div class="status-time">Datang ke kantor dan scan wajah di scanner Check-In</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Riwayat Kunjungan --}}
    <div class="table-card">
        <div class="table-hdr">
            <span class="table-title">Riwayat Kunjungan Saya</span>
            <span class="table-count">{{ $kunjungans->count() }} catatan</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kunjungans as $k)
                <tr>
                    <td style="color:#374151;">{{ \Carbon\Carbon::parse($k->jam_masuk)->translatedFormat('d M Y') }}</td>
                    <td class="time-val">{{ \Carbon\Carbon::parse($k->jam_masuk)->format('H:i') }} WIB</td>
                    <td>
                        @if($k->jam_keluar)
                            <span class="time-val">{{ \Carbon\Carbon::parse($k->jam_keluar)->format('H:i') }} WIB</span>
                        @else
                            <span class="time-dash">—</span>
                        @endif
                    </td>
                    <td style="color:#64748b;">{{ $k->keperluan ?: '—' }}</td>
                    <td>
                        @if(in_array(strtolower(trim($k->status ?? '')), ['sedang berkunjung', 'sedang_berkunjung']))
                            <span class="status-badge status-active">● Aktif</span>
                        @else
                            <span class="status-badge status-done">✓ Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="icon">📋</div>
                            <p>Belum ada riwayat kunjungan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
