<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$target = null;

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $target = $stmt->fetch();

    if (!$target) {
        set_flash('error', 'Pengguna tidak ditemukan.');
        redirect(url('admin/pengguna/index.php'));
    }
}

$isSelf = $isEdit && (int) $target['id'] === (int) current_user()['id'];

$form = [
    'username' => $target['username'] ?? '',
    'nama'     => $target['nama'] ?? '',
    'email'    => $target['email'] ?? '',
    'no_wa'    => $target['no_wa'] ?? '',
    'role'     => $target['role'] ?? 'pelanggan',
    'aktif'    => $target['aktif'] ?? 1,
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['username'] = clean_text($_POST['username'] ?? '', 60);
    $form['nama']     = clean_text($_POST['nama'] ?? '', 120);
    $form['email']    = mb_strtolower(clean_text($_POST['email'] ?? '', 150));
    $form['no_wa']    = clean_text($_POST['no_wa'] ?? '', 30);
    $form['role']     = clean_text($_POST['role'] ?? '', 20);
    $form['aktif']    = isset($_POST['aktif']) ? 1 : 0;

    $password  = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if (!valid_username($form['username'])) {
        $errors[] = 'Username 4–20 karakter, diawali huruf, hanya huruf/angka/garis bawah.';
    }
    if (!valid_name($form['nama'])) {
        $errors[] = 'Nama lengkap 3–120 karakter.';
    }
    if (!valid_email($form['email'])) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($form['no_wa'] !== '' && !valid_phone($form['no_wa'])) {
        $errors[] = 'Nomor WhatsApp tidak valid.';
    }
    if (!isset(roles()[$form['role']])) {
        $errors[] = 'Peran tidak valid.';
    }

    if ($isSelf) {
        $form['role'] = $target['role'];
        $form['aktif'] = 1;
    }

    // Jangan sampai tidak ada administrator aktif yang tersisa.
    if ($isEdit && $target['role'] === 'admin' && (int) $target['aktif'] === 1
        && ($form['role'] !== 'admin' || $form['aktif'] === 0)) {
        $activeAdmins = (int)
            db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND aktif = 1")->fetchColumn();
        if ($activeAdmins <= 1) {
            $errors[] = 'Tidak dapat menonaktifkan atau menurunkan administrator aktif terakhir.';
        }
    }

    $stmt =
        db()->prepare('SELECT id, username, email FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
    $stmt->execute([$form['username'], $form['email'], $id]);
    if ($existing = $stmt->fetch()) {
        if (strcasecmp((string) $existing['username'], $form['username']) === 0) {
            $errors[] = 'Username sudah dipakai.';
        } else {
            $errors[] = 'Email sudah dipakai.';
        }
    }

    if (!$isEdit && $password === '') {
        $errors[] = 'Password wajib diisi untuk pengguna baru.';
    }
    if ($password !== '') {
        if (($passError = password_error($password)) !== null) {
            $errors[] = $passError;
        }
        if (!hash_equals($password, $password2)) {
            $errors[] = 'Konfirmasi password tidak sama.';
        }
    }

    if (!$errors) {
        try {
            if ($isEdit) {
                $sql =
                    'UPDATE users SET username = ?, nama = ?, email = ?, no_wa = ?, role = ?, aktif = ?';
                $params = [$form['username'], $form['nama'], $form['email'], $form['no_wa'] !==
                    '' ? $form['no_wa'] : null, $form['role'], $form['aktif']];

                if ($password !== '') {
                    $sql .= ', password = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }

                $sql .= ' WHERE id = ?';
                $params[] = $id;
                db()->prepare($sql)->execute($params);

                if ($password !== '' || $form['aktif'] === 0) {
                    db()->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$id]);
                }

                log_activity((int) current_user()['id'], 'pengguna.ubah', 'Pengguna #' . $id .
                    ' (' . $form['username'] . ') peran=' . $form['role']);
                set_flash('success', 'Data pengguna berhasil diperbarui.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO users (username, nama, email, no_wa, password, role, aktif)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $form['username'],
                    $form['nama'],
                    $form['email'],
                    $form['no_wa'] !== '' ? $form['no_wa'] : null,
                    password_hash($password, PASSWORD_DEFAULT),
                    $form['role'],
                    $form['aktif'],
                ]);
                log_activity((int) current_user()['id'], 'pengguna.tambah', 'Pengguna baru: ' .
                    $form['username'] . ' (' . $form['role'] . ')');
                set_flash('success', 'Pengguna baru berhasil dibuat.');
            }

            redirect(url('admin/pengguna/index.php'));
        } catch (PDOException $e) {
            error_log('[pengguna] ' . $e->getMessage());
            $errors[] = 'Gagal menyimpan pengguna. Periksa kembali data yang diisi.';
        }
    }
}

