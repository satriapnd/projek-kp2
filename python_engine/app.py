import os
import json
import base64
import numpy as np
import cv2
import face_recognition
from flask import Flask, request, jsonify
from flask_cors import CORS
from dotenv import load_dotenv

# =============================================================================
# IMPORT SIMULASI / REMOTE SERVER
# USE_SIMULATION=true  → proses lokal (face_recognition + MySQL lokal)
# USE_SIMULATION=false → forward ke server production via call_remote_api()
# =============================================================================
from simulation_config import USE_SIMULATION, call_remote_api, REMOTE_SERVER_CONFIG

# =============================================================================
# KONFIGURASI ENVIRONMENT
# =============================================================================
load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), '.env'))

FLASK_API_KEY = os.getenv('FLASK_API_KEY', 'dev-secret-key-ganti-nanti')
FLASK_PORT    = int(os.getenv('FLASK_PORT', 5050))
FLASK_HOST    = os.getenv('FLASK_HOST', '127.0.0.1')

ENCODINGS_FILE = os.getenv(
    'ENCODINGS_FILE',
    os.path.join(os.path.dirname(__file__), 'face_encodings.json')
)

app = Flask(__name__)
CORS(app)


# =============================================================================
# API KEY MIDDLEWARE
# =============================================================================
@app.before_request
def check_api_key():
    """Validasi API key sebelum setiap request ke endpoint /api/."""
    if request.path == '/api/status':
        return None
    if request.path.startswith('/api/'):
        incoming_key = request.headers.get('X-API-Key', '')
        if incoming_key != FLASK_API_KEY:
            return jsonify({
                'success': False,
                'message': 'Unauthorized: API key tidak valid atau tidak disertakan.'
            }), 401
    return None


# =============================================================================
# HELPER FUNCTIONS
# =============================================================================

def get_db_connection():
    """Koneksi ke MySQL lokal."""
    import mysql.connector
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', '127.0.0.1'),
        user=os.getenv('DB_USERNAME', 'root'),
        password=os.getenv('DB_PASSWORD', ''),
        database=os.getenv('DB_DATABASE', 'db_buku_tamu'),
        port=int(os.getenv('DB_PORT', 3306))
    )


def decode_image_from_request() -> np.ndarray:
    """
    Decode gambar dari request menjadi array NumPy BGR.
    Mendukung: multipart/form-data (file) ATAU application/json (base64).
    """
    img = None
    if 'image' in request.files:
        file_bytes = np.frombuffer(request.files['image'].read(), np.uint8)
        img = cv2.imdecode(file_bytes, cv2.IMREAD_COLOR)
    elif request.is_json and request.json and 'image' in request.json:
        b64 = request.json['image']
        if ',' in b64:
            b64 = b64.split(',', 1)[1]
        arr = np.frombuffer(base64.b64decode(b64), np.uint8)
        img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    else:
        raise ValueError("Tidak ada field 'image' dalam request (file upload atau JSON base64).")
    if img is None:
        raise ValueError("Gagal mendekode gambar. Pastikan format file valid (JPEG/PNG).")
    if len(img.shape) == 2:
        img = cv2.cvtColor(img, cv2.COLOR_GRAY2BGR)
    elif img.shape[2] == 4:
        img = cv2.cvtColor(img, cv2.COLOR_BGRA2BGR)
    return img


def extract_image_base64() -> str:
    """
    Ambil gambar dari request dan kembalikan sebagai string base64 murni.
    Dipakai saat USE_SIMULATION=false (forward ke remote server).
    """
    if 'image' in request.files:
        return base64.b64encode(request.files['image'].read()).decode('utf-8')
    if request.is_json and request.json and 'image' in request.json:
        b64 = request.json['image']
        if ',' in b64:
            b64 = b64.split(',', 1)[1]
        return b64
    raise ValueError("Tidak ada field 'image' dalam request.")


def bgr_to_rgb(img: np.ndarray) -> np.ndarray:
    """Konversi BGR (OpenCV) → RGB uint8 contiguous array."""
    return np.ascontiguousarray(cv2.cvtColor(img, cv2.COLOR_BGR2RGB), dtype=np.uint8)


def get_nama_from_request() -> str:
    """Ambil field 'nama' dari multipart form data atau JSON body."""
    if request.is_json and request.json:
        return request.json.get('nama', '').strip()
    return request.form.get('nama', '').strip()


