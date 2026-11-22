<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Beranda';
$pageDesc  = 'Berkah Rasa - UMKM camilan singkong asli desa. Renyah, gurih, dan penuh berkah.';
$active    = 'index';

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="badge-pill"><span class="dot"></span> UMKM Desa Sejak 2019</span>
      <h1>Camilan Singkong <span class="hl">Renyah</span>, Gurih, Penuh Berkah</h1>
      <p class="lead">Diolah dari singkong pilihan petani lokal, dibumbui rempah alami, dan
          digoreng higienis. Teman santai paling pas untuk keluarga Indonesia.</p>
      <div class="hero-cta">
        <a href="<?= url('produk.php') ?>" class="btn">Lihat Produk <svg viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline
                    points="12 5 19 12 12 19"/></svg></a>
        <a href="<?= url('cara-memesan.php') ?>" class="btn btn-ghost">Cara Memesan</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><b>6+</b><span>Varian produk</span></div>
        <div class="stat"><b>2019</b><span>Berdiri sejak</span></div>
        <div class="stat"><b>500+</b><span>Pelanggan setia</span></div>
        <div class="stat"><b>5</b><span>Petani mitra</span></div>
      </div>
    </div>
    <div class="hero-art">
      <div class="frame"><img src="<?= asset('gambar/keripiksingkong.jpg') ?>"
          alt="Keripik singkong Berkah Rasa"></div>
      <div class="float f1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
              d="M20 6 9 17l-5-5"/></svg> 100% Bahan Alami</div>
      <div class="float f2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
              d="M12 2 15 8l7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg> Rasa Bintang Lima</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Kenapa Berkah Rasa</span>
      <h2>Kualitas yang Bisa Kamu Rasakan</h2>
      <p>Kami menjaga setiap proses dari kebun sampai kemasan agar camilan desa terasa istimewa.</p>
    </div>
    <div class="features">
      <div class="feature reveal d1">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/></svg></div>
        <h3>Bahan Alami</h3>
        <p>Singkong pilihan petani lokal, rempah asli, tanpa pengawet berbahaya.</p>
      </div>
      <div class="feature reveal d2">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path
                    d="m9 12 2 2 4-4"/></svg></div>
        <h3>Higienis</h3>
        <p>Produksi terjaga, minyak rutin diganti, dan kemasan sealed rapat.</p>
      </div>
      <div class="feature reveal d3">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12"
                cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path
                    d="M12 18V6"/></svg></div>
        <h3>Harga Terjangkau</h3>
        <p>Mulai Rp5.000, ramah kantong pelajar sampai ibu rumah tangga.</p>
      </div>
      <div class="feature reveal">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3"
                width="15" height="13"/><polygon
                    points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5"
                        r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <h3>Siap Kirim</h3>
        <p>Pengiriman desa, antar kecamatan, sampai ekspedisi luar kota.</p>
      </div>
    </div>
  </div>
</section>

<!-- PRODUCTS -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Menu Favorit</span>
      <h2>Produk Unggulan Kami</h2>
      <p>Pilih camilan favoritmu, tambahkan ke keranjang, lalu checkout langsung di website.</p>
    </div>
    <div id="productGrid" class="product-grid"></div>
    <div class="center mt-2">
      <a href="<?= url('produk.php') ?>" class="btn btn-ghost">Lihat Semua Produk</a>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="section">
  <div class="container split">
    <div class="split-media reveal">
      <img src="gambar/Gatot-Tiwul.jpg" alt="Gatot dan tiwul khas Berkah Rasa">
      <div class="accent-box"><b>2019</b><span>Mulai dari dapur rumah</span></div>
    </div>
    <div class="reveal d1">
      <span class="eyebrow">Tentang Kami</span>
      <h2>Dari Dapur Desa, untuk Indonesia</h2>
      <p>Berkah Rasa berawal dari lima kilogram keripik per hari yang dibuat keluarga Bapak
          Slamet. Kini kami memberdayakan belasan warga desa dan lima keluarga petani singkong.</p>
      <p>Setiap potong singkong dipilih teliti, dicuci bersih, diiris seragam, dibumbui rempah
          pilihan, lalu digoreng dengan suhu terkontrol agar renyah tahan lama.</p>
      <ul class="list-check">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Bekerja sama dengan petani lokal dengan harga adil</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Melibatkan ibu rumah tangga dalam pengemasan</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Label halal dan nomor induk berusaha</li>
      </ul>
      <a href="<?= url('tentang.php') ?>" class="btn">Kenali Kami Lebih Dekat</a>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Testimoni</span>
      <h2>Kata Pelanggan Kami</h2>
      <p>Cerita hangat dari mereka yang sudah mencicipi camilan desa kami.</p>
    </div>
    <div class="testi-grid">
      <div class="testi reveal d1">
        <div class="stars">★★★★★</div>
        <p>"Keripik baladonya mantap, pedasnya pas dan bumbunya melimpah. Anak-anak di rumah rebutan!"</p>
        <div class="who"><div class="avatar">SR</div><div><b>Siti Rahma</b><span>Ibu rumah
            tangga, Sumber Rejeki</span></div></div>
      </div>
      <div class="testi reveal d2">
        <div class="stars">★★★★★</div>
        <p>"Sudah langganan buat jualan di warung. Barang selalu datang tepat waktu, kalau ada yang rusak langsung diganti."</p>
        <div class="who"><div class="avatar">AB</div><div><b>Agus Budi</b><span>Pemilik warung,
            Panen Raya</span></div></div>
      </div>
      <div class="testi reveal d3">
        <div class="stars">★★★★★</div>
        <p>"Stik kejunya renyah banget dan harganya masuk kantong pelajar. Cocok buat oleh-oleh juga."</p>
        <div class="who"><div class="avatar">DN</div><div><b>Dinda Nuraini</b><span>Mahasiswa,
            Kota Tetangga</span></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container">
    <div class="cta-band reveal">
      <h2>Siap Coba Camilan Desa Kami?</h2>
      <p>Pesan sekarang langsung di website, atau daftar jadi reseller dan raih penghasilan
          tambahan bersama Berkah Rasa.</p>
      <div class="hero-cta">
        <a href="<?= url('produk.php') ?>" class="btn btn-accent">Pesan Sekarang</a>
        <a href="<?= url('cara-memesan.php') ?>" class="btn btn-ghost btn-outline-light">Jadi
            Reseller</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
