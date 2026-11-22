<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_staff()) {
    redirect(url('admin/index.php'));
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = clean_text($_POST['username'] ?? '', 60);
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    $result = attempt_login($username, $password, true);

    if ($result['ok'] && $result['user'] !== null) {
        login_user($result['user'], $remember);
        set_flash('success', 'Selamat datang, ' . $result['user']['nama'] . '.');

        $next = take_intended_url(url('admin/index.php'));
        if (!str_contains($next, '/admin/')) {
            $next = url('admin/index.php');
        }

        redirect($next);
    }

    $errors[] = $result['error'] ?? 'Gagal masuk.';
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin - <?= e(APP_NAME) ?></title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= url('gambar/logo-icon.svg') ?>">
<link rel="stylesheet" href="<?= url('style.css') ?>">
<link rel="stylesheet" href="<?= url('admin/assets/admin.css') ?>">
</head>
<body class="<?= theme_preference() === 'dark' ? 'dark' : '' ?>">
<button type="button" class="icon-btn login-dark" id="darkToggle" onclick="toggleDark()"
    aria-label="Mode gelap"><?php if (theme_preference() === 'dark'): ?><svg viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path
                d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg><?php endif; ?></button>
<div class="login-wrap">
  <div class="login-card">
    <div class="brand-login">
      <img src="<?= url('gambar/logo-icon.svg') ?>" alt="Logo <?= e(APP_NAME) ?>">
      <div>
        <h1>Admin <?= e(APP_NAME) ?></h1>
        <p class="sub">Masuk untuk mengelola produk, pesanan, dan pesan.</p>
      </div>
    </div>

    <?php if ($flash = get_flash()): ?>
    <div class="flash flash-<?= e($flash['type']) ?> flash-login"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
    <div class="login-error">
      <?php foreach ($errors as $error): ?>
      <p><?= e($error) ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" class="admin-form">
      <?= csrf_field() ?>
      <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" required
            autofocus autocomplete="username" maxlength="60">
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required
            autocomplete="current-password" maxlength="72">
      </div>
      <label class="check-row admin-check">
        <input type="checkbox" name="remember" value="1">
        <span>Ingat saya selama <?= REMEMBER_DAYS ?> hari</span>
      </label>
      <button class="btn" type="submit">Masuk</button>
    </form>

    <div class="login-links">
      <a class="login-back" href="<?= url('index.php') ?>">&larr; Kembali ke situs</a>
      <a class="login-back" href="<?= url('masuk.php') ?>">Masuk sebagai pelanggan</a>
    </div>

    <?php if (APP_DEBUG): ?>
    <div class="demo-box">
      <strong>Akun demo (lingkungan lokal)</strong>
      <ul>
        <li>Admin: <code>admin</code> / <code>admin123</code></li>
        <li>Operator: <code>operator01</code> / <code>Operator#2026</code></li>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>
<script src="<?= url('script.js') ?>" defer></script>
</body>
</html>
