"""
================================================================================
SIMULATION CONFIG — Remote API Server (Web Services / REST API)
================================================================================
File ini HANYA untuk simulasi selama akses ke server production belum diberikan.
Tujuannya: mendefinisikan semua variabel, endpoint, dan helper yang NANTINYA
akan diganti dengan konfigurasi nyata dari pihak instansi/pembimbing.

Cara pakai:
  - Jalankan: python simulation_config.py
    → Akan menjalankan simulasi penuh dan menampilkan hasil di terminal.
  - Import di app.py:
    from simulation_config import REMOTE_SERVER_CONFIG, build_headers, call_remote_api

TODO (setelah akses server production diterima):
  1. Isi REMOTE_API_KEY dengan key asli server.
  2. Ganti REMOTE_BASE_URL ke URL production server.
  3. Ganti REMOTE_DB_* dengan kredensial server production.
  4. Set USE_SIMULATION = False agar app.py pakai endpoint asli.
  5. Hapus semua fungsi simulate_* yang tidak lagi diperlukan.
================================================================================
"""

import os
import json
import time
import random
import hashlib
import secrets
from datetime import datetime
from dotenv import load_dotenv

# Baca dari .env lokal (jika ada override)
load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), '.env'))

# ==============================================================================
# FLAG UTAMA
# Set ke False jika sudah pakai server production real
# ==============================================================================
USE_SIMULATION: bool = os.getenv('USE_SIMULATION', 'true').lower() == 'true'


# ==============================================================================
# KONFIGURASI REMOTE SERVER (PLACEHOLDER)
# Semua nilai di bawah ini adalah DUMMY — akan diisi setelah akses diberikan
# ==============================================================================
REMOTE_SERVER_CONFIG = {
    # --- Identitas Aplikasi (diberikan oleh instansi saat registrasi app) ---
    "APP_ID":        os.getenv("REMOTE_APP_ID",      "SIM-APP-ID-XXXX"),
    "APP_SECRET":    os.getenv("REMOTE_APP_SECRET",  "SIM-APP-SECRET-XXXX"),

    # --- API Key utama (diberikan oleh instansi) ---
    "API_KEY":       os.getenv("REMOTE_API_KEY",     "SIM-APIKEY-ganti-nanti"),

    # --- Base URL server production ---
    # Contoh format umum:
    #   https://api.[nama-server].go.id/v1
    #   https://services.[instansi].go.id/api
    "BASE_URL":      os.getenv("REMOTE_BASE_URL",    "https://api.server-simulasi.go.id/v1"),

    # --- Timeout request (detik) ---
    "TIMEOUT":       int(os.getenv("REMOTE_TIMEOUT", "30")),

    # --- Versi API ---
    "API_VERSION":   os.getenv("REMOTE_API_VERSION", "v1"),

    # --- Environment ("sandbox" | "production") ---
    "ENVIRONMENT":   os.getenv("REMOTE_ENV",         "sandbox"),
}


# ==============================================================================
# KONFIGURASI DATABASE REMOTE SERVER (PLACEHOLDER)
# Dipakai Flask jika nantinya face encoding disimpan di DB server production,
# bukan di DB lokal. Isi setelah kredensial production diberikan.
# ==============================================================================
REMOTE_DB_CONFIG = {
    "HOST":     os.getenv("REMOTE_DB_HOST",     "db.server-simulasi.go.id"),
    "PORT":     int(os.getenv("REMOTE_DB_PORT", "3306")),
    "DATABASE": os.getenv("REMOTE_DB_NAME",     "db_buku_tamu_prod"),
    "USERNAME": os.getenv("REMOTE_DB_USER",     "prod_user"),
    "PASSWORD": os.getenv("REMOTE_DB_PASS",     "SIM-DB-PASS-ganti-nanti"),
}


