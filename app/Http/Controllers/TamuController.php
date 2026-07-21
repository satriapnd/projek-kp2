<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TamuController extends Controller
{
    public function register()
    {
        return view('tamu.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'image' => 'required|string', // Base64 image
        ]);

        $nama = $request->nama;
        $imageBase64 = $request->image;

        // Kirim gambar ke Python API untuk diproses
        try {
            $response = Http::timeout(60)->post('http://127.0.0.1:5050/api/register', [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] == 'success') {
                    // Cek jika wajah sudah pernah didaftarkan
                    // Python akan mengembalikan "is_duplicate"
                    if (isset($data['is_duplicate']) && $data['is_duplicate']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Wajah ini sudah terdaftar sebagai: ' . $data['duplicate_name']
                        ]);
                    }

                    // Simpan gambar ke storage public Laravel
                    $image_parts = explode(";base64,", $imageBase64);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    
                    $fileName = 'tamu_' . time() . '.' . $image_type;
                    Storage::disk('public')->put('foto_tamu/' . $fileName, $image_base64);

                    // Simpan ke database
                    $tamu = Tamu::create([
                        'nama' => $nama,
                        'foto' => 'foto_tamu/' . $fileName,
                        'face_encoding' => json_encode($data['face_encoding'])
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Tamu berhasil didaftarkan!',
                        'tamu' => $tamu
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
            $response = Http::timeout(45)->post('http://127.0.0.1:5050/api/scan', [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] == 'success') {
                    $confidence = $data['confidence'];

                    if ($confidence < 70) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'retry',
                            'message' => 'Wajah tidak dikenal (Confidence: ' . $confidence . '%)'
                        ]);
                    }

                    $tamu = Tamu::find($data['tamu_id']);

                    // Cek apakah sudah check-in (kunjungan aktif)
                    $kunjunganAktif = \App\Models\Kunjungan::where('tamu_id', $tamu->id)
                                        ->where('status', 'sedang berkunjung')
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

                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Gagal diproses']);
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
            $response = Http::timeout(45)->post('http://127.0.0.1:5050/api/scan', [
                'image' => $imageBase64
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] == 'success') {
                    $confidence = $data['confidence'];

                    if ($confidence < 70) {
                        return response()->json([
                            'success' => false,
                            'action'  => 'retry',
                            'message' => 'Wajah tidak dikenal (Confidence: ' . $confidence . '%)'
                        ]);
                    }

                    $tamu = Tamu::find($data['tamu_id']);

                    // Cek apakah sedang berkunjung (boleh check-out)
                    $kunjunganAktif = \App\Models\Kunjungan::where('tamu_id', $tamu->id)
                                        ->where('status', 'sedang berkunjung')
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

                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Gagal diproses']);
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
                        ->where('status', 'sedang berkunjung')
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
