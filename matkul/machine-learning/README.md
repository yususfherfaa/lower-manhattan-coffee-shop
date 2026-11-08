# MODUL PRAKTIKUM MACHINE LEARNING — LOWER MANHATTAN COFFEE SHOP

Dokumen ini merupakan panduan indeks untuk seluruh materi tugas mata kuliah **Machine Learning** pada repositori `lower-manhattan-coffee-shop`.

---

## 1. Peta Dokumen & Berkas Pendukung

Folder ini memuat 8 dokumen bab kajian, 5 notebook Jupyter interaktif, grafik hasil eksperimen, tabel numerik CSV, serta bukti verifikasi:

| No | Berkas Bab | Topik Pembahasan Utama | Rujukan Kunci |
|---|---|---|---|
| 01 | [`01_definisi_masalah.md`](01_definisi_masalah.md) | Formulasi tugas klasifikasi kategori & regresi keramaian | Ruang lingkup, input/output, baseline |
| 02 | [`02_dataset_dan_provenance.md`](02_dataset_dan_provenance.md) | Provenance dataset Maven Roasters, sifat simulasi, filter Lower Manhattan | SHA-256 hash, 47.782 baris |
| 03 | [`03_kualitas_data_dan_eda.md`](03_kualitas_data_dan_eda.md) | 15 Data Quality Gates, distribusi kategori, uji Chi-Square & Cramér's V | `eda_distribusi.png`, asosiasi waktu |
| 04 | [`04_praproses_dan_fitur.md`](04_praproses_dan_fitur.md) | Rekayasa fitur siklikal, penskalaan, pencegahan kebocoran target | StandardScaler, split 80:20 |
| 05 | [`05_pemodelan_knn.md`](05_pemodelan_knn.md) | Algoritma KNN, jarak Euclidean, pemilihan hyperparameter K | `pemilihan_k.png`, $K=20$ dan $K=17$ |
| 06 | [`06_evaluasi_dan_baseline.md`](06_evaluasi_dan_baseline.md) | Evaluasi metrik, perbandingan baseline mayoritas & baseline top-3 | Analisis jujur Top-3 Hit Rate 78,83% |
| 07 | [`07_deployment_flask.md`](07_deployment_flask.md) | Arsitektur microservice Flask port 5001, endpoints, integrasi CI4 | `/health`, `/api/rekomendasi`, fallback |
| 08 | [`08_kesimpulan_dan_keterbatasan.md`](08_kesimpulan_dan_keterbatasan.md) | Sintesis temuan, keterbatasan data simulasi, saran pengembangan | Rekomendasi masa depan |

---

## 2. Struktur Direktori Praktikum

```
matkul/machine-learning/
├── README.md                      # Indeks modul (berkas ini)
├── 01_definisi_masalah.md
├── 02_dataset_dan_provenance.md
├── 03_kualitas_data_dan_eda.md
├── 04_praproses_dan_fitur.md
├── 05_pemodelan_knn.md
├── 06_evaluasi_dan_baseline.md
├── 07_deployment_flask.md
├── 08_kesimpulan_dan_keterbatasan.md
├── notebooks/
│   ├── 01_eda_kualitas_data.ipynb
│   ├── 02_praproses_dan_split.ipynb
│   ├── 03_knn_klasifikasi.ipynb
│   ├── 04_knn_regresi_keramaian.ipynb
│   └── 05_baseline_dan_top3.ipynb
├── gambar/
│   ├── eda_distribusi.png
│   ├── pemilihan_k.png
│   ├── confusion_matrix.png
│   └── perbandingan_metode.png
├── tabel/
│   ├── profil_dataset.csv
│   ├── distribusi_kategori.csv
│   ├── metrik_klasifikasi.csv
│   └── metrik_regresi.csv
└── bukti/
    ├── jalankan_ulang.log
    └── hash_dataset.txt
```

---

## 3. Panduan Menjalankan Notebook & Reproduksi Hasil

1. **Persiapan Lingkungan**:
   Pastikan menggunakan virtual environment Python 3.12 dengan paket scikit-learn, pandas, numpy, dan matplotlib:
   ```bash
   .\.venv-ml\Scripts\python.exe -m pip list
   ```
2. **Eksekusi Notebook**:
   Notebook pada subfolder `notebooks/` membaca dataset melalui jalur relatif root repositori (`dataset/` dan `ml/`) tanpa mengubah berkas sumber.
3. **Verifikasi Hash Kriptografis**:
   Pastikan hash SHA-256 dataset cocok dengan data yang terdokumentasi pada `bukti/hash_dataset.txt`.
