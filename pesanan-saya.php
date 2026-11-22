<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
require_permission('pesanan.own');

$user = current_user();
$orders = get_user_orders((int) $user['id']);

$pageTitle = 'Pesanan Saya';
$pageDesc  = 'Riwayat dan status pesanan akun Berkah Rasa.';
$active    = '';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / <a
        href="<?= url('akun.php') ?>">Akun</a> / Pesanan</div>
    <h1>Pesanan Saya</h1>
    <p>Semua pesanan yang tercatat dengan akun <strong>@<?= e($user['username']) ?></strong>.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="orders-head reveal">
      <div>
        <h2>Riwayat Pesanan</h2>
        <p class="muted-text"><?= count($orders) ?> pesanan ditemukan.</p>
      </div>
      <a class="btn btn-ghost" href="<?= url('produk.php') ?>">Belanja Lagi</a>
    </div>

    <?php if (!$orders): ?>
    <div class="empty-orders reveal">
      <h3>Belum ada pesanan</h3>
      <p>Pesanan yang Anda checkout saat sudah masuk akan otomatis muncul di halaman ini.</p>
      <a class="btn" href="<?= url('produk.php') ?>">Lihat Produk</a>
    </div>
    <?php else: ?>
    <div class="orders-list">
      <?php foreach ($orders as $order): ?>
      <article class="order-card reveal">
        <div class="order-card-head">
          <div>
            <span class="order-code"><?= e($order['kode']) ?></span>
            <span class="order-date"><?= e(format_tanggal($order['created_at'])) ?> WIB</span>
          </div>
          <?= status_badge((string) $order['status']) ?>
        </div>
        <div class="order-card-body">
          <div class="order-fact">
            <span>Jumlah item</span>
            <b><?= (int) $order['jumlah_item'] ?> jenis</b>
          </div>
          <div class="order-fact">
            <span>Total</span>
            <b><?= rupiah($order['total']) ?></b>
          </div>
          <div class="order-fact">
            <span>Penerima</span>
            <b><?= e($order['nama_pembeli']) ?></b>
          </div>
          <div class="order-card-actions">
            <a class="btn btn-sm"
                href="<?= url('pesanan-saya-detail.php?id=' . (int) $order['id']) ?>">Lihat
                    Detail</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="content-card mt-2 reveal">
      <h3>Catatan</h3>
      <p>Pesanan berstatus <strong>Baru</strong> masih dapat dibatalkan dari halaman detail.
          Setelah diproses, perubahan pesanan dilakukan melalui WhatsApp agar tercatat oleh
              penjual.</p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
