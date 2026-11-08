# PROMPT AI AGENT — MEMBUAT FOLDER TAMBAHAN MATA KULIAH (MACHINE LEARNING & KECERDASAN BUATAN)

Salin seluruh isi di bawah garis ini ke AI agent. Sebelum menjalankan, ISI bagian "KETENTUAN RESMI MATA KULIAH" (Bagian B) dengan teks asli dari dosen (RPS, soal tugas, format pengumpulan). Jika dibiarkan kosong, agent wajib memakai struktur default di Bagian D dan menandainya jelas sebagai ASUMSI.

---

## A. PERAN DAN TUJUAN

Kamu adalah engineer dan technical writer senior (Python, scikit-learn, CodeIgniter 4, dokumentasi akademik). Tugasmu: menambahkan dua folder pengumpulan mata kuliah ke repo `lower-manhattan-coffee-shop`, yaitu **Machine Learning** dan **Kecerdasan Buatan**, yang berisi laporan, bukti, dan penunjuk ke berkas proyek yang sudah ada, SESUAI ketentuan mata kuliah.

Konteks proyek (jangan diubah): aplikasi pemesanan meja berbasis QR + POS kasir (CodeIgniter 4, MariaDB), layanan Flask di port 5001, dataset publik Maven "Coffee Shop Sales" (simulasi) difilter ke cabang Lower Manhattan (store_id=5, 47.782 transaksi, Jan–Jun 2023). Dua model KNN: klasifikasi kategori produk dan regresi jumlah transaksi per jam. Judul penelitian: "Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu Menggunakan K-Nearest Neighbor". Kelompok: Yusuf Herfa Auliansyah (15240075), Yusuf Mansur (15240047), Rizqi Nuzul Firman (15240116), kelas 15.5A.05, UBSI Kaliabang.

## ATURAN KERAS

1. Folder baru bersifat TAMBAHAN. JANGAN mengubah, memindahkan, atau menghapus kode aplikasi (`app/`, `public/`, `ml/`, `database/`, `dataset/`, `tests/`). Satu-satunya berkas lama yang boleh diubah: `README.md` (hanya menambah tautan ke folder baru) dan `.gitignore` (hanya bila perlu).
2. Sebelum mengubah apa pun, buat branch `matkul-ml-kb` dan tag `backup-sebelum-matkul`. Tampilkan bukti.
3. JANGAN menyentuh database utama. Tidak perlu koneksi DB sama sekali; semua bukti dari berkas hasil yang sudah ada atau dari data di `dataset/`.
4. JANGAN menampilkan isi `.env`, password, hash, token, atau kunci.
5. JANGAN menduplikasi berkas besar (model `.pkl`, dataset `.xlsx`, `vendor/`). Rujuk lewat path relatif dan tulis cara membuatnya ulang. Hindari symlink (tidak portabel di Windows).
6. Semua angka di laporan harus berasal dari berkas hasil proyek (`ml/hasil_evaluasi.json`, `ml/metadata.json`, `docs/laporan_kualitas_data.md`) atau dari skrip yang kamu jalankan sendiri. Jangan mengarang angka. Jika berkas hasil tidak ada, jalankan ulang pipeline di salinan sementara atau berhenti dan laporkan.
7. Jujur soal hasil: akurasi KNN klasifikasi (34,89%) di bawah baseline mayoritas (38,10%); Top-3 Hit Rate 78,83% BELUM dibandingkan baseline top-3 dan baseline sederhana (tiga kategori terpopuler) sekitar 83,7% pada cabang ini. Jangan menyajikan 78,83% sebagai keunggulan. Hitung baseline top-3 sendiri di notebook/skrip folder matkul (tanpa mengubah `ml/`) dan laporkan hasilnya.
8. Dataset adalah data publik SIMULASI (Maven Analytics). Tulis jelas di setiap laporan; jangan menyebutnya data operasional nyata.
9. Jangan mengklaim fitur yang tidak ada. Verifikasi setiap klaim sistem terhadap kode (route, controller, tabel) sebelum menuliskannya.
10. Jangan menerima status "selesai" tanpa bukti (output perintah, berkas yang benar-benar ada, hasil tes).

---

## B. KETENTUAN RESMI MATA KULIAH (ISI DULU SEBELUM DIJALANKAN)

```
[TEMPEL DI SINI: ketentuan/RPS/soal tugas Machine Learning — struktur folder, nama berkas, format laporan,
 komponen penilaian, batas halaman, bahasa, format pengumpulan (zip/GitHub/LMS), tenggat, dan sebagainya]

[TEMPEL DI SINI: ketentuan/RPS/soal tugas Kecerdasan Buatan — hal yang sama]
```

