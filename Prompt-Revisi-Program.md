# PROMPT LENGKAP PLAN B — WARKOP BAHAGIA (DATASET COFFEE SHOP SALES / MAVEN ROASTERS)

Salin seluruh isi di bawah garis ini ke AI agent di text editor. Taruh dulu file dataset di `dataset/Coffee Shop Sales.xlsx` (di root folder proyek) sebelum menjalankan prompt.

---

## PERAN

Kamu adalah engineer senior (Python/pandas/scikit-learn, Flask, CodeIgniter 4, MariaDB 10.4, front-end) yang merombak proyek "Warkop Bahagia" agar komponen machine learning-nya memakai dataset publik bertema coffee shop, bukan dataset sintetis buatan sendiri.

Judul penelitian sudah disetujui dosen dan TIDAK boleh berubah: "Sistem Rekomendasi Menu Warkop Berbasis Waktu Menggunakan K-Nearest Neighbor". Jadi sistem tetap harus berupa rekomendasi menu berbasis waktu (realtime) dengan KNN, dengan data bertema coffee shop.

Bekerjalah bertahap sesuai fase di bawah. Setelah tiap fase, tulis ring
kasan singkat + bukti. Jika ada keputusan ambigu, tulis keputusan beserta alasan dan alternatifnya di `docs/keputusan_planB.md`, jangan diam-diam memilih.

---

## ATURAN KERAS (WAJIB, TIDAK BOLEH DILANGGAR)

1. JANGAN mengubah data di database utama `warkop_bahagia`. Semua pengujian yang mengubah data hanya pada salinan `warkop_bahagia_test`. Lakukan `mysqldump` backup dulu sebelum menyentuh DB apa pun, simpan di `backup/` dengan stempel waktu.
2. Sebelum mengubah file apa pun, buat pengaman versi: (a) jika repo git, buat tag `backup-sebelum-planB` dan branch kerja `planB-dataset-coffeeshop`; (b) buat salinan ZIP seluruh proyek di luar folder proyek (mis. `../warkop-bahagia-backup-sebelum-planB.zip`), kecuali `vendor/`, `node_modules/`, dan `writable/`. Bukti keduanya harus ditampilkan sebelum lanjut.
3. JANGAN menampilkan isi `.env`, password, hash, token, atau kunci enkripsi di output, log, atau dokumen mana pun.
4. JANGAN memperbaiki atau mengubah tabel sistem MariaDB (mis. global_priv).
5. Jangan menerima status "selesai" tanpa bukti (output perintah, hasil tes, atau file yang benar-benar ada). Setiap klaim harus disertai bukti yang bisa dicek.
6. Jangan mengarang angka. Semua angka evaluasi dan profil data harus berasal dari hasil menjalankan kode. Jika file dataset tidak ada, BERHENTI dan laporkan; jangan membuat data pengganti.
7. Jangan menyetel data, parameter, atau split hanya agar akurasi terlihat tinggi. Laporkan apa adanya.
8. Jangan mengedit migrasi lama. Perubahan skema hanya lewat migrasi BARU dan file SQL BARU.
9. Fase pembersihan file (Fase 11) hanya boleh dijalankan setelah SEMUA verifikasi (Fase 10) lulus.

---

## KONTEKS PROYEK

- Stack: CodeIgniter 4 (PHP) untuk aplikasi + Flask (Python) untuk layanan ML + MariaDB 10.4.
- Folder `ml/` saat ini berisi dataset sintetis (1000 baris, seed 42) dan pipeline KNN: LabelEncoder, StandardScaler, split 80:20 stratified (random_state=42), K dipilih 5-fold CV (K=1–20), Euclidean, fallback rule-based dan "jam sepi", ambang confidence 150, gerbang data 30 hari/2000 baris (klaim PR-18: baca kode dan buktikan nilainya).
- Catatan basi: `ml/hasil_evaluasi.txt` (K=1, 90,50%, support 80/120) tidak cocok dengan CSV terkini (89,00%). Semua angka lama akan diganti hasil run baru.
- Catatan DB: migrasi 2026-10-14 menghapus FK data_products→products. Pelajaran dari pengujian sebelumnya: import schema.sql apa adanya memberi error 1005 (urutan FK), JOIN beda collation memberi error 1267, dan enum terpotong memberi error 1265 jika urutan migrasi salah. File SQL baru harus menghindari ketiganya.

## DATASET BARU

