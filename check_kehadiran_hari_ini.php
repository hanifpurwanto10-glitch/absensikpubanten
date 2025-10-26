<?php
require 'config.php';

header('Content-Type: application/json');

// Cek koneksi database
if (!isset($pdo)) {
    error_log('Error in check_kehadiran_hari_ini.php: Database connection not established');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

// Cek sesi user
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi telah berakhir']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah tabel absensi ada
    $check_table = $pdo->query("SHOW TABLES LIKE 'absensi'");
    if ($check_table->rowCount() == 0) {
        throw new Exception('Tabel absensi tidak ditemukan');
    }
    
    // Query untuk cek kehadiran hari ini
    $sql = "SELECT COUNT(*) as cnt 
            FROM absensi 
            WHERE user_id = :user_id 
            AND DATE(tanggal) = CURRENT_DATE()
            AND jam_masuk IS NOT NULL";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    
    if ($stmt === false) {
        throw new Exception('Query gagal dieksekusi');
    }
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row === false) {
        throw new Exception('Gagal mengambil data absensi');
    }
    
    $present = intval($row['cnt']) > 0;
    
    // Tambahkan informasi debug jika diperlukan
    $response = [
        'success' => true,
        'present' => $present,
        'message' => $present ? 'Sudah absen hari ini' : 'Belum absen hari ini'
    ];
    
    echo json_encode($response);

} catch (PDOException $e) {
    error_log('Database Error in check_kehadiran_hari_ini.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error in check_kehadiran_hari_ini.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

?>
