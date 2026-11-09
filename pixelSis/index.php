<?php
require __DIR__ . '/config/database.php';
$db = getDB();

$q   = trim($_GET['q'] ?? '');
$kat = (int)($_GET['kat'] ?? 0);

$sql = 'SELECT b.*, k.nama_kategori FROM barang b
        JOIN kategori k ON k.id = b.kategori_id
        WHERE (b.nama_barang LIKE :q1 OR b.kode_barang LIKE :q2)';
$params = [':q1' => "%$q%", ':q2' => "%$q%"];
if ($kat > 0) {
    $sql .= ' AND b.kategori_id = :kat';
    $params[':kat'] = $kat;
}
$sql .= ' ORDER BY b.nama_barang';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$barang = $stmt->fetchAll();

$kategori       = $db->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
$total_item     = $db->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$total_stok     = $db->query('SELECT COALESCE(SUM(stok),0) FROM barang')->fetchColumn();
$total_menipis  = $db->query('SELECT COUNT(*) FROM barang WHERE stok <= stok_min')->fetchColumn();
$total_supplier = $db->query('SELECT COUNT(*) FROM supplier')->fetchColumn();

$judul = 'Katalog Toko';
require __DIR__ . '/layout/header.php';
?>
<div class="card">
    <h2>Selamat Datang di PixelSis</h2>
    <p>Toko elektronik dan perlengkapan rumah tangga. Cek ketersediaan produk di gudang kami secara langsung.</p>
</div>

<div class="grid" style="margin-bottom:20px">
    <div class="stat"><b><?= (int)$total_item ?></b>Jenis Produk</div>
    <div class="stat"><b><?= (int)$total_stok ?></b>Total Stok</div>
    <div class="stat"><b><?= (int)$total_menipis ?></b>Stok Menipis</div>
    <div class="stat"><b><?= (int)$total_supplier ?></b>Supplier</div>
</div>

<div class="card">
    <form method="get" class="grid">
        <input type="text" name="q" placeholder="Cari nama / kode produk..." value="<?= e($q) ?>">
        <select name="kat">
            <option value="0">Semua Kategori</option>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= $kat === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit" style="margin-top:0">Cari</button>
    </form>
</div>

<div class="card tbl">
    <table>
        <tr><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th></tr>
        <?php if (!$barang): ?>
            <tr><td colspan="6" style="text-align:center;color:#999">Produk tidak ditemukan.</td></tr>
        <?php endif; ?>
        <?php foreach ($barang as $b): $s = statusStok($b['stok'], $b['stok_min']); ?>
        <tr>
            <td><?= e($b['kode_barang']) ?></td>
            <td><?= e($b['nama_barang']) ?></td>
            <td><?= e($b['nama_kategori']) ?></td>
            <td align="right">Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?></td>
            <td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
            <td><span class="badge <?= $s[1] ?>"><?= $s[0] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>