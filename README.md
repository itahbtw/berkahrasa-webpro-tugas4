# Berkah Rasa — Tugas 4 / UAS WebPro TI C

Aplikasi web UMKM “Berkah Rasa — Camilan Singkong Desa”. Tugas 4 melanjutkan Tugas 3 dengan **session, cookie, autentikasi, otorisasi berbasis peran, validasi/keamanan, pengujian, dokumentasi, dan deployment**.

## 1. Informasi singkat

| Item            | Keterangan                                              |
| --------------- | ------------------------------------------------------- |
| Nama aplikasi   | Berkah Rasa                                             |
| Versi           | 4.2.1                                                   |
| Tema            | Session, Cookie, dan Keamanan Pengembangan Aplikasi Web |
| Kelompok        | 2 — TI C                                                |
| Teknologi       | PHP 8, MariaDB 10.4+/MySQL 8, HTML5, CSS3, JavaScript   |
| Pola arsitektur | PHP tradisional (tanpa framework), PDO, MVC-sederhana   |
| URL produksi    | https://berkahrasa.ifree.page/                          |

## 2. Fitur utama

### Publik

- Katalog produk dari database, pencarian, dan keranjang berbasis `localStorage`.
- Checkout langsung di website (`checkout.php`) dengan pencatatan pesanan ke database melalui `api/order.php`, pilihan COD/transfer, dan konfirmasi pesanan.
- Formulir kontak dengan CSRF, honeypot, pembatasan panjang, dan throttle.
- Halaman akun: profil, ganti password, dan riwayat pesanan.
- Cookie persetujuan (`berkah_consent`) dan preferensi tema (`berkah_theme`).

### Admin / staf

- Login admin dengan pembatasan percobaan, session rotation, cookie HttpOnly/SameSite, remember-me berbasis token DB.
- RBAC tiga peran: `admin`, `operator`, `pelanggan`.
- CRUD produk, monitoring dan perubahan status pesanan, pesan kontak.
- Manajemen pengguna dan audit log (khusus admin).
- AdminNeo opsional, hanya untuk admin yang login dan `ENABLE_ADMINNEO=1`.

## 3. Instalasi lokal (XAMPP)

1. Salin folder proyek ke `htdocs/`.
2. Salin `.env.example` menjadi `.env`. Untuk lokal, biarkan `APP_ENV=local` dan `APP_DEBUG=1`.
3. Buat database:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Untuk upgrade dari Tugas 3:
   ```bash
   mysql -u root -p berkahrasa < database/migrate_v4.sql
   mysql -u root -p berkahrasa < database/migrate_v5.sql
   mysql -u root -p berkahrasa < database/migrate_v6.sql
   ```
4. Pastikan `DB_USER`, `DB_PASS`, `DB_NAME`, dan `DB_PORT` di `.env` sesuai server.
5. Buka `http://localhost/nama-folder/index.php`.

Akun demo (hanya ditampilkan saat `APP_DEBUG=1`):

| Username     | Password        | Peran         |
| ------------ | --------------- | ------------- |
| `admin`      | `admin123`      | Administrator |
| `operator01` | `Operator#2026` | Operator Toko |
| `budi01`     | `Budi#2026`     | Pelanggan     |

> Ganti seluruh password demo sebelum produksi.

## 4. Menjalankan pengujian

```bash
# Unit test (validasi, RBAC, token, CSRF, status)
php tests/run.php

# Smoke test HTTP (jalankan server dulu)
php -S 127.0.0.1:8080
bash tests/smoke.sh http://127.0.0.1:8080
```

## 5. Struktur folder

```text
config/        konfigurasi environment, koneksi PDO, URL helper
database/      schema.sql dan migrate_v4.sql
includes/      security.php, validators.php, functions.php, header/footer
admin/         panel admin (auth, produk, pesanan, pesan, pengguna, log)
api/           endpoint checkout JSON
gambar/        aset produk dan folder uploads
docs/          dokumentasi ERD, RBAC, keamanan, deploy, pengujian, panduan
tests/         unit test dan smoke test
logs/          error log (tidak untuk publik)
```

## 6. Dokumentasi lain

- `docs/ERD.md`
- `docs/RBAC.md`
- `docs/KEAMANAN.md`
- `docs/TEST-CASE.md`
- `docs/DEPLOY.md`
- `docs/PANDUAN-PENGGUNA.md`
- `docs/CHANGELOG.md`

## 7. Catatan keamanan

- Jangan pernah mengunggah `.env`, `database/*.sql`, `logs/*.log`, atau folder `tests/` ke web root publik tanpa proteksi `.htaccess`.
- Default `APP_ENV=production` dan `APP_DEBUG=false`.
- `ENABLE_ADMINNEO=0` secara default; aktifkan hanya di lokal.
- Semua perubahan data memakai CSRF; semua query memakai prepared statement.
