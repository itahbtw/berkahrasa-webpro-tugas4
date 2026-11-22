<?php
declare(strict_types=1);

/*
 * Unit test mandiri tanpa PHPUnit/Composer.
 * Jalankan dari root proyek:
 *   php tests/run.php
 */

require_once __DIR__ . '/../includes/validators.php';

$passed = 0;
$failed = 0;
$failures = [];

function check(string $name, bool $condition, string $detail = ''): void
{
    global $passed, $failed, $failures;

    if ($condition) {
        $passed++;
        echo "  \033[32m✔\033[0m {$name}\n";
        return;
    }

    $failed++;
    $failures[] = $name . ($detail !== '' ? ' — ' . $detail : '');
    echo "  \033[31m✘\033[0m {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
}

function is_true(string $name, bool $actual): void
{
    check($name, $actual, 'diharapkan true');
}

function is_false(string $name, bool $actual): void
{
    check($name, !$actual, 'diharapkan false');
}

function same(string $name, mixed $expected, mixed $actual): void
{
    check($name, $expected === $actual, 'diharapkan ' . var_export($expected, true) .
        ', didapat ' . var_export($actual, true));
}

echo "\n=== Berkah Rasa — Unit Test (Tugas 4) ===\n";
echo "PHP " . PHP_VERSION . " · " . date('Y-m-d H:i:s') . "\n\n";

echo "[1] Validasi username\n";
foreach (['budi01', 'admin', 'operator_2', 'A123'] as $username) {
    is_true("username valid: {$username}", valid_username($username));
}
foreach (['ab', '1budi', 'budi 01', 'budi-01', 'budi@mail', str_repeat('a', 21), ''] as $username) {
    is_false('username ditolak: ' . ($username === '' ? '(kosong)' : $username),
        valid_username($username));
}

echo "\n[2] Validasi email\n";
is_true('email valid', valid_email('budi@example.com'));
is_true('email plus-tag valid', valid_email('budi+umkm@example.co.id'));
is_false('email tanpa @ ditolak', valid_email('budi.example.com'));
is_false('email kosong ditolak', valid_email(''));
is_false('email terlalu panjang ditolak', valid_email(str_repeat('a', 145) . '@example.com'));

echo "\n[3] Validasi nama\n";
is_true('nama 3 karakter diterima', valid_name('Budi'));
is_true('nama 120 karakter diterima', valid_name(str_repeat('a', 120)));
is_false('nama 2 karakter ditolak', valid_name('Bu'));
is_false('nama 121 karakter ditolak', valid_name(str_repeat('a', 121)));

echo "\n[4] Validasi nomor WhatsApp\n";
is_true('08xx valid', valid_phone('081234567890'));
is_true('+62 dengan pemisah valid', valid_phone('+62 812-3456-7890'));
is_true('62 tanpa + valid', valid_phone('6281234567890'));
is_false('huruf ditolak', valid_phone('abcdefghij'));
is_false('terlalu pendek ditolak', valid_phone('0812'));
is_false('tanpa awalan 0/62 ditolak', valid_phone('81234567890'));

echo "\n[5] Kekuatan password\n";
same('password kuat diterima', null, password_error('Budi#2026'));
same('password 8 karakter huruf+angka diterima', null, password_error('admin123'));
same('kurang dari 8 ditolak', 'Password minimal 8 karakter.', password_error('a1b2c3'));
same('tanpa angka ditolak', 'Password harus memuat huruf dan angka.', password_error('password'));
same('tanpa huruf ditolak', 'Password harus memuat huruf dan angka.', password_error('12345678'));
same('lebih dari 72 ditolak', 'Password maksimal 72 karakter.', password_error(str_repeat('a',
    71) . '12'));

echo "\n[6] Validasi harga\n";
is_true('harga 0 valid', valid_price(0));
is_true('harga 100 juta valid', valid_price(100_000_000));
is_true('harga string numerik valid', valid_price('7500'));
is_false('harga negatif ditolak', valid_price(-1));
is_false('harga di atas batas ditolak', valid_price(100_000_001));
is_false('harga non-numerik ditolak', valid_price('murah'));

echo "\n[7] Path gambar aman (anti traversal)\n";
is_true('path upload valid', safe_image_path('gambar/uploads/produk-20260101-abcd.jpg'));
is_true('path aset valid', safe_image_path('gambar/logo-icon.svg'));
is_false('traversal ditolak', safe_image_path('gambar/../config/config.php'));
is_false('awalan salah ditolak', safe_image_path('uploads/foto.jpg'));
is_false('URL eksternal ditolak', safe_image_path('https://example.com/foto.jpg'));
is_false('backslash ditolak', safe_image_path('gambar\\uploads\\foto.jpg'));
is_false('ekstensi skrip ditolak', safe_image_path('gambar/uploads/shell.php'));
is_false('path kosong ditolak', safe_image_path(''));

