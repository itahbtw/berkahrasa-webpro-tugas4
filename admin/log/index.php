<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$q = clean_text($_GET['q'] ?? '', 80);
$aksi = clean_text($_GET['aksi'] ?? '', 60);

$sql = 'SELECT l.*, u.username, u.nama AS user_nama
        FROM activity_log l
        LEFT JOIN users u ON u.id = l.user_id';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(u.username LIKE ? OR l.detail LIKE ? OR l.ip LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($aksi !== '') {
    $where[] = 'l.aksi = ?';
    $params[] = $aksi;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY l.created_at DESC, l.id DESC LIMIT 200';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$actions =
    db()->query('SELECT DISTINCT aksi FROM activity_log ORDER BY aksi ASC')->fetchAll(PDO::FETCH_COLUMN);

$adminTitle  = 'Log Aktivitas';
$adminActive = 'log';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Audit Log (<?= count($logs) ?> terbaru)</h2>
    <form method="get" class="inline-search">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari user / detail / IP…"
          aria-label="Cari log">
      <select name="aksi" aria-label="Filter aksi">
        <option value="">Semua aksi</option>
        <?php foreach ($actions as $action): ?>
        <option value="<?= e($action) ?>" <?= $aksi === $action ? 'selected' : '' ?>><?=
            e($action) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn-sm btn-sm-ghost" type="submit">Filter</button>
    </form>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Detail</th><th>IP</th></tr>
      </thead>
      <tbody>
        <?php if (!$logs): ?>
        <tr><td colspan="5" class="empty-note">Belum ada aktivitas yang cocok.</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $log): ?>
        <tr>
          <td><small><?= e(format_tanggal($log['created_at'], 'd/m/Y H:i:s')) ?></small></td>
          <td>
            <?php if ($log['username']): ?>
            <b>@<?= e($log['username']) ?></b><br><small><?= e($log['user_nama']) ?></small>
            <?php else: ?>
            <small>Tamu / sistem</small>
            <?php endif; ?>
          </td>
          <td><span class="log-action"><?= e($log['aksi']) ?></span></td>
          <td><?= e($log['detail'] ?: '-') ?></td>
          <td><code><?= e($log['ip']) ?></code></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="admin-note">Log mencatat login berhasil/gagal, perubahan data, perubahan status, dan
    akses yang ditolak. Log lama dibersihkan bersama data percobaan login setiap 7 hari.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
