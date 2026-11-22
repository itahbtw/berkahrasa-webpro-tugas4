# Changelog

## 4.7.0 — Ongkir otomatis

- Ongkir Rp10.000 untuk subtotal di bawah Rp100.000; gratis untuk subtotal ≥ Rp100.000.
- Kolom `pesanan.ongkir` ditambahkan via `database/migrate_v6.sql`.
- Ringkasan checkout, struk, dan detail pesanan menampilkan rincian subtotal & ongkir.

## 4.2.1 — Perapian tampilan & kode

- **Perbaikan penting**: `session_set_cookie_params()` kini memakai opsi `lifetime` (bukan `expires`). Sebelumnya warning PHP muncul di atas halaman dan membuat sesi gagal dimulai pada PHP 8.5.
- Opsi cookie dipisah menjadi `session_cookie_options()` (untuk sesi) dan `cookie_options()` (untuk `setcookie()`).
- Versi aset CSS/JS/gambar dipusatkan di konstanta `ASSET_VERSION` lewat helper `asset()`; menghapus cache-buster hardcode yang tersebar.
- Font Google dipindah dari `@import` CSS ke `<link>` + `preconnect` di header agar rendering tidak diblokir.
- Menu hamburger kini memperbarui `aria-expanded`/`aria-label`, ikut tertutup saat Escape atau klik di luar header.
- Toast memakai `role="status"` dan `aria-live="polite"` untuk pembaca layar.
- Inline style pada tombol dan band CTA diganti kelas utilitas (`.btn-block`, `.btn-outline-light`, `.mb-1`, `.mb-2`).
- Teks CTA diselaraskan dengan alur checkout di website (bukan lagi "lewat WhatsApp").
- Seluruh tautan internal di beranda memakai helper `url()` agar konsisten di subfolder.

## 4.2.0 — Perbaikan fitur & deploy permanen

- Fitur **Lacak Pesanan** (`lacak-pesanan.php`): cek status pesanan dengan kode + nomor WhatsApp, tanpa login.
- **Quick view produk** lewat popup pada katalog.
- **Struk & cetak checkout**: bukti pesanan bisa dicetak langsung dari halaman sukses.
- Perbaikan visual dan navigasi mobile, fokus aksesibilitas, dan reduced-motion.
- Dukungan shared hosting yang menonaktifkan `putenv()` — `.env` tetap terbaca lewat `$_ENV`.
- Deploy permanen ke InfinityFree: **https://berkahrasa.ifree.page/**
- Header panel admin diselaraskan dengan header situs publik (terang, pill aktif, chip akun), tanpa menu bertumpuk sampai lebar 768 px.
- Menu hamburger ditambahkan pada panel admin untuk layar potrat/kecil; klik tautan atau Escape menutup menu.
- Ukuran item menu publik dan admin dibuat seragam.
- Gambar keripik singkong diganti dengan foto baru dari URL yang diberikan (dengan cache-busting).

## 4.1.0 — Checkout langsung di website

- Menghapus alur checkout WhatsApp; pesanan kini dibuat dari halaman `checkout.php`.
- Kolom `email` dan `pembayaran` (COD/transfer) ditambahkan ke tabel `pesanan` via `database/migrate_v5.sql`.
- Halaman checkout menampilkan ringkasan keranjang, validasi form, CSRF, dan konfirmasi kode pesanan.
- Admin dan pelanggan dapat melihat metode pembayaran pada detail pesanan.
- Tombol bantuan WhatsApp diganti dengan tautan halaman kontak pada detail pesanan pelanggan.
- Header publik dan admin dirapikan: menu tidak lagi turun ke dua baris pada layar menengah; label menu kini `nowrap`, dan peran/navigasi admin menyesuaikan layar.
- Pembatasan login kini mengenali IP asli di belakang Cloudflare Tunnel (`CF-Connecting-IP`/`X-Forwarded-For`).
- Screenshot laporan diperbarui seluruhnya; Gambar 3.2 kini menampilkan form masuk dan daftar secara berdampingan.

## 4.0.0 — Tugas 4 / UAS WebPro

### Ditambahkan

- Environment loader `.env` tanpa Composer; `APP_ENV`, `APP_DEBUG`, konfigurasi DB dan URL.
- Tabel `users` dengan peran `admin`, `operator`, `pelanggan` serta migrasi dari tabel `admin` Tugas 3.
- Remember-me berbasis token DB, rate limiting login, audit log, dan session timeout/rotation.
- CSRF pada seluruh form dan API checkout JSON.
- Halaman publik baru: masuk, daftar, akun, pesanan saya, detail pesanan.
- Panel admin RBAC, manajemen pengguna, dan log aktivitas.
- Validasi terpusat, upload aman, header keamanan, dan proteksi `.htaccess`.
- Unit test `tests/run.php` dan smoke test `tests/smoke.sh`.
- Dokumentasi README, ERD, RBAC, keamanan, pengujian, deployment, dan panduan pengguna.

### Diubah

- Autentikasi dipindah dari tabel `admin` ke `users`.
- Checkout terhubung dengan akun pelanggan bila sedang login.
- Tema gelap memakai cookie, bukan localStorage.
- AdminNeo diproteksi dan dinonaktifkan secara default.

### Perbaikan

- Mencegah IDOR pada detail pesanan pelanggan.
- Mencegah kebocoran keberadaan akun pada login admin.
- Validasi dan batas panjang pada formulir kontak.
- Pesan galat produksi disembunyikan dan dicatat ke log.
