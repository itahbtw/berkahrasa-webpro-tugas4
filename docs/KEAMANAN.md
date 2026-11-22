# Keamanan Aplikasi

## 1. Session

- Nama cookie: `BERKAHRASASESSID`.
- Atribut: `HttpOnly`, `SameSite=Lax`, `Secure` saat HTTPS, path mengikuti `BASE_URL`.
- Idle timeout 30 menit, absolute timeout 8 jam.
- ID sesi dirotasi saat login, logout, dan setiap 15 menit.
- Fingerprint User-Agent disimpan sebagai hash SHA-256; perubahan UA mencabut sesi.
- `use_strict_mode` dan `use_only_cookies` aktif.

## 2. Remember-me

- Cookie `berkah_remember` berisi `selector:validator`.
- Database hanya menyimpan `validator_hash = SHA-256(validator)`.
- Token dicoba ulang (rotation) setiap pemakaian; maksimal 5 token per akun.
- Mismatch validator mencabut semua token akun dan menulis log `cookie.tidak_valid`.
- Logout dan ganti password mencabut token.

## 3. CSRF

- Setiap form memakai `csrf_field()`; API JSON memakai header `X-CSRF-Token`.
- Perbandingan memakai `hash_equals()`.
- API checkout juga memeriksa `Origin` dan membatasi 10 pesanan / 10 menit / sesi.

## 4. Autorisasi & IDOR

- Guard: `require_login()`, `require_staff()`, `require_admin()`, `require_permission()`.
- Detail pesanan pelanggan memakai `get_order_for_user($id, $userId)` sehingga pesanan orang lain tidak dapat dibuka.
- Halaman admin hanya staf; halaman admin khusus admin menolak operator dengan HTTP 403.

## 5. Input & output

- `clean_text()` membuang karakter kontrol dan membatasi panjang; prepared statement PDO untuk semua query.
- Validasi username, email, nama, telepon, password, harga, dan path gambar terpusat di `includes/validators.php`.
- Output HTML dienkripsi dengan `e()`; string JavaScript dienkripsi dengan `escapeHtml()`.
- Upload gambar: batas 3 MB, MIME asli via `finfo`, dimensi 40–6000 px, nama acak, `.htaccess` melarang eksekusi skrip di `gambar/uploads/`.

## 6. Header & konfigurasi

- Header: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, CSP, dan HSTS saat HTTPS.
- `APP_ENV=production` menyembunyikan detail galat; log ke `logs/error.log`.
- Kredensial dibaca dari `.env`, bukan source code.
- AdminNeo dimatikan default dan hanya untuk admin yang login (`ENABLE_ADMINNEO=1`).
- `.htaccess` memblokir akses ke `config/`, `logs/`, `database/`, `tests/`, dan file `.env`/`.sql`.

## 7. Pencatatan

`activity_log` mencatat login sukses/gagal, logout, perubahan profil/password, pembuatan pesanan, perubahan status, pembatalan, kontak, akses ditolak, dan anomasi token.
