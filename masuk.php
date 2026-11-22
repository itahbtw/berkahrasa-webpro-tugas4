<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_staff() ? url('admin/index.php') : url('akun.php'));
}

$pageTitle = 'Masuk';
$pageDesc  = 'Masuk ke akun Berkah Rasa untuk melihat riwayat pesanan dan checkout lebih cepat.';
$active    = '';

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = clean_text($_POST['username'] ?? '', 60);
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    $result = attempt_login($username, $password);

    if ($result['ok'] && $result['user'] !== null) {
        login_user($result['user'], $remember);
        set_flash('success', 'Selamat datang kembali, ' . $result['user']['nama'] . '.');

        redirect(url('index.php'));
    }

    $errors[] = $result['error'] ?? 'Gagal masuk.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Masuk</div>
    <h1>Masuk ke Akun</h1>
    <p>Kelola profil dan pantau status pesanan Anda di satu tempat.</p>
  </div>
</section>

<main class="section">
  <div class="container auth-wrap">
    <div class="auth-card reveal">
      <h2>Form Masuk</h2>

      <?php if ($errors): ?>
      <div class="flash flash-error">
        <?php foreach ($errors as $error): ?>
        <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="post" class="auth-form" autocomplete="on">
        <?= csrf_field() ?>
        <div class="form-field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?= e($username) ?>" required
              maxlength="60" autocomplete="username" autofocus>
        </div>
        <div class="form-field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required maxlength="72"
              autocomplete="current-password">
        </div>
        <label class="check-row">
          <input type="checkbox" name="remember" value="1">
          <span>Ingat saya selama <?= REMEMBER_DAYS ?> hari</span>
        </label>
        <button class="btn btn-block" type="submit">Masuk</button>
      </form>

      <p class="auth-switch">Belum punya akun? <a href="<?= url('daftar.php') ?>">Daftar
          gratis</a></p>
      <p class="auth-note">Lupa password? Hubungi admin di <a
          href="https://wa.me/<?= e(WA_NUMBER) ?>" target="_blank" rel="noopener">WhatsApp</a>
              untuk reset.</p>

      <?php if (APP_DEBUG): ?>
      <div class="demo-box">
        <strong>Akun demo (lingkungan lokal)</strong>
        <ul>
          <li>Pelanggan: <code>budi01</code> / <code>Budi#2026</code></li>
          <li>Operator: <code>operator01</code> / <code>Operator#2026</code></li>
          <li>Admin: <code>admin</code> / <code>admin123</code></li>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <div class="auth-side reveal d1">
      <h3>Kenapa perlu akun?</h3>
      <ul class="list-check">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Riwayat pesanan tersimpan otomatis.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Checkout lebih cepat karena data terisi.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Pantau status pesanan secara mandiri.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Sesi login dilindungi cookie HttpOnly.</li>
      </ul>
      <p class="auth-small">Dengan masuk, Anda menyetujui penggunaan cookie sesi sesuai <a
          href="<?= url('tentang.php#privasi') ?>">kebijakan privasi</a>.</p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
