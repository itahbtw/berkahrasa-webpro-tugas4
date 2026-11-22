<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_staff() ? url('admin/index.php') : url('akun.php'));
}

$pageTitle = 'Daftar Akun';
$pageDesc  = 'Buat akun Berkah Rasa untuk riwayat pesanan dan checkout yang lebih cepat.';
$active    = '';

$errors = [];
$form = [
    'username' => '',
    'nama'     => '',
    'email'    => '',
    'no_wa'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Honeypot: bot biasanya mengisi kolom tersembunyi.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        set_flash('success', 'Pendaftaran berhasil. Silakan masuk.');
        redirect(url('masuk.php'));
    }

    $lastRegister = (int) ($_SESSION['last_register_at'] ?? 0);
    if ($lastRegister > 0 && (time() - $lastRegister) < 20) {
        $errors[] = 'Terlalu cepat. Tunggu beberapa detik sebelum mendaftar lagi.';
    }

    $form['username'] = clean_text($_POST['username'] ?? '', 60);
    $form['nama']     = clean_text($_POST['nama'] ?? '', 120);
    $form['email']    = mb_strtolower(clean_text($_POST['email'] ?? '', 150));
    $form['no_wa']    = clean_text($_POST['no_wa'] ?? '', 30);
    $password         = (string) ($_POST['password'] ?? '');
    $password2        = (string) ($_POST['password2'] ?? '');

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
        $errors[] = 'Nomor WhatsApp tidak valid (contoh: 081234567890).';
    }
    if (($passError = password_error($password)) !== null) {
        $errors[] = $passError;
    }
    if (!hash_equals($password, $password2)) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }
    if (!isset($_POST['setuju'])) {
        $errors[] = 'Anda harus menyetujui kebijakan privasi.';
    }

    if (!$errors) {
        $stmt =
            db()->prepare('SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$form['username'], $form['email']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $errors[] = strcasecmp((string) $existing['username'], $form['username']) === 0
                ? 'Username sudah dipakai. Pilih username lain.'
                : 'Email sudah terdaftar. Gunakan email lain atau masuk.';
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO users (username, nama, email, no_wa, password, role, aktif)
                 VALUES (?, ?, ?, ?, ?, "pelanggan", 1)'
            );
            $stmt->execute([
                $form['username'],
                $form['nama'],
                $form['email'],
                $form['no_wa'] !== '' ? $form['no_wa'] : null,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([(int) db()->lastInsertId()]);
            $user = $stmt->fetch();

            if ($user) {
                login_user($user, false);
                $_SESSION['last_register_at'] = time();
                log_activity((int) $user['id'], 'daftar', 'Pendaftaran akun pelanggan.');
                set_flash('success', 'Akun berhasil dibuat. Selamat datang, ' . $user['nama'] .
                    '!');
                redirect(url('akun.php'));
            }

            $errors[] = 'Akun dibuat, tetapi gagal dimuat. Silakan masuk.';
        } catch (PDOException $e) {
            error_log('[register] ' . $e->getMessage());
            $errors[] = 'Pendaftaran gagal. Username atau email mungkin sudah dipakai.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Daftar</div>
    <h1>Buat Akun Baru</h1>
    <p>Gratis, cepat, dan memudahkan Anda memantau pesanan.</p>
  </div>
</section>

<main class="section">
  <div class="container auth-wrap">
    <div class="auth-card reveal">
      <h2>Form Pendaftaran</h2>

      <?php if ($errors): ?>
      <div class="flash flash-error">
        <?php foreach ($errors as $error): ?>
        <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="hp-field" aria-hidden="true">
          <label for="website">Website</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="form-field">
          <label for="nama">Nama Lengkap</label>
          <input type="text" id="nama" name="nama" value="<?= e($form['nama']) ?>" required
              maxlength="120" autocomplete="name">
        </div>
        <div class="form-field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?= e($form['username']) ?>"
              required maxlength="20" autocomplete="username" placeholder="contoh: budi01">
          <small class="form-hint">4–20 karakter, diawali huruf, tanpa spasi.</small>
        </div>
        <div class="form-field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required
              maxlength="150" autocomplete="email">
        </div>
        <div class="form-field">
          <label for="no_wa">Nomor WhatsApp (opsional)</label>
          <input type="tel" id="no_wa" name="no_wa" value="<?= e($form['no_wa']) ?>"
              maxlength="30" autocomplete="tel" placeholder="081234567890">
        </div>
        <div class="form-row-2">
          <div class="form-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required maxlength="72"
                autocomplete="new-password">
          </div>
          <div class="form-field">
            <label for="password2">Ulangi Password</label>
            <input type="password" id="password2" name="password2" required maxlength="72"
                autocomplete="new-password">
          </div>
        </div>
        <small class="form-hint form-hint-wide">Minimal 8 karakter, memuat huruf dan angka.</small>

        <label class="check-row">
          <input type="checkbox" name="setuju" value="1" required>
          <span>Saya menyetujui <a href="<?= url('tentang.php#privasi') ?>">kebijakan
              privasi</a> dan penggunaan cookie sesi.</span>
        </label>

        <button class="btn btn-block" type="submit">Daftar Sekarang</button>
      </form>

      <p class="auth-switch">Sudah punya akun? <a href="<?= url('masuk.php') ?>">Masuk di
          sini</a></p>
    </div>

    <div class="auth-side reveal d1">
      <h3>Data Anda aman</h3>
      <ul class="list-check">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Password disimpan sebagai hash bcrypt, bukan teks asli.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Form dilindungi token CSRF dan validasi server.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Hanya Anda yang dapat melihat detail pesanan sendiri.</li>
      </ul>
      <p class="auth-small">Kami tidak pernah meminta password melalui WhatsApp atau telepon.</p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
