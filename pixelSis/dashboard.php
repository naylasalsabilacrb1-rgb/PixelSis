<?php
require __DIR__ . '/config/database.php';
checkAuth();
$db = getDB();

$total_barang   = $db->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$total_kategori = $db->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$total_supplier = $db->query('SELECT COUNT(*) FROM supplier')->fetchColumn();
$total_trx      = $db->query('SELECT COUNT(*) FROM transaksi_stok')->fetchColumn();

$menipis  = $db->query('SELECT * FROM barang WHERE stok <= stok_min ORDER BY stok ASC LIMIT 5')->fetchAll();
$terakhir = $db->query('SELECT t.*, b.nama_barang FROM transaksi_stok t
                        JOIN barang b ON b.id = t.barang_id ORDER BY t.id DESC LIMIT 5')->fetchAll();

$judul = 'Dashboard';
require __DIR__ . '/layout/header.php';
?>
<h2>Halo, <?= e($_SESSION['user']['nama']) ?></h2>

<div class="grid" style="margin-bottom:20px">
    <div class="stat"><b><?= (int)$total_barang ?></b>Produk</div>
    <div class="stat"><b><?= (int)$total_kategori ?></b>Kategori</div>
    <div class="stat"><b><?= (int)$total_supplier ?></b>Supplier</div>
    <div class="stat"><b><?= (int)$total_trx ?></b>Transaksi</div>
</div>

<div class="card tbl">
    <h3>Produk Stok Menipis</h3>
    <table>
        <tr><th>Kode</th><th>Nama</th><th>Stok</th><th>Batas Min</th></tr>
        <?php if (!$menipis): ?><tr><td colspan="4" style="text-align:center;color:#999">Tidak ada produk yang stoknya menipis.</td></tr><?php endif; ?>
        <?php foreach ($menipis as $b): ?>
        <tr><td><?= e($b['kode_barang']) ?></td><td><?= e($b['nama_barang']) ?></td><td><?= (int)$b['stok'] ?></td><td><?= (int)$b['stok_min'] ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card tbl">
    <h3>5 Mutasi Stok Terakhir</h3>
    <table>
        <tr><th>No Transaksi</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Tanggal</th></tr>
        <?php if (!$terakhir): ?><tr><td colspan="5" style="text-align:center;color:#999">Belum ada transaksi.</td></tr><?php endif; ?>
        <?php foreach ($terakhir as $t): ?>
        <tr>
            <td><?= e($t['no_transaksi']) ?></td>
            <td><?= e($t['nama_barang']) ?></td>
            <td><span class="badge <?= $t['jenis_transaksi'] === 'Masuk' ? 'ok' : 'bad' ?>"><?= e($t['jenis_transaksi']) ?></span></td>
            <td><?= (int)$t['jumlah'] ?></td>
            <td><?= e(date('d-m-Y', strtotime($t['tanggal']))) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>