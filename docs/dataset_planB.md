# DOKUMENTASI DATASET PLAN B (COFFEE SHOP SALES / MAVEN ROASTERS)

---

## 1. Provenance & Informasi Sumber
- **Nama Dataset**: Coffee Shop Sales (Maven Roasters)
- **Penerbit / Sumber**: Maven Analytics Data Playground  
  URL: [https://mavenanalytics.io/data-playground/coffee-shop-sales](https://mavenanalytics.io/data-playground/coffee-shop-sales)
- **Lisensi**: Public Domain
- **Sifat Data**: **Simulasi fiktif untuk pembelajaran analitik**, BUKAN data operasional riil dari kedai kopi di dunia nyata.
- **Lokasi File**: `dataset/Coffee Shop Sales.xlsx` (Ukuran: 8.55 MB, Sheet: `Transactions`)
- **Checksum Integritas (SHA-256)**: `287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1`

---

## 2. Struktur Skema & Rekonsiliasi Baris
- **Baris sumber mentah**: 149.116 baris.
- **Lokasi penelitian**: `Lower Manhattan` (`store_id = 5`).
- **Transaksi lokasi target**: 47.782 baris.
- **Transaksi lokasi lain yang dikecualikan**: 101.334 baris (Astoria dan Hell's Kitchen); ini pengecualian ruang lingkup, bukan data rusak.
- **Baris bersih**: 47.782 baris.
- **Baris tidak valid yang dibuang setelah filter lokasi**: 0.
- **11 Kolom Wajib**:
  1. `transaction_id` (int): ID transaksi asli dari rangkaian gabungan cabang; celah antar-ID wajar setelah pemilihan Lower Manhattan dan bukan baris hilang.
  2. `transaction_date` (datetime): 1 Januari 2023 s.d. 30 Juni 2023 (181 hari lengkap).
  3. `transaction_time` (time): Pukul 06:00 s.d. 20:59 WIB.
  4. `transaction_qty` (int): Kuantitas pembelian pada subset Lower Manhattan (1 s.d. 6 item).
  5. `store_id` (int): bernilai 5 untuk seluruh baris setelah filter lokasi.
  6. `store_location` (string): Nama lokasi gerai.
  7. `product_id` (int): 80 produk unik (1 s.d. 80).
  8. `unit_price` (float): harga satuan sumber dalam USD; rentang aktual subset dicatat dalam laporan kualitas data.
  9. `product_category` (string): 9 kategori produk.
  10. `product_type` (string): 29 tipe produk.
  11. `product_detail` (string): 80 detail produk dengan ukuran di akhiran nama.

**Kategori produk yang dipakai langsung pada katalog dan filter menu:** Bakery, Branded, Coffee, Coffee beans, Drinking Chocolate, Flavours, Loose Tea, Packaged Chocolate, dan Tea.

---

## 3. Pra-pemrosesan & Rekayasa Fitur
1. **Segmen Waktu (4 Segmen)**:
   - **Pagi**: 06:00 – 10:59
   - **Siang**: 11:00 – 14:59
   - **Sore**: 15:00 – 17:59
   - **Malam**: 18:00 – 20:59
   - **Di Luar Jam Operasional**: 21:00 – 05:59 (tidak ada transaksi pada subset; ditangani lewat respons fallback ramah sistem).
2. **Representasi Siklis Waktu**:
   $$\text{waktu\_desimal} = \text{jam} + \frac{\text{menit}}{60}$$
   $$\theta = \frac{2\pi \cdot \text{waktu\_desimal}}{24}$$
   $$\text{jam\_sin} = \sin(\theta), \quad \text{jam\_cos} = \cos(\theta)$$
3. **Ekstraksi Ukuran & Nama Bersih**:
   - Pola regex `\b(Sm|Rg|Lg)\b$` diekstrak menjadi kolom `ukuran`. Nama bersih tanpa akhiran disimpan pada `nama_bersih`.
4. **Harga Katalog**:
   - Modus `unit_price` historis per `product_id` dijadikan acuan harga katalog standar warkop.

---

## 4. Temuan Kritis & Batasan Ilmiah
1. **Invariansi Kategori terhadap Waktu**:
   - Distribusi sembilan kategori dan hasil uji Chi-Square dihitung ulang hanya dari subset Lower Manhattan.
   - Metrik KNN dibandingkan dengan baseline dari subset yang sama. Nilai final terbarui di `ml/metadata.json` dan laporan evaluasi hasil pipeline.
2. **Sinyal Waktu yang Nyata pada Volume Keramaian**:
   - Uji Kruskal-Wallis volume transaksi per jam menghasilkan p < 1e-10.
   - Model KNN Regresi dan baseline dilatih serta dievaluasi hanya menggunakan agregat transaksi Lower Manhattan.
3. **Batasan Penelitian**:
   - Dataset merupakan simulasi lokasi New York (US context).
   - Tidak terdapat ID pelanggan (bukan analisis market-basket atau personalisasi individu).
