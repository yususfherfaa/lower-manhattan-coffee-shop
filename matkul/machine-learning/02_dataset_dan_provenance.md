# DATASET DAN PROVENANCE

**Mata Kuliah**: Machine Learning  
**Dataset**: Coffee Shop Sales (Maven Roasters)  
**Status Data**: Dataset Publik SIMULASI (Mockup / Edukasi)  

---

## 1. Identitas & Provenance Dataset

Dataset yang digunakan dalam penelitian dan pengembangan model ini berasal dari dataset publik terbuka yang diterbitkan oleh Maven Analytics:
- **Nama Dataset**: Coffee Shop Sales
- **Penerbit**: Maven Analytics Data Playground
- **Tautan Sumber**: [https://mavenanalytics.io/data-playground](https://mavenanalytics.io/data-playground)
- **Sifat Data**: **Data Publik SIMULASI (Synthetic/Educational Mockup)**. Data ini bukan merupakan data operasional riil dari toko fisik di New York maupun transaksi kedai kopi di Indonesia, melainkan dataset benchmark yang dirancang untuk keperluan analisis bisnis dan pemodelan data.
- **Periode Waktu**: 1 Januari 2023 s.d. 30 Juni 2023 (6 bulan / 181 hari berurutan).
- **Format Berkas Asli**: CSV (`dataset/Coffee Shop Sales.csv`, ukuran 8,55 MB / 8.970.256 bytes).

---

## 2. Struktur Kolom (11 Kolom Baku)

| No | Nama Kolom | Tipe Data | Deskripsi | Contoh Nilai |
|---|---|---|---|---|
| 1 | `transaction_id` | Integer | ID unik rekaman transaksi | 1, 42, 149451 |
| 2 | `transaction_date` | Date (YYYY-MM-DD) | Tanggal transaksi | 2023-01-01, 2023-06-30 |
| 3 | `transaction_time` | Time (HH:MM:SS) | Waktu tepat transaksi | 07:06:11, 20:54:10 |
| 4 | `transaction_qty` | Integer | Kuantitas item yang dipesan | 1, 2, 3, 6 |
| 5 | `store_id` | Integer | ID lokasi cabang toko | 3, 5, 8 |
| 6 | `store_location` | String | Nama area lokasi cabang | Lower Manhattan, Astoria, Hell's Kitchen |
| 7 | `product_id` | Integer | ID produk katalog (1 s.d. 87) | 1, 32, 59 |
| 8 | `unit_price` | Float | Harga per unit dalam USD ($) | 2.50, 3.00, 45.00 |
| 9 | `product_category` | String | Kategori produk utama (9 kelas) | Coffee, Tea, Bakery, Flavours |
| 10 | `product_type` | String | Sub-kategori / tipe olahan | Gourmet brewed coffee, Scone |
| 11 | `product_detail` | String | Varian rinci atau ukuran | Ethiopian Rg, Earl Grey Lg |

---

## 3. Filtrasi Ruang Lingkup: Cabang Lower Manhattan

Pada dataset mentah Maven Roasters, terdapat **149.116 baris transaksi** yang tersebar di 3 lokasi cabang di Kota New York. Sesuai fokus penelitian, dilakukan pemfilteran ketat khusus untuk **Cabang Lower Manhattan (`store_id = 5`)**:

| Metrik Pemfilteran | Jumlah Baris | Persentase | Status |
|---|---|---|---|
| **Total Baris Mentah (3 Cabang)** | 149.116 | 100,00% | Sumber: `dataset/Coffee Shop Sales.csv` |
| **Cabang Lain Dikecualikan** (Astoria & Hell's Kitchen) | 101.334 | 67,96% | Dieliminasi dari ruang lingkup |
| **Cabang Lower Manhattan (`store_id = 5`)** | 47.782 | 32,04% | Basis analisis: `ml/data/processed/dataset_bersih.csv` |
| **Baris Rusak / Invalid Dibuang** | 0 | 0,00% | Lulus 100% Data Quality Gate |

### Alasan Pemilihan Cabang Tunggal:
1. Menghindari variasi bias demografis antar wilayah yang berbeda karakter konsumsi.
2. Cabang Lower Manhattan memiliki profil pelanggan kawasan perkantoran dan bisnis dengan volume transaksi terbesar (47.782 baris) yang representatif untuk pengujian berbasis waktu harian.
3. Menghilangkan fitur konstan `store_id` dan `store_location` agar model pembelajaran mesin tidak mengalami kebocoran target (*target leakage*) atau bias lokasi statis.

---

## 4. Integritas File & Hash Kriptografis (SHA-256)

Untuk menjamin auditabilitas akademik dan reproduksibilitas ilmiah tanpa duplikasi berkas besar yang tidak perlu, kedua berkas dataset diverifikasi menggunakan algoritma *hash* SHA-256:

- **Berkas Mentah Asli**:
  - Lokasi: `dataset/Coffee Shop Sales.csv`
  - Ukuran: 8.970.256 bytes (8,55 MB)
  - Hash SHA-256: `287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1`
- **Berkas Bersih Hasil Filter Lower Manhattan**:
  - Lokasi: `ml/data/processed/dataset_bersih.csv`
  - Ukuran: 9.141.026 bytes (8,72 MB)
  - Hash SHA-256: `fade0fa022a56467b1071429f265e3252d7069fbc02e29d24381578d6625f7bd`

---

## 5. Keterbatasan Intrinsik Dataset

1. **Ketiadaan ID Pelanggan (*Customer ID*) & ID Keranjang (*Cart/Basket ID*)**:
   - Setiap baris transaksi hanya mencatat waktu, kuantitas, dan jenis produk. Tidak terdapat informasi identitas pelanggan unik (*User ID*), demografi, riwayat transaksi individu, maupun pengelompokan produk dalam satu struk belanja (*market basket analysis*). Oleh karena itu, sistem rekomendasi **tidak dapat melakukan personalisasi perorangan** (*collaborative filtering* berbasis pengguna).
2. **Karakteristik Wilayah Geografis**:
   - Pola konsumsi merefleksikan kebiasaan konsumen urban Amerika Serikat (katalog berbasis USD, dominasi varian *brewed coffee* dan *tea*). Hal ini berbeda dengan dinamika kedai kopi lokal di Indonesia.
3. **Sifat Invarian Waktu Komposisi Menu**:
   - Proporsi pemesanan kategori produk relatif seragam sepanjang hari (kopi selalu mendominasi 38% baik pagi, siang, sore, maupun malam), sebagaimana dibuktikan pada tahap pengujian statistik kualitas data.
