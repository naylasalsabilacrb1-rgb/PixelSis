<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'PixelSis') ?> - PixelSis</title>
<style>
:root{--utama:#3b2fa0;--utama2:#5146c4}
*{box-sizing:border-box}
body{margin:0;font-family:Segoe UI,Tahoma,Arial,sans-serif;background:#f3f2fb;color:#333}
nav{background:var(--utama);padding:10px 20px;display:flex;flex-wrap:wrap;gap:6px;align-items:center}
nav a{color:#fff;text-decoration:none;padding:6px 10px;border-radius:4px;font-size:14px}
nav a:hover{background:var(--utama2)}
nav .sp{flex:1}
nav .logo{color:#fff;font-weight:bold;font-size:18px;margin-right:10px}
.wrap{max-width:1000px;margin:20px auto;padding:0 15px}
.card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.08);margin-bottom:20px}
h2,h3{margin-top:0;color:var(--utama)}
label{display:block;font-weight:bold;font-size:13px;margin:10px 0 4px}
input,select,textarea{width:100%;padding:9px;border:1px solid #ccc;border-radius:4px;font-size:14px}
.btn{display:inline-block;padding:8px 14px;border:none;border-radius:4px;background:var(--utama);color:#fff;cursor:pointer;text-decoration:none;font-size:13px;margin-top:10px}
.btn.orange{background:#f39c12;margin-top:0}
.btn.red{background:#e74c3c;margin-top:0}
.btn.gray{background:#7f8c8d}
.tbl{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{border:1px solid #e0e0e0;padding:8px 10px;text-align:left}
th{background:var(--utama);color:#fff}
tr:nth-child(even){background:#f9f8fe}
.badge{padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold}
.ok{background:#d4edda;color:#155724}
.warn{background:#fff3cd;color:#856404}
.bad{background:#f8d7da;color:#721c24}
.pesan{background:#ece9fb;border-left:4px solid var(--utama);padding:10px;margin-bottom:15px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px}
.stat{background:#fff;padding:18px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.08);text-align:center}
.stat b{display:block;font-size:28px;color:var(--utama)}
form.inline{display:inline}
</style>
</head>
<body>
<nav>
    <span class="logo">PixelSis</span>
    <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/modul/kategori.php">Kategori</a>
        <a href="<?= BASE_URL ?>/modul/supplier.php">Supplier</a>
        <a href="<?= BASE_URL ?>/modul/barang.php">Barang</a>
        <a href="<?= BASE_URL ?>/modul/transaksi.php">Mutasi Stok</a>
        <span class="sp"></span>
        <a href="<?= BASE_URL ?>/index.php">Katalog Toko</a>
        <a href="<?= BASE_URL ?>/auth/logout.php">Keluar (<?= e($_SESSION['user']['nama']) ?>)</a>
    <?php else: ?>
        <span class="sp"></span>
        <a href="<?= BASE_URL ?>/auth/login.php">Portal Petugas</a>
    <?php endif; ?>
</nav>
<div class="wrap">