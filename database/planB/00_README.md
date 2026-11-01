# PANDUAN PENGGUNAAN FILE SQL PLAN B

Folder ini berisi file SQL untuk menyelaraskan katalog, agregat, dan model Lower Manhattan Coffee Shop dengan dataset Coffee Shop Sales (Maven Roasters).

## Urutan Impor Database (phpMyAdmin / MySQL CLI):
1. `01_schema_planB.sql` — Menambah kolom penanda dataset pada `products` dan `recommendation_logs`, serta membuat tabel agregat `agg_keramaian_jam` dan `agg_kategori_segmen`.
2. `02_seed_katalog.sql` — Mengganti katalog aktif dengan seluruh produk dari subset Lower Manhattan, ID berawalan `PRDM`, kategori sesuai dataset, dan harga Rupiah yang telah dikonversi.
3. `03_seed_agregat.sql` — Memasukkan ringkasan analitik jam dan kategori yang dihitung hanya dari transaksi Lower Manhattan.

Setelah impor SQL manual, jalankan `php spark db:seed KatalogMavenSeeder` agar semua produk mendapat deskripsi yang sesuai dengan nama dan kategorinya. Proses ini hanya mengisi deskripsi yang masih kosong; deskripsi yang sudah disunting melalui Manajemen Menu kasir tetap dipertahankan. Untuk sinkronisasi dari aplikasi, jalankan `php spark migrate` lalu seeder tersebut.

## Rollback:
Jika ingin membatalkan perubahan Plan B, jalankan:
- `99_rollback_planB.sql`

> **PERINGATAN KERAS**: Uji selalu file ini pada database pengujian (`warkop_bahagia_test`) sebelum diterapkan pada basis data utama.
