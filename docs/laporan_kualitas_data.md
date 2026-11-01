# LAPORAN PENGUJIAN KUALITAS DATA (DATA QUALITY GATE)
**Dataset**: Coffee Shop Sales (Maven Roasters)  
**Ruang Lingkup**: Cabang Lower Manhattan (`store_id` 5)  
**Tanggal Pengujian**: 2026-10-28 13:08:04  
**Status Kelayakan**: **DATA LAYAK DIGUNAKAN (YA)**  

---

## 1. Ringkasan Eksekutif & Rekonsiliasi Baris

- **Total Baris Sumber**: 149,116 baris
- **Transaksi di lokasi target**: 47,782 baris
- **Transaksi cabang lain yang dikecualikan dari ruang lingkup**: 101,334 baris
- **Total Baris Bersih (Diproses)**: 47,782 baris
- **Baris tidak valid yang dibuang**: 0 baris
- **Rekonsiliasi sumber**: `149,116 = 101,334 di luar lokasi + 47,782 transaksi Lower Manhattan`.
- **Rekonsiliasi cleaning**: `47,782 transaksi target = 47,782 baris bersih + 0 baris tidak valid dibuang`.
- **Integritas File (SHA-256)**: `287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1`

---

## 2. Tabel Hasil 15 Pengujian Kualitas Data

