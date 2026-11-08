# Alur Machine Learning dan Letak Label Dataset

Dokumen ini menjelaskan tujuan, sumber label, preprocessing, eksperimen, evaluasi, dan hubungan hasil machine learning dengan aplikasi Lower Manhattan Coffee Shop. Angka hasil di bawah berasal dari artefak eksperimen proyek yang tercatat pada `ml/metadata.json` dan laporan evaluasi, bukan hasil eksperimen baru.

## 1. Tujuan proyek

### Tujuan umum

Mempelajari pola transaksi dataset simulasi coffee shop dan membuat prototipe yang dapat:

1. Memperkirakan kategori produk yang cocok dengan konteks waktu.
2. Memperkirakan jumlah transaksi per jam sebagai indikator keramaian.
3. Menyajikan rekomendasi menu dan estimasi keramaian melalui API aplikasi.

### Tujuan khusus

- Mengidentifikasi label yang sudah tersedia pada dataset, bukan mengarang label produk baru.
- Menyiapkan subset dan fitur yang relevan untuk dua masalah berbeda: klasifikasi kategori dan regresi jumlah transaksi.
- Membandingkan model dengan baseline sederhana agar kinerja model tidak dinilai dari angkanya sendiri saja.
- Menjelaskan keterbatasan dataset dan evaluasi, terutama ketidakseimbangan label dan sifat waktu pada data.

## 2. Dataset dan lokasi kolom label

Sumber yang dipakai dalam eksperimen adalah **Coffee Shop Sales (Maven Roasters)** dari Maven Analytics Data Playground. Dataset bersifat simulasi untuk pembelajaran, bukan rekaman operasional aktual Lower Manhattan.

### Label klasifikasi yang sudah ada

Label utama **sudah tersedia sebagai kolom `product_category` pada setiap baris transaksi**. Program tidak membuat label kategori ini dari nol.

- Pada berkas mentah yang diharapkan pipeline: `dataset/Coffee Shop Sales.xlsx`, sheet `Transactions`.
- Pada data setelah lolos pemeriksaan kualitas: `ml/data/interim/transaksi_valid.csv`.
- Pada hasil preprocessing: `ml/data/processed/dataset_bersih.csv`.
- Pemakaian sebagai target model: `y_cat_str = df['product_category']` di `ml/train_evaluate.py`.
- Program mengubah label teks menjadi kode angka untuk pelatihan menggunakan `LabelEncoder`; daftar nama kelas aslinya disimpan dalam metadata dan artefak model.

Sembilan nilai kategori yang ada:

| Nilai `product_category` | Jumlah baris Lower Manhattan |
|---|---:|
| Coffee | 18.204 |
| Tea | 13.912 |
| Bakery | 7.890 |
| Drinking Chocolate | 3.405 |
| Flavours | 2.930 |
| Coffee beans | 531 |
| Loose Tea | 381 |
| Branded | 349 |
| Packaged Chocolate | 180 |
| **Total** | **47.782** |

Artinya label tidak seimbang. Kelas terbanyak adalah Coffee (sekitar 38,1%); kelas tersedikit Packaged Chocolate (180 baris). Oleh sebab itu, akurasi saja tidak cukup untuk menyimpulkan model bagus.

### Label lain yang tersedia

- `product_type` adalah label teks pada dataset, dengan 29 kelas.
- `product_detail` adalah label teks pada dataset, dengan 80 nilai detail.

Kedua kolom ini **belum menjadi model klasifikasi yang dilatih dan diuji di pipeline saat ini**. Pada Tugas 3, program baru mengukur baseline kelas mayoritas masing-masing, bukan membangun model final.

### Label regresi dan label keramaian turunan

Untuk memprediksi keramaian, program membuat target numerik baru:

1. Mengelompokkan baris menurut tanggal dan jam.
2. Menghitung banyaknya `transaction_id` pada setiap kelompok sebagai `total_transaksi`.
3. Memakai hitungan itu sebagai target regresi untuk memperkirakan jumlah transaksi pada satu jam.

Kemudian target numerik ini dibagi menjadi tiga label deskriptif menggunakan kuartil data latih:

- **Sepi**: jumlah transaksi `<= 11`.
- **Sedang**: `> 11` dan `<= 28`.
- **Ramai**: `> 28`.