Aturan prioritas: (1) ketentuan di Bagian B menang atas segalanya; (2) jika Bagian B kosong atau tidak lengkap, pakai Bagian D sebagai default dan catat setiap butir yang berasal dari asumsi di `docs/matkul/ASUMSI.md`; (3) jika ketentuan bertentangan dengan aturan keras di atas (mis. meminta mengubah kode), berhenti dan laporkan konflik, jangan memutuskan sendiri.

---

## C. FASE 0 — AUDIT (READ-ONLY)

1. Pembuatan branch/tag pengaman (Aturan 2) dan bukti.
2. Petakan repo: `app/` (routes, controllers rekomendasi/laporan, libraries klien Flask, migrasi, seeder), `ml/` (skrip, model, hasil, tes, config), `database/` (schema, SQL planB), `dataset/`, `docs/`, `tests/`. Catat jumlah berkas dan ukuran.
3. Verifikasi fakta yang akan dikutip, dengan bukti (perintah/keluaran singkat): jumlah baris dataset per cabang, nilai K terpilih, metrik evaluasi, ambang keramaian (Sepi/Sedang/Ramai), definisi segmen waktu, endpoint Flask, fallback rekomendasi di CI4, jumlah produk dan kategori di seeder.
4. Periksa konsistensi antar dokumen (README, `ml/hasil_evaluasi.txt`, `ml/metadata.json`, `docs/*`) dan catat setiap ketidakcocokan di `docs/matkul/temuan_audit.md` (jangan memperbaikinya di berkas lama; hanya laporkan).
5. Temukan berkas basi/duplikat (mis. `ml/dataset_warkop.csv`, `ml/generate_dataset.py`, `ml/model_knn.joblib`, salinan dataset di `database/`) dan CATAT saja; jangan dihapus pada tugas ini.
6. Tentukan apakah lingkungan Python dapat menjalankan pipeline (versi paket di `requirements.txt` vs terpasang). Jika tidak, buat venv sementara di luar repo.
7. Keluaran: `docs/matkul/audit_matkul.md`. Berhenti dan tampilkan ringkasan bila ada kontradiksi penghalang.

---

## D. FASE 1 — STRUKTUR FOLDER

Gunakan struktur ini kecuali Bagian B menetapkan lain. Nama folder tanpa spasi.

```
matkul/
├── README.md                      # indeks: tujuan, siapa mengerjakan apa, peta berkas, cara reproduksi
├── machine-learning/
│   ├── README.md                  # ringkasan, cara membaca folder
│   ├── 01_definisi_masalah.md
│   ├── 02_dataset_dan_provenance.md
│   ├── 03_kualitas_data_dan_eda.md
│   ├── 04_praproses_dan_fitur.md
│   ├── 05_pemodelan_knn.md
│   ├── 06_evaluasi_dan_baseline.md
│   ├── 07_deployment_flask.md
│   ├── 08_kesimpulan_dan_keterbatasan.md
│   ├── notebooks/
│   │   ├── 01_eda_kualitas_data.ipynb
│   │   ├── 02_praproses_dan_split.ipynb
│   │   ├── 03_knn_klasifikasi.ipynb
│   │   ├── 04_knn_regresi_keramaian.ipynb
│   │   └── 05_baseline_dan_top3.ipynb
│   ├── gambar/                    # PNG hasil (salinan ringan atau dibangkitkan ulang)
│   ├── tabel/                     # CSV/MD tabel hasil
│   └── bukti/                     # log run, screenshot, hash dataset
├── kecerdasan-buatan/
│   ├── README.md
│   ├── 01_deskripsi_sistem_cerdas.md
│   ├── 02_representasi_pengetahuan.md
│   ├── 03_metode_ai_knn_dan_aturan.md
│   ├── 04_alur_inferensi.md       # diagram Mermaid + penjelasan langkah
│   ├── 05_pengujian_sistem.md
│   ├── 06_analisis_etika_dan_keterbatasan.md
│   ├── diagram/                   # .mmd + .png (alur inferensi, arsitektur, state fallback)
│   ├── skenario_uji/              # tabel skenario input → output yang diharapkan + hasil
│   └── bukti/                     # screenshot widget rekomendasi, respons API (tanpa data sensitif)
└── laporan/
    ├── Laporan_Machine_Learning.md    # gabungan siap diubah ke PDF/Word
    └── Laporan_Kecerdasan_Buatan.md
```

Catatan: jika Bagian B meminta nama/urutan berkas lain, ikuti dan sediakan tabel pemetaan "ketentuan → berkas" di `matkul/README.md`.

---

## E. FASE 2 — ISI FOLDER MACHINE LEARNING

Setiap dokumen berbahasa Indonesia baku, ringkas, bernomor, dengan tabel dan gambar bila perlu. Setiap angka disertai sumbernya (nama berkas atau skrip) di catatan kaki atau kolom "Sumber".

