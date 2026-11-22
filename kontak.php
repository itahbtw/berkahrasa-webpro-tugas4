<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kontak';
$pageDesc  =
    'Hubungi Berkah Rasa untuk pesanan, kerja sama reseller, dan pertanyaan seputar produk.';
$active    = 'kontak';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Honeypot: bot biasanya mengisi kolom tersembunyi.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        set_flash('success', 'Pesan terkirim! Kami akan segera menghubungi Anda.');
        redirect(url('kontak.php'));
    }

    $lastContact = (int) ($_SESSION['last_contact_at'] ?? 0);
    if ($lastContact > 0 && (time() - $lastContact) < CONTACT_MIN_INTERVAL) {
        set_flash('error',
            'Pesan sebelumnya baru saja terkirim. Tunggu beberapa detik sebelum mengirim lagi.');
        redirect(url('kontak.php'));
    }

    $nama  = clean_text($_POST['nama'] ?? '', 120);
    $email = mb_strtolower(clean_text($_POST['email'] ?? '', 150));
    $pesan = clean_text($_POST['pesan'] ?? '', 2000);

    if (!valid_name($nama)) {
        set_flash('error', 'Nama 3–120 karakter.');
    } elseif ($email !== '' && !valid_email($email)) {
        set_flash('error', 'Format email tidak valid.');
    } elseif (mb_strlen($pesan) < 10) {
        set_flash('error', 'Pesan minimal 10 karakter.');
    } elseif (mb_strlen($pesan) > 2000) {
        set_flash('error', 'Pesan maksimal 2000 karakter.');
    } else {
        $stmt = db()->prepare('INSERT INTO pesan_kontak (nama, email, pesan) VALUES (?, ?, ?)');
        $stmt->execute([$nama, $email !== '' ? $email : null, $pesan]);
        $_SESSION['last_contact_at'] = time();
        log_activity(is_logged_in() ? (int) current_user()['id'] : null, 'kontak.kirim',
            'Pesan kontak dari ' . $nama);
        set_flash('success', 'Pesan terkirim! Kami akan segera menghubungi Anda.');
    }

    redirect(url('kontak.php'));
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Beranda</a> / Kontak</div>
    <h1>Hubungi Berkah Rasa</h1>
    <p>Kami siap membantu pesanan, kerja sama reseller, maupun pertanyaan seputar produk. Jangan
        ragu menghubungi kami.</p>
  </div>
</section>

<main class="section">
  <div class="container">
    <div class="features mb-2">
      <div class="feature reveal d1">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10"
                    r="3"/></svg></div>
        <h3>Alamat</h3>
        <p>Jl. Raya Desa Makmur No. 12, Kec. Sumber Rejeki, Kab. Panen Raya.</p>
      </div>
      <div class="feature reveal d2">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <h3>Telepon / WA</h3>
        <p>0812-3456-7890<br>a/n Ibu Slamet</p>
      </div>
      <div class="feature reveal d3">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path
                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <h3>Email</h3>
        <p>halo@berkahrasa.id</p>
      </div>
      <div class="feature reveal">
        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12"
                cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <h3>Jam Pelayanan</h3>
        <p>Senin–Sabtu<br>08.00–17.00 WIB</p>
      </div>
    </div>

    <div class="grid-2">
      <div class="content-card reveal d1">
        <h2>Kirim Pesan</h2>
        <p>Pesan divalidasi di browser dan di server, lalu disimpan ke database.</p>
        <form method="post" action="<?= url('kontak.php') ?>"
            onsubmit="return validateContact(event)">
          <?= csrf_field() ?>
          <div class="hp-field" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <div class="form-grid">
            <div class="form-field">
              <label for="cname">Nama Lengkap</label>
              <input type="text" id="cname" name="nama" placeholder="Nama Anda" required
                  maxlength="120" autocomplete="name">
            </div>
            <div class="form-field">
              <label for="cemail">Email (opsional)</label>
              <input type="email" id="cemail" name="email" placeholder="nama@email.com"
                  maxlength="150" autocomplete="email">
            </div>
          </div>
          <div class="form-field">
            <label for="cmsg">Pesan</label>
            <textarea id="cmsg" name="pesan"
                placeholder="Tulis pesan, pesanan, atau pertanyaan Anda..." required
                    maxlength="2000"></textarea>
          </div>
          <button class="btn" type="submit">Kirim Pesan</button>
        </form>
      </div>

      <div class="content-card reveal d2">
        <h2>Informasi Pengiriman</h2>
        <p>Pengiriman dalam desa dilakukan setiap sore menggunakan motor. Pengiriman antar
            kecamatan dua kali seminggu, setiap hari Selasa dan Jumat.</p>
        <p>Untuk luar kota kami bekerja sama dengan jasa ekspedisi. Paket dibungkus kardus tebal
            dan plastik tambahan agar keripik tidak hancur selama perjalanan.</p>
        <div class="table-wrap mt-1">
          <table>
            <thead><tr><th>Jenis Informasi</th><th>Keterangan</th></tr></thead>
            <tbody>
              <tr><td>Nama UMKM</td><td>Berkah Rasa - Camilan Singkong Desa</td></tr>
              <tr><td>Alamat</td><td>Jl. Raya Desa Makmur No. 12, Kec. Sumber Rejeki</td></tr>
              <tr><td>Telepon / WA</td><td>0812-3456-7890 a/n Ibu Slamet</td></tr>
              <tr><td>Jam Pelayanan</td><td>Senin–Sabtu, 08.00–17.00</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="content-card reveal">
      <h3>FAQ Seputar Pesanan</h3>
      <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)"><span>Apakah bisa kirim ke
            luar kota?</span><span class="chev">⌄</span></div>
        <div class="accordion-body"><p>Bisa. Kami bekerja sama dengan jasa ekspedisi dan
            mengemas produk dengan kardus tebal agar tetap renyah sampai tujuan.</p></div>
      </div>
      <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)"><span>Berapa minimal
            pembelian reseller?</span><span class="chev">⌄</span></div>
        <div class="accordion-body"><p>Paket awal 20 bungkus dengan harga grosir. Tidak ada
            biaya pendaftaran.</p></div>
      </div>
      <div class="accordion">
        <div class="accordion-header" onclick="toggleAccordion(this)"><span>Bagaimana bila
            produk rusak saat diterima?</span><span class="chev">⌄</span></div>
        <div class="accordion-body"><p>Kirimkan foto dan keterangan maksimal 2 hari setelah
            diterima, kami ganti atau kembalikan uang sesuai nilai kekurangan.</p></div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