# ==============================================================================
# ENDPOINT MAP
# Daftar semua endpoint yang akan dipanggil ke server production.
# Setelah dokumentasi API dari instansi diterima, sesuaikan path-path di bawah.
# ==============================================================================
REMOTE_ENDPOINTS = {
    # Health check — cek apakah server production hidup
    "health":           "/status",

    # Autentikasi — mendapatkan token sesi (jika server pakai OAuth/JWT)
    "auth_token":       "/auth/token",

    # Registrasi wajah ke server production
    "face_register":    "/face/register",

    # Pengenalan wajah via server production
    "face_recognize":   "/face/recognize",

    # Upload foto ke server production (jika dipisah dari register)
    "photo_upload":     "/media/upload",

    # Sinkronisasi data tamu
    "tamu_sync":        "/tamu/sync",

    # Log kunjungan ke server
    "visit_log":        "/visit/log",
}


# ==============================================================================
# HELPER: Bangun URL lengkap dari nama endpoint
# ==============================================================================
def build_url(endpoint_name: str) -> str:
    """
    Gabungkan BASE_URL + path endpoint.

    Contoh:
        build_url("face_recognize")
        → "https://api.server-simulasi.go.id/v1/face/recognize"
    """
    base = REMOTE_SERVER_CONFIG["BASE_URL"].rstrip("/")
    path = REMOTE_ENDPOINTS.get(endpoint_name, "")
    return f"{base}{path}"


# ==============================================================================
# HELPER: Bangun headers standar untuk request ke server production
# ==============================================================================
def build_headers(extra: dict | None = None) -> dict:
    """
    Kembalikan dict headers yang wajib disertakan ke setiap request server.

    Parameter:
        extra (dict): Header tambahan opsional (mis. {'Content-Type': 'multipart/form-data'})

    Return:
        dict berisi header standar + extra (jika ada).

    Contoh:
        headers = build_headers({"Content-Type": "application/json"})
    """
    headers = {
        # API Key utama
        "X-API-Key":      REMOTE_SERVER_CONFIG["API_KEY"],

        # Identitas aplikasi (sering dibutuhkan untuk multi-tenant)
        "X-App-ID":       REMOTE_SERVER_CONFIG["APP_ID"],

        # Timestamp request (untuk signature / replay-attack prevention)
        "X-Timestamp":    datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ"),

        # Request ID unik (memudahkan tracking di server)
        "X-Request-ID":   secrets.token_hex(8),

        # Versi API yang dipakai
        "X-API-Version":  REMOTE_SERVER_CONFIG["API_VERSION"],

        # Mode environment
        "X-Environment":  REMOTE_SERVER_CONFIG["ENVIRONMENT"],

        # Standard content type (bisa di-override via extra)
        "Accept":         "application/json",
    }

    if extra:
        headers.update(extra)

    return headers


# ==============================================================================
# HELPER: Generate HMAC Signature (jika server pakai signed request)
# Beberapa server instansi mewajibkan setiap request di-sign menggunakan
# APP_SECRET untuk mencegah pemalsuan request.
# ==============================================================================
def generate_signature(payload: dict, timestamp: str) -> str:
    """
    Buat tanda tangan HMAC-SHA256 untuk request ke server production.

    Formula (umum dipakai server instansi pemerintah):
        sign = HMAC-SHA256(APP_SECRET, "APP_ID:TIMESTAMP:SHA256(payload)")

    Parameter:
        payload   (dict): Body JSON yang akan dikirim.
        timestamp (str) : Timestamp dalam format ISO-8601.

    Return:
        str: Hex string dari signature.

    TODO: Sesuaikan formula ini dengan dokumentasi resmi API yang diterima.
    """
    import hmac

    secret     = REMOTE_SERVER_CONFIG["APP_SECRET"].encode()
    app_id     = REMOTE_SERVER_CONFIG["APP_ID"]
    body_str   = json.dumps(payload, separators=(",", ":"), sort_keys=True)
    body_hash  = hashlib.sha256(body_str.encode()).hexdigest()
    message    = f"{app_id}:{timestamp}:{body_hash}".encode()

    return hmac.new(secret, message, hashlib.sha256).hexdigest()


# ==============================================================================
# FUNGSI SIMULASI
# Menirukan respons yang SEHARUSNYA datang dari server production.
# Dipakai saat USE_SIMULATION = True (belum ada akses server asli).
# ==============================================================================

