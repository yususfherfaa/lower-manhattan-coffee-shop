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
