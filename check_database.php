<?php
require 'config.php';

header('Content-Type: application/json');

try {
    // Cek koneksi database
    $pdo->query('SELECT 1');
    
    // Cek keberadaan tabel absensi
    $tables = $pdo->query("SHOW TABLES LIKE 'absensi'")->fetchAll();
    
    if (empty($tables)) {
        throw new Exception('Tabel absensi tidak ditemukan. Pastikan database sudah diimport dengan benar.');
    }
    
    // Cek struktur tabel absensi
    $columns = $pdo->query("SHOW COLUMNS FROM absensi")->fetchAll(PDO::FETCH_COLUMN);
    $required_columns = ['user_id', 'tanggal', 'jam_masuk', 'jam_keluar', 'foto_masuk', 'foto_keluar', 'status_masuk'];
    
    $missing_columns = array_diff($required_columns, $columns);
    
    if (!empty($missing_columns)) {
        throw new Exception('Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database dan tabel sudah benar',
        'database' => DB_NAME,
        'tables' => $tables,
        'columns' => $columns
    ]);
    
} catch (Exception $e) {
    error_log('Check Database Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'database' => DB_NAME
    ]);
}
?>