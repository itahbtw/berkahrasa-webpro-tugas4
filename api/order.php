<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
}

// Permintaan lintas situs ditolak (lapisan tambahan selain token CSRF).
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($originHost === null || strcasecmp((string) $originHost, (string)
        preg_replace('/:\d+$/', '', $host)) !== 0) {
        respond(['success' => false, 'message' => 'Permintaan lintas situs ditolak.'], 403);
    }
}

$csrf = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!csrf_token_matches((string) ($_SESSION['csrf_token'] ?? ''), $csrf)) {
    respond(['success' => false, 'message' =>
        'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.'], 419);
}

// Throttle sederhana: maksimal 10 pesanan per 10 menit per sesi.
$now = time();
$times = array_values(array_filter(
    (array) ($_SESSION['order_times'] ?? []),
    static fn ($t): bool => is_int($t) && $t > ($now - 600)
));
if (count($times) >= 10) {
    respond(['success' => false, 'message' =>
        'Terlalu banyak pesanan dalam waktu singkat. Coba lagi nanti.'], 429);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    respond(['success' => false, 'message' => 'Data pesanan tidak valid.'], 400);
}

$nama      = clean_text($data['nama'] ?? '', 120);
$email     = mb_strtolower(clean_text($data['email'] ?? '', 150));
$noWa      = clean_text($data['no_wa'] ?? '', 30);
$alamat    = clean_text($data['alamat'] ?? '', 300);
$catatan   = clean_text($data['catatan'] ?? '', 500);
$pembayaran = clean_text($data['pembayaran'] ?? 'cod', 20);
$items     = $data['items'] ?? [];

if (!valid_name($nama)) {
    respond(['success' => false, 'message' => 'Nama minimal 3 karakter.'], 422);
}
if ($email !== '' && !valid_email($email)) {
    respond(['success' => false, 'message' => 'Format email tidak valid.'], 422);
}
if (!valid_phone($noWa)) {
    respond(['success' => false, 'message' => 'Nomor WhatsApp tidak valid.'], 422);
}
if (!valid_payment_method($pembayaran)) {
    respond(['success' => false, 'message' => 'Metode pembayaran tidak valid.'], 422);
}
if (!is_array($items) || count($items) === 0) {
    respond(['success' => false, 'message' => 'Keranjang masih kosong.'], 422);
}
if (count($items) > 50) {
    respond(['success' => false, 'message' =>
        'Jenis produk dalam satu pesanan terlalu banyak.'], 422);
}

$pdo = db();

$ids = [];
$qtyById = [];
foreach ($items as $item) {
    $id  = (int) ($item['id'] ?? 0);
    $qty = (int) ($item['qty'] ?? 0);

    if ($id <= 0 || $qty < 1 || $qty > 99) {
        continue;
    }

    $ids[$id] = true;
    $qtyById[$id] = min(99, ($qtyById[$id] ?? 0) + $qty);
}
$ids = array_keys($ids);

if (count($ids) === 0) {
    respond(['success' => false, 'message' => 'Produk tidak valid.'], 422);
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt =
    $pdo->prepare("SELECT id, nama, harga FROM produk WHERE aktif = 1 AND id IN ($placeholders)");
$stmt->execute($ids);

$produkMap = [];
foreach ($stmt->fetchAll() as $row) {
    $produkMap[(int) $row['id']] = $row;
}

$detail = [];
$total  = 0;

foreach ($qtyById as $id => $qty) {
    if (!isset($produkMap[$id])) {
        continue;
    }

    $produk   = $produkMap[$id];
    $subtotal = (int) $produk['harga'] * $qty;

    $detail[] = [
        'produk_id'   => $id,
        'nama_produk' => $produk['nama'],
        'harga'       => (int) $produk['harga'],
        'qty'         => $qty,
        'subtotal'    => $subtotal,
    ];
    $total += $subtotal;
}

if (count($detail) === 0) {
    respond(['success' => false, 'message' => 'Tidak ada produk yang tersedia.'], 422);
}
$subtotal = $total;
$ongkir   = ongkir_amount($subtotal);
$grandTotal = $subtotal + $ongkir;

if ($grandTotal > 50_000_000) {
    respond(['success' => false, 'message' =>
        'Total pesanan melebihi batas. Hubungi kami langsung.'], 422);
}

$kode = 'BR-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
$userId = is_logged_in() ? (int) current_user()['id'] : null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO pesanan (user_id, kode, nama_pembeli, email, no_wa, alamat, catatan, pembayaran, total, ongkir, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "baru")'
    );
    $stmt->execute([
        $userId,
        $kode,
        $nama,
        $email !== '' ? $email : null,
        $noWa,
        $alamat,
        $catatan,
        $pembayaran,
        $grandTotal,
        $ongkir,
    ]);
    $pesananId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO pesanan_detail (pesanan_id, produk_id, nama_produk, harga, qty, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($detail as $row) {
        $stmt->execute([
            $pesananId,
            $row['produk_id'],
            $row['nama_produk'],
            $row['harga'],
            $row['qty'],
            $row['subtotal'],
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[order] ' . $e->getMessage());
    respond(['success' => false, 'message' => 'Gagal menyimpan pesanan. Coba lagi.'], 500);
}

$times[] = $now;
$_SESSION['order_times'] = $times;

log_activity(
    $userId,
    'pesanan.buat',
    'Pesanan ' . $kode . ' subtotal ' . $subtotal . ' ongkir ' . $ongkir . ' total ' .
        $grandTotal . ' metode ' . $pembayaran . ($userId ? '' : ' (tamu)')
);

respond([
    'success'  => true,
    'kode'     => $kode,
    'subtotal' => $subtotal,
    'ongkir'   => $ongkir,
    'total'    => $grandTotal,
    'akun'     => $userId !== null,
]);
