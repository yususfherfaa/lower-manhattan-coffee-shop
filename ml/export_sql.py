"""
export_sql.py
=============
Fase 4: Pembangkitan Berkas SQL Plan B secara Reproduktif & Otomatis.
Menghasilkan:
- database/planB/01_schema_planB.sql
- database/planB/02_seed_katalog.sql
- database/planB/03_seed_agregat.sql
- database/planB/00_README.md
- database/planB/99_rollback_planB.sql
"""

import os
import json
import pandas as pd
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.abspath(os.path.join(BASE_DIR, '..'))
DATASET_PATH = os.path.join(BASE_DIR, 'data', 'processed', 'dataset_bersih.csv')
PLANB_DIR = os.path.join(PROJECT_ROOT, 'database', 'planB')
KURS_PATH = os.path.join(BASE_DIR, 'config', 'kurs.json')
MAPPING_PATH = os.path.join(BASE_DIR, 'config', 'mapping_kategori.json')
IMAGE_DIR = os.path.join(PROJECT_ROOT, 'public', 'uploads')
IMAGE_BY_CATEGORY = {
    'Bakery': 'bakery.svg',
    'Branded': 'branded.svg',
    'Coffee': '1782892671_5d17140debb9adc1a0f7.jpg',
    'Coffee beans': '1782893018_3852702b5d883e8b42a6.jpg',
    'Drinking Chocolate': '1782892953_7355e2cffff88b69963b.png',
    'Flavours': 'flavours.svg',
    'Loose Tea': '1782892849_cca365c575d52226cdfc.jpeg',
    'Packaged Chocolate': 'packaged-chocolate.svg',
    'Tea': '1782892739_5710e7e00431f6e601b9.jpg',
}

os.makedirs(PLANB_DIR, exist_ok=True)

