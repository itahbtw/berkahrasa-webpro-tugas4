<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php';

/* ============================================================
   Helper umum, data produk, pesanan, dan tampilan.
   ============================================================ */

/* ---------- helper dasar ---------- */

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah(mixed $number): string
{
    return 'Rp' . number_format((float) $number, 0, ',', '.');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Ubah nomor Indonesia (08xx / +62 / 62) menjadi format wa.me.
 */
function normalize_wa(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '0')) {
        return '62' . substr($digits, 1);
    }

    return $digits;
}

function format_tanggal(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if ($datetime === null || $datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }

    $time = strtotime($datetime);

    return $time === false ? '-' : date($format, $time);
}

/* ---------- data produk ---------- */

function get_products(bool $onlyActive = true): array
{
    $sql = 'SELECT * FROM produk';
    if ($onlyActive) {
        $sql .= ' WHERE aktif = 1';
    }
    $sql .= ' ORDER BY id ASC';

    return db()->query($sql)->fetchAll();
}

function get_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Ubah baris produk dari database ke bentuk yang dipakai script.js.
 */
function products_for_js(array $rows): array
{
    return array_map(static fn (array $p): array => [
        'id'      => (int) $p['id'],
        'name'    => $p['nama'],
        'variant' => $p['varian'],
        'size'    => $p['ukuran'],
        'price'   => (int) $p['harga'],
        'img'     => str_starts_with((string) $p['gambar'], 'gambar/')
            ? asset((string) $p['gambar'])
            : (string) $p['gambar'],
        'tag'     => $p['tag'] ?? '',
        'desc'    => $p['deskripsi'] ?? '',
    ], $rows);
}

/* ---------- flash message ---------- */

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/* ---------- upload gambar produk ---------- */

/**
 * Simpan gambar unggahan dengan nama acak dan validasi isi file.
 *
 * @return array{path:?string,error:?string}
 */
function save_product_image(array $file): array
{
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Gambar gagal diunggah (kode ' . $errorCode . ').'];
    }

    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        return ['path' => null, 'error' => 'Ukuran gambar maksimal 3 MB.'];
    }

    if (!is_uploaded_file((string) $file['tmp_name'])) {
        return ['path' => null, 'error' => 'Berkas unggahan tidak valid.'];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    $ext = image_mime_extension((string) $mime);

    if ($ext === null) {
        return ['path' => null, 'error' => 'Format gambar harus JPG, PNG, WEBP, atau GIF.'];
    }

    $info = @getimagesize((string) $file['tmp_name']);
    if ($info === false || $info[0] < 40 || $info[1] < 40 || $info[0] > 6000 || $info[1] > 6000) {
        return ['path' => null, 'error' =>
            'Dimensi gambar tidak wajar (minimal 40x40, maksimal 6000x6000).'];
    }

    $dir = APP_ROOT . '/gambar/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['path' => null, 'error' => 'Folder unggahan tidak dapat dibuat.'];
    }

    $filename = 'produk-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
    $target = $dir . '/' . $filename;

    if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
        return ['path' => null, 'error' => 'Gagal menyimpan gambar di server.'];
    }

    @chmod($target, 0644);

    return ['path' => 'gambar/uploads/' . $filename, 'error' => null];
}

/**
 * Hapus hanya file yang berada di folder gambar/uploads.
 */
function remove_uploaded_image(?string $path): void
{
    if ($path === null || !str_starts_with($path, 'gambar/uploads/') || str_contains($path, '..')) {
        return;
    }

    $file = realpath(APP_ROOT . '/' . $path);
    $base = realpath(APP_ROOT . '/gambar/uploads');

    if ($file !== false && $base !== false && str_starts_with($file, $base .
        DIRECTORY_SEPARATOR) && is_file($file)) {
        @unlink($file);
    }
}

/* ---------- data pesanan ---------- */

function pesanan_statuses(): array
{
    return [
        'baru'       => 'Baru',
        'diproses'   => 'Diproses',
        'dikirim'    => 'Dikirim',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
}

function status_badge(string $status): string
{
    $label = pesanan_statuses()[$status] ?? $status;

    return '<span class="badge badge-' . e($status) . '">' . e($label) . '</span>';
}

/**
 * Ambil pesanan milik satu pengguna (mencegah IDOR).
 */
function get_order_for_user(int $orderId, int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM pesanan WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$orderId, $userId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function get_order_details(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM pesanan_detail WHERE pesanan_id = ? ORDER BY id ASC');
    $stmt->execute([$orderId]);

    return $stmt->fetchAll();
}

function get_user_orders(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT p.*, COUNT(d.id) AS jumlah_item
         FROM pesanan p
         LEFT JOIN pesanan_detail d ON d.pesanan_id = p.id
         WHERE p.user_id = ?
         GROUP BY p.id
         ORDER BY p.created_at DESC, p.id DESC'
    );
    $stmt->execute([$userId]);

    return $stmt->fetchAll();
}

/* ---------- ringkasan dashboard ---------- */

function dashboard_counts(): array
{
    $pdo = db();

    $counts = [
        'produk'       => (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn(),
        'pesanan'      => (int) $pdo->query('SELECT COUNT(*) FROM pesanan')->fetchColumn(),
        'pesanan_baru' => (int)
            $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'baru'")->fetchColumn(),
        'pesan_baru'   => (int)
            $pdo->query('SELECT COUNT(*) FROM pesan_kontak WHERE dibaca = 0')->fetchColumn(),
        'pelanggan'    => (int)
            $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pelanggan' AND aktif = 1")->fetchColumn(),
    ];

    return $counts;
}

/* ---------- kompatibilitas nama lama ---------- */

function is_admin_logged_in(): bool
{
    return is_staff();
}

function admin_name(): string
{
    return user_name();
}
