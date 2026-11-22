<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
require_permission('akun.view');

$user = current_user(true);
$errors = [];
$activeTab = $_GET['tab'] ?? 'profil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'profil') {
        $activeTab = 'profil';
        $nama  = clean_text($_POST['nama'] ?? '', 120);
        $email = mb_strtolower(clean_text($_POST['email'] ?? '', 150));
        $noWa  = clean_text($_POST['no_wa'] ?? '', 30);

        if (!valid_name($nama)) {
            $errors[] = 'Nama lengkap 3–120 karakter.';
        }
        if (!valid_email($email)) {
            $errors[] = 'Format email tidak valid.';
        }
        if ($noWa !== '' && !valid_phone($noWa)) {
            $errors[] = 'Nomor WhatsApp tidak valid.';
        }

        if (!$errors) {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
            $stmt->execute([$email, (int) $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah dipakai akun lain.';
            }
        }

        if (!$errors) {
            $stmt = db()->prepare('UPDATE users SET nama = ?, email = ?, no_wa = ? WHERE id = ?');
            $stmt->execute([$nama, $email, $noWa !== '' ? $noWa : null, (int) $user['id']]);
            log_activity((int) $user['id'], 'profil.ubah', 'Memperbarui data profil.');
            set_flash('success', 'Profil berhasil diperbarui.');
            redirect(url('akun.php'));
        }
    }

    if ($action === 'password') {
        $activeTab = 'keamanan';
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!password_verify($current, (string) $user['password'])) {
            $errors[] = 'Password saat ini tidak benar.';
        }
        if (($passError = password_error($new)) !== null) {
            $errors[] = $passError;
        }
        if (!hash_equals($new, $confirm)) {
            $errors[] = 'Konfirmasi password baru tidak sama.';
        }
        if (password_verify($new, (string) $user['password'])) {
            $errors[] = 'Password baru harus berbeda dari password lama.';
        }

        if (!$errors) {
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), (int) $user['id']]);

            // Semua token "ingat saya" dicabut setelah password berubah.
            db()->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([(int)
                $user['id']]);
            clear_remember_cookie();

            log_activity((int) $user['id'], 'password.ubah', 'Mengganti password akun.');
            set_flash('success', 'Password berhasil diganti. Perangkat lain harus masuk ulang.');
            redirect(url('akun.php?tab=keamanan'));
        }
    }
}

$stats = [
    'total'  => 0,
    'aktif'  => 0,
    'belanja' => 0,
];

$stmt = db()->prepare(
    "SELECT COUNT(*) AS total,
            SUM(status IN ('baru','diproses','dikirim')) AS aktif,
            COALESCE(SUM(CASE WHEN status <> 'dibatalkan' THEN total ELSE 0 END), 0) AS belanja
     FROM pesanan WHERE user_id = ?"
);
$stmt->execute([(int) $user['id']]);
$row = $stmt->fetch();
if ($row) {
    $stats['total'] = (int) $row['total'];
    $stats['aktif'] = (int) $row['aktif'];
    $stats['belanja'] = (int) $row['belanja'];
}

$pageTitle = 'Akun Saya';
$pageDesc  = 'Kelola profil, keamanan, dan riwayat pesanan akun Berkah Rasa.';
$active    = '';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Akun</div>
    <h1>Akun Saya</h1>
    <p>Halo, <?= e($user['nama']) ?>. Kelola data akun dan pantau pesanan Anda.</p>
  </div>
</section>

