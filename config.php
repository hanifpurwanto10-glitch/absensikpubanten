<?php
/**
 * File Konfigurasi Database
 * * Menggunakan PDO (PHP Data Objects) untuk koneksi yang lebih aman dan fleksibel.
 */

// Pengaturan Database
define('DB_HOST', 'localhost');      // Ganti dengan host database Anda (misal: 127.0.0.1)
define('DB_PORT', '3306');           // Port MySQL (default: 3306)
define('DB_NAME', 'db_absensi_pegawai_kpu'); // Nama database yang digunakan
define('DB_USER', 'root');           // Ganti dengan username database Anda
define('DB_PASS', '');               // Ganti dengan password database Anda
define('DB_CHARSET', 'utf8mb4');

// Data Source Name (DSN)
$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// Opsi untuk PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Menampilkan error sebagai exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengembalikan hasil sebagai associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli
];

// Inisialisasi koneksi PDO
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Menangani error koneksi database
    // Di lingkungan produksi, jangan tampilkan error detail ke pengguna
    // Cukup log error dan tampilkan pesan umum
    error_log("Koneksi Database Gagal: " . $e->getMessage());
    die("Koneksi ke server database gagal. Silakan coba lagi nanti.");
}

// Pengaturan Sesi (Session)
// Memulai sesi di file config agar tersedia di semua file yang meng-include-nya
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // Session berlaku 1 hari (dalam detik)
        'cookie_secure'   => isset($_SERVER['HTTPS']), // Hanya kirim cookie via HTTPS jika ada
        'cookie_httponly' => true,  // Cookie tidak bisa diakses via JavaScript
        'use_strict_mode' => true   // Mencegah session fixation attacks
    ]);
}

// Pengaturan zona waktu
date_default_timezone_set('Asia/Jakarta');

?>