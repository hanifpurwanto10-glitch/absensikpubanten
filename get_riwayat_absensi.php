<?php
require 'config.php';

header('Content-Type: application/json');

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

    // Cek apakah user_id valid
    $check_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->execute([$user_id]);
    if (!$check_user->fetch()) {
        throw new Exception('User tidak ditemukan');
    }

    // Query untuk riwayat absensi
    // Ambil setting jam kerja
    $stmt_setting = $pdo->query("SELECT setting_value FROM settings WHERE setting_key IN ('jam_masuk', 'toleransi_menit')");
    $settings = $stmt_setting->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $jam_masuk = isset($settings['jam_masuk']) ? $settings['jam_masuk'] : '07:30:00';
    $toleransi = isset($settings['toleransi_menit']) ? (int)$settings['toleransi_menit'] : 15;

    $sql = "SELECT 
                a.id, 
                a.user_id, 
                a.foto_masuk as foto, 
                a.waktu_absen,
                a.latitude, 
                a.longitude,
                TIME(a.waktu_absen) as jam_actual,
                CASE 
                    WHEN TIME(a.waktu_absen) <= ADDTIME(?, SEC_TO_TIME(? * 60)) THEN 'Tepat Waktu'
                    ELSE 'Terlambat'
                END as status_kehadiran
            FROM absensi a
            WHERE a.user_id = ? 
            ORDER BY a.waktu_absen DESC 
            LIMIT 30";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$jam_masuk, $toleransi, $user_id]);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($riwayat as $absen) {
        // Pastikan kolom waktu_absen ada dan tidak null
        $waktu_str = isset($absen['waktu_absen']) ? $absen['waktu_absen'] : null;
        if (empty($waktu_str)) {
            // jika waktu tidak ada, lewati record
            continue;
        }

        try {
            $waktu = new DateTime($waktu_str);
            
            // Array nama bulan dalam bahasa Indonesia
            $bulan = array(
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            );
            
            $tanggal = $waktu->format('d F Y');
            // Ganti nama bulan ke bahasa Indonesia
            $tanggal = strtr($tanggal, $bulan);
            $jam = $waktu->format('H:i');
        } catch (Exception $exRow) {
            // Jika gagal parse tanggal, set nilai default dan catat log
            error_log('get_riwayat_absensi.php: gagal parse waktu_absen: ' . $exRow->getMessage());
            $tanggal = date('d F Y');
            $jam = '-';
        }

        $data[] = [
            'id' => $absen['id'],
            'tanggal' => $tanggal,
            'jam_masuk' => $jam,
            'foto' => $absen['foto'],
            'status' => $absen['status_kehadiran'],
            'lokasi' => [
                'lat' => isset($absen['latitude']) ? $absen['latitude'] : null,
                'lng' => isset($absen['longitude']) ? $absen['longitude'] : null
            ]
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log("Error in get_riwayat_absensi.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil data riwayat absensi'
    ]);
}
?>