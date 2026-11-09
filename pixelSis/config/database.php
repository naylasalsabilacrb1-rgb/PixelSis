<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'db_pixelsis');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/pixelsis');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// Penjaga halaman: kalau belum login, dilempar ke form login
function checkAuth() {
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Pengaman XSS
function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Status stok: [teks, warna badge]
function statusStok($stok, $min) {
    if ($stok <= 0)    return ['Habis', 'bad'];
    if ($stok <= $min) return ['Stok Menipis', 'warn'];
    return ['Tersedia', 'ok'];
}