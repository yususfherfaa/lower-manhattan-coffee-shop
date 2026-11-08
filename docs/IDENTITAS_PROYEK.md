# Identitas Proyek Lower Manhattan Coffee Shop

Dokumen ini menjadi profil produk dan panduan orientasi teknis untuk proyek
Lower Manhattan Coffee Shop. Isinya merangkum perilaku yang ditemukan pada
route, controller, view, model, seeder, dan layanan yang tersedia di repository.
Dokumen ini tidak menganggap fitur yang belum ada sebagai fitur aktif.

## 1. Identitas Ringkas

| Elemen | Identitas |
| --- | --- |
| Nama aplikasi | Lower Manhattan Coffee Shop |
| Jenis aplikasi | Aplikasi web pemesanan menu berbasis meja dan Point of Sale (POS) kasir |
| Tujuan | Menampilkan katalog, menerima pesanan meja, mencatat pembayaran, mengelola produk dan staf kasir, serta menyajikan laporan dan analitik |
| Pengguna utama | Pelanggan di meja, kasir, dan administrator |
| Framework web | CodeIgniter 4 |
| Bahasa sisi server | PHP 8.2 atau lebih baru |
| Database | MySQL/MariaDB |
| Database aplikasi lokal | `lowermanhattancoffeshop` |
| Database proyek lain | `warkop_bahagia` adalah database terpisah dan bukan target aplikasi ini |
| Microservice analitik | Python dan Flask, port lokal `5001` |
| URL pengembangan | `http://localhost:8080` |
| Repository | [yususfherfaa/lower-manhattan-coffee-shop](https://github.com/yususfherfaa/lower-manhattan-coffee-shop) |

### Identitas produk

Ini adalah sistem operasional coffee shop dengan pola **pesan dari meja melalui
QR, kemudian pesanan diselesaikan melalui alur pembayaran yang ditangani kasir**.
Selain fungsi operasional, proyek membawa katalog Maven Roasters dan komponen
analitik/rekomendasi berbasis waktu dari data publik Lower Manhattan.

Nama database adalah identitas teknis instalasi. Nama toko yang tampil pada
halaman dapat diubah dari pengaturan aplikasi; alamat, kontak, URL server, logo,
gambar QRIS, dan batas waktu pembayaran juga disimpan sebagai konfigurasi.

## 2. Tujuan dan Ruang Lingkup

### Tujuan operasional

1. Memungkinkan pelanggan melihat menu yang sesuai dengan meja tempat mereka
   berada, tanpa harus membuat akun pelanggan.
2. Mengirim pilihan pelanggan sebagai pesanan yang dapat dilihat dan ditangani
   oleh kasir.
3. Membantu kasir memeriksa pesanan, mencatat uang pembayaran, dan menerbitkan
   struk.
4. Memberikan administrator sarana untuk memelihara akun kasir, identitas toko,
   QR meja, stok, dan pelaporan.
5. Menggunakan pola historis transaksi coffee shop sebagai referensi analitik
   kategori, waktu ramai, dan rekomendasi menu.

### Yang bukan tujuan yang dapat diasumsikan

- Sistem tidak menyediakan integrasi gateway pembayaran eksternal yang
  mengonfirmasi pembayaran QRIS secara otomatis. Aplikasi menampilkan gambar
  QRIS dan status pesanan; pencatatan pembayaran tetap dilakukan melalui alur
  kasir.
- Dataset Maven bukan rekaman transaksi pelanggan yang sedang menggunakan
  instalasi lokal ini. Dataset tersebut adalah data historis publik yang
  difilter ke Lower Manhattan dan digunakan untuk katalog/analitik model.
- Keberadaan model rekomendasi tidak menjamin rekomendasi selalu lebih akurat
  daripada baseline; metrik evaluasinya dijelaskan pada bagian 9.

## 3. Jenis Pengguna dan Hak Kerja

### Pelanggan di meja

- Membuka tautan pemesanan yang terikat dengan ID meja, biasanya melalui QR
  meja.
- Melihat katalog, mencari menu, memilih kategori, ukuran, suhu, dan varian yang
  tersedia.
- Mengatur keranjang, jumlah pesanan, dan catatan per item.
- Mengirim nama pemesan serta metode pembayaran.
- Melihat ringkasan/status pesanan dengan token akses pesanan.

Pelanggan tidak memerlukan akun login aplikasi. Identitas meja dan token
pesanan digunakan dalam alur pemesanan.

### Kasir

- Login ke area kasir.
- Melihat ringkasan kerja hari ini dan notifikasi pesanan.
- Memeriksa detail pesanan meja.
- Memproses pembayaran pesanan yang menunggu pembayaran.
- Membatalkan pesanan yang belum dibayar; stok dikembalikan oleh layanan
  pembatalan.
- Mencetak/melihat struk dan laporan penjualan.
- Mengelola katalog, stok, foto, suhu, deskripsi, serta opsi/varian produk.

### Administrator

- Melihat dashboard toko.
- Membuat, melihat, mengubah, dan menonaktifkan akun kasir.
- Mengubah profil toko dan aset pembayaran.
- Menghasilkan, mengunduh, dan mencetak QR code meja.
- Melihat laporan stok/aktivitas produk serta laporan penjualan.
- Melihat analitik dataset, hasil evaluasi model, dan ringkasan penerimaan
  rekomendasi.

Sesuai konfigurasi route, pengelolaan menu harian berada pada role `kasir`,
sementara area administrasi dan pengelolaan akun kasir berada pada role `admin`.

## 4. Fitur Pelanggan dan Alur Pemesanan Meja

### 4.1 Masuk ke menu berdasarkan meja

Route `/order/{id_meja}` memvalidasi bahwa ID meja terdaftar sebelum
menampilkan menu. Administrator menyediakan halaman QR meja untuk menghasilkan
kode akses ke menu meja, sehingga QR dapat dicetak dan diletakkan pada tent
card.

### 4.2 Menjelajah katalog

Halaman pemesanan pelanggan menyediakan:

- Kartu produk dengan gambar, nama, kategori, deskripsi, harga, dan keadaan
  stok.
- Pencarian menu.
- Filter chip kategori dan tampilan katalog per kategori.
- Indikator stok habis.
- Widget rekomendasi waktu dengan jam berjalan, status metode, dan keadaan
  loading/error/retry di antarmuka.
- Tampilan kelompok ukuran Small/Regular/Large sebagai satu kartu menu jika
  ukuran tersebut memang tersedia untuk produk.
- Pemilihan panas/es atau suhu tetap untuk produk minuman yang mendukungnya.
- Pemilihan opsi produk aktif (misalnya pilihan rasa) jika produk memiliki
  opsi.

Harga menu berasal dari produk. Tambahan harga es atau opsi yang dipilih
dihitung kembali di sisi server saat item dimasukkan/checkout, bukan hanya
dipercayakan pada tampilan browser.

### 4.3 Keranjang

Keranjang disimpan pada sesi berdasarkan meja dan mendukung:

- Menambah item sesuai stok tersedia.
- Membedakan baris untuk suhu atau opsi yang berbeda.
- Mengubah jumlah dan menghapus item.
- Menambahkan catatan untuk masing-masing item, maksimal 500 karakter.
- Melihat rincian harga dan total.
- Memilih pembayaran tunai atau QRIS.
- Mengisi nama pemesan sebelum checkout.

### 4.4 Checkout dan konfirmasi

Saat checkout, server memvalidasi ulang meja, produk, jumlah, stok, opsi aktif,
suhu, metode pembayaran, dan catatan. Jika valid, aplikasi menjalankan transaksi
database untuk:

1. Membuat pesanan dengan status `menunggu_pembayaran` dan token akses acak.
2. Menyimpan snapshot item, pilihan, catatan, harga, kuantitas, dan subtotal.
3. Mengurangi stok secara transaksional setelah memastikan stok masih cukup.
4. Membuat notifikasi baru bagi kasir.
5. Mengosongkan keranjang hanya setelah transaksi berhasil.

Untuk pilihan tunai, pelanggan diarahkan ke ringkasan pesanan. Untuk pilihan
QRIS, pelanggan diarahkan ke halaman yang menampilkan informasi toko, jumlah,
dan aset QRIS yang dikonfigurasi.

Halaman keberhasilan/status dan halaman QRIS memerlukan token pesanan yang
sesuai. Token dapat berasal dari URL atau sesi browser. Endpoint status tidak
menampilkan status pesanan kepada pemanggil tanpa token yang cocok.

## 5. Fitur Kasir

### 5.1 Dashboard kasir

Dashboard menampilkan nama kasir yang sedang login, ringkasan jumlah pembayaran
dan pendapatan kasir untuk hari berjalan, jumlah produk, serta pintasan ke
pekerjaan operasional.

### 5.2 Notifikasi pesanan

Kasir dapat melihat notifikasi baru beserta ID meja, pemesan, total, dan item
pesanan. Endpoint notifikasi mengembalikan item dan rincian opsi/suhu/catatan;
kasir dapat menandai notifikasi satu per satu atau semuanya sudah dibaca.

### 5.3 Daftar, detail, dan pembatalan pesanan

- Daftar pesanan menampilkan pesanan terbaru dan ringkasan pembayaran.
- Kasir dapat membuka rincian item serta informasi pembayaran.
- Pembatalan manual hanya berlaku untuk pesanan yang masih
  `menunggu_pembayaran`.
- Pembatalan mengubah status menjadi `dibatalkan` dan mengembalikan kuantitas
  stok dalam transaksi yang sama.
- Operasi pembatalan dikunci terhadap pesanan agar stok tidak dikembalikan dua
  kali ketika ada permintaan yang bersamaan.
- Pesanan yang sudah lunas tidak dapat dibatalkan melalui alur ini.

### 5.4 Konfirmasi pembayaran dan struk

Pada halaman pembayaran, kasir melihat jumlah yang harus dibayar, lalu
memasukkan jumlah uang yang diterima. Sistem:

- Menolak jumlah pembayaran yang kurang.
- Menghitung kembalian.
- Mengunci pesanan saat memproses pembayaran agar tidak dicatat dua kali.
- Mencatat metode, kasir, total, uang dibayar, dan kembalian.
- Mengubah status pesanan menjadi `lunas` hanya bila pencatatan pembayaran
  berhasil.
- Mengarahkan kasir ke struk yang mencantumkan rincian transaksi.

QRIS pada aplikasi adalah tampilan QRIS yang dikonfigurasi pengelola toko;
konfirmasi pembayaran QRIS tetap merupakan bagian dari proses operasional
kasir, bukan callback otomatis dari penyedia pembayaran.

### 5.5 Pengelolaan katalog dan stok

Kasir dapat menambah, mengubah, dan menghapus/nonaktifkan menu. Form produk
mendukung:

- Nama dan satu dari sembilan kategori katalog.
- Harga, stok, deskripsi singkat (maksimal 255 karakter), dan foto.
- Foto JPG, PNG, atau WebP, dengan batas unggah 2 MB.
- Pengaturan pilihan suhu panas/es, suhu tetap, dan tambahan harga es untuk
  minuman yang relevan.
- Opsi produk bernama dengan tambahan harga, status aktif/nonaktif, dan urutan.
- Tampilan produk yang memiliki ukuran sebagai kelompok ukuran pada daftar
  manajemen menu.

Penambahan produk juga mencatat aktivitas ke tabel aktivitas produk. File foto
produk bawaan katalog berada pada `public/uploads/`; foto unggahan runtime juga
disimpan di area upload publik.

## 6. Fitur Administrator

### 6.1 Dashboard admin

Ringkasan dashboard mencakup:

- Jumlah pesanan yang memiliki pembayaran pada hari berjalan.
- Pendapatan dari pembayaran pada hari berjalan.
- Jumlah akun dengan role kasir sesuai query dashboard.
- Jumlah produk.
- Pintasan untuk QR meja, laporan stok/menu, dan pengaturan.

### 6.2 Manajemen akun kasir

Administrator dapat:

- Melihat daftar akun dengan role `kasir`.
- Menambahkan akun dengan nama, username unik, dan password.
- Mengubah nama/username dan mengganti password secara opsional.
- Menonaktifkan akun; jika kolom status akun tidak tersedia pada skema, alur
  fallback controller menghapus record.

Password yang disimpan akun baru/perubahan password dibuat menggunakan
`password_hash`; login memverifikasi hash tersebut.

### 6.3 Identitas toko dan konfigurasi

Halaman pengaturan meliputi:

- Nama coffee shop.
- Alamat dan kontak/nomor WhatsApp.
- IP/URL server yang digunakan pengunjung meja.
- Batas waktu pembayaran pesanan, 1 sampai 1440 menit; nilai bawaan layanan
  pembatalan adalah 15 menit.
- Gambar logo.
- Gambar QRIS.

Unggahan gambar yang diterima adalah JPG, PNG, atau WebP dengan batas 2 MB.
Nilai pengaturan disimpan dalam tabel `settings`.

### 6.4 QR meja

Halaman QR meja mengurutkan daftar meja, menghasilkan tautan/QR untuk masing-
masing meja, dan menyediakan aksi unduh serta cetak semua QR. URL host
pengunjung meja mengikuti pengaturan server.

### 6.5 Laporan stok dan aktivitas

Laporan stok menyajikan data produk dan histori aktivitas produk. Tersedia
keluaran PDF untuk stok dan aktivitas. Informasi produk mencakup identitas,
kategori, harga, jumlah stok, dan jejak aktivitas kasir.

### 6.6 Analitik penjualan dan keramaian

Halaman analitik menyajikan ringkasan dari profil data serta tabel agregat
dataset, antara lain:

- Jumlah transaksi bersih dataset.
- Kategori dengan proporsi tertinggi.
- Puncak dan jam terendah berdasarkan distribusi historis.
- Grafik distribusi transaksi per jam.
- Heatmap hari terhadap jam.
- Rata-rata volume transaksi dan label kepadatan per jam.
- Ringkasan kategori menurut segmen waktu.

Halaman ini merupakan analitik dataset/hasil olah yang tersedia di project;
angka historisnya bukan dashboard real-time dari transaksi lokal toko.

### 6.7 Evaluasi model

Admin dapat melihat metrik, grafik, matriks konfusi, nilai pemilihan K, dan
ringkasan evaluasi dari artefak ML di folder `ml/`. Halaman menampilkan hasil
terakhir yang tersimpan; melatih ulang model dilakukan melalui pipeline
terpisah.

### 6.8 Penerimaan rekomendasi

Apabila migrasi tabel `recommendation_logs` sudah diterapkan, aplikasi mencatat
menu yang ditampilkan dan pilihan menu yang diklik tanpa menyimpan nama atau
identitas pelanggan pada log ini. Dashboard admin merangkum impression, pilihan,
rasio penerimaan, dan pola berdasarkan jam. Jika tabel/log tidak tersedia,
rekomendasi tetap dapat berjalan dan kegagalan pencatatan dicatat sebagai
peringatan.

## 7. Laporan Penjualan untuk Admin dan Kasir

Route `/laporan` digunakan bersama oleh role admin dan kasir:

- Rentang tanggal dapat dipilih; bawaan halaman adalah awal bulan sampai hari
  ini.
- Admin dapat memfilter berdasarkan kasir atau seluruh kasir.
- Kasir dibatasi pada transaksi miliknya sendiri oleh query server.
- Hanya pesanan berstatus `lunas` yang masuk ke laporan pendapatan.
- Ringkasan mencakup jumlah transaksi dan pendapatan.
- Daftar menu terjual menyertakan agregasi kuantitas dan pendapatan, termasuk
  pemisahan opsi/suhu yang berbeda.
- Laporan dapat diekspor sebagai PDF.

Rentang tanggal divalidasi sebelum query dijalankan.

## 8. Siklus Pesanan, Status, dan Persediaan

| Status | Makna |
| --- | --- |
| `menunggu_pembayaran` | Pesanan tercatat, stok sudah dikurangi, dan menunggu diselesaikan melalui pembayaran kasir |
| `lunas` | Pembayaran berhasil dicatat |
| `dibatalkan` | Pembayaran tidak dilanjutkan; stok pesanan dikembalikan |

Timeout pesanan belum dibayar dibaca dari `batas_pembayaran_menit`. Pemeriksaan
pesanan kedaluwarsa berjalan saat halaman daftar pesanan atau proses pembayaran
diakses. Repository juga menyediakan perintah `php spark orders:expire` untuk
penjadwalan berkala jika instalasi memerlukan pemeriksaan tanpa trafik web.

Checkout dan proses pembatalan dirancang menggunakan transaksi database agar
pembuatan pesanan, perubahan stok, status, dan notifikasi tetap konsisten.

## 9. Katalog, Dataset, Rekomendasi, dan Batas Interpretasi

### 9.1 Katalog

Seeder `KatalogMavenSeeder` menyinkronkan katalog 80 produk dari dataset Coffee
Shop Sales Maven Roasters. Sembilan kategorinya:

1. Bakery
2. Branded
3. Coffee
4. Coffee beans
5. Drinking Chocolate
6. Flavours
7. Loose Tea
8. Packaged Chocolate
9. Tea

Produk dengan ukuran Small/Regular/Large tetap menjadi record produk tersendiri
agar harga, stok, dan identitas dataset per ukuran dapat dipertahankan. UI
katalog mengelompokkan record tersebut sebagai satu menu dengan pilihan ukuran.
Gambar katalog yang tersedia digunakan sesuai mapping produk; dataset transaksi
Maven sendiri tidak menyertakan foto produk.

### 9.2 Dasar dataset

- Nama dataset: Coffee Shop Sales (Maven Roasters).
- Cabang yang dianalisis: Lower Manhattan (`store_id = 5`).
- Metadata pelatihan mencatat 47.782 baris untuk lokasi tersebut.
- Data bersih digunakan untuk analitik historis, evaluasi, agregat, dan model.
- Pipeline juga mendukung data sintetis serta jalur evaluasi terpisah terhadap
  data transaksi database; kedua sumber tersebut tidak boleh dianggap sebagai
  satu kumpulan data yang sama.

Rincian dataset dan reproduksi pipeline terdapat di [README utama](../README.md),
`ml/metadata.json`, `ml/hasil_evaluasi.txt`, dan `docs/laporan_kualitas_data.md`.

### 9.3 Rekomendasi waktu

Pada halaman menu pelanggan, aplikasi meminta rekomendasi ke endpoint
CodeIgniter `/api/rekomendasi`. Controller memanggil microservice Flask di
`127.0.0.1:5001`, menggabungkan hasil rekomendasi dengan informasi produk dan
gambar dari database, lalu menyajikannya pada widget menu. Jam dapat memakai
waktu saat ini atau jam/menit yang divalidasi sebagai input.

Jika microservice tidak tersedia atau hasilnya tidak valid, controller memiliki
jalur fallback berbasis aturan/popularitas yang tersedia di aplikasi. Dengan
demikian, microservice mendukung rekomendasi tetapi tidak seharusnya menjadi
satu-satunya sumber untuk seluruh proses checkout.

Pilihan dari daftar rekomendasi dapat dicatat ke endpoint
`POST /recommendation-log/select`. Pencatatan ini hanya bekerja jika skema log
terkait telah tersedia.

### 9.4 Microservice ML

Flask berjalan secara lokal pada port `5001`. Endpoint yang ditemukan:

| Endpoint | Fungsi |
| --- | --- |
| `GET /health` | Status service dan versi |
| `GET /model-info` | Metadata model dan evaluasi |
| `GET /api/rekomendasi` | Rekomendasi lima menu serta estimasi/label keramaian; menerima jam/menit opsional |
| `POST /predict-crowd` | Prediksi jumlah transaksi per jam dan kelas keramaian dari konteks waktu |

API Flask membatasi CORS ke origin lokal yang terdaftar di konfigurasi
microservice.

### 9.5 Hasil evaluasi tersimpan

Nilai dalam `ml/metadata.json` pada repository ini mencatat:

| Tugas | Model/metrik yang tercatat |
| --- | --- |
| Klasifikasi kategori | KNN, K=20; akurasi sekitar 34,89%; baseline kelas mayoritas sekitar 38,10%; Top-3 hit rate sekitar 78,83% |
| Prediksi jumlah transaksi per jam | KNN regresi, K=17; MAE sekitar 6,12 transaksi/jam; R² sekitar 0,619 |
| Baseline prediksi keramaian | MAE sekitar 7,42 dan R² sekitar 0,417 |

Pada eksperimen tersimpan, akurasi klasifikasi KNN berada di bawah baseline
mayoritas. Karena itu model ini diposisikan sebagai komponen eksperimen/
pendukung rekomendasi, bukan sebagai bukti bahwa personalisasi telah
mengungguli pilihan populer sederhana. Nilai dapat berubah jika pipeline dan
dataset dilatih ulang.

## 10. Peta Route Aplikasi

| Route | Peran/fungsi |
| --- | --- |
| `/` | Halaman login staf |
| `/order/{id_meja}` | Katalog pemesanan pelanggan berdasarkan meja |
| `/cart/{id_meja}` | Keranjang pelanggan |
| `/cart/add/...`, `/cart/qty/...`, `/cart/remove/...`, `/cart/reset/...` | Perubahan isi keranjang |
| `POST /cart/note/...` | Catatan item keranjang |
| `POST /checkout/{id_meja}` | Pembuatan pesanan |
| `/payment-qris/{id_order}/{id_meja}` | Tampilan instruksi QRIS |
| `/order-success/{id_order}/{id_meja}` | Ringkasan pesanan/token |
| `/cek-status/{id_order}` | Pengecekan status pesanan bertoken |
| `/laporan`, `/laporan/pdf` | Laporan penjualan admin/kasir |
| `/laporan-stok`, `/laporan-stok/pdf` | Laporan stok admin |
| `/admin/dashboard` | Dashboard administrator |
| `/admin/kasir...` | Pengelolaan akun kasir |
| `/admin/settings` | Identitas toko dan konfigurasi |
| `/admin/qr-meja` | QR meja |
| `/admin/analitik-penjualan` | Analitik historis |
| `/admin/evaluasi-model` | Evaluasi model tersimpan |
| `/admin/recommendation-acceptance` | Penerimaan rekomendasi |
| `/kasir/dashboard` | Dashboard kasir |
| `/kasir/menu...` | Katalog dan opsi produk |
| `/kasir/pesanan...` | Daftar/detail/pembatalan pesanan |
| `/kasir/bayar...`, `/kasir/struk...` | Pembayaran dan struk |
| `/api/rekomendasi...` | API rekomendasi CodeIgniter |
| `/api/notifications...` | API notifikasi kasir |

Daftar otoritatif route dan filter ada di `app/Config/Routes.php`.

## 11. Teknologi dan Komponen

### Aplikasi web

- PHP `^8.2`.
- CodeIgniter `^4.7`.
- MySQL/MariaDB sebagai penyimpanan operasional.
- HTML, CSS, dan JavaScript untuk halaman publik dan back office.
- FPDF untuk laporan PDF.
- Endroid QR Code untuk pembuatan QR meja.
- PHPUnit untuk pengujian PHP.

### Komponen data/ML

- Python dengan paket yang dikunci dalam `requirements.txt`.
- Flask dan Flask-CORS sebagai API lokal untuk prediksi.
- scikit-learn, pandas, NumPy, dan joblib untuk pengolahan/pelatihan model.
- Model, metadata, grafik, data olahan, dan tes berada dalam `ml/`.

Versi Python dan versi paket harus disesuaikan dengan file dependency serta
artefak yang dipakai oleh instalasi. Metadata pelatihan mencatat versi
lingkungan pada saat model tersebut dilatih.

## 12. Gambaran Data Utama

| Tabel | Kegunaan |
| --- | --- |
| `users` | Akun admin dan kasir |
| `tables_meja` | ID/daftar meja untuk alur order |
| `products` | Katalog, harga, stok, kategori, gambar, dan atribut suhu/ukuran |
| `product_options` | Opsi/varian produk dan tambahan harga |
| `orders` | Header pesanan, meja, status, total, metode, dan token akses |
| `order_items` | Snapshot item, jumlah, harga, opsi, suhu, dan catatan |
| `payments` | Pembayaran kasir, nominal diterima, kembalian, dan kasir pencatat |
| `notifications` | Pemberitahuan pesanan kepada kasir |
| `data_products` | Riwayat aktivitas perubahan produk |
| `settings` | Konfigurasi toko dan tampilan |
| `agg_keramaian_jam` | Agregat transaksi/keramaian per waktu |
| `agg_kategori_segmen` | Agregat kategori berdasarkan segmen waktu |
| `recommendation_logs` | Menu yang ditampilkan/dipilih, jika migrasi log telah diterapkan |

Skema awal bersih tersedia dalam `database/schema.sql`. Migrasi CodeIgniter dan
SQL manual merupakan bagian dari siklus perubahan skema; file SQL manual perlu
ditinjau dan dijalankan secara sadar. Instalasi aplikasi menggunakan database
`lowermanhattancoffeshop`, bukan `warkop_bahagia`.

## 13. Keamanan dan Keandalan yang Terlihat di Implementasi

- Area admin dan kasir memakai filter login dan pemeriksaan role pada route.
- Password staf dibuat dan diverifikasi dengan API password hashing PHP.
- Login memiliki batas lima kegagalan per IP dalam jendela 15 menit melalui
  cache aplikasi.
- Token pesanan acak membatasi akses halaman QRIS, ringkasan, dan status.
- Checkout, pemrosesan pembayaran, dan pembatalan menggunakan transaksi serta
  penguncian record pada operasi yang berisiko terhadap stok/status.
- Input inti seperti nama produk, harga, stok, tanggal laporan, opsi, dan
  upload gambar divalidasi pada sisi server.
- Endpoint perubahan opsi produk dan pemilihan rekomendasi memasang filter
  CSRF sesuai route-nya.
- File `.env`, virtual environment lokal, dan dump database berisi data tidak
  seharusnya masuk ke repository.

Untuk deployment, gunakan kredensial database khusus aplikasi, password kuat,
HTTPS, backup berkala, dan konfigurasi environment production. Detail setup
terbaru tersedia di [README utama](../README.md).

## 14. Struktur Folder Penting

| Folder/file | Isi |
| --- | --- |
| `app/Config/` | Route, database, filter, dan konfigurasi CodeIgniter |
| `app/Controllers/` | Alur pelanggan, admin, kasir, checkout, laporan, dan API |
| `app/Models/` | Model tabel pengguna, produk, pesanan, pembayaran, dan pengaturan |
| `app/Database/Migrations/` | Migrasi skema dan penyesuaian katalog |
| `app/Database/Seeds/` | Sinkronisasi katalog dan utilitas seeder |
| `app/Libraries/` | Aturan pembayaran/checkout, pembatalan, deskripsi, dan klien Flask |
| `app/Views/` | Halaman pelanggan, admin, kasir, dan autentikasi |
| `database/` | Skema bersih dan SQL migrasi manual |
| `dataset/` | Data sumber/kerja yang dipakai pipeline |
| `docs/` | Dokumentasi dataset, desain, hasil, serta identitas proyek |
| `ml/` | API Flask, pipeline, model, konfigurasi, dan tes ML |
| `public/assets/` | CSS, JavaScript, grafik, dan aset UI |
| `public/uploads/` | Gambar katalog, logo, QRIS, dan file media yang disajikan web |
| `tests/` | Pengujian CodeIgniter/PHP |
| `writable/` | Log, cache, sesi, dan data runtime CodeIgniter |

## 15. Menjalankan Secara Lokal

1. Pasang dependensi PHP dengan `composer install`.
2. Salin `.env.example` menjadi `.env`; atur `app.baseURL` serta kredensial
   database lokal.
3. Buat database MySQL/MariaDB **`lowermanhattancoffeshop`** dan impor
   `database/schema.sql` untuk skema awal.
4. Jalankan `php spark migrate`.
5. Jalankan `php spark db:seed KatalogMavenSeeder` untuk menyinkronkan katalog
   dan agregat dataset.
6. Siapkan akun admin/kasir dan konfigurasi toko sesuai instalasi.
7. Jalankan `php spark serve` untuk aplikasi web.
8. Untuk rekomendasi berbasis microservice, siapkan virtual environment Python,
   pasang `requirements.txt`, lalu jalankan `python ml/app_flask.py` pada
   terminal lain.

Server Flask diperlukan untuk layanan rekomendasi model; server web CodeIgniter
tetap melayani rute lainnya secara terpisah. Untuk mode rekomendasi lengkap,
pastikan database, model, dan kedua service berjalan.

## 16. Pengujian dan Batasan Operasional

- Tes PHP tersedia melalui `php vendor/bin/phpunit`.
- Tes ML tersedia pada `ml/tests/`; tes yang melakukan HTTP memerlukan API Flask
  berjalan di port `5001`.
- Aplikasi lokal memerlukan service MySQL/MariaDB pada host/port yang
  dikonfigurasi di `.env`.
- Mode pengembangan dapat menggunakan toolbar/debug CodeIgniter; jangan
  mengekspos environment development pada publik.
- QRIS adalah instruksi pembayaran yang memanfaatkan gambar yang dikonfigurasi,
  bukan integrasi rekonsiliasi provider.
- Analitik dataset historis dan hasil training perlu dibaca sebagai hasil
  penelitian/observasi dataset, bukan jaminan prediksi untuk toko aktual.
- Prototipe fase 2 yang disebut oleh sebagian catatan lama tidak termasuk
  source utama yang terdaftar dalam repository ini.

## 17. Berkas Acuan

- [README dan panduan instalasi](../README.md)
- [Konfigurasi route](../app/Config/Routes.php)
- [Skema database bersih](../database/schema.sql)
- [Seeder katalog](../app/Database/Seeds/KatalogMavenSeeder.php)
- [Metadata model](../ml/metadata.json)
- [Evaluasi model](../ml/hasil_evaluasi.txt)
- [Kualitas dataset](./laporan_kualitas_data.md)
