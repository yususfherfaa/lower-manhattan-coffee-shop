-- ============================================================================
-- MIGRASI KEAMANAN DAN INTEGRITAS DATA (PR-03 / PR-11)
-- ============================================================================
-- CATATAN PENTING & PROSEDUR CADANGAN SEBELUM MENJALANKAN MIGRASI:
-- 1. Jangan jalankan file ini secara otomatis ke database produksi tanpa backup!
-- 2. Buat cadangan penuh database dengan mysqldump di terminal/PowerShell:
--      mysqldump -u root -p warkop_bahagia > database/backup_sebelum_migrasi_keamanan.sql
-- 3. File SQL ini dirancang IDEMPOTENT (aman dijalankan berulang kali tanpa error).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 0. CEK DATA DUPLIKAT SEBELUM CONSTRAINT UNIQUE
-- ----------------------------------------------------------------------------
-- Jalankan tiga SELECT ini terlebih dahulu pada salinan database.
-- Hasil setiap query harus kosong sebelum melanjutkan ke bagian migrasi.
-- Jika ada hasil, rapikan duplikat secara manual; jangan hapus riwayat transaksi.
-- Duplikat settings.nama yang NULL tidak bertentangan dengan UNIQUE MySQL.
SELECT nama, COUNT(*) AS jumlah
FROM settings
WHERE nama IS NOT NULL
GROUP BY nama
HAVING COUNT(*) > 1;

-- Periksa duplikat username akun:
SELECT username, COUNT(*) AS jumlah
FROM users
GROUP BY username
HAVING COUNT(*) > 1;

-- Periksa duplikat id_order di payments:
SELECT id_order, COUNT(*) AS jumlah
FROM payments
GROUP BY id_order
HAVING COUNT(*) > 1;

-- ----------------------------------------------------------------------------
-- 1. PENYESUAIAN FOREIGN KEY KE RESTRICT & HAPUS CASCADE BERBAHAYA
-- ----------------------------------------------------------------------------

-- A. order_items -> products (fk_orderitems_produk)
-- Hapus FK lama jika ada, lalu buat baru dengan ON DELETE RESTRICT ON UPDATE CASCADE
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'order_items'
      AND COLUMN_NAME = 'id_produk'
      AND REFERENCED_TABLE_NAME = 'products'
    LIMIT 1
);
SET @sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE `order_items` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_produk`
  FOREIGN KEY (`id_produk`) REFERENCES `products` (`id_produk`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- B. payments -> users (fk_payment_user)
-- Mencegah penghapusan kasir menghilangkan riwayat pembayaran
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'payments'
      AND COLUMN_NAME = 'id_user'
      AND REFERENCED_TABLE_NAME = 'users'
    LIMIT 1
);
SET @sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE `payments` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_user`
  FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- C. orders -> tables_meja (fk_orders_meja)
-- Mencegah penghapusan meja menghilangkan riwayat transaksi orders
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND COLUMN_NAME = 'id_meja'
      AND REFERENCED_TABLE_NAME = 'tables_meja'
    LIMIT 1
);
SET @sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE `orders` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_meja`
  FOREIGN KEY (`id_meja`) REFERENCES `tables_meja` (`id_meja`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- D. data_products -> products
-- Hapus ON DELETE CASCADE agar jejak riwayat aktivitas log tidak terhapus
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'data_products'
      AND COLUMN_NAME = 'id_produk'
      AND REFERENCED_TABLE_NAME = 'products'
    LIMIT 1
);
SET @sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE `data_products` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ----------------------------------------------------------------------------
-- 2. PENAMBAHAN UNIQUE CONSTRAINT DAN INDEKS PERFORMA
-- ----------------------------------------------------------------------------

-- UNIQUE pada settings.nama
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'settings'
      AND INDEX_NAME = 'uniq_settings_nama'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `settings` ADD UNIQUE KEY `uniq_settings_nama` (`nama`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- UNIQUE pada users.username
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'uniq_users_username'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_username` (`username`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- UNIQUE pada payments.id_order (1 order memiliki 1 pembayaran)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'payments'
      AND INDEX_NAME = 'uniq_payments_id_order'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `payments` ADD UNIQUE KEY `uniq_payments_id_order` (`id_order`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Indeks komposit orders(status, created_at)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND INDEX_NAME = 'idx_orders_status_created'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `orders` ADD INDEX `idx_orders_status_created` (`status`, `created_at`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ----------------------------------------------------------------------------
-- 3. KOLOM AKTIF PADA TABEL PRODUCTS DAN USERS (SOFT DELETE / NONAKTIFKAN)
-- ----------------------------------------------------------------------------

-- Kolom aktif pada products
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'aktif'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `products` ADD COLUMN `aktif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `deskripsi`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Kolom aktif pada users
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'aktif'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `aktif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ----------------------------------------------------------------------------
-- 4. PENYERAGAMAN FORMAT ID_USER MENJADI USR000000X (7 DIGIT ANGKA)
-- ----------------------------------------------------------------------------

-- Konversi data lama USR002 -> USR0000002, USR003 -> USR0000003
UPDATE `payments`
SET `id_user` = CONCAT('USR', LPAD(SUBSTRING(`id_user`, 4), 7, '0'))
WHERE `id_user` REGEXP '^USR[0-9]{1,6}$';

UPDATE `users`
SET `id_user` = CONCAT('USR', LPAD(SUBSTRING(`id_user`, 4), 7, '0'))
WHERE `id_user` REGEXP '^USR[0-9]{1,6}$';

SET FOREIGN_KEY_CHECKS = 1;
