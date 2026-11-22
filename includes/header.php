<?php
if (!function_exists('e')) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/functions.php';
}

$pageTitle = $pageTitle ?? APP_NAME;
$pageDesc  = $pageDesc ?? 'UMKM camilan singkong asli desa. Renyah, gurih, dan penuh berkah.';
$active    = $active ?? '';
$loggedInUser = current_user();

// Tutup promo sepenuhnya di server agar tidak berkedip saat pindah halaman.
if (isset($_GET['promo']) && $_GET['promo'] === 'close') {
    setcookie('berkah_promo_closed', '1', time() + (30 * 86400), '/');
    $params = $_GET;
    unset($params['promo']);
    $path = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';
    header('Location: ' . $path . ($params ? '?' . http_build_query($params) : ''));
    exit;
}

$promoParams = $_GET;
$promoParams['promo'] = 'close';
$promoPath = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';
$promoCloseUrl = htmlspecialchars($promoPath . '?' . http_build_query($promoParams), ENT_QUOTES,
    'UTF-8');

$navItems = [
    'index'      => ['index.php', 'Beranda',
        '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
    'produk'     => ['produk.php', 'Produk',
        '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
    'cara'       => ['cara-memesan.php', 'Cara Memesan',
        '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>'],
    'kontak'     => ['kontak.php', 'Kontak',
        '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>'],
    'tentang'    => ['tentang.php', 'Tentang Kami',
        '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= url('gambar/logo-icon.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('style.css') ?>">
</head>
<body class="<?= theme_preference() === 'dark' ? 'dark' : '' ?>">
<?php if (empty($_COOKIE['berkah_promo_closed'])): ?>
<div class="topbar" id="topbarPromo">
  <div class="topbar-inner">
    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15"
            height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5"
                cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg><span
                    class="topbar-long">Gratis ongkir area desa untuk pembelian min.
                        Rp100.000</span><span class="topbar-short">Gratis ongkir min.
                            Rp100.000</span></span>
    <span class="topbar-contact"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 0812-3456-7890</span>
    <a class="topbar-close" href="<?= $promoCloseUrl ?>" aria-label="Tutup promo"
        title="Tutup promo">&times;</a>
  </div>
</div>
<?php endif; ?>

<header class="site-header">
  <div class="container header-inner">
    <button class="nav-toggle" type="button" onclick="toggleNav()" aria-label="Buka menu"
        aria-controls="mainNav"
            aria-expanded="false"><span></span><span></span><span></span></button>
    <a class="brand" href="<?= url('index.php') ?>">
      <img src="<?= url('gambar/logo-icon.svg') ?>" alt="Logo <?= e(APP_NAME) ?>">
      <span class="brand-text">
        <strong class="brand-site-name"><?= e(APP_NAME) ?></strong>
        <strong class="brand-page-name"><?= e($navItems[$active][1] ?? APP_NAME) ?></strong>
      </span>
    </a>
    <nav class="main-nav" id="mainNav">
      <?php foreach ($navItems as $key => [$href, $label, $icon]): ?>
      <a href="<?= url($href) ?>" class="nav-link<?= $active === $key ? ' active' : '' ?>"><svg
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg><span><?=
                  e($label) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <div class="header-actions">
      <button class="icon-btn" id="darkToggle" onclick="toggleDark()"
          aria-label="Mode gelap"><?php if (theme_preference() === 'dark'): ?><svg
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12"
                      r="4"/><path
                          d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg><?php endif; ?></button>
      <?php if ($loggedInUser): ?>
      <button class="icon-btn cart-btn" onclick="openCart()" aria-label="Keranjang"><svg
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21"
                  r="1"/><circle cx="20" cy="21" r="1"/><path
                      d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span class="cart-count" id="cartCount">0</span></button>
      <?php if (is_staff()): ?>
      <a class="icon-btn admin-link" href="<?= url('admin/index.php') ?>" title="Panel admin"
          aria-label="Panel admin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3"
                  width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14"
                      y="14" width="7" height="7"/><rect x="3" y="14" width="7"
                          height="7"/></svg></a>
      <?php endif; ?>
      <div class="account-menu-wrap">
        <button type="button" class="account-chip" onclick="toggleAccountMenu()"
            aria-haspopup="true" aria-expanded="false" aria-label="Menu akun">
          <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr($loggedInUser['nama'], 0, 1)))
              ?></span>
          <span class="account-name"><?= e(mb_strimwidth($loggedInUser['nama'], 0, 18, '…'))
              ?></span>
        </button>
        <div class="account-dropdown" id="accountDropdown">
          <div class="account-dropdown-head">
            <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr($loggedInUser['nama'], 0,
                1))) ?></span>
            <span><b><?= e($loggedInUser['nama']) ?></b><small>@<?= e($loggedInUser['username'])
                ?></small></span>
          </div>
          <a href="<?= url('akun.php') ?>">Profil Saya</a>
          <a href="<?= url('pesanan-saya.php') ?>">Pesanan Saya</a>
          <a href="<?= url('lacak-pesanan.php') ?>">Lacak Pesanan</a>
          <form method="post" action="<?= url('keluar.php') ?>">
            <?= csrf_field() ?>
            <button type="submit">Keluar</button>
          </form>
        </div>
      </div>
      <?php else: ?>
      <button class="icon-btn cart-btn" onclick="openCart()" aria-label="Keranjang"><svg
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21"
                  r="1"/><circle cx="20" cy="21" r="1"/><path
                      d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span class="cart-count" id="cartCount">0</span></button>
      <a class="login-link" href="<?= url('masuk.php') ?>" aria-label="Masuk">
        <span>Masuk</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php if ($flash = get_flash()): ?>
<div class="container">
  <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
</div>
<?php endif; ?>
