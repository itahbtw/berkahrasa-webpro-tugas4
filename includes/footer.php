<?php
$footerProducts = products_for_js(get_products());
$footerUser     = current_user();
?><footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <img src="<?= url('gambar/logo-icon.svg') ?>" alt="Logo <?= e(APP_NAME) ?>">
      <p>UMKM camilan singkong asli desa. Renyah, gurih, dan penuh berkah — dikelola bersama
          warga desa Sumber Rejeki.</p>
      <p class="footer-badge">Tugas 4 · Session, Cookie &amp; Keamanan</p>
    </div>
    <div class="footer-col">
      <h4>Navigasi</h4>
      <ul>
        <li><a href="<?= url('index.php') ?>">Beranda</a></li>
        <li><a href="<?= url('produk.php') ?>">Produk</a></li>
        <li><a href="<?= url('cara-memesan.php') ?>">Cara Memesan</a></li>
        <li><a href="<?= url('kontak.php') ?>">Kontak</a></li>
        <li><a href="<?= url('tentang.php') ?>">Tentang Kami</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Akun</h4>
      <ul>
        <?php if ($footerUser): ?>
        <li><a href="<?= url('akun.php') ?>">Akun Saya</a></li>
        <li><a href="<?= url('pesanan-saya.php') ?>">Pesanan Saya</a></li>
        <li><a href="<?= url('keluar.php') ?>">Keluar</a></li>
        <?php else: ?>
        <li><a href="<?= url('masuk.php') ?>">Masuk</a></li>
        <li><a href="<?= url('daftar.php') ?>">Daftar Akun</a></li>
        <?php endif; ?>
        <li><a href="<?= url('lacak-pesanan.php') ?>">Lacak Pesanan</a></li>
        <?php if (is_staff()): ?>
        <li><a href="<?= url('admin/index.php') ?>">Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Kontak</h4>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path
                d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10"
                    r="3"/></svg> Jl. Raya Desa Makmur No. 12</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 0812-3456-7890</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path
                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> halo@berkahrasa.id</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12"
                r="10"/><polyline points="12 6 12 12 16 14"/></svg> Senin–Sabtu 08.00–17.00</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <span id="year"></span> <?= e(APP_NAME) ?> · UAS WebPro TI C — Kelompok 2 · <a
        href="<?= url('admin/login.php') ?>">Admin</a> · <a
            href="<?= url('tentang.php#privasi') ?>">Kebijakan Privasi</a></p>
  </div>
</footer>

<?php if (!has_cookie_consent()): ?>
<div class="cookie-banner" id="cookieBanner" role="dialog" aria-label="Persetujuan cookie">
  <p><strong>Kami memakai cookie.</strong> Cookie digunakan untuk sesi login yang aman,
      preferensi tema, dan perlindungan formulir. <a
          href="<?= url('tentang.php#privasi') ?>">Pelajari</a>.</p>
  <div class="cookie-actions">
    <button type="button" class="btn btn-sm" onclick="acceptCookies()">Setuju</button>
  </div>
</div>
<?php endif; ?>

<?php if (!($hideCart ?? false)): ?>
<div class="product-modal" id="productModal" role="dialog" aria-label="Detail produk"
    onclick="if(event.target===this) closeProduct()">
  <div class="product-modal-inner">
    <button class="cart-close" onclick="closeProduct()" aria-label="Tutup">✕</button>
    <div class="product-modal-media">
      <span class="product-tag" id="quickTag"></span>
      <img id="quickImg" src="" alt="">
    </div>
    <div class="product-modal-body">
      <h2 id="quickName"></h2>
      <p class="variant" id="quickVariant"></p>
      <p class="size" id="quickSize"></p>
      <p class="product-modal-desc" id="quickDesc"></p>
      <div class="product-modal-foot">
        <span class="price" id="quickPrice"></span>
        <button class="add-btn" id="quickAdd">Tambah ke keranjang</button>
      </div>
    </div>
  </div>
</div>
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<aside class="cart-drawer" id="cartDrawer" aria-label="Keranjang belanja">
  <div class="cart-head">
    <h3>Keranjang Belanja</h3>
    <button class="cart-close" onclick="closeCart()" aria-label="Tutup">✕</button>
  </div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-foot">
    <div class="checkout-form">
      <?php if (!$footerUser): ?>
      <p class="cart-login-hint">Sudah punya akun? <a href="<?= url('masuk.php') ?>">Masuk</a>
          agar pesanan tersimpan di riwayat.</p>
      <?php endif; ?>
    </div>
    <div class="cart-total"><span>Total</span><span id="cartTotal">Rp0</span></div>
    <button class="btn btn-block" onclick="goToCheckout()">Checkout</button>
  </div>
</aside>
<?php endif; ?>

<button class="to-top" id="toTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"
    aria-label="Ke atas">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12"
          y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
</button>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script>
window.__BERKAH_ASSET_VERSION__ = <?= json_encode(ASSET_VERSION) ?>;
window.__BERKAH_PRODUCTS__ = <?= json_encode($footerProducts, JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE) ?>;
window.__BERKAH_API__ = <?= json_encode(url('api/order.php')) ?>;
window.__BERKAH_CSRF__ = <?= json_encode(csrf_token()) ?>;
window.__BERKAH_BASE__ = <?= json_encode(BASE_URL) ?>;
window.__BERKAH_ONGKIR__ = <?= json_encode(['threshold' => ONGKIR_THRESHOLD, 'fee' =>
    ONGKIR_FEE]) ?>;
</script>
<script src="<?= asset('script.js') ?>"></script>
</body>
</html>
