# ANALISIS ETIKA, PRIVASI, DAN KETERBATASAN SISTEM CERDAS

**Mata Kuliah**: Kecerdasan Buatan (Artificial Intelligence)  
**Topik**: Etika AI, Privasi Data Konsumen, Transparansi Rekomendasi, & Dampak Sosial  

---

## 1. Etika Kecerdasan Buatan & Transparansi Algoritmik (Explainable AI)

Penerapan kecerdasan buatan pada sektor layanan publik dan retail pangan menuntut kepatuhan etis agar keputusan algoritma tidak memanipulasi atau merugikan konsumen:

1. **Transparansi Sumber Rekomendasi (*Algorithmic Transparency*)**:
   - Pelanggan berhak mengetahui mengapa produk tertentu direkomendasikan pada layar mereka.
   - Pada antarmuka antrean pemesanan meja, sistem menyertakan label informatif yang jelas: *"Rekomendasi Menu Pilihan Berdasarkan Waktu Sekarang"*.
   - Sistem tidak menyamarkan menu yang kurang laku (*slow-moving items*) sebagai rekomendasi pintar jika menu tersebut tidak didukung oleh probabilitas data historis.
2. **Keterbukaan Status Dataset Simulasi**:
   - Sebagai bagian dari integritas etika ilmiah, sistem secara terbuka menyatakan bahwa model dilatih menggunakan **dataset publik simulasi (*Maven Roasters*)** dan bukan data perilaku konsumsi nyata masyarakat Indonesia. Tidak ada klaim berlebihan yang menyesatkan dosen, mahasiswa, maupun pengguna akhir.

---

## 2. Privasi Konsumen & Prinsip Perlindungan Data (Privacy by Design)

Sistem mengadopsi prinsip perlindungan data sejak perancangan (*Privacy by Design*):

1. **Nir-Pelacakan Individu (*Zero Personal Identifiable Information - PII*)**:
   - Sistem **tidak meminta, tidak merekam, dan tidak menyimpan** nama pelanggan, nomor telepon, alamat email, alamat IP pribadi, maupun nomor kartu perbankan.
   - Kueri inferensi hanya membawa dua parameter lingkungan umum: jam dan menit saat ini.
2. **Pencegahan Profiling Diskriminatif**:
   - Karena tidak ada data demografi (usia, jenis kelamin, ras, status sosial ekonomi), agen cerdas bebas dari risiko diskriminasi harga (*price discrimination*) atau pembedaan perlakuan antar-konsumen.
3. **Penyimpanan Log Tanpa Identitas (*Anonymized Audit Logging*)**:
   - Tabel `recommendation_logs` pada database hanya mencatat `rekomendasi_id`, `jam`, `menit`, `produk_id`, dan biner konversi `dipilih (0/1)`. Tidak ada relasi ke tabel identitas individu.

---

## 3. Bias Data & Keterbatasan Kontekstual (Contextual Bias)

1. **Bias Geografis & Kebiasaan Budaya**:
   - Data pelatihan berasal dari simulasi kawasan perkantoran Lower Manhattan, New York. Budaya konsumsi kopi di New York didominasi oleh *drip coffee*, *bagel*, dan konsumsi cepat (*grab-and-go* di pagi hari).
   - Di Indonesia, budaya warkop dan kedai kopi modern sering kali berfungsi sebagai ruang komunal sosial (*third place*) dengan waktu kunjungan puncak di malam hari serta preferensi menu kopi susu manis, makanan berat, dan camilan lokal. Perbedaan budaya ini menjadi bias inheren dalam data pelatihan.
2. **Risiko Efek Halusinasi Rekomendasi (*Echo Chamber / Filter Bubble*)**:
   - Jika sistem secara terus-menerus merekomendasikan tiga menu teratas (Coffee, Tea, Bakery) karena frekuensi historisnya yang dominan (83,73%), maka menu-menu lain di katalog (seperti cokelat kemasan atau biji kopi sangrai) berpotensi semakin tidak pernah dilihat atau dipesan oleh pelanggan.
   - Oleh karena itu, diimplementasikan mekanisme diversifikasi katalog pada kontroler aplikasi untuk menjaga eksposur menu alternatif.

---

## 4. Batasan Tanggung Jawab & Intervensi Manusia (Human-in-the-Loop)

1. **AI Sebagai Alat Bantu Keputusan (*Augmented Intelligence*)**:
   - Sistem rekomendasi dan prediksi keramaian bertindak sebagai asisten informatif, bukan pengambil keputusan mutlak (*autonomous decision-maker*).
   - Pelanggan tetap memiliki kebebasan penuh (*human agency*) untuk menjelajahi seluruh menu katalog dan memilih makanan/minuman yang diinginkan.
2. **Otoritas Kasir & Barista**:
   - Petugas kasir berhak membatalkan pesanan, mengubah jumlah item, atau memberikan diskon manual tanpa dapat diintervensi atau ditolak oleh sistem AI.
   - Barista memiliki wewenang penuh untuk menonaktifkan menu di sistem kasir ketika bahan baku habis di dapur fisik.
