<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesanan.view');

$statuses = pesanan_statuses();
$filter = $_GET['status'] ?? '';
if ($filter !== '' && !isset($statuses[$filter])) {
    $filter = '';
}
$q = clean_text($_GET['q'] ?? '', 80);

$pdo = db();
$where = [];
$params = [];

if ($filter !== '') {
    $where[] = 'p.status = ?';
    $params[] = $filter;
}
if ($q !== '') {
    $where[] = '(p.kode LIKE ? OR p.nama_pembeli LIKE ? OR p.no_wa LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}

$sql = 'SELECT p.*, u.username AS akun_username
        FROM pesanan p
        LEFT JOIN users u ON u.id = p.user_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.created_at DESC, p.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$adminTitle  = 'Kelola Pesanan';
$adminActive = 'pesanan';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Daftar Pesanan (<?= count($orders) ?>)</h2>
    <div class="filter-bar">
      <a class="<?= $filter === '' ? 'active' : '' ?>"
          href="<?= url('admin/pesanan/index.php') ?>">Semua</a>
      <?php foreach ($statuses as $key => $label): ?>
      <a class="<?= $filter === $key ? 'active' : '' ?>"
          href="<?= url('admin/pesanan/index.php?status=' . $key) ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="admin-table-wrap">
    <form method="get" class="table-search">
      <?php if ($filter !== ''): ?><input type="hidden" name="status"
          value="<?= e($filter) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= e($q) ?>"
          placeholder="Cari kode, nama, atau WhatsApp…" aria-label="Cari pesanan">
      <button class="btn-sm btn-sm-ghost" type="submit">Cari</button>
    </form>

    <table class="admin-table">
      <thead>
        <tr><th>Kode</th><th>Pembeli</th><th>Akun</th><th>Bayar</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$orders): ?>
        <tr><td colspan="8" class="empty-note">Belum ada pesanan pada filter ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><b><?= e($o['kode']) ?></b></td>
          <td><?= e($o['nama_pembeli']) ?><br><small><?= e($o['no_wa']) ?></small></td>
          <td><?= $o['akun_username'] ? '<span class="badge badge-akun">@' .
              e($o['akun_username']) . '</span>' : '<small>Tamu</small>' ?></td>
          <td><span class="badge badge-<?= e($o['pembayaran']) ?>"><?= $o['pembayaran'] ===
              'transfer' ? 'Transfer' : 'COD' ?></span></td>
          <td><?= rupiah($o['total']) ?></td>
          <td><?= status_badge($o['status']) ?></td>
          <td><small><?= e(format_tanggal($o['created_at'])) ?></small></td>
          <td>
            <div class="actions">
              <a class="btn-sm btn-sm-ghost"
                  href="<?= url('admin/pesanan/detail.php?id=' . (int) $o['id']) ?>">Detail</a>
              <?php if (user_can('pesanan.hapus')): ?>
              <form method="post" action="<?= url('admin/pesanan/delete.php') ?>"
                  onsubmit="return confirm('Hapus pesanan <?= e(addslashes($o['kode'])) ?>?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                <button class="btn-sm btn-sm-danger" type="submit">Hapus</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
