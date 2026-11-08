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