| No | Nama Pengujian | Status | Temuan Kritis | Tindakan | Rationale / Alasan |
|---|---|---|---|---|---|
| 1 | Keberadaan & Integritas File | **LULUS** | File valid, ukuran 8.55 MB, SHA-256: 287d06d19f7a832d48f1deea7b149836fb32d591010f060bdcf8d1959653bfe1 | Pertahankan file asli di dataset/ | File mentah tidak pernah diubah |
| 2 | Kesesuaian Skema | **LULUS** | 11 kolom persis sesuai spesifikasi: transaction_id, transaction_date, transaction_time, transaction_qty, store_id, store_location, product_id, unit_price, product_category, product_type, product_detail | Pertahankan struktur kolom | Struktur standar Maven Roasters |
| 3 | Pemeriksaan Nilai Hilang (Missing Values) | **LULUS** | 0 missing values di semua kolom (0 NA total, 0 spasi/null) | Tidak diperlukan imputasi atau penghapusan baris | Dataset lengkap 100% |
| 4 | Pemeriksaan Duplikasi | **LULUS** | Duplikat penuh = 0, duplikat transaction_id = 0, duplikat semu (fitur sama, ID beda) = 112 baris | Duplikat semu dipertahankan | Transaksi dengan produk, qty, dan waktu yang sama pada toko yang sama adalah transaksi pelanggan sah |
| 5 | Validitas Tanggal & Jam | **LULUS** | 181 hari pada Lower Manhattan, 1 toko aktif pada lokasi target (181/181 kombinasi toko-hari), rentang jam 06:00-20:59 (0 transaksi di luar operasional) | Pertahankan seluruh baris | Konsistensi temporal sempurna |
| 6 | Validitas Rentang Numerik & Outlier | **LULUS** | Qty 1-6 (26 outlier IQR), Harga $0.80-$45.00 (1456 outlier IQR) | Pertahankan semua nilai; tandai is_outlier_qty dan is_outlier_price | Harga $45 adalah produk sah (Coffee beans/Branded), qty 8 adalah pembelian sah rombongan |
| 7 | Konsistensi Kategorikal | **LULUS** | Tiap product_id memetakan tepat ke 1 kategori, 1 tipe, dan 1 detail. Tiap store_id memetakan tepat ke 1 lokasi. | Pertahankan relasi | Integritas relasional katalog terjaga |
| 8 | Konsistensi Harga & Harga Katalog | **LULUS** | 12 dari 80 produk mengalami perubahan harga historis; modus diambil sebagai harga katalog acuan. | Turunkan kolom harga_katalog (modus per product_id) | Harga katalog merepresentasikan harga standar operasional |
| 9 | Urutan ID Transaksi | **LULUS** | ID Lower Manhattan berada pada rentang 1 s.d. 149451; tidak ada duplikasi transaction_id pada subset. | Pertahankan ID asli; celah antar-ID tidak dihitung sebagai baris hilang karena penomoran mencakup cabang lain. | Filter lokasi mengambil sebagian dari rangkaian ID transaksi gabungan |
| 10 | Distribusi Jam Operasional | **LULUS** | Operasional pukul 06:00 s.d. 20:59. Pukul 20 memiliki volume paling sedikit (75 baris). | Pertahankan seluruh data jam 06–20 | Menjadi representasi kondisi jam tutup dan periode sepi coffee shop |
| 11 | Ketidakseimbangan Kelas Kategori | **LULUS** | 9 kategori ditemukan; jumlah sampel terkecil Packaged Chocolate=180. Coffee memiliki 18,204 baris (38.1%). | Semua 9 kategori dipertahankan tanpa penggabungan ke 'Lainnya' | Jumlah sampel memenuhi syarat minimal pelatihan stratifikasi |
| 12 | Tren Volume Bulanan (Pergeseran Distribusi) | **LULUS** | Volume transaksi meningkat setiap bulan: Jan (5,533) s.d. Jun (11,296). | Gunakan split stratifikasi 80:20 sebagai evaluasi utama, dan laporkan split berbasis waktu (Jan-Mei vs Jun) untuk perbandingan pergeseran distribusi. | Mencegah bias evaluasi temporal |
| 13 | Uji Ketergantungan Waktu (Chi-Square & Kruskal-Wallis) | **LULUS** | Kategori x Segmen: Chi2=161.2, p=2.63e-22, Cramér's V=0.0335 (asosiasi sangat lemah mendekati nol). Volume x Jam: Kruskal-Wallis H=1016.86, p=3.78e-208 (asosiasi sangat kuat). | Memvalidasi temuan kritis jurnal: komposisi menu invarian waktu, sinyal waktu ada pada volume transaksi per jam. | Fondasi teoritis pembagian Tugas 1 (kategori) dan Tugas 2 (prediksi keramaian) |
| 14 | Pemeriksaan Kebocoran Target (Anti-Leakage) | **LULUS** | Daftar fitur terlarang untuk klasifikasi kategori: transaction_id, product_id, unit_price, product_detail, product_type. | Fitur yang diizinkan: jam, hari_minggu, is_weekend, jam_sin, dan jam_cos. store_id tidak digunakan karena seluruh data berasal dari satu cabang. | Mencegah kebocoran identitas produk dan fitur lokasi konstan |
| 15 | Rekonsiliasi Jumlah Baris | **LULUS** | Baris sumber: 149,116 | dikecualikan karena lokasi lain: 101,334 | Lower Manhattan: 47,782 | bersih: 47,782 | baris tidak valid dibuang: 0 | Hanya transaksi Lower Manhattan masuk ke tahap pemrosesan; transaksi cabang lain dikecualikan dari ruang lingkup. | Pemisahan ruang lingkup lokasi bukan penghapusan data rusak |

---

## 3. Temuan Kritis Analisis Ketergantungan Waktu

1. **Invariansi Kategori terhadap Waktu (Chi-Square & Cramér's V)**:
   - Pengujian chi-square independensi antara 9 kategori produk dan segmen waktu menghasilkan Cramér's V = **0.0335**.
   - Distribusi ini dihitung hanya dari transaksi cabang Lower Manhattan dan tidak digeneralisasi ke cabang lain.
2. **Ketergantungan Kuat Volume terhadap Jam (Kruskal-Wallis)**:
   - Uji Kruskal-Wallis untuk volume transaksi per jam menghasilkan p-value **< 1e-10**.
   - Sinyal waktu terkuat pada subset Lower Manhattan terletak pada **volume transaksi per jam**, bukan perubahan drastis pada kategori produk.

---

## 4. Keputusan Data Quality Gate

Berdasarkan seluruh pengujian di atas yang lulus 100% tanpa adanya data korup, missing value, atau kesalahan skema:
**STATUS: DATA LAYAK DIGUNAKAN: YA**.
Pipeline diperbolehkan melanjutkan ke Fase 2 (Pra-pemrosesan & Rekayasa Fitur).
Seluruh proses lanjutan wajib menggunakan subset `store_location = 'Lower Manhattan'`.
