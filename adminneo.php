<?php
declare(strict_types=1);

/*
 * Wrapper AdminNeo: memakai bahasa Indonesia secara default.
 * Aplikasi aslinya ada di adminneo-app.php, konfigurasi di adminneo-config.php.
 *
 * Keamanan:
 * - Hanya administrator yang sudah login yang boleh membuka halaman ini.
 * - Gunakan ENABLE_ADMINNEO=1 di .env hanya pada lingkungan lokal/percobaan.
 *   Pada produksi biarkan nonaktif (default) atau hapus file adminneo*.php.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!env_bool('ENABLE_ADMINNEO', false)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Not Found');
}

require_admin();

if (empty($_COOKIE['neo_lang']) && empty($_COOKIE['neo_settings'])) {
    $_COOKIE['neo_lang'] = 'id';
}

/*
 * Cegah autofill browser (username "admin" / password "admin123" dari login
 * panel admin) agar tidak dipakai sebagai kredensial database.
 * Kredensial lain yang diketik manual (mis. user "itah") tetap dihormati.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth']) && is_array($_POST['auth'])) {
    if (($_POST['auth']['server'] ?? '') === env('ADMINNEO_SERVER', 'berkahrasa')) {
        $neoUser = trim((string) ($_POST['auth']['username'] ?? ''));
        $neoPass = (string) ($_POST['auth']['password'] ?? '');

        if (strcasecmp($neoUser, 'admin') === 0 && $neoPass === 'admin123') {
            $_POST['auth']['username'] = '';
            $_POST['auth']['password'] = '';
        }
    }
}

require __DIR__ . '/adminneo-app.php';
