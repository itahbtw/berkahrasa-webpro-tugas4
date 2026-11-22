<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Cara Memesan';
$pageDesc  = 'Cara memesan camilan Berkah Rasa, aturan pemesanan, dan peluang menjadi reseller.';
$active    = 'cara';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Beranda</a> / Cara Memesan</div>
    <h1>Cara Memesan</h1>
    <p>Pesan camilan favoritmu dalam tiga cara mudah: datang langsung, hubungi kami, atau
        checkout lewat keranjang website.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="grid-2">
      <div class="content-card reveal d1">
        <h2>Tiga Cara Memesan</h2>
        <p><strong>1. Datang langsung</strong> ke rumah produksi kami. Pembeli dapat melihat
            proses pembuatan, mencicipi varian rasa, dan membawa pulang produk pada hari itu
                juga selama persediaan ada.</p>
        <p><strong>2. Hubungi kami</strong> melalui telepon atau pesan singkat dengan
            menyebutkan nama lengkap, alamat, daftar produk beserta jumlahnya, dan waktu
                pengambilan atau pengiriman.</p>
        <p><strong>3. Checkout di website</strong> — pilih produk di halaman <a
            href="produk.php">Produk</a>, tambahkan ke keranjang, lalu isi formulir checkout.
                Pesanan langsung tersimpan di sistem toko tanpa perlu membuka WhatsApp.</p>
      </div>
      <div class="content-card reveal d2">
        <h2>Aturan Pemesanan</h2>
        <ul class="list-check mt-sm">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12"
                  r="10"/><polyline points="12 6 12 12 16 14"/></svg> Pesanan harian dilayani
                      pada hari yang sama bila sebelum pukul 15.00.</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                  Pesanan acara 50 bungkus atau lebih minimal 3 hari sebelumnya.</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                  Reseller di atas 200 bungkus minimal 1 minggu sebelumnya, DP 50%.</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                  Pembayaran tunai, transfer bank, atau dompet digital.</li>
        </ul>
      </div>
    </div>

    <div class="content-card reveal">
      <h3>Langkah-Langkah Memesan</h3>
      <div class="features features-3">
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9"
                  cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path
                      d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
          <h3>1. Pilih Produk</h3>
          <p>Buka halaman produk dan tentukan varian serta jumlahnya.</p>
        </div>
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
          <h3>2. Konfirmasi</h3>
          <p>Hubungi kami atau checkout, lalu tunggu konfirmasi total harga.</p>
        </div>
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3"
                  width="15" height="13"/><polygon
                      points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5"
                          r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
          <h3>3. Terima Pesanan</h3>
          <p>Bayar sesuai kesepakatan, lalu periksa produk saat diterima.</p>
        </div>
      </div>
    </div>

    <div class="content-card reveal">
      <h2>Peluang Menjadi Reseller</h2>
      <p>Kami membuka pendaftaran reseller tanpa biaya. Cukup membeli paket awal 20 bungkus
          dengan harga grosir, reseller sudah mendapat brosur, daftar harga, dan contoh produk
              untuk promosi. Keuntungan berkisar Rp2.000–Rp5.000 per bungkus tergantung varian
                  dan ukuran.</p>
      <p>Kami juga menerima pesanan label khusus untuk pernikahan, khitanan, dan oleh-oleh
          perusahaan, serta bersedia mengikuti bazar dan pameran UMKM.</p>
      <a href="kontak.php" class="btn">Hubungi Kami untuk Jadi Reseller</a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
