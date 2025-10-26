<?php
require 'config.php';

// Hasilkan token CSRF dan simpan di sesi
try {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Gagal membuat token CSRF']);
}

?>
