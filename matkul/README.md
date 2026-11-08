# DOKUMENTASI MATERI MATA KULIAH (MACHINE LEARNING & KECERDASAN BUATAN)
## REPOSITORI: LOWER MANHATTAN COFFEE SHOP

Folder `matkul/` merupakan penambahan direktori khusus akademik yang memuat seluruh modul laporan, notebook praktikum, tabel numerik, bukti audit reproduksi, dan artefak verifikasi untuk dua mata kuliah:
1. **Machine Learning** (`matkul/machine-learning/`)
2. **Kecerdasan Buatan / Artificial Intelligence** (`matkul/kecerdasan-buatan/`)

---

## 1. Identitas Proyek & Anggota Kelompok

- **Judul Proyek**: Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu Menggunakan K-Nearest Neighbor (Studi Kasus: Cabang Lower Manhattan)
- **Kelas**: 15.5A.05
- **Program Studi**: Informatika / Ilmu Komputer
- **Kampus**: Universitas Bina Sarana Informatika (UBSI) Kampus Kaliabang
- **Dosen Pengampu**: `[ISI NAMA DOSEN PENGAMPU]` *(Silakan disunting sebelum pengumpulan final)*
- **Anggota Kelompok & Pembagian Tugas**:

| No | Nama Mahasiswa | NIM | Peran & Kontribusi Utama | Status Penugasan |
|---|---|---|---|---|
| 1 | **Yusuf Herfa Auliansyah** | 15240075 | Koordinator Proyek, Arsitektur Sistem CI4 & Integrasi REST API Flask | `[ISI DETAIL AKTIVITAS MANDIRI]` |
| 2 | **Yusuf Mansur** | 15240047 | Eksplorasi Kualitas Data (EDA), Praproses Fitur, & Analisis Baseline | `[ISI DETAIL AKTIVITAS MANDIRI]` |
| 3 | **Rizqi Nuzul Firman** | 15240116 | Pemodelan KNN (Klasifikasi & Regresi), Evaluasi Metrik, & Dokumentasi AI | `[ISI DETAIL AKTIVITAS MANDIRI]` |

---

## 2. Peta Berkas & Keterlacakan Ketentuan

```
matkul/
├── README.md                      # Indeks modul (berkas ini)
├── machine-learning/              # Seluruh berkas mata kuliah Machine Learning
│   ├── README.md                  # Panduan modul ML
│   ├── 01_definisi_masalah.md     # Formulasi tugas klasifikasi & regresi
│   ├── 02_dataset_dan_provenance.md # Provenance dataset Maven Roasters (simulasi)
│   ├── 03_kualitas_data_dan_eda.md # 15 Data Quality Gates & Uji Statistik
│   ├── 04_praproses_dan_fitur.md  # Rekayasa fitur siklikal & StandardScaler
│   ├── 05_pemodelan_knn.md        # Tuning hyperparameter K=20 dan K=17
│   ├── 06_evaluasi_dan_baseline.md # Metrik evaluasi & analisis jujur Top-3
│   ├── 07_deployment_flask.md     # Integrasi Microservice Flask :5001 & Fallback
│   ├── 08_kesimpulan_dan_keterbatasan.md # Sintesis & rekomendasi masa depan
│   ├── notebooks/                 # 5 Notebook Jupyter (.ipynb)
│   ├── gambar/                    # Visualisasi hasil (PNG)
│   ├── tabel/                     # Tabel numerik hasil evaluasi (CSV)
│   └── bukti/                     # Log eksekusi & hash SHA-256
├── kecerdasan-buatan/             # Seluruh berkas mata kuliah Kecerdasan Buatan
│   ├── README.md                  # Panduan modul AI/KB
│   ├── 01_deskripsi_sistem_cerdas.md # Formulasi Agen Cerdas (PEAS)
│   ├── 02_representasi_pengetahuan.md # Taksonomi 9 kategori & segmentasi waktu
│   ├── 03_metode_ai_knn_dan_aturan.md # Pembelajaran berbasis instans & hybrid reasoning
│   ├── 04_alur_inferensi.md       # Diagram alur & studi kasus Senin 08:30
│   ├── 05_pengujian_sistem.md     # 10 skenario pengujian nilai batas
│   ├── 06_analisis_etika_dan_keterbatasan.md # Etika AI, transparansi, & privasi data
│   ├── diagram/                   # Diagram alir & arsitektur (.mmd)
│   ├── skenario_uji/              # Tabel CSV skenario pengujian
│   └── bukti/                     # Contoh respons JSON & verifikasi sistem
└── laporan/
    ├── Laporan_Machine_Learning.md    # Laporan gabungan ML monolitik (siap ekspor PDF)
    └── Laporan_Kecerdasan_Buatan.md   # Laporan gabungan KB monolitik (siap ekspor PDF)
```

---

## 3. Cara Reproduksi & Menjalankan Ulang (Reproducibility Guide)

Seluruh hasil analisis, angka metrik, dan model dapat direproduksi secara deterministik:

1. **Aktifkan Virtual Environment Python**:
   ```powershell
   # Windows PowerShell
   .\.venv-ml\Scripts\Activate.ps1
   ```
2. **Periksa Versi Pustaka**:
   ```bash
   python -V
   # Python 3.12.x
   python -c "import sklearn, pandas, numpy, flask, matplotlib; print('Semua pustaka terpasang!')"
   ```
3. **Validasi Konsistensi Angka & Tautan**:
   ```bash
   python matkul/cek_konsistensi.py
   ```
4. **Jalankan Ulang Microservice Flask**:
   ```powershell
   python ml/app_flask.py
   # Layanan berjalan pada http://127.0.0.1:5001
   ```

---

## 4. Panduan Ekspor Laporan ke PDF / Word

Untuk mengubah laporan gabungan di folder `matkul/laporan/` menjadi dokumen PDF atau Microsoft Word:

- **Cara 1: Menggunakan Ekstensi VS Code / Antigravity IDE (Paling Mudah)**:
  1. Pasang ekstensi **Markdown PDF** (`yzane.markdown-pdf`) atau **Markdown Preview Enhanced**.
  2. Buka berkas `matkul/laporan/Laporan_Machine_Learning.md` atau `Laporan_Kecerdasan_Buatan.md`.
  3. Klik kanan pada editor dan pilih **Markdown PDF: Export (pdf)** atau **(docx)**.
- **Cara 2: Menggunakan Pandoc (Command Line)**:
  ```bash
  pandoc matkul/laporan/Laporan_Machine_Learning.md -o Laporan_Machine_Learning.pdf --pdf-engine=xelatex
  pandoc matkul/laporan/Laporan_Kecerdasan_Buatan.md -o Laporan_Kecerdasan_Buatan.docx
  ```
