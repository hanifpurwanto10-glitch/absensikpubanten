<?php
require 'config.php';

// Pastikan yang mengakses adalah admin
if (empty($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

try {
    // Ambil daftar pegawai dari database
    $query = $pdo->query("SELECT nama_lengkap, email, status FROM users WHERE role = 'pegawai' ORDER BY nama_lengkap");
    $pegawai = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Kirim response
    header('Content-Type: application/json');
    echo json_encode($pegawai);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Gagal mengambil data pegawai']);
}
?>