<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Checkout';
$pageDesc  = 'Selesaikan pesanan Berkah Rasa langsung di website tanpa perlu membuka WhatsApp.';
$active    = '';
$hideCart  = true;
$coUser    = current_user();

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero auth-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / <a
        href="<?= url('produk.php') ?>">Produk</a> / Checkout</div>
    <h1>Checkout Pesanan</h1>
    <p>Periksa kembali pesanan Anda, pilih metode pembayaran, lalu kirim langsung dari halaman
        ini.</p>
  </div>
</section>

<main class="section">
  <div class="container checkout-layout">
    <aside class="content-card reveal d1" id="checkoutSummaryCard">
      <div class="panel-title-row">
        <h2>Ringkasan Pesanan</h2>
        <a class="btn btn-sm btn-ghost" href="<?= url('produk.php') ?>">Tambah produk</a>
      </div>
      <div id="checkoutItems" class="checkout-items">
        <p class="muted-text">Memuat keranjang...</p>
      </div>
      <div class="checkout-breakdown">
        <div><span>Subtotal</span><span id="checkoutSubtotal">Rp0</span></div>
        <div><span>Ongkir</span><span id="checkoutOngkir">Rp0</span></div>
      </div>
      <div class="cart-total checkout-total">
        <span>Total</span><span id="checkoutTotal">Rp0</span>
      </div>
      <p class="ongkir-note" id="checkoutOngkirNote"></p>
    </aside>

    <section class="content-card reveal" id="checkoutFormCard">
      <div class="panel-title-row">
        <h2>Data Pemesan</h2>
        <?php if (!$coUser): ?>
        <a class="btn btn-sm btn-ghost" href="<?= url('masuk.php') ?>">Masuk untuk riwayat</a>
        <?php endif; ?>
      </div>

      <?php if (!$coUser): ?>
      <p class="cart-login-hint">Anda dapat checkout sebagai tamu. Masuk jika ingin memantau
          status pesanan di <a href="<?= url('pesanan-saya.php') ?>">Pesanan Saya</a>.</p>
      <?php endif; ?>

      <form id="checkoutForm" class="auth-form" onsubmit="return submitCheckout(event)">
        <?= csrf_field() ?>

        <div class="form-row-2">
          <div class="form-field">
            <label for="coName">Nama Lengkap</label>
            <input type="text" id="coName" name="nama" required maxlength="120"
                autocomplete="name" value="<?= e($coUser['nama'] ?? '') ?>">
          </div>
          <div class="form-field">
            <label for="coPhone">Nomor WhatsApp</label>
            <input type="tel" id="coPhone" name="no_wa" required maxlength="30"
                autocomplete="tel" value="<?= e($coUser['no_wa'] ?? '') ?>"
                    placeholder="081234567890">
          </div>
        </div>
        <div class="form-field">
          <label for="coEmail">Email (opsional)</label>
          <input type="email" id="coEmail" name="email" maxlength="150" autocomplete="email"
              value="<?= e($coUser['email'] ?? '') ?>" placeholder="nama@email.com">
          <small class="form-hint">Dipakai untuk konfirmasi pesanan jika tersedia.</small>
        </div>
        <div class="form-field">
          <label for="coAddress">Alamat Pengiriman</label>
          <textarea id="coAddress" name="alamat" maxlength="300"
              placeholder="Nama jalan, nomor rumah, desa/kecamatan"><?=
                  e($_SESSION['checkout_alamat'] ?? '') ?></textarea>
        </div>
        <div class="form-field">
          <label for="coNote">Catatan Pesanan (opsional)</label>
          <textarea id="coNote" name="catatan" maxlength="500"
              placeholder="Contoh: kirim sore hari, bungkus tambahan"></textarea>
        </div>

        <div class="form-field">
          <label>Metode Pembayaran</label>
          <div class="payment-options">
            <label class="payment-option">
              <input type="radio" name="pembayaran" value="cod" checked>
              <span><b>Bayar di Tempat (COD)</b><small>Pembayaran dilakukan saat pesanan
                  diterima.</small></span>
            </label>
            <label class="payment-option">
              <input type="radio" name="pembayaran" value="transfer">
              <span><b>Transfer Bank</b><small>Transfer ke rekening BCA Berkah Rasa.</small></span>
            </label>
          </div>
          <div class="transfer-info" id="transferInfo" hidden>
            <span class="bank-logo">BCA</span>
            <div>
              <b>Transfer ke rekening berikut:</b>
              <span class="transfer-number"><?= e(BCA_ACCOUNT) ?></span>
              <small>a.n. <?= e(BCA_HOLDER) ?></small>
            </div>
          </div>
        </div>

        <label class="check-row">
          <input type="checkbox" id="coAgree" required>
          <span>Saya menyetujui <a href="<?= url('tentang.php#privasi') ?>">kebijakan
              privasi</a> dan benar data di atas.</span>
        </label>

        <button class="btn btn-block" type="submit" id="coSubmit">Kirim Pesanan</button>
      </form>
    </section>


    <section class="content-card checkout-success" id="checkoutSuccess" hidden>
      <div class="success-icon">✓</div>
      <h2>Pesanan Berhasil Dikirim</h2>
      <p>Terima kasih! Pesanan Anda sudah tersimpan di sistem Berkah Rasa.</p>
      <p class="checkout-code">Kode pesanan: <strong id="checkoutKode">-</strong></p>
      <p class="muted-text" id="checkoutPaymentInfo"></p>
      <div class="struk" id="struk">
        <div class="struk-head">
          <strong>BERKAH RASA</strong>
          <small>Camilan Singkong Desa — Bukti Pesanan</small>
        </div>
        <div class="struk-meta">
          <span>Kode: <b id="strukKode">-</b></span>
          <span>Tanggal: <b id="strukTanggal">-</b></span>
          <span>Pembayaran: <b id="strukBayar">-</b></span>
        </div>
        <div id="strukItems" class="struk-items"></div>
        <div class="struk-line"><span>Subtotal</span><span id="strukSubtotal">Rp0</span></div>
        <div class="struk-line"><span>Ongkir</span><span id="strukOngkir">Rp0</span></div>
        <div class="struk-total"><span>Total</span><strong id="strukTotal">Rp0</strong></div>
        <p class="struk-foot">Simpan struk ini sebagai bukti pesanan Anda.</p>
      </div>

      <div class="detail-actions checkout-print-actions">
        <?php if ($coUser): ?>
        <a class="btn btn-ghost" href="<?= url('pesanan-saya.php') ?>">Lihat Pesanan Saya</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="<?= url('lacak-pesanan.php') ?>">Lacak Pesanan</a>
        <a class="btn btn-ghost" href="<?= url('produk.php') ?>">Belanja Lagi</a>
      </div>
    </section>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
