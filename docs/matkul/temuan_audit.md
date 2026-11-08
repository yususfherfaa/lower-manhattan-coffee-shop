# TEMUAN AUDIT KONSISTENSI & BERKAS LAMA (READ-ONLY AUDIT)

Dokumen ini mencatat seluruh temuan ketidakcocokan, variasi versi antar dokumen, serta berkas basi/duplikat dalam repositori `lower-manhattan-coffee-shop`. Sesuai aturan keras Fase 0, berkas-berkas lama di bawah ini **TIDAK DIUBAH/DIHAPUS** pada penugasan ini, melainkan hanya dilaporkan agar mahasiswa dan tim pengembang dapat menindaklanjutinya pada iterasi berikutnya.

---

## 1. Temuan Berkas Basi / Duplikat / Artefak Lama

1. **`ml/dataset_warkop.csv`**
   - *Status*: Merupakan artefak sisa dari iterasi awal ("Plan A / Warkop Bahagia") sebelum migrasi penuh ke dataset Maven Roasters Lower Manhattan ("Plan B").
   - *Catatan*: Status di Git saat ini terhapus di *working directory* unstaged, namun masih ada di riwayat Git. Berkas ini tidak digunakan lagi oleh sistem saat ini.
2. **`ml/generate_dataset.py`**
   - *Status*: Skrip pembangkit data transaksi sintetis untuk mockup warkop lokal terdahulu.
   - *Catatan*: Tidak digunakan dalam alur pelatihan Maven Roasters Lower Manhattan (`ml/run_all.py` hanya menjalankan `data_quality.py` dan `ml_model.py`).
3. **`ml/model_knn.joblib`**
   - *Status*: Berkas model lama bertipe `.joblib` (303.762 bytes).
   - *Catatan*: Pipeline aktif saat ini menyimpan dan memuat model menggunakan `pickle` yakni `ml/model_rekomendasi.pkl` dan `ml/model_keramaian.pkl`.
4. **Duplikasi / Riwayat Skema Database di `database/`**
   - *Status*: Terdapat skrip `database/planB/` dan migrasi/seeder utama di `app/Database/`.
   - *Catatan*: Pengujian dan bukti model sepenuhnya terisolasi pada berkas dataset CSV tanpa ketergantungan langsung ke database aktif.

---

## 2. Ketidakcocokan Versi Pustaka Antara `metadata.json` vs Lingkungan Nyata

1. **Versi Library pada Metadata**:
   - Di dalam `ml/metadata.json` dan `ml/hasil_evaluasi.json`, tercatat:
     - `sklearn_version: "1.9.1"`
     - `pandas_version: "3.0.6"`
     - `numpy_version: "2.5.4"`
   - Di lingkungan aktual `.venv-ml` (Python 3.12.6) yang terpasang:
     - `scikit-learn`: `1.8.0`
     - `pandas`: `2.2.3`
     - `numpy`: `2.1.3`
   - *Catatan Audit*: Versi pada berkas metadata kemungkinan ditulis secara *forward-looking* / simulasi atau dihasilkan dari runner lingkungan eksperimental terpisah. Semua angka metrik evaluasi (akurasi 34,89%, MAE 6,12, dsb.) tetap sepenuhnya konsisten dan dapat direproduksi pada pipeline scikit-learn standard.

---

## 3. Konsistensi Hash Dataset SHA-256

- **Dataset Mentah Maven Roasters** (`dataset/Coffee Shop Sales.csv`, 149.116 baris):
  - Hash SHA-256: `287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1`
  - Sesuai dengan yang dicatat pada `docs/laporan_kualitas_data.md` Poin 1 & 2.
- **Dataset Bersih Lower Manhattan** (`ml/data/processed/dataset_bersih.csv`, 47.782 baris):
  - Hash SHA-256: `fade0fa022a56467b1071429f265e3252d7069fbc02e29d24381578d6625f7bd`
  - Sesuai dengan yang dicatat pada `ml/hasil_evaluasi.json`, `ml/hasil_evaluasi.txt`, dan `ml/metadata.json`.
- *Catatan*: Kedua hash ini valid dan mewakili dua tahapan berkas yang berbeda (data mentah gabungan 3 cabang vs data terfilter bersih Lower Manhattan).

---

## 4. Pelaporan Metrik Top-3 Hit Rate

- Pada berkas `ml/hasil_evaluasi.txt` tertulis:
  `Akurasi KNN 34.89% dibandingkan baseline mayoritas 38.10%. Top-3 Hit Rate mencapai 78.83%.`
- *Temuan Kritis*: Dokumen lama belum menyertakan perbandingan *baseline top-3*.
- Setelah diaudit dan dihitung langsung pada `dataset_bersih.csv`:
  - 3 kategori produk terbanyak di cabang Lower Manhattan adalah **Coffee** (38,10%), **Tea** (29,12%), dan **Bakery** (16,51%).
  - Total proporsi 3 kategori terpopuler ini mencakup **83,73%** dari seluruh transaksi Lower Manhattan.
  - Per segmen waktu: Pagi (82,66%), Siang (84,98%), Sore (85,05%), Malam (85,99%).
  - Oleh karena itu, Top-3 Hit Rate model KNN sebesar 78,83% sebenarnya **berada di bawah baseline sederhana 3 kategori terpopuler (83,73%)**. Hal ini dicatat secara jujur dan transparan pada seluruh berkas materi perkuliahan baru di `matkul/`.
