# REPRESENTASI PENGETAHUAN SISTEM CERDAS

**Mata Kuliah**: Kecerdasan Buatan (Artificial Intelligence)  
**Sistem**: Sistem Rekomendasi Menu Coffee Shop Berbasis Waktu  
**Basis Data**: Konfigurasi Agregat JSON & Skema Relasional  

---

## 1. Taksonomi & Ontologi Katalog Produk (9 Kategori Baku)

Pengetahuan produk pada sistem direpresentasikan dalam struktur taksonomi hirarkis 3 tingkat:
1. **Kategori Tingkat Tinggi (`product_category`)**: 9 kelas utama yang menjadi target klasifikasi model.
2. **Tipe Olahan (`product_type`)**: 29 subtipe yang mengelompokkan karakteristik bahan (misalnya *Brewed Coffee*, *Hot Chocolate*, *Scone*).
3. **Detail Produk / Varian (`product_detail`)**: 80 varian menu individual dengan spesifikasi ukuran (*Small*, *Regular*, *Large*) atau cita rasa (misalnya *Ethiopian Small*, *Chai Tea Regular*).

### Daftar 9 Kategori Produk Utama:
1. `Bakery`: Makanan pendamping (roti, pastry, scone, muffin, kue kering).
2. `Branded`: Merchandise fisik (cangkir, botol minum, kaos, mug).
3. `Coffee`: Minuman olahan kopi (brewed coffee, espresso, latte, cappuccino).
4. `Coffee beans`: Kemasan biji kopi sangrai utuh untuk seduh manual.
5. `Drinking Chocolate`: Minuman cokelat panas dan dingin non-kafein.
6. `Flavours`: Sirup perasa tambahan (karamel, vanila, hazelnut).
7. `Loose Tea`: Daun teh kering racikan premium dalam kemasan.
8. `Packaged Chocolate`: Cokelat batang atau bubuk kemasan siap konsumsi.
9. `Tea`: Minuman seduhan teh siap minum (teh hitam, hijau, teh herbal).

---

## 2. Struktur Diskretisasi Waktu & Konsep Segmen

Pengetahuan temporal tidak diperlakukan sebagai angka mentah acak, melainkan dipetakan ke dalam ontologi segmen operasional:

$$\text{Segmen}(H) = \begin{cases} 
\text{"Pagi"}, & 06 \le H \le 10 \\ 
\text{"Siang"}, & 11 \le H \le 14 \\ 
\text{"Sore"}, & 15 \le H \le 17 \\ 
\text{"Malam"}, & 18 \le H \le 20 \\ 
\text{"Tutup"}, & \text{di luar jam 06–20} 
\end{cases}$$

Di mana $H$ merupakan komponen jam lokal toko. Representasi diskret ini menyederhanakan komunikasi interpretatif antara mesin kecerdasan buatan dengan manusia (pelanggan dan staf kasir).

---

## 3. Basis Pengetahuan Agregat Heuristik (`popularitas_segmen.json`)

Selain model KNN yang bersifat dinamis dalam ruang metrik, sistem menyimpan basis pengetahuan simbolik statis dalam format JSON terstruktur (`ml/config/popularitas_segmen.json`). Basis pengetahuan ini memuat:
- **Frekuensi Historis Produk per Segmen**: Urutan produk dengan transaksi tertinggi di segmen pagi, siang, sore, dan malam hari.
- **Nilai Probabilitas Prior**: Bobot prior yang digunakan oleh aturan logika ketika model inferensi tidak dapat dihubungi.
- **Struktur Tabel Relasional Database**:
  - `products`: Menyimpan katalog menu aktif, status visibilitas, dan harga jual dalam rupiah (menggunakan konversi kurs standar Rp15.500/USD).
  - `categories`: Menyimpan 9 kategori resmi yang terpetakan dengan dataset Maven.
  - `recommendation_logs`: Menyimpan jejak audit (*audit trail*) rekomendasi yang disajikan kepada pelanggan beserta status apakah menu tersebut dimasukkan ke keranjang belanja.

---

## 4. Representasi Ambang Status Keramaian (Kuantil Empiris)

Pengetahuan mengenai kepadatan pengunjung direpresentasikan melalui aturan logika batas ambang (*threshold boundaries*) berbasis kuantil 33% dan 67% dari distribusi volume historis per jam:

$$\text{Status}(V) = \begin{cases} 
\text{"Sepi"}, & V \le 11,0 \\ 
\text{"Sedang"}, & 11,0 < V \le 28,0 \\ 
\text{"Ramai"}, & V > 28,0 
\end{cases}$$

Di mana $V$ adalah estimasi volume transaksi per jam yang diprediksi oleh model regresi. Nilai ambang ini merefleksikan daya tampung fisik kedai dan kapasitas barista dalam melayani pesanan secara optimal.