$adminTitle  = $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
$adminActive = 'pengguna';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2><?= $isEdit ? 'Edit Pengguna: @' . e($target['username']) : 'Pengguna Baru' ?></h2>
    <a class="btn-sm btn-sm-ghost" href="<?= url('admin/pengguna/index.php') ?>">&larr; Kembali</a>
  </div>
  <div class="panel-body">
    <?php if ($errors): ?>
    <div class="flash flash-error">
      <ul>
        <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="post" class="admin-form">
      <?= csrf_field() ?>

      <div class="form-row">
        <div>
          <label for="nama">Nama Lengkap <span class="required">*</span></label>
          <input type="text" id="nama" name="nama" value="<?= e($form['nama']) ?>" required
              maxlength="120">
        </div>
        <div>
          <label for="username">Username <span class="required">*</span></label>
          <input type="text" id="username" name="username" value="<?= e($form['username']) ?>"
              required maxlength="20">
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="email">Email <span class="required">*</span></label>
          <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required
              maxlength="150">
        </div>
        <div>
          <label for="no_wa">Nomor WhatsApp</label>
          <input type="tel" id="no_wa" name="no_wa" value="<?= e($form['no_wa']) ?>" maxlength="30">
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="role">Peran <span class="required">*</span></label>
          <select id="role" name="role" <?= $isSelf ? 'disabled' : '' ?>>
            <?php foreach (roles() as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $form['role'] === $key ? 'selected' : '' ?>><?=
                e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($isSelf): ?>
          <input type="hidden" name="role" value="<?= e($form['role']) ?>">
          <p class="form-hint">Anda tidak dapat mengubah peran akun sendiri.</p>
          <?php endif; ?>
        </div>
        <div>
          <label>Status Akun</label>
          <label class="check-row form-spacer">
            <input type="checkbox" name="aktif" value="1" <?= $form['aktif'] ? 'checked' : '' ?>
                <?= $isSelf ? 'disabled' : '' ?>>
            <span>Aktif (dapat masuk)</span>
          </label>
          <?php if ($isSelf): ?><input type="hidden" name="aktif" value="1"><?php endif; ?>
        </div>
      </div>

      <hr class="admin-divider">

      <div class="form-row">
        <div>
          <label for="password"><?= $isEdit ? 'Password Baru (opsional)' : 'Password' ?> <?=
              $isEdit ? '' : '<span class="required">*</span>' ?></label>
          <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?>
              maxlength="72" autocomplete="new-password">
          <p class="form-hint">Minimal 8 karakter, memuat huruf dan angka.</p>
        </div>
        <div>
          <label for="password2">Ulangi Password</label>
          <input type="password" id="password2" name="password2" <?= $isEdit ? '' : 'required'
              ?> maxlength="72" autocomplete="new-password">
        </div>
      </div>

      <?php if ($isEdit): ?>
      <p class="form-hint">Mengisi password baru akan mencabut semua cookie “ingat saya”
          pengguna ini.</p>
      <?php endif; ?>

      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna'
            ?></button>
        <a class="btn btn-ghost" href="<?= url('admin/pengguna/index.php') ?>">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
