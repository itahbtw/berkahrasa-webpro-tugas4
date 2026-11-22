<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* ============================================================
   Koneksi database PDO (MySQL / MariaDB).
   Kredensial diambil dari environment/.env melalui config.php.
   ============================================================ */

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST
        . ';port=' . DB_PORT
        . ';dbname=' . DB_NAME
        . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    } catch (PDOException $e) {
        // Detail teknis hanya masuk log, tidak ditampilkan ke pengguna.
        error_log('[DB] ' . $e->getMessage());

        http_response_code(500);

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, 'Koneksi database gagal: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

        $pesan = APP_DEBUG
            ? '<p style="color:#b91c1c">' . htmlspecialchars($e->getMessage(), ENT_QUOTES,
                'UTF-8') . '</p>'
            : '<p>Silakan hubungi pengelola situs.</p>';

        exit(
            '<!DOCTYPE html><html lang="id"><meta charset="utf-8">'
            . '<title>Koneksi database gagal</title>'
            .
                '<body style="font-family:system-ui;max-width:640px;margin:80px auto;padding:0 20px;">'
            . '<h2>Koneksi database gagal</h2>'
            . '<p>Pastikan MySQL/MariaDB berjalan dan database <strong>'
            . htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8')
            . '</strong> sudah diimpor dari <code>database/schema.sql</code>.</p>'
            . $pesan
            . '</body></html>'
        );
    }

    return $pdo;
}