Jadi, `sepi/sedang/ramai` **bukan kolom label asli dataset**. Label tersebut diturunkan dari `total_transaksi` per jam dan digunakan aplikasi untuk menampilkan kelas keramaian dari hasil prediksi regresi.

## 3. Alur pemrosesan yang ada dalam program

Pipeline utama di `ml/run_all.py` berurutan seperti ini:

### Tahap 1 — Data quality gate

`ml/data_quality.py` memeriksa keberadaan workbook, membaca sheet `Transactions`, memeriksa struktur kolom dan kualitas data, lalu membatasi analisis ke `store_location = 'Lower Manhattan'` (`store_id = 5`).

Profil hasil yang tersimpan di `ml/data/profil_dataset.json` mencatat:

- Data mentah: 149.116 baris.
- Subset Lower Manhattan: 47.782 baris.
- Baris dari lokasi lain yang dikecualikan: 101.334.
- Baris Lower Manhattan yang dibuang sebagai data tidak valid: 0.
- Rentang tanggal: 1 Januari–30 Juni 2023, 181 hari.
- Rentang transaksi: jam 06 sampai 20.
- Nilai kosong dan duplikasi penuh: dilaporkan tidak ditemukan.

Baris dari cabang lain dikecualikan karena ruang lingkup penelitian, bukan karena datanya rusak. Outlier harga/kuantitas yang dianggap nilai transaksi sah dipertahankan menurut laporan kualitas data.

### Tahap 2 — Preprocessing dan feature engineering

`ml/prepare_dataset.py` membaca data interim dan membentuk beberapa fitur:

- `hari_minggu`: 0=Senin sampai 6=Minggu.
- `is_weekend`: 1 untuk Sabtu/Minggu, selain itu 0.
- `bulan`: bulan transaksi.
- `segmen_waktu`: pagi, siang, sore, malam, atau di luar jam operasional.
- `jam_sin` dan `jam_cos`: merepresentasikan jam sebagai waktu siklis.
- `ukuran` dan `nama_bersih`: diekstrak dari akhiran ukuran seperti Sm/Rg/Lg pada `product_detail`.
- `harga_katalog`: modus harga historis per `product_id`.

Hasil preprocessing ditulis ke `ml/data/processed/dataset_bersih.csv`. Label asli seperti `product_category`, `product_type`, dan `product_detail` tetap tersedia di berkas hasil tersebut.

### Tahap 3 — Eksperimen klasifikasi kategori

Di `ml/train_evaluate.py`, fitur masukan untuk Tugas 1 adalah:

```text
jam, hari_minggu, is_weekend, jam_sin, jam_cos
```

Targetnya:

```text
product_category
```

Program menggunakan `StandardScaler` diikuti K-Nearest Neighbors (KNN) classifier dengan jarak Euclidean. Data dibagi stratified 80:20 dengan `random_state=42`; pemilihan tetangga mencoba K=1 sampai 20 menggunakan 5-fold stratified cross-validation pada bagian training. Nilai K yang tersimpan sebagai pilihan adalah **K=20**.

Pembandingnya adalah:

- Baseline kelas mayoritas.
- Baseline kategori yang paling sering pada segmen waktu.

Evaluasi mencakup akurasi, macro/weighted precision-recall-F1, Top-3 hit rate, confusion matrix, dan pemeriksaan split waktu Januari–Mei untuk training serta Juni untuk pengujian.

### Tahap 4 — Eksperimen regresi jumlah transaksi

Untuk Tugas 2, data transaksi diagregasi per tanggal dan jam. Fitur masukan:

```text
jam, hari_minggu, is_weekend, bulan, jam_sin, jam_cos
```

Target numeriknya adalah `total_transaksi` pada kelompok tanggal-jam. Modelnya `StandardScaler` + KNN Regressor jarak Euclidean. K=1 sampai 20 dicoba; hasil tersimpan memilih **K=17**. Pembanding regresi adalah rata-rata transaksi per jam yang dihitung dari data training.

Metrik regresi: MAE, RMSE, dan R². Setelah regresi memprediksi jumlah transaksi, program mengubah nilai tersebut menjadi label sepi/sedang/ramai berdasarkan batas kuartil.

### Tahap 5 — Tugas eksploratif dan artefak

