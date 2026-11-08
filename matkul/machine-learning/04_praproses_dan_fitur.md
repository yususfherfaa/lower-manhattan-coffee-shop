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
