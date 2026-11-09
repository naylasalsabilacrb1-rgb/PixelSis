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
            $id     = (int)($_POST['id'] ?? 0);
            $kode   = strtoupper(trim($_POST['kode_supplier'] ?? ''));
            $nama   = trim($_POST['nama_supplier'] ?? '');
            $telp   = trim($_POST['telepon'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            if ($kode === '' || $nama === '') throw new Exception('Kode dan nama supplier wajib diisi.');
            if ($telp !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $telp)) {
                throw new Exception('Format nomor telepon tidak valid (8-20 digit angka).');
            }

            if ($id > 0) {
                $st = $db->prepare('UPDATE supplier SET kode_supplier=:kode, nama_supplier=:nama, telepon=:telp, alamat=:alamat WHERE id=:id');
                $st->execute([':kode' => $kode, ':nama' => $nama, ':telp' => $telp, ':alamat' => $alamat, ':id' => $id]);
            } else {
                $st = $db->prepare('INSERT INTO supplier (kode_supplier, nama_supplier, telepon, alamat) VALUES (:kode, :nama, :telp, :alamat)');
                $st->execute([':kode' => $kode, ':nama' => $nama, ':telp' => $telp, ':alamat' => $alamat]);
            }
            $pesan = 'Data supplier berhasil disimpan.';

        } elseif ($aksi === 'hapus') {
            $st = $db->prepare('DELETE FROM supplier WHERE id = :id');
            $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
            $pesan = 'Supplier berhasil dihapus.';
        }
    } catch (PDOException $e) {
        $pesan = (($e->errorInfo[1] ?? 0) == 1062) ? 'Kode supplier sudah dipakai.' : 'Terjadi kesalahan database.';
    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $db->prepare('SELECT * FROM supplier WHERE id = :id');
    $st->execute([':id' => (int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$rows = $db->query("SELECT s.*, (SELECT COUNT(*) FROM transaksi_stok t WHERE t.supplier_id = s.id AND t.jenis_transaksi = 'Masuk') AS total_pasok
                    FROM supplier s ORDER BY s.id DESC")->fetchAll();

$judul = 'Supplier';
require __DIR__ . '/../layout/header.php';
?>
<?php if ($pesan): ?><div class="pesan"><?= e($pesan) ?></div><?php endif; ?>

<div class="card">
    <h3><?= $edit ? 'Edit Supplier' : 'Tambah Supplier' ?></h3>
    <form method="post" action="supplier.php">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        <label>Kode Supplier</label>
        <input type="text" name="kode_supplier" value="<?= e($edit['kode_supplier'] ?? '') ?>" placeholder="Contoh: SUP03" required>
        <label>Nama Supplier</label>
        <input type="text" name="nama_supplier" value="<?= e($edit['nama_supplier'] ?? '') ?>" placeholder="Contoh: PT Sinar Elektronik" required>
        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= e($edit['telepon'] ?? '') ?>" placeholder="Contoh: 081234567890">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= e($edit['alamat'] ?? '') ?>">
        <button class="btn" type="submit">Simpan</button>
        <?php if ($edit): ?><a class="btn gray" href="supplier.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card tbl">
    <h3>Daftar Supplier</h3>
    <table>
        <tr><th>No</th><th>Kode</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Total Pasokan</th><th>Aksi</th></tr>
        <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;color:#999">Belum ada data.</td></tr><?php endif; ?>
        <?php $no = 1; foreach ($rows as $r): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><b><?= e($r['kode_supplier']) ?></b></td>
            <td><?= e($r['nama_supplier']) ?></td>
            <td><?= e($r['telepon']) ?></td>
            <td><?= e($r['alamat']) ?></td>
            <td><?= (int)$r['total_pasok'] ?>x</td>
            <td>
                <a class="btn orange" href="supplier.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" class="inline" onsubmit="return confirm('Yakin hapus supplier ini?')">
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