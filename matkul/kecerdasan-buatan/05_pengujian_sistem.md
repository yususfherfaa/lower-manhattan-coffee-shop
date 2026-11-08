# PENGUJIAN SISTEM CERDAS DAN VERIFIKASI SKENARIO

**Mata Kuliah**: Kecerdasan Buatan (Artificial Intelligence)  
**Komponen Pengujian**: Logika Inferensi, Penanganan Batas (*Boundary Value*), dan Ketahanan Kegagalan (*Fault Tolerance*)  
**Rujukan Berkas**: `matkul/kecerdasan-buatan/skenario_uji/`  

---

## 1. Metodologi Pengujian Sistem

Pengujian sistem cerdas mencakup verifikasi terhadap:
1. **Validitas Fungsional Tiap Segmen Waktu**: Menguji inferensi pada jam-jam representatif pagi, siang, sore, dan malam.
2. **Kondisi Nilai Batas (*Boundary Conditions*)**: Menguji titik pergantian segmen waktu (pukul 10:59 vs 11:00, 14:59 vs 15:00, 17:59 vs 18:00, 20:59 vs 21:00).
3. **Kondisi di Luar Jam Operasional (*Out-of-Bounds*)**: Menguji pemanggilan pada jam toko tutup (misalnya pukul 02:00 dini hari atau 22:00 malam).
4. **Perbedaan Pola Hari Kerja vs Akhir Pekan (*Weekday vs Weekend*)**.
5. **Uji Ketahanan & Kegagalan (*Robustness & Fault Tolerance*)**: Menguji respon sistem saat microservice Flask dimatikan atau parameter input tidak valid.

---

## 2. Tabel Ringkasan Hasil Pengujian Skenario (10 Skenario Kunci)

| ID | Skenario Pengujian | Input Waktu / Kondisi | Hasil yang Diharapkan | Hasil Aktual | Status |
|---|---|---|---|---|---|
| **TC-01** | Segmen Pagi (Jam Sibuk) | Senin, 08:30 WIB | Kategori Coffee & Bakery mendominasi; Label: Ramai | Coffee (P=0.50), Bakery (P=0.25); Label Ramai (34,5 tx/jam) | **LULUS** |
| **TC-02** | Batas Pagi ke Siang | Hari Kerja, 10:59 WIB | Segmen tetap "Pagi"; kategori teratas Coffee/Bakery | Segmen "Pagi"; Rekomendasi Pagi termuat normal | **LULUS** |
| **TC-03** | Batas Awal Siang | Hari Kerja, 11:00 WIB | Segmen beralih ke "Siang"; Menu minuman dingin/teh naik | Segmen "Siang"; Peringkat Tea & Minuman Dingin meningkat | **LULUS** |
| **TC-04** | Batas Siang ke Sore | Hari Kerja, 14:59 WIB | Segmen "Siang"; Label keramaian Sedang | Segmen "Siang"; Label Sedang (21,2 tx/jam) | **LULUS** |
| **TC-05** | Batas Awal Sore | Hari Kerja, 15:00 WIB | Segmen "Sore"; Menu teh dan kudapan santai | Segmen "Sore"; Tea & Bakery masuk 3 besar | **LULUS** |
| **TC-06** | Batas Sore ke Malam | Hari Kerja, 17:59 WIB | Segmen "Sore"; Status keramaian Sedang | Segmen "Sore"; Evaluasi kuantil sesuai | **LULUS** |
| **TC-07** | Batas Awal Malam | Hari Kerja, 18:00 WIB | Segmen "Malam"; Jam santai, volume transaksi mulai menurun | Segmen "Malam"; Label Sedang menuju Sepi | **LULUS** |
| **TC-08** | Batas Penutupan Toko | Hari Kerja, 20:59 WIB | Transaksi terakhir sah; Status: Sepi (jam tutup) | Status Sepi (vol < 11 tx/jam); Berhasil diproses | **LULUS** |
| **TC-09** | Luar Jam Operasional | Hari Kerja, 23:15 WIB | Sistem menolak inferensi aktif; menampilkan info tutup | Sistem mengembalikan flag tutup / fallback aman | **LULUS** |
| **TC-10** | Ketahanan Layanan (Flask Mati) | Flask dihentikan (Port 5001 Down) | CI4 beralih ke Fallback Heuristik dalam < 3 detik | Fallback sukses memuat data popularitas_segmen.json | **LULUS** |

---

## 3. Hasil Pengujian Unit Otomatis (ml/tests/)

Logika segmentasi waktu dan pembersihan data telah divalidasi oleh skrip pengujian unit di `ml/tests/`:
- `test_time_segmentation.py`: Memvalidasi bahwa seluruh kombinasi 24 jam dan 60 menit terpetakan secara deterministik ke dalam 4 segmen baku tanpa celah waktu yang hilang (*gap-free*).
- `test_data_quality.py`: Memvalidasi 15 kondisi kualitas data bahwa tidak ada nilai hilang, tidak ada nilai harga negatif, dan rentang tanggal berada tepat pada 181 hari.
- `test_flask_api.py`: Memvalidasi respon kode status HTTP 200 dan kelengkapan skema JSON pada endpoint `/health` dan `/api/rekomendasi`.

Rincian skenario input-output lengkap terdokumentasi dalam berkas CSV:
- Lokasi: `matkul/kecerdasan-buatan/skenario_uji/hasil_pengujian_skenario.csv`
