<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi KPU Provinsi Banten</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg,#eef5fb,#ffffff);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 360px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-header h1 {
            color: #0b3b6b;
            font-size: 24px;
            margin: 0 0 8px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .register-link {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Sistem Absensi KPU</h1>
            <p>Provinsi Banten</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                $error = htmlspecialchars($_GET['error']);
                switch($error) {
                    case 'invalid':
                        echo 'Email atau password salah';
                        break;
                    case 'empty':
                        echo 'Mohon isi semua field';
                        break;
                    default:
                        echo 'Terjadi kesalahan. Silakan coba lagi';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Registrasi berhasil! Silakan login.
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan email Anda">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
            </div>
            <button type="submit" class="submit-btn">Masuk</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>
</html>