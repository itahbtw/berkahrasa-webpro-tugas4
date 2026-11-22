<?php
require_once __DIR__ . '/includes/auth.php';
require_permission('dashboard.view');

$counts = dashboard_counts();
$pdo = db();

$recentOrders = $pdo->query(
    'SELECT * FROM pesanan ORDER BY created_at DESC, id DESC LIMIT 5'
)->fetchAll();

$recentMessages = $pdo->query(
    'SELECT * FROM pesan_kontak ORDER BY created_at DESC, id DESC LIMIT 5'
)->fetchAll();

$adminTitle  = 'Dashboard';
$adminActive = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="label">Total Produk</div>
    <div class="value"><?= $counts['produk'] ?></div>
    <div class="hint">Produk terdaftar di katalog</div>
  </div>
  <div class="stat-card">
    <div class="label">Total Pesanan</div>
    <div class="value"><?= $counts['pesanan'] ?></div>
    <div class="hint">Seluruh pesanan masuk</div>
  </div>
  <div class="stat-card warn">
    <div class="label">Pesanan Baru</div>
    <div class="value"><?= $counts['pesanan_baru'] ?></div>
    <div class="hint">Menunggu diproses</div>
  </div>
  <div class="stat-card warn">
    <div class="label">Pesan Belum Dibaca</div>
    <div class="value"><?= $counts['pesan_baru'] ?></div>
    <div class="hint">Dari formulir kontak</div>
  </div>
</div>

<div class="security-strip">
  <span><b>Peran Anda:</b> <?= e(role_label((string) current_user()['role'])) ?></span>
  <span><b>HTTPS:</b> <?= is_https() ? 'Aktif' : 'Nonaktif (lokal)' ?></span>
  <span><b>Idle timeout:</b> <?= (int) (SESSION_IDLE_TIMEOUT / 60) ?> menit</span>
  <span><b>Pelanggan aktif:</b> <?= $counts['pelanggan'] ?></span>
  <span><b>Versi:</b> <?= e(APP_VERSION) ?></span>
</div>

<div class="quick-actions">
  <?php if (user_can('produk.create')): ?>
  <a class="quick-action" href="<?= url('admin/produk/form.php') ?>"><b>+ Produk</b><span>Tambah
      katalog baru</span></a>
  <?php endif; ?>
  <a class="quick-action"
      href="<?= url('admin/pesanan/index.php') ?>"><b>Pesanan</b><span>Proses status
          pesanan</span></a>
  <a class="quick-action" href="<?= url('admin/pesan/index.php') ?>"><b>Pesan</b><span>Balas
      pertanyaan pelanggan</span></a>
  <?php if (is_admin()): ?>
  <a class="quick-action"
      href="<?= url('admin/pengguna/index.php') ?>"><b>Pengguna</b><span>Atur akun &amp;
          peran</span></a>
  <a class="quick-action" href="<?= url('admin/log/index.php') ?>"><b>Log</b><span>Audit
      aktivitas</span></a>
  <?php endif; ?>
</div>

<div class="grid-2-admin">
  <div class="panel">
    <div class="panel-head">
      <h2>Pesanan Terbaru</h2>
      <a class="btn-sm btn-sm-ghost" href="<?= url('admin/pesanan/index.php') ?>">Lihat semua</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Kode</th><th>Pembeli</th><th>Total</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (!$recentOrders): ?>
          <tr><td colspan="5" class="empty-note">Belum ada pesanan.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td><b><?= e($order['kode']) ?></b><br><small><?=
                e(format_tanggal($order['created_at'])) ?></small></td>
            <td><?= e($order['nama_pembeli']) ?></td>
            <td><?= rupiah($order['total']) ?></td>
            <td><?= status_badge($order['status']) ?></td>
            <td><a class="btn-sm btn-sm-ghost"
                href="<?= url('admin/pesanan/detail.php?id=' . (int) $order['id']) ?>">Detail</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2>Pesan Kontak Terbaru</h2>
      <a class="btn-sm btn-sm-ghost" href="<?= url('admin/pesan/index.php') ?>">Lihat semua</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Nama</th><th>Pesan</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (!$recentMessages): ?>
          <tr><td colspan="4" class="empty-note">Belum ada pesan.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentMessages as $msg): ?>
          <tr>
            <td><b><?= e($msg['nama']) ?></b><br><small><?=
                e(format_tanggal($msg['created_at'])) ?></small></td>
            <td><?= e(mb_strimwidth($msg['pesan'], 0, 45, '…')) ?></td>
            <td><?= $msg['dibaca'] ? '<span class="badge badge-selesai">Dibaca</span>' :
                '<span class="badge badge-baru">Baru</span>' ?></td>
            <td><a class="btn-sm btn-sm-ghost"
                href="<?= url('admin/pesan/detail.php?id=' . (int) $msg['id']) ?>">Lihat</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
