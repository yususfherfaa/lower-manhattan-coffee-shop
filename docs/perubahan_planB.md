# DAFTAR PERUBAHAN SISTEM (PLAN B — DATASET COFFEE SHOP SALES)

---

## 1. Berkas Baru yang Ditambahkan

### A. Machine Learning (`ml/`)
- `ml/data_quality.py`: Skrip mandiri Data Quality Gate (15 pengujian kualitas, rekonsiliasi baris, ekspor profil JSON & tabel/grafik jurnal).
- `ml/prepare_dataset.py`: Pra-pemrosesan data, pemetaan 4 segmen waktu, rekayasa fitur waktu siklis (`jam_sin`/`jam_cos`), ekstraksi ukuran produk, dan harga katalog modus.
- `ml/train_evaluate.py`: Pelatihan dan evaluasi KNN 3 Tugas (Klasifikasi Rekomendasi Menu K=20 vs 2 baseline, Regresi Prediksi Keramaian K=18 vs baseline mean, dan Eksploratif product_type/detail).
- `ml/export_sql.py`: Generator otomatis berkas SQL Plan B yang idempoten dan konsisten collation.
- `ml/run_all.py`: Skrip pipeline tunggal ujung-ke-ujung (Data Quality -> Prepare -> Train -> Export SQL -> Pytest).
- `ml/config/kurs.json`: Konfigurasi kurs referensi konversi USD ke Rupiah (Rp16.000).
- `ml/config/mapping_kategori.json`: Pemetaan 9 kategori Maven Roasters ke kategori menu warkop lokal.
- `ml/config/popularitas_segmen.json`: Ringkasan popularitas produk per 4 segmen waktu untuk pemeringkatan menu.
- `ml/tests/test_data_quality.py`: Unit test pytest untuk validasi integritas dataset & interim.
- `ml/tests/test_time_segmentation.py`: Unit test pytest untuk pengujian batas jam segmen waktu warkop.
- `ml/tests/test_flask_api.py`: Unit test pytest untuk endpoint Flask API (`/health`, `/model-info`, `/api/rekomendasi`, `/predict-crowd`).

### B. Basis Data (`database/planB/`)
- `database/planB/01_schema_planB.sql`: Perubahan skema aman (tabel `agg_keramaian_jam`, `agg_kategori_segmen`, kolom pelengkap `products` dan `recommendation_logs`).
- `database/planB/02_seed_katalog.sql`: 80 produk dari katalog dataset publik Maven Roasters dengan ID `PRDMxxxx` dan harga Rupiah.
- `database/planB/03_seed_agregat.sql`: Data agregat keramaian per jam dan komposisi kategori dari 47.782 transaksi Lower Manhattan.
- `database/planB/00_README.md`: Panduan impor dan rollback.
- `database/planB/99_rollback_planB.sql`: Skrip pembatalan perubahan Plan B.

### C. Backend CI4 & Tampilan Admin
- `app/Controllers/Admin/AnalitikPenjualan.php`: Controller dasbor analitik pola penjualan jam warkop.
- `app/Controllers/Admin/EvaluasiModel.php`: Controller visualisasi evaluasi model KNN dan confusion matrix.
- `app/Views/admin/analitik_penjualan.php`: Tampilan analitik admin lengkap dengan grafik distribusi jam dan heatmap hari x jam.
- `app/Views/admin/evaluasi_model.php`: Tampilan laporan metrik ilmiah evaluasi KNN dan baseline untuk dosen penguji.
- `app/Database/Seeds/KatalogMavenSeeder.php`: Seeder CI4 untuk katalog menu Maven.
- `tests/unit/RekomendasiEndToEndTest.php`: Tes integrasi PHPUnit CI4 -> Flask -> Database Test.

### D. Dokumentasi (`docs/`)
- `docs/audit_planB.md`: Laporan audit fase 0 pengamanan versi dan database.
- `docs/laporan_kualitas_data.md`: Laporan 15 pengujian kualitas data (Data Quality Gate).
- `docs/dataset_planB.md`: Provenance, skema, dan batasan dataset publik.
- `docs/desain_db_planB.md`: Desain skema baru, ERD, dan LRS.
- `docs/keputusan_planB.md`: Log keputusan desain dan asumsi ilmiah.
- `docs/perubahan_planB.md`: Berkas ini.
- `docs/tabel_jurnal/`: 15 berkas CSV, Markdown, dan Gambar PNG 300 dpi (distribusi jam, kategori, heatmap, pemilihan K, matriks konfusi, regresi keramaian).

---

## 2. Berkas yang Diperbarui
- `ml/app_flask.py`: Ditambahkan endpoint `/health`, `/model-info`, `/predict-crowd`, serta integrasi model KNN Plan B.
- `ml/predict.py`: Implementasi inferensi model kategori KNN + ranking popularitas menu + prediksi keramaian per jam.
- `app/Config/Routes.php`: Ditambahkan rute admin `/admin/analitik-penjualan` dan `/admin/evaluasi-model`.
- `app/Views/layout/navbar_admin.php`: Ditambahkan menu link "☕ Analitik Penjualan" dan "🤖 Evaluasi Model".
- `app/Controllers/RekomendasiAPI.php`: Ditambahkan pencatatan metadata Plan B (`sumber_model`, `label_keramaian`, `versi_model`) pada tabel `recommendation_logs`.
- `app/Views/customer/order.php`: Ditambahkan tampilan status keramaian ("Lagi Ramai/Santai/Normal") dan sumber model publik coffee shop.
- `public/assets/theme.css`: Ditambahkan variabel tema coffee shop Plan B (espresso, kopi-tua, karamel, krim, susu, hijau-daun, merah-bata).
- `requirements.txt`: Pembaruan versi dependensi Python terpasang.
