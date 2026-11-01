# AUDIT AWAL PROYEK (FASE 0 — PLAN B)
Tanggal Audit: 27 Oktober 2026
Target: Sistem Rekomendasi Menu Warkop Berbasis Waktu Menggunakan K-Nearest Neighbor (Dataset Coffee Shop Sales / Maven Roasters)

---

## 1. Bukti Pengamanan Versi & Basis Data

### A. Git Tag & Branch
- **Tag Pengaman**: `backup-sebelum-planB` telah dibuat pada commit terakhir sebelum perubahan Plan B (`6e45cee`).
- **Branch Kerja**: `planB-dataset-coffeeshop` telah dibuat dan aktif (`Switched to a new branch 'planB-dataset-coffeeshop'`).

### B. Salinan ZIP di Luar Folder Proyek
- File zip dibuat di: `C:\xampp\htdocs\warkop-bahagia-backup-sebelum-planB.zip`
- Ukuran: ~93.28 MB (tidak mencakup `vendor/`, `node_modules/`, `writable/`, dan `.venv-ml/`).

### C. Backup Database MySQL / MariaDB
- Database yang ada pada server MariaDB 10.4: `db_warkop_bahagia` (database aktif aplikasi dengan 12 tabel terisi) dan database kosong `warkop_bahagia`.
- Backup dilakukan menggunakan `mysqldump` ke:
  1. `backup/db_warkop_bahagia_backup_20261027_080000.sql` (55,155 bytes)
  2. `backup/warkop_bahagia_backup_20261027_080000.sql` (1,509 bytes)
- Database uji terisolasi telah disiapkan: `warkop_bahagia_test` (utf8mb4_general_ci). Database utama tidak akan disentuh selama pengujian.

---

## 2. Audit Struktur Repositori

- `app/`:
  - `Config/`: `Database.php` (menunjuk database `warkop_bahagia`), `Recommendation.php` (`LOW_CONFIDENCE_THRESHOLD = 150`), `Routes.php`.
  - `Controllers/`: `RekomendasiAPI.php` (memanggil Flask di port 5001, menangani fallback rule-based & jam sepi, logging ke `recommendation_logs`), `Cart.php`, `Customer/Order.php`, `Kasir/*`, `Admin/*`.
  - `Libraries/`: `FlaskRecommendationClient.php` (HTTP client ke `http://127.0.0.1:5001/api/rekomendasi`).
  - `Models/`: `OrderModel.php`, `OrderItemModel.php`, `PaymentModel.php`, `ProductModel.php`, `StockLogModel.php` (kandidat dead code Fase 11 jika tidak digunakan).
  - `Database/Migrations/`: Berisi migrasi CI4 (mis. `2026-06-22-000001_CreateNotificationsTable.php`, `2026-10-14-*`).
  - `Database/Seeds/`: `MenuMinumanSeeder.php`, dll.
- `ml/`:
  - `dataset_warkop.csv`: Dataset sintetis lama (1.000 baris, seed 42).
  - `generate_dataset.py`: Generator data sintetis lama.
  - `ml_model.py`: Training pipeline lama (fitur sin/cos waktu, 4-fold cross-validation, evaluasi bootstrap 2000 iterasi, gerbang data 30 hari & 2000 baris).
  - `predict.py`: Modul inferensi KNN Top-5 (NearestNeighbors Euclidean, K=5).
  - `app_flask.py`: Server Flask port 5001 endpoint `GET /api/rekomendasi`.
  - `hasil_evaluasi.txt`: Ringkasan metrik lama (tidak sinkron dengan run terkini).
- `public/`:
  - Aset CSS (`theme.css`, Bootstrap), JS, ikon SVG menu (`public/uploads/*.svg`), QRIS placeholder.
- `database/`:
  - `migrasi_varian_suhu.sql`, `Coffee Shop Sales.xlsx` (sumber dataset yang disalin ke `dataset/Coffee Shop Sales.xlsx`).

---

## 3. Alur ML Saat Ini vs Kebutuhan Plan B

