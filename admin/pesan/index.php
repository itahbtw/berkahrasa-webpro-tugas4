<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesan.view');

$pdo = db();
$messages =
    $pdo->query('SELECT * FROM pesan_kontak ORDER BY dibaca ASC, created_at DESC, id DESC')->fetchAll();
$unread = count(array_filter($messages, static fn (array $m): bool => (int) $m['dibaca'] === 0));

$adminTitle  = 'Pesan Kontak';
$adminActive = 'pesan';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Kotak Masuk (<?= count($messages) ?>)</h2>
    <span class="badge badge-baru"><?= $unread ?> belum dibaca</span>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Nama</th><th>Email</th><th>Pesan</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$messages): ?>
        <tr><td colspan="6" class="empty-note">Belum ada pesan masuk.</td></tr>
        <?php endif; ?>
        <?php foreach ($messages as $m): ?>
        <tr>
          <td><b><?= e($m['nama']) ?></b></td>
          <td><?= e($m['email'] ?: '-') ?></td>
          <td><?= e(mb_strimwidth($m['pesan'], 0, 60, '…')) ?></td>
          <td><?= $m['dibaca'] ? '<span class="badge badge-selesai">Dibaca</span>' :
              '<span class="badge badge-baru">Baru</span>' ?></td>
          <td><small><?= e(format_tanggal($m['created_at'])) ?></small></td>
          <td>
            <div class="actions">
              <a class="btn-sm btn-sm-ghost"
                  href="<?= url('admin/pesan/detail.php?id=' . (int) $m['id']) ?>">Lihat</a>
              <?php if (user_can('pesan.hapus')): ?>
              <form method="post" action="<?= url('admin/pesan/delete.php') ?>"
                  onsubmit="return confirm('Hapus pesan dari <?= e(addslashes($m['nama'])) ?>?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
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
