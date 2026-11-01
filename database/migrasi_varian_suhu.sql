-- ====================================================================
-- MIGRASI VARIAN SUHU & PRODUK BARU WARKOP BAHAGIA
-- File: database/migrasi_varian_suhu.sql
-- ====================================================================

-- 1. Tambah kolom varian_suhu dan tambahan_harga_es ke tabel products (jika belum ada)
SET @exist_varian_suhu := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'varian_suhu'
);
SET @sql_varian := IF(@exist_varian_suhu = 0, 
    'ALTER TABLE `products` ADD COLUMN `varian_suhu` TINYINT(1) NOT NULL DEFAULT 0 AFTER `stok`', 
    'SELECT "varian_suhu already exists"'
);
PREPARE stmt_var FROM @sql_varian;
EXECUTE stmt_var;
DEALLOCATE PREPARE stmt_var;

SET @exist_tambahan_es := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'tambahan_harga_es'
);
SET @sql_tambahan := IF(@exist_tambahan_es = 0, 
    'ALTER TABLE `products` ADD COLUMN `tambahan_harga_es` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `varian_suhu`', 
    'SELECT "tambahan_harga_es already exists"'
);
PREPARE stmt_tam FROM @sql_tambahan;
EXECUTE stmt_tam;
DEALLOCATE PREPARE stmt_tam;

-- 2. Tambah kolom suhu ke tabel order_items (jika belum ada)
SET @exist_suhu := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'suhu'
);
SET @sql_suhu := IF(@exist_suhu = 0, 
    'ALTER TABLE `order_items` ADD COLUMN `suhu` VARCHAR(10) NULL DEFAULT NULL AFTER `kategori`', 
    'SELECT "suhu already exists"'
);
PREPARE stmt_suhu FROM @sql_suhu;
EXECUTE stmt_suhu;
DEALLOCATE PREPARE stmt_suhu;

-- 3. Update & Gabungkan Menu Minuman Lama
-- A. PRD0000025 "Kopi Item" -> "Kopi Hitam" (varian_suhu = 1)
UPDATE `products` SET `nama` = 'Kopi Hitam', `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000025';
UPDATE `data_products` SET `nama_produk` = 'Kopi Hitam' WHERE `id_produk` = 'PRD0000025';

-- B. PRD0000024 "Kopi Susu", PRD0000023 "Capucinno"
UPDATE `products` SET `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` IN ('PRD0000024', 'PRD0000023');

-- C. PRD0000018 "Es Teh Manis" -> "Teh Manis" (varian_suhu = 1)
UPDATE `products` SET `nama` = 'Teh Manis', `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000018';
UPDATE `data_products` SET `nama_produk` = 'Teh Manis' WHERE `id_produk` = 'PRD0000018';

-- Pindahkan stok PRD0000020 ("Teh Anget Manis") ke PRD0000018 ("Teh Manis") jika PRD0000020 ada.
-- Aman dijalankan ulang: setelah PRD0000020 dihapus, stok tidak ditambah lagi.
UPDATE `products`
SET `stok` = `stok` + (
    SELECT COALESCE(p20.stok, 0)
    FROM (SELECT `stok` FROM `products` WHERE `id_produk` = 'PRD0000020') AS p20
)
WHERE `id_produk` = 'PRD0000018'
  AND EXISTS (SELECT 1 FROM (SELECT `id_produk` FROM `products` WHERE `id_produk` = 'PRD0000020') AS p20_exists);

-- Update foreign key data_products dari PRD0000020 ke PRD0000018 sebelum menghapus
UPDATE `data_products` SET `id_produk` = 'PRD0000018', `nama_produk` = 'Teh Manis (Gabungan)' WHERE `id_produk` = 'PRD0000020';

