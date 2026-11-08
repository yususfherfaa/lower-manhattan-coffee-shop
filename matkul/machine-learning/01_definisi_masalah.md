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
