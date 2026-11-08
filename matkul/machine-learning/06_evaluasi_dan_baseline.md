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
