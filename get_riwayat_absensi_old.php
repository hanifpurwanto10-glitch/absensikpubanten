<?php
require 'config.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (empty($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sesi telah berakhir. Silakan login kembali.'
        ]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    
    // Cek apakah tabel menggunakan struktur baru atau lama
    $table_check = $pdo->query("SHOW COLUMNS FROM absensi LIKE 'tipe'");
    $has_tipe = $table_check->rowCount() > 0;
    
    if ($has_tipe) {
        // Struktur tabel baru
        $query = $pdo->prepare("
            SELECT 
                DATE(waktu_absen) as tanggal,
                MIN(CASE WHEN tipe = 'masuk' THEN TIME(waktu_absen) END) as jam_masuk,
                MAX(CASE WHEN tipe = 'keluar' THEN TIME(waktu_absen) END) as jam_pulang,
                CASE 
                    WHEN COUNT(DISTINCT tipe) = 2 THEN 'Hadir Lengkap'
                    WHEN COUNT(DISTINCT tipe) = 1 AND MAX(tipe) = 'masuk' THEN 'Belum Pulang'
                    ELSE 'Hadir'
                END as status
            FROM absensi 
            WHERE user_id = :user_id
            GROUP BY DATE(waktu_absen)
            ORDER BY tanggal DESC
            LIMIT 30");
    } else {
        // Struktur tabel lama
        $query = $pdo->prepare("
            SELECT 
                DATE(waktu_absen) as tanggal,
                TIME(waktu_absen) as jam_masuk,
                NULL as jam_pulang,
                'Hadir' as status
            FROM absensi 
            WHERE user_id = :user_id
            GROUP BY DATE(waktu_absen)
            ORDER BY tanggal DESC
            LIMIT 30");
    ");
    
    $query->execute(['user_id' => $user_id]);
    $riwayat = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($riwayat)) {
        echo json_encode([
            'success' => true,
            'message' => 'Belum ada riwayat absensi yang tercatat',
            'data' => []
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => $riwayat
        ]);
    }
} catch (PDOException $e) {
    error_log('Error in get_riwayat_absensi.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Maaf, terjadi kesalahan saat mengambil data riwayat absensi',
        'error_details' => $e->getMessage()
    ]);
}
?>