<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Wajah Real-time</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col">

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-gray-800 p-6 rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col items-center">
            <h1 class="text-3xl font-bold mb-2 text-blue-400">Scanner Kehadiran Tamu</h1>
            <p class="text-gray-400 mb-6">Arahkan wajah Anda ke kamera untuk Check-in / Check-out otomatis.</p>

            <div class="relative w-72 md:w-96 aspect-[4/3] bg-black rounded-xl overflow-hidden border-4 border-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                <video id="camera-stream" autoplay playsinline class="w-full h-full object-contain transform -scale-x-100"></video>
                <canvas id="canvas" class="hidden"></canvas>
                
                <!-- Overlay scanning animation -->
                <div id="scan-line" class="absolute top-0 left-0 w-full h-1 bg-green-400 shadow-[0_0_10px_#4ade80] opacity-75"></div>
            </div>

            <!-- Panel Status & Sapaan -->
            <div class="mt-6 w-full max-w-md bg-gray-700 rounded-xl p-5 text-center">
                <p id="status-text" class="text-lg font-semibold text-blue-300">Menunggu wajah...</p>
                
                <!-- Panel sapaan yang muncul ketika wajah terdeteksi -->
                <div id="greeting-panel" class="hidden mt-4 p-4 bg-gray-600 rounded-lg flex items-center gap-4">
                    <div class="text-left">
                        <p id="greeting-text" class="text-2xl font-bold text-white"></p>
                        <p id="action-text" class="text-sm text-gray-300 mt-1"></p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex gap-3">
                <button onclick="history.back()" class="px-5 py-2 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg transition flex items-center gap-2">
                    ← Kembali
                </button>
                <button id="toggle-scan-btn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition">
                    Jeda Scanner
                </button>
                <a href="{{ route('dashboard') }}" class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-lg transition flex items-center gap-2">
                    🏠 Dashboard
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes scan-animation {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        #scan-line {
            animation: scan-animation 3s linear infinite;
        }
    </style>

    <script>
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('canvas');
        const statusText = document.getElementById('status-text');
        const toggleScanBtn = document.getElementById('toggle-scan-btn');
        const scanLine = document.getElementById('scan-line');
        const greetingPanel = document.getElementById('greeting-panel');
        const greetingText = document.getElementById('greeting-text');
        const actionText = document.getElementById('action-text');

        function showGreeting(nama, actionType, confidence) {
            const emoji = actionType === 'checkin' ? '👋' : '👍';
            const actionLabel = actionType === 'checkin' 
                ? '✅ Check-in berhasil' 
                : '🔚 Check-out berhasil';
            
            greetingText.textContent = `${emoji} Halo, ${nama}!`;
            actionText.textContent = `${actionLabel} • Confidence: ${confidence}%`;
            greetingPanel.classList.remove('hidden');
        }

        function hideGreeting() {
            greetingPanel.classList.add('hidden');
        }

        let isScanning = true;
        let scanInterval;

        // Initialize Camera
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                video.srcObject = stream;
            } catch (err) {
                console.error("Error accessing camera:", err);
                Swal.fire('Error', 'Tidak dapat mengakses kamera.', 'error');
            }
        }

        startCamera();

        function captureFrame() {
            // Kita perkecil resolusi gambar ke max width 320px 
            // agar Python super ringan dan tidak timeout
            const maxWidth = 320;
            const scale = Math.min(1, maxWidth / video.videoWidth);
            canvas.width = video.videoWidth * scale;
            canvas.height = video.videoHeight * scale;
            
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.7); 
        }

        async function processScan() {
            if (!isScanning) return;
            
            // Jeda sementara selama request berjalan
            isScanning = false;
            statusText.textContent = "Memproses wajah...";
            statusText.className = "text-lg font-semibold text-yellow-400";
            
            const imageBase64 = captureFrame();

            try {
                const response = await fetch('{{ route("tamu.scan-process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image: imageBase64 })
                });

                const result = await response.json();

                if (result.success) {
                    if (result.action === 'auto') {
                        // >= 85% - Auto check-in/out, tampilkan sapaan
                        statusText.textContent = result.message;
                        statusText.className = "text-lg font-semibold text-green-400";
                        showGreeting(result.nama, result.action_type, result.confidence);
                        await confirmAction(result.tamu_id, result.action_type, result.confidence, result.nama);
                    } else if (result.action === 'confirm') {
                        // 70 - 85% Confidence
                        scanLine.style.display = 'none';
                        const confirmResult = await Swal.fire({
                            title: 'Konfirmasi Petugas',
                            text: result.message,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Bukan'
                        });

                        if (confirmResult.isConfirmed) {
                            showGreeting(result.nama, result.action_type, result.confidence);
                            await confirmAction(result.tamu_id, result.action_type, result.confidence, result.nama);
                        } else {
                            statusText.textContent = "Dibatalkan petugas. Silakan scan ulang.";
                            statusText.className = "text-lg font-semibold text-red-400";
                            resumeScanning();
                        }
                    }
                } else {
                    // < 70% atau error lainnya
                    if (result.action === 'retry') {
                        statusText.textContent = result.message;
                        statusText.className = "text-lg font-semibold text-red-400";
                    } else {
                        statusText.textContent = result.message || "Menunggu wajah...";
                        statusText.className = "text-lg font-semibold text-blue-300";
                    }
                    setTimeout(resumeScanning, 2000);
                }
            } catch (error) {
                console.error("Error:", error);
                statusText.textContent = "Terjadi kesalahan sistem.";
                statusText.className = "text-lg font-semibold text-red-500";
                setTimeout(resumeScanning, 3000);
            }
        }

        async function confirmAction(tamu_id, action_type, confidence, nama) {
            try {
                const response = await fetch('{{ route("tamu.confirm") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ tamu_id, action_type, confidence })
                });

                const result = await response.json();

                Swal.fire({
                    icon: 'success',
                    title: `Halo, ${nama}!`,
                    text: result.message,
                    timer: 2500,
                    showConfirmButton: false
                });

                statusText.textContent = `Halo, ${nama}! ${result.message}`;
                statusText.className = "text-lg font-semibold text-green-400";

                // Hilangkan sapaan dan lanjut scan setelah 4 detik
                setTimeout(() => {
                    hideGreeting();
                    resumeScanning();
                }, 4000);
            } catch (error) {
                Swal.fire('Error', 'Gagal menyimpan data.', 'error');
                hideGreeting();
                resumeScanning();
            }
        }

        function resumeScanning() {
            scanLine.style.display = 'block';
            statusText.textContent = "Menunggu wajah...";
            statusText.className = "text-lg font-semibold text-blue-300";
            isScanning = true;
        }

        // Jalankan scan otomatis tiap 2 detik
        scanInterval = setInterval(() => {
            if (isScanning && video.readyState === 4) {
                processScan();
            }
        }, 2000);

        toggleScanBtn.addEventListener('click', () => {
            if (isScanning) {
                isScanning = false;
                scanLine.style.display = 'none';
                toggleScanBtn.textContent = 'Mulai Scanner';
                toggleScanBtn.className = 'px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition';
                statusText.textContent = "Scanner dijeda.";
            } else {
                resumeScanning();
                toggleScanBtn.textContent = 'Jeda Scanner';
                toggleScanBtn.className = 'px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition';
            }
        });

    </script>
</body>
</html>
