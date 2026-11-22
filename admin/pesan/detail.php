<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesan.view');

$id = (int) ($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM pesan_kontak WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    set_flash('error', 'Pesan tidak ditemukan.');
    redirect(url('admin/pesan/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('pesan.read');
    verify_csrf();

    if (($_POST['action'] ?? '') === 'dibaca' && !$message['dibaca']) {
        $pdo->prepare('UPDATE pesan_kontak SET dibaca = 1 WHERE id = ?')->execute([$id]);
        log_activity((int) current_user()['id'], 'pesan.dibaca', 'Pesan #' . $id . ' dari ' .
            $message['nama']);
        set_flash('success', 'Pesan ditandai sudah dibaca.');
    }

    redirect(url('admin/pesan/detail.php?id=' . $id));
}

$adminTitle  = 'Detail Pesan';
$adminActive = 'pesan';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Pesan dari <?= e($message['nama']) ?></h2>
    <a class="btn-sm btn-sm-ghost" href="<?= url('admin/pesan/index.php') ?>">&larr; Kembali</a>
  </div>
  <div class="panel-body">
    <ul class="detail-list">
      <li><span class="k">Nama</span><span class="v"><?= e($message['nama']) ?></span></li>
      <li><span class="k">Email</span><span class="v"><?= e($message['email'] ?: '-') ?></span></li>
      <li><span class="k">Tanggal</span><span class="v"><?=
          e(format_tanggal($message['created_at'])) ?></span></li>
      <li><span class="k">Status</span><span class="v"><?= $message['dibaca'] ? 'Dibaca' :
          'Baru' ?></span></li>
    </ul>

    <h3 class="detail-section-title">Isi Pesan</h3>
    <div class="msg-body"><?= nl2br(e($message['pesan'])) ?></div>

    <div class="detail-actions">
      <?php if ($message['email']): ?>
      <a class="btn-sm btn-sm-primary" href="mailto:<?= e($message['email']) ?>">Balas via Email</a>
      <?php endif; ?>

      <?php if (!$message['dibaca'] && user_can('pesan.read')): ?>
      <form method="post" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="dibaca">
        <button class="btn-sm btn-sm-ghost" type="submit">Tandai Dibaca</button>
      </form>
      <?php endif; ?>
    </div>

    <?php if (user_can('pesan.hapus')): ?>
    <form method="post" action="<?= url('admin/pesan/delete.php') ?>"
        onsubmit="return confirm('Hapus pesan ini?')" class="mt-1">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
      <button class="btn-sm btn-sm-danger" type="submit">Hapus Pesan</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
