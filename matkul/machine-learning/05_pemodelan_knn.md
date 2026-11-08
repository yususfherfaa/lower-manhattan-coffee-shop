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
