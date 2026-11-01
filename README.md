# Lower Manhattan Coffee Shop

Aplikasi pemesanan dan kasir berbasis CodeIgniter 4 untuk katalog Maven Roasters, dilengkapi rekomendasi waktu dari transaksi Lower Manhattan.

## Kebutuhan

- PHP beserta ekstensi yang disyaratkan CodeIgniter 4
- Composer
- MySQL atau MariaDB
- Python 3.12.6 (versi lingkungan evaluasi ML yang tercatat)

## Instalasi aplikasi

1. Pasang dependensi PHP: `composer install`.
2. Salin `.env.example` menjadi `.env`, lalu atur environment, URL aplikasi, dan kredensial database. Jangan commit `.env`. Database aplikasi ini terpisah dari `warkop_bahagia`; gunakan `lowermanhattancoffeshop`.
3. Buat database kosong `lowermanhattancoffeshop` di MySQL/MariaDB, terpisah dari `warkop_bahagia`, lalu impor skema bersih:

   ```sh
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS lowermanhattancoffeshop CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
   mysql -u root -p lowermanhattancoffeshop < database/schema.sql
   ```

4. Jalankan migrasi CodeIgniter yang tersedia: `php spark migrate`.
5. Untuk mengisi atau menyinkronkan katalog 80 produk Maven dan agregatnya, jalankan `php spark db:seed KatalogMavenSeeder`.
6. Buat akun admin/kasir sesuai prosedur instalasi lokal; jangan gunakan atau bagikan kredensial bawaan.
7. Jalankan aplikasi: `php spark serve`.

## Menjalankan API rekomendasi

Buka terminal kedua di root proyek. Buat dan aktifkan virtual environment Python 3.12.6, pasang dependensi, lalu jalankan Flask:

```sh
python -m venv .venv
.venv/Scripts/activate
python -m pip install -r requirements.txt
python ml/app_flask.py
```

Di Linux/macOS, gunakan `source .venv/bin/activate`. Flask mendengarkan di `127.0.0.1:5001`. Model dan artefak yang dibutuhkan berada di `ml/`.

## Struktur direktori

- `app/` — konfigurasi, controller, model, migrasi, seeder, dan view CodeIgniter.
- `database/` — skema bersih dan migrasi SQL manual.
- `ml/` — API Flask, prediksi, model KNN, dataset, dan hasil evaluasi.
- `public/` — entry point web, aset statis, dan direktori upload runtime.
- `writable/` — cache, log, sesi, dan berkas runtime CodeIgniter.

## Dataset Publik & Sistem Rekomendasi (Plan B)

