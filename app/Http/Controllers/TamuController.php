<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TamuController extends Controller
{
    /**
     * Buat HTTP client dengan X-API-Key ke Flask.
     * URL dan key dibaca dari config/services.php (sumber: .env Laravel).
     * TODO: Setelah info server production dikonfirmasi, pastikan .env sudah diupdate.
     */
    private function flaskClient()
    {
        return Http::timeout((int) config('services.flask.timeout', 60))
                   ->withHeaders(['X-API-Key' => config('services.flask.api_key')]);
    }

    private function flaskUrl(string $path): string
    {
        return rtrim(config('services.flask.base_url', 'http://127.0.0.1:5050'), '/') . $path;
    }
    public function register()
    {
        return view('tamu.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'image' => 'required|string',
        ]);

        $nama = $request->nama;
        $imageBase64 = $request->image;

        try {
            $response = $this->flaskClient()->post($this->flaskUrl('/api/face/register'), [
                'nama'  => $nama,
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    if (isset($data['is_duplicate']) && $data['is_duplicate']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Wajah ini sudah terdaftar sebagai: ' . $data['duplicate_name']
                        ]);
                    }

                    $image_parts = explode(";base64,", $imageBase64);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);

                    $fileName = 'tamu_' . time() . '.' . $image_type;
                    Storage::disk('public')->put('foto_tamu/' . $fileName, $image_base64);

                    $tamu = Tamu::create([
                        'nama'          => $nama,
                        'foto'          => 'foto_tamu/' . $fileName,
                        'face_encoding' => json_encode($data['face_encoding'])
                    ]);

                    // Auto-login: simpan sesi tamu setelah registrasi
                    session(['tamu_id' => $tamu->id, 'tamu_nama' => $tamu->nama]);

                    return response()->json([
                        'success'      => true,
                        'message'      => 'Pendaftaran berhasil! Selamat datang, ' . $nama . '!',
                        'tamu'         => $tamu,
                        'redirect_url' => route('tamu.profil')
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Gagal memproses wajah di sistem AI.'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke Python AI Server.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }


    // ===============================
    //  TAMU SELF-SERVICE: LOGIN & PROFIL
    // ===============================

    public function loginPage()
    {
        // Jika sudah login sebagai tamu, langsung ke profil
        if (session('tamu_id')) {
            return redirect()->route('tamu.profil');
        }
        return view('tamu.login');
    }

    public function loginProcess(Request $request)
    {
        $imageBase64 = $request->image;

        try {
            $response = $this->flaskClient()->post($this->flaskUrl('/api/face/recognize'), [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['recognized'] ?? false) {
                    $confidence = $data['confidence'];

                    if ($confidence < 70) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Wajah tidak dikenali (confidence terlalu rendah). Coba lagi.'
                        ]);
                    }

                    $tamu = Tamu::find($data['tamu_id']);
                    if (!$tamu) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Wajah dikenali tapi data tidak ditemukan. Silakan daftar ulang.'
                        ]);
                    }

                    // Set session tamu
                    session(['tamu_id' => $tamu->id, 'tamu_nama' => $tamu->nama]);

                    return response()->json([
                        'success'      => true,
                        'nama'         => $tamu->nama,
                        'confidence'   => $confidence,
                        'message'      => 'Login berhasil! Selamat datang kembali, ' . $tamu->nama . '!',
                        'redirect_url' => route('tamu.profil')
                    ]);
                }

                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Wajah tidak dikenali.']);
            }

            return response()->json(['success' => false, 'message' => 'Server AI tidak merespon.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function logoutTamu(Request $request)
    {
        session()->forget(['tamu_id', 'tamu_nama']);
        return redirect()->route('home');
    }

    public function profil()
    {
        $tamuId = session('tamu_id');
        if (!$tamuId) {
            return redirect()->route('tamu.login')->with('info', 'Silakan login terlebih dahulu.');
        }

        $tamu = Tamu::find($tamuId);
        if (!$tamu) {
            session()->forget(['tamu_id', 'tamu_nama']);
            return redirect()->route('tamu.login');
        }

        $kunjungans = \App\Models\Kunjungan::where('tamu_id', $tamuId)
            ->orderBy('jam_masuk', 'desc')
            ->take(15)
            ->get();

        $kunjunganAktif = $kunjungans->whereIn('status', ['sedang berkunjung', 'sedang_berkunjung'])->first();

        return view('tamu.profil', compact('tamu', 'kunjungans', 'kunjunganAktif'));
    }

    // ===============================
    //  CHECK-IN
    // ===============================
    public function checkin()
    {
        return view('tamu.checkin');
    }

    public function processCheckin(Request $request)
    {
        $imageBase64 = $request->image;

        try {
            // Endpoint: /api/face/recognize (response: recognized, tamu_id, nama, confidence)
            // USE_SIMULATION=true  → diproses lokal oleh Flask (face_recognition + MySQL lokal)
            // USE_SIMULATION=false → Flask forward ke remote server production
            $response = $this->flaskClient()->post($this->flaskUrl('/api/face/recognize'), [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['recognized'] ?? false) {
                    $confidence = $data['confidence'];

                    if ($confidence < 70) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'retry',
                            'message' => 'Wajah tidak dikenal (Confidence: ' . $confidence . '%)'
                        ]);
                    }

                    // tamu_id dikembalikan langsung oleh Flask / remote server
                    $tamu = Tamu::find($data['tamu_id']);

                    // Guard: tamu_id dari server tidak ada di database lokal
                    if (!$tamu) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'not_found',
                            'message' => 'Tamu dikenali oleh sistem AI tetapi data tidak ditemukan di database lokal. (tamu_id: ' . ($data['tamu_id'] ?? 'null') . ')'
                        ]);
                    }

                    // Cek apakah sudah check-in (kunjungan aktif)
                    $kunjunganAktif = \App\Models\Kunjungan::where('tamu_id', $tamu->id)
                                        ->whereIn('status', ['sedang berkunjung', 'sedang_berkunjung'])
                                        ->first();

                    if ($kunjunganAktif) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'already_checkin',
                            'message' => $tamu->nama . ' sudah check-in sebelumnya. Silakan gunakan halaman Check-Out.'
                        ]);
                    }

                    if ($confidence >= 85) {
                        return response()->json([
                            'success'    => true,
                            'action'     => 'auto',
                            'tamu_id'    => $tamu->id,
                            'nama'       => $tamu->nama,
                            'confidence' => $confidence,
                            'message'    => 'Check-in otomatis berhasil (' . $confidence . '%)'
                        ]);
                    } else {
                        return response()->json([
                            'success'    => true,
                            'action'     => 'confirm',
                            'tamu_id'    => $tamu->id,
                            'nama'       => $tamu->nama,
                            'confidence' => $confidence,
                            'message'    => 'Apakah ini ' . $tamu->nama . '? (' . $confidence . '%)'
                        ]);
                    }
                }

                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Wajah tidak dikenali.']);
            }
            return response()->json(['success' => false, 'message' => 'Server Python tidak merespon.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function confirmCheckin(Request $request)
    {
        $tamu_id    = $request->tamu_id;
        $confidence = $request->confidence;
        $keperluan  = $request->keperluan ?? '-';

        \App\Models\Kunjungan::create([
            'tamu_id'          => $tamu_id,
            'keperluan'        => $keperluan,
            'status'           => 'sedang berkunjung',
            'confidence_score' => $confidence
        ]);

        return response()->json(['success' => true, 'message' => 'Check-in berhasil disimpan.']);
    }

    // ===============================
    //  CHECK-OUT
    // ===============================
    public function checkout()
    {
        return view('tamu.checkout');
    }

    public function processCheckout(Request $request)
    {
        $imageBase64 = $request->image;

        try {
            // Endpoint: /api/face/recognize (response: recognized, tamu_id, nama, confidence)
            // USE_SIMULATION=true  → diproses lokal oleh Flask (face_recognition + MySQL lokal)
            // USE_SIMULATION=false → Flask forward ke remote server production
            $response = $this->flaskClient()->post($this->flaskUrl('/api/face/recognize'), [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['recognized'] ?? false) {
                    $confidence = $data['confidence'];

                    if ($confidence < 70) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'retry',
                            'message' => 'Wajah tidak dikenal (Confidence: ' . $confidence . '%)'
                        ]);
                    }

                    // tamu_id dikembalikan langsung oleh Flask / remote server
                    $tamu = Tamu::find($data['tamu_id']);

                    // Guard: tamu_id dari server tidak ada di database lokal
                    if (!$tamu) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'not_found',
                            'message' => 'Tamu dikenali oleh sistem AI tetapi data tidak ditemukan di database lokal. (tamu_id: ' . ($data['tamu_id'] ?? 'null') . ')'
                        ]);
                    }

                    // Cek apakah sedang berkunjung (boleh check-out)
                    $kunjunganAktif = \App\Models\Kunjungan::where('tamu_id', $tamu->id)
                                        ->whereIn('status', ['sedang berkunjung', 'sedang_berkunjung'])
                                        ->first();

                    if (!$kunjunganAktif) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'not_checkedin',
                            'message' => $tamu->nama . ' belum melakukan check-in hari ini.'
                        ]);
                    }

                    if ($confidence >= 85) {
                        return response()->json([
                            'success'    => true,
                            'action'     => 'auto',
                            'tamu_id'    => $tamu->id,
                            'nama'       => $tamu->nama,
                            'confidence' => $confidence,
                            'message'    => 'Check-out otomatis berhasil (' . $confidence . '%)'
                        ]);
                    } else {
                        return response()->json([
                            'success'    => true,
                            'action'     => 'confirm',
                            'tamu_id'    => $tamu->id,
                            'nama'       => $tamu->nama,
                            'confidence' => $confidence,
                            'message'    => 'Apakah ini ' . $tamu->nama . '? (' . $confidence . '%)'
                        ]);
                    }
                }

                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Wajah tidak dikenali.']);
            }
            return response()->json(['success' => false, 'message' => 'Server Python tidak merespon.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function confirmCheckout(Request $request)
    {
        $tamu_id    = $request->tamu_id;
        $confidence = $request->confidence;

        $kunjungan = \App\Models\Kunjungan::where('tamu_id', $tamu_id)
                        ->whereIn('status', ['sedang berkunjung', 'sedang_berkunjung'])
                        ->first();

        if ($kunjungan) {
            $kunjungan->update([
                'jam_keluar' => now(),
                'status'     => 'selesai'
            ]);
            return response()->json(['success' => true, 'message' => 'Check-out berhasil disimpan.']);
        }

        return response()->json(['success' => false, 'message' => 'Data kunjungan aktif tidak ditemukan.']);
    }

    // ===============================
    //  LEGACY (kept for backward compat)
    // ===============================
    public function scan()
    {
        return redirect()->route('tamu.checkin');
    }
}
