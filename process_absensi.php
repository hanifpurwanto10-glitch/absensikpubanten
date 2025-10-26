<?php
require 'config.php';

// Pastikan user sudah login
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
    exit;
}

// Terima data JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Format data tidak valid. Silakan muat ulang halaman.']);
    exit;
}

// Validasi data yang diterima
if (empty($data['photo']) || empty($data['latitude']) || empty($data['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Data kehadiran tidak lengkap. Pastikan foto dan lokasi sudah terdata.']);
    exit;
}

try {
    // Buat direktori untuk menyimpan foto jika belum ada
    $uploadDir = 'uploads/absensi/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Decode base64 image
    $photo = $data['photo'];
    $photo = str_replace('data:image/jpeg;base64,', '', $photo);
    $photo = str_replace(' ', '+', $photo);
    $photoData = base64_decode($photo);

    // Generate nama file unik
    $photoFilename = uniqid() . '_' . date('Ymd_His') . '.jpg';
    $photoPath = $uploadDir . $photoFilename;

    // Simpan foto
    if (!file_put_contents($photoPath, $photoData)) {
        throw new Exception('Gagal menyimpan foto');
    }

        // Siapkan data untuk database
        $userId = $_SESSION['user_id'];
        $latitude = floatval($data['latitude']);
        $longitude = floatval($data['longitude']);
        $timestamp = date('Y-m-d H:i:s');

        // Jenis absensi: 'masuk' atau 'keluar'
        $type = isset($data['type']) && $data['type'] === 'keluar' ? 'keluar' : 'masuk';

        // Cek apakah user sudah absen hari ini
        $today = date('Y-m-d');
        $checkStmt = $pdo->prepare(
            "SELECT id, jam_masuk, jam_keluar FROM absensi WHERE user_id = ? AND tanggal = ?"
        );
        $checkStmt->execute([$userId, $today]);
        $existingAbsen = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($type === 'masuk') {
            if ($existingAbsen && !empty($existingAbsen['jam_masuk'])) {
                throw new Exception('Anda sudah melakukan absen masuk hari ini');
            }

            // Cek status keterlambatan
            $jamMasuk = date('H:i:s');
            $statusMasuk = 'tepat_waktu';
            $settingStmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('jam_masuk', 'toleransi_menit')");
            $settings = [];
            while ($row = $settingStmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            if (isset($settings['jam_masuk'])) {
                $toleransiMenit = isset($settings['toleransi_menit']) ? intval($settings['toleransi_menit']) : 15;
                $batasJamMasuk = strtotime($settings['jam_masuk']) + $toleransiMenit * 60;
                if (strtotime($jamMasuk) > $batasJamMasuk) {
                    $statusMasuk = 'terlambat';
                }
            }

            $stmt = $pdo->prepare(
                "INSERT INTO absensi (user_id, tanggal, jam_masuk, foto_masuk, lokasi_masuk_lat, lokasi_masuk_lng, status_masuk) VALUES (?, DATE(NOW()), TIME(NOW()), ?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $photoFilename, $latitude, $longitude, $statusMasuk]);

        } else {
            // type === 'keluar'
            if (!$existingAbsen) {
                throw new Exception('Belum ada catatan absen masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.');
            }
            if (!empty($existingAbsen['jam_keluar'])) {
                throw new Exception('Anda sudah melakukan absen pulang hari ini');
            }

            $stmt = $pdo->prepare(
                "UPDATE absensi SET jam_keluar = TIME(NOW()), foto_keluar = ?, lokasi_keluar_lat = ?, lokasi_keluar_lng = ?, status_keluar = ? WHERE id = ?"
            );
            $stmt->execute([$photoFilename, $latitude, $longitude, 'sesuai', $existingAbsen['id']]);
        }

    echo json_encode([
        'success' => true,
        'message' => 'Absensi berhasil dicatat',
        'data' => [
            'timestamp' => $timestamp,
            'photo' => $photoFilename
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}