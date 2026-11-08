# LAPORAN LENGKAP PRAKTIKUM MACHINE LEARNING
## SISTEM REKOMENDASI MENU COFFEE SHOP BERBASIS WAKTU MENGGUNAKAN K-NEAREST NEIGHBOR (STUDI KASUS: CABANG LOWER MANHATTAN)

**Disusun Oleh**:
- **Yusuf Herfa Auliansyah** (NIM: 15240075)
- **Yusuf Mansur** (NIM: 15240047)
- **Rizqi Nuzul Firman** (NIM: 15240116)

**Kelas**: 15.5A.05  
**Program Studi**: Informatika / Ilmu Komputer  
**Perguruan Tinggi**: Universitas Bina Sarana Informatika (UBSI) Kampus Kaliabang  
**Dosen Pengampu**: [ISI NAMA DOSEN PENGAMPU]  
**Tanggal Pengesahan**: 2026-11-02  

---

## DAFTAR ISI
1. [BAB I: Definisi Masalah](#bab-i-definisi-masalah)
2. [BAB II: Dataset dan Provenance](#bab-ii-dataset-dan-provenance)
3. [BAB III: Kualitas Data dan Exploratory Data Analysis (EDA)](#bab-iii-kualitas-data-dan-exploratory-data-analysis-eda)
4. [BAB IV: Praproses dan Rekayasa Fitur](#bab-iv-praproses-dan-rekayasa-fitur)
5. [BAB V: Pemodelan K-Nearest Neighbors (KNN)](#bab-v-pemodelan-k-nearest-neighbors-knn)
6. [BAB VI: Evaluasi Model dan Analisis Baseline](#bab-vi-evaluasi-model-dan-analisis-baseline)
7. [BAB VII: Deployment Microservice Flask dan Integrasi Sistem](#bab-vii-deployment-microservice-flask-dan-integrasi-sistem)
8. [BAB VIII: Kesimpulan dan Keterbatasan Penelitian](#bab-viii-kesimpulan-dan-keterbatasan-penelitian)
9. [DAFTAR PUSTAKA](#daftar-pustaka)

---


---

## BAB I: 01 DEFINISI MASALAH

# DEFINISI MASALAH (PROBLEM DEFINITION)

**Mata Kuliah**: Machine Learning  
**Topik**: Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu Menggunakan K-Nearest Neighbor  
**Ruang Lingkup**: Lower Manhattan (`store_id = 5`), Dataset Publik Maven Roasters  

---

## 1. Latar Belakang & Urgensi

Pengelolaan gerai kopi modern menghadapi tantangan dinamis dalam menyajikan rekomendasi produk yang relevan secara waktu serta memprediksi lonjakan antrean pengunjung (*crowd surge*). Pada operasional kedai kopi, pola konsumsi pagi hari cenderung didominasi oleh minuman berkafein tinggi dan sarapan cepat saji, sementara siang dan malam hari menunjukkan pergeseran ke arah minuman santai atau makanan penutup.

Untuk menjawab kebutuhan efisiensi dan personalisasi operasional ini, dikembangkan sistem cerdas berbasis pembelajaran mesin (*Machine Learning*) dengan dua tugas utama:
1. Rekomendasi menu teratas berdasarkan waktu transaksi (*Product Category Classification*).
2. Prediksi tingkat kepadatan pesanan per jam (*Hourly Transaction Volume Regression*).

---

## 2. Formulasi Tugas Machine Learning

### 2.1. Tugas 1: Rekomendasi Menu (Klasifikasi Multikelas Kategori Produk)
- **Tujuan**: Menentukan kategori produk yang paling mungkin dipesan oleh pelanggan berdasarkan fitur temporal saat pemesanan berlangsung.
- **Masukan (Input Features)**:
  - `jam`: Jam transaksi (integer 6 s.d. 20).
  - `hari_minggu`: Hari dalam pekan (0 = Senin, ..., 6 = Minggu).
  - `is_weekend`: Indikator biner akhir pekan (1 = Sabtu/Minggu, 0 = Hari kerja).
  - `jam_sin`: Transformasi siklikal sinus jam ($\sin(2\pi \cdot \text{jam} / 24)$).
  - `jam_cos`: Transformasi siklikal kosinus jam ($\cos(2\pi \cdot \text{jam} / 24)$).
- **Keluaran (Target Output)**:
  - Kategori produk dari 9 kelas: `Bakery`, `Branded`, `Coffee`, `Coffee beans`, `Drinking Chocolate`, `Flavours`, `Loose Tea`, `Packaged Chocolate`, `Tea`.
- **Metrik Evaluasi**:
  - Akurasi (*Top-1 Accuracy*), Macro Precision, Macro Recall, Macro F1-Score, Weighted F1-Score, dan Top-3 Hit Rate.
- **Kriteria Keberhasilan & Baseline**:
  - Diuji terhadap *Baseline Mayoritas* (selalu memprediksi kelas paling sering muncul, yaitu 'Coffee' sebesar 38,10%).
  - Diuji terhadap *Baseline Top-3 Populer* (selalu menyajikan 3 kategori teratas: Coffee, Tea, Bakery sebesar 83,73%).

### 2.2. Tugas 2: Prediksi Keramaian (Regresi Volume Transaksi per Jam)
- **Tujuan**: Memprediksi jumlah total transaksi yang terjadi dalam rentang satu jam tertentu untuk memberikan estimasi status kepadatan kedai.
- **Masukan (Input Features)**:
  - `jam`, `hari_minggu`, `is_weekend`, `bulan` (1 s.d. 6), `jam_sin`, `jam_cos`.
- **Keluaran (Target Output)**:
  - `total_transaksi_per_jam`: Nilai riil kontinu estimasi jumlah transaksi.
- **Label Kategori Keramaian (Berdasarkan Kuantil Historis)**:
  - **Sepi**: $\le 11,0$ transaksi/jam.
  - **Sedang**: $> 11,0$ s.d. $\le 28,0$ transaksi/jam.
  - **Ramai**: $> 28,0$ transaksi/jam.
- **Metrik Evaluasi**:
  - Mean Absolute Error (MAE), Root Mean Squared Error (RMSE), dan Koefisien Determinasi ($R^2$).
- **Kriteria Keberhasilan & Baseline**:
  - Mengungguli *Baseline Rata-rata per Jam* (MAE 7,42 transaksi/jam, RMSE 10,09 transaksi/jam, $R^2 = 0,4173$).

---

## 3. Matriks Perbandingan Formulasi

| Parameter | Tugas 1 (Rekomendasi Menu) | Tugas 2 (Prediksi Keramaian) |
|---|---|---|
| **Paradigma** | Supervised Learning — Multiclass Classification | Supervised Learning — Regression |
| **Model** | K-Nearest Neighbors Classifier ($K=20$) | K-Nearest Neighbors Regressor ($K=17$) |
| **Metrik Jarak** | Euclidean Distance | Euclidean Distance |
| **Penskalaan** | StandardScaler | StandardScaler |
| **Skema Split** | 80:20 Stratified Random Split & Temporal Split | K-Fold / Time Split Agregasi Jam |
| **Sumber Fakta** | `ml/hasil_evaluasi.json`, baris 25–45 | `ml/hasil_evaluasi.json`, baris 46–67 |


---

## BAB II: 02 DATASET DAN PROVENANCE

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


---

## BAB III: 03 KUALITAS DATA DAN EDA

# KUALITAS DATA DAN EXPLORATORY DATA ANALYSIS (EDA)

**Mata Kuliah**: Machine Learning  
**Dataset**: Lower Manhattan Coffee Shop (47.782 baris transaksi)  
**Rujukan Bukti**: `docs/laporan_kualitas_data.md`, `matkul/machine-learning/gambar/eda_distribusi.png`  

---

## 1. Ringkasan Pengujian Kualitas Data (15 Data Quality Gates)

Seluruh data transaksi Lower Manhattan telah melalui 15 tahap audit kualitas data otomatis sebelum memasuki proses pelatihan:

| No | Pengujian | Hasil Pengujian | Status |
|---|---|---|---|
| 1 | Integritas File | Ukuran 8,55 MB, SHA-256 terverifikasi | **LULUS** |
| 2 | Kesesuaian Skema | 11 kolom baku terpetakan sempurna | **LULUS** |
| 3 | Pemeriksaan Nilai Hilang | 0 baris bernilai null, NaN, atau whitespace | **LULUS** |
| 4 | Pemeriksaan Duplikasi | 0 duplikasi ID transaksi; 112 transaksi sah bersamaan | **LULUS** |
| 5 | Validitas Tanggal & Jam | 181 hari berurutan, jam operasional 06:00 s.d. 20:59 | **LULUS** |
| 6 | Rentang Nilai & Outlier | Qty 1–6 (26 outlier), Harga $0,80–$45,00 (1.456 outlier) | **LULUS** |
| 7 | Konsistensi Relasional | Pemetaan 1-ke-1 ID produk terhadap kategori dan tipe | **LULUS** |
| 8 | Konsistensi Harga | 12 produk mengalami fluktuasi harga historis (modus dipakai) | **LULUS** |
| 9 | Urutan ID Transaksi | Rentang ID 1–149.451 tanpa duplikasi internal | **LULUS** |
| 10 | Distribusi Jam Buka | Jam terpadat pukul 08:00–10:00; jam terendah pukul 20:00 (75 baris) | **LULUS** |
| 11 | Keseimbangan Kelas | 9 kelas; kelas minoritas (Packaged Chocolate: 180 baris) | **LULUS** |
| 12 | Tren Volume Bulanan | Transaksi meningkat konsisten: Jan (5.533) s.d. Jun (11.296) | **LULUS** |
| 13 | Uji Ketergantungan Waktu | Chi-Square Cramér's V = 0,0335; Kruskal-Wallis p < 1e-10 | **LULUS** |
| 14 | Pencegahan Kebocoran Target | Seluruh fitur turunan produk dieliminasi dari input model | **LULUS** |
| 15 | Rekonsiliasi Baris | Tepat 47.782 baris Lower Manhattan diproses tanpa baris cacat | **LULUS** |

---

## 2. Distribusi Kelas Kategori Produk (Ketidakseimbangan Kelas)

Berdasarkan analisis frekuensi pada 47.782 transaksi di cabang Lower Manhattan, proporsi pemesanan terdistribusi sebagai berikut:

| Kategori Produk | Frekuensi Transaksi | Persentase | Karakteristik Distribusi |
|---|---|---|---|
| **Coffee** | 18.204 | 38,10% | Kelas Mayoritas (Dominan) |
| **Tea** | 13.913 | 29,12% | Kelas Terbanyak Kedua |
| **Bakery** | 7.890 | 16,51% | Makanan Pendamping Utama |
| **Drinking Chocolate** | 3.405 | 7,13% | Minuman Non-Kopi |
| **Flavours** | 2.930 | 6,13% | Sirup Tambahan (*Add-on*) |
| **Coffee beans** | 531 | 1,11% | Produk Kemasan Biji Kopi |
| **Loose Tea** | 381 | 0,80% | Daun Teh Kemasan |
| **Branded** | 349 | 0,73% | Merchandise & Aksesoris |
| **Packaged Chocolate** | 180 | 0,38% | Cokelat Batang Kemasan (Minoritas) |
| **Total** | **47.782** | **100,00%** | Sumber: `ml/hasil_evaluasi.json` & dataset bersih |

> **Temuan Kritis**: Tiga kategori teratas (**Coffee**, **Tea**, dan **Bakery**) secara kumulatif mencakup **83,73%** dari total seluruh volume transaksi di cabang Lower Manhattan.

---

## 3. Analisis Pola Temporal & Uji Ketergantungan Statistik

### 3.1. Uji Independensi Chi-Square & Cramér's V (Kategori vs Waktu)
Untuk menguji apakah preferensi kategori menu pelanggan berubah secara signifikan berdasarkan waktu dalam hari, dilakukan uji Chi-Square terhadap kontingensi *Kategori Produk $\times$ Segmen Waktu*:
- Statistik Chi-Square ($\chi^2$): **161,20**
- Derajat Bebas (*degrees of freedom*): 24
- P-value: **$2,63 \times 10^{-22}$**
- Koefisien Asosiasi **Cramér's V**: **0,0335**

**Interpretasi Ilmiah**:
Meskipun nilai *p-value* sangat kecil (disebabkan oleh ukuran sampel yang besar, $N = 47.782$), nilai **Cramér's V sebesar 0,0335** menunjukkan bahwa kekuatan asosiasi hubungan antara waktu hari dengan jenis kategori menu yang dipesan adalah **sangat lemah (*negligible association*) mendekati nol**. Komposisi menu relatif invarian terhadap waktu; pelanggan memesan kopi dan teh dengan proporsi hampir identik baik di pagi, siang, sore, maupun malam hari.

### 3.2. Uji Kruskal-Wallis (Volume Transaksi vs Jam)
Sebaliknya, pengujian non-parametrik Kruskal-Wallis untuk menguji perbedaan volume kepadatan transaksi pada setiap jam operasional menunjukkan:
- Statistik Kruskal-Wallis $H$: **1016,86**
- P-value: **$3,78 \times 10^{-208}$** ($p < 10^{-10}$)

**Interpretasi Ilmiah**:
Terdapat perbedaan volume kepadatan yang **sangat signifikan secara statistik** antar jam operasional. Jam 08:00–10:00 pagi mengalami lonjakan drastis, sedangkan jam 19:00–20:00 malam mengalami penurunan tajam. 

Kesimpulan dari EDA ini meletakkan dasar metodologis pemisahan dua tugas: **Tugas 1 (rekomendasi kategori) memiliki sinyal prediktif temporal yang sangat tipis, sedangkan Tugas 2 (prediksi keramaian volume) memiliki sinyal temporal yang sangat kuat dan terbukti nyata**.

---

## 4. Visualisasi Distribusi EDA

Visualisasi distribusi transaksi per jam, per hari, dan per kategori disimpan dalam berkas:
- Gambar: `matkul/machine-learning/gambar/eda_distribusi.png`
- Visualisasi ini memperlihatkan kurva lonjakan jam sibuk pagi hari (*morning coffee rush*) serta dominasi proporsional kategori Coffee dan Tea di setiap jam operasional.


---

## BAB IV: 04 PRAPROSES DAN FITUR

# PRAPROSES DAN REKAYASA FITUR (PREPROCESSING & FEATURE ENGINEERING)

**Mata Kuliah**: Machine Learning  
**Dataset**: Lower Manhattan Coffee Shop (47.782 baris transaksi)  
**Tujuan**: Menyiapkan matriks fitur temporal dan memastikan integritas metodologi anti-kebocoran data (*anti-leakage*).

---

## 1. Definisi Segmen Waktu Operasional

Meskipun model KNN beroperasi pada fitur numerik kontinu dan siklikal jam, aplikasi bisnis mengelompokkan operasional coffee shop ke dalam 4 segmen waktu:

| Segmen Waktu | Rentang Jam Operasional | Karakteristik Perilaku Konsumen |
|---|---|---|
| **Pagi** | 06:00 – 10:59 | Jam sarapan, lonjakan pesanan kopi hangat dan sarapan cepat (*bakery*). |
| **Siang** | 11:00 – 14:59 | Jam makan siang, transisi pesanan kopi dingin, teh, dan minuman segar. |
| **Sore** | 15:00 – 17:59 | Jam rehat kerja sore (*coffee break*), teh dan kudapan ringan. |
| **Malam** | 18:00 – 20:59 | Jam santai menjelang penutupan toko (toko tutup pukul 21:00). |

Segmen ini digunakan sebagai basis pelabelan antarmuka pengguna serta sebagai acuan penghitungan **Baseline 2 (Mode per Segmen)**.

---

## 2. Rekayasa Fitur Temporal (Feature Engineering)

Untuk menangkap dinamika waktu tanpa memperkenalkan ketergantungan linier yang keliru, fitur waktu ditransformasikan ke dalam representasi siklikal:

1. **`jam` (Integer)**: Diekstrak dari `transaction_time` (nilai 6 s.d. 20).
2. **`hari_minggu` (Integer)**: Diekstrak dari `transaction_date` (0 = Senin s.d. 6 = Minggu).
3. **`is_weekend` (Biner)**: 1 jika `hari_minggu` $\ge 5$ (Sabtu atau Minggu), 0 jika hari kerja (Senin–Jumat).
4. **`jam_sin` & `jam_cos` (Siklikal)**:
   $$\text{jam\_sin} = \sin\left(\frac{2 \pi \cdot \text{jam}}{24}\right), \quad \text{jam\_cos} = \cos\left(\frac{2 \pi \cdot \text{jam}}{24}\right)$$
   *Tujuan*: Menjamin bahwa jarak antara jam 23:00 dan 00:00 atau jam 20:00 dan 06:00 dapat dipahami oleh model metrik jarak Euclidean sebagai waktu yang berdekatan secara melingkar.
5. **`bulan` (Integer 1 s.d. 6)**: Khusus digunakan pada Tugas 2 (Prediksi Keramaian) untuk menangkap tren kenaikan volume musiman dari Januari hingga Juni.

---

## 3. Pencegahan Kebocoran Target (Anti-Data Leakage)

Pencegahan kebocoran target merupakan keharusan mutlak dalam integritas machine learning:

1. **Fitur Terlarang untuk Input Model**:
   - `transaction_id`: Identifier arbitrer acak.
   - `product_id`, `product_type`, `product_detail`: Merupakan turunan langsung atau sinonim dari target `product_category`. Menggunakannya akan menghasilkan akurasi palsu (*spurious accuracy*) 100%.
   - `unit_price`: Harga produk berkorelasi langsung dengan jenis kategori produk tertentu (misalnya biji kopi berharga $45,00 vs teh $2,50). Penggunaan harga saat inferensi pelanggan adalah tidak realistis karena harga belum diketahui sebelum menu dipilih.
2. **Alasan `store_id` dan `store_location` Tidak Digunakan**:
   - Seluruh baris yang digunakan telah difilter secara konsisten pada satu lokasi: Cabang Lower Manhattan (`store_id = 5`). Menjadikan `store_id` sebagai fitur hanya akan menghasilkan kolom berbobot varians nol (*zero variance feature*) yang tidak bernilai prediktif.

---

## 4. Penskalaan Fitur (Feature Scaling)

Algoritma K-Nearest Neighbors (KNN) mengukur jarak Euclidean antar-vektor instans:
$$d(x, x') = \sqrt{\sum_{i=1}^n (x_i - x'_i)^2}$$

Karena fitur memiliki rentang nilai yang berbeda (misalnya `jam` bernilai 6 s.d. 20 sedangkan `jam_sin` bernilai $-1$ s.d. $+1$), seluruh fitur masukan diskalakan menggunakan **StandardScaler**:
$$z = \frac{x - \mu}{\sigma}$$
Penskalaan di-*fit* hanya pada set data latih (*training set*) dan kemudian di-*transform* ke set data uji (*test set*) untuk mencegah kebocoran informasi statistik.

---

## 5. Skema Pemisahan Data (Data Splitting Strategy)

Untuk memvalidasi keandalan model secara obyektif, diterapkan dua pendekatan pemisahan data:

1. **Stratified Random Split (80:20)**:
   - Data latih: 80% (38.225 baris).
   - Data uji: 20% (9.557 baris).
   - Parameter: `random_state = 42`, `stratify = y`.
   - Menjamin bahwa proporsi 9 kategori produk pada data uji identik dengan data latih, termasuk untuk kelas minoritas (*Packaged Chocolate* sebanyak 36 sampel pada data uji).
2. **Temporal Split (Pemisahan Berbasis Waktu)**:
   - Data latih: Transaksi bulan Januari s.d. Mei 2023.
   - Data uji: Transaksi bulan Juni 2023.
   - Bertujuan untuk menguji ketahanan model terhadap pergeseran distribusi temporal (*temporal distribution shift*) di dunia nyata.


---

## BAB V: 05 PEMODELAN KNN

# PEMODELAN K-NEAREST NEIGHBORS (KNN)

**Mata Kuliah**: Machine Learning  
**Dataset**: Lower Manhattan Coffee Shop (47.782 baris transaksi)  
**Rujukan Artefak**: `ml/hasil_evaluasi.json`, `matkul/machine-learning/gambar/pemilihan_k.png`  

---

## 1. Konsep Algoritma K-Nearest Neighbors (KNN)

K-Nearest Neighbors (KNN) merupakan algoritma pembelajaran terawasi non-parametrik berbasis instans (*instance-based learning* atau *lazy learning*). Algoritma ini tidak membentuk fungsi hipotesis eksplisit selama tahap pelatihan, melainkan menyimpan seluruh vektor data latih dalam ruang metrik berdimensi $D$.

Ketika vektor kueri baru $x_q$ diberikan pada saat inferensi:
1. Menghitung jarak geometris Euclidean antara $x_q$ dan seluruh titik data latih $x_i$:
   $$d(x_q, x_i) = \sqrt{\sum_{j=1}^D (x_{q,j} - x_{i,j})^2}$$
2. Mengidentifikasi himpunan tetangga terdekat sebanyak $K$: $\mathcal{N}_K(x_q)$.
3. **Untuk Klasifikasi (Tugas 1)**: Menghitung distribusi probabilitas kelas melalui mayoritas suara (*majority voting*) atau proporsi tetangga:
   $$\hat{P}(y = c \mid x_q) = \frac{1}{K} \sum_{i \in \mathcal{N}_K(x_q)} \mathbb{I}(y_i = c)$$
4. **Untuk Regresi (Tugas 2)**: Menghitung nilai rata-rata dari nilai target $K$ tetangga terdekat:
   $$\hat{y}(x_q) = \frac{1}{K} \sum_{i \in \mathcal{N}_K(x_q)} y_i$$

---

## 2. Pemilihan Hyperparameter K (Hyperparameter Tuning)

Pemilihan nilai tetangga $K$ menentukan titik keseimbangan antara *bias* dan *variance*:
- Nilai $K$ yang terlalu kecil ($K=1, 3$) rentan terhadap *overfitting* dan sensitif terhadap derau data (*noise*).
- Nilai $K$ yang terlalu besar mengarah pada *underfitting* di mana model cenderung merata-ratakan prediksi ke arah kelas mayoritas global.

### 2.1. Tuning K pada Tugas 1 (Klasifikasi Kategori)
- Metode: 5-Fold Stratified Cross-Validation pada rentang $K \in [3, 35]$.
- Metrik Evaluasi Tuning: Akurasi Rata-rata Validasi.
- **Nilai K Optimal Terpilih**: **$K = 20$**.
- Hasil: $K=20$ memberikan stabilitas probabilitas tertinggi pada 9 kelas dengan jarak metrik Euclidean terstandarisasi.

### 2.2. Tuning K pada Tugas 2 (Regresi Kepadatan Transaksi per Jam)
- Metode: 5-Fold Cross-Validation pada data agregasi jam $\times$ tanggal.
- Metrik Evaluasi Tuning: Negated Mean Squared Error (MSE).
- **Nilai K Optimal Terpilih**: **$K = 17$**.
- Hasil: $K=17$ meminimalkan kesalahan MAE dan RMSE pada fluktuasi antrean per jam.

---

## 3. Konfigurasi Parameter & Lingkungan Eksperimen

| Komponen Parameter | Tugas 1 (Rekomendasi Menu) | Tugas 2 (Prediksi Keramaian) |
|---|---|---|
| **Kelas Model Scikit-Learn** | `sklearn.neighbors.KNeighborsClassifier` | `sklearn.neighbors.KNeighborsRegressor` |
| **Nilai K (`n_neighbors`)** | 20 | 17 |
| **Fungsi Jarak (`metric`)** | `'euclidean'` ($p=2$) | `'euclidean'` ($p=2$) |
| **Pembobotan (`weights`)** | `'uniform'` | `'uniform'` |
| **Algoritma Pencarian** | `'auto'` (kd_tree / ball_tree) | `'auto'` |
| **Random State / Seed** | 42 (untuk stratifikasi pemisahan data) | 42 |
| **Pustaka & Versi Latih** | Python 3.12, scikit-learn 1.8.0, pandas 2.2.3, numpy 2.1.3 |

Grafik kurva tuning pemilihan nilai $K$ terhadap skor evaluasi tersimpan pada:
- Lokasi berkas: `matkul/machine-learning/gambar/pemilihan_k.png`


---

## BAB VI: 06 EVALUASI DAN BASELINE

# EVALUASI MODEL DAN ANALISIS BASELINE

**Mata Kuliah**: Machine Learning  
**Dataset**: Lower Manhattan Coffee Shop (47.782 baris transaksi)  
**Rujukan Sumber**: `ml/hasil_evaluasi.json`, `ml/hasil_evaluasi.txt`, `matkul/machine-learning/tabel/`  

---

## 1. Evaluasi Tugas 1: Rekomendasi Menu (KNN Klasifikasi)

Pengujian dilakukan pada set data uji 20% (9.557 baris transaksi) dengan stratifikasi seimbang.

### 1.1. Tabel Metrik Klasifikasi Keseluruhan

| Metrik Evaluasi | Nilai Model KNN ($K=20$) | Nilai Baseline Acuan | Interpretasi & Keterangan |
|---|---|---|---|
| **Akurasi (Stratified 80:20)** | **34,89%** ($0,34885$) | **38,10%** ($0,38098$) | KNN berada di bawah Baseline Mayoritas |
| **Baseline 1 (Kelas Mayoritas)** | — | **38,10%** | Selalu memprediksi kelas 'Coffee' |
| **Baseline 2 (Mode per Segmen)** | — | **38,10%** | Selalu memprediksi mode segmen ('Coffee') |
| **Akurasi Split Temporal** | **34,61%** ($0,34605$) | — | Dilatih Jan–Mei, diuji pada bulan Juni |
| **Macro Precision** | **9,79%** ($0,09793$) | — | Rata-rata presisi 9 kelas tidak seimbang |
| **Macro Recall** | **11,12%** ($0,11125$) | — | Rata-rata sensitivitas terhadap seluruh kelas |
| **Macro F1-Score** | **9,30%** ($0,09297$) | — | Rata-rata harmonik P & R antarkelas |
| **Weighted F1-Score** | **28,02%** ($0,28021$) | — | Pembobotan F1 berdasarkan jumlah sampel |
| **Top-3 Hit Rate KNN** | **78,83%** ($0,78832$) | **83,73%** | KNN berada di bawah Baseline Top-3 Populer |

### 1.2. Laporan Klasifikasi Rinci per Kelas (*Classification Report*)

| Kategori Produk | Presisi (Precision) | Recall (Sensitivitas) | F1-Score | Support (Sampel Uji) |
|---|---|---|---|---|
| **Bakery** | 0,12 | 0,03 | 0,05 | 1.578 |
| **Branded** | 0,00 | 0,00 | 0,00 | 70 |
| **Coffee** | 0,38 | 0,69 | 0,49 | 3.641 |
| **Coffee beans** | 0,00 | 0,00 | 0,00 | 106 |
| **Drinking Chocolate** | 0,07 | 0,00 | 0,01 | 681 |
| **Flavours** | 0,00 | 0,00 | 0,00 | 586 |
| **Loose Tea** | 0,00 | 0,00 | 0,00 | 76 |
| **Packaged Chocolate** | 0,00 | 0,00 | 0,00 | 36 |
| **Tea** | 0,30 | 0,28 | 0,29 | 2.783 |
| **Rata-rata Akurasi** | — | — | **0,35** | **9.557** |
| **Macro Avg** | **0,10** | **0,11** | **0,09** | **9.557** |
| **Weighted Avg** | **0,26** | **0,35** | **0,28** | **9.557** |

---

## 2. Analisis Kritis & Perhitungan Baseline Top-3

> [!WARNING]
> **Peringatan Integritas Ilmiah**: Angka Top-3 Hit Rate sebesar 78,83% pada dokumen lama **TIDAK BOLEH** disajikan sebagai bukti keunggulan model KNN.

### Perhitungan Baseline Top-3 Sederhana (Populer):
Di cabang Lower Manhattan, tiga kategori teratas adalah:
1. `Coffee` (proporsi 38,10%)
2. `Tea` (proporsi 29,12%)
3. `Bakery` (proporsi 16,51%)

Total frekuensi ketiga kategori ini adalah:
$$\text{Baseline Top-3 Populer} = 38,10\% + 29,12\% + 16,51\% = \mathbf{83,73\%}$$

Jika sebuah sistem rekomendasi tanpa model cerdas (*naive baseline*) hanya merekomendasikan tiga kategori ini sepanjang waktu, sistem tersebut akan menghasilkan *Hit Rate* sebesar **83,73%**.
Bahkan ketika dianalisis per masing-masing segmen waktu operasional:
- Segmen Pagi: 82,66%
- Segmen Siang: 84,98%
- Segmen Sore: 85,05%
- Segmen Malam: 85,99%

**Kesimpulan Jujur**:
Model KNN ($K=20$) yang menghasilkan Top-3 Hit Rate sebesar **78,83%** sesungguhnya **berkinerja lebih rendah** sebesar 4,90% dibandingkan baseline statis tiga menu terpopuler (83,73%). Hal ini disebabkan oleh sifat data simulasi di mana distribusi menu hampir seragam antar-jam, sehingga penambahan fitur waktu tidak memberikan daya diskriminasi yang cukup bagi KNN untuk melampaui distribusi frekuensi agregat.

---

## 3. Evaluasi Tugas 2: Prediksi Keramaian (KNN Regresi)

Berbeda dengan tugas klasifikasi, pada tugas prediksi keramaian (*crowd density regression*), model KNN menunjukkan keunggulan yang nyata terhadap baseline.

### 3.1. Tabel Perbandingan Metrik Regresi

| Metrik Evaluasi | Model KNN ($K=17$) | Baseline Rata-rata per Jam | Selisih Peningkatan |
|---|---|---|---|
| **MAE (Mean Absolute Error)** | **6,12 transaksi/jam** ($6,1152$) | 7,42 transaksi/jam ($7,4193$) | Turun 1,30 transaksi/jam (Lebih Akurat) |
| **RMSE (Root Mean Squared Error)** | **8,16 transaksi/jam** ($8,1595$) | 10,09 transaksi/jam ($10,0919$) | Turun 1,93 transaksi/jam (Derau Berkurang) |
| **$R^2$ Score (Koefisien Determinasi)** | **0,6191** ($61,91\%$) | 0,4173 ($41,73\%$) | Naik **+20,18%** varians terjelaskan |

### 3.2. Ambang Kuantil Kepadatan (Crowd Thresholds)
Untuk memetakan nilai kontinu ke label ramah pengguna:
- **Sepi**: Estimasi $\le 11,0$ transaksi/jam.
- **Sedang**: Estimasi $> 11,0$ s.d. $\le 28,0$ transaksi/jam.
- **Ramai**: Estimasi $> 28,0$ transaksi/jam.

---

## 4. Kesimpulan Evaluasi Komparatif

1. **Tugas 1 (Rekomendasi Menu)**:
   - Fitur waktu murni tidak memiliki daya prediktif yang cukup untuk memprediksi kategori produk secara akurat pada dataset simulasi ini (Akurasi 34,89% vs Baseline 38,10%).
   - Model KNN cenderung merekomendasikan kelas mayoritas (Coffee dan Tea) dan gagal mengenali kelas minoritas (Branded, Flavours, Packaged Chocolate memiliki Recall 0,00).
2. **Tugas 2 (Prediksi Keramaian)**:
   - Sinyal waktu terbukti sangat kuat dan berhasil dipelajari dengan sangat baik oleh model KNN Regresi ($R^2 = 0,6191$). Model ini andal dan layak digunakan untuk membantu manajemen toko mengatur staf dan operasional kasir.
3. Berkas matriks konfusi dan perbandingan metode dapat dirujuk pada:
   - `matkul/machine-learning/gambar/confusion_matrix.png`
   - `matkul/machine-learning/gambar/perbandingan_metode.png`


---

## BAB VII: 07 DEPLOYMENT FLASK

# DEPLOYMENT MICROSERVICE FLASK DAN INTEGRASI SISTEM

**Mata Kuliah**: Machine Learning  
**Komponen**: Layanan REST API Microservice (`ml/app_flask.py`)  
**Integrasi**: Klien CodeIgniter 4 (`app/Libraries/FlaskRecommendationClient.php`)  

---

## 1. Arsitektur Komunikasi Sistem

Sistem mengadopsi arsitektur terdistribusi *loosely coupled* antara antarmuka web dan mesin kecerdasan buatan:
- **Web Application Core**: CodeIgniter 4 (PHP 8.2 / Web Server Apache XAMPP).
- **Machine Learning Engine**: Python Flask Microservice yang berjalan secara lokal pada port **5001** (`http://127.0.0.1:5001`).
- **Protokol Pertukaran**: RESTful JSON melalui HTTP GET/POST via cURL.

```
+---------------------------+             HTTP GET/POST              +-----------------------------+
|    CodeIgniter 4 (Web)    | -------------------------------------> |    Flask Microservice       |
|  - RekomendasiAPI.php     |                                        |  - app_flask.py (Port 5001) |
|  - FlaskRecommendation... | <------------------------------------- |  - predict.py (KNN Models)  |
+---------------------------+              JSON Response             +-----------------------------+
              |                                                                     |
     (Fallback Otomatis                                                    (Memuat Model .pkl)
     Bila Layanan Mati)                                                             |
              v                                                                     v
   popularitas_segmen.json                                                model_rekomendasi.pkl
                                                                          model_keramaian.pkl
```

---

## 2. Spesifikasi Endpoint REST API

### 2.1. `GET /health`
- **Fungsi**: Memeriksa ketersediaan dan status detak jantung (*health check*) layanan Flask.
- **Contoh Permintaan**:
  ```http
  GET http://127.0.0.1:5001/health HTTP/1.1
  Host: 127.0.0.1:5001
  ```
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "UP",
    "service": "Lower Manhattan Coffee Shop ML Microservice",
    "timestamp": "2026-11-02T08:20:00.123456",
    "version": "2.0.0-planB"
  }
  ```

### 2.2. `GET /model-info`
- **Fungsi**: Mengembalikan informasi metadata model, versi pustaka, tanggal pelatihan, dan hash integritas dataset.
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "success",
    "metadata": {
      "model_version": "2.0.0-planB",
      "timestamp_trained": "2026-10-28 13:08:19",
      "store_location": "Lower Manhattan",
      "store_id": 5,
      "training_rows": 47782,
      "dataset_sha256": "fade0fa022a56467b1071429f265e3252d7069fbc02e29d24381578d6625f7bd"
    }
  }
  ```

### 2.3. `GET /api/rekomendasi`
- **Fungsi**: Menghasilkan rekomendasi daftar menu dan estimasi keramaian berdasarkan parameter jam dan menit.
- **Parameter Kueri**:
  - `jam`: Integer (0–23), contoh `?jam=9`.
  - `menit`: Integer (0–59), contoh `&menit=30`.
- **Contoh Permintaan**:
  ```http
  GET http://127.0.0.1:5001/api/rekomendasi?jam=9&menit=30 HTTP/1.1
  ```
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "success",
    "data": {
      "jam": 9,
      "menit": 30,
      "hari": "Senin",
      "segmen": "pagi",
      "status_keramaian": "Ramai",
      "estimasi_transaksi_per_jam": 34.5,
      "rekomendasi": [
        {
          "peringkat": 1,
          "kategori": "Coffee",
          "nama_produk": "Brewed Coffee",
          "skor_probabilitas": 0.45
        },
        {
          "peringkat": 2,
          "kategori": "Bakery",
          "nama_produk": "Croissant",
          "skor_probabilitas": 0.25
        }
      ]
    }
  }
  ```

### 2.4. `POST /predict-crowd`
- **Fungsi**: Memprediksi secara spesifik volume transaksi per jam untuk perencanaan operasional kasir.
- **Payload (JSON)**:
  ```json
  {
    "jam": 14,
    "hari": 2,
    "bulan": 5
  }
  ```
- **Respons (JSON)**:
  ```json
  {
    "status": "success",
    "estimasi_transaksi": 24.8,
    "label_keramaian": "Sedang",
    "ambang_batas": {
      "sepi_max": 11.0,
      "sedang_max": 28.0
    }
  }
  ```

---

## 3. Mekanisme Toleransi Kesalahan (Fallback di CI4)

Untuk menjamin layanan pelanggan tidak pernah terputus meskipun microservice Flask mengalami gangguan teknis atau mati, diimplementasikan mekanisme *fail-safe* pada `app/Controllers/RekomendasiAPI.php`:

1. **Batas Waktu Koneksi Singkat (Timeout Gate)**:
   - cURL dikonfigurasi dengan batas waktu koneksi **1 detik** dan batas waktu transfer total **3 detik**.
2. **Fallback Berbasis Pengetahuan Historis (*Heuristic Fallback*)**:
   - Jika panggilan cURL gagal atau mengembalikan status HTTP selain 200, kontroler secara otomatis beralih memuat berkas konfigurasi lokal `ml/config/popularitas_segmen.json`.
   - Sistem tetap menyajikan rekomendasi menu berdasarkan modus historis segmen waktu tersebut.
   - Indikator peringatan dicatat pada log internal CodeIgniter tanpa memunculkan galat pada layar pelanggan.
3. **Keamanan CORS (Cross-Origin Resource Sharing)**:
   - Layanan Flask membatasi origin hanya pada domain lokal resmi (`http://localhost:8080`, `http://127.0.0.1:8080`, `http://localhost`, `http://127.0.0.1`) untuk mencegah eksploitasi antardomain.


---

## BAB VIII: 08 KESIMPULAN DAN KETERBATASAN

# KESIMPULAN DAN KETERBATASAN PENELITIAN

**Mata Kuliah**: Machine Learning  
**Topik**: Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu Menggunakan K-Nearest Neighbor  
**Ruang Lingkup**: Lower Manhattan (`store_id = 5`), Dataset Publik Maven Roasters  

---

## 1. Kesimpulan Utama

Berdasarkan seluruh rangkaian audit, eksperimen pemodelan, evaluasi komparatif, dan integrasi sistem yang telah dilaksanakan, diperoleh kesimpulan sebagai berikut:

1. **Kinerja Tugas 1 (Rekomendasi Menu - KNN Klasifikasi)**:
   - Model KNN ($K=20$) menghasilkan akurasi sebesar **34,89%** pada pengujian *stratified 80:20* dan **34,61%** pada *temporal split*.
   - Nilai akurasi ini berada di bawah *Baseline Mayoritas* (**38,10%**) maupun *Baseline Mode Segmen* (**38,10%**).
   - Metrik Top-3 Hit Rate model mencapai **78,83%**, namun secara faktual masih berada di bawah *Baseline Sederhana Tiga Kategori Terpopuler* (Coffee + Tea + Bakery) yang mencapai **83,73%**.
   - Evaluasi menunjukkan bahwa fitur temporal (jam dan hari) saja tidak memiliki varians diskriminatif yang cukup untuk memprediksi kategori produk secara akurat pada dataset simulasi ini (didukung oleh uji Cramér's V sebesar $0,0335$).
2. **Kinerja Tugas 2 (Prediksi Keramaian - KNN Regresi)**:
   - Model KNN ($K=17$) berhasil membuktikan bahwa sinyal temporal sangat kuat dalam memprediksi kepadatan volume transaksi per jam.
   - Model KNN mencapai **MAE 6,12 transaksi/jam**, **RMSE 8,16 transaksi/jam**, dan **$R^2 = 0,6191$**, mengungguli secara signifikan *Baseline Rata-rata per Jam* (MAE 7,42 transaksi/jam, RMSE 10,09 transaksi/jam, $R^2 = 0,4173$).
   - Model ini memberikan kontribusi nyata dalam penentuan ambang kuantil status antrean (Sepi, Sedang, Ramai).
3. **Keandalan Arsitektur**:
   - Integrasi arsitektur microservice Flask (port 5001) dengan CodeIgniter 4 melalui klien cURL terbukti stabil dengan mekanisme toleransi kesalahan (*heuristic fallback*) yang menjamin ketersediaan layanan rekomendasi 100% bagi pelanggan.

---

## 2. Keterbatasan Penelitian (Study Limitations)

1. **Sifat Dataset Simulasi**:
   - Data Maven Roasters merupakan data benchmark simulasi pendidikan. Pola transaksi di dalamnya tidak merefleksikan dinamika psikologis konsumen riil yang mungkin memiliki preferensi musiman atau pengaruh cuaca lokal.
2. **Invariansi Waktu terhadap Komposisi Menu**:
   - Dalam dataset ini, proporsi pemesanan kopi selalu berkisar 38% di setiap jam operasional. Hal ini membatasi efektivitas pendekatan klasifikasi berbasis waktu semata.
3. **Ketiadaan Data Identitas Pelanggan**:
   - Tidak adanya `customer_id` mencegah diterapkannya teknik penyaringan kolaboratif (*Collaborative Filtering* / Matrix Factorization) untuk memberikan rekomendasi yang dipersonalisasi per individu.
4. **Ketiadaan Fitur Kontekstual Eksternal**:
   - Model belum mempertimbangkan faktor cuaca (hujan/panas), suhu udara, hari libur nasional lokal, promo diskon, atau ketersediaan stok fisik secara waktu nyata.

---

## 3. Saran Pengembangan Masa Depan

1. **Pemanfaatan Data Transaksi Riil Kasir**:
   - Mengumpulkan data transaksi langsung dari POS CodeIgniter setelah gerai beroperasi di dunia nyata untuk menangkap pola konsumsi lokal konsumen Indonesia.
2. **Eksplorasi Algoritma Alternatif**:
   - Menguji algoritma berbasis pohon keputusan (*Gradient Boosted Trees* / XGBoost / LightGBM) atau jaringan saraf tiruan (*Multi-Layer Perceptron*) untuk melihat apakah pola non-linier yang lebih kompleks dapat diekstraksi dari data transaksi gabungan.
3. **Pengembangan Personalisasi Berbasis Keranjang (*Association Rule Mining*)**:
   - Mengimplementasikan algoritma Apriori atau FP-Growth untuk merekomendasikan menu pendamping (*cross-selling*, misalnya: "Pembeli Cappuccino 60% membeli Croissant") terlepas dari faktor waktu.
4. **Integrasi Sensor Keramaian Fisik**:
   - Menggabungkan prediksi regresi dengan data kamera counter atau status sensor meja untuk menghasilkan estimasi waktu tunggu pesanan (*waiting time*) yang lebih presisi bagi pengunjung kedai.


---

## DAFTAR PUSTAKA

1. Cover, T., & Hart, P. (1967). Nearest neighbor pattern classification. *IEEE Transactions on Information Theory*, 13(1), 21-27.
2. Pedregosa, F., et al. (2011). Scikit-learn: Machine learning in Python. *Journal of Machine Learning Research*, 12, 2825-2830.
3. McKinney, W. (2010). Data structures for statistical computing in Python. *Proceedings of the 9th Python in Science Conference*, 445, 51-56.
4. Harris, C. R., et al. (2020). Array programming with NumPy. *Nature*, 585(7825), 357-362.
5. Hunter, J. D. (2007). Matplotlib: A 2D graphics environment. *Computing in Science & Engineering*, 9(3), 90-95.
6. Grinberg, M. (2018). *Flask Web Development: Developing Web Applications with Python*. O'Reilly Media.
7. Maven Analytics. (2023). *Coffee Shop Sales Dataset: A simulated dataset of a fictitious coffee shop chain in NYC*. Maven Analytics Data Playground.
8. Fawcett, T. (2006). An introduction to ROC analysis. *Pattern Recognition Letters*, 27(8), 861-874.
9. Powers, D. M. (2020). Evaluation: from precision, recall and F-measure to ROC, informedness, markedness and correlation. *arXiv preprint arXiv:2010.16061*.
10. Chicco, D., & Jurman, G. (2020). The advantages of the Matthews correlation coefficient (MCC) over F1 score and accuracy in binary classification evaluation. *BMC Genomics*, 21(1), 1-13.
11. Cramér, H. (1946). *Mathematical Methods of Statistics*. Princeton University Press.
12. Kruskal, W. H., & Wallis, W. A. (1952). Use of ranks in one-criterion variance analysis. *Journal of the American Statistical Association*, 47(260), 583-621.
13. Altman, N. S. (1992). An introduction to kernel and nearest-neighbor nonparametric regression. *The American Statistician*, 46(3), 175-185.
14. Hastie, T., Tibshirani, R., & Friedman, J. (2009). *The Elements of Statistical Learning: Data Mining, Inference, and Prediction*. Springer Science & Business Media.
15. Goodfellow, I., Bengio, Y., & Courville, A. (2016). *Deep Learning*. MIT Press.
16. Russell, S. J., & Norvig, P. (2020). *Artificial Intelligence: A Modern Approach (4th ed.)*. Pearson.
17. Ricci, F., Rokach, L., & Shapira, B. (2015). *Recommender Systems Handbook*. Springer.
18. Aggarwal, C. C. (2016). *Recommender Systems: The Textbook*. Springer.
19. CodeIgniter Foundation. (2024). *CodeIgniter 4 User Guide*. https://codeigniter.com/user_guide/
20. Fielding, R. T. (2000). *Architectural Styles and the Design of Network-based Software Architectures* (Doctoral dissertation, University of California, Irvine).