def simulate_response(endpoint_name: str, payload: dict | None = None) -> dict:
    """
    Simulasikan respons dari server production berdasarkan nama endpoint.

    Parameter:
        endpoint_name (str) : Nama endpoint (key dari REMOTE_ENDPOINTS).
        payload       (dict): Body yang dikirimkan ke endpoint (opsional).

    Return:
        dict: Respons simulasi yang formatnya sama dengan respons asli.
    """
    payload  = payload or {}
    delay_ms = random.randint(80, 300)   # Simulasi network latency

    print(f"  [SIM] Memanggil endpoint '{endpoint_name}' (latency simulasi: {delay_ms}ms)")
    time.sleep(delay_ms / 1000)

    if endpoint_name == "health":
        return {
            "status":      "ok",
            "server":      "Face API Server [SIMULASI]",
            "environment": REMOTE_SERVER_CONFIG["ENVIRONMENT"],
            "version":     REMOTE_SERVER_CONFIG["API_VERSION"],
            "timestamp":   datetime.utcnow().isoformat() + "Z",
        }

    elif endpoint_name == "auth_token":
        # Simulasi OAuth2 token response
        return {
            "access_token":  f"sim-token-{secrets.token_hex(16)}",
            "token_type":    "Bearer",
            "expires_in":    3600,
            "scope":         "face:register face:recognize",
        }

    elif endpoint_name == "face_register":
        nama  = payload.get("nama", "Tamu Simulasi")
        # Simulasi ada kemungkinan duplikat (15%)
        is_dup = random.random() < 0.15
        if is_dup:
            return {
                "success":        True,
                "is_duplicate":   True,
                "duplicate_name": "Budi Santoso [SIM]",
                "message":        "Wajah sudah terdaftar [SIMULASI]",
            }
        return {
            "success":        True,
            "is_duplicate":   False,
            "tamu_id":        random.randint(100, 9999),
            "nama":           nama,
            "message":        "Wajah berhasil didaftarkan [SIMULASI]",
            # face_encoding dikembalikan agar Laravel bisa simpan ke DB lokal
            "face_encoding":  [round(random.uniform(-0.2, 0.2), 6) for _ in range(128)],
        }

    elif endpoint_name == "face_recognize":
        # Simulasi 75% dikenali, 25% tidak dikenali
        recognized = random.random() < 0.75
        if recognized:
            return {
                "recognized": True,
                "tamu_id":    random.randint(1, 50),
                "nama":       random.choice(["Andi Wijaya", "Siti Rahayu", "Budi Santoso"]) + " [SIM]",
                "confidence": round(random.uniform(72.0, 98.5), 2),
            }
        return {
            "recognized": False,
            "tamu_id":    None,
            "nama":       None,
            "confidence": round(random.uniform(20.0, 65.0), 2),
            "message":    "Wajah tidak dikenali (confidence di bawah threshold) [SIMULASI]",
        }

    elif endpoint_name == "photo_upload":
        return {
            "success":  True,
            "file_id":  f"SIM-FILE-{secrets.token_hex(6).upper()}",
            "url":      f"https://cdn.server-simulasi.go.id/photos/{secrets.token_hex(8)}.jpg",
            "size_kb":  random.randint(80, 400),
        }

    elif endpoint_name == "tamu_sync":
        return {
            "success":       True,
            "synced_count":  random.randint(1, 10),
            "message":       "Sinkronisasi data tamu berhasil [SIMULASI]",
        }

    elif endpoint_name == "visit_log":
        return {
            "success":   True,
            "log_id":    random.randint(1000, 9999),
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "message":   "Log kunjungan berhasil dicatat [SIMULASI]",
        }

    else:
        return {
            "success": False,
            "message": f"Endpoint '{endpoint_name}' tidak dikenali dalam simulasi.",
        }


