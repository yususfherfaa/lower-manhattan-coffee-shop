# DESKRIPSI SISTEM CERDAS SEBAGAI AGEN CERDAS (INTELLIGENT AGENT)

**Mata Kuliah**: Kecerdasan Buatan (Artificial Intelligence)  
**Sistem**: Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu Menggunakan K-Nearest Neighbor  
**Ruang Lingkup**: Lower Manhattan (`store_id = 5`), Dataset Publik Maven Roasters  

---

## 1. Definisi Agen Cerdas & Paradigma PEAS

Sistem rekomendasi dan prediksi keramaian pada kedai kopi Lower Manhattan diformulasikan sebagai sebuah **Agen Cerdas Berbasis Model & Sasaran (*Goal-Based & Model-Based Intelligent Agent*)**. Agen ini berinteraksi dengan lingkungannya secara terstruktur melalui siklus persepsi, penalaran berbasis basis pengetahuan, dan aksi.

### Matriks Karakterisasi PEAS (Performance, Environment, Actuators, Sensors):

| Komponen PEAS | Deskripsi Spesifikasi Sistem |
|---|---|
| **Performance Measure** (Ukuran Kinerja) | • Kecepatan waktu tanggap rekomendasi (< 100 ms).<br>• Keandalan layanan ketersediaan menu (100% *uptime* via *fallback*).<br>• Akurasi kuantil status kepadatan antrean kedai (MAE $\le 6,12$ transaksi/jam, $R^2 \ge 0,61$).<br>• Rasio penerimaan menu oleh pelanggan (*recommendation acceptance rate*). |
| **Environment** (Lingkungan Operasional) | • Pengunjung kedai kopi (pelanggan yang memindai QR meja).<br>• Petugas kasir dan barista di meja kasir (*Point of Sale*).<br>• Fluktuasi jam sibuk kedai fisik Lower Manhattan (jam operasional 06:00 s.d. 20:59). |
| **Actuators** (Aktuator / Keluaran Aksi) | • Kartu menu widget dinamis pada halaman web pemesanan meja pelanggan.<br>• Label indikator status keramaian kedai (Sepi, Sedang, Ramai) beserta ikon jam sibuk.<br>• Rekomendasi cadangan berbasis aturan agregat saat anomali layanan terjadi. |
| **Sensors** (Sensor / Masukan Persepsi) | • Sensor waktu *real-time* sistem (jam, menit, hari dalam sepekan) dalam zona waktu operasional toko (`Asia/Jakarta`).<br>• Log rekaman interaksi klik tombol pemesanan oleh pelanggan. |

---

## 2. Siklus Kerja Agen Cerdas (Perception - Knowledge - Reasoning - Action)

1. **Persepsi (Perception)**:
   - Agen menerima rangsangan lingkungan berupa waktu kueri saat pelanggan membuka antarmuka pemesanan meja.
   - **Prinsip Privasi**: Agen **tidak mengumpulkan** data pribadi pelanggan (*no personal tracking*), nomor telepon, identitas akun, maupun riwayat belanja masa lalu individu. Persepsi murni bersifat temporal-kontekstual.
2. **Representasi Pengetahuan (Knowledge Base)**:
   - Agen menyimpan pola historis 47.782 baris transaksi Maven Roasters Lower Manhattan yang telah ditransformasikan ke dalam model KNN terstandarisasi serta tabel ringkasan agregat segmen waktu.
3. **Penalaran Cerdas (Reasoning Engine)**:
   - Algoritma K-Nearest Neighbors menghitung kedekatan vektor waktu kueri dengan distribusi transaksi historis untuk mengestimasi probabilitas kategori menu teratas dan volume transaksi per jam.
   - Sistem Aturan Logika (*Rule-Based Reasoning*) memvalidasi batasan jam buka kedai dan ketersediaan stok fisik menu.
4. **Aksi (Action Execution)**:
   - Agen menyajikan 5 rekomendasi menu teratas yang paling relevan pada segmen waktu tersebut ke layar *smartphone* pelanggan dan menampilkan visualisasi kepadatan antrean.

---

## 3. Lingkup dan Batasan Sistem (Scope & Non-Scope)

Untuk menjaga kejujuran akademis dan menghindari klaim fitur yang berlebihan:

- **Termasuk dalam Lingkup Sistem (In-Scope)**:
  - Pemesanan meja mandiri berbasis pemindaian kode QR.
  - Estimasi status keramaian kedai (Sepi / Sedang / Ramai) berbasis regresi historis per jam.
  - Rekomendasi menu teratas berbasis pola waktu transaksi.
  - Dasbor POS kasir untuk konfirmasi pesanan, cetak struk, dan pelaporan keuangan.
  - Mekanisme *fail-safe* otomatis ketika layanan inferensi AI mati.
- **Bukan Bagian dari Lingkup Sistem (Non-Scope)**:
  - Tidak terhubung dengan *payment gateway* perbankan otomatis (pembayaran dilakukan manual di kasir / tunai / transfer terverifikasi kasir).
  - Bukan sistem personalisasi perorangan (*collaborative filtering* berbasis akun pengguna).
  - Tidak memiliki sensor kamera fisik atau pemindai RFID untuk mendeteksi jumlah pengunjung secara optik.