# =============================================================================
# LOGIKA LOKAL (USE_SIMULATION=true)
# Memproses face recognition menggunakan library lokal + MySQL lokal.
# Mengembalikan format JSON yang SAMA dengan yang akan dikembalikan remote server,
# sehingga saat switch ke USE_SIMULATION=false tidak perlu ubah kode apapun.
# =============================================================================

def _local_face_register() -> dict:
    """
    Registrasi wajah secara lokal — dipakai saat USE_SIMULATION=true.
    Proses: decode gambar → deteksi wajah → cek duplikat di MySQL → return encoding.
    Format respons sama persis dengan yang diharapkan dari remote server.
    """
    nama = get_nama_from_request()
    if not nama:
        return {'success': False, 'message': "Field 'nama' wajib diisi."}, 400

    img     = decode_image_from_request()
    rgb_img = bgr_to_rgb(img)

    face_locations = face_recognition.face_locations(rgb_img)
    if len(face_locations) == 0:
        return {'success': False, 'message': 'Wajah tidak terdeteksi. Harap foto ulang.'}, 200
    if len(face_locations) > 1:
        return {'success': False, 'message': 'Terdeteksi lebih dari satu wajah. Pastikan hanya ada satu wajah dalam frame.'}, 200

    face_encodings_list = face_recognition.face_encodings(rgb_img, face_locations)
    new_encoding        = face_encodings_list[0]

    # Cek duplikat di MySQL lokal
    conn   = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
    rows   = cursor.fetchall()
    cursor.close()
    conn.close()

    for row in rows:
        try:
            db_enc  = np.array(json.loads(row['face_encoding']))
            matches = face_recognition.compare_faces([db_enc], new_encoding, tolerance=0.5)
            if matches[0]:
                return {
                    'success':        True,
                    'is_duplicate':   True,
                    'duplicate_name': row['nama'],
                    'message':        f"Wajah sudah terdaftar sebagai: {row['nama']}"
                }, 200
        except Exception:
            continue

    return {
        'success':      True,
        'is_duplicate': False,
        'face_encoding': new_encoding.tolist()
    }, 200


def _local_face_recognize() -> dict:
    """
    Pengenalan wajah secara lokal — dipakai saat USE_SIMULATION=true.
    Proses: decode gambar → deteksi wajah → bandingkan dengan MySQL → return hasil.
    Format respons sama persis dengan yang diharapkan dari remote server.
    """
    img     = decode_image_from_request()
    rgb_img = bgr_to_rgb(img)

    face_locations = face_recognition.face_locations(rgb_img)
    if len(face_locations) == 0:
        return {
            'recognized': False, 'tamu_id': None, 'nama': None, 'confidence': None,
            'message': 'Tidak ada wajah yang terdeteksi.'
        }, 200

    face_encodings_list = face_recognition.face_encodings(rgb_img, face_locations)
    unknown_enc         = face_encodings_list[0]

    conn   = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
    rows   = cursor.fetchall()
    cursor.close()
    conn.close()

    if not rows:
        return {
            'recognized': False, 'tamu_id': None, 'nama': None, 'confidence': None,
            'message': 'Belum ada data wajah terdaftar di database.'
        }, 200

    best_id         = None
    best_name       = None
    best_confidence = 0.0

    for row in rows:
        try:
            db_enc        = np.array(json.loads(row['face_encoding']))
            face_distance = face_recognition.face_distance([db_enc], unknown_enc)[0]

            if face_distance <= 0.4:
                conf = 100 - (face_distance / 0.4) * 15
            elif face_distance <= 0.5:
                conf = 85 - ((face_distance - 0.4) / 0.1) * 15
            else:
                conf = max(0.0, 70 - ((face_distance - 0.5) / 0.5) * 70)

            if conf > best_confidence:
                best_confidence = conf
                best_id         = row['id']
                best_name       = row['nama']
        except Exception:
            continue

    THRESHOLD = 70.0
    if best_confidence >= THRESHOLD:
        return {
            'recognized': True,
            'tamu_id':    best_id,
            'nama':       best_name,
            'confidence': round(best_confidence, 2)
        }, 200
    else:
        return {
            'recognized': False,
            'tamu_id':    None,
            'nama':       None,
            'confidence': round(best_confidence, 2),
            'message':    'Wajah tidak dikenali (confidence di bawah threshold).'
        }, 200