# ==============================================================================
# FUNGSI UTAMA: Panggil endpoint server (asli ATAU simulasi)
# Inilah fungsi yang akan dipakai oleh app.py nantinya.
# ==============================================================================
def call_remote_api(
    endpoint_name: str,
    payload: dict | None = None,
    method: str = "POST",
    extra_headers: dict | None = None,
) -> dict:
    """
    Antarmuka tunggal untuk memanggil server production.
    Secara otomatis memilih mode simulasi atau asli berdasarkan USE_SIMULATION.

    Parameter:
        endpoint_name  (str) : Nama endpoint (key dari REMOTE_ENDPOINTS).
        payload        (dict): Body JSON yang dikirim (None jika GET).
        method         (str) : HTTP method ("GET" | "POST" | "PUT").
        extra_headers  (dict): Header tambahan (opsional).

    Return:
        dict: Respons JSON dari server (asli atau simulasi).

    Contoh:
        result = call_remote_api("face_recognize", {"image": "<base64>"})
        if result.get("recognized"):
            print("Wajah dikenali:", result["nama"])
    """
    if USE_SIMULATION:
        print(f"\n[SIMULATION MODE] Panggil '{endpoint_name}' — server asli BELUM tersedia.")
        return simulate_response(endpoint_name, payload)

    # --- Mode ASLI (pakai server production) ---
    try:
        import requests

        url       = build_url(endpoint_name)
        timestamp = datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ")
        headers   = build_headers(extra_headers)

        # Tambahkan signature jika diperlukan (aktifkan setelah konfirmasi)
        # signature              = generate_signature(payload or {}, timestamp)
        # headers["X-Signature"] = signature

        response = requests.request(
            method  = method.upper(),
            url     = url,
            headers = headers,
            json    = payload if method.upper() != "GET" else None,
            params  = payload if method.upper() == "GET"  else None,
            timeout = REMOTE_SERVER_CONFIG["TIMEOUT"],
        )
        response.raise_for_status()
        return response.json()

    except Exception as exc:
        return {
            "success": False,
            "message": f"Gagal menghubungi server production: {exc}",
        }


# ==============================================================================
# ENTRY POINT — Jalankan demo simulasi
# python simulation_config.py
# ==============================================================================
if __name__ == "__main__":
    print("=" * 70)
    print("  SIMULASI INTEGRASI SERVER PRODUCTION (REST API / Web Services)")
    print("  File ini adalah PLACEHOLDER — bukan koneksi asli ke server production.")
    print("=" * 70)

    print(f"\n[CONFIG] USE_SIMULATION   : {USE_SIMULATION}")
    print(f"[CONFIG] ENVIRONMENT      : {REMOTE_SERVER_CONFIG['ENVIRONMENT']}")
    print(f"[CONFIG] BASE_URL         : {REMOTE_SERVER_CONFIG['BASE_URL']}")
    print(f"[CONFIG] API_KEY          : {REMOTE_SERVER_CONFIG['API_KEY'][:20]}...")
    print(f"[CONFIG] APP_ID           : {REMOTE_SERVER_CONFIG['APP_ID']}")

    print("\n" + "-" * 70)
    print("  DEMO: Membangun headers standar")
    print("-" * 70)
    headers = build_headers({"Content-Type": "application/json"})
    for k, v in headers.items():
        print(f"  {k:<20}: {v}")

    print("\n" + "-" * 70)
    print("  DEMO: Simulasi setiap endpoint")
    print("-" * 70)

    endpoints_to_test = [
        ("health",          None),
        ("auth_token",      {"grant_type": "client_credentials"}),
        ("face_register",   {"nama": "Andi Wijaya", "image": "<base64_placeholder>"}),
        ("face_recognize",  {"image": "<base64_placeholder>"}),
        ("photo_upload",    {"filename": "foto_tamu.jpg"}),
        ("tamu_sync",       {"tamu_ids": [1, 2, 3]}),
        ("visit_log",       {"tamu_id": 5, "action": "checkin"}),
    ]

    for ep_name, ep_payload in endpoints_to_test:
        print(f"\n>>> Endpoint: {ep_name}")
        print(f"    URL      : {build_url(ep_name)}")
        result = call_remote_api(ep_name, ep_payload)
        print(f"    Respons  : {json.dumps(result, ensure_ascii=False, indent=4)}")

    print("\n[DONE] Simulasi selesai. Set USE_SIMULATION=false di .env setelah")
    print("       akses server production diterima.\n")