def generate_planB_sql():
    print("=" * 70)
    print("FASE 4: EKSPOR FILE SQL PLAN B SECARA REPRODUKTIF")
    print("=" * 70)
    
    with open(KURS_PATH, 'r', encoding='utf-8') as f:
        kurs_cfg = json.load(f)
    usd_to_idr = float(kurs_cfg.get('usd_to_idr', 16000))
    pembulatan = int(kurs_cfg.get('pembulatan', 500))
    
    with open(MAPPING_PATH, 'r', encoding='utf-8') as f:
        mapping_kat = json.load(f)
        
    df = pd.read_csv(DATASET_PATH)
    
    # -------------------------------------------------------------
    # 1. 01_schema_planB.sql
    # -------------------------------------------------------------
    sql_01 = """-- ====================================================================
-- 01_schema_planB.sql
-- Penyesuaian skema database untuk mendukung Plan B (Dataset Coffee Shop)
-- Kompatibel MariaDB 10.4.32 | Collation Konsisten: utf8mb4_general_ci
-- ====================================================================

-- 1. Tambah kolom penanda dataset pada tabel products (idempoten)
SET @col_exist_kategori := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'kategori_dataset');
SET @sql_kategori := IF(@col_exist_kategori = 0, 
    'ALTER TABLE `products` ADD COLUMN `kategori_dataset` VARCHAR(50) NULL AFTER `kategori`', 
    'SELECT \"Column kategori_dataset already exists\"');
PREPARE stmt FROM @sql_kategori; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_tipe := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'tipe_dataset');
SET @sql_tipe := IF(@col_exist_tipe = 0, 
    'ALTER TABLE `products` ADD COLUMN `tipe_dataset` VARCHAR(50) NULL AFTER `kategori_dataset`', 
    'SELECT \"Column tipe_dataset already exists\"');
PREPARE stmt FROM @sql_tipe; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_ukuran := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'ukuran');
SET @sql_ukuran := IF(@col_exist_ukuran = 0, 
    'ALTER TABLE `products` ADD COLUMN `ukuran` VARCHAR(20) NULL AFTER `tipe_dataset`', 
    'SELECT \"Column ukuran already exists\"');
PREPARE stmt FROM @sql_ukuran; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_kode := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'kode_produk_dataset');
SET @sql_kode := IF(@col_exist_kode = 0, 
    'ALTER TABLE `products` ADD COLUMN `kode_produk_dataset` INT(11) NULL AFTER `ukuran`', 
    'SELECT \"Column kode_produk_dataset already exists\"');
PREPARE stmt FROM @sql_kode; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_harga_ref := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'harga_dolar_ref');
SET @sql_harga_ref := IF(@col_exist_harga_ref = 0, 
    'ALTER TABLE `products` ADD COLUMN `harga_dolar_ref` DECIMAL(8,2) NULL AFTER `harga`', 
    'SELECT \"Column harga_dolar_ref already exists\"');
PREPARE stmt FROM @sql_harga_ref; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Tambah kolom metadata pada recommendation_logs (idempoten)
SET @col_exist_sumber := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'recommendation_logs' AND COLUMN_NAME = 'sumber_model');
SET @sql_sumber := IF(@col_exist_sumber = 0, 
    'ALTER TABLE `recommendation_logs` ADD COLUMN `sumber_model` VARCHAR(50) DEFAULT \"public_dataset\" AFTER `source`', 
    'SELECT \"Column sumber_model already exists\"');
PREPARE stmt FROM @sql_sumber; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_label_k := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'recommendation_logs' AND COLUMN_NAME = 'label_keramaian');
SET @sql_label_k := IF(@col_exist_label_k = 0, 
    'ALTER TABLE `recommendation_logs` ADD COLUMN `label_keramaian` VARCHAR(20) NULL AFTER `sumber_model`', 
    'SELECT \"Column label_keramaian already exists\"');
PREPARE stmt FROM @sql_label_k; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exist_versi := (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'recommendation_logs' AND COLUMN_NAME = 'versi_model');
SET @sql_versi := IF(@col_exist_versi = 0, 
    'ALTER TABLE `recommendation_logs` ADD COLUMN `versi_model` VARCHAR(30) DEFAULT \"2.0.0-planB\" AFTER `label_keramaian`', 
    'SELECT \"Column versi_model already exists\"');
PREPARE stmt FROM @sql_versi; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Tabel Agregat Keramaian per Jam (agg_keramaian_jam)
CREATE TABLE IF NOT EXISTS `agg_keramaian_jam` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `store_id` INT(11) NOT NULL DEFAULT 0,
    `hari_minggu` TINYINT(4) NOT NULL,
    `jam` TINYINT(4) NOT NULL,
    `avg_transaksi` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `total_transaksi` INT(11) NOT NULL DEFAULT 0,
    `total_qty` INT(11) NOT NULL DEFAULT 0,
    `label_keramaian` VARCHAR(20) NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_store_hari_jam` (`store_id`, `hari_minggu`, `jam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabel Agregat Kategori per Segmen (agg_kategori_segmen)
CREATE TABLE IF NOT EXISTS `agg_kategori_segmen` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `segmen` VARCHAR(20) NOT NULL,
    `product_category` VARCHAR(50) NOT NULL,
    `total_penjualan` INT(11) NOT NULL DEFAULT 0,
    `proporsi` DECIMAL(6,4) NOT NULL DEFAULT 0.0000,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_segmen_kategori` (`segmen`, `product_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
"""
    with open(os.path.join(PLANB_DIR, '01_schema_planB.sql'), 'w', encoding='utf-8') as f:
        f.write(sql_01)
        
    # -------------------------------------------------------------
    # 2. 02_seed_katalog.sql (80 Produk Dataset Maven)
    # -------------------------------------------------------------
    prods = df.groupby('product_id').first().reset_index()
    # Harga katalog adalah modus
    price_modes = df.groupby('product_id')['unit_price'].apply(lambda s: s.mode().iloc[0]).to_dict()
    
    sql_02_lines = [
        "-- ====================================================================",
        "-- 02_seed_katalog.sql",
        "-- Seed 80 Produk dari Dataset Coffee Shop Sales (Maven Roasters)",
        f"-- Asumsi Kurs: 1 USD = Rp{int(usd_to_idr):,} (Pembulatan Rp{pembulatan})",
        "-- ====================================================================\n",
        "INSERT INTO `products` (`id_produk`, `nama`, `kategori`, `kategori_dataset`, `tipe_dataset`, `ukuran`, `kode_produk_dataset`, `harga`, `harga_dolar_ref`, `stok`, `varian_suhu`, `gambar`, `aktif`) VALUES"
    ]
    
    val_rows = []
    for _, row in prods.iterrows():
        pid = int(row['product_id'])
        id_prd = f"PRDM{pid:04d}"
        nama = row['nama_bersih'].replace("'", "''")
        kat_dataset = row['product_category'].replace("'", "''")
        tipe_dataset = row['product_type'].replace("'", "''")
        ukuran = row['ukuran']
        
        kategori_produk = mapping_kat.get(row['product_category'], row['product_category'])
        harga_dolar = price_modes[pid]
        
        # Konversi Rupiah
        rupiah_raw = harga_dolar * usd_to_idr
        harga_rupiah = round(rupiah_raw / pembulatan) * pembulatan
        
        gambar = IMAGE_BY_CATEGORY.get(row['product_category'])
        if gambar is None:
            raise ValueError(f"Tidak ada ilustrasi kategori untuk {row['product_category']!r}.")
        if not os.path.isfile(os.path.join(IMAGE_DIR, gambar)):
            raise FileNotFoundError(f"Ilustrasi menu tidak ditemukan: {os.path.join(IMAGE_DIR, gambar)}")

        if ukuran != 'none':
            nama = f"{nama} ({ukuran})"
            
        val_rows.append(
            f"('{id_prd}', '{nama}', '{kategori_produk}', '{kat_dataset}', '{tipe_dataset}', '{ukuran}', {pid}, {harga_rupiah:.2f}, {harga_dolar:.2f}, 100, 0, '{gambar}', 1)"
        )
        
    sql_02_body = ",\n".join(val_rows)
    sql_02 = "\n".join(sql_02_lines) + "\n" + sql_02_body + """
ON DUPLICATE KEY UPDATE 
    `nama`=VALUES(`nama`),
    `kategori`=VALUES(`kategori`),
    `kategori_dataset`=VALUES(`kategori_dataset`),
    `tipe_dataset`=VALUES(`tipe_dataset`),
    `ukuran`=VALUES(`ukuran`),
    `harga`=VALUES(`harga`),
    `harga_dolar_ref`=VALUES(`harga_dolar_ref`),
    `gambar`=VALUES(`gambar`),
    `aktif`=1;
"""
    active_catalog_ids = ", ".join(
        f"'PRDM{int(product_id):04d}'" for product_id in sorted(df['product_id'].unique())
    )
    sql_02 += f"\nUPDATE `products` SET `aktif`=0 WHERE `id_produk` NOT IN ({active_catalog_ids});\n"
    with open(os.path.join(PLANB_DIR, '02_seed_katalog.sql'), 'w', encoding='utf-8') as f:
        f.write(sql_02)
        
    # -------------------------------------------------------------
    # 3. 03_seed_agregat.sql (Hasil Perhitungan Nyata Dataset)
    # -------------------------------------------------------------
    # Agregat Keramaian Jam: Global (store_id=0) dan Per Store
    # Hitung rata-rata per hari-jam
    daily_hourly = df.groupby(['store_id', 'parsed_date', 'hari_minggu', 'jam']).agg(
        tx_count=('transaction_id', 'count'),
        qty_count=('transaction_qty', 'sum')
    ).reset_index()
    
    agg_jam_store = daily_hourly.groupby(['store_id', 'hari_minggu', 'jam']).agg(
        avg_tx=('tx_count', 'mean'),
        tot_tx=('tx_count', 'sum'),
        tot_qty=('qty_count', 'sum')
    ).reset_index()
    
    # Global store_id=0
    daily_hourly_global = df.groupby(['parsed_date', 'hari_minggu', 'jam']).agg(
        tx_count=('transaction_id', 'count'),
        qty_count=('transaction_qty', 'sum')
    ).reset_index()
    
    agg_jam_global = daily_hourly_global.groupby(['hari_minggu', 'jam']).agg(
        avg_tx=('tx_count', 'mean'),
        tot_tx=('tx_count', 'sum'),
        tot_qty=('qty_count', 'sum')
    ).reset_index()
    agg_jam_global['store_id'] = 0
    
    all_agg_jam = pd.concat([agg_jam_global, agg_jam_store], ignore_index=True)
    
    # Label keramaian berdasarkan rata-rata
    def label_c(avg):
        if avg <= 15:
            return 'sepi'
        elif avg <= 35:
            return 'sedang'
        else:
            return 'ramai'
            
    all_agg_jam['label_keramaian'] = all_agg_jam['avg_tx'].apply(label_c)
    
    sql_03_lines = [
        "-- ====================================================================",
        "-- 03_seed_agregat.sql",
        "-- Data Agregat Dataset Lower Manhattan untuk Analitik dan Rekomendasi per Waktu",
        f"-- Dihitung dari {len(df):,} transaksi Lower Manhattan (store_id={int(df['store_id'].iloc[0])})",
        "DELETE FROM `agg_keramaian_jam`;",
        "DELETE FROM `agg_kategori_segmen`;",
        "-- ====================================================================\n",
        "INSERT INTO `agg_keramaian_jam` (`store_id`, `hari_minggu`, `jam`, `avg_transaksi`, `total_transaksi`, `total_qty`, `label_keramaian`) VALUES"
    ]
    
    agg_jam_vals = []
    for _, r in all_agg_jam.iterrows():
        agg_jam_vals.append(
            f"({int(r['store_id'])}, {int(r['hari_minggu'])}, {int(r['jam'])}, {r['avg_tx']:.2f}, {int(r['tot_tx'])}, {int(r['tot_qty'])}, '{r['label_keramaian']}')"
        )
    sql_03_lines.append(",\n".join(agg_jam_vals))
    sql_03_lines.append("""ON DUPLICATE KEY UPDATE 
    `avg_transaksi`=VALUES(`avg_transaksi`),
    `total_transaksi`=VALUES(`total_transaksi`),
    `total_qty`=VALUES(`total_qty`),
    `label_keramaian`=VALUES(`label_keramaian`);\n""")
    
    # Agregat Kategori Segmen
    seg_cat = df.groupby(['segmen_waktu', 'product_category']).size().reset_index(name='total_penjualan')
    seg_totals = df.groupby('segmen_waktu').size().to_dict()
    seg_cat['proporsi'] = seg_cat.apply(lambda r: r['total_penjualan'] / seg_totals[r['segmen_waktu']], axis=1)
    
    sql_03_lines.append("INSERT INTO `agg_kategori_segmen` (`segmen`, `product_category`, `total_penjualan`, `proporsi`) VALUES")
    seg_vals = []
    for _, r in seg_cat.iterrows():
        cat_escaped = str(r['product_category']).replace("'", "''")
        seg_vals.append(
            f"('{r['segmen_waktu']}', '{cat_escaped}', {int(r['total_penjualan'])}, {r['proporsi']:.4f})"
        )
    sql_03_lines.append(",\n".join(seg_vals))
    sql_03_lines.append("""ON DUPLICATE KEY UPDATE 
    `total_penjualan`=VALUES(`total_penjualan`),
    `proporsi`=VALUES(`proporsi`);\n""")
    
    sql_03 = "\n".join(sql_03_lines)
    with open(os.path.join(PLANB_DIR, '03_seed_agregat.sql'), 'w', encoding='utf-8') as f:
        f.write(sql_03)
        
    # -------------------------------------------------------------
    # 4. 99_rollback_planB.sql
    # -------------------------------------------------------------
    sql_99 = """-- ====================================================================
-- 99_rollback_planB.sql
-- Pembatalan Seluruh Perubahan Struktur dan Data Plan B
-- ====================================================================

DROP TABLE IF EXISTS `agg_kategori_segmen`;
DROP TABLE IF EXISTS `agg_keramaian_jam`;

DELETE FROM `products` WHERE `id_produk` LIKE 'PRDM%';

ALTER TABLE `products` 
    DROP COLUMN IF EXISTS `kategori_dataset`,
    DROP COLUMN IF EXISTS `tipe_dataset`,
    DROP COLUMN IF EXISTS `ukuran`,
    DROP COLUMN IF EXISTS `kode_produk_dataset`,
    DROP COLUMN IF EXISTS `harga_dolar_ref`;

ALTER TABLE `recommendation_logs` 
    DROP COLUMN IF EXISTS `sumber_model`,
    DROP COLUMN IF EXISTS `label_keramaian`,
    DROP COLUMN IF EXISTS `versi_model`;
"""
    with open(os.path.join(PLANB_DIR, '99_rollback_planB.sql'), 'w', encoding='utf-8') as f:
        f.write(sql_99)
        
    # -------------------------------------------------------------
    # 5. 00_README.md
    # -------------------------------------------------------------
    readme_content = """# PANDUAN PENGGUNAAN FILE SQL PLAN B

Folder ini berisi file SQL untuk menyelaraskan katalog, agregat, dan model Lower Manhattan Coffee Shop dengan dataset Coffee Shop Sales (Maven Roasters).

## Urutan Impor Database (phpMyAdmin / MySQL CLI):
1. `01_schema_planB.sql` — Menambah kolom penanda dataset pada `products` dan `recommendation_logs`, serta membuat tabel agregat `agg_keramaian_jam` dan `agg_kategori_segmen`.
2. `02_seed_katalog.sql` — Mengganti katalog aktif dengan seluruh produk dari subset Lower Manhattan, ID berawalan `PRDM`, kategori sesuai dataset, dan harga Rupiah yang telah dikonversi.
3. `03_seed_agregat.sql` — Memasukkan ringkasan analitik jam dan kategori yang dihitung hanya dari transaksi Lower Manhattan.

## Rollback:
Jika ingin membatalkan perubahan Plan B, jalankan:
- `99_rollback_planB.sql`

> **PERINGATAN KERAS**: Uji selalu file ini pada database pengujian (`warkop_bahagia_test`) sebelum diterapkan pada basis data utama.
"""
    with open(os.path.join(PLANB_DIR, '00_README.md'), 'w', encoding='utf-8') as f:
        f.write(readme_content)
        
    print("Semua file SQL Plan B berhasil dibangkitkan di database/planB/.")

if __name__ == '__main__':
    generate_planB_sql()