-- Hapus PRD0000020 dari products hanya jika tidak ada referensi pesanan.
DELETE FROM `products`
WHERE `id_produk` = 'PRD0000020'
  AND NOT EXISTS (SELECT 1 FROM (SELECT `id_produk` FROM `order_items` WHERE `id_produk` = 'PRD0000020' LIMIT 1) AS oi20);

-- D. Rename dan set varian_suhu untuk minuman lainnya:
-- PRD0000016 "Es Milo" -> "Milo"
UPDATE `products` SET `nama` = 'Milo', `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000016';
UPDATE `data_products` SET `nama_produk` = 'Milo' WHERE `id_produk` = 'PRD0000016';

-- PRD0000017 "Es Jeruk" -> "Jeruk"
UPDATE `products` SET `nama` = 'Jeruk', `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000017';
UPDATE `data_products` SET `nama_produk` = 'Jeruk' WHERE `id_produk` = 'PRD0000017';

-- PRD0000019 "Es Lemon Tea" -> "Lemon Tea"
UPDATE `products` SET `nama` = 'Lemon Tea', `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000019';
UPDATE `data_products` SET `nama_produk` = 'Lemon Tea' WHERE `id_produk` = 'PRD0000019';

-- PRD0000021 "Susu Putih" (varian_suhu = 1)
UPDATE `products` SET `varian_suhu` = 1, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000021';

-- PRD0000022 "Air Mineral" (tanpa varian)
UPDATE `products` SET `varian_suhu` = 0, `tambahan_harga_es` = 0.00 WHERE `id_produk` = 'PRD0000022';

-- 4. Tambah 5 Produk Baru (PRD0000026 sampai PRD0000030)
INSERT INTO `products` (`id_produk`, `nama`, `kategori`, `harga`, `stok`, `varian_suhu`, `tambahan_harga_es`, `gambar`) VALUES
('PRD0000026', 'Nasi Kuning', 'Makanan Berat', 10000.00, 20, 0, 0.00, 'nasi-kuning.jpg'),
('PRD0000027', 'Nasi Uduk', 'Makanan Berat', 10000.00, 20, 0, 0.00, 'nasi-uduk.jpg'),
('PRD0000028', 'Lontong Sayur', 'Makanan Berat', 10000.00, 20, 0, 0.00, 'lontong-sayur.jpg'),
('PRD0000029', 'Bubur Ayam', 'Makanan Berat', 12000.00, 20, 0, 0.00, 'bubur-ayam.jpg'),
('PRD0000030', 'Ayam Geprek + Nasi', 'Makanan Berat', 15000.00, 20, 0, 0.00, 'ayam-geprek.png')
ON DUPLICATE KEY UPDATE 
    `nama` = VALUES(`nama`),
    `kategori` = VALUES(`kategori`),
    `harga` = VALUES(`harga`),
    `stok` = VALUES(`stok`),
    `varian_suhu` = VALUES(`varian_suhu`),
    `gambar` = VALUES(`gambar`);

-- Catat aktivitas penambahan produk baru ke data_products
INSERT INTO `data_products` (`id_data`, `aktivitas`, `id_produk`, `nama_produk`, `nama_kasir`, `created_at`) VALUES
('DAT0000026', 'Tambah Produk', 'PRD0000026', 'Nasi Kuning', 'Kasir Demo', CURRENT_TIMESTAMP),
('DAT0000027', 'Tambah Produk', 'PRD0000027', 'Nasi Uduk', 'Kasir Demo', CURRENT_TIMESTAMP),
('DAT0000028', 'Tambah Produk', 'PRD0000028', 'Lontong Sayur', 'Kasir Demo', CURRENT_TIMESTAMP),
('DAT0000029', 'Tambah Produk', 'PRD0000029', 'Bubur Ayam', 'Kasir Demo', CURRENT_TIMESTAMP),
('DAT0000030', 'Tambah Produk', 'PRD0000030', 'Ayam Geprek + Nasi', 'Kasir Demo', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `nama_produk` = VALUES(`nama_produk`);
