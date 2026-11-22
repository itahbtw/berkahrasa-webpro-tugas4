<?php
declare(strict_types=1);

/* ============================================================
   Konfigurasi aplikasi Berkah Rasa (Tugas 4 / UAS WebPro).
   Nilai sensitif dibaca dari environment atau file .env.
   Salin .env.example menjadi .env lalu sesuaikan.
   ============================================================ */

define('APP_ROOT', dirname(__DIR__));

/**
 * Pembaca .env sederhana tanpa dependensi Composer.
 * Nilai yang sudah ada di environment tidak ditimpa.
 */
function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

load_env_file(APP_ROOT . '/.env');

function env(string $key, ?string $default = null): ?string
{
    // Beberapa shared hosting menonaktifkan putenv(); .env tetap tersimpan di $_ENV.
    $value = getenv($key);
    if ($value === false || $value === '') {
        $value = $_ENV[$key] ?? null;
    }

    return ($value === false || $value === null || $value === '') ? $default : (string) $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env($key);
    if ($value === null) {
        return $default;
    }

    return in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
}

function is_https(): bool
{
    if (env_bool('APP_HTTPS', false)) {
        return true;
    }

    return (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

/* ---------- identitas & lingkungan ---------- */

define('APP_NAME', env('APP_NAME', 'Berkah Rasa'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env_bool('APP_DEBUG', false));
define('APP_VERSION', '4.7.5');

/* Ongkir: gratis untuk subtotal minimal Rp100.000, selain itu Rp10.000. */
define('ONGKIR_THRESHOLD', 100000);
define('ONGKIR_FEE', 10000);

/**
 * Versi aset statis (CSS/JS/gambar). Naikkan nilai ini setelah mengubah aset
 * agar pengunjung tidak mendapatkan salinan lama dari browser cache.
 */
define('ASSET_VERSION', '4.7.5');

/* ---------- database ---------- */

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'berkahrasa'));
define('DB_USER', env('DB_USER', 'berkah'));
define('DB_PASS', env('DB_PASS', 'berkah123'));

/* ---------- aplikasi ---------- */

define('WA_NUMBER', env('WA_NUMBER', '6281234567890'));

// Rekening sementara untuk metode Transfer Bank (ganti dengan rekening asli nanti).
define('BCA_ACCOUNT', env('BCA_ACCOUNT', '1234567890'));
define('BCA_HOLDER', env('BCA_HOLDER', 'Berkah Rasa'));

define('SESSION_NAME', 'BERKAHRASASESSID');
define('SESSION_IDLE_TIMEOUT', 1800);          // 30 menit tanpa aktivitas
define('SESSION_ABSOLUTE_TIMEOUT', 28800);     // 8 jam sejak login
define('SESSION_REGENERATE_INTERVAL', 900);    // ganti ID session tiap 15 menit

define('REMEMBER_COOKIE', 'berkah_remember');
define('REMEMBER_DAYS', 30);
define('THEME_COOKIE', 'berkah_theme');
define('CONSENT_COOKIE', 'berkah_consent');

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_WINDOW_MINUTES', 15);
define('CONTACT_MIN_INTERVAL', 30);            // detik antar kiriman formulir kontak

/* ---------- penanganan galat ---------- */

date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

$logDir = APP_ROOT . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
ini_set('error_log', $logDir . '/error.log');

/* ---------- URL dasar (mendukung subfolder XAMPP) ---------- */

function base_url_path(): string
{
    $override = env('APP_BASE_URL');
    if ($override !== null) {
        return rtrim($override, '/');
    }

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $projRoot = realpath(APP_ROOT);

    if ($docRoot && $projRoot && str_starts_with($projRoot, $docRoot)) {
        $base = str_replace('\\', '/', substr($projRoot, strlen($docRoot)));

        return rtrim($base, '/');
    }

    return '';
}

if (!defined('BASE_URL')) {
    define('BASE_URL', base_url_path());
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

/**
 * URL aset statis yang sudah diberi query string cache-buster.
 */
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $url = url($path);

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . ASSET_VERSION;
    }
}

/**
 * URL absolut (dipakai untuk redirect aman / berbagi tautan).
 */
function absolute_url(string $path = ''): string
{
    $host = $_SERVER['HTTP_HOST'] ?? env('APP_HOST', 'localhost');
    $scheme = is_https() ? 'https' : 'http';

    return $scheme . '://' . $host . url($path);
}
