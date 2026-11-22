-- ============================================================
-- Berkah Rasa — Skema Database v4 (MySQL 8 / MariaDB 10.4+)
-- Tugas 4 / UAS WebPro TI-C
--
-- Impor untuk instalasi BARU:
--   mysql -u root -p < database/schema.sql
--
-- Untuk database Tugas 3 yang sudah berisi data, gunakan:
--   mysql -u root -p < database/migrate_v4.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS berkahrasa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE berkahrasa;

-- ------------------------------------------------------------
-- User aplikasi (koneksi default di config/.env).
-- ------------------------------------------------------------
CREATE USER IF NOT EXISTS 'berkah'@'localhost' IDENTIFIED BY 'berkah123';
CREATE USER IF NOT EXISTS 'berkah'@'127.0.0.1' IDENTIFIED BY 'berkah123';
GRANT ALL PRIVILEGES ON berkahrasa.* TO 'berkah'@'localhost';
GRANT ALL PRIVILEGES ON berkahrasa.* TO 'berkah'@'127.0.0.1';

-- ------------------------------------------------------------
-- Tabel produk
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produk (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(150) NOT NULL,
    varian      VARCHAR(200) NOT NULL DEFAULT '',
    ukuran      VARCHAR(100) NOT NULL DEFAULT '',
    harga       INT NOT NULL DEFAULT 0,
    gambar      VARCHAR(255) NOT NULL DEFAULT 'gambar/logo-icon.svg',
    tag         VARCHAR(50) NOT NULL DEFAULT '',
    deskripsi   TEXT NULL,
    aktif       TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel pengguna: admin, operator, pelanggan (RBAC)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60) NOT NULL,
    nama          VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    no_wa         VARCHAR(30) NULL,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('admin','operator','pelanggan') NOT NULL DEFAULT 'pelanggan',
    aktif         TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel pesanan (header)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NULL,
    kode         VARCHAR(30) NOT NULL UNIQUE,
    nama_pembeli VARCHAR(120) NOT NULL,
    email        VARCHAR(150) NULL,
    no_wa        VARCHAR(30) NOT NULL,
    alamat       TEXT NULL,
    catatan      TEXT NULL,
    pembayaran   ENUM('cod','transfer') NOT NULL DEFAULT 'cod',
    total        INT NOT NULL DEFAULT 0,
    ongkir       INT NOT NULL DEFAULT 0,
    status       ENUM('baru','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'baru',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pesanan_user (user_id),
    KEY idx_pesanan_status (status),
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel detail pesanan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan_detail (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id  INT NOT NULL,
    produk_id   INT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga       INT NOT NULL DEFAULT 0,
    qty         INT NOT NULL DEFAULT 1,
    subtotal    INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(id) ON DELETE CASCADE,
    CONSTRAINT fk_detail_produk FOREIGN KEY (produk_id)
        REFERENCES produk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel pesan kontak
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesan_kontak (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) NULL,
    pesan      TEXT NOT NULL,
    dibaca     TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Token "ingat saya" (selector + validator hash)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS remember_tokens (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    selector       VARCHAR(32) NOT NULL,
    validator_hash CHAR(64) NOT NULL,
    user_id        INT NOT NULL,
    expires_at     DATETIME NOT NULL,
    user_agent     VARCHAR(255) NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_remember_selector (selector),
    KEY idx_remember_user (user_id),
    KEY idx_remember_expires (expires_at),
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Percobaan login (rate limit brute force)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60) NOT NULL,
    ip         VARCHAR(45) NOT NULL,
    sukses     TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_attempts_user_time (username, created_at),
    KEY idx_attempts_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Audit log aktivitas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NULL,
    aksi       VARCHAR(60) NOT NULL,
    detail     TEXT NULL,
    ip         VARCHAR(45) NOT NULL DEFAULT '',
    user_agent VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_log_user (user_id),
    KEY idx_log_time (created_at),
    CONSTRAINT fk_log_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Data awal produk
-- ------------------------------------------------------------
INSERT IGNORE INTO produk (id, nama, varian, ukuran, harga, gambar, tag, deskripsi) VALUES
(1, 'Keripik Singkong Original', 'Gurih asin, iris tipis renyah', '50g / 125g / 250g', 5000, 'gambar/keripiksingkong.jpg', 'Terlaris', 'Keripik singkong iris tipis dengan rasa gurih asin alami.'),
(2, 'Keripik Singkong Balado', 'Manis pedas, bumbu melimpah', '50g / 125g / 250g', 6000, 'gambar/keripik-balado.jpg', 'Favorit', 'Keripik singkong dengan bumbu balado manis pedas melimpah.'),
(3, 'Stik Singkong Keju', 'Keju original & pedas', 'Kecil / Besar', 8000, 'gambar/stiksingkongkeju.webp', 'Rekomendasi', 'Stik singkong renyah dengan taburan keju gurih.'),
(4, 'Opak Singkong', 'Original gurih, mekar saat digoreng', 'Isi 10 keping', 10000, 'gambar/opak-bersih.jpg', 'Tradisional', 'Opak singkong tradisional yang mekar saat digoreng.'),
(5, 'Tiwul Instan Gula Aren', 'Manis legit, praktis diseduh', '250g', 7000, 'gambar/tiwul-gula-aren.jpg', 'Khas Desa', 'Tiwul instan praktis dengan pemanis gula aren.'),
(6, 'Gatot Kering', 'Gurih kenyal, taburan kelapa', '250g', 7000, 'gambar/gatot.jpg', 'Langka', 'Gatot kering gurih kenyal dengan taburan kelapa.');

-- ------------------------------------------------------------
-- Akun awal (demo):
--   admin      / admin123       -> Administrator
--   operator01 / Operator#2026  -> Operator Toko
--   budi01     / Budi#2026      -> Pelanggan
-- GANTI PASSWORD AKUN DEMO SEBELUM PRODUKSI.
-- ------------------------------------------------------------
INSERT INTO users (id, username, nama, email, no_wa, password, role, aktif, created_at) VALUES
(1, 'admin', 'Administrator Berkah Rasa', 'admin@berkahrasa.id', '081234567890',
 '$2y$12$wo/ZiDZhpP5Jq1dG0R5c5upeH/LacGr2RVpjSfNsPAVZv8rkqbwjG', 'admin', 1, NOW()),
(2, 'operator01', 'Siti Aminah', 'operator@berkahrasa.id', '081298765432',
 '$2y$12$9z0Zq0cKKVwTpoDGnCwMd.HTKTM4uZbS1gXOHzZlS2K41xp8lYww2', 'operator', 1, NOW()),
(3, 'budi01', 'Budi Santoso', 'budi@example.com', '081234567890',
 '$2y$12$TauZCWYenNhGh1s4aVtAuue8ZLwm5wtI8KZYyVECKARVtkjgqmoVi', 'pelanggan', 1, NOW())
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- ------------------------------------------------------------
-- Contoh data pesanan & pesan (opsional, boleh dihapus)
-- ------------------------------------------------------------
INSERT IGNORE INTO pesanan (id, user_id, kode, nama_pembeli, no_wa, alamat, catatan, total, status) VALUES
(1, 3, 'BR-CONTOH-0001', 'Budi Santoso', '081234567890', 'Jl. Raya Desa Makmur No. 5', 'Kirim sore hari', 18000, 'baru');

INSERT IGNORE INTO pesanan_detail (id, pesanan_id, produk_id, nama_produk, harga, qty, subtotal) VALUES
(1, 1, 2, 'Keripik Singkong Balado', 6000, 3, 18000);

INSERT IGNORE INTO pesan_kontak (id, nama, email, pesan, dibaca) VALUES
(1, 'Agus Budi', 'agus@example.com', 'Halo, saya ingin menjadi reseller. Apakah masih terbuka?', 0);
