<?php
require 'config.php';

// Hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Pastikan login
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

$userId = $_SESSION['user_id'];

// CSRF
$csrf = $_POST['csrf_token'] ?? null;
if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
    exit;
}

$nama = trim($_POST['nama_lengkap'] ?? '');
$jabatan = trim($_POST['jabatan'] ?? null);
$divisi = trim($_POST['divisi'] ?? null);
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

try {
    // Cek email sudah dipakai orang lain
    $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'id' => $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email sudah dipakai oleh pengguna lain.']);
        exit;
    }

    // Bangun query update dinamis
    $params = ['id' => $userId];
    $sets = [];
    $sets[] = 'nama_lengkap = :nama_lengkap'; $params['nama_lengkap'] = $nama;
    $sets[] = 'jabatan = :jabatan'; $params['jabatan'] = $jabatan;
    $sets[] = 'divisi = :divisi'; $params['divisi'] = $divisi;
    $sets[] = 'email = :email'; $params['email'] = $email;

    if (!empty($password)) {
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Kata sandi minimal 8 karakter.']);
            exit;
        }
        $sets[] = 'password = :password';
        $params['password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Perbarui session nama jika berubah
    $_SESSION['nama_lengkap'] = $nama;

    echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
    exit;

} catch (\PDOException $e) {
    error_log('Profile update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
    exit;
}

?>
