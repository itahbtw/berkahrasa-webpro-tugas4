# Berkah Rasa — Tugas 4 / UAS WebPro

Aplikasi web UMKM **Berkah Rasa — Camilan Singkong Desa**, kelanjutan Tugas 3.
Selain CRUD produk/pesanan/pesan kontak, aplikasi ini memiliki session aman,
cookie, autentikasi, otorisasi tiga peran, validasi/keamanan, pengujian, dan
checkout langsung di website.

## Fitur utama

- Katalog produk dari database + pencarian + keranjang `localStorage`
- Akun pelanggan, daftar, ganti password, riwayat & lacak pesanan
- Checkout di website: COD / transfer BCA, ongkir Rp10.000 untuk subtotal < Rp100.000
- Panel admin dengan RBAC: admin, operator, pelanggan
- CSRF, prepared statement, rate limit login, remember-me, audit log, IDOR-safe
- Dark mode, cookie consent, responsive + hamburger di layar kecil

## Struktur ringkas

```
config/config.php        konstanta env, aset, URL helper
config/database.php      koneksi PDO
database/schema.sql      skema + data awal (sudah termasuk ongkir)
database/migrate_v4..v6.sql  migrasi dari Tugas 3/v4/v5
includes/                security, validators, functions, header, footer
admin/                   panel admin (dashboard, produk, pesanan, pesan, pengguna, log)
api/order.php            API checkout JSON
checkout.php             halaman checkout + struk
lacak-pesanan.php        cek status tanpa login
tests/                   unit test + smoke test
```

## Cara menjalankan (XAMPP)

1. Salin folder proyek ke `htdocs`.
2. Salin `.env.example` menjadi `.env` (default: DB `berkahrasa`, user `berkah`, pass `berkah123`).
3. Impor `database/schema.sql`, atau untuk upgrade jalankan berurutan:
   `migrate_v4.sql` → `migrate_v5.sql` → `migrate_v6.sql`.
4. Buka `http://localhost/<nama-folder>/index.php`.
5. Panel admin: `/admin/login.php` — `admin` / `admin123`
   (`operator01` / `Operator#2026`, `budi01` / `Budi#2026`).

Ganti password demo sebelum produksi.

## AdminNeo

`adminneo.php` hanya aktif jika `ENABLE_ADMINNEO=1` dan diakses oleh admin yang
sudah login. Kredensial database dibaca dari `.env`, bukan hardcode.

## Pengujian

```bash
php tests/run.php
bash tests/smoke.sh http://localhost/<nama-folder>
```
