-- PR-09: bersihkan tabel stock_logs dan sederhanakan status pesanan.
-- Jalankan hanya secara manual setelah membuat backup database.
-- Periksa hasil SELECT sebelum melanjutkan pada database produksi.

-- Pastikan pesanan lama berstatus sedang_dibuat dipindahkan ke status pembayaran.
SELECT id_order, status
FROM orders
WHERE status = 'sedang_dibuat';

-- Pindahkan data lama sebelum menghapus nilai tersebut dari ENUM.
UPDATE orders
SET status = 'menunggu_pembayaran'
WHERE status = 'sedang_dibuat';

ALTER TABLE orders
    MODIFY status ENUM('menunggu_pembayaran', 'lunas')
    DEFAULT 'menunggu_pembayaran';

-- Tidak ada pemanggil StockLogModel di aplikasi.
DROP TABLE IF EXISTS stock_logs;
