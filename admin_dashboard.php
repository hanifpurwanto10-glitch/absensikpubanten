<?php
require 'config.php';

// Cek apakah user sudah login dan memiliki role admin
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Fungsi untuk mendapatkan total pegawai
function getTotalPegawai($pdo) {
    $query = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'pegawai'");
    return $query->fetch(PDO::FETCH_ASSOC)['total'];
}

// Fungsi untuk mendapatkan total kehadiran hari ini
function getKehadiranHariIni($pdo) {
    $today = date('Y-m-d');
    $query = $pdo->prepare("
        SELECT COUNT(DISTINCT user_id) as total 
        FROM absensi 
        WHERE tanggal = ?
    ");
    $query->execute([$today]);
    return $query->fetch(PDO::FETCH_ASSOC)['total'];
}

// Fungsi untuk mendapatkan persentase kehadiran
function getPersentaseKehadiran($pdo) {
    $totalPegawai = getTotalPegawai($pdo);
    if ($totalPegawai == 0) return 0;
    
    $hadir = getKehadiranHariIni($pdo);
    return round(($hadir / $totalPegawai) * 100);
}

// Mendapatkan statistik untuk dashboard
$totalPegawai = getTotalPegawai($pdo);
$kehadiranHariIni = getKehadiranHariIni($pdo);
$persentaseKehadiran = getPersentaseKehadiran($pdo);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - KPU Provinsi Banten</title>
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
            margin-bottom: 30px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .stat-card.total-pegawai {
            background: #3498db;
        }
        .stat-card.kehadiran {
            background: #2ecc71;
        }
        .stat-card.persentase {
            background: #e67e22;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .stat-card i {
            margin-right: 10px;
            font-size: 24px;
        }
        .stat-value {
            font-size: 42px;
            font-weight: bold;
            margin-top: 15px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            letter-spacing: 1px;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 25%;
            height: 25%;
            background: rgba(255, 255, 255, 0.1);
            border-top-left-radius: 50%;
        }
        .data-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            font-size: 24px;
        }
        .data-table th,
        .data-table td {
            padding: 20px 25px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
            font-size: 28px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            background-color: #3498db;
            font-size: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn.danger {
            background-color: #e74c3c;
        }
        .search-box {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            font-size: 24px;
        }
        .filter-container {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard Admin KPU Provinsi Banten</h1>
            <a href="logout.php" class="btn danger">Keluar</a>
        </div>

        <div class="stats-container">
            <div class="stat-card total-pegawai">
                <h3><i class="fas fa-users"></i> Total Pegawai</h3>
                <div class="stat-value"><?php echo $totalPegawai; ?></div>
            </div>
            <div class="stat-card kehadiran">
                <h3><i class="fas fa-user-check"></i> Kehadiran Hari Ini</h3>
                <div class="stat-value"><?php echo $kehadiranHariIni; ?></div>
            </div>
            <div class="stat-card persentase">
                <h3><i class="fas fa-chart-pie"></i> Persentase Kehadiran</h3>
                <div class="stat-value"><?php echo $persentaseKehadiran; ?>%</div>
            </div>
        </div>

        <!-- Tabel Riwayat Absensi Hari Ini -->
        <h2 style="font-size: 32px; margin-bottom: 25px; color: #2c3e50;">Riwayat Absensi Hari Ini</h2>
        <div class="filter-container">
            <input type="date" id="dateFilter" class="search-box">
            <a href="absensi_hari_ini.php" class="btn btn-primary">Lihat Detail Absensi</a>
        </div>

        <table class="data-table" id="absensiTable">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Pulang</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = date('Y-m-d');
                $query = $pdo->prepare("
                    SELECT 
                        u.nama_lengkap,
                        a.jam_masuk as waktu_masuk,
                        a.jam_keluar as waktu_pulang,
                        a.status_masuk as status
                    FROM users u
                    LEFT JOIN absensi a ON u.id = a.user_id 
                    WHERE a.tanggal = ?
                    ORDER BY a.jam_masuk DESC
                ");
                $query->execute([$today]);
                
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                    echo "<td>" . ($row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-') . "</td>";
                    echo "<td>" . ($row['waktu_pulang'] ? date('H:i', strtotime($row['waktu_pulang'])) : '-') . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Tombol Tampilkan Daftar Pegawai -->
        <div style="margin-top: 30px;">
            <input type="text" id="searchInput" class="search-box" placeholder="Cari pegawai..." style="display: none;">
            <button class="btn" onclick="togglePegawaiTable()" id="togglePegawaiBtn">Tampilkan Daftar Pegawai</button>
        </div>

        <!-- Tombol dan Tabel Data Pegawai -->
        <button class="btn" onclick="togglePegawaiTable()" id="togglePegawaiBtn">Tampilkan Daftar Pegawai</button>
        
        <div id="pegawaiSection" style="display: none; margin-top: 20px;">
            <h2>Daftar Pegawai</h2>
            <table class="data-table" id="pegawaiTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Terakhir Hadir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = $pdo->query("
                    SELECT 
                        u.id,
                        u.nama_lengkap,
                        u.email,
                        u.status,
                        MAX(CONCAT(a.tanggal, ' ', COALESCE(a.jam_masuk, a.jam_keluar))) as last_presence
                    FROM users u
                    LEFT JOIN absensi a ON u.id = a.user_id
                    WHERE u.role = 'pegawai'
                    GROUP BY u.id, u.nama_lengkap, u.email, u.status
                    ORDER BY u.nama_lengkap
                ");
                
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . ($row['last_presence'] ? date('d/m/Y H:i', strtotime($row['last_presence'])) : '-') . "</td>";
                    echo "<td>";
                    echo "<a href='detail_pegawai.php?id=" . $row['id'] . "' class='btn'>Detail</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>


    </div>

    <script>
        // Fungsi untuk menampilkan/menyembunyikan tabel pegawai
        function togglePegawaiTable() {
            const section = document.getElementById('pegawaiSection');
            const button = document.getElementById('togglePegawaiBtn');
            if (section.style.display === 'none') {
                section.style.display = 'block';
                button.textContent = 'Sembunyikan Daftar Pegawai';
            } else {
                section.style.display = 'none';
                button.textContent = 'Tampilkan Daftar Pegawai';
            }
        }

        // Fungsi pencarian
        document.getElementById('searchInput').addEventListener('keyup', function() {
            if (document.getElementById('pegawaiSection').style.display === 'none') return;
            
            const searchText = this.value.toLowerCase();
            const table = document.getElementById('pegawaiTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[0];
                if (nameCell) {
                    const name = nameCell.textContent || nameCell.innerText;
                    if (name.toLowerCase().indexOf(searchText) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });

        // Set tanggal hari ini sebagai default untuk filter tanggal
        document.getElementById('dateFilter').valueAsDate = new Date();

        // Tampilkan/sembunyikan search box saat tabel ditampilkan/disembunyikan
        function updateSearchVisibility() {
            const searchInput = document.getElementById('searchInput');
            const pegawaiSection = document.getElementById('pegawaiSection');
            searchInput.style.display = pegawaiSection.style.display;
        }

        // Observer untuk memantau perubahan visibility pada pegawaiSection
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    updateSearchVisibility();
                }
            });
        });

        observer.observe(document.getElementById('pegawaiSection'), {
            attributes: true
        });
    </script>
</body>
</html>