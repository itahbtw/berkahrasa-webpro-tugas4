<?php
declare(strict_types=1);

/* ============================================================
   Validasi murni (tanpa efek samping) — mudah diuji unit.
   ============================================================ */

function clean_text(mixed $value, int $max = 255): string
{
    $text = trim((string) $value);
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';

    return mb_substr($text, 0, $max);
}

function valid_username(string $username): bool
{
    return (bool) preg_match('/^[A-Za-z][A-Za-z0-9_]{3,19}$/', $username);
}

function valid_email(string $email): bool
{
    return $email !== '' && mb_strlen($email) <= 150 && filter_var($email,
        FILTER_VALIDATE_EMAIL) !== false;
}

function valid_name(string $name): bool
{
    $length = mb_strlen(trim($name));

    return $length >= 3 && $length <= 120;
}

function valid_phone(string $phone): bool
{
    if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
        return false;
    }

    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    if (strlen($digits) < 9 || strlen($digits) > 15) {
        return false;
    }

    return str_starts_with($digits, '0') || str_starts_with($digits, '62');
}

/**
 * Mengembalikan pesan kesalahan, atau null bila password kuat.
 */
function password_error(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password minimal 8 karakter.';
    }
    if (strlen($password) > 72) {
        return 'Password maksimal 72 karakter.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return 'Password harus memuat huruf dan angka.';
    }

    return null;
}

function ongkir_amount(int $subtotal): int
{
    $threshold = defined('ONGKIR_THRESHOLD') ? (int) ONGKIR_THRESHOLD : 100000;
    $fee = defined('ONGKIR_FEE') ? (int) ONGKIR_FEE : 10000;

    return $subtotal > 0 && $subtotal < $threshold ? $fee : 0;
}

function valid_payment_method(string $method): bool
{
    return in_array($method, ['cod', 'transfer'], true);
}

function valid_price(mixed $price): bool
{
    if (!is_numeric($price)) {
        return false;
    }

    $value = (int) $price;

    return $value >= 0 && $value <= 100_000_000;
}

/**
 * Path gambar harus relatif di dalam folder gambar/ dan tidak memakai traversal.
 */
function safe_image_path(string $path): bool
{
    if ($path === '' || strlen($path) > 255) {
        return false;
    }
    if (!str_starts_with($path, 'gambar/')) {
        return false;
    }
    if (str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, '://')) {
        return false;
    }
    if (!preg_match('#^gambar/[A-Za-z0-9_./-]+\.(jpe?g|png|webp|gif|svg)$#i', $path)) {
        return false;
    }

    return true;
}

function image_mime_extension(string $mime): ?string
{
    return match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => null,
    };
}

/* ---------- token "ingat saya" ---------- */

/**
 * @return array{selector:string,validator:string,hash:string,cookie:string}
 */
function make_remember_token(): array
{
    $selector  = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));

    return [
        'selector'  => $selector,
        'validator' => $validator,
        'hash'      => hash('sha256', $validator),
        'cookie'    => $selector . ':' . $validator,
    ];
}

function verify_remember_validator(string $validator, string $storedHash): bool
{
    return $storedHash !== '' && hash_equals($storedHash, hash('sha256', $validator));
}

/**
 * @return array{selector:string,validator:string}|null
 */
function parse_remember_cookie(string $value): ?array
{
    if (!preg_match('/^([a-f0-9]{32}):([a-f0-9]{64})$/', $value, $m)) {
        return null;
    }

    return ['selector' => $m[1], 'validator' => $m[2]];
}

function csrf_token_matches(string $sessionToken, string $inputToken): bool
{
    return $sessionToken !== '' && $inputToken !== '' && hash_equals($sessionToken, $inputToken);
}

/* ---------- peran & izin (RBAC) ---------- */

function roles(): array
{
    return [
        'admin'     => 'Administrator',
        'operator'  => 'Operator Toko',
        'pelanggan' => 'Pelanggan',
    ];
}

function role_label(string $role): string
{
    return roles()[$role] ?? ucfirst($role);
}

function role_permissions(): array
{
    return [
        'admin' => ['*'],
        'operator' => [
            'dashboard.view',
            'produk.view', 'produk.create', 'produk.edit',
            'pesanan.view', 'pesanan.status',
            'pesan.view', 'pesan.read',
        ],
        'pelanggan' => [
            'akun.view', 'akun.edit', 'pesanan.own',
        ],
    ];
}

function role_can(string $role, string $permission): bool
{
    $permissions = role_permissions()[$role] ?? [];

    return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
}

function is_staff_role(string $role): bool
{
    return $role === 'admin' || $role === 'operator';
}

/* ---------- status pesanan ---------- */

function order_status_transitions(): array
{
    return [
        'baru'       => ['diproses', 'dibatalkan'],
        'diproses'   => ['dikirim', 'dibatalkan'],
        'dikirim'    => ['selesai'],
        'selesai'    => [],
        'dibatalkan' => [],
    ];
}

/**
 * Status yang boleh dipilih untuk sebuah pesanan.
 * Administrator bebas memilih semua status; operator mengikuti alur.
 */
function allowed_order_statuses(string $role, string $current): array
{
    $all = array_keys(order_status_transitions());

    if ($role === 'admin') {
        return $all;
    }

    $next = order_status_transitions()[$current] ?? [];

    return array_values(array_unique(array_merge([$current], $next)));
}