Sistem rekomendasi menu menggunakan dataset publik bertema coffee shop:
- **Nama Dataset**: Coffee Shop Sales (Maven Roasters)
- **Sumber**: [Maven Analytics Data Playground](https://mavenanalytics.io/data-playground/coffee-shop-sales) (Public Domain).
- **Ruang lingkup penelitian**: hanya transaksi `Lower Manhattan` (`store_id = 5`); dataset sumber tetap 149.116 baris, subset cabang berisi 47.782 transaksi.
- **Katalog**: 80 produk dalam sembilan kategori: Bakery, Branded, Coffee, Coffee beans, Drinking Chocolate, Flavours, Loose Tea, Packaged Chocolate, dan Tea.
- **Pilihan ukuran**: produk dataset dengan ukuran Small/Regular/Large (misalnya Brazilian) ditampilkan sebagai satu menu dengan pilihan ukuran. Harga, stok, dan ID transaksi tiap ukuran tetap mengikuti baris dataset; ukuran yang tidak tersedia tidak dibuat-buat. Manajemen menu kasir dan laporan stok admin juga menampilkan ukuran-ukuran itu dalam satu kelompok.
- **Logo**: aplikasi memakai file logo Manhattan Coffee yang diberikan pengguna, tersimpan di `public/uploads/manhattan-coffee-shop-logo.png`; migrasi `UseProvidedCoffeeShopLogo` memperbarui pengaturan logo database.
- **Gambar menu**: foto produk minuman yang tersedia di proyek dipakai untuk kategori terkait; kategori tanpa foto sumber memakai ilustrasi lokal. Dataset Maven tidak menyertakan foto produk.
- **Lokasi Berkas Mentah**: `dataset/Coffee Shop Sales.xlsx` (diabaikan dari commit git jika > 8 MB).

## Pipeline Tunggal Ujung-ke-Ujung (End-to-End)

Untuk menjalankan seluruh rangkaian pra-pemrosesan data, pelatihan model KNN (3 tugas), evaluasi ilmiah, ekspor tabel/grafik jurnal, dan pembuatan berkas SQL:

```sh
python ml/run_all.py
```

## Sinkronisasi Katalog Dataset

Setelah pipeline menghasilkan file SQL terbaru, jalankan migrasi dan seed untuk mengganti katalog aktif serta mengisi ulang agregat analitik Lower Manhattan. Produk katalog lama dinonaktifkan, bukan dihapus, agar relasi transaksi historis tetap tersimpan.

```sh
php spark migrate
php spark db:seed KatalogMavenSeeder
```

Gunakan `php spark db:seed MenuMinumanSeeder` sebagai nama seeder lama yang kini menjalankan sinkronisasi katalog yang sama. Hasil training terbaru ada di `ml/metadata.json`, sedangkan ringkasan kualitas data ada di `docs/laporan_kualitas_data.md`.

## Menghapus riwayat penjualan lama

Untuk mengosongkan transaksi penjualan lama tanpa menghapus akun admin/kasir atau katalog, pastikan `.env` menunjuk ke database aplikasi yang benar, buat cadangan database, lalu jalankan:

```sh
php spark db:seed HapusRiwayatPenjualanSeeder
```

Seeder ini menghapus baris pesanan, item pesanan, pembayaran, dan notifikasi yang terkait secara transaksional. Seeder tidak menghapus pengguna, produk, pengaturan, data analitik produk, ataupun log rekomendasi. Operasi ini menghapus riwayat secara permanen pada database aktif.

## Hasil Evaluasi Terbaru

Hasil berikut dihitung dari 47.782 transaksi Lower Manhattan, bukan gabungan cabang:
- **Klasifikasi kategori (KNN)**: akurasi 34,89%; baseline kelas mayoritas 38,10%; Top-3 hit rate 78,83%.
- **Prediksi transaksi per jam (KNN)**: MAE 6,12 transaksi; baseline per jam 7,42; R² 0,619 (baseline 0,417).
- **Hubungan kategori dengan segmen waktu**: Cramér's V 0,0335, asosiasi sangat lemah.

Nilai eksperimen dapat berubah saat pipeline dilatih ulang. `ml/metadata.json` dan dokumen hasil pipeline merupakan sumber angka terbaru.

## Batasan sumber foto

Dataset transaksi Maven tidak menyertakan foto produk. File foto yang memang tersedia di proyek dipasangkan ke kategori yang sesuai; kategori tanpa foto sumber menggunakan ilustrasi lokal, bukan foto produk fiktif.

Untuk menjalankan prototipe fase 2 dari root proyek, gunakan seed dan direktori keluaran berikut:

```sh
python prototipe_fase2.py 42 out
```

`prototipe_fase2.py` tidak terdapat di checkout repository ini; perintah tersebut berlaku setelah file prototipe fase 2 disediakan. Pipeline yang tersedia di repository dapat dijalankan dengan `python ml/generate_dataset.py` lalu `python ml/ml_model.py`. Untuk angka yang dipakai dalam naskah, gunakan `ml/hasil_evaluasi.txt` sebagai satu-satunya sumber hasil evaluasi.

Versi paket yang dipatok di `requirements.txt`: scikit-learn 1.8.0, pandas 2.2.3, numpy 2.1.3, joblib 1.5.3, matplotlib 3.10.9, Flask 3.1.1, dan Flask-Cors 5.0.1. Versi Python pada evaluasi tersimpan adalah 3.12.6.

## Catatan database

`database/schema.sql` hanya memuat DDL, termasuk kolom token akses pesanan. Dump lama di root adalah arsip historis dan dapat berisi data; gunakan skema bersih untuk instalasi baru. Migrasi SQL manual di `database/` tidak dijalankan otomatis. Cadangkan database dan tinjau isi migrasi sebelum menjalankannya.

### Pesanan belum dibayar

PR-13 menambahkan status `dibatalkan`. Buat cadangan MariaDB sebelum menerapkan `database/migrasi_pembatalan_pesanan.sql`, lalu jalankan file itu secara manual pada database yang benar. Batas waktu bawaan adalah 15 menit dan dapat diubah pada Pengaturan Admin (1–1440 menit). Daftar pesanan dan proses pembayaran memeriksa pesanan kedaluwarsa; untuk pembatalan rutin tanpa trafik web, jadwalkan perintah ini setiap menit:

```sh
php spark orders:expire
```

Setiap pembatalan mengunci pesanan dan mengembalikan stok dalam transaksi yang sama. Laporan pendapatan hanya menghitung pesanan berstatus `lunas`.

### Hak akses opsi produk

Pengelolaan menu dan opsi produk tetap dibatasi untuk role `kasir`, termasuk route opsi yang memakai `RoleFilter`. Admin tidak dapat membuka halaman menu kasir; ini keputusan desain agar pengelolaan katalog harian dilakukan oleh kasir.

### Log penerimaan rekomendasi

PR-20 menambahkan log tanpa nama atau identitas pelanggan. Sebelum mengaktifkannya, tinjau lalu jalankan `database/migrasi_recommendation_logs.sql` secara manual. Jika tabel belum ada atau pencatatan gagal, rekomendasi dan proses pemesanan tetap berjalan. Admin dapat melihat ringkasan dipilih dibanding ditampilkan per jam melalui `/admin/recommendation-acceptance`.

### Pengguna database aplikasi

Buat pengguna khusus aplikasi, bukan `root`. Jalankan perintah berikut melalui klien MariaDB/MySQL sebagai administrator database, ganti placeholder kata sandi, dan batasi haknya ke database aplikasi:

```sql
CREATE USER 'warkop_app'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON `warkop_bahagia`.* TO 'warkop_app'@'localhost';
```

Atur pengguna dan kata sandi yang sama pada `.env` lokal. Berkas `.env.example` tidak berisi rahasia; `.env` tetap dikecualikan oleh `.gitignore`.

### Evaluasi ML dari transaksi database

Pipeline ML memakai CSV sintetis secara default dan hanya menulis artefak ke `ml/`. Evaluasi data transaksi memakai argumen terpisah dan menyimpan laporan serta model di `ml/hasil_db/`, agar tidak menimpa hasil sintetis:

```sh
python -m pip install -r requirements.txt
python ml/ml_model.py --sumber csv
python ml/ml_model.py --sumber db
```

Mode database membaca `orders` dan `order_items` melalui SELECT transaksi berstatus `lunas`. Sediakan `DB_HOST`, `DB_PORT` (opsional, bawaan 3306), `DB_USER`, `DB_PASSWORD`, dan `DB_NAME` sebagai environment variable pada terminal. Gunakan akun MariaDB khusus baca saja. Evaluasi ditolak bila rentang kurang dari 30 hari atau hasil kurang dari 2.000 baris; dataset tidak digabungkan dengan data sintetis. `python ml/export_dari_db.py` mengekspor data lunas ke `ml/dataset_db.csv`. Waktu transaksi dikonversi ke `Asia/Jakarta` dengan menganggap timestamp naive dari server sebagai UTC.
