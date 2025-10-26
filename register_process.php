<?php
/**
 * Proses pendaftaran user baru
 */
require 'config.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Metode request tidak diizinkan.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

// CSRF check
$csrf_post = $_POST['csrf_token'] ?? null;
if (empty($csrf_post) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_post)) {
    http_response_code(400);
    $response['message'] = 'Token CSRF tidak valid.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Basic validation
if ($nip === '' || $nama === '' || $email === '' || $password === '' || $password_confirm === '') {
    $response['message'] = 'Semua field harus diisi.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Format email tidak valid.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

if (strlen($password) < 8) {
    $response['message'] = 'Kata sandi minimal 8 karakter.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

if ($password !== $password_confirm) {
    $response['message'] = 'Konfirmasi kata sandi tidak cocok.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

try {
    // Cek apakah email atau NIP sudah terdaftar
    $sql = "SELECT id FROM users WHERE email = :email OR nip = :nip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'nip' => $nip]);
    $exists = $stmt->fetch();
    if ($exists) {
        $response['message'] = 'Email atau NIP sudah terdaftar.';
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $insert = "INSERT INTO users (nip, nama_lengkap, jabatan, divisi, email, password, foto_profil, role, status) 
               VALUES (:nip, :nama_lengkap, NULL, NULL, :email, :password, 'default.png', 'pegawai', 'aktif')";
    $stmt = $pdo->prepare($insert);
    $stmt->execute([
        'nip' => $nip,
        'nama_lengkap' => $nama,
        'email' => $email,
        'password' => $hash
    ]);

    // Ambil id user yang baru dibuat
    $userId = $pdo->lastInsertId();

    // Ambil data user untuk response (tanpa password)
    $stmt = $pdo->prepare("SELECT id, nip, nama_lengkap, email, role, status, foto_profil FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $newUser = $stmt->fetch();

    if ($newUser) {
        // Set session (auto-login)
        session_regenerate_id(true);
        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['nama_lengkap'] = $newUser['nama_lengkap'];
        $_SESSION['role'] = $newUser['role'];
        $_SESSION['logged_in'] = true;

        // Hapus CSRF token agar tidak dipakai ulang
        if (isset($_SESSION['csrf_token'])) unset($_SESSION['csrf_token']);

        $response['success'] = true;
        $response['message'] = 'Pendaftaran berhasil. Sedang masuk...';
        $response['user'] = $newUser;
        $response['auto_login'] = true;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

    // Jika sesuatu aneh terjadi dan user tidak ditemukan setelah insert
    $response['success'] = true;
    $response['message'] = 'Pendaftaran berhasil. Silakan masuk.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;

} catch (\PDOException $e) {
    error_log('Register Error: ' . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'Terjadi kesalahan pada server. Silakan coba lagi.';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

?>