1. `01_definisi_masalah.md`: latar singkat; dua tugas (klasifikasi kategori produk, regresi jumlah transaksi per jam) beserta input/output, tujuan, metrik, dan kriteria berhasil yang diukur terhadap baseline.
2. `02_dataset_dan_provenance.md`: nama, penerbit, URL, lisensi (verifikasi ulang di halaman sumber), sifat simulasi, struktur 11 kolom, jumlah baris total (149.116) dan cabang (47.782), rentang tanggal, filter Lower Manhattan beserta alasannya, hash SHA-256 berkas, batasan (tidak ada ID pelanggan/keranjang, data New York bukan Indonesia).
3. `03_kualitas_data_dan_eda.md`: ringkas hasil pengujian di `docs/laporan_kualitas_data.md` (missing value, duplikat, rentang, outlier, konsistensi, celah ID, jam operasional), distribusi kategori, pola jam×hari, tren bulanan, uji chi-square dan Cramér's V. Grafik dari `ml/` atau dibangkitkan ulang oleh notebook.
4. `04_praproses_dan_fitur.md`: fitur (jam, hari_minggu, is_weekend, segmen waktu), segmen 06.00–10.59/11.00–14.59/15.00–17.59/18.00–20.59, penskalaan, split 80:20 stratified (random_state=42) dan split temporal (Jan–Mei/Jun), pencegahan kebocoran data, alasan `store_id` tidak dipakai.
5. `05_pemodelan_knn.md`: KNN klasifikasi dan regresi, jarak Euclidean, pemilihan K lewat cross-validation (K=20 dan K=17 menurut hasil proyek; verifikasi), grafik K vs skor, parameter dan seed, versi pustaka.
6. `06_evaluasi_dan_baseline.md`: tabel metrik klasifikasi (akurasi, macro/weighted P/R/F1, confusion matrix, per kelas), baseline mayoritas dan mode per segmen, split temporal, **top-3 hit rate beserta baseline top-3 yang dihitung sendiri**, tabel metrik regresi (MAE, RMSE, R²) vs baseline rata-rata per jam, ambang kuantil keramaian. Tulis kesimpulan jujur: KNN tidak mengungguli baseline pada klasifikasi kategori; sinyal waktu nyata ada pada volume transaksi.
7. `07_deployment_flask.md`: arsitektur CI4 ↔ Flask, endpoint (`/health`, `/model-info`, `/api/rekomendasi`, `/predict-crowd`), contoh permintaan/respons (data contoh, tanpa rahasia), fallback, CORS lokal.
8. `08_kesimpulan_dan_keterbatasan.md`: kesimpulan, keterbatasan (simulasi, invarian waktu pada komposisi menu, ketiadaan ID pelanggan), dan saran (data transaksi nyata toko, fitur tambahan).
9. Notebook (`.ipynb`): reproduksi langkah dari berkas di `dataset/` dan `ml/` dengan seed tetap, sel berurutan, keluaran tersimpan, penjelasan Markdown di antara kode. Notebook membaca path relatif dari root repo dan TIDAK menimpa artefak `ml/`; hasil tulis ke `matkul/machine-learning/` saja. Notebook 05 menghitung baseline top-3 dan membandingkannya dengan KNN.
10. `tabel/` dan `gambar/`: CSV/MD dan PNG 300 dpi berlabel bahasa Indonesia (profil dataset, distribusi, K vs CV, confusion matrix, prediksi vs aktual keramaian, perbandingan metode).
11. `bukti/`: keluaran run (`jalankan_ulang.log`), hash dataset, versi lingkungan, tanggal run.

---

## F. FASE 3 — ISI FOLDER KECERDASAN BUATAN

