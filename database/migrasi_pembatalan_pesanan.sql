-- PR-13: tambahkan status pembatalan untuk pesanan yang belum dibayar.
-- Cadangkan database sebelum menjalankan migrasi ini:
-- mysqldump -u USER_DATABASE -p --single-transaction warkop_bahagia > warkop_bahagia-sebelum-pr13.sql
-- Jalankan berkas ini secara manual setelah meninjau target database.

ALTER TABLE orders
    MODIFY status ENUM('menunggu_pembayaran', 'lunas', 'dibatalkan')
    DEFAULT 'menunggu_pembayaran';
