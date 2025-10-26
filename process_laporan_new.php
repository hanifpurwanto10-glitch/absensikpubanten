<?php
require 'config.php';

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Starting process_laporan_new.php");

// Debug log untuk session
error_log("Session contents: " . print_r($_SESSION, true));

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Pastikan user sudah login
if (empty($_SESSION['user_id'])) {
    error_log("User not logged in - Session user_id is empty");
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// Ambil data JSON dari request
$raw_input = file_get_contents('php://input');
error_log("Raw input received: " . $raw_input);

$input = json_decode($raw_input, true);
error_log("Decoded input: " . print_r($input, true));

$laporan = $input['laporan'] ?? '';
$user_id = $_SESSION['user_id'];

// Debug log untuk user_id dan laporan
error_log("Processing report for user_id: " . $user_id);
error_log("Report content length: " . strlen($laporan));

if (empty($laporan)) {
    error_log("Empty report submitted");
    echo json_encode(['success' => false, 'message' => 'Laporan tidak boleh kosong']);
    exit;
}

try {
    // Periksa apakah ada absensi hari ini
    $check_query = "SELECT id FROM absensi WHERE user_id = :user_id AND DATE(tanggal) = CURDATE()";
    $check_stmt = $pdo->prepare($check_query);
    error_log("Checking attendance with query: " . $check_query);
    
    $check_stmt->execute(['user_id' => $user_id]);
    $attendance = $check_stmt->fetch();
    
    if (!$attendance) {
        error_log("No attendance found for user_id: " . $user_id);
        echo json_encode(['success' => false, 'message' => 'Anda harus melakukan absensi terlebih dahulu']);
        exit;
    }
    
    // Update laporan kerja
    $query = "UPDATE absensi SET laporan_kerja = :laporan WHERE user_id = :user_id AND DATE(tanggal) = CURDATE()";
    $stmt = $pdo->prepare($query);
    error_log("Updating report with query: " . $query);
    
    $params = [
        'laporan' => $laporan,
        'user_id' => $user_id
    ];
    error_log("Parameters: " . print_r($params, true));
    
    $result = $stmt->execute($params);
    $affected_rows = $stmt->rowCount();
    
    error_log("Update result: " . ($result ? "true" : "false"));
    error_log("Affected rows: " . $affected_rows);
    
    if ($result && $affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Laporan berhasil disimpan']);
    } else {
        error_log("Failed to update report. User ID: $user_id, Affected rows: " . $affected_rows);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan laporan. Silakan coba lagi.']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in process_laporan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("General error in process_laporan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}