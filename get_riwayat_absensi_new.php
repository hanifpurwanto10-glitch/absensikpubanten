<?php
require 'config.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesi telah berakhir. Silakan login kembali.'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Query sederhana untuk mengambil riwayat absensi
    $query = $pdo->prepare("
        SELECT 
            waktu_absen,
            foto,
            latitude,
            longitude
        FROM absensi 
        WHERE user_id = ?
        ORDER BY waktu_absen DESC
        LIMIT 30
    ");
    
    $query->execute([$user_id]);
    $riwayat = $query->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_riwayat = [];
    foreach ($riwayat as $absen) {
        $waktu = new DateTime($absen['waktu_absen']);
        $formatted_riwayat[] = [
            'tanggal' => $waktu->format('d F Y'),
            'jam_masuk' => $waktu->format('H:i'),
            'status' => 'Hadir',
            'foto' => $absen['foto']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_riwayat
    ]);

} catch (PDOException $e) {
    error_log('Error in get_riwayat_absensi.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Maaf, terjadi kesalahan saat mengambil data riwayat absensi'
    ]);
}
?>