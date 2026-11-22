<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tentang Kami';
$pageDesc  = 'Kisah Berkah Rasa, visi misi, tim UMKM, dan informasi kelompok pembuat website.';
$active    = 'tentang';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Beranda</a> / Tentang Kami</div>
    <h1>Tentang Berkah Rasa</h1>
    <p>Perjalanan UMKM desa yang tumbuh dari dapur rumah menjadi camilan kebanggaan warga.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="split mb-section">
      <div class="split-media reveal">
        <img src="gambar/keripik-balado.jpg" alt="Keripik singkong balado Berkah Rasa">
        <div class="accent-box"><b>500+</b><span>Pelanggan setia</span></div>
      </div>
      <div class="reveal d1">
        <span class="eyebrow">Cerita Kami</span>
        <h2>Berawal dari Lima Kilogram</h2>
        <p>Berkah Rasa didirikan tahun 2019 oleh keluarga Bapak Slamet di desa penghasil
            singkong. Awalnya hanya lima kilogram keripik per hari dengan wajan kecil dan
                kemasan lilin, dititipkan ke dua warung tetangga.</p>
        <p>Berkat ketekunan menjaga rasa, kebersihan, dan kejujuran timbangan, pesanan bertambah
            dari mulut ke mulut. Kini kami mempekerjakan belasan warga, memiliki alat pemotong,
                wajan besar, dan mesin sealer, serta label halal dan nomor induk berusaha.</p>
        <p>Visi kami: menjadi UMKM desa yang mandiri, profesional, dan mampu bersaing di pasar
            digital tanpa meninggalkan nilai gotong royong dan kekeluargaan.</p>
      </div>
    </div>

    <div class="content-card reveal">
      <h2>Misi Kami</h2>
      <ul class="list-check">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Mengolah hasil pertanian lokal menjadi produk berkualitas agar petani mendapat
                    harga adil.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Membuka lapangan pekerjaan bagi warga sekitar, terutama ibu rumah tangga.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Menjaga kualitas rasa dan kebersihan produksi dengan bahan segar dan minyak
                    rutin diganti.</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                Memanfaatkan teknologi internet untuk memperkenalkan produk desa ke pasar lebih
                    luas.</li>
      </ul>
    </div>

    <div class="content-card reveal">
      <h2>Struktur Tim UMKM</h2>
      <div class="features">
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                  d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7"
                      r="4"/></svg></div>
          <h3>Bapak Slamet</h3>
          <p>Pemilik — mencari singkong dan menggoreng.</p>
        </div>
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                  d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7"
                      r="4"/></svg></div>
          <h3>Ibu Slamet</h3>
          <p>Meracik bumbu dan mengatur keuangan.</p>
        </div>
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                  d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7"
                      r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path
                          d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
          <h3>Warga Desa</h3>
          <p>Mengupas, mencuci, mengiris, dan mengemas.</p>
        </div>
        <div class="feature">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3"
                  width="15" height="13"/><polygon
                      points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5"
                          r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
          <h3>Karang Taruna</h3>
          <p>Membantu pengantaran pesanan dalam jumlah besar.</p>
        </div>
      </div>
    </div>

    <div class="content-card reveal">
      <h2>Informasi Kelompok Pembuat Website</h2>
      <p>Website untuk UMKM ini dibuat sebagai <strong>Tugas 4 (UAS)</strong> mata kuliah WebPro
          TI C oleh <strong>Kelompok 2</strong> dengan tema UMKM. Aplikasi memakai PHP dan
              MySQL/MariaDB, melengkapi CRUD Tugas 3 dengan session, cookie, autentikasi,
                  otorisasi berbasis peran, validasi, dan pengujian keamanan.</p>
      <div class="features features-3 mt-card">
        <div class="feature">
          <div class="ico ico-text">MU</div>
          <h3>Miftahul Ulum</h3>
          <p>Anggota Kelompok 2</p>
        </div>
        <div class="feature">
          <div class="ico ico-text">CA</div>
          <h3>Chocky Ardian Dimas Indarmawan</h3>
          <p>Anggota Kelompok 2</p>
        </div>
        <div class="feature">
          <div class="ico ico-text">NK</div>
          <h3>Nathanael Kevin Nugraha</h3>
          <p>Anggota Kelompok 2</p>
        </div>
      </div>
      <p class="mt-2">Silakan lanjut ke <a href="cara-memesan.php">Cara Memesan</a> atau kembali
          ke <a href="index.php">Beranda</a>.</p>
    </div>

    <div class="content-card reveal" id="privasi">
      <h2>Kebijakan Privasi &amp; Cookie</h2>
      <p>Kami menghargai data Anda. Halaman ini menjelaskan data apa yang diproses website
          Berkah Rasa dan bagaimana data tersebut dilindungi.</p>

      <h3>Data yang kami simpan</h3>
      <ul>
        <li><strong>Data akun:</strong> nama, username, email, nomor WhatsApp, dan password yang
            disimpan sebagai hash bcrypt (bukan teks asli).</li>
        <li><strong>Data pesanan:</strong> nama penerima, nomor WhatsApp, alamat, catatan, item,
            dan total pesanan.</li>
        <li><strong>Pesan kontak:</strong> nama, email (opsional), dan isi pesan yang Anda kirim
            melalui formulir kontak.</li>
        <li><strong>Log keamanan:</strong> waktu login, alamat IP, dan aktivitas penting untuk
            mendeteksi penyalahgunaan.</li>
      </ul>

      <h3>Cookie yang digunakan</h3>
      <ul>
        <li><strong>Cookie sesi (<code>BERKAHRASASESSID</code>):</strong> menjaga status login.
            Bersifat HttpOnly, SameSite=Lax, dan Secure saat HTTPS aktif.</li>
        <li><strong>Cookie “ingat saya” (<code>berkah_remember</code>):</strong> hanya dibuat
            jika Anda mencentang “Ingat saya”. Token disimpan dalam bentuk hash dan dapat
                dicabut saat logout atau ganti password.</li>
        <li><strong>Cookie tema (<code>berkah_theme</code>):</strong> mengingat pilihan mode
            terang/gelap.</li>
        <li><strong>Cookie persetujuan (<code>berkah_consent</code>):</strong> menandai bahwa
            Anda sudah melihat pemberitahuan cookie.</li>
      </ul>
      <p>Kami tidak memakai cookie iklan atau pelacak pihak ketiga. Penyimpanan keranjang
          belanja memakai <em>localStorage</em> browser dan tidak dikirim ke server sampai Anda
              melakukan checkout.</p>

      <h3>Keamanan dan retensi</h3>
      <ul>
        <li>Semua formulir dilindungi token CSRF dan validasi di sisi server.</li>
        <li>Sesi berakhir otomatis setelah <?= (int) (SESSION_IDLE_TIMEOUT / 60) ?> menit tanpa
            aktivitas atau <?= (int) (SESSION_ABSOLUTE_TIMEOUT / 3600) ?> jam setelah login.</li>
        <li>Percobaan login dibatasi untuk mencegah tebak password (brute force).</li>
        <li>Data pesanan dan log disimpan selama diperlukan untuk operasional dan audit, lalu
            dibersihkan secara berkala.</li>
      </ul>

      <h3>Hak Anda</h3>
      <p>Anda dapat melihat dan memperbarui profil melalui halaman <a
          href="<?= url('akun.php') ?>">Akun Saya</a>, mencabut sesi dengan logout, serta
              meminta penghapusan akun melalui <a href="<?= url('kontak.php') ?>">formulir
                  kontak</a> atau WhatsApp. Akun staf tidak dihapus permanen agar jejak audit
                      tetap utuh, tetapi dapat dinonaktifkan oleh administrator.</p>
      <p class="auth-small">Dokumen ini disusun untuk kebutuhan tugas akademik dan dapat
          disesuaikan sebelum digunakan pada layanan produksi.</p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