1. `01_deskripsi_sistem_cerdas.md`: sistem sebagai agen: persepsi (waktu realtime Asia/Jakarta, tanpa data pelanggan), pengetahuan (pola historis dataset), penalaran (KNN + aturan), aksi (menampilkan rekomendasi dan label keramaian), lingkungan (toko, pelanggan, kasir). Sebut lingkup dan non-lingkup (tanpa gateway pembayaran, bukan personalisasi per pelanggan).
2. `02_representasi_pengetahuan.md`: segmen waktu, kategori (9), ambang Sepi/Sedang/Ramai, tabel agregat `agg_keramaian_jam` dan `agg_kategori_segmen` (struktur dan kegunaan, verifikasi ke skema), pemetaan kategori→menu.
3. `03_metode_ai_knn_dan_aturan.md`: KNN sebagai pembelajaran berbasis instans (rumus jarak Euclidean, voting/rata-rata tetangga), alasan memilih KNN, perbandingan singkat dengan alternatif (tanpa mengklaim hasil percobaan yang tidak dilakukan), peran aturan/fallback dan kapan aktif (layanan mati, confidence rendah, di luar jam operasional).
4. `04_alur_inferensi.md`: diagram Mermaid (juga PNG): waktu masuk → validasi → fitur → model → peringkat kategori → pilih menu → label keramaian → tampil di widget; cabang fallback. Penjelasan langkah per langkah, contoh numerik satu kasus (mis. Senin 08.30) dengan keluaran nyata dari panggilan lokal.
5. `05_pengujian_sistem.md` + `skenario_uji/`: tabel skenario (id, input, kondisi, hasil diharapkan, hasil aktual, lulus/gagal) minimal: tiap segmen waktu, batas 10.59/11.00, 14.59/15.00, 17.59/18.00, 20.59/21.00, di luar jam operasional, weekend vs weekday, Flask mati, input jam tidak valid, model hilang. Jalankan uji nyata (tes yang sudah ada di `ml/tests/` dan `tests/` bila bisa; skrip tambahan hanya di folder matkul) dan lampirkan keluaran. Jika sebuah skenario tidak bisa dijalankan di lingkunganmu, tandai "tidak dijalankan" beserta alasan.
6. `06_analisis_etika_dan_keterbatasan.md`: data simulasi, bias dataset (New York), privasi (log rekomendasi tanpa identitas), risiko menyesatkan bila rekomendasi dianggap personal, transparansi (pesan sumber data di UI), batas klaim akurasi.
7. `diagram/`: arsitektur sistem, alur inferensi, diagram keadaan fallback; berkas `.mmd` sumber dan `.png` render.
8. `bukti/`: screenshot widget rekomendasi dan halaman evaluasi (jika aplikasi bisa dijalankan; jika tidak, tandai tidak tersedia dan jangan membuat tampilan palsu), contoh respons API.

---

## G. FASE 4 — LAPORAN GABUNGAN & INDEKS

1. `laporan/Laporan_Machine_Learning.md` dan `laporan/Laporan_Kecerdasan_Buatan.md`: gabungan berurutan dari dokumen bernomor dengan halaman judul (judul penelitian, nama kelompok dan NIM, kelas, kampus, dosen pengampu: tulis `[ISI NAMA DOSEN]` bila tidak diketahui), daftar isi, daftar gambar/tabel, dan daftar pustaka (hanya yang benar-benar dikutip; gunakan 20 referensi jurnal proyek yang relevan; jangan menambah referensi yang tidak bisa diverifikasi).
2. `matkul/README.md`: tujuan, peta berkas, tabel keterlacakan "ketentuan → berkas bukti", pembagian tugas anggota (isi placeholder `[ISI]` bila tidak diketahui; jangan mengarang), cara reproduksi (perintah persis, urutan, versi Python), cara mengekspor laporan ke PDF/Word.
3. Tambahkan tautan ke `matkul/README.md` di `README.md` utama (satu baris).
4. `docs/matkul/ASUMSI.md`: semua asumsi dan butir default yang dipakai karena Bagian B tidak mencakupnya.

---

## H. FASE 5 — VERIFIKASI (WAJIB, DENGAN BUKTI)

- Semua berkas pada struktur ada dan tidak kosong; daftar pohon folder ditampilkan.
- Setiap angka di laporan cocok dengan sumbernya (jalankan skrip pemeriksa `matkul/cek_konsistensi.py` yang membandingkan angka kunci di Markdown dengan `ml/hasil_evaluasi.json` dan `ml/metadata.json`; tampilkan hasilnya).
- Notebook berjalan dari awal sampai akhir tanpa galat (`jupyter nbconvert --execute`) dan hasilnya cocok dengan `ml/hasil_evaluasi.json` (toleransi dijelaskan); dua run identik.
- Baseline top-3 dihitung dan dilaporkan.
- Semua tautan relatif antar dokumen valid (skrip pemeriksa tautan).
- Diagram Mermaid dapat dirender; PNG ada.
- Bukti tidak ada rahasia di folder baru (pencarian pola password/token/`.env`), dan tidak ada berkas besar baru (> 5 MB) selain yang dibenarkan.
- Berkas lama tidak berubah kecuali yang diizinkan: tampilkan `git status` dan `git diff --stat`.
- Penutupan: bukti bahwa ketentuan di Bagian B terpenuhi satu per satu (tabel centang dengan rujukan berkas).

---

## I. LAPORAN AKHIR

Ringkas dan jujur: (1) pohon folder akhir, (2) daftar ketentuan terpenuhi/belum/asumsi, (3) angka kunci dan sumbernya, (4) temuan audit (ketidakcocokan antar dokumen, berkas basi) untuk ditindaklanjuti pengguna, (5) langkah manual tersisa (isi nama dosen, pembagian tugas, ekspor PDF, screenshot yang butuh aplikasi berjalan), (6) peringatan bahwa Top-3 78,83% di dokumen lama tidak boleh dikutip sebagai keunggulan.
