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
            $kode = strtoupper(trim($_POST['kode_kategori'] ?? ''));
            $nama = trim($_POST['nama_kategori'] ?? '');
            $ket  = trim($_POST['keterangan'] ?? '');
            if ($kode === '' || $nama === '') throw new Exception('Kode dan nama kategori wajib diisi.');

            if ($id > 0) {
                $st = $db->prepare('UPDATE kategori SET kode_kategori=:kode, nama_kategori=:nama, keterangan=:ket WHERE id=:id');
                $st->execute([':kode' => $kode, ':nama' => $nama, ':ket' => $ket, ':id' => $id]);
            } else {
                $st = $db->prepare('INSERT INTO kategori (kode_kategori, nama_kategori, keterangan) VALUES (:kode, :nama, :ket)');
                $st->execute([':kode' => $kode, ':nama' => $nama, ':ket' => $ket]);
            }
            $pesan = 'Data kategori berhasil disimpan.';

        } elseif ($aksi === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $db->prepare('SELECT COUNT(*) FROM barang WHERE kategori_id = :id');
            $st->execute([':id' => $id]);
            if ($st->fetchColumn() > 0) throw new Exception('Kategori masih dipakai produk, tidak bisa dihapus.');

            $st = $db->prepare('DELETE FROM kategori WHERE id = :id');
            $st->execute([':id' => $id]);
            $pesan = 'Kategori berhasil dihapus.';
        }
    } catch (PDOException $e) {
        $pesan = (($e->errorInfo[1] ?? 0) == 1062) ? 'Kode kategori sudah dipakai.' : 'Terjadi kesalahan database.';
    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $db->prepare('SELECT * FROM kategori WHERE id = :id');
    $st->execute([':id' => (int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$rows = $db->query('SELECT k.*, (SELECT COUNT(*) FROM barang b WHERE b.kategori_id = k.id) AS total
                    FROM kategori k ORDER BY k.id DESC')->fetchAll();

$judul = 'Kategori Produk';
require __DIR__ . '/../layout/header.php';
?>
<?php if ($pesan): ?><div class="pesan"><?= e($pesan) ?></div><?php endif; ?>

<div class="card">
    <h3><?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h3>
    <form method="post" action="kategori.php">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        <label>Kode Kategori (huruf besar, unik)</label>
        <input type="text" name="kode_kategori" value="<?= e($edit['kode_kategori'] ?? '') ?>" placeholder="Contoh: ELK" required>
        <label>Nama Kategori</label>
        <input type="text" name="nama_kategori" value="<?= e($edit['nama_kategori'] ?? '') ?>" placeholder="Contoh: Elektronik" required>
        <label>Keterangan</label>
        <input type="text" name="keterangan" value="<?= e($edit['keterangan'] ?? '') ?>" placeholder="Contoh: TV, kulkas, mesin cuci">
        <button class="btn" type="submit">Simpan</button>
        <?php if ($edit): ?><a class="btn gray" href="kategori.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card tbl">
    <h3>Daftar Kategori</h3>
    <table>
        <tr><th>No</th><th>Kode</th><th>Nama</th><th>Keterangan</th><th>Total Produk</th><th>Aksi</th></tr>
        <?php if (!$rows): ?><tr><td colspan="6" style="text-align:center;color:#999">Belum ada data.</td></tr><?php endif; ?>
        <?php $no = 1; foreach ($rows as $r): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><b><?= e($r['kode_kategori']) ?></b></td>
            <td><?= e($r['nama_kategori']) ?></td>
            <td><?= e($r['keterangan']) ?></td>
            <td><?= (int)$r['total'] ?></td>
            <td>
                <a class="btn orange" href="kategori.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" class="inline" onsubmit="return confirm('Yakin hapus kategori ini?')">
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