| Komponen | Implementasi Saat Ini (Lama) | Perubahan Plan B (Fase 1–7) |
|---|---|---|
| **Dataset** | Sintetis `ml/dataset_warkop.csv` (1.000 baris) | Dataset publik riil Maven Roasters (`149.116` baris) di `dataset/Coffee Shop Sales.xlsx` |
| **Target Prediksi** | Produk langsung (id_produk) | **Tugas 1**: Klasifikasi Kategori Menu (`product_category`) dengan ranking produk berbasis popularitas segmen.<br>**Tugas 2**: Regresi prediksi keramaian per jam. |
| **Fitur** | `sin_time`, `cos_time` (jam + menit/60) | `jam`, `hari_minggu`, `is_weekend`, jam siklis (`jam_sin`/`jam_cos`), `store_id` (opsional) |
| **Pipeline** | Manual numpy / scikit NearestNeighbors | `sklearn.pipeline.Pipeline` (StandardScaler + KNeighborsClassifier/Regressor) |
| **Evaluasi** | Custom bootstrap | Accuracy, Macro/Weighted Precision, Recall, F1, Top-3 Hit Rate, Baseline mayoritas & baseline segmen; MAE, RMSE, R² |
| **Ambang & Gerbang** | Confidence threshold 150, gerbang 30 hari / 2.000 baris | Ambang probabilitas tetangga KNN yang terkalibrasi; gerbang data tetap dipertahankan untuk model mandiri |

---

## 4. Kontrak Antarmuka Flask & CI4

- **Endpoint Eksisting**: `GET /api/rekomendasi?jam={jam}&menit={menit}`
  - Format response:
    ```json
    {
      "status": "success",
      "data": {
        "jam": 10,
        "menit": 30,
        "segmen": "pagi",
        "emoji": "🌅",
        "hari": "Senin",
        "is_weekend": false,
        "kepercayaan": 150,
        "n_tetangga": 5,
        "total_rekomendasi": 5,
        "rekomendasi": [
          {
            "peringkat": 1,
            "id_produk": "PRD0000001",
            "nama": "...",
            "kategori": "...",
            "harga": 16000,
            "skor": 0.4,
            "confidence": 40.0,
            "source": "knn-waktu"
          }
        ],
        "source": "knn-waktu"
      },
      "kepercayaan": 150,
      "n_tetangga": 5
    }
    ```
- **Kontrak Baru**: Semua field di atas **wajib dipertahankan**. Ditambahkan field `sumber_model`, `label_keramaian`, `prediksi_keramaian`, serta endpoint baru `GET /health`, `GET /model-info`, `POST /predict-crowd`.
- **Pencatatan Log**: `RekomendasiAPI::logRecommendation()` mencatat ke tabel `recommendation_logs` (kolom: `jam`, `source`, `menu_ditampilkan`, `id_produk_dipilih`). Kolom baru `sumber_model`, `label_keramaian`, `versi_model` akan ditambahkan di migrasi Plan B.

---

## 5. Audit Skema Database & Collation

- Temuan MariaDB:
  - Tabel `products` memakai `utf8mb4_general_ci`.
  - Tabel `recommendation_logs` memakai `utf8mb4_unicode_ci` (pernah menyebabkan collation mismatch jika JOIN langsung tanpa penyeragaman).
  - Skema Plan B akan memastikan seluruh tabel & kolom baru konsisten menggunakan `utf8mb4_general_ci` untuk mencegah Error 1267.
  - Foreign key aman tanpa circular dependency (mencegah Error 1005).

---

## 6. Daftar Aset Front-End untuk Perombakan Visual (Fase 8)

- File CSS utama: `public/assets/theme.css`
- Halaman Pelanggan:
  - `app/Views/customer/order.php` (halaman pemesanan & widget rekomendasi)
  - `app/Views/customer/cart.php`
  - `app/Views/customer/payment_qris.php`
  - `app/Views/customer/order_success.php`
- Halaman Admin & Kasir:
  - `app/Views/admin/dashboard.php`
  - `app/Views/kasir/dashboard.php`
  - `app/Views/admin/recommendation_acceptance.php`
  - Halaman baru yang akan dibuat: `app/Views/admin/analitik_penjualan.php`, `app/Views/admin/evaluasi_model.php`

---

## 7. Status Audit

Pengamanan dan audit selesai dengan bukti lengkap. Siap melanjutkan ke Fase 1 (Data Quality Gate & Ingesti).
