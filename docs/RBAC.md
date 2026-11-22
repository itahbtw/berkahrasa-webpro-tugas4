# RBAC — Peran dan Izin

Sumber: `includes/validators.php` (`role_permissions()`, `role_can()`), `includes/security.php` (`require_*()`).

| Izin                      | admin | operator | pelanggan |
| ------------------------- | :---: | :------: | :-------: |
| `dashboard.view`          |   ✔   |    ✔     |     –     |
| `produk.view/create/edit` |   ✔   |    ✔     |     –     |
| `produk.hapus`            |   ✔   |    –     |     –     |
| `pesanan.view/status`     |   ✔   |    ✔     |     –     |
| `pesanan.hapus`           |   ✔   |    –     |     –     |
| `pesan.view/read`         |   ✔   |    ✔     |     –     |
| `pesan.hapus`             |   ✔   |    –     |     –     |
| `akun.view/edit`          |   ✔   |    ✔     |     ✔     |
| `pesanan.own`             |   ✔   |    ✔     |     ✔     |
| Manajemen pengguna        |   ✔   |    –     |     –     |
| Audit log                 |   ✔   |    –     |     –     |
| AdminNeo                  |   ✔   |    –     |     –     |

## Aturan penting

- `admin` memakai wildcard `*` sehingga seluruh izin diberikan.
- `operator` tidak dapat menghapus produk/pesanan/pesan dan tidak dapat mengelola pengguna.
- `pelanggan` hanya dapat mengelola akun sendiri dan melihat pesanan sendiri.
- Transisi status pesanan:
  - admin: bebas memilih semua status;
  - operator: `baru → diproses → dikirim → selesai`, dengan opsi `dibatalkan` dari `baru`/`diproses`.
- Pembatalan oleh pelanggan hanya boleh saat status masih `baru`.
- `admin/login.php` memakai pesan generik “Username atau password salah.” untuk akun non-staf agar tidak membocorkan keberadaan akun.