# =============================================================================
# ENDPOINTS UTAMA — OPSI A
#
# Alur saat USE_SIMULATION=true (development — akses server belum diberikan):
#   Browser → Flask → _local_face_*() → face_recognition lokal + MySQL lokal
#
# Alur saat USE_SIMULATION=false (production — setelah akses server diterima):
#   Browser → Flask → call_remote_api() → server production → Flask → Browser
#
# Format JSON respons IDENTIK di kedua mode, sehingga tidak perlu ubah
# kode Laravel/frontend saat beralih dari simulasi ke production.
# =============================================================================

@app.route('/api/status', methods=['GET'])
def status():
    """Health-check publik — tidak butuh API key."""
    mode = 'SIMULASI (lokal)' if USE_SIMULATION else 'PRODUCTION (remote server)'
    return jsonify({
        'status':          'ok',
        'message':         f'Flask Face API berjalan — mode: {mode}.',
        'simulation_mode': USE_SIMULATION,
        'remote_server':   REMOTE_SERVER_CONFIG['BASE_URL'],
        'environment':     REMOTE_SERVER_CONFIG['ENVIRONMENT'],
        'version':         '3.0.0'
    })


@app.route('/api/face/register', methods=['POST'])
def face_register():
    """
    Endpoint registrasi wajah.

    USE_SIMULATION=true  → proses lokal (face_recognition + MySQL lokal)
    USE_SIMULATION=false → forward ke remote server via call_remote_api()

    Input (pilih salah satu):
        multipart/form-data : field 'image' (file) + field 'nama' (string)
        application/json    : { "image": "<base64>", "nama": "<string>" }

    Header wajib:
        X-API-Key: <FLASK_API_KEY>

    Response JSON:
        Sukses baru : { "success": true,  "is_duplicate": false, "face_encoding": [...] }
        Duplikat    : { "success": true,  "is_duplicate": true,  "duplicate_name": str }
        Gagal       : { "success": false, "message": str }
    """
    try:
        if USE_SIMULATION:
            # Proses lokal — simulasi server production menggunakan data nyata
            result, status_code = _local_face_register()
            return jsonify(result), status_code
        else:
            # Forward ke remote server production
            nama      = get_nama_from_request()
            image_b64 = extract_image_base64()
            app.logger.info(f"[REMOTE] face_register → '{REMOTE_SERVER_CONFIG['BASE_URL']}/face/register' | nama='{nama}' | payload={len(image_b64)} chars")
            result = call_remote_api('face_register', {'nama': nama, 'image': image_b64})
            app.logger.info(f"[REMOTE] face_register ← success={result.get('success')} is_duplicate={result.get('is_duplicate')}")
            return jsonify(result)

    except ValueError as ve:
        return jsonify({'success': False, 'message': str(ve)}), 400
    except Exception as e:
        app.logger.error(f"[face_register] Error: {e}")
        return jsonify({'success': False, 'message': 'Internal server error.'}), 500


@app.route('/api/face/recognize', methods=['POST'])
def face_recognize():
    """
    Endpoint pengenalan wajah.

    USE_SIMULATION=true  → proses lokal (face_recognition + MySQL lokal)
    USE_SIMULATION=false → forward ke remote server via call_remote_api()

    Input (pilih salah satu):
        multipart/form-data : field 'image' (file upload)
        application/json    : { "image": "<base64 string>" }

    Header wajib:
        X-API-Key: <FLASK_API_KEY>

    Response JSON:
        Dikenali : { "recognized": true,  "tamu_id": int,  "nama": str, "confidence": float }
        Tidak    : { "recognized": false, "tamu_id": null, "nama": null, "confidence": float }
    """
    try:
        if USE_SIMULATION:
            # Proses lokal — simulasi server production menggunakan data nyata
            result, status_code = _local_face_recognize()
            return jsonify(result), status_code
        else:
            # Forward ke remote server production
            image_b64 = extract_image_base64()
            app.logger.info(f"[REMOTE] face_recognize → '{REMOTE_SERVER_CONFIG['BASE_URL']}/face/recognize' | payload={len(image_b64)} chars")
            result = call_remote_api('face_recognize', {'image': image_b64})
            app.logger.info(f"[REMOTE] face_recognize ← recognized={result.get('recognized')} tamu_id={result.get('tamu_id')} confidence={result.get('confidence')}")
            return jsonify(result)

    except ValueError as ve:
        return jsonify({
            'recognized': False, 'tamu_id': None,
            'nama': None, 'confidence': None, 'message': str(ve)
        }), 400
    except Exception as e:
        app.logger.error(f"[face_recognize] Error: {e}")
        return jsonify({
            'recognized': False, 'tamu_id': None,
            'nama': None, 'confidence': None, 'message': 'Internal server error.'
        }), 500


