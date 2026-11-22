<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$q = clean_text($_GET['q'] ?? '', 80);
$roleFilter = $_GET['role'] ?? '';
if ($roleFilter !== '' && !isset(roles()[$roleFilter])) {
    $roleFilter = '';
}

$sql = 'SELECT u.*, COUNT(p.id) AS jumlah_pesanan
        FROM users u
        LEFT JOIN pesanan p ON p.user_id = u.id';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(u.username LIKE ? OR u.nama LIKE ? OR u.email LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($roleFilter !== '') {
    $where[] = 'u.role = ?';
    $params[] = $roleFilter;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' GROUP BY u.id ORDER BY FIELD(u.role, "admin", "operator", "pelanggan"), u.nama ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$adminTitle  = 'Kelola Pengguna';
$adminActive = 'pengguna';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Daftar Pengguna (<?= count($users) ?>)</h2>
    <div class="panel-tools">
      <form method="get" class="inline-search">
        <?php if ($roleFilter !== ''): ?><input type="hidden" name="role"
            value="<?= e($roleFilter) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($q) ?>"
            placeholder="Cari nama / username / email…" aria-label="Cari pengguna">
        <button class="btn-sm btn-sm-ghost" type="submit">Cari</button>
      </form>
      <a class="btn-sm btn-sm-primary" href="<?= url('admin/pengguna/form.php') ?>">+ Tambah
          Pengguna</a>
    </div>
  </div>

  <div class="filter-bar filter-bar-panel">
    <a class="<?= $roleFilter === '' ? 'active' : '' ?>"
        href="<?= url('admin/pengguna/index.php') ?>">Semua</a>
    <?php foreach (roles() as $key => $label): ?>
    <a class="<?= $roleFilter === $key ? 'active' : '' ?>"
        href="<?= url('admin/pengguna/index.php?role=' . $key) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Nama</th><th>Username</th><th>Email</th><th>Peran</th><th>Pesanan</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$users): ?>
        <tr><td colspan="8" class="empty-note">Tidak ada pengguna yang cocok.</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><b><?= e($u['nama']) ?></b><?= (int) $u['id'] === (int) current_user()['id'] ?
              ' <small>(Anda)</small>' : '' ?></td>
          <td>@<?= e($u['username']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="role-badge role-<?= e($u['role']) ?>"><?= e(role_label((string)
              $u['role'])) ?></span></td>
          <td><?= (int) $u['jumlah_pesanan'] ?></td>
          <td><?= $u['aktif'] ? '<span class="badge badge-aktif">Aktif</span>' :
              '<span class="badge badge-nonaktif">Nonaktif</span>' ?></td>
          <td><small><?= e(format_tanggal($u['last_login_at'])) ?></small></td>
          <td><a class="btn-sm btn-sm-ghost"
              href="<?= url('admin/pengguna/form.php?id=' . (int) $u['id']) ?>">Edit</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="admin-note">Akun tidak dihapus permanen agar riwayat pesanan dan audit log tetap utuh.
    Nonaktifkan akun untuk mencabut akses.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
