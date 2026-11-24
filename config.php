<?php
// ============================================================
// Konfigurasi koneksi database
// Default: MySQL (XAMPP/Laragon). Ubah sesuai pengaturan Anda.
// ============================================================
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql'); // 'mysql' atau 'sqlite'
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_alamat_gps');
define('DB_USER', 'root');
define('DB_PASS', '');

function db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        if (DB_DRIVER === 'sqlite') {
            // Mode alternatif tanpa MySQL (otomatis membuat tabel)
            $pdo = new PDO('sqlite:' . __DIR__ . '/data.sqlite');
            $pdo->exec("CREATE TABLE IF NOT EXISTS rumah (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_pemilik TEXT NOT NULL,
                alamat TEXT NOT NULL,
                latitude REAL NOT NULL,
                longitude REAL NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
    }
    return $pdo;
}

function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