# =============================================================================
# LEGACY ENDPOINTS — DEPRECATED
# Dipertahankan agar klien lama tidak langsung rusak.
# TODO: Migrasi semua klien ke /api/face/register dan /api/face/recognize.
# =============================================================================

@app.route('/api/register', methods=['POST'])
def register_legacy():
    """[DEPRECATED] Gunakan /api/face/register."""
    data = request.json or {}
    if 'image' not in data:
        return jsonify({'status': 'error', 'message': 'Tidak ada gambar yang dikirim'})
    try:
        img     = decode_image_from_request()
        rgb_img = bgr_to_rgb(img)

        face_locations = face_recognition.face_locations(rgb_img)
        if len(face_locations) == 0:
            return jsonify({'status': 'error', 'message': 'Wajah tidak terdeteksi. Harap foto ulang.'})
        if len(face_locations) > 1:
            return jsonify({'status': 'error', 'message': 'Terdeteksi lebih dari satu wajah.'})

        face_encodings = face_recognition.face_encodings(rgb_img, face_locations)
        new_encoding   = face_encodings[0]

        conn   = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
        rows   = cursor.fetchall()

        for row in rows:
            try:
                db_enc  = np.array(json.loads(row['face_encoding']))
                matches = face_recognition.compare_faces([db_enc], new_encoding, tolerance=0.5)
                if matches[0]:
                    cursor.close(); conn.close()
                    return jsonify({'status': 'success', 'is_duplicate': True, 'duplicate_name': row['nama']})
            except Exception:
                continue

        cursor.close(); conn.close()
        return jsonify({'status': 'success', 'is_duplicate': False, 'face_encoding': new_encoding.tolist()})

    except Exception as e:
        app.logger.error(f"[register_legacy] Error: {e}")
        return jsonify({'status': 'error', 'message': str(e)})


@app.route('/api/scan', methods=['POST'])
def scan_legacy():
    """[DEPRECATED] Gunakan /api/face/recognize."""
    data = request.json or {}
    if 'image' not in data:
        return jsonify({'status': 'error', 'message': 'Tidak ada gambar'})
    try:
        img     = decode_image_from_request()
        rgb_img = bgr_to_rgb(img)

        face_locations = face_recognition.face_locations(rgb_img)
        if len(face_locations) == 0:
            return jsonify({'status': 'error', 'message': 'Tidak ada wajah'})

        face_encodings_list = face_recognition.face_encodings(rgb_img, face_locations)
        unknown_enc         = face_encodings_list[0]

        conn   = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
        rows   = cursor.fetchall()
        cursor.close(); conn.close()

        if not rows:
            return jsonify({'status': 'error', 'message': 'Belum ada data tamu terdaftar'})

        best_id   = None
        best_name = None
        best_conf = 0.0

        for row in rows:
            try:
                db_enc        = np.array(json.loads(row['face_encoding']))
                face_distance = face_recognition.face_distance([db_enc], unknown_enc)[0]

                if face_distance <= 0.4:
                    conf = 100 - (face_distance / 0.4) * 15
                elif face_distance <= 0.5:
                    conf = 85 - ((face_distance - 0.4) / 0.1) * 15
                else:
                    conf = max(0.0, 70 - ((face_distance - 0.5) / 0.5) * 70)

                if conf > best_conf:
                    best_conf = conf
                    best_id   = row['id']
                    best_name = row['nama']
            except Exception:
                continue

        return jsonify({
            'status':     'success',
            'tamu_id':    best_id,
            'nama':       best_name,
            'confidence': round(best_conf, 2)
        })

    except Exception as e:
        app.logger.error(f"[scan_legacy] Error: {e}")
        return jsonify({'status': 'error', 'message': str(e)})


# =============================================================================
# ENTRY POINT
# =============================================================================
if __name__ == '__main__':
    mode = 'SIMULASI (lokal)' if USE_SIMULATION else 'PRODUCTION (remote server)'
    print(f"[Flask] API berjalan di http://{FLASK_HOST}:{FLASK_PORT}")
    print(f"[Flask] Mode          : {mode}")
    print(f"[Flask] Remote server : {REMOTE_SERVER_CONFIG['BASE_URL']}")
    if USE_SIMULATION:
        print("[Flask] ⚠  USE_SIMULATION=true — set ke false setelah akses server diterima.")
    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=True, threaded=False)
