# Panduan Deployment

## 1. Mode lokal (XAMPP)

1. Letakkan folder di `htdocs/`.
2. Salin `.env.example` → `.env`, isi koneksi database.
3. Impor `database/schema.sql` (baru) atau `database/migrate_v4.sql` + `database/migrate_v5.sql` + `database/migrate_v6.sql` (dari Tugas 3/v4/v5).
4. Buka `http://localhost/<folder>/index.php`.

## 2. Mode produksi (shared hosting)

1. **Kode**: unggah isi folder proyek ke `public_html/` (atau folder docroot). Jangan unggah `.env` dari lokal; buat `.env` baru di server.
2. **Environment produksi**:
   ```ini
   APP_ENV=production
   APP_DEBUG=0
   APP_HTTPS=1
   APP_BASE_URL=
   DB_HOST=localhost
   DB_NAME=nama_db_hosting
   DB_USER=user_db_hosting
   DB_PASS=password_kuat
   ENABLE_ADMINNEO=0
   ```
3. **Database**: buat database dan user melalui cPanel, lalu impor `database/schema.sql` via phpMyAdmin. Jika database sudah berisi v4/v5, jalankan `database/migrate_v5.sql` lalu `database/migrate_v6.sql`.
4. **Izin**: `gambar/uploads` dan `logs` harus dapat ditulis PHP (0755/0775), tetapi jangan 0777.
5. **Proteksi**: pastikan `.htaccess` aktif (`AllowOverride All`) atau pindahkan folder sensitif di luar docroot.
6. **SSL**: aktifkan HTTPS/SSL gratis, lalu set `APP_HTTPS=1`.
7. **Verifikasi**: buka halaman depan, login admin, checkout uji, dan periksa `logs/error.log`.

## 3. Paket deploy

Jalankan `bash deploy/build-package.sh` untuk menghasilkan ZIP yang sudah menyertakan `.env.example` dan tidak memuat `.env`, `logs/*.log`, atau unggahan pengguna.

## 4. Rollback

- Simpan dump database sebelum migrasi.
- Jika gagal, kembalikan file sebelumnya dan impor dump lama.
- Untuk migrasi Tugas 3 → v4, tabel lama tidak dihapus sehingga aplikasi lama masih dapat dijalankan sementara.

## 5. Status saat ini

Aplikasi **sudah online permanen** di:

**https://berkahrasa.ifree.page/**

Panel admin: **https://berkahrasa.ifree.page/admin/login.php**

Akun hosting: InfinityFree (`if0_43491565`), PHP 8.3, MariaDB, docroot `htdocs`.
Database: `if0_43491565_berkahrasadb` dengan user `if0_43491565` di host `sql202.infinityfree.com`.

Konfigurasi produksi tersimpan di `.env` pada server (tidak ikut disertakan dalam paket):
`APP_ENV=production`, `APP_DEBUG=0`, `APP_HTTPS=1`, `ENABLE_ADMINNEO=0`.

### Catatan deploy InfinityFree

- Beberapa shared hosting menonaktifkan `putenv()`, sehingga `config/config.php` juga membaca nilai dari `$_ENV` (sudah diterapkan).
- Untuk database baru cukup impor `database/schema.sql` (sudah mencakup kolom v5).
- `deploy/install-on-server.php` adalah installer sekali pakai; sudah dihapus dari server setelah instalasi.
- Pastikan domain `berkahrasa.ifree.page` terdaftar di client area InfinityFree dan sertifikat SSL aktif.
