<?php
require 'config.php';

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Jika belum login, redirect ke halaman utama
    header('Location: index.html');
    exit;
}

$nama = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pengguna');
$role = htmlspecialchars($_SESSION['role'] ?? 'pegawai');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Absensi KPU Provinsi Banten</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; padding: 24px; max-width: 1200px; margin: 0 auto; }
        .button { display: inline-block; padding: 8px 16px; background: #e74c3c; color: #fff; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; font-weight: 500; }
        .button.green { background: #27ae60; }
        .button.blue { background: #3498db; }
        .riwayat-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .riwayat-table th { 
            background: #f5f6fa; 
            padding: 15px; 
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e8ecef;
        }
        .riwayat-table td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .riwayat-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .foto-absensi {
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
            border: 2px solid #e8ecef;
        }
        .foto-absensi:hover {
            transform: scale(1.1);
            border-color: #3498db;
        }
        .status-hadir { 
            color: #27ae60; 
            font-weight: bold;
            background-color: #d4edda;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .status-terlambat { 
            color: #e74c3c;
            font-weight: bold;
            background-color: #f8d7da;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .status-belum-pulang { 
            color: #f39c12; 
            font-weight: bold;
            background-color: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .status-tidak-hadir { 
            color: #e74c3c; 
            font-weight: bold;
            background-color: #f8d7da;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        /* Style untuk form laporan */
        #laporan-form {
            display: none;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .textarea-laporan {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        
        .foto-absensi {
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .foto-absensi:hover {
            transform: scale(1.1);
        }
        .badge-hadir {
            background-color: #27ae60;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }
        .close {
            color: #fff;
            position: absolute;
            right: 35px;
            top: 15px;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .absensi-form { display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        #camera-preview { width: 100%; max-width: 400px; margin: 10px 0; border-radius: 4px; }
        #captured-photo { display: none; max-width: 400px; margin: 10px 0; border-radius: 4px; }
        .location-info { background: #e8f4fc; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .status { padding: 12px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .header { margin-bottom: 30px; }
        .form-title { color: #2c3e50; margin-bottom: 20px; }
        .info-text { color: #666; font-size: 0.9em; }
        .admin-panel { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f6fa; }
        .top-right-logout { position: absolute; right: 16px; top: 16px; }
        .actions-inline { display: flex; gap: 12px; align-items: center; margin-bottom: 18px; }
        .dashboard-section { margin-bottom: 30px; }
        .section-title { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px; }
    </style>
</head>
<body>
    <div class="top-right-logout">
        <a class="button" href="logout.php">Keluar</a>
    </div>

    <h1>Halo, <?php echo $nama; ?>!</h1>
    <p>Anda login sebagai: <strong><?php echo $role; ?></strong></p>

    <div id="status-message"></div>

    <?php if ($role === 'admin'): ?>
    <!-- Admin Dashboard -->
    <div class="dashboard-section">
        <h2 class="section-title">Panel Admin</h2>
        <div class="actions-inline">
            <button class="button blue" onclick="showDaftarPegawai()">
                Daftar Pegawai
            </button>
            <button class="button blue" onclick="showRekapAbsensi()">
                Rekap Absensi
            </button>
        </div>

        <div class="admin-panel" id="daftar-pegawai" style="display: none;">
            <h3>Daftar Pegawai</h3>
            <div id="pegawai-table">
                <!-- Data pegawai akan dimuat di sini -->
            </div>
        </div>

        <div class="admin-panel" id="rekap-absensi" style="display: none;">
            <h3>Rekap Absensi</h3>
            <div class="actions-inline">
                <input type="date" id="tanggal-filter">
                <button class="button" onclick="filterAbsensi()">Filter</button>
            </div>
            <div id="absensi-table">
                <!-- Data absensi akan dimuat di sini -->
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'pegawai'): ?>
    <!-- Pegawai Dashboard -->
    <div class="dashboard-section">
        <h2 class="section-title">Absensi Hari Ini</h2>
        <div class="header actions-inline">
            <button class="button green" onclick="showAbsensiForm('masuk')">
                Absen Masuk
            </button>
            <button class="button" onclick="showAbsensiForm('keluar')">
                Absen Pulang
            </button>
            <button class="button blue" onclick="showLaporanForm()">
                Input Laporan Kerja
            </button>
        </div>

        <!-- Form Laporan Kerja -->
        <div id="laporan-form">
            <h2 class="form-title">Form Input Laporan Kerja</h2>
            <div>
                <p class="info-text">Silakan masukkan laporan kerja Anda hari ini</p>
                <textarea id="laporan-kerja" class="textarea-laporan" placeholder="Tuliskan laporan kerja Anda di sini..."></textarea>
            </div>
            <button class="button blue" onclick="submitLaporan()">
                Kirim Laporan
            </button>
        </div>

        <div id="absensi-form" class="absensi-form">
            <h2 class="form-title">Form Pencatatan Kehadiran</h2>
            <input type="hidden" id="absensi-type" value="masuk">
            
            <div>
                <h3>Foto Kehadiran</h3>
                <p class="info-text">Pastikan wajah terlihat jelas dan pencahayaan cukup</p>
                <video id="camera-preview" autoplay playsinline></video>
                <img id="captured-photo" alt="Foto Kehadiran">
                <br>
                <button class="button" onclick="capturePhoto()">
                    <i class="fas fa-camera"></i> Ambil Foto
                </button>
                <button class="button" onclick="retakePhoto()" style="display:none" id="retake-button">
                    <i class="fas fa-redo"></i> Ambil Ulang Foto
                </button>
            </div>

            <div class="location-info">
                <h3>Lokasi Anda</h3>
                <p class="info-text">Sistem akan mencatat lokasi Anda saat ini untuk verifikasi kehadiran</p>
                <p id="location-status">Sedang mendapatkan lokasi...</p>
                <input type="hidden" id="latitude">
                <input type="hidden" id="longitude">
            </div>

            <button class="button green" onclick="submitAbsensi()" id="submit-button">
                Kirim Data
            </button>
        </div>

        <!-- Riwayat Absensi Pegawai -->
        <div class="dashboard-section">
            <h2 class="section-title">Riwayat Absensi</h2>
            <div id="riwayat-absensi">
                <!-- Riwayat absensi pegawai akan dimuat di sini -->
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        let stream = null;
        let photoData = null;

        // Fungsi untuk menampilkan form laporan
        function showLaporanForm() {
            document.getElementById('laporan-form').style.display = 'block';
            document.getElementById('absensi-form').style.display = 'none';
        }

        // Fungsi untuk mengirim laporan
        async function submitLaporan() {
            const laporan = document.getElementById('laporan-kerja').value.trim();
            
            if (!laporan) {
                showStatus('Mohon isi laporan kerja Anda', 'error');
                return;
            }

            try {
                showStatus('Menyimpan laporan...', 'info');
                const response = await fetch('process_laporan_new.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        laporan: laporan
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    showStatus('Laporan kerja berhasil disimpan!', 'success');
                    document.getElementById('laporan-form').style.display = 'none';
                    document.getElementById('laporan-kerja').value = '';
                } else {
                    showStatus('Gagal menyimpan laporan: ' + result.message, 'error');
                }
            } catch (error) {
                showStatus('Terjadi kesalahan saat menyimpan laporan', 'error');
            }
        }

        // Fungsi-fungsi Admin
        async function showDaftarPegawai() {
            document.getElementById('daftar-pegawai').style.display = 'block';
            document.getElementById('rekap-absensi').style.display = 'none';
            
            try {
                const response = await fetch('get_pegawai.php');
                const data = await response.json();
                
                let html = '<table><tr><th>Nama</th><th>Email</th><th>Status</th></tr>';
                data.forEach(pegawai => {
                    html += `<tr>
                        <td>${pegawai.nama_lengkap}</td>
                        <td>${pegawai.email}</td>
                        <td>${pegawai.status}</td>
                    </tr>`;
                });
                html += '</table>';
                
                document.getElementById('pegawai-table').innerHTML = html;
            } catch (error) {
                showStatus('Gagal memuat daftar pegawai', 'error');
            }
        }

        async function showRekapAbsensi() {
            document.getElementById('daftar-pegawai').style.display = 'none';
            document.getElementById('rekap-absensi').style.display = 'block';
            await filterAbsensi();
        }

        async function filterAbsensi() {
            const tanggal = document.getElementById('tanggal-filter').value;
            try {
                const response = await fetch(`get_absensi.php?tanggal=${tanggal}`);
                const data = await response.json();
                
                let html = '<table><tr><th>Nama</th><th>Tanggal</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th></tr>';
                data.forEach(absen => {
                    html += `<tr>
                        <td>${absen.nama_lengkap}</td>
                        <td>${absen.tanggal}</td>
                        <td>${absen.jam_masuk || '-'}</td>
                        <td>${absen.jam_pulang || '-'}</td>
                        <td>${absen.status}</td>
                    </tr>`;
                });
                html += '</table>';
                
                document.getElementById('absensi-table').innerHTML = html;
            } catch (error) {
                showStatus('Gagal memuat data absensi', 'error');
            }
        }

        // Fungsi-fungsi Pegawai

        function showAbsensiForm(type = 'masuk') {
            document.getElementById('absensi-form').style.display = 'block';
            document.getElementById('absensi-type').value = type;
            const title = document.querySelector('.form-title');
            const submitBtn = document.getElementById('submit-button');
            if (type === 'masuk') {
                title.textContent = 'Form Absen Masuk';
                submitBtn.textContent = 'Kirim Absen Masuk';
            } else {
                title.textContent = 'Form Absen Pulang';
                submitBtn.textContent = 'Kirim Absen Pulang';
            }
            initCamera();
            getLocation();
        }

        async function initCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user' }, 
                    audio: false 
                });
                document.getElementById('camera-preview').srcObject = stream;
            } catch (err) {
                showStatus('Gagal mengakses kamera. Mohon izinkan akses kamera pada browser Anda', 'error');
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-preview');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            photoData = canvas.toDataURL('image/jpeg');
            
            document.getElementById('captured-photo').src = photoData;
            document.getElementById('captured-photo').style.display = 'block';
            document.getElementById('camera-preview').style.display = 'none';
            document.getElementById('retake-button').style.display = 'inline-block';
            
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        }

        function retakePhoto() {
            document.getElementById('captured-photo').style.display = 'none';
            document.getElementById('camera-preview').style.display = 'block';
            document.getElementById('retake-button').style.display = 'none';
            photoData = null;
            initCamera();
        }

        function getLocation() {
            if (!navigator.geolocation) {
                document.getElementById('location-status').textContent = 'Geolocation tidak didukung di browser ini';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('location-status').textContent = 
                        `Lokasi ditemukan: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}`;
                },
                (error) => {
                    document.getElementById('location-status').textContent = 'Error: Tidak dapat mendapatkan lokasi';
                }
            );
        }

        function showStatus(message, type) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.className = `status ${type}`;
            statusDiv.textContent = message;
        }

        async function submitAbsensi() {
            if (!photoData) {
                showStatus('Mohon ambil foto terlebih dahulu sebelum mengirim data kehadiran', 'error');
                return;
            }

            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                showStatus('Mohon tunggu hingga sistem mendapatkan lokasi Anda', 'error');
                return;
            }

            try {
                showStatus('Sedang mengirim data kehadiran...', 'info');
                
                const response = await fetch('process_absensi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        photo: photoData,
                        latitude: lat,
                        longitude: lng,
                        type: document.getElementById('absensi-type').value
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    showStatus('Data kehadiran berhasil dicatat! Terima kasih.', 'success');
                    document.getElementById('absensi-form').style.display = 'none';
                    // Reload riwayat absensi setelah berhasil submit
                    loadRiwayatAbsensi();
                } else {
                    // Cek dulu apakah sudah absen hari ini
                    try {
                        const checkResponse = await fetch('check_kehadiran_hari_ini.php');
                        const checkResult = await checkResponse.json();
                        
                        if (checkResult.success && checkResult.present) {
                            showStatus('Anda sudah absen masuk hari ini', 'success');
                            document.getElementById('absensi-form').style.display = 'none';
                            loadRiwayatAbsensi();
                            return;
                        }
                    } catch (e) {
                        console.error('Error checking attendance:', e);
                    }
                    
                    // Jika belum absen, tampilkan pesan error
                    showStatus('Gagal: ' + result.message, 'error');
                }
            } catch (error) {
                // Cek dulu apakah sudah absen hari ini
                try {
                    const checkResponse = await fetch('check_kehadiran_hari_ini.php');
                    const checkResult = await checkResponse.json();
                    
                    if (checkResult.success && checkResult.present) {
                        showStatus('Anda sudah absen masuk hari ini', 'success');
                        document.getElementById('absensi-form').style.display = 'none';
                        loadRiwayatAbsensi();
                        return;
                    }
                } catch (e) {
                    console.error('Error checking attendance:', e);
                }
                
                // Jika belum absen, tampilkan pesan error
                showStatus('Terjadi kesalahan saat mengirim data kehadiran. Silakan coba lagi.', 'error');
            }
        }

        // Memuat riwayat absensi saat halaman dimuat (untuk pegawai)
        function showFotoAbsensi(fotoUrl) {
            const modal = document.getElementById('fotoModal');
            const modalImg = document.getElementById('fotoPreview');
            modal.style.display = "block";
            modalImg.src = fotoUrl;
        }

        // Ketika pengguna mengklik tanda (x), tutup modal
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('close') || event.target.classList.contains('modal')) {
                document.getElementById('fotoModal').style.display = "none";
            }
        });

        <?php if ($role === 'pegawai'): ?>
        // Load riwayat absensi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadRiwayatAbsensi();
            // Reload riwayat setiap 5 menit
            setInterval(loadRiwayatAbsensi, 300000);
        });
        <?php endif; ?>
        // Fungsi untuk memuat riwayat absensi
        async function loadRiwayatAbsensi() {
            try {
                // Selalu cek status kehadiran hari ini terlebih dahulu
                const chkResponse = await fetch('check_kehadiran_hari_ini.php');
                if (chkResponse.ok) {
                    const chkResult = await chkResponse.json();
                    
                    if (chkResult.success && chkResult.present) {
                        // Tampilkan pesan sudah absen
                        showStatus('Anda sudah absen masuk hari ini', 'success');
                        document.getElementById('riwayat-absensi').innerHTML =
                            '<div class="info-text">Anda sudah absen masuk hari ini</div>';
                        return;
                    }
                }

                // Jika belum absen hari ini, lanjut ambil riwayat
                showStatus('Memuat data riwayat absensi...', 'info');
                const response = await fetch('get_riwayat_absensi.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Gagal memuat data');
                }
                
                if (!result.data || result.data.length === 0) {
                    document.getElementById('riwayat-absensi').innerHTML = 
                        '<div class="info-text">Belum ada riwayat absensi yang tercatat</div>';
                    showStatus('', ''); // Clear status message
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="riwayat-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Absen</th>
                                    <th>Status</th>
                                    <th>Foto Absensi</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                result.data.forEach(absen => {
                    const fotoPath = absen.foto ? 'uploads/absensi/' + absen.foto : 'assets/img/default-photo.png';
                    // Tentukan kelas CSS berdasarkan status
                    let statusClass = absen.status === 'Tepat Waktu' ? 'status-hadir' : 'status-terlambat';
                    
                    html += `
                        <tr>
                            <td>${absen.tanggal}</td>
                            <td>${absen.jam_masuk} WIB</td>
                            <td><span class="${statusClass}">${absen.status}</span></td>
                            <td>
                                <img src="${fotoPath}" alt="Foto Absensi" 
                                     class="foto-absensi" 
                                     width="50" height="50"
                                     onclick="showFotoAbsensi('${fotoPath}')"
                                     onerror="this.src='assets/img/default-photo.png'">
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Modal untuk preview foto -->
                    <div id="fotoModal" class="modal">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <img class="modal-content" id="fotoPreview">
                    </div>
                `;
                
                document.getElementById('riwayat-absensi').innerHTML = html;
                showStatus('', ''); // Clear status message
                
            } catch (error) {
                console.error('Error:', error);

                // Jika terjadi error, coba cek kehadiran hari ini sekali lagi
                try {
                    const chk = await fetch('check_kehadiran_hari_ini.php');
                    if (chk.ok) {
                        const chkRes = await chk.json();
                        if (chkRes.success && chkRes.present) {
                            // Tampilkan pesan sudah absen
                            showStatus('Anda sudah absen masuk hari ini', 'success');
                            document.getElementById('riwayat-absensi').innerHTML =
                                '<div class="info-text">Anda sudah absen masuk hari ini</div>';
                            return;
                        }
                    }

                    // Jika sudah cek tapi belum absen, tampilkan pesan belum absen
                    document.getElementById('riwayat-absensi').innerHTML =
                        '<div class="info-text">Belum ada absensi yang tercatat hari ini</div>';
                    showStatus('', '');
                    
                } catch (e2) {
                    // Jika gagal cek kehadiran, tampilkan pesan error
                    console.error('Error saat cek kehadiran:', e2);
                    showStatus('Gagal memeriksa status kehadiran. Silakan coba lagi.', 'error');
                }
            }
        }

        // Fungsi untuk menampilkan foto absensi dalam modal
        function showFotoAbsensi(fotoUrl) {
            const modal = document.getElementById('fotoModal');
            const modalImg = document.getElementById('fotoPreview');
            modal.style.display = "block";
            modalImg.src = fotoUrl;
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('fotoModal').style.display = "none";
        }

        // Menangani klik di luar gambar untuk menutup modal
        window.onclick = function(event) {
            const modal = document.getElementById('fotoModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        <?php if ($role === 'pegawai'): ?>
        // Load riwayat absensi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', loadRiwayatAbsensi);
        <?php endif; ?>
    </script>
</body>
</html>
