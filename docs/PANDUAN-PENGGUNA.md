# Panduan Pengguna

## 1. Pengunjung / pelanggan

### Melihat produk

Buka menu **Produk**. Gunakan kolom pencarian untuk mencari nama atau varian. Klik **Tambah** untuk memasukkan produk ke keranjang.

### Checkout

1. Tambahkan produk ke keranjang, lalu klik **Checkout** (ikon keranjang di kanan atas).
2. Isi nama, nomor WhatsApp, email (opsional), alamat, dan catatan.
3. Pilih metode pembayaran: **COD** atau **Transfer Bank**.
4. Centang persetujuan privasi, lalu klik **Kirim Pesanan**.
5. Pesanan langsung tersimpan di database dan halaman menampilkan **kode pesanan** serta instruksi pembayaran. Tidak perlu membuka WhatsApp.

Jika sudah masuk, data nama dan nomor WhatsApp terisi otomatis dan pesanan muncul di menu **Pesanan Saya**.

### Membuat akun

Buka **Masuk → Daftar gratis**. Isi nama, username, email, nomor WhatsApp, password, dan centang persetujuan kebijakan privasi.

### Mengelola akun

Buka **Akun Saya** untuk mengubah profil atau password. Ganti password akan mencabut semua cookie “ingat saya” pada perangkat lain.

### Melihat dan membatalkan pesanan

Buka **Pesanan Saya**, lalu klik **Lihat Detail**. Pesanan dengan status **Baru** dapat dibatalkan sendiri; status lain diubah melalui WhatsApp agar tercatat penjual.

## 2. Operator toko

1. Masuk melalui panel admin (`admin/login.php`).
2. Menu yang tersedia: Dashboard, Produk, Pesanan, Pesan Kontak.
3. Tambah/edit produk pada menu **Produk** (menghapus produk bukan hak operator).
4. Pada **Pesanan**, buka detail lalu pilih status sesuai alur: Baru → Diproses → Dikirim → Selesai.
5. Pesan kontak dapat dibaca dan ditandai dibaca.

## 3. Administrator

Seluruh hak operator, ditambah:

- menghapus produk, pesanan, dan pesan;
- menambah, mengedit, menonaktifkan pengguna serta mengubah peran;
- melihat log aktivitas;
- mengaktifkan AdminNeo hanya jika diperlukan (`ENABLE_ADMINNEO=1`).

## 4. Preferensi dan cookie

- Ikon bulan/matahari mengubah tema; pilihan disimpan di cookie `berkah_theme`.
- Banner cookie dapat ditutup dengan tombol **Setuju**; pilihan disimpan di `berkah_consent`.
- Centang **Ingat saya** saat login untuk tetap masuk selama 30 hari.
- Klik **Keluar** untuk mencabut sesi dan cookie remember-me.
