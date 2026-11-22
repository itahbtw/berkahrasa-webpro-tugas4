<?php
$adminTitle  = $adminTitle ?? 'Dashboard';
$adminActive = $adminActive ?? '';
$adminUser   = current_user();

$adminMenu = [
    'dashboard' => ['index.php', 'Dashboard',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>', 'dashboard.view'],
    'produk'    => ['produk/index.php', 'Produk',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>', 'produk.view'],
    'pesanan'   => ['pesanan/index.php', 'Pesanan',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>', 'pesanan.view'],
    'pesan'     => ['pesan/index.php', 'Pesan Kontak',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>', 'pesan.view'],
    'pengguna'  => ['pengguna/index.php', 'Pengguna',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'admin'],
    'log'       => ['log/index.php', 'Log Aktivitas',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>', 'admin'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($adminTitle) ?> - Admin <?= e(APP_NAME) ?></title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= url('gambar/logo-icon.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('style.css') ?>">
<link rel="stylesheet" href="<?= asset('admin/assets/admin.css') ?>">
</head>
<body class="admin-body <?= theme_preference() === 'dark' ? 'dark' : '' ?>">

<header class="admin-header">
  <div class="admin-header-inner">
    <button class="admin-nav-toggle" type="button" onclick="toggleAdminNav()"
        aria-label="Buka menu admin"
            aria-expanded="false"><span></span><span></span><span></span></button>
    <a class="admin-brand" href="<?= url('admin/index.php') ?>">
      <img src="<?= url('gambar/logo-icon.svg') ?>" alt="Logo <?= e(APP_NAME) ?>">
      <span class="admin-brand-text">
        <span class="admin-brand-app"><?= e(APP_NAME) ?> <small class="admin-brand-sub">Admin
            Panel</small></span>
        <span class="admin-brand-site"><?= e($adminMenu[$adminActive][1] ?? 'Dashboard') ?></span>
      </span>
    </a>
    <nav class="admin-nav" id="adminNav">
      <?php foreach ($adminMenu as $key => [$href, $label, $icon, $permission]): ?>
        <?php if ($permission === 'admin' ? is_admin() : user_can($permission)): ?>
        <a href="<?= url('admin/' . $href) ?>"
            class="<?= $adminActive === $key ? 'active' : '' ?>"><span class="ic"><?= $icon
                ?></span><?= e($label) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="admin-user">
      <button type="button" class="icon-btn" id="darkToggle" onclick="toggleDark()"
          aria-label="Mode gelap"><?php if (theme_preference() === 'dark'): ?><svg
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12"
                      r="4"/><path
                          d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg><?php endif; ?></button>
      <a class="icon-btn" href="<?= url('index.php') ?>" target="_blank" rel="noopener"
          aria-label="Lihat situs" title="Lihat Situs"><svg viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round"><path
                      d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline
                          points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
      <div class="admin-profile-wrap">
        <button type="button" class="admin-who" onclick="toggleAdminProfile()"
            aria-haspopup="true" aria-expanded="false" aria-label="Menu profil">
          <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr((string) ($adminUser['nama']
              ?? 'S'), 0, 1))) ?></span>
          <span class="admin-who-text">
            <b><?= e($adminUser['nama'] ?? 'Staf') ?></b>
            <small><?= e(role_label((string) ($adminUser['role'] ?? 'operator'))) ?></small>
          </span>
        </button>
        <div class="admin-profile-dropdown" id="adminProfileDropdown">
          <div class="account-dropdown-head">
            <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr((string) ($adminUser['nama']
                ?? 'S'), 0, 1))) ?></span>
            <span><b><?= e($adminUser['nama'] ?? 'Staf') ?></b><small><?= e(role_label((string)
                ($adminUser['role'] ?? 'operator'))) ?></small></span>
          </div>
          <a href="<?= url('akun.php') ?>">Profil Saya</a>
          <?php if (is_admin() && env_bool('ENABLE_ADMINNEO', false)): ?>
          <a href="<?= url('adminneo.php') ?>" target="_blank" rel="noopener">AdminNeo</a>
          <?php endif; ?>
          <form method="post" action="<?= url('admin/logout.php') ?>">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<main class="admin-main">
  <div class="admin-page-head">
    <h1><?= e($adminTitle) ?></h1>
  </div>

  <?php if ($flash = get_flash()): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>
