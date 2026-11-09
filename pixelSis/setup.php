<?php
require __DIR__ . '/config/database.php';
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4');
        $pdo->exec('USE `' . DB_NAME . '`');

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            role VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS kategori (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_kategori VARCHAR(20) NOT NULL UNIQUE,
            nama_kategori VARCHAR(100) NOT NULL,
            keterangan VARCHAR(255)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS supplier (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_supplier VARCHAR(20) NOT NULL UNIQUE,
            nama_supplier VARCHAR(100) NOT NULL,
            telepon VARCHAR(20),
            alamat VARCHAR(255)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS barang (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode_barang VARCHAR(30) NOT NULL UNIQUE,
            kategori_id INT NOT NULL,
            nama_barang VARCHAR(100) NOT NULL,
            satuan VARCHAR(20),
            harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
            harga_jual DECIMAL(12,2) NOT NULL DEFAULT 0,
            stok INT NOT NULL DEFAULT 0,
            stok_min INT NOT NULL DEFAULT 5,
            FOREIGN KEY (kategori_id) REFERENCES kategori(id)
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS transaksi_stok (
            id INT AUTO_INCREMENT PRIMARY KEY,
            no_transaksi VARCHAR(30) NOT NULL UNIQUE,
            barang_id INT NOT NULL,
            supplier_id INT NULL,
            jenis_transaksi ENUM('Masuk','Keluar') NOT NULL,
            jumlah INT NOT NULL,
            tanggal DATE NOT NULL,
            keterangan VARCHAR(255),
            petugas VARCHAR(100),
            FOREIGN KEY (barang_id) REFERENCES barang(id),
            FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        // Akun awal (password di-hash, bukan teks biasa)
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $st = $pdo->prepare('INSERT IGNORE INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)');
        $st->execute(['admin', $hash, 'Pemilik Toko', 'Admin']);
        $st->execute(['petugas1', $hash, 'Staf Gudang', 'Petugas']);

        // Contoh kategori
        $st = $pdo->prepare('INSERT IGNORE INTO kategori (kode_kategori, nama_kategori, keterangan) VALUES (?, ?, ?)');
        $st->execute(['ELK', 'Elektronik', 'TV, kulkas, mesin cuci, kipas angin']);
        $st->execute(['DPR', 'Peralatan Dapur', 'Rice cooker, blender, dispenser, kompor']);
        $st->execute(['LMP', 'Lampu & Penerangan', 'Lampu LED, stop kontak, kabel']);
        $st->execute(['RMT', 'Perlengkapan Rumah', 'Setrika, vacuum cleaner, penyedot debu']);

        // Contoh supplier
        $st = $pdo->prepare('INSERT IGNORE INTO supplier (kode_supplier, nama_supplier, telepon, alamat) VALUES (?, ?, ?, ?)');
        $st->execute(['SUP01', 'PT Cahaya Elektro Nusantara', '081234567890', 'Jl. Siliwangi No. 10, Cirebon']);
        $st->execute(['SUP02', 'CV Rumah Tangga Makmur', '082198765432', 'Jl. Kesambi No. 25, Cirebon']);

        // Contoh barang
        $st = $pdo->prepare('INSERT IGNORE INTO barang (kode_barang, kategori_id, nama_barang, satuan, harga_beli, harga_jual, stok, stok_min)
                             VALUES (?, (SELECT id FROM kategori WHERE kode_kategori = ?), ?, ?, ?, ?, ?, ?)');
        $st->execute(['PXL-001', 'ELK', 'Kipas Angin Berdiri 16 Inci', 'unit', 185000, 225000, 15, 5]);
        $st->execute(['PXL-002', 'ELK', 'Mesin Cuci 1 Tabung 7 Kg', 'unit', 1650000, 1899000, 4, 5]);
        $st->execute(['PXL-003', 'DPR', 'Rice Cooker 1,8 Liter', 'unit', 245000, 299000, 20, 6]);
        $st->execute(['PXL-004', 'DPR', 'Blender Kaca 2 Liter', 'unit', 195000, 249000, 0, 5]);
        $st->execute(['PXL-005', 'LMP', 'Lampu LED 12 Watt', 'pcs', 18000, 25000, 120, 30]);
        $st->execute(['PXL-006', 'RMT', 'Setrika Uap 350 Watt', 'unit', 135000, 175000, 9, 5]);

        $pesan = 'Berhasil! Database PixelSis dan 5 tabel sudah dibuat. Silakan login (admin / admin123).';
    } catch (PDOException $e) {
        $pesan = 'Gagal: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Setup PixelSis</title>
<style>
body{font-family:Segoe UI,Arial,sans-serif;background:#f3f2fb;display:flex;justify-content:center;padding:40px 15px}
.card{background:#fff;padding:25px;border-radius:8px;max-width:420px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
h3{color:#3b2fa0;margin-top:0}
button{background:#16a34a;color:#fff;border:none;padding:10px 16px;border-radius:4px;cursor:pointer}
a{color:#3b2fa0}
</style>
</head>
<body>
<div class="card">
    <h3>Setup Database PixelSis</h3>
    <?php if ($pesan): ?><p><?= e($pesan) ?></p><?php endif; ?>
    <form method="post"><button type="submit">Inisialisasi Database Sekarang</button></form>
    <p><a href="auth/login.php">Ke halaman login</a></p>
</div>
</body>
</html>