<?php
require __DIR__ . '/../config/database.php';
checkAuth();
$db = getDB();
$pesan = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'simpan') {
            $id   = (int)($_POST['id'] ?? 0);
            $kode = strtoupper(trim($_POST['kode_barang'] ?? ''));
            $kat  = (int)($_POST['kategori_id'] ?? 0);
            $nama = trim($_POST['nama_barang'] ?? '');
            $sat  = trim($_POST['satuan'] ?? '');
            $hb   = (float)($_POST['harga_beli'] ?? 0);
            $hj   = (float)($_POST['harga_jual'] ?? 0);
            $stok = (int)($_POST['stok'] ?? 0);
            $min  = (int)($_POST['stok_min'] ?? 0);

            if ($kode === '' || $nama === '' || $kat <= 0) throw new Exception('Kode, nama, dan kategori wajib diisi.');
            if ($hb < 0 || $hj < 0 || $stok < 0 || $min < 0) throw new Exception('Angka tidak boleh negatif.');

            if ($id > 0) {
                // stok tidak diubah di sini, stok berubah lewat Mutasi Stok
                $st = $db->prepare('UPDATE barang SET kode_barang=:kode, kategori_id=:kat, nama_barang=:nama,
                                    satuan=:sat, harga_beli=:hb, harga_jual=:hj, stok_min=:min WHERE id=:id');
                $st->execute([':kode' => $kode, ':kat' => $kat, ':nama' => $nama, ':sat' => $sat,
                              ':hb' => $hb, ':hj' => $hj, ':min' => $min, ':id' => $id]);
            } else {
                $st = $db->prepare('INSERT INTO barang (kode_barang, kategori_id, nama_barang, satuan, harga_beli, harga_jual, stok, stok_min)
                                    VALUES (:kode, :kat, :nama, :sat, :hb, :hj, :stok, :min)');
                $st->execute([':kode' => $kode, ':kat' => $kat, ':nama' => $nama, ':sat' => $sat,
                              ':hb' => $hb, ':hj' => $hj, ':stok' => $stok, ':min' => $min]);
            }
            $pesan = 'Data produk berhasil disimpan.';

        } elseif ($aksi === 'hapus') {
            $st = $db->prepare('DELETE FROM barang WHERE id = :id');
            $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
            $pesan = 'Produk berhasil dihapus.';
        }
    } catch (PDOException $e) {
        $kodeErr = $e->errorInfo[1] ?? 0;
        if ($kodeErr == 1062)     $pesan = 'Kode produk sudah dipakai.';
        elseif ($kodeErr == 1451) $pesan = 'Produk tidak bisa dihapus karena sudah punya riwayat transaksi.';
        else                      $pesan = 'Terjadi kesalahan database.';
    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $db->prepare('SELECT * FROM barang WHERE id = :id');
    $st->execute([':id' => (int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$kategori = $db->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();

$filter = (int)($_GET['kat'] ?? 0);
$sql = 'SELECT b.*, k.nama_kategori FROM barang b JOIN kategori k ON k.id = b.kategori_id';
$params = [];
if ($filter > 0) {
    $sql .= ' WHERE b.kategori_id = :kat';
    $params[':kat'] = $filter;
}
$sql .= ' ORDER BY b.id DESC';
$st = $db->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$judul = 'Data Produk';
require __DIR__ . '/../layout/header.php';
?>
<?php if ($pesan): ?><div class="pesan"><?= e($pesan) ?></div><?php endif; ?>

<div class="card">
    <h3><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></h3>
    <?php if (!$kategori): ?>
        <p>Belum ada kategori. <a href="kategori.php">Tambah kategori dulu</a>.</p>
    <?php else: ?>
    <form method="post" action="barang.php">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        <label>Kode Produk</label>
        <input type="text" name="kode_barang" value="<?= e($edit['kode_barang'] ?? '') ?>" placeholder="Contoh: PXL-007" required>
        <label>Kategori</label>
        <select name="kategori_id" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= (int)($edit['kategori_id'] ?? 0) === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Nama Produk</label>
        <input type="text" name="nama_barang" value="<?= e($edit['nama_barang'] ?? '') ?>" placeholder="Contoh: Dispenser Air Galon" required>
        <label>Satuan</label>
        <input type="text" name="satuan" value="<?= e($edit['satuan'] ?? '') ?>" placeholder="unit / pcs / box">
        <label>Harga Beli (Rp)</label>
        <input type="number" name="harga_beli" min="0" step="100" value="<?= e($edit['harga_beli'] ?? 0) ?>">
        <label>Harga Jual (Rp)</label>
        <input type="number" name="harga_jual" min="0" step="100" value="<?= e($edit['harga_jual'] ?? 0) ?>">
        <?php if (!$edit): ?>
            <label>Stok Awal</label>
            <input type="number" name="stok" min="0" value="0">
        <?php endif; ?>
        <label>Stok Minimum (batas stok menipis)</label>
        <input type="number" name="stok_min" min="0" value="<?= e($edit['stok_min'] ?? 5) ?>">
        <button class="btn" type="submit">Simpan</button>
        <?php if ($edit): ?><a class="btn gray" href="barang.php">Batal</a><?php endif; ?>
    </form>
    <?php endif; ?>
</div>

<div class="card tbl">
    <h3>Daftar Produk</h3>
    <form method="get" style="margin-bottom:10px">
        <select name="kat" onchange="this.form.submit()">
            <option value="0">Semua Kategori</option>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= $filter === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <table>
        <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
        <?php if (!$rows): ?><tr><td colspan="8" style="text-align:center;color:#999">Belum ada data.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): $s = statusStok($r['stok'], $r['stok_min']); ?>
        <tr>
            <td><b><?= e($r['kode_barang']) ?></b></td>
            <td><?= e($r['nama_barang']) ?></td>
            <td><?= e($r['nama_kategori']) ?></td>
            <td align="right">Rp <?= number_format($r['harga_beli'], 0, ',', '.') ?></td>
            <td align="right">Rp <?= number_format($r['harga_jual'], 0, ',', '.') ?></td>
            <td><?= (int)$r['stok'] ?> <?= e($r['satuan']) ?></td>
            <td><span class="badge <?= $s[1] ?>"><?= $s[0] ?></span></td>
            <td>
                <a class="btn orange" href="barang.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" class="inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn red" type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>