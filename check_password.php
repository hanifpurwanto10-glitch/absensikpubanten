<?php
require 'config.php';
// Ganti $hash dan $plain sesuai data
$hash = '$2y$10$...'; // salin dari kolom password di DB
$plain = 'password123';
var_dump(password_verify($plain, $hash));