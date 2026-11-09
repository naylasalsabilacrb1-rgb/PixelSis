<?php
require __DIR__ . '/../config/database.php';
checkAuth();
$db = getDB();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'simpan') {
            $barangId = (int)($_POST['barang_id'] ?? 0);
            $supId    = (int)($_POST['supplier_id'] ?? 0);
            $jenis    = $_POST['jenis'] ?? '';
            $jumlah   = (int)($_POST['jumlah'] ?? 0);
            $tanggal  = $_POST['tanggal'] ?? '';
            $ket      = trim($_POST['keterangan'] ?? '');

            if ($barangId <= 0 || !in_array($jenis, ['Masuk', 'Keluar'], true) || $jumlah <= 0 || $tanggal === '') {
                throw new Exception('Data belum lengkap atau tidak valid (jumlah harus lebih dari 0).');
            }

            $db->beginTransaction();   // mulai: semua langkah harus sukses bareng
            try {
                $st = $db->prepare('SELECT stok FROM barang WHERE id = :id FOR UPDATE');
                $st->execute([':id' => $barangId]);
                $stok = $st->fetchColumn();
                if ($stok === false) throw new Exception('Produk tidak ditemukan.');
                if ($jenis === 'Keluar' && (int)$stok < $jumlah) {
                    throw new Exception('Stok tidak mencukupi untuk dikeluarkan! Sisa stok: ' . (int)$stok);
                }

                $noTrx = 'TRX-' . date('ymdHis') . rand(10, 99);
                $st = $db->prepare('INSERT INTO transaksi_stok (no_transaksi, barang_id, supplier_id, jenis_transaksi, jumlah, tanggal, keterangan, petugas)
                                    VALUES (:no, :bid, :sid, :jenis, :jml, :tgl, :ket, :ptg)');
                $st->execute([
                    ':no' => $noTrx, ':bid' => $barangId, ':sid' => $supId > 0 ? $supId : null,
                    ':jenis' => $jenis, ':jml' => $jumlah, ':tgl' => $tanggal, ':ket' => $ket,
                    ':ptg' => $_SESSION['user']['nama'],
                ]);

                $sqlUpdate = ($jenis === 'Masuk')
                    ? 'UPDATE barang SET stok = stok + :jml WHERE id = :id'
                    : 'UPDATE barang SET stok = stok - :jml WHERE id = :id';
                $st = $db->prepare($sqlUpdate);
                $st->execute([':jml' => $jumlah, ':id' => $barangId]);

                $db->commit();         // sukses: simpan permanen
            } catch (Throwable $e) {
                $db->rollBack();       // gagal: batalkan semuanya
                throw $e;
            }
            $pesan = 'Mutasi stok berhasil dicatat. Stok produk sudah diperbarui.';

        } elseif ($aksi === 'batal') {
            $id = (int)($_POST['id'] ?? 0);
            $db->beginTransaction();
            try {
                $st = $db->prepare('SELECT * FROM transaksi_stok WHERE id = :id FOR UPDATE');
                $st->execute([':id' => $id]);
                $t = $st->fetch();
                if (!$t) throw new Exception('Transaksi tidak ditemukan.');

                $st = $db->prepare('SELECT stok FROM barang WHERE id = :id FOR UPDATE');
                $st->execute([':id' => $t['barang_id']]);
                $stok = (int)$st->fetchColumn();

                if ($t['jenis_transaksi'] === 'Masuk') {
                    if ($stok < (int)$t['jumlah']) throw new Exception('Tidak bisa dibatalkan: stok sekarang lebih kecil dari jumlah barang masuk.');
                    $sqlUpdate = 'UPDATE barang SET stok = stok - :jml WHERE id = :id';
                } else {
                    $sqlUpdate = 'UPDATE barang SET stok = stok + :jml WHERE id = :id';
                }
                $st = $db->prepare($sqlUpdate);
                $st->execute([':jml' => $t['jumlah'], ':id' => $t['barang_id']]);

                $st = $db->prepare('DELETE FROM transaksi_stok WHERE id = :id');
                $st->execute([':id' => $id]);

                $db->commit();
            } catch (Throwable $e) {
                $db->rollBack();
                throw $e;
            }
            $pesan = 'Transaksi dibatalkan dan stok dikembalikan.';
        }
    } catch (PDOException $e) {
        $pesan = 'Terjadi kesalahan database.';
    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}

$barang   = $db->query('SELECT id, kode_barang, nama_barang, stok, satuan FROM barang ORDER BY nama_barang')->fetchAll();
$supplier = $db->query('SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier')->fetchAll();
$rows = $db->query('SELECT t.*, b.nama_barang, s.nama_supplier FROM transaksi_stok t
                    JOIN barang b ON b.id = t.barang_id
                    LEFT JOIN supplier s ON s.id = t.supplier_id
                    ORDER BY t.id DESC LIMIT 50')->fetchAll();

$judul = 'Mutasi Stok';
require __DIR__ . '/../layout/header.php';
?>
<?php if ($pesan): ?><div class="pesan"><?= e($pesan) ?></div><?php endif; ?>

<div class="card">
    <h3>Catat Mutasi Stok</h3>
    <?php if (!$barang): ?>
        <p>Belum ada produk. <a href="barang.php">Tambah produk dulu</a>.</p>
    <?php else: ?>
    <form method="post" action="transaksi.php">
        <input type="hidden" name="aksi" value="simpan">
        <label>Produk</label>
        <select name="barang_id" required>
            <option value="">-- Pilih Produk --</option>
            <?php foreach ($barang as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= e($b['kode_barang']) ?> - <?= e($b['nama_barang']) ?> (stok: <?= (int)$b['stok'] ?> <?= e($b['satuan']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <label>Jenis Transaksi</label>
        <select name="jenis" required>
            <option value="Masuk">Barang Masuk (+)</option>
            <option value="Keluar">Barang Keluar / Terjual (-)</option>
        </select>
        <label>Supplier (isi kalau barang masuk)</label>
        <select name="supplier_id">
            <option value="0">-- Tanpa Supplier --</option>
            <?php foreach ($supplier as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= e($s['nama_supplier']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Jumlah</label>
        <input type="number" name="jumlah" min="1" required>
        <label>Tanggal</label>
        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        <label>Keterangan</label>
        <input type="text" name="keterangan" placeholder="Contoh: Restock bulanan / Penjualan toko">
        <button class="btn" type="submit">Simpan Mutasi</button>
    </form>
    <?php endif; ?>
</div>

<div class="card tbl">
    <h3>Histori Mutasi Stok (50 terakhir)</h3>
    <table>
        <tr><th>No Transaksi</th><th>Tanggal</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Supplier</th><th>Petugas</th><th>Aksi</th></tr>
        <?php if (!$rows): ?><tr><td colspan="8" style="text-align:center;color:#999">Belum ada transaksi.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['no_transaksi']) ?></td>
            <td><?= e(date('d-m-Y', strtotime($r['tanggal']))) ?></td>
            <td><?= e($r['nama_barang']) ?></td>
            <td><span class="badge <?= $r['jenis_transaksi'] === 'Masuk' ? 'ok' : 'bad' ?>"><?= e($r['jenis_transaksi']) ?></span></td>
            <td><?= (int)$r['jumlah'] ?></td>
            <td><?= e($r['nama_supplier'] ?? '-') ?></td>
            <td><?= e($r['petugas']) ?></td>
            <td>
                <form method="post" class="inline" onsubmit="return confirm('Batalkan transaksi ini? Stok akan dikembalikan.')">
                    <input type="hidden" name="aksi" value="batal">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn red" type="submit">Batalkan</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>