<main class="section">
  <div class="container account-layout">
    <aside class="account-side reveal">
      <div class="account-avatar"><?= e(mb_strtoupper(mb_substr($user['nama'], 0, 1))) ?></div>
      <h2><?= e($user['nama']) ?></h2>
      <p class="account-username">@<?= e($user['username']) ?></p>
      <span class="role-badge role-<?= e($user['role']) ?>"><?= e(role_label((string)
          $user['role'])) ?></span>

      <ul class="account-meta">
        <li><span>Email</span><b><?= e($user['email']) ?></b></li>
        <li><span>WhatsApp</span><b><?= e($user['no_wa'] ?: '-') ?></b></li>
        <li><span>Bergabung</span><b><?= e(format_tanggal($user['created_at'], 'd M Y')) ?></b></li>
        <li><span>Login terakhir</span><b><?= e(format_tanggal($user['last_login_at'])) ?></b></li>
      </ul>

      <nav class="account-menu">
        <a class="<?= $activeTab === 'profil' ? 'active' : '' ?>"
            href="<?= url('akun.php') ?>">Profil</a>
        <a class="<?= $activeTab === 'keamanan' ? 'active' : '' ?>"
            href="<?= url('akun.php?tab=keamanan') ?>">Keamanan</a>
        <a href="<?= url('pesanan-saya.php') ?>">Pesanan Saya</a>
        <?php if (is_staff()): ?>
        <a href="<?= url('admin/index.php') ?>" target="_blank">Panel Admin</a>
        <?php endif; ?>
      </nav>

      <form method="post" action="<?= url('keluar.php') ?>" class="account-logout">
        <?= csrf_field() ?>
        <button class="btn btn-ghost btn-block" type="submit">Keluar</button>
      </form>
    </aside>

    <div class="account-main">
      <div class="stat-grid account-stats reveal">
        <div class="stat-card"><div class="label">Total Pesanan</div><div class="value"><?=
            $stats['total'] ?></div></div>
        <div class="stat-card"><div class="label">Pesanan Aktif</div><div class="value"><?=
            $stats['aktif'] ?></div></div>
        <div class="stat-card"><div class="label">Total Belanja</div><div
            class="value value-sm"><?= rupiah($stats['belanja']) ?></div></div>
      </div>

      <?php if ($errors): ?>
      <div class="flash flash-error reveal">
        <?php foreach ($errors as $error): ?>
        <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($activeTab === 'keamanan'): ?>
      <section class="content-card reveal">
        <h2>Keamanan Akun</h2>
        <p>Ganti password secara berkala. Setelah password berubah, semua cookie “ingat saya”
            milik akun ini dicabut.</p>
        <form method="post" class="auth-form">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="password">
          <div class="form-field">
            <label for="current_password">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required
                maxlength="72" autocomplete="current-password">
          </div>
          <div class="form-row-2">
            <div class="form-field">
              <label for="new_password">Password Baru</label>
              <input type="password" id="new_password" name="new_password" required
                  maxlength="72" autocomplete="new-password">
              <small class="form-hint">Minimal 8 karakter, memuat huruf dan angka.</small>
            </div>
            <div class="form-field">
              <label for="confirm_password">Ulangi Password Baru</label>
              <input type="password" id="confirm_password" name="confirm_password" required
                  maxlength="72" autocomplete="new-password">
            </div>
          </div>
          <button class="btn" type="submit">Simpan Password</button>
        </form>

        <div class="security-note">
          <h3>Perlindungan sesi</h3>
          <ul class="list-check">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Sesi berakhir otomatis setelah <?= (int) (SESSION_IDLE_TIMEOUT / 60) ?>
                        menit tanpa aktivitas.</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Cookie sesi memakai HttpOnly dan SameSite=Lax.</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    ID sesi diperbarui setelah login dan secara berkala.</li>
          </ul>
        </div>
      </section>
      <?php else: ?>
      <section class="content-card reveal">
        <h2>Data Profil</h2>
        <p>Email dan nomor WhatsApp dipakai untuk mempercepat checkout serta konfirmasi pesanan.</p>
        <form method="post" class="auth-form">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="profil">
          <div class="form-row-2">
            <div class="form-field">
              <label for="nama">Nama Lengkap</label>
              <input type="text" id="nama" name="nama" value="<?= e($user['nama']) ?>" required
                  maxlength="120" autocomplete="name">
            </div>
            <div class="form-field">
              <label for="username">Username</label>
              <input type="text" id="username" value="<?= e($user['username']) ?>" disabled>
              <small class="form-hint">Username tidak dapat diubah.</small>
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?= e($user['email']) ?>"
                  required maxlength="150" autocomplete="email">
            </div>
            <div class="form-field">
              <label for="no_wa">Nomor WhatsApp</label>
              <input type="tel" id="no_wa" name="no_wa" value="<?= e($user['no_wa'] ?? '') ?>"
                  maxlength="30" autocomplete="tel">
            </div>
          </div>
          <button class="btn" type="submit">Simpan Perubahan</button>
        </form>
      </section>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
