-- ============================================================
-- Migrasi v5 -> v6: kolom ongkir pesanan
-- Jalankan: mysql -u user -p db < database/migrate_v6.sql
-- ============================================================

SET @has_ongkir = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'ongkir'
);
SET @sql = IF(@has_ongkir = 0,
    'ALTER TABLE pesanan ADD COLUMN ongkir INT NOT NULL DEFAULT 0 AFTER total',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migrasi v6 selesai: pesanan.ongkir siap.' AS hasil;
