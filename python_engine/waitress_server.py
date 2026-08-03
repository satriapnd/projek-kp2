"""
Waitress WSGI Server — Production runner untuk Flask di Windows
Menggantikan 'python app.py' (Flask dev server) dengan server yang proper.

Cara pakai:
    python waitress_server.py

Waitress cocok untuk Windows karena Gunicorn tidak support Windows.
"""

import os
from dotenv import load_dotenv

load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), '.env'))

FLASK_HOST = os.getenv('FLASK_HOST', '127.0.0.1')
FLASK_PORT = int(os.getenv('FLASK_PORT', 5050))
THREADS    = int(os.getenv('FLASK_THREADS', 4))

if __name__ == '__main__':
    try:
        from waitress import serve
    except ImportError:
        print("[ERROR] Waitress belum terinstall.")
        print("        Jalankan: pip install waitress")
        exit(1)

    from app import app

    mode = 'SIMULASI (lokal)' if os.getenv('USE_SIMULATION', 'true').lower() == 'true' else 'PRODUCTION'

    print("=" * 60)
    print("  Flask Face API — Waitress WSGI Server")
    print("=" * 60)
    print(f"  Host    : {FLASK_HOST}")
    print(f"  Port    : {FLASK_PORT}")
    print(f"  Threads : {THREADS}")
    print(f"  Mode    : {mode}")
    print(f"  URL     : http://{FLASK_HOST}:{FLASK_PORT}")
    print("=" * 60)
    print("  Tekan Ctrl+C untuk menghentikan server.")
    print("=" * 60)

    serve(
        app,
        host=FLASK_HOST,
        port=FLASK_PORT,
        threads=THREADS,
        channel_timeout=60,
    )