Program menghitung baseline mayoritas untuk `product_type` dan `product_detail`, tetapi belum melatih model klasifikasi untuk keduanya. Model dan laporannya disimpan pada:

- `ml/model_rekomendasi.pkl`
- `ml/model_keramaian.pkl`
- `ml/metadata.json`
- `ml/hasil_evaluasi.json`
- `ml/hasil_evaluasi.txt`
- `docs/tabel_jurnal/` untuk tabel dan grafik evaluasi

`ml/predict.py` memuat model untuk inferensi rekomendasi dan keramaian. Rekomendasi menu final menggabungkan probabilitas kategori dari KNN dengan popularitas produk per segmen waktu; ini bukan personalisasi pelanggan karena dataset tidak memiliki ID pelanggan.

## 4. Hasil evaluasi yang tercatat

### Tugas 1 — klasifikasi kategori

| Evaluasi | Hasil |
|---|---:|
| KNN, K=20, akurasi stratified 80:20 | 34,89% |
| Baseline kelas mayoritas | 38,10% |
| Macro precision | 9,79% |
| Macro recall | 11,12% |
| Macro F1 | 9,30% |
| Weighted F1 | 28,02% |
| Top-3 hit rate | 78,83% |
| Akurasi split waktu Jan–Mei / Juni | 34,61% |

Kesimpulan yang tepat: berdasarkan akurasi, KNN kategori **belum mengungguli baseline mayoritas**. Top-3 hit rate menunjukkan kelas sebenarnya sering ada di tiga prediksi teratas, tetapi harus dibandingkan dengan baseline Top-3 yang sepadan sebelum dipakai sebagai bukti peningkatan. Macro F1 yang rendah memperlihatkan model belum merata dalam mengenali semua kelas.

### Tugas 2 — regresi jumlah transaksi

| Evaluasi | KNN regresi, K=17 | Baseline rata-rata per jam |
|---|---:|---:|
| MAE | 6,12 | 7,42 |
| RMSE | 8,16 | 10,09 |
| R² | 0,6191 | 0,4173 |

Pada evaluasi yang tersimpan, model regresi lebih baik dari baseline untuk ketiga metrik tersebut. Namun, evaluasi utama yang tercatat memakai pembagian acak, bukan validasi temporal murni; hasil ini belum cukup untuk menyatakan kinerja prediksi masa depan.

### Temuan analisis data

