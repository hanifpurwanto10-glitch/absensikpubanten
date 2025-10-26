<?php
require 'config.php';

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Pastikan user sudah login
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil data JSON dari request
$input = json_decode(file_get_contents('php://input'), true);
$laporan = $input['laporan'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($laporan)) {
    echo json_encode(['success' => false, 'message' => 'Laporan tidak boleh kosong']);
    exit;
}

try {
    // Untuk debugging
    error_log("User ID: " . $user_id);
    error_log("Laporan: " . $laporan);
    
    // Update laporan kerja ke dalam tabel absensi untuk hari ini
    $query = "UPDATE absensi SET laporan_kerja = ? WHERE user_id = ? AND DATE(tanggal) = CURDATE()";
    error_log("Query: " . $query);
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Database prepare failed");
    }
    
    $stmt->bind_param('si', $laporan, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Laporan berhasil disimpan']);
        } else {
            // Jika tidak ada baris yang terupdate, mungkin belum ada record absensi hari ini
            echo json_encode(['success' => false, 'message' => 'Anda harus melakukan absensi terlebih dahulu']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan laporan']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error in process_laporan.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}

$conn->close();
?>