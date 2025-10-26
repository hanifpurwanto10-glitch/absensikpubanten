<?php
/**
 * Proses Login Pegawai (Backend)
 * * Menerima data POST dari form login (index.html).
 * Memverifikasi kredensial dengan database menggunakan prepared statements dan password_verify.
 * Membuat session jika berhasil.
 */

// 1. Include file koneksi dan konfigurasi
require 'config.php';

// 2. Inisialisasi response (untuk dikirim balik sebagai JSON)
$response = [
    'success' => false,
    'message' => 'Email atau password salah.',
    'user'    => null
];

// 3. Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Request Method: " . $_SERVER["REQUEST_METHOD"]); // Debugging line
    // 4. Ambil data JSON dari body request (jika frontend mengirim JSON)
    // Jika frontend mengirim 'application/x-www-form-urlencoded', gunakan $_POST
    
    // 4a. Validasi CSRF Token (jika disertakan)
    $csrf_post = $_POST['csrf_token'] ?? null;
    if (empty($csrf_post) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_post)) {
        http_response_code(400);
        $response['message'] = 'Token CSRF tidak valid.';
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    
    // 5. Validasi input dasar
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email dan password tidak boleh kosong.';
        echo json_encode($response);
        exit;
    }
    
    // 6. Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Jika login bisa menggunakan NIP, tambahkan logika cek NIP di sini
        // Untuk saat ini, kita asumsikan input adalah email
        // $response['message'] = 'Format email tidak valid.';
        // echo json_encode($response);
        // exit;
    }

    try {
        // Cek apakah ini admin default
        if ($email === 'admin@123.com' && $password === 'admin123') {
            // Set session untuk admin default
            session_regenerate_id(true);
            $_SESSION['user_id'] = 0; // ID khusus untuk admin default
            $_SESSION['nama_lengkap'] = 'Administrator';
            $_SESSION['role'] = 'admin';
            $_SESSION['logged_in'] = true;

            $response['success'] = true;
            $response['message'] = 'Login berhasil. Mengarahkan ke dashboard...';
            $response['user'] = [
                'id' => 0,
                'nama_lengkap' => 'Administrator',
                'email' => 'admin@123.com',
                'role' => 'admin',
                'status' => 'aktif'
            ];
            
            echo json_encode($response);
            exit;
        }

        // Jika bukan admin default, cek di database
        $sql = "SELECT id, nama_lengkap, email, password, role, status, foto_profil 
            FROM users 
            WHERE (email = :email_cred OR nip = :nip_cred) 
            AND status = 'aktif'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'email_cred' => $email,
            'nip_cred'   => $email
        ]);
        $user = $stmt->fetch();

        // 8. Verifikasi User dan Password
        // Cek apakah user ditemukan DAN password cocok
        if ($user && password_verify($password, $user['password'])) {
            
            // 9. Regenerasi Session ID untuk keamanan
            session_regenerate_id(true);

            // 10. Simpan data penting ke dalam Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // 11. Siapkan data user untuk dikirim kembali ke frontend (tanpa password)
            unset($user['password']); // Hapus password dari data response

            // Setelah login sukses, hapus CSRF token agar tidak dapat dipakai ulang
            if (isset($_SESSION['csrf_token'])) {
                unset($_SESSION['csrf_token']);
            }

            $response['success'] = true;
            $response['message'] = 'Login berhasil. Mengarahkan ke dashboard...';
            $response['user'] = $user;

        } else {
            // User tidak ditemukan atau password salah
            $response['message'] = 'Kombinasi Email/NIP dan password salah atau akun tidak aktif.';
        }

    } catch (\PDOException $e) {
        // Tangani error database (log detail, tapi jangan kirim detail ke client)
        error_log("Login Error: " . $e->getMessage());
        http_response_code(500);
        $response['message'] = 'Terjadi kesalahan pada server. Silakan coba lagi.';
    }

} else {
    // Jika bukan request POST
    $response['message'] = 'Metode request tidak diizinkan.';
}

// 12. Kirim response sebagai JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;
