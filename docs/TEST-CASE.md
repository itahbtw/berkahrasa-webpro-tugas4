# Test Case dan Hasil Pengujian

## 1. Unit test — `php tests/run.php`

| No  | Kelompok          | Jumlah | Hasil |
| --- | ----------------- | ------ | ----- |
| 1   | Username          | 11     | Lulus |
| 2   | Email             | 5      | Lulus |
| 3   | Nama              | 4      | Lulus |
| 4   | Nomor WhatsApp    | 6      | Lulus |
| 5   | Password          | 6      | Lulus |
| 6   | Harga             | 6      | Lulus |
| 7   | Path gambar       | 8      | Lulus |
| 8   | MIME gambar       | 6      | Lulus |
| 9   | Remember token    | 9      | Lulus |
| 10  | CSRF              | 4      | Lulus |
| 11  | RBAC              | 13     | Lulus |
| 12  | Transisi status   | 5      | Lulus |
| 13  | Pembersihan teks  | 4      | Lulus |
| 14  | Metode pembayaran | 4      | Lulus |

**Total: 91 asersi, 91 lulus, 0 gagal.**

## 2. Smoke test HTTP — `bash tests/smoke.sh`

| No  | Skenario                         | Diharapkan      | Aktual | Status |
| --- | -------------------------------- | --------------- | ------ | ------ |
| 1   | Buka beranda/produk/kontak/masuk | HTTP 200        | 200    | Lulus  |
| 2   | Form kontak memuat token CSRF    | Token ada       | Ada    | Lulus  |
| 3   | Admin area tanpa login           | Redirect 302    | 302    | Lulus  |
| 4   | AdminNeo tanpa `ENABLE_ADMINNEO` | HTTP 404        | 404    | Lulus  |
| 5   | Checkout API tanpa CSRF          | HTTP 419        | 419    | Lulus  |
| 6   | Login admin demo                 | 302 → dashboard | Sesuai | Lulus  |
| 7   | Admin buka kelola pengguna       | HTTP 200        | 200    | Lulus  |
| 8   | Login pelanggan demo             | 302 → akun      | Sesuai | Lulus  |
| 9   | Pelanggan buka dashboard admin   | HTTP 403        | 403    | Lulus  |
| 10  | Pelanggan buka pesanan sendiri   | HTTP 200        | 200    | Lulus  |
| 11  | Header keamanan                  | Ada             | Ada    | Lulus  |

**Total: 26 pemeriksaan, 26 lulus, 0 gagal.**

## 3. Uji manual tambahan

| Skenario            | Prosedur                                                          | Hasil                                                                     |
| ------------------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------- |
| IDOR detail pesanan | Login sebagai pelanggan lain, buka `pesanan-saya-detail.php?id=1` | Dialihkan ke daftar pesanan dengan pesan “tidak ditemukan pada akun Anda” |
| Remember-me         | Login dengan centang “Ingat saya”, tutup browser, buka kembali    | Tetap masuk; token lama dihapus dan token baru dibuat                     |
| Ganti password      | Ubah password di akun, lalu buka di perangkat lain                | Semua remember token dicabut, perangkat lain diminta login                |
| Tema                | Klik ikon bulan/matahari, muat ulang halaman                      | Tema tetap sesuai cookie `berkah_theme`                                   |
| Cookie consent      | Klik “Setuju”, muat ulang                                         | Banner tidak muncul lagi                                                  |
| Upload gambar       | Unggah file `.php` yang disamarkan sebagai gambar                 | Ditolak oleh validasi MIME/getimagesize                                   |
