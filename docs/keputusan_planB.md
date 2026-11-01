# LOG KEPUTUSAN DESAIN & ASUMSI PLAN B
Target: Sistem Rekomendasi Menu Warkop Berbasis Waktu Menggunakan K-Nearest Neighbor

---

## 1. Kurs Konversi USD ke Rupiah
- **Keputusan**: Ditetapkan nilai asumsi dasar $1 USD = Rp16.000.
- **Aturan Pembulatan**: Dibulatkan ke kelipatan Rp500 terdekat.
- **Alasan**: Dataset Maven Roasters menggunakan satuan USD ($0.8 - $45.00). Untuk menu warkop Indonesia, konversi realistis mempermudah integrasi transaksi dan persepsi harga pelanggan.
- **Konfigurasi**: Disimpan di `ml/config/kurs.json` agar mudah diubah tanpa mengubah kode.

---

## 2. Definisi Segmen Waktu
- **Data Operasional Dataset**: Transaksi tercatat hanya pada pukul 06:00 s.d. 20:59.
- **Keputusan Pembagian Segmen**:
  1. **Pagi**: 06:00 – 10:59 WIB
  2. **Siang**: 11:00 – 14:59 WIB
  3. **Sore**: 15:00 – 17:59 WIB
  4. **Malam**: 18:00 – 20:59 WIB
  5. **Di Luar Jam Operasional**: 21:00 – 05:59 WIB (fallback ramah "Toko Sedang Tutup / Di Luar Jam Operasional").
- **Alasan**: Pada kode lama, segmen pagi mencakup jam 05:00, namun dataset Maven tidak memiliki transaksi sebelum 06:00. Sinkronisasi dilakukan antara Flask, CI4, dan dokumentasi.

---

## 3. Pemilihan Target Prediksi KNN
- **Keputusan**:
  - **Tugas 1 (Klasifikasi)**: Target utama adalah `product_category` (9 kategori pada cabang Lower Manhattan). Menu individual diurutkan berdasarkan popularitas pada segmen waktu tersebut.
  - **Tugas 2 (Regresi)**: Prediksi volume transaksi per jam (`total_transaksi_jam`) untuk mengukur kepadatan/keramaian ("Lagi ramai/sedang/sepi").
- **Alasan Ilmiah**: Distribusi kategori dan metrik ketergantungan dihitung ulang hanya dari transaksi Lower Manhattan. Proporsi aktual dan hasil evaluasi tidak diasumsikan dari gabungan tiga cabang; angka terbaru ada pada laporan kualitas data dan hasil evaluasi yang dihasilkan pipeline.

---

## 4. Penggunaan store_id dan Lokasi
- **Keputusan**: Semua proses cleaning, feature engineering, training, evaluasi, popularitas menu, dan agregasi SQL memakai transaksi dengan `store_location = 'Lower Manhattan'` (`store_id = 5`) saja.
- **Rekonsiliasi sumber**: Dataset berisi 149.116 baris; 47.782 transaksi Lower Manhattan menjadi ruang lingkup penelitian, sedangkan 101.334 transaksi dua lokasi lain dikecualikan dari analisis, bukan dianggap sebagai data rusak.
- **Fitur model**: `store_id` tidak dipakai sebagai prediktor karena nilainya konstan setelah filter satu cabang.
- **Katalog**: Kategori `products.kategori` mengikuti nilai kategori dataset, dan `kategori_dataset` menyimpan salinan sumber untuk integrasi dan audit.
