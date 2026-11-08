# LAPORAN AUDIT REPOSITORI (FASE 0 — READ-ONLY)

**Nama Repositori**: `lower-manhattan-coffee-shop`  
**Waktu Audit**: 2026-11-02 08:15:00 WIB  
**Auditor**: Antigravity Technical Senior Engineer & Academic Writer  
**Cabang Git / Tag Keamanan**: Branch `matkul-ml-kb`, Tag `backup-sebelum-matkul` (berbasis tag `backup-sebelum-planB`)

---

## 1. Pemetaan Struktur & Volume Berkas

| Direktori | Fungsi Utama | Catatan Audit |
|---|---|---|
| `app/` | Aplikasi Web POS & Reservasi Meja QR (CodeIgniter 4) | Controllers (`RekomendasiAPI.php`, `RecommendationLog.php`, `EvaluasiModel.php`, `RecommendationAcceptance.php`), Views pelanggan & kasir, Libraries (`FlaskRecommendationClient.php`). Kode lama terjaga utuh. |
| `ml/` | Pipeline Machine Learning (Python scikit-learn & Flask) | Model rekomendasi & keramaian (`.pkl`), data interim & processed (`dataset_bersih.csv`), konfigurasi JSON, microservice Flask port 5001 (`app_flask.py`). |
| `dataset/` | Berkas mentah dataset publik Maven Roasters | Berkas tunggal `Coffee Shop Sales.csv` (8.970.256 bytes / 8,55 MB). |
| `database/` | Skrip skema database & migrasi SQL Plan B | Skema tabel MariaDB / MySQL. Tidak disentuh selama audit. |
| `docs/` | Dokumentasi arsitektur dan kualitas data | `IDENTITAS_PROYEK.md`, `laporan_kualitas_data.md`, dan folder baru `docs/matkul/`. |
| `public/` | Asset web publik (CSS, JS, upload gambar menu) | Tidak dimodifikasi. |
| `tests/` | Unit test aplikasi PHPUnit | Tidak dimodifikasi. |
| `.venv-ml/` | Virtual environment Python 3.12 lokal | Memuat pustaka `scikit-learn 1.8.0`, `pandas 2.2.3`, `numpy 2.1.3`, `flask 3.1.1`, `matplotlib 3.10.9`. |

---

## 2. Verifikasi Fakta Kunci & Pembuktian Numerik

Seluruh fakta numerik berikut telah diverifikasi secara empiris dari berkas resmi dalam repositori:

1. **Volume & Cakupan Dataset**:
   - Total baris dataset mentah Maven Roasters: **149.116 baris** (SHA-256: `287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1`).
   - Baris cabang lain yang dikecualikan (Astoria & Hell's Kitchen): **101.334 baris**.
   - Baris cabang Lower Manhattan (`store_id = 5`): **47.782 baris** (SHA-256: `fade0fa022a56467b1071429f265e3252d7069fbc02e29d24381578d6625f7bd`).
   - Rentang tanggal: 1 Januari 2023 s.d. 30 Juni 2023 (181 hari berturut-turut).
   - Jam operasional: 06:00 s.d. 20:59.
2. **Hasil Evaluasi Tugas 1 (Klasifikasi Kategori Produk - KNN)**:
   - Nilai $K$ terpilih (5-Fold Stratified CV): **$K = 20$** (jarak Euclidean).
   - Fitur masukan: `jam`, `hari_minggu`, `is_weekend`, `jam_sin`, `jam_cos`.
   - Akurasi KNN Uji (Stratified 80:20): **34,89%** ($0,34885$).
   - Akurasi Baseline 1 (Kelas Mayoritas 'Coffee'): **38,10%** ($0,38098$).
   - Akurasi Baseline 2 (Mode per Segmen Waktu): **38,10%** ($0,38098$).
   - Macro Precision: **9,79%**, Macro Recall: **11,12%**, Macro F1-Score: **9,30%**, Weighted F1-Score: **28,02%**.
   - Top-3 Hit Rate KNN: **78,83%** ($0,78832$).
   - **Baseline Top-3 Sederhana**: Tiga kategori terpopuler ('Coffee', 'Tea', 'Bakery') mencakup **83,73%** transaksi.
   - Akurasi Split Temporal (Jan-Mei latih, Jun uji): **34,61%**.
3. **Hasil Evaluasi Tugas 2 (Regresi Kepadatan Transaksi per Jam - KNN)**:
   - Nilai $K$ terpilih: **$K = 17$**.
   - Fitur masukan: `jam`, `hari_minggu`, `is_weekend`, `bulan`, `jam_sin`, `jam_cos`.
   - KNN MAE: **6,12 transaksi/jam** ($6,1152$) vs Baseline Rata-rata: **7,42 transaksi/jam** ($7,4193$).
   - KNN RMSE: **8,16 transaksi/jam** ($8,1595$) vs Baseline Rata-rata: **10,09 transaksi/jam** ($10,0919$).
   - KNN $R^2$: **0,6191** ($61,91\%$) vs Baseline Rata-rata $R^2$: **0,4173** ($41,73\%$).
   - Ambang Kuantil Keramaian:
     - **Sepi**: $\le 11,0$ transaksi/jam.
     - **Sedang**: $> 11,0$ s.d. $\le 28,0$ transaksi/jam.
     - **Ramai**: $> 28,0$ transaksi/jam.
4. **Analisis Ketergantungan Waktu**:
   - Chi-Square Kategori $\times$ Segmen Waktu: $\chi^2 = 161,2$, $p = 2,63 \times 10^{-22}$, **Cramér's V = 0,0335** (asosiasi sangat lemah/praktis mendekati nol).
   - Kruskal-Wallis Volume Transaksi $\times$ Jam: $H = 1016,86$, $p = 3,78 \times 10^{-208}$ (ketergantungan waktu sangat kuat).
5. **Arsitektur Integrasi & Endpoint Flask**:
   - Port Microservice: `http://127.0.0.1:5001`.
   - Endpoints: `GET /health`, `GET /model-info`, `GET /api/rekomendasi`, `POST /predict-crowd`.
   - Fallback di CodeIgniter 4: `app/Controllers/RekomendasiAPI.php` via `FlaskRecommendationClient.php` menyediakan fallback berbasis data agregat `ml/config/popularitas_segmen.json` ketika Flask mati atau tidak merespons dalam 1 detik koneksi / 3 detik total.

---

## 3. Kesimpulan Audit Fase 0

Tidak ada kontradiksi fatal yang menghambat pembuatan materi mata kuliah. Repositori memiliki data empiris yang lengkap, bersih, dan konsisten. Seluruh struktur folder baru akan dibuat secara terisolasi di bawah direktori `matkul/` tanpa menyentuh kode aplikasi utama.
