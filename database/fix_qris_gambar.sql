-- README: Backup database utama terlebih dahulu. Jalankan skrip ini hanya pada database aplikasi yang benar setelah menyalin aset demo ke public/uploads/. Skrip ini idempoten: mengatur gambar demo dan qris_mode=demo, tanpa mengubah alur pembayaran; untuk pembayaran sungguhan, minta QR resmi dari bank/PJSP lalu unggah apa adanya dan pilih mode live di pengaturan.
START TRANSACTION;

UPDATE `settings`
SET `value` = 'uploads/qris-demo-lowermanhattan.png'
WHERE `nama` = 'qris';

INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT CONCAT('SET', LPAD(next_id, 3, '0')), 'qris',
       'uploads/qris-demo-lowermanhattan.png'
FROM (
    SELECT COALESCE(MAX(CAST(SUBSTRING(`id_setting`, 4) AS UNSIGNED)), 0) + 1 AS next_id
    FROM `settings`
) AS ids
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'qris');

UPDATE `settings`
SET `value` = 'demo'
WHERE `nama` = 'qris_mode';

INSERT INTO `settings` (`id_setting`, `nama`, `value`)
SELECT CONCAT('SET', LPAD(next_id, 3, '0')), 'qris_mode', 'demo'
FROM (
    SELECT COALESCE(MAX(CAST(SUBSTRING(`id_setting`, 4) AS UNSIGNED)), 0) + 1 AS next_id
    FROM `settings`
) AS ids
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `nama` = 'qris_mode');

COMMIT;
