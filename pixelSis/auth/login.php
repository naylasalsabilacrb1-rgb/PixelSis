<?php
require __DIR__ . '/../config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDB()->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'nama' => $user['nama_lengkap'], 'role' => $user['role']];
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    $error = 'Username atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Petugas - PixelSis</title>
<style>
body{font-family:Segoe UI,Arial,sans-serif;background:#f3f2fb;display:flex;justify-content:center;padding:60px 15px}
.card{background:#fff;padding:25px;border-radius:8px;width:100%;max-width:360px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
h3{color:#3b2fa0;margin-top:0}
label{display:block;font-weight:bold;font-size:13px;margin:10px 0 4px}
input{width:100%;padding:9px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box}
button{width:100%;margin-top:15px;background:#3b2fa0;color:#fff;border:none;padding:10px;border-radius:4px;cursor:pointer}
.err{background:#f8d7da;color:#721c24;padding:8px;border-radius:4px;margin-bottom:10px;font-size:14px}
a{color:#3b2fa0}
</style>
</head>
<body>
<div class="card">
    <h3>Login Petugas PixelSis</h3>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Masuk ke Sistem</button>
    </form>
    <p style="font-size:13px"><a href="<?= BASE_URL ?>/index.php">← Kembali ke Katalog Toko</a></p>
</div>
</body>
</html>