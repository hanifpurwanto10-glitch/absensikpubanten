<?php
require 'config.php';

// Pastikan yang mengakses adalah admin
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

try {
    $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
    
    // Ambil data absensi untuk tanggal tertentu
    $query = $pdo->prepare("
        SELECT 
            u.nama_lengkap,
            DATE(a.waktu) as tanggal,
            MIN(CASE WHEN a.tipe = 'masuk' THEN TIME(a.waktu) END) as jam_masuk,
            MAX(CASE WHEN a.tipe = 'keluar' THEN TIME(a.waktu) END) as jam_pulang,
            CASE 
                WHEN COUNT(DISTINCT a.tipe) = 2 THEN 'Hadir'
                WHEN COUNT(DISTINCT a.tipe) = 1 AND MAX(a.tipe) = 'masuk' THEN 'Belum Pulang'
                ELSE 'Tidak Lengkap'
            END as status
        FROM users u
        LEFT JOIN absensi a ON u.id = a.user_id AND DATE(a.waktu) = :tanggal
        WHERE u.role = 'pegawai'
        GROUP BY u.id, u.nama_lengkap, DATE(a.waktu)
        ORDER BY u.nama_lengkap
    ");
    
    $query->execute(['tanggal' => $tanggal]);
    $absensi = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Kirim response
    header('Content-Type: application/json');
    echo json_encode($absensi);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Gagal mengambil data absensi']);
}
?>