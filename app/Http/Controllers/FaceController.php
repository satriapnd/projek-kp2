<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * FaceController — Proxy Laravel ke Flask Face Recognition Engine
 *
 * Controller ini bertindak sebagai perantara antara frontend Blade/JS
 * dan Python Flask API, sehingga:
 * - API key Flask tidak pernah terekspos ke browser
 * - URL Flask bisa diganti cukup di .env tanpa menyentuh kode
 * - Frontend selalu berkomunikasi melalui Laravel (bukan langsung ke Flask)
 *
 * Konfigurasi bersumber dari config/services.php yang membaca .env:
 *   FLASK_BASE_URL, FLASK_API_KEY, FLASK_TIMEOUT
 */
class FaceController extends Controller
{
    /**
     * Ambil konfigurasi Flask dari config/services.php.
     * TODO: Setelah info server production dikonfirmasi, pastikan nilai-nilai
     *       ini sudah diupdate di .env production (bukan development).
     */
    private function flaskConfig(): array
    {
        return [
            'base_url' => config('services.flask.base_url', 'http://127.0.0.1:5050'),
            'api_key'  => config('services.flask.api_key'),
            'timeout'  => (int) config('services.flask.timeout', 60),
        ];
    }

    /**
     * Buat HTTP client dengan header X-API-Key ke Flask.
     */
    private function flaskClient()
    {
        $cfg = $this->flaskConfig();
        return Http::timeout($cfg['timeout'])
                   ->withHeaders(['X-API-Key' => $cfg['api_key']]);
    }

    // =========================================================================
    // POST /face/recognize
    // Terima gambar dari frontend (file upload ATAU base64 JSON),
    // teruskan ke Flask /api/face/recognize, kembalikan result ke browser.
    // =========================================================================
    public function recognize(Request $request)
    {
        $request->validate([
            // Validasi: harus ada salah satu (file ATAU base64 string)
            'image' => 'required',
        ]);

        $cfg = $this->flaskConfig();

        try {
            // ---- Format 1: File upload (multipart/form-data) ----
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $response = $this->flaskClient()
                    ->attach('image', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                    ->post($cfg['base_url'] . '/api/face/recognize');

            // ---- Format 2: Base64 JSON ----
            } else {
                $response = $this->flaskClient()
                    ->post($cfg['base_url'] . '/api/face/recognize', [
                        'image' => $request->input('image'),
                    ]);
            }

            if ($response->successful()) {
                // Kembalikan response Flask langsung ke browser
                return response()->json($response->json());
            }

            return response()->json([
                'recognized' => false,
                'nama'       => null,
                'confidence' => null,
                'message'    => 'Flask engine merespon dengan error: ' . $response->status(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'recognized' => false,
                'nama'       => null,
                'confidence' => null,
                'message'    => 'Tidak dapat terhubung ke Flask engine: ' . $e->getMessage(),
            ], 503);
        }
    }

    // =========================================================================
    // POST /face/register
    // Terima gambar + nama dari frontend, teruskan ke Flask /api/face/register.
    // =========================================================================
    public function register(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:255',
            'image' => 'required',
        ]);

        $cfg  = $this->flaskConfig();
        $nama = $request->input('nama');

        try {
            // ---- Format 1: File upload ----
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $response = $this->flaskClient()
                    ->attach('image', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                    ->post($cfg['base_url'] . '/api/face/register', ['nama' => $nama]);

            // ---- Format 2: Base64 JSON ----
            } else {
                $response = $this->flaskClient()
                    ->post($cfg['base_url'] . '/api/face/register', [
                        'nama'  => $nama,
                        'image' => $request->input('image'),
                    ]);
            }

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Flask engine merespon dengan error: ' . $response->status(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke Flask engine: ' . $e->getMessage(),
            ], 503);
        }
    }

    // =========================================================================
    // GET /face/demo
    // Tampilkan halaman demo sederhana untuk test recognize & register.
    // =========================================================================
    public function demo()
    {
        return view('face.demo');
    }
}
