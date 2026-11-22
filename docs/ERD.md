# ERD / Skema Database v4

Database: `berkahrasa` (utf8mb4, InnoDB). Sumber: `database/schema.sql`, `database/migrate_v4.sql`.

## Tabel dan relasi

```text
users (id, username, nama, email, no_wa, password, role, aktif, ...)
  │
  ├──< remember_tokens (user_id)      token "ingat saya"
  ├──< login_attempts  (username/ip)  rate limit login
  ├──< activity_log    (user_id)      audit trail
  └──< pesanan         (user_id, NULL untuk tamu)
         └──< pesanan_detail (pesanan_id, produk_id)

produk ──< pesanan_detail (produk_id, ON DELETE SET NULL)
pesan_kontak (berdiri sendiri)
```

## Ringkasan tabel

| Tabel             | Fungsi                 | Kolom penting                                                           |
| ----------------- | ---------------------- | ----------------------------------------------------------------------- |
| `users`           | Autentikasi & RBAC     | `username` UNIQUE, `email` UNIQUE, `role` ENUM, `aktif`                 |
| `produk`          | Katalog                | `nama`, `harga`, `gambar`, `aktif`                                      |
| `pesanan`         | Header pesanan         | `kode` UNIQUE, `user_id` NULL, `email`, `pembayaran`, `total`, `status` |
| `pesanan_detail`  | Item pesanan           | `nama_produk`, `harga`, `qty`, `subtotal` (snapshot harga)              |
| `pesan_kontak`    | Pesan formulir         | `nama`, `email`, `pesan`, `dibaca`                                      |
| `remember_tokens` | Login otomatis         | `selector` UNIQUE, `validator_hash` SHA-256, `expires_at`               |
| `login_attempts`  | Brute-force protection | `username`, `ip`, `sukses`, `created_at`                                |
| `activity_log`    | Audit                  | `user_id`, `aksi`, `detail`, `ip`, `created_at`                         |

## Catatan desain

- `pesanan.user_id` nullable agar checkout tamu tetap berfungsi.
- `pesanan_detail` menyimpan `nama_produk` dan `harga` sebagai snapshot agar riwayat tidak berubah saat harga produk diubah.
- Foreign key `ON DELETE SET NULL` menjaga riwayat tetap ada meski produk/user dihapus; akun staf cukup dinonaktifkan.
- `ON DELETE CASCADE` dipakai pada token remember-me.
