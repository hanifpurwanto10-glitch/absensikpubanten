<?php
require 'config.php';

// Pastikan user sudah login
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.html');
    exit;
}

$userId = $_SESSION['user_id'];

// Ambil data user dari DB
$stmt = $pdo->prepare('SELECT id, nip, nama_lengkap, jabatan, divisi, email, role, foto_profil FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
if (!$user) {
    // Jika user tidak ditemukan, logout
    header('Location: logout.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Profil Saya</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;padding:24px;background:#f5f7fa}
        .card{background:#fff;padding:18px;border-radius:8px;max-width:720px;margin:24px auto;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
        label{display:block;margin-top:10px;font-weight:600}
        input,select{width:100%;padding:8px;margin-top:6px;border:1px solid #d8dee7;border-radius:6px}
        button{margin-top:12px;padding:10px 14px;background:#28a745;color:#fff;border:0;border-radius:6px}
        .muted{color:#666;font-size:13px}
    </style>
</head>
<body>
    <div class="card">
        <h2>Profil Saya</h2>
        <p class="muted">Kelola informasi akun Anda. Kosongkan kata sandi jika tidak ingin mengubahnya.</p>

        <form id="profileForm" action="profile_update.php" method="POST">
            <input type="hidden" id="csrf_token" name="csrf_token" value="">
            <label>NIP</label>
            <input name="nip" value="<?php echo htmlspecialchars($user['nip']); ?>" readonly>

            <label>Nama Lengkap</label>
            <input name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>

            <label>Jabatan</label>
            <input name="jabatan" value="<?php echo htmlspecialchars($user['jabatan']); ?>">

            <label>Divisi</label>
            <input name="divisi" value="<?php echo htmlspecialchars($user['divisi']); ?>">

            <label>Email</label>
            <input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label>Kata Sandi Baru (opsional)</label>
            <input name="password" type="password" placeholder="Kosongkan jika tidak ingin mengubah">

            <button type="submit" id="submitBtn">Simpan Perubahan</button>
            <a href="dashboard.php" style="margin-left:12px">Kembali</a>
            <div id="message" style="margin-top:12px;min-height:20px"></div>
        </form>
    </div>

    <script>
    (function(){
        const form = document.getElementById('profileForm');
        const msg = document.getElementById('message');
        const btn = document.getElementById('submitBtn');

        // Ambil CSRF token
        fetch('csrf_token.php', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(j => { if (j.csrf_token) document.getElementById('csrf_token').value = j.csrf_token; })
            .catch(() => {});

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            msg.textContent = '';
            btn.disabled = true;
            const orig = btn.textContent;
            btn.textContent = 'Menyimpan...';

            const formData = new URLSearchParams(new FormData(form));

            try {
                const resp = await fetch(form.action, { method: 'POST', body: formData, credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await resp.json();
                if (resp.ok && data.success) {
                    msg.style.color = 'green'; msg.textContent = data.message || 'Perubahan tersimpan.';
                    // Update nama pada halaman jika ada
                    setTimeout(()=>{ btn.disabled = false; btn.textContent = orig; }, 800);
                } else {
                    msg.style.color = 'crimson'; msg.textContent = data.message || 'Gagal menyimpan perubahan.';
                    btn.disabled = false; btn.textContent = orig;
                }
            } catch (err) {
                console.error(err);
                msg.style.color = 'crimson'; msg.textContent = 'Kesalahan jaringan.';
                btn.disabled = false; btn.textContent = orig;
            }
        });
    })();
    </script>
</body>
</html>
