<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Produk';
$pageDesc  =
    'Daftar produk camilan singkong Berkah Rasa: keripik, stik keju, opak, tiwul, dan gatot.';
$active    = 'produk';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Beranda</a> / Produk</div>
    <h1>Menu Camilan Kami</h1>
    <p>Semua olahan singkong dibuat dari bahan pilihan, rempah alami, dan proses higienis.
        Tambahkan ke keranjang dan selesaikan pembelian langsung di website.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line
              x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" class="search-box"
          placeholder="Cari produk, mis. balado, keju, tiwul...">
    </div>
    <div id="productGrid" class="product-grid"></div>

    <div class="content-card mt-2 reveal">
      <h3>Tabel Harga Produk</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Foto</th><th>Nama Produk</th><th>Varian
                Rasa</th><th>Ukuran</th><th>Harga</th></tr>
          </thead>
          <tbody>
            <?php foreach (get_products() as $p): ?>
            <tr>
              <td><img src="<?= url($p['gambar']) ?>" alt="<?= e($p['nama']) ?>" width="80"></td>
              <td><?= e($p['nama']) ?></td>
              <td><?= e($p['varian']) ?></td>
              <td><?= e($p['ukuran']) ?></td>
              <td><?= rupiah($p['harga']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="mt-1">Tersedia potongan harga untuk pembelian di atas 50 bungkus dan harga
          grosir khusus reseller.</p>
      <a href="cara-memesan.php" class="btn">Pelajari Cara Memesan</a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
