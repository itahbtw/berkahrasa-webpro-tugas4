-- ============================================================
-- Migrasi v4 -> v5: checkout langsung di website (tanpa WhatsApp)
-- Jalankan: mysql -u root -p berkahrasa < database/migrate_v5.sql
-- ============================================================

USE berkahrasa;

SET @has_email = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'email'
);
SET @sql = IF(@has_email = 0,
    'ALTER TABLE pesanan ADD COLUMN email VARCHAR(150) NULL AFTER nama_pembeli',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_bayar = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'pembayaran'
);
SET @sql = IF(@has_bayar = 0,
    'ALTER TABLE pesanan ADD COLUMN pembayaran ENUM(''cod'',''transfer'') NOT NULL DEFAULT ''cod'' AFTER catatan',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migrasi v5 selesai: pesanan.email dan pesanan.pembayaran siap.' AS hasil;
