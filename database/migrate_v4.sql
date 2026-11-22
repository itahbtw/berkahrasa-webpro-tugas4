-- ============================================================
-- Migrasi Tugas 3 -> Tugas 4 (aman untuk data yang sudah ada)
-- Jalankan: mysql -u root -p berkahrasa < database/migrate_v4.sql
-- ============================================================

USE berkahrasa;

-- ------------------------------------------------------------
-- 1. Tabel pengguna dengan peran (RBAC)
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

-- Salin akun dari tabel admin lama (bila ada), tanpa menimpa akun yang sudah ada.
SET @has_admin = (
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 'admin'
);
SET @sql = IF(
    @has_admin > 0,
    'INSERT INTO users (username, nama, email, password, role, aktif, created_at)
     SELECT username, nama, CONCAT(username, ''@berkahrasa.local''), password, ''admin'', 1, created_at
     FROM admin
     ON DUPLICATE KEY UPDATE username = VALUES(username)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Akun demo (jika belum ada).
INSERT INTO users (username, nama, email, no_wa, password, role, aktif) VALUES
('admin', 'Administrator Berkah Rasa', 'admin@berkahrasa.id', '081234567890',
 '$2y$12$wo/ZiDZhpP5Jq1dG0R5c5upeH/LacGr2RVpjSfNsPAVZv8rkqbwjG', 'admin', 1),
('operator01', 'Siti Aminah', 'operator@berkahrasa.id', '081298765432',
 '$2y$12$9z0Zq0cKKVwTpoDGnCwMd.HTKTM4uZbS1gXOHzZlS2K41xp8lYww2', 'operator', 1),
('budi01', 'Budi Santoso', 'budi@example.com', '081234567890',
 '$2y$12$TauZCWYenNhGh1s4aVtAuue8ZLwm5wtI8KZYyVECKARVtkjgqmoVi', 'pelanggan', 1)
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Tambah kolom no_wa bila migrasi dari versi lama.
SET @has_wa = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'no_wa'
);
SET @sql = IF(@has_wa = 0,
    'ALTER TABLE users ADD COLUMN no_wa VARCHAR(30) NULL AFTER email',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2. Tabel pendukung keamanan
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

CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60) NOT NULL,
    ip         VARCHAR(45) NOT NULL,
    sukses     TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_attempts_user_time (username, created_at),
    KEY idx_attempts_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- 3. Tambah kolom pesanan.user_id + index + foreign key
-- ------------------------------------------------------------
SET @has_col = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'user_id'
);
SET @sql = IF(@has_col = 0,
    'ALTER TABLE pesanan ADD COLUMN user_id INT NULL AFTER id',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND index_name = 'idx_pesanan_user'
);
SET @sql = IF(@has_idx = 0,
    'ALTER TABLE pesanan ADD INDEX idx_pesanan_user (user_id)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fk = (
    SELECT COUNT(*) FROM information_schema.referential_constraints
    WHERE constraint_schema = DATABASE() AND table_name = 'pesanan' AND constraint_name = 'fk_pesanan_user'
);
SET @sql = IF(@has_fk = 0,
    'ALTER TABLE pesanan ADD CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hubungkan contoh pesanan lama ke akun pelanggan demo (bila ada).
UPDATE pesanan SET user_id = (SELECT id FROM users WHERE username = 'budi01' LIMIT 1)
WHERE kode = 'BR-CONTOH-0001' AND user_id IS NULL;

-- ------------------------------------------------------------
-- 4. Bersihkan percobaan login lama
-- ------------------------------------------------------------
DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 7 DAY);

SELECT 'Migrasi v4 selesai. Tabel users, remember_tokens, login_attempts, activity_log siap.' AS hasil;
