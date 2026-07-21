import os
import json
import numpy as np
import cv2
import face_recognition
import mysql.connector
from flask import Flask, request, jsonify
from flask_cors import CORS
from dotenv import load_dotenv

# Load database config from Laravel's .env file
load_dotenv(dotenv_path='../.env')

app = Flask(__name__)
CORS(app) # Allow cross-origin requests from Laravel frontend

def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', '127.0.0.1'),
        user=os.getenv('DB_USERNAME', 'root'),
        password=os.getenv('DB_PASSWORD', ''),
        database=os.getenv('DB_DATABASE', 'db_buku_tamu'),
        port=os.getenv('DB_PORT', '3306')
    )

@app.route('/api/status', methods=['GET'])
def status():
    return jsonify({"status": "Python Face Recognition API is running!"})

import base64

def base64_to_image(base64_string):
    # Hapus header base64 jika ada (e.g. data:image/jpeg;base64,)
    if "," in base64_string:
        base64_string = base64_string.split(',')[1]
    
    img_data = base64.b64decode(base64_string)
    nparr = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)  # Force 3-channel BGR
    
    if img is None:
        raise ValueError("Gagal mendekode gambar dari Base64.")
    
    # Pastikan gambar adalah 8-bit 3-channel (RGB)
    if len(img.shape) == 2:  # Grayscale
        img = cv2.cvtColor(img, cv2.COLOR_GRAY2BGR)
    elif img.shape[2] == 4:  # BGRA/RGBA
        img = cv2.cvtColor(img, cv2.COLOR_BGRA2BGR)
    
    return img

def img_to_rgb(img):
    """
    Konversi gambar OpenCV (BGR) ke array RGB uint8 yang kompatibel dengan dlib/face_recognition.
    Numpy 2.x membutuhkan array yang contiguous dan dtype uint8 eksplisit.
    """
    rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    rgb = np.ascontiguousarray(rgb, dtype=np.uint8)
    return rgb

@app.route('/api/register', methods=['POST'])
def register():
    print("--- MULAI REGISTRASI ---")
    data = request.json
    if 'image' not in data:
        return jsonify({'status': 'error', 'message': 'Tidak ada gambar yang dikirim'})
    
    try:
        print("1. Mengubah Base64 ke Gambar...")
        img = base64_to_image(data['image'])
        # Konversi ke RGB uint8 yang kompatibel dengan dlib/face_recognition
        rgb_img = img_to_rgb(img)
        print(f"   -> Shape: {rgb_img.shape}, dtype: {rgb_img.dtype}")
        
        print("2. Mendeteksi letak wajah...")
        # Deteksi wajah
        face_locations = face_recognition.face_locations(rgb_img)
        print(f"   -> Ditemukan {len(face_locations)} wajah.")
        if len(face_locations) == 0:
            return jsonify({'status': 'error', 'message': 'Wajah tidak terdeteksi. Harap foto ulang.'})
        elif len(face_locations) > 1:
            return jsonify({'status': 'error', 'message': 'Terdeteksi lebih dari satu wajah. Pastikan hanya ada satu wajah dalam frame.'})
            
        print("3. Membuat Encoding Wajah (AI Processing)...")
        # Ambil face encoding wajah tersebut
        face_encodings = face_recognition.face_encodings(rgb_img, face_locations)
        new_encoding = face_encodings[0]
        print("   -> Encoding berhasil dibuat.")
        
        print("4. Menyambungkan ke MySQL...")
        # Cek duplikasi di database
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
        rows = cursor.fetchall()
        
        for row in rows:
            try:
                db_encoding = np.array(json.loads(row['face_encoding']))
                # Bandingkan wajah (tolerance standar: 0.6)
                matches = face_recognition.compare_faces([db_encoding], new_encoding, tolerance=0.5)
                if matches[0]:
                    cursor.close()
                    conn.close()
                    return jsonify({
                        'status': 'success', 
                        'is_duplicate': True, 
                        'duplicate_name': row['nama']
                    })
            except Exception as e:
                print(f"Error parsing encoding for {row['nama']}: {e}")
                
        cursor.close()
        conn.close()
        
        # Jika lolos cek, kembalikan encoding sebagai list
        return jsonify({
            'status': 'success',
            'is_duplicate': False,
            'face_encoding': new_encoding.tolist()
        })
        
    except Exception as e:
        print(f"Error di backend: {e}")
        return jsonify({'status': 'error', 'message': str(e)})

@app.route('/api/scan', methods=['POST'])
def scan():
    data = request.json
    if 'image' not in data:
        return jsonify({'status': 'error', 'message': 'Tidak ada gambar'})

    try:
        img = base64_to_image(data['image'])
        rgb_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

        face_locations = face_recognition.face_locations(rgb_img)
        if len(face_locations) == 0:
            return jsonify({'status': 'error', 'message': 'Tidak ada wajah'})
        
        # Ambil wajah terbesar (jika ada lebih dari 1, kita asumsikan yang paling dekat dengan kamera)
        # Atau cukup ambil index 0 jika sistem scan per orang
        face_encodings = face_recognition.face_encodings(rgb_img, face_locations)
        unknown_encoding = face_encodings[0]

        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama, face_encoding FROM tamu WHERE face_encoding IS NOT NULL")
        rows = cursor.fetchall()
        cursor.close()
        conn.close()

        if not rows:
            return jsonify({'status': 'error', 'message': 'Belum ada data tamu terdaftar'})

        best_match_id = None
        best_match_name = None
        best_confidence = 0.0

        for row in rows:
            try:
                db_encoding = np.array(json.loads(row['face_encoding']))
                # Hitung jarak wajah (semakin kecil semakin mirip)
                face_distance = face_recognition.face_distance([db_encoding], unknown_encoding)[0]
                
                # Konversi distance ke persentase confidence (pendekatan sederhana)
                # Umumnya distance 0.0 = 100%, distance 0.6 = ~40%
                confidence = max(0.0, 100.0 - (face_distance * 100.0))
                # Penyesuaian agar nilai lebih representatif untuk requirement > 85%
                # Kita gunakan multiplier agar distance 0.3 menjadi ~85%
                adjusted_confidence = min(100.0, max(0.0, (1.0 - (face_distance / 0.5)) * 100.0))
                # Jika distance 0.0 => 100%. distance 0.25 => 50%. Ini terlalu kecil.
                # Kita ubah rumusnya:
                
                # Rumus mapping: distance 0.0=100, 0.4=85, 0.5=70, 0.6=50
                if face_distance <= 0.4:
                    conf = 100 - ((face_distance / 0.4) * 15) # 85-100%
                elif face_distance <= 0.5:
                    conf = 85 - (((face_distance - 0.4) / 0.1) * 15) # 70-85%
                else:
                    conf = 70 - (((face_distance - 0.5) / 0.5) * 70) # <70%

                if conf > best_confidence:
                    best_confidence = conf
                    best_match_id = row['id']
                    best_match_name = row['nama']
            except Exception as e:
                continue

        return jsonify({
            'status': 'success',
            'tamu_id': best_match_id,
            'nama': best_match_name,
            'confidence': round(best_confidence, 2)
        })

    except Exception as e:
        print(f"Error di backend scan: {e}")
        return jsonify({'status': 'error', 'message': str(e)})

if __name__ == '__main__':
    print("Engine AI Python Berjalan pada http://127.0.0.1:5050")
    app.run(host='127.0.0.1', port=5050, debug=True, threaded=False)
