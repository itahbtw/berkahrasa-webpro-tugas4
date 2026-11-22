<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Lacak Pesanan';
$pageDesc  = 'Cek status pesanan Berkah Rasa dengan kode pesanan dan nomor WhatsApp.';
$active    = '';

$kode = clean_text($_POST['kode'] ?? $_GET['kode'] ?? '', 30);
$wa   = clean_text($_POST['no_wa'] ?? $_GET['no_wa'] ?? '', 30);
$order = null;
$error = '';
$details = [];

if ($kode !== '' || $wa !== '') {
    verify_csrf();

    $now = time();
    $times = array_values(array_filter(
        (array) ($_SESSION['track_times'] ?? []),
        static fn ($t): bool => is_int($t) && $t > ($now - 600)
    ));
    if (count($times) >= 8) {
        $error = 'Terlalu banyak percobaan. Coba lagi beberapa menit.';
    } else {
        $times[] = $now;
        $_SESSION['track_times'] = $times;

        if ($kode === '' || $wa === '') {
            $error = 'Kode pesanan dan nomor WhatsApp wajib diisi.';
        } else {
            $stmt = db()->prepare('SELECT * FROM pesanan WHERE kode = ? LIMIT 1');
            $stmt->execute([$kode]);
            $candidate = $stmt->fetch();

            if ($candidate && hash_equals(normalize_wa((string) $candidate['no_wa']),
                normalize_wa($wa))) {
                $order = $candidate;
                $details = get_order_details((int) $order['id']);
            } else {
                $error =
                    'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan nomor WhatsApp Anda.';
            }
        }
    }
}

$steps = ['baru' => 'Baru', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' =>
    'Selesai'];
$currentIndex = $order ? array_search($order['status'], array_keys($steps), true) : -1;
$isCancelled = $order && $order['status'] === 'dibatalkan';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Lacak Pesanan</div>
    <h1>Lacak Pesanan</h1>
    <p>Cek status pesanan Anda tanpa perlu login. Cukup masukkan kode pesanan dan nomor
        WhatsApp.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="track-layout">
      <section class="content-card reveal" id="trackFormCard">
        <h2>Cari Pesanan</h2>
        <p>Masukkan kode pesanan yang dikirim setelah checkout, lalu nomor WhatsApp yang dipakai
            saat memesan.</p>

        <?php if ($error): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form" action="<?= url('lacak-pesanan.php') ?>">
          <?= csrf_field() ?>
          <div class="form-field">
            <label for="trackKode">Kode Pesanan</label>
            <input type="text" id="trackKode" name="kode" value="<?= e($kode) ?>" required
                maxlength="30" placeholder="BR-20261121-XXXXXX" autocomplete="off">
            <small class="form-hint">Contoh: BR-20261121-AB12CD</small>
          </div>
          <div class="form-field">
            <label for="trackWa">Nomor WhatsApp</label>
            <input type="tel" id="trackWa" name="no_wa" value="<?= e($wa) ?>" required
                maxlength="30" placeholder="081234567890" autocomplete="tel">
            <small class="form-hint">Nomor yang dipakai saat checkout.</small>
          </div>
          <button class="btn btn-block" type="submit">Lacak Pesanan</button>
        </form>
      </section>

      <?php if ($order): ?>
      <section class="content-card reveal d1" id="trackResult">
        <div class="panel-title-row">
          <h2>Hasil Pelacakan</h2>
          <?= status_badge((string) $order['status']) ?>
        </div>

        <ul class="detail-list track-summary">
          <li><span class="k">Kode</span><span class="v"><?= e($order['kode']) ?></span></li>
          <li><span class="k">Tanggal</span><span class="v"><?=
              e(format_tanggal($order['created_at'])) ?> WIB</span></li>
          <li><span class="k">Penerima</span><span class="v"><?= e($order['nama_pembeli'])
              ?></span></li>
          <li><span class="k">Pembayaran</span><span class="v"><?= $order['pembayaran'] ===
              'transfer' ? 'Transfer Bank' : 'Bayar di Tempat (COD)' ?></span></li>
          <li><span class="k">Total</span><span class="v"><strong><?= rupiah($order['total'])
              ?></strong></span></li>
        </ul>

        <h3 class="mt-2">Status Pesanan</h3>
        <?php if ($isCancelled): ?>
        <div class="flash flash-error">Pesanan ini telah dibatalkan.</div>
        <?php else: ?>
        <ol class="order-timeline">
          <?php $i = 0; foreach ($steps as $key => $label): ?>
          <li class="<?= $i <= (int) $currentIndex ? 'done' : '' ?>"><span
              class="dot"></span><span><?= e($label) ?></span></li>
          <?php $i++; endforeach; ?>
        </ol>
        <?php endif; ?>

        <h3 class="mt-2">Item Pesanan</h3>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Produk</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($details as $item): ?>
              <tr>
                <td><?= e($item['nama_produk']) ?></td>
                <td><?= (int) $item['qty'] ?></td>
                <td><?= rupiah($item['subtotal']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="detail-actions">
          <button type="button" class="btn btn-ghost" onclick="window.print()">Cetak Bukti</button>
          <a class="btn btn-ghost" href="<?= url('kontak.php') ?>">Butuh bantuan?</a>
        </div>
      </section>
      <?php elseif ($error === ''): ?>
      <aside class="content-card reveal d1">
        <h2>Belum punya kode pesanan?</h2>
        <p>Kode pesanan muncul otomatis setelah Anda menyelesaikan checkout di website. Pesanan
            yang dibuat sebagai tamu juga bisa dilacak di sini.</p>
        <a class="btn" href="<?= url('produk.php') ?>">Mulai Belanja</a>
      </aside>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