Profil dataset mencatat asosiasi kategori dan segmen waktu sangat lemah (Cramér's V sekitar 0,0335), sedangkan jumlah transaksi per jam berbeda menurut jam dalam uji Kruskal–Wallis. Ini mendukung pemisahan tugas: komposisi kategori sulit dijelaskan hanya dengan fitur waktu, sementara volume transaksi tampak punya sinyal waktu.

## 5. Catatan metodologi yang penting untuk disampaikan

Hasil ini adalah hasil proyek yang tercatat, tetapi beberapa hal perlu diperbaiki atau diberi batasan sebelum disebut evaluasi final:

1. Pada cross-validation klasifikasi, `StandardScaler` difit ke seluruh training sebelum data training dibagi menjadi fold. Agar tidak terjadi kebocoran informasi antar-fold, scaler sebaiknya menjadi bagian dari `Pipeline` yang difit ulang pada setiap fold.
2. Baseline kategori per segmen dibentuk dari seluruh dataframe, termasuk data uji. Baseline harus dipelajari dari training saja.
3. Pemilihan K regresi pada kode memakai `KFold(shuffle=True)`, bukan `TimeSeriesSplit`, walau `TimeSeriesSplit` diimpor dan disebut dalam komentar/header.
4. Evaluasi 80:20 acak dapat menempatkan tanggal dekat dari periode yang sama pada training dan test. Untuk klaim prediksi ke depan, jadikan split kronologis atau time-series validation sebagai evaluasi utama.
5. Agregasi jumlah transaksi hanya mencakup pasangan tanggal-jam yang muncul pada baris data. Kode belum secara eksplisit menambahkan kombinasi tanggal-jam tanpa transaksi sebagai observasi bernilai nol.
6. Untuk kelas tidak seimbang, selalu laporkan confusion matrix, precision/recall/F1 per kelas, macro F1 dan baseline. Jangan menyimpulkan model bagus dari akurasi atau Top-3 saja.
7. Tugas 3 belum memiliki eksperimen model. Jika ingin mengklaim `product_type` atau `product_detail` sebagai model, tambahkan pelatihan, validasi, dan evaluasinya.

## 6. Menjalankan pipeline proyek

Alur yang dimaksud oleh `ml/run_all.py`:

```text
dataset mentah (xlsx)
  -> ml/data_quality.py
  -> ml/prepare_dataset.py
  -> ml/train_evaluate.py
  -> ml/export_sql.py
  -> pytest untuk pemeriksaan data dan segmentasi waktu
```

Perintah:

```powershell
python ml/run_all.py
```

Catatan operasional: skrip `ml/data_quality.py` dan `ml/run_all.py` secara spesifik mengharapkan file **`dataset/Coffee Shop Sales.xlsx`** dengan sheet `Transactions`. Berkas CSV saja tidak memenuhi jalur input tersebut tanpa mengubah skrip atau mengekspor ulang workbook sesuai format yang diharapkan.

## 7. Memenuhi arahan dosen dengan Google Colab

Kode model yang sudah ada adalah Python/scikit-learn. Dari opsi RapidMiner, Google Colab, atau Weka, Google Colab adalah jalur yang paling langsung untuk mereproduksi pendekatan tersebut tanpa mengganti metodologi ke aplikasi lain. Namun, artefak proyek saat ini **tidak membuktikan bahwa eksperimen sebelumnya dijalankan di Colab, RapidMiner, atau Weka**.

Alur laporan/eksperimen yang bisa dibawa ke Colab:

1. Unggah file sumber yang sesuai dan catat nama dataset, sumber, lisensi/provenance, kolom, serta ruang lingkup Lower Manhattan.
2. Tunjukkan kolom label `product_category` di data mentah dan distribusi sembilan kelasnya.
3. Lakukan pemeriksaan data (tipe, missing, duplikasi, rentang, distribusi kelas) sebelum preprocessing.
4. Pisahkan fitur dan target secara eksplisit: `X` berisi fitur waktu; `y` berisi `product_category`. Untuk keramaian, agregasikan per tanggal-jam, lalu tetapkan `X` kalender/waktu dan `y=total_transaksi`.
5. Pisahkan training/test secara kronologis untuk evaluasi prediksi masa depan; jangan menghitung scaler, imputasi, atau encoding berdasarkan data test.
6. Pada training saja, jalankan cross-validation dan eksperimen baseline plus beberapa model yang masuk akal. Misalnya KNN dan Decision Tree/Random Forest untuk klasifikasi, serta KNN Regressor dan Random Forest Regressor untuk regresi. Gunakan pipeline agar preprocessing fit di dalam setiap fold.
7. Bandingkan hasil memakai metrik yang sesuai, confusion matrix dan metrik per kelas untuk klasifikasi, serta MAE/RMSE/R² untuk regresi.
8. Simpan notebook, versi library, pembagian data, parameter model, hasil, dan kesimpulan termasuk keterbatasan.

Ini adalah **rencana eksperimen Colab**, bukan klaim bahwa eksperimen tambahan tersebut sudah dijalankan.

## 8. Narasi singkat untuk dosen

> Dataset Coffee Shop Sales dari Maven sudah memiliki label kategori pada kolom `product_category`. Penelitian ini menggunakan subset Lower Manhattan, sebanyak 47.782 baris dari 149.116 baris sumber. Target klasifikasi utamanya adalah sembilan kategori produk, sedangkan fitur masukan yang dipakai adalah jam dan informasi kalender. Program juga membuat tugas regresi: jumlah transaksi per tanggal dan jam diprediksi dari fitur waktu, lalu hasil regresi dikelompokkan menjadi sepi, sedang, dan ramai memakai kuartil data training. Model kategori saat ini memperoleh akurasi 34,89%, di bawah baseline mayoritas 38,10%, jadi belum dapat diklaim lebih baik. Model regresi memperoleh MAE 6,12 dan R² 0,6191, lebih baik dari baseline yang tercatat, tetapi evaluasi temporal dan pencegahan kebocoran preprocessing masih perlu diperkuat. Eksperimen notebook Google Colab dapat dipakai untuk mengulang preprocessing, membandingkan model, dan melaporkan evaluasi secara lebih ketat.
