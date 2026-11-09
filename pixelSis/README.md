# PixelSis

Sistem informasi inventaris gudang untuk toko elektronik dan perlengkapan rumah tangga. PixelSis membantu petugas gudang mencatat kategori, supplier, produk, serta keluar masuknya stok, sementara pengunjung bisa mengecek ketersediaan produk lewat katalog publik.

Proyek ini dibuat untuk mata kuliah **Pemrograman Web Dasar** (Program Studi D3 Komputerisasi Akuntansi, STMIK IKMI Cirebon, Semester Ganjil 2026/2027, dosen pengampu Rosidin, S.Kom., M.Kom.).

## Fitur

**Halaman publik (tanpa login)**
- Katalog produk dengan pencarian nama/kode dan filter kategori
- Ringkasan: jumlah jenis produk, total stok, jumlah stok menipis, jumlah supplier
- Badge status stok otomatis: Tersedia, Stok Menipis, Habis

**Portal petugas (wajib login)**
- Dashboard berisi ringkasan data, daftar produk stok menipis, dan 5 mutasi stok terakhir
- Empat modul CRUD:

| Modul | Halaman | Fitur utama |
|---|---|---|
| 1. Kategori | `modul/kategori.php` | Kode kategori otomatis huruf besar dan unik, total produk per kategori, kategori yang masih dipakai produk tidak bisa dihapus |
| 2. Barang | `modul/barang.php` | Pilih kategori lewat dropdown, filter per kategori, status stok kritis, stok hanya berubah lewat Mutasi Stok |
| 3. Supplier | `modul/supplier.php` | Validasi format nomor telepon, kolom total frekuensi pasokan dihitung otomatis dari riwayat barang masuk |
| 4. Mutasi Stok | `modul/transaksi.php` | Catat barang masuk/keluar, stok diperbarui otomatis, anti stok minus, pembatalan transaksi dengan pengembalian stok, memakai transaksi database (ACID) |

## Teknologi

- PHP native (disarankan PHP 7.4 atau lebih baru)
- MySQL / MariaDB dengan mesin InnoDB
- PDO (Prepared Statements)
- HTML5 dan CSS3 (tanpa framework), JavaScript sederhana (`confirm()` sebelum hapus)
- Server lokal: XAMPP

## Struktur Folder

```
pixelsis/
├── config/
│   └── database.php     # Koneksi PDO, checkAuth(), e(), statusStok()
├── auth/
│   ├── login.php        # Form login, password_verify()
│   └── logout.php       # Menghapus sesi
├── layout/
│   ├── header.php       # Menu atas dan CSS bersama
│   └── footer.php       # Penutup halaman
├── modul/
│   ├── kategori.php     # Modul 1
│   ├── barang.php       # Modul 2
│   ├── supplier.php     # Modul 3
│   └── transaksi.php    # Modul 4
├── index.php            # Katalog publik
├── dashboard.php        # Dashboard petugas
└── setup.php            # Pembuat database, tabel, dan data contoh
```

## Skema Database

Database `db_pixelsis` terdiri dari 5 tabel:

| Tabel | Fungsi | Kolom kunci |
|---|---|---|
| `users` | Akun petugas dan admin | id, username (unik), password (hash), role |
| `kategori` | Pengelompokan produk | id, kode_kategori (unik), nama_kategori |
| `supplier` | Data pemasok | id, kode_supplier (unik), nama_supplier, telepon, alamat |
| `barang` | Data produk dan stok | id, kode_barang (unik), kategori_id (FK), harga_beli, harga_jual, stok, stok_min |
| `transaksi_stok` | Riwayat barang masuk dan keluar | id, no_transaksi (unik), barang_id (FK), supplier_id (FK, boleh kosong), jenis_transaksi, jumlah, tanggal |

Relasi:
- `kategori` 1 : N `barang`
- `barang` 1 : N `transaksi_stok`
- `supplier` 1 : N `transaksi_stok` (`ON DELETE SET NULL`, riwayat tetap ada jika supplier dihapus)

## Cara Menjalankan

1. Pasang [XAMPP](https://www.apachefriends.org/) lalu jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
2. Salin folder proyek ke `C:\xampp\htdocs\` dengan nama folder **`pixelsis`**.
3. Buka `http://localhost/pixelsis/setup.php`, lalu klik **Inisialisasi Database Sekarang**. Langkah ini membuat database `db_pixelsis`, 5 tabel, akun login, dan data contoh.
4. Buka `http://localhost/pixelsis/` untuk melihat katalog, atau `http://localhost/pixelsis/auth/login.php` untuk masuk sebagai petugas.

Jika pengaturan berbeda (nama folder, port, atau password MySQL), ubah di `config/database.php`:

```php
define('DB_PASS', '');                          // password MySQL
define('BASE_URL', 'http://localhost/pixelsis'); // harus sama dengan nama folder
```

## Akun Contoh

| Username | Password | Peran |
|---|---|---|
| `admin` | `admin123` | Admin |
| `petugas1` | `admin123` | Petugas |

> Akun ini hanya untuk keperluan belajar dan demo. Ganti passwordnya jika aplikasi dipakai di luar lingkungan lokal.

## Keamanan

- **SQL Injection:** semua query memakai PDO Prepared Statements, dengan `EMULATE_PREPARES` dimatikan.
- **XSS:** seluruh teks dinamis ditampilkan lewat fungsi `e()` (pembungkus `htmlspecialchars`).
- **Sesi dan akses:** halaman petugas dijaga `checkAuth()`, ID sesi diperbarui setelah login (`session_regenerate_id`), dan sesi dihapus saat logout.
- **Password:** disimpan dengan `password_hash()` dan diperiksa dengan `password_verify()`.
- **Validasi server:** semua input divalidasi ulang di sisi server, tidak hanya di form.
- **Konfirmasi hapus:** setiap penghapusan diawali dialog `confirm()` dan dikirim lewat POST.

## Pembagian Tim

| Anggota | NIM | Modul |
|---|---|---|
| Dian | 32251700 | Modul 1: Kategori |
| Nayla | 32251705 | Modul 2: Barang |
| Reva | 32251708 | Modul 3: Supplier |
| Sapna | 32251709 | Modul 4: Mutasi Stok |

## Pengembangan Selanjutnya

- Token CSRF pada setiap form
- Hak akses berbeda untuk Admin dan Petugas
- Stok awal saat tambah barang dicatat sebagai transaksi masuk pertama
- Laporan stok yang bisa dicetak dan notifikasi stok menipis