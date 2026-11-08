-- =====================================================================
-- FIX QRIS PENGATURAN (IDEMPOTEN) - LOWER MANHATTAN COFFEE SHOP
-- =====================================================================
-- Petunjuk Penggunaan:
-- 1. Pastikan Anda telah membackup database terlebih dahulu:
--    mysqldump -u root lowermanhattancoffeshop settings > backup_settings.sql
-- 2. Jalankan skrip ini di database lowermanhattancoffeshop:
--    mysql -u root lowermanhattancoffeshop < database/fix_qris_pengaturan.sql
-- =====================================================================

-- 1. Tambah kunci nama_toko jika belum ada (salin dari nama_warkop bila ada)
INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT 
    CONCAT('SET', LPAD(COALESCE(MAX(CAST(SUBSTRING(id_setting, 4) AS UNSIGNED)), 0) + 1, 3, '0')),
    'nama_toko',
    COALESCE((SELECT s2.value FROM `settings` s2 WHERE s2.nama = 'nama_warkop' LIMIT 1), 'Lower Manhattan Coffee Shop')
FROM `settings`
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'nama_toko');

-- 2. Tambah kunci alamat_lengkap jika belum ada (salin dari alamat lama bila ada)
INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT 
    CONCAT('SET', LPAD(COALESCE(MAX(CAST(SUBSTRING(id_setting, 4) AS UNSIGNED)), 0) + 1, 3, '0')),
    'alamat_lengkap',
    COALESCE((SELECT s2.value FROM `settings` s2 WHERE s2.nama = 'alamat' LIMIT 1), '')
FROM `settings`
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'alamat_lengkap');

-- 3. Tambah kunci nmid jika belum ada (default kosong)
INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT 
    CONCAT('SET', LPAD(COALESCE(MAX(CAST(SUBSTRING(id_setting, 4) AS UNSIGNED)), 0) + 1, 3, '0')),
    'nmid',
    ''
FROM `settings`
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'nmid');

-- 4. Tambah kunci qris_mode jika belum ada (default 'demo' demi kepatuhan keamanan)
INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT 
    CONCAT('SET', LPAD(COALESCE(MAX(CAST(SUBSTRING(id_setting, 4) AS UNSIGNED)), 0) + 1, 3, '0')),
    'qris_mode',
    'demo'
FROM `settings`
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'qris_mode');

