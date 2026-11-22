<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
require_permission('pesanan.own');

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

// Kueri selalu menyertakan user_id -> mencegah IDOR.
$order = $id > 0 ? get_order_for_user($id, (int) $user['id']) : null;

if (!$order) {
    // Jangan ungkap apakah pesanan milik orang lain ada atau tidak.
    set_flash('error', 'Pesanan tidak ditemukan pada akun Anda.');
    redirect(url('pesanan-saya.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (($_POST['action'] ?? '') === 'batalkan') {
        $stmt = db()->prepare(
            "UPDATE pesanan SET status = 'dibatalkan'
             WHERE id = ? AND user_id = ? AND status = 'baru'"
        );
        $stmt->execute([(int) $order['id'], (int) $user['id']]);

        if ($stmt->rowCount() === 1) {
            log_activity((int) $user['id'], 'pesanan.batal', 'Membatalkan pesanan ' .
                $order['kode']);
            set_flash('success', 'Pesanan ' . $order['kode'] . ' berhasil dibatalkan.');
        } else {
            set_flash('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
        }

        redirect(url('pesanan-saya-detail.php?id=' . (int) $order['id']));
    }
}

$details = get_order_details((int) $order['id']);

$steps = ['baru' => 'Baru', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' =>
    'Selesai'];
$currentIndex = array_search($order['status'], array_keys($steps), true);
$isCancelled = $order['status'] === 'dibatalkan';

$pageTitle = 'Detail Pesanan';
$pageDesc  = 'Detail pesanan ' . $order['kode'] . ' di Berkah Rasa.';
$active    = '';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / <a
        href="<?= url('akun.php') ?>">Akun</a> / <a
            href="<?= url('pesanan-saya.php') ?>">Pesanan</a> / Detail</div>
    <h1>Pesanan <?= e($order['kode']) ?></h1>
    <p>Dibuat <?= e(format_tanggal($order['created_at'])) ?> WIB · <?= status_badge((string)
        $order['status']) ?></p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="detail-page-grid">
      <section class="content-card reveal">
        <div class="panel-title-row">
          <h2>Item Pesanan</h2>
          <a class="btn btn-sm btn-ghost" href="<?= url('pesanan-saya.php') ?>">&larr; Kembali</a>
        </div>

        <div class="table-wrap">
          <table>
            <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($details as $item): ?>
              <tr>
                <td><?= e($item['nama_produk']) ?></td>
                <td><?= rupiah($item['harga']) ?></td>
                <td><?= (int) $item['qty'] ?></td>
                <td><?= rupiah($item['subtotal']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr><th colspan="3" class="text-right">Total</th><th><?= rupiah($order['total'])
                  ?></th></tr>
            </tfoot>
          </table>
        </div>

        <h3 class="mt-2">Status Pesanan</h3>
        <?php if ($isCancelled): ?>
        <div class="flash flash-error mt-sm">Pesanan ini telah dibatalkan.</div>
        <?php else: ?>
        <ol class="order-timeline">
          <?php $i = 0; foreach ($steps as $key => $label): ?>
          <li class="<?= $i <= (int) $currentIndex ? 'done' : '' ?>">
            <span class="dot"></span>
            <span><?= e($label) ?></span>
          </li>
          <?php $i++; endforeach; ?>
        </ol>
        <?php endif; ?>
      </section>

      <aside class="content-card reveal d1">
        <h2>Info Pengiriman</h2>
        <ul class="detail-list">
          <li><span class="k">Nama</span><span class="v"><?= e($order['nama_pembeli'])
              ?></span></li>
          <li><span class="k">Pembayaran</span><span class="v"><?= $order['pembayaran'] ===
              'transfer' ? 'Transfer Bank — BCA ' . e(BCA_ACCOUNT) : 'Bayar di Tempat (COD)'
                  ?></span></li>
          <li><span class="k">Ongkir</span><span class="v"><?= (int) ($order['ongkir'] ?? 0) > 0
              ? rupiah((int) $order['ongkir']) : 'Gratis' ?></span></li>
          <li><span class="k">WhatsApp</span><span class="v"><?= e($order['no_wa']) ?></span></li>
          <li><span class="k">Alamat</span><span class="v"><?= nl2br(e($order['alamat'] ?: '-'))
              ?></span></li>
          <li><span class="k">Catatan</span><span class="v"><?= nl2br(e($order['catatan'] ?:
              '-')) ?></span></li>
          <li><span class="k">Total</span><span class="v"><strong><?= rupiah($order['total'])
              ?></strong></span></li>
        </ul>

        <div class="detail-actions">
          <a class="btn" href="<?= url('kontak.php') ?>">Hubungi Penjual</a>
          <button type="button" class="btn btn-ghost" onclick="window.print()">Cetak</button>
        </div>

        <?php if ($order['status'] === 'baru'): ?>
        <form method="post" class="cancel-form"
            onsubmit="return confirm('Batalkan pesanan ini? Tindakan tidak dapat dibatalkan.')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="batalkan">
          <button class="btn btn-danger-soft" type="submit">Batalkan Pesanan</button>
        </form>
        <?php endif; ?>

        <p class="auth-small">Hanya pemilik pesanan yang dapat melihat halaman ini. Akses ke
            pesanan pengguna lain akan ditolak.</p>
      </aside>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