echo "\n[8] Tipe MIME gambar\n";
same('jpeg dikenali', 'jpg', image_mime_extension('image/jpeg'));
same('png dikenali', 'png', image_mime_extension('image/png'));
same('webp dikenali', 'webp', image_mime_extension('image/webp'));
same('gif dikenali', 'gif', image_mime_extension('image/gif'));
same('svg ditolak untuk unggahan', null, image_mime_extension('image/svg+xml'));
same('teks ditolak', null, image_mime_extension('text/plain'));

echo "\n[9] Token ingat saya\n";
$token = make_remember_token();
check('selector 32 hex', (bool) preg_match('/^[a-f0-9]{32}$/', $token['selector']));
check('validator 64 hex', (bool) preg_match('/^[a-f0-9]{64}$/', $token['validator']));
check('hash SHA-256 cocok', hash_equals(hash('sha256', $token['validator']), $token['hash']));
$parsed = parse_remember_cookie($token['cookie']);
same('cookie dapat diurai', $token['selector'], $parsed['selector'] ?? null);
same('validator hasil urai cocok', $token['validator'], $parsed['validator'] ?? null);
is_false('cookie tanpa pemisah ditolak', parse_remember_cookie('abc:def') !== null);
is_false('cookie kosong ditolak', parse_remember_cookie('') !== null);
is_true('validator benar diterima', verify_remember_validator($token['validator'], $token['hash']));
is_false('validator salah ditolak', verify_remember_validator(str_repeat('0', 64), $token['hash']));

echo "\n[10] CSRF\n";
$csrf = bin2hex(random_bytes(32));
is_true('token sama diterima', csrf_token_matches($csrf, $csrf));
is_false('token berbeda ditolak', csrf_token_matches($csrf, str_repeat('a', 64)));
is_false('token sesi kosong ditolak', csrf_token_matches('', $csrf));
is_false('token input kosong ditolak', csrf_token_matches($csrf, ''));

echo "\n[11] RBAC\n";
is_true('admin boleh apa saja (1)', role_can('admin', 'produk.hapus'));
is_true('admin boleh apa saja (2)', role_can('admin', 'log.view'));
is_true('operator boleh lihat produk', role_can('operator', 'produk.view'));
is_true('operator boleh ubah status pesanan', role_can('operator', 'pesanan.status'));
is_false('operator tidak boleh hapus produk', role_can('operator', 'produk.hapus'));
is_false('operator tidak boleh hapus pesanan', role_can('operator', 'pesanan.hapus'));
is_true('pelanggan boleh pesanan sendiri', role_can('pelanggan', 'pesanan.own'));
is_true('pelanggan boleh lihat akun', role_can('pelanggan', 'akun.view'));
is_false('pelanggan tidak boleh lihat produk admin', role_can('pelanggan', 'produk.view'));
is_false('peran tidak dikenal ditolak', role_can('tamu', 'akun.view'));
is_true('admin adalah staf', is_staff_role('admin'));
is_true('operator adalah staf', is_staff_role('operator'));
is_false('pelanggan bukan staf', is_staff_role('pelanggan'));

echo "\n[12] Transisi status pesanan\n";
same('operator dari baru', ['baru', 'diproses', 'dibatalkan'],
    allowed_order_statuses('operator', 'baru'));
same('operator dari diproses', ['diproses', 'dikirim', 'dibatalkan'],
    allowed_order_statuses('operator', 'diproses'));
same('operator dari dikirim', ['dikirim', 'selesai'], allowed_order_statuses('operator',
    'dikirim'));
same('operator dari selesai hanya selesai', ['selesai'], allowed_order_statuses('operator',
    'selesai'));
same('admin bebas semua status', ['baru', 'diproses', 'dikirim', 'selesai', 'dibatalkan'],
    allowed_order_statuses('admin', 'baru'));

echo "\n[15] Ongkir\n";
is_true('subtotal 50rb kena ongkir', ongkir_amount(50000) === 10000);
is_true('subtotal 99rb kena ongkir', ongkir_amount(99000) === 10000);
is_true('subtotal 100rb gratis', ongkir_amount(100000) === 0);
is_true('subtotal 150rb gratis', ongkir_amount(150000) === 0);

echo "\n[14] Metode pembayaran\n";
is_true('COD diterima', valid_payment_method('cod'));
is_true('Transfer diterima', valid_payment_method('transfer'));
is_false('Metode kosong ditolak', valid_payment_method(''));
is_false('Metode tidak dikenal ditolak', valid_payment_method('whatsapp'));

echo "\n[13] Pembersihan teks\n";
same('spasi dipangkas', 'Halo', clean_text('  Halo  '));
same('karakter kontrol dibuang', 'Halo', clean_text("Ha\x00lo\x1F"));
same('dipotong sesuai batas', 'abcde', clean_text('abcdefghij', 5));
same('string kosong aman', '', clean_text(null));

echo "\n----------------------------------------\n";
echo "Hasil: {$passed} lulus, {$failed} gagal.\n";

if ($failed > 0) {
    echo "\nGagal:\n";
    foreach ($failures as $failure) {
        echo " - {$failure}\n";
    }
    exit(1);
}

echo "Semua pengujian lulus.\n";
exit(0);
