<?php
require 'config.php';

// Cek apakah user sudah login dan memiliki role admin
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Ambil data absensi hari ini
$today = date('Y-m-d');
$query = $pdo->prepare("
    SELECT 
        u.id,
        u.nama_lengkap,
        u.nip,
        CONCAT('uploads/absensi/', a.foto_masuk) as foto,
        CONCAT(a.tanggal, ' ', a.jam_masuk) as waktu_absen,
        a.tanggal,
        a.laporan_kerja,
        CASE 
            WHEN a.jam_masuk <= '08:00:00' THEN 'tepat_waktu'
            ELSE 'terlambat'
        END as status_masuk
    FROM users u
    LEFT JOIN absensi a ON u.id = a.user_id AND a.tanggal = ?
    WHERE u.role = 'pegawai' AND u.status = 'aktif'
    ORDER BY u.nama_lengkap ASC
");
$query->execute([$today]);
$pegawai = $query->fetchAll(PDO::FETCH_ASSOC);

// Query untuk pegawai yang sudah absen tapi belum isi laporan
$queryBelumLaporan = $pdo->prepare("
    SELECT DISTINCT u.nama_lengkap
    FROM users u
    INNER JOIN absensi a ON u.id = a.user_id 
    WHERE u.role = 'pegawai' 
    AND u.status = 'aktif'
    AND a.tanggal = ?
    AND (a.laporan_kerja IS NULL OR a.laporan_kerja = '')
    ORDER BY u.nama_lengkap ASC
");
$queryBelumLaporan->execute([$today]);
$pegawaiBelumLaporan = $queryBelumLaporan->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Hari Ini - KPU Provinsi Banten</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f6fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            background-color: #3498db;
            font-size: 18px;
            font-weight: 500;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn.back {
            background-color: #2c3e50;
        }
        .section-title {
            color: #2c3e50;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .content-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .pegawai-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .belum-absen-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        /* Style untuk container kedua daftar */
        .side-lists-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 20px;
        }
        .belum-absen-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            color: #e74c3c;
            font-weight: 700;
            font-size: 24px;
            display: flex;
            align-items: center;
        }
        .belum-absen-item:last-child {
            border-bottom: none;
        }
        .belum-absen-item .nomor-urut {
            color: #e74c3c;
            margin-right: 12px;
            font-weight: 700;
            font-size: 24px;
        }
        .status-count {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .status-box {
            flex: 1;
            padding: 4px 8px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            color: white;
            transition: transform 0.3s ease;
            height: 35px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
            max-width: fit-content;
        }
        .status-count {
            justify-content: center;
            gap: 15px;
        }
        .status-box:nth-child(1) {
            background: #3498db;
        }
        .status-box:nth-child(2) {
            background: #2ecc71;
        }
        .status-box:nth-child(3) {
            background: #e74c3c;
        }
        .status-box h3 {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 500;
            line-height: 1;
            white-space: nowrap;
        }
        .status-box .count {
            font-size: 18px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            line-height: 1;
            margin: 0;
        }
        .status-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2px;
        }
        .waktu-absen {
            font-size: 14px;
            color: #666;
            margin-left: 10px;
        }
        .waktu-absen i {
            margin-right: 5px;
            color: #3498db;
        }
        .foto-container {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background-color: #f8f9fa;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .foto-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30%;
            background: linear-gradient(transparent, rgba(0,0,0,0.3));
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .foto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }
        .foto-container img:hover {
            transform: scale(1.05);
        }
        .status-absen {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 0;
        }
        .pegawai-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .foto-container {
            width: 100%;
            height: 150px;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .foto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .foto-placeholder {
            color: #95a5a6;
            font-size: 14px;
            text-align: center;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .foto-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }
        .pegawai-info {
            padding: 8px;
        }
        .pegawai-nama {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #2c3e50;
        }

        .status-absen {
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
        .hadir {
            background-color: #d4edda;
            color: #155724;
        }
        .terlambat {
            background-color: #fff3cd;
            color: #856404;
        }
        .belum-hadir {
            background-color: #f8d7da;
            color: #721c24;
        }
        .izin, .sakit, .cuti {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .waktu-absen {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .title {
            color: #2c3e50;
            margin: 0;
        }
        .laporan-kerja {
            font-size: 13px;
            color: #444;
            margin: 8px 0;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        .laporan-kerja i {
            color: #3498db;
            margin-right: 5px;
        }
        .laporan-title {
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }
        .laporan-content {
            color: #444;
            line-height: 1.4;
            margin-left: 23px;
        }
        .date-info {
            color: #7f8c8d;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="title">Absensi Hari Ini</h1>
                <p class="date-info"><?php echo date('l, d F Y'); ?></p>
            </div>
            <a href="admin_dashboard.php" class="btn back">Kembali ke Dashboard</a>
        </div>

        <?php
        $sudah_absen = array_filter($pegawai, function($p) { return !empty($p['waktu_absen']); });
        $belum_absen = array_filter($pegawai, function($p) { return empty($p['waktu_absen']); });
        
        $total_pegawai = count($pegawai);
        $total_hadir = count($sudah_absen);
        $total_belum = count($belum_absen);
        ?>

        <div class="status-count">
            <div class="status-box">
                <h3>Total Pegawai</h3>
                <div class="count"><?php echo $total_pegawai; ?></div>
            </div>
            <div class="status-box">
                <h3>Sudah Absen</h3>
                <div class="count"><?php echo $total_hadir; ?></div>
            </div>
            <div class="status-box">
                <h3>Belum Absen</h3>
                <div class="count"><?php echo $total_belum; ?></div>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if (!empty($sudah_absen)): ?>
            <div>
                <h2 class="section-title">Pegawai Yang Sudah Absen</h2>
                <div class="pegawai-grid">
                    <?php foreach ($sudah_absen as $p): ?>
                    <div class="pegawai-card">
                    <div class="foto-container">
                        <?php if ($p['foto']): ?>
                            <img src="<?php echo $p['foto']; ?>" 
                                 alt="Foto Absensi <?php echo htmlspecialchars($p['nama_lengkap']); ?>"
                                 onerror="this.onerror=null; this.src='assets/img/default-avatar.png';"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="foto-placeholder">
                                <i class="fas fa-user-circle"></i>
                                <span>Belum ada foto</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="pegawai-info">
                        <h3 class="pegawai-nama"><?php echo htmlspecialchars($p['nama_lengkap']); ?></h3>
                        <?php if ($p['waktu_absen']): ?>
                            <div class="status-wrapper">
                                <span class="status-absen <?php echo $p['status_masuk'] === 'tepat_waktu' ? 'hadir' : 'terlambat'; ?>">
                                    <?php echo $p['status_masuk'] === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat'; ?>
                                </span>
                                <span class="waktu-absen">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                        $waktu = new DateTime($p['waktu_absen']);
                                        echo $waktu->format('H:i'); 
                                    ?> WIB
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="status-absen belum-hadir">Belum Hadir</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($p['laporan_kerja'])): ?>
                            <div class="laporan-kerja">
                                <div class="laporan-title">
                                    <i class="fas fa-tasks"></i>
                                    <span>Laporan Hari Ini</span>
                                </div>
                                <div class="laporan-content"><?php echo htmlspecialchars($p['laporan_kerja']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($belum_absen) || !empty($pegawaiBelumLaporan)): ?>
            <div>
                <?php if (!empty($belum_absen)): ?>
                    <h2 class="section-title">Pegawai Yang Belum Absen</h2>
                    <div class="belum-absen-list">
                        <?php 
                        $no = 1;
                        foreach ($belum_absen as $p): 
                        ?>
                            <div class="belum-absen-item">
                                <span class="nomor-urut"><?php echo $no; ?>.</span>
                                <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                            </div>
                        <?php 
                        $no++;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pegawaiBelumLaporan)): ?>
                    <h2 class="section-title" style="margin-top: 20px;">Pegawai Yang Belum Mengisi Laporan</h2>
                    <div class="belum-absen-list">
                        <?php 
                        $no = 1;
                        foreach ($pegawaiBelumLaporan as $p): 
                        ?>
                            <div class="belum-absen-item">
                                <span class="nomor-urut"><?php echo $no; ?>.</span>
                                <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                            </div>
                        <?php 
                        $no++;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Fungsi untuk refresh halaman setiap 10 detik
        function autoRefresh() {
            // Tampilkan countdown di title
            let countdown = 10;
            const originalTitle = document.title;
            
            const updateCountdown = () => {
                document.title = `(${countdown}s) ${originalTitle}`;
                countdown--;
                
                if (countdown < 0) {
                    // Reset countdown dan refresh halaman
                    countdown = 10;
                    location.reload();
                }
            };

            // Update setiap detik
            setInterval(updateCountdown, 1000);

            // Tampilkan notifikasi kecil bahwa halaman akan di-refresh
            const notif = document.createElement('div');
            notif.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(52, 152, 219, 0.9);
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                font-size: 14px;
                z-index: 1000;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            `;
            notif.innerHTML = 'Halaman akan diperbarui otomatis setiap 10 detik';
            document.body.appendChild(notif);

            // Sembunyikan notifikasi setelah 5 detik
            setTimeout(() => {
                notif.style.opacity = '0';
                notif.style.transition = 'opacity 0.5s ease';
                setTimeout(() => notif.remove(), 500);
            }, 5000);
        }

        // Jalankan fungsi autoRefresh saat halaman dimuat
        document.addEventListener('DOMContentLoaded', autoRefresh);
    </script>
</body>
</html>