- Nama: "Coffee Shop Sales" (Maven Roasters), Maven Analytics. Sumber: https://mavenanalytics.io/data-playground/coffee-shop-sales (lisensi tertera Public Domain; verifikasi ulang di halaman dan catat).
- Lokasi file: `dataset/Coffee Shop Sales.xlsx`. Jika tidak ada, berhenti dan minta user menaruhnya di sana.
- Dataset ini fiktif/simulasi untuk pembelajaran, BUKAN data operasional nyata. Semua dokumentasi dan naskah harus menyebutnya jujur: "dataset publik simulasi bertema coffee shop".

### Fakta terverifikasi (acuan; tetap divalidasi ulang dengan kode)
- Satu sheet `Transactions`, 149.116 baris × 11 kolom. Abaikan angka "499 records/25 fields" di halaman Maven (salah).
- Kolom: transaction_id (int), transaction_date (datetime), transaction_time (datetime.time, jangan diparse sebagai string biasa), transaction_qty (int 1–8), store_id (3=Astoria, 5=Lower Manhattan, 8=Hell's Kitchen), store_location, product_id (80 unik), unit_price (0,8–45), product_category (9), product_type (29), product_detail (80; ukuran Sm/Rg/Lg ada di nama).
- Rentang 2023-01-01 s.d. 2023-06-30 (181 hari); 3 toko lengkap tiap hari. Tanpa NA dan tanpa duplikat pada pengecekan awal.
- transaction_id punya celah (maks 149.456 vs 149.116 baris, ±340 hilang). Dokumentasikan; jangan "diperbaiki".
- Jam transaksi hanya 06:00–20:59 (jam 20 hanya ±603 baris).
- Satu baris = satu item transaksi; TIDAK ada ID pelanggan/keranjang. Jangan mengklaim analisis per pelanggan atau market-basket.
- Harga: satu product_id bisa punya hingga 4 harga (perubahan harga). Harga 45 hanya di Coffee beans/Branded (non-minuman) dan itu sah, bukan outlier salah.
- Volume naik per bulan (Jan ±17 ribu, Jun ±35 ribu baris). Split berbasis waktu akan mengalami pergeseran distribusi; laporkan.
- TEMUAN KRITIS: komposisi kategori hampir tidak bergantung pada waktu (Coffee 37–41%, Tea 29–33%, Bakery 14–16% di semua segmen). Uji awal: KNN (fitur jam, hari, weekend, toko) TIDAK melampaui tebakan mayoritas (kategori: mayoritas 39,2% vs KNN 31,5–37,5%; product_type: 11,5% vs ±10%; product_detail: 2,1% vs ±1,9%). Sinyal waktu yang nyata ada pada VOLUME transaksi per jam, bukan pada komposisi menu. Desain di bawah mengikuti temuan ini.

---

## FASE 0 — PENGAMANAN & AUDIT (READ-ONLY terhadap kode)

1. Jalankan aturan 2 (tag, branch, ZIP) dan `mysqldump` DB utama ke `backup/`. Tampilkan bukti.
2. Audit dan laporkan ke `docs/audit_planB.md`:
   a. Struktur repo: `app/` (controller, model, view, migrasi, seeder, config), `ml/` (skrip, pkl, csv, hasil evaluasi), `public/` (aset), README, requirements, file SQL (`db_mysql.sql`, `schema.sql`, migrasi SQL lain).
   b. Alur ML saat ini (fitur, label, preprocessing, pemilihan K, fallback, ambang, gerbang data).
   c. Kontrak antarmuka Flask: path, method, JSON request/response; pemanggil di CI4; bagaimana hasil dicatat ke `recommendation_logs`.
   d. Semua tempat yang menyebut angka evaluasi atau dataset (README, hasil_evaluasi.txt, komentar kode, view).
   e. Skema DB relevan: products, data_products, orders, order_items, payments, recommendation_logs, settings; kolom, tipe, FK, collation, enum.
   f. Daftar view/CSS/JS untuk perombakan visual (framework CSS, layout, halaman pelanggan dan admin).
   g. Daftar file yang KANDIDAT sampah/duplikat (belum dihapus; hanya dicatat untuk Fase 11).
3. Jika ada kontradiksi yang menghalangi, berhenti dan tampilkan ringkasan.

---

## FASE 1 — INGESTI & PENGUJIAN KUALITAS DATA (DATA QUALITY GATE)

Buat `ml/data_quality.py` (dapat dijalankan mandiri) dan `ml/prepare_dataset.py`. Seluruh pengujian menghasilkan laporan dan menghentikan pipeline bila ada kegagalan kritis (gate).

### 1A. Alur tahapan dan rekonsiliasi jumlah baris
Tahap data: `dataset/` (mentah, TIDAK PERNAH diubah) → `ml/data/interim/transaksi_valid.parquet/csv` (setelah validasi) → `ml/data/processed/dataset_bersih.csv` (siap model). Catat jumlah baris di setiap tahap dan buktikan: baris_awal = baris_bersih + baris_dibuang (dengan rincian alasan). Jika tidak cocok, pipeline gagal.

### 1B. Daftar pengujian (tiap pengujian mencatat: nama, hasil lulus/gagal, jumlah temuan, contoh 5 baris, tindakan, alasan)
1. Keberadaan & integritas file: file ada, dapat dibuka, nama sheet `Transactions`, hash SHA-256 file dicatat ke laporan.
2. Skema: 11 kolom wajib ada dengan nama persis; tidak ada kolom ekstra tak terduga; tipe data sesuai (int, datetime, time, float, string).
3. Missing value: hitung NA/NaN/None/string kosong/"NULL"/"-"/spasi saja pada SEMUA kolom (persentase dan jumlah). Jika ada: tentukan perlakuan per kolom (hapus baris untuk kolom kunci seperti tanggal/jam/produk/qty/harga; imputasi hanya bila beralasan kuat dan didokumentasikan). Jika tidak ada, tulis tegas "0 missing value" lengkap dengan bukti.
4. Duplikat: duplikat baris penuh dan duplikat transaction_id; juga duplikat semu (semua kolom sama kecuali transaction_id) — laporkan, JANGAN dibuang otomatis karena bisa transaksi sah (qty sama, waktu sama), beri keputusan tertulis.
5. Validitas tanggal & jam: tanggal dalam rentang yang diharapkan; jam 0–23; tidak ada tanggal masa depan; kelengkapan tanggal (181 hari; cek tiap toko × tiap hari); daftar hari kosong bila ada.
6. Validitas rentang numerik: transaction_qty > 0 dan bilangan bulat; unit_price > 0; laporkan min/maks/kuartil. Outlier (IQR dan z-score): tandai, tetapi tentukan sah/tidaknya dengan pengecekan konteks (qty 8, harga 45 pada Coffee beans/Branded adalah sah; simpan, beri kolom penanda `is_outlier_qty`/`is_outlier_price`, jangan dibuang tanpa alasan).
7. Konsistensi kategorikal: whitespace di awal/akhir, perbedaan huruf besar/kecil, ejaan ganda; satu product_id harus memetakan ke tepat satu (product_category, product_type, product_detail) (laporkan pelanggar); store_id ↔ store_location satu-ke-satu.
8. Konsistensi harga: untuk tiap product_id, daftar harga berbeda dan periode berlakunya; tentukan "harga katalog" = modus; dokumentasikan.
9. ID transaksi: celah urutan transaction_id (jumlah dan contoh); dokumentasikan sebagai "ID hilang dari sumber", tanpa imputasi.
10. Jam operasional: distribusi per jam, per segmen, per toko; tandai jam sangat sedikit (jam 20) tapi pertahankan; transaksi di luar 06–20 (seharusnya 0) dilaporkan.
11. Distribusi & ketidakseimbangan kelas: frekuensi tiap kategori/tipe/produk; kelas dengan < 30 sampel dicatat dan diputuskan (gabung ke "Lainnya" untuk pemodelan kategori bila perlu; dokumentasikan).
12. Tren volume per bulan/minggu dan potensi pergeseran distribusi (untuk evaluasi split-waktu).
13. Uji ketergantungan: chi-square independensi (product_category × segmen_waktu) dengan p-value dan Cramér's V; uji volume transaksi vs jam (mis. Kruskal-Wallis atau ANOVA) beserta ukuran efek.
14. Pemeriksaan kebocoran target (lihat Fase 3) dilakukan di tahap ini sebagai daftar fitur terlarang.
15. Reprodusibilitas: jalankan seluruh pipeline data dua kali; hash `dataset_bersih.csv` harus identik.

### 1C. Keluaran
- `docs/laporan_kualitas_data.md` (ringkas, bahasa Indonesia, tabel temuan per pengujian dan log keputusan "temuan → tindakan → alasan").
- `ml/data/profil_dataset.json` (angka mentah semua pengujian).
- `docs/tabel_jurnal/` berisi tabel profil dataset (CSV dan Markdown) dan gambar PNG 300 dpi: distribusi jam, distribusi kategori, heatmap hari×jam, tren bulanan, matriks missing (bila ada).
- `ml/data/log_pembersihan.csv`: setiap baris yang dibuang/diubah beserta alasan (kosong bila tidak ada).
- Status akhir "DATA LAYAK DIGUNAKAN: YA/TIDAK" lengkap alasan. Fase 2 dan seterusnya hanya berjalan jika status YA.
- Tes otomatis (pytest) di `ml/tests/test_data_quality.py` yang mengulang pengujian kritis (skema, NA, rentang, rekonsiliasi baris) agar bisa dijalankan ulang kapan saja.

---

## FASE 2 — PRA-PEMROSESAN & REKAYASA FITUR

1. Parse tanggal dan jam. Turunkan: `jam` (0–23), `hari_minggu` (0–6), `is_weekend`, `bulan`, `segmen_waktu` dengan 4 segmen yang disesuaikan data: pagi 06:00–10:59, siang 11:00–14:59, sore 15:00–17:59, malam 18:00–20:59. Jam di luar 06:00–20:59 = "di luar jam operasional" (tidak dimodelkan; sistem memakai fallback statis). Baca definisi segmen di kode lama, jelaskan perbedaannya, dan sinkronkan Flask + CI4 + dokumentasi.
2. Turunkan juga `ukuran` (Sm/Rg/Lg/none) dari akhiran product_detail, `nama_bersih` tanpa akhiran ukuran, `harga_katalog` (modus per product_id).
3. Target klasifikasi UTAMA: `product_category`. Ranking produk dalam kategori dari popularitas per segmen.
4. Fitur model: `jam`, `hari_minggu`, `is_weekend`; `store_id` opsional (uji dengan dan tanpa, pilih dengan alasan tertulis). Uji juga jam siklik (jam_sin/jam_cos) dan bandingkan.
5. Anti-kebocoran: JANGAN pakai transaction_id, product_id, unit_price, product_detail, product_type sebagai fitur untuk target kategori. Split train/test SEBELUM fit scaler/encoder (gunakan sklearn Pipeline).
6. Split 80:20 stratified, random_state=42. Tambahkan split berbasis waktu (latih Jan–Mei, uji Juni) sebagai evaluasi tambahan; laporkan pergeseran distribusinya.
7. Keluaran: `ml/data/processed/dataset_bersih.csv`, tabel pra-pemrosesan di `docs/tabel_jurnal/`.

---

## FASE 3 — MODEL KNN & EVALUASI (TIGA TUGAS)

1. **Tugas 1 — Rekomendasi menu (KNN klasifikasi), inti sistem.** Pipeline (StandardScaler + KNeighborsClassifier, Euclidean). K dipilih lewat 5-fold stratified CV (K=1–20) pada data latih saja; simpan grafik K vs akurasi CV. Evaluasi WAJIB: accuracy, macro dan weighted precision/recall/F1, classification report per kelas, confusion matrix (PNG), top-3 hit rate, support per kelas. Bandingkan dengan DUA baseline: (a) kelas mayoritas, (b) kelas terpopuler per segmen. Laporkan apa adanya, termasuk jika KNN tidak mengungguli baseline. Tulis kesimpulan jujur: "komposisi menu pada dataset ini hampir invarian terhadap waktu" didukung chi-square dan Cramér's V.
2. **Tugas 2 — Prediksi keramaian (KNN regresi).** Agregasi jumlah transaksi dan total qty per (tanggal, jam, store_id). Fitur: jam, hari_minggu, is_weekend (tambah bulan/tren bila perlu). Target: jumlah transaksi per jam. Evaluasi: MAE, RMSE, R², dibanding baseline rata-rata per jam. Pemilihan K dengan TimeSeriesSplit dan juga 5-fold biasa sebagai perbandingan. Turunkan label "ramai/sedang/sepi" lewat kuantil data latih; hubungkan ke fallback "jam sepi" lama.
3. **Tugas 3 (eksploratif, lampiran)** — KNN klasifikasi product_type dan product_detail; dilaporkan saja, bukan fitur utama.
4. Penyimpanan: `model_rekomendasi.pkl`, `model_keramaian.pkl` (Pipeline lengkap) + `metadata.json` (versi sklearn/pandas/numpy, seed, tanggal, fitur, K, metrik, hash dataset). Pastikan dapat dimuat ulang dan prediksi identik.
5. Tulis `ml/hasil_evaluasi.txt` dan `ml/hasil_evaluasi.json` OTOMATIS dari kode. Jalankan dua kali dan buktikan hasil identik.
6. Jangan mengubah `n_neighbors`, split, atau fitur setelah melihat hasil uji hanya untuk memperbaiki angka.

---

## FASE 4 — DATABASE: MIGRASI & FILE SQL BARU

Tujuan: mendukung katalog dari dataset, analitik, dan log, tanpa merusak skema yang ada. DB utama tidak disentuh; semua uji di `warkop_bahagia_test`.

1. Rancang perubahan minimal dan tulis alasannya di `docs/desain_db_planB.md`. Kandidat (putuskan berdasarkan audit Fase 0, jangan duplikat kolom yang sudah ada):
   - Kolom tambahan di `products` (atau tabel kategori terpisah): `kategori_dataset`, `tipe_dataset`, `ukuran`, `kode_produk_dataset` (product_id Maven, UNIQUE, nullable), `harga_dolar_ref`.
   - Tabel `ref_kategori` (9 kategori dataset → kategori menu warkop) bila perlu pemetaan.
   - Tabel agregat kecil `agg_keramaian_jam` (hari_minggu, jam, store opsional, rata-rata/total transaksi, total qty, periode) untuk halaman analitik tanpa memuat 149 ribu baris.
   - Tabel `agg_kategori_segmen` (segmen, kategori, jumlah, proporsi).
   - Tabel stagging `dataset_transaksi` (opsional): hanya bila halaman analitik memang butuh data mentah; jika dibuat, isi lewat skrip impor terpisah (bukan lewat file SQL raksasa) dan beri indeks pada (transaction_date, jam).
   - Kolom tambahan `recommendation_logs`: `sumber_model` (public_dataset/fallback/sistem_sendiri), `label_keramaian`, `versi_model`, bila belum ada.
2. Buat migrasi CI4 BARU (nama dengan stempel waktu terbaru) — jangan edit migrasi lama. Migrasi harus idempoten, memiliki `down()` yang benar, dan konsisten collation `utf8mb4` dengan collation tabel yang ada (hindari error 1267), tipe enum sesuai (hindari 1265), serta urutan FK benar (hindari 1005).
3. Buat file SQL BARU yang bisa diimpor lewat phpMyAdmin untuk MariaDB 10.4.32:
   - `database/planB/01_schema_planB.sql` — hanya perubahan struktur (ALTER/CREATE) dengan `IF NOT EXISTS`/pengecekan, dan urutan aman.
   - `database/planB/02_seed_katalog.sql` — 80 produk dari dataset (nama_bersih, kategori, tipe, ukuran, harga Rupiah hasil konversi).
   - `database/planB/03_seed_agregat.sql` — isi tabel agregat (hasil perhitungan nyata dari dataset, bukan angka karangan).
   - `database/planB/00_README.md` — urutan impor, prasyarat, cara rollback, peringatan "jalankan di DB test dulu".
   - `database/planB/99_rollback_planB.sql` — membatalkan perubahan.
   - File-file ini dibangkitkan oleh skrip (`ml/export_sql.py`) dari data nyata sehingga bisa direproduksi.
4. Konversi harga: kurs di file konfigurasi `ml/config/kurs.json` (default 1 USD = Rp16.000, tandai sebagai ASUMSI), dibulatkan ke Rp500, mudah diganti.
5. Pengujian DB (di instans/salinan `warkop_bahagia_test`, atau instans MariaDB sementara): impor schema → seed → agregat berurutan tanpa error; impor ulang (idempotensi) tidak menggandakan data; rollback mengembalikan struktur; `SHOW CREATE TABLE` dan jumlah baris sebagai bukti; uji JOIN lintas tabel tanpa error collation; uji FK (RESTRICT) tetap bekerja.
6. Bukti bahwa DB utama tidak berubah (checksum/jumlah baris tiap tabel sebelum dan sesudah).
7. Jelaskan di `docs/desain_db_planB.md` perubahan ERD/LRS (tabel/kolom baru), agar gambar ERD dan LRS bisa diperbarui.

---

## FASE 5 — REKOMENDASI MENU (KATEGORI → MENU)

1. Model memeringkat KATEGORI untuk waktu realtime (top-N berdasarkan probabilitas tetangga), lalu memilih menu dari kategori tersebut berdasarkan penjualan terbanyak pada segmen itu (dari tabel agregat/katalog; jika data sistem cukup, dari `order_items`).
2. Berkas pemetaan `ml/config/mapping_kategori.json`: kategori dataset → kategori menu di tabel `products`. Cek struktur dan nama kategori sebenarnya lewat migrasi/seed; jangan menebak.
3. Fallback tetap: (a) rule-based per segmen bila confidence rendah (definisikan ulang ambang dalam skala probabilitas KNN; jelaskan konversi dari angka 150 lama), (b) "jam sepi" bila segmen tanpa data, (c) "di luar jam operasional" untuk jam < 06:00 atau ≥ 21:00 dengan pesan ramah.
4. Gerbang data (30 hari/2000 baris) tetap berlaku untuk model yang dilatih dari data sistem sendiri; jelaskan kapan sistem memakai model publik vs model data sendiri, dan buat mode sumber data di konfigurasi.
5. Waktu realtime: CI4 mengirim waktu server (zona Asia/Jakarta) saat permintaan; endpoint juga menerima override waktu untuk pengujian. Pastikan tidak ada bug zona waktu (uji di batas 05:59/06:00, 10:59/11:00, 14:59/15:00, 17:59/18:00, 20:59/21:00, tengah malam).

---

## FASE 6 — API FLASK & INTEGRASI CI4

1. Pertahankan KONTRAK endpoint lama (path, method, bentuk JSON). Field baru hanya ditambah, tidak ada yang dihapus.
2. Endpoint tambahan: `GET /health`, `GET /model-info` (versi, metrik, tanggal latih, hash dataset), `POST /predict-crowd` (jam, hari → perkiraan transaksi + label keramaian).
3. Validasi input, penanganan model tidak ditemukan, respons JSON error dengan kode HTTP tepat, timeout dan fallback saat Flask mati (CI4 tetap jalan dengan rekomendasi statis).
4. `recommendation_logs` tetap terisi benar, termasuk `sumber_model`, `label_keramaian`, `versi_model`.
5. Uji end-to-end di `warkop_bahagia_test`: CI4 → Flask → log tercatat.

---

## FASE 7 — FITUR TAMBAHAN BERBASIS DATASET (BANGUN SEMUA, TERMASUK TES)

1. **Katalog menu** dari 80 produk dataset (seeder CI4 dan/atau SQL Fase 4): nama, kategori, tipe, ukuran, harga Rupiah. Seeder default hanya menyentuh DB test; DB utama hanya dengan flag eksplisit setelah backup.
2. **Halaman Analitik Penjualan (admin):** heatmap hari×jam, grafik transaksi per jam, komposisi kategori per segmen, top 10 produk per segmen, tren harian/mingguan/bulanan, pendapatan per kategori (qty × harga), perbandingan 3 toko (opsional; jelaskan bahwa warkop tunggal bisa menyembunyikannya). Semua angka dibaca dari tabel agregat/JSON hasil skrip, bukan hard-code.
3. **Panel "Rekomendasi untuk jam ini" (pelanggan):** kartu top-3/5 menu per segmen, label sumber ("Berdasarkan pola penjualan"), indikator "Lagi ramai/sedang/sepi", dan catatan kecil bahwa rekomendasi berbasis dataset publik simulasi bila itu sumbernya.
4. **Filter menu** berdasarkan kategori dan ukuran (Sm/Rg/Lg) di halaman pemesanan.
5. **Halaman "Evaluasi Model" (admin):** metrik, baseline, confusion matrix, grafik K vs CV, grafik prediksi keramaian, hasil chi-square — dibaca dari berkas hasil evaluasi.
6. **Perintah tunggal** `python ml/run_all.py` (dan `make train` bila ada Makefile): kualitas data → prepare → latih kedua model → evaluasi → simpan artefak → ekspor tabel/gambar jurnal → ekspor SQL → ringkasan di terminal. Berhenti bila gate kualitas data gagal.
7. **Skrip ekspor tabel jurnal** ke `docs/tabel_jurnal/`: profil dataset, distribusi kategori, K vs CV, metrik, perbandingan baseline, metrik keramaian; gambar PNG 300 dpi berlabel bahasa Indonesia.
8. **Mode sumber data** di konfigurasi: model publik (Maven) vs model data sistem sendiri; sumber dicatat di log.

---

## FASE 8 — PERUBAHAN VISUAL (TEMA COFFEE SHOP)

Ubah HANYA presentasi, bukan logika. Audit dulu view/CSS/JS (Fase 0f) dan daftar file yang akan diubah.

1. Palet warna sebagai CSS variables di satu berkas tema: espresso #2B1B12, kopi-tua #4A2C1A, karamel #B5763A, krim #F3E9DC, susu #FBF6EE, hijau-daun #4F6F52 (sukses/ramai-sepi), merah-bata #A63D2F (peringatan). Pastikan kontras teks memenuhi WCAG AA (hitung rasio dan perbaiki yang gagal). Mode gelap opsional berlatar espresso.
2. Tipografi: judul serif hangat (Playfair Display atau Lora), isi sans bersih (Inter atau Nunito) dari Google Fonts dengan `font-display: swap` dan fallback sistem.
3. Komponen: kartu menu dengan ikon kategori SVG buatan sendiri/berlisensi bebas (DILARANG memakai logo atau merek kedai kopi nyata), sudut membulat, bayangan lembut, lencana kategori berwarna, tombol utama karamel, tombol sekunder outline, header bertema (cangkir/biji kopi SVG sederhana), footer "Warkop Bahagia".
4. Sprite SVG untuk 9 kategori: Coffee, Tea, Bakery, Drinking Chocolate, Flavours, Coffee beans, Loose Tea, Branded, Packaged Chocolate.
5. Grafik dashboard memakai palet tema, warna kategorikal konsisten antar grafik (maks 6–8 warna, label langsung bila mungkin), heatmap skala sekuensial krim → espresso, tooltip jelas.
6. Responsif mobile-first: uji lebar 360, 768, 1280 px; tanpa scroll horizontal; target sentuh ≥ 44 px.
7. Aksesibilitas: alt teks, label form, fokus terlihat, status tidak hanya bergantung warna (tambah teks/ikon).
8. Teks Bahasa Indonesia ramah-santai dan konsisten ("Rekomendasi jam ini", "Lagi ramai", "Lagi sepi"); hapus teks sisa tema lama.
9. Screenshot sebelum/sesudah halaman utama (pelanggan: beranda/menu/pesan/keranjang; admin: dasbor/analitik/evaluasi model) di `docs/screenshots/`.
10. Jangan mengubah fungsi form, route, nama field, atau validasi. Jalankan seluruh tes fungsional sesudahnya.

---

## FASE 9 — DOKUMENTASI

1. `README.md`: sumber dataset (nama, URL, lisensi, bahwa data simulasi), cara menaruh file di `dataset/`, cara menjalankan `run_all.py`, cara mengimpor SQL Plan B, cara menjalankan Flask dan CI4, angka evaluasi HASIL RUN NYATA (bukan angka lama).
2. `docs/dataset_planB.md`: provenance, kolom, jumlah baris, tahapan pengujian kualitas, pra-pemrosesan langkah demi langkah, definisi segmen, alasan pemilihan target dan fitur, batasan (data fiktif New York, tidak ada konteks warkop Indonesia, kelas tidak seimbang, tidak ada ID pelanggan), catatan etika/lisensi.
3. `docs/perubahan_planB.md`: daftar file ditambah/diubah/dipindahkan/dihapus beserta alasan.
4. `docs/keputusan_planB.md`: keputusan desain dan asumsi (kurs, pemetaan kategori, definisi segmen, store_id, target).
5. `requirements.txt` dengan versi di-pin (scikit-learn, pandas, numpy, openpyxl, flask, joblib, matplotlib, scipy, pytest).
6. Aturan git untuk dataset: jelaskan apakah file mentah ikut di-commit (±9 MB; putuskan dan dokumentasikan, mis. `.gitignore` + instruksi unduh).

---

## FASE 10 — VERIFIKASI (WAJIB, DENGAN BUKTI)

Sajikan bukti untuk tiap poin:
- Jumlah baris/kolom sebenarnya, rentang tanggal, hasil semua pengujian kualitas data (termasuk "0 missing value" atau rincian bila ada), status "DATA LAYAK: YA".
- Rekonsiliasi baris: awal = bersih + dibuang.
- Daftar fitur akhir dan bukti scaler/encoder hanya di-fit pada data latih (tanpa kebocoran).
- Tabel metrik Tugas 1 (dengan dua baseline) dan Tugas 2 (MAE/RMSE/R² vs baseline), dan kalimat kesimpulan jujur.
- Hasil chi-square dan Cramér's V.
- Dua kali run identik (hash data bersih, metrik, prediksi).
- Tes unit segmentasi waktu di semua batas (lihat Fase 5.5) dan fungsi pemetaan; tes pytest data quality lulus.
- Tes API Flask (input valid, input salah, model hilang, Flask mati).
- Tes end-to-end CI4→Flask→recommendation_logs pada DB test.
- Impor SQL Plan B berurutan di DB test: sukses, idempoten, rollback berhasil; tidak ada error 1005/1267/1265.
- DB utama tidak berubah (checksum/jumlah baris sebelum dan sesudah).
- Tes fungsional (pesan, bayar, rekomendasi, log) lulus setelah perubahan visual; screenshot dan hasil kontras.
- Tidak ada rahasia yang tercetak di log atau dokumen.
- `python ml/run_all.py` dari kondisi bersih (tanpa artefak lama) berhasil sampai selesai.

---

## FASE 11 — PEMBERSIHAN FILE (HANYA SETELAH FASE 10 LULUS SEMUA)

Tujuan: menghapus file tak berguna, duplikat, dan sampah dengan aman.

1. Pengaman: pastikan tag `backup-sebelum-planB` dan ZIP backup masih ada. Lakukan pembersihan di commit terpisah agar bisa di-revert.
2. Inventarisasi dulu (dry-run), tulis `docs/rencana_pembersihan.md` berisi tabel: path, ukuran, kategori (sampah / duplikat / usang / tidak dirujuk), bukti (hasil grep referensi, hash duplikat), keputusan hapus/pertahankan.
3. Kandidat yang BOLEH dihapus bila terbukti tidak dirujuk:
   - Cache dan artefak otomatis: `__pycache__/`, `*.pyc`, `.pytest_cache/`, `.ipynb_checkpoints/`, `.DS_Store`, `Thumbs.db`, `*.log` lama, `*.tmp`, `*~`, berkas `.bak`/`.orig`, `writable/cache` dan `writable/debugbar` (bukan sesi aktif, bukan upload).
   - Peninggalan dataset sintetis lama: `ml/dataset_warkop.csv`, `ml/generate_dataset.py`, `.pkl` lama, `hasil_evaluasi.txt` lama (sudah digantikan; tetap ada di tag backup).
   - Berkas duplikat (hash identik) dan salinan "copy"/"final"/"revisi"/"baru" yang terbukti sama atau usang.
   - Dump SQL usang yang sudah digantikan dan terdokumentasi (mis. `db_mysql.sql` lama) HANYA bila README dan dokumen menyatakan penggantinya dan tidak ada kode yang merujuk; jika ragu, pertahankan.
   - Model/`StockLogModel.php` dan kode mati lain HANYA bila terbukti tidak dipakai (grep seluruh `app/`, rute, view, test) dan tabelnya sudah tidak ada di skema.
4. DILARANG dihapus: `.env` dan konfigurasi rahasia, `vendor/` (kelola lewat composer), seluruh migrasi lama, `app/`, `public/` yang dipakai, `dataset/` (file mentah), model dan metadata terbaru, `database/planB/`, `docs/`, `backup/`, upload pengguna, folder `.git`.
5. Aturan penghapusan: hanya yang tertulis "hapus" di rencana dan terbukti aman. Jika ragu → pertahankan dan catat.
6. Setelah menghapus: jalankan ulang seluruh tes Fase 10 (minimal: `run_all.py`, pytest, tes API, tes end-to-end, impor SQL di DB test) dan tunjukkan semua lulus. Jika ada yang gagal, kembalikan file (git revert/restore dari backup) dan catat.
7. Tulis `docs/laporan_pembersihan.md`: daftar file dihapus, total ruang dibebaskan, ukuran folder sebelum/sesudah, bukti tes lulus.

---

## LAPORAN AKHIR

Ringkas dan jujur:
1. Apa yang diubah/ditambah/dihapus (daftar file).
2. Hasil pengujian kualitas data (missing value, duplikat, outlier, dll.) dan keputusan pembersihan.
3. Angka evaluasi nyata (Tugas 1 dengan baseline, Tugas 2) dan kesimpulan jujur tentang sinyal waktu pada dataset.
4. File SQL baru dan urutan impornya; perubahan ERD/LRS.
5. Asumsi yang dibuat (kurs, pemetaan kategori, definisi segmen, store_id, target).
6. Batasan penelitian yang jujur (data simulasi AS, tidak ada ID pelanggan, komposisi menu invarian waktu).
7. Langkah manual yang tersisa untuk user (mis. memperbarui abstrak/README dengan angka baru, memperbarui gambar ERD/LRS, mengimpor SQL ke DB utama setelah backup).

CATATAN UNTUK JURNAL: akurasi KNN untuk rekomendasi kategori kemungkinan tidak melampaui baseline, dan itu normal serta jujur. Nilai ilmiah sistem ada pada pipeline yang transparan, pengujian kualitas data yang lengkap, dan prediksi keramaian yang memiliki sinyal waktu nyata. Jangan menyetel ulang parameter atau mengubah split demi angka tinggi.
