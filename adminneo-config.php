<?php
declare(strict_types=1);

/*
 * Konfigurasi AdminNeo — terhubung ke database yang sama dengan aplikasi.
 * Buka: /adminneo.php (hanya admin yang login dan ENABLE_ADMINNEO=1).
 *
 * Tidak ada kredensial hardcoded di sini. Nilai dibaca dari .env / environment:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
 * Jangan mengaktifkan AdminNeo di produksi; hapus file adminneo*.php bila tidak dipakai.
 */

require_once __DIR__ . '/config/config.php';

$neoServer = env('ADMINNEO_SERVER', 'berkahrasa');

return [
    'colorVariant'  => 'green',
    'defaultDriver' => 'mysql',
    'defaultServer' => $neoServer,
    'servers' => [
        $neoServer => [
            'driver'   => 'mysql',
            'name'     => APP_NAME . ' - MariaDB',
            'server'   => DB_HOST . ':' . DB_PORT,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
        ],
    ],
];
