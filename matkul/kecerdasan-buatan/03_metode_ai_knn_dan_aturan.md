# METODE KECERDASAN BUATAN: KNN DAN PENALARAN BERBASIS ATURAN (HYBRID REASONING)

**Mata Kuliah**: Kecerdasan Buatan (Artificial Intelligence)  
**Sistem**: Kombinasi Pembelajaran Berbasis Instans & Sistem Berbasis Aturan  
**Komponen**: Model KNN Scikit-Learn + Heuristik Fallback CodeIgniter 4  

---

## 1. Pembelajaran Berbasis Instans (Instance-Based Learning)

Dalam taksonomi Kecerdasan Buatan, algoritma K-Nearest Neighbors (KNN) diklasifikasikan sebagai **pembelajaran berbasis instans (*instance-based learning*)** atau pembelajaran lambat (*lazy learner*). Berbeda dengan pembelajaran induktif eager (seperti jaringan saraf tiruan atau regresi linier yang mereduksi data menjadi himpunan bobot parameter tetap selama fase pelatihan), KNN mempertahankan seluruh kasus kasus pengalaman masa lalu (*case repository*).

### 1.1. Formulasi Matematis Inferensi KNN
Diberikan basis data kasus masa lalu $D = \{(x_i, y_i)\}_{i=1}^N$, di mana $x_i \in \mathbb{R}^d$ merepresentasikan vektor ciri waktu dan $y_i$ adalah label kelas atau nilai kontinu.
Untuk sebuah instans kueri waktu baru $x$:
1. Hitung jarak metrik Euclidean terstandarisasi ke seluruh kasus:
   $$D(x, x_i) = \sqrt{\sum_{k=1}^d \left(\frac{x_k - x_{i,k}}{\sigma_k}\right)^2}$$
2. Urutkan kasus berdasarkan jarak minimum dan ambil subset $K$ tetangga terdekat: $\mathcal{N}_K(x)$.
3. Lakukan penalaran:
   - **Tugas 1 (Klasifikasi)**: Menghitung fungsi keanggotaan probabilitas:
     $$P(y = c \mid x) = \frac{1}{K} \sum_{i \in \mathcal{N}_K(x)} \mathbb{I}(y_i = c)$$
   - **Tugas 2 (Regresi)**: Agregasi rata-rata kontinu:
     $$\hat{y}(x) = \frac{1}{K} \sum_{i \in \mathcal{N}_K(x)} y_i$$

### 1.2. Alasan Pemilihan Metode KNN
1. **Transparansi & Kemudahan Penjelasan (*Explainable AI*)**: Hasil inferensi KNN dapat dijelaskan secara visual dan intuitif kepada pemilik kedai ("Rekomendasi Cappuccino diberikan karena pada 20 transaksi terdekat di jam serupa pada hari Senin, 14 pelanggan memesan varian kopi hangat").
2. **Kesesuaian dengan Skala Data**: Dataset bersih Lower Manhattan memiliki 47.782 baris pada ruang ciri berdimensi rendah ($d \le 6$). Struktur pohon data (*KD-Tree*) memungkinkan pencarian tetangga terdekat dalam waktu sub-milidetik ($O(\log N)$).
3. **Fleksibilitas Multi-Tugas**: Kerangka matematika yang sama dapat digunakan secara elegan untuk menyelesaikan klasifikasi kategori maupun regresi volume secara serempak.

---

## 2. Perbandingan Singkat dengan Alternatif Pendekatan AI

Tanpa membuat klaim empiris spekulatif terhadap percobaan yang tidak dilaksanakan, perbandingan teoretis adalah sebagai berikut:

| Karakteristik | K-Nearest Neighbors (KNN) | Sistem Pakar Murni (Expert System) | Jaringan Saraf Tiruan (Deep Learning) |
|---|---|---|---|
| **Kebutuhan Data** | Sedang (menyimpan vektor instans) | Nol (hanya butuh aturan manual) | Sangat Besar (butuh jutaan parameter) |
| **Kemudahan Adaptasi** | Tinggi (cukup tambahkan data baru) | Rendah (harus ubah aturan kode) | Rendah (butuh *retraining* lama) |
| **Kompleksitas Komputasi Inferensi** | $O(N)$ atau $O(\log N)$ dengan pohon | $O(R)$ sangat cepat | $O(W)$ perkalian matriks |
| **Tingkat Transparansi** | Tinggi (berbasis tetangga nyata) | Sangat Tinggi (rantai logika *IF-THEN*) | *Black Box* (sukar diinterpretasi) |

---

## 3. Sistem Hibrida: Integrasi Penalaran Berbasis Aturan (Rule-Based Fallback)

Kecerdasan Buatan modern tidak hanya mengandalkan model statistik probabilitas, melainkan memadukannya dengan **sistem berbasis aturan (*Rule-Based Reasoning*)** untuk menjamin keamanan operasional (*safety & reliability*).

### Logika Aturan Bisnis (*Business Rules Engine*):
1. **Aturan Jam Buka / Tutup (*Operational Hours Gate*)**:
   - `IF (jam < 6 OR jam >= 21) THEN nonaktifkan rekomendasi aktif; kembalikan status "Toko Tutup".`
2. **Aturan Kegagalan Jaringan / Layanan (*Failure Recovery Rule*)**:
   - `IF (Flask response status != 200 OR response_time > 3000ms) THEN aktifkan mode Fallback Heuristik; baca data ranking popularitas_segmen.json.`
3. **Aturan Ketersediaan Stok (*Inventory Constraint Rule*)**:
   - `IF (produk_terpilih.stok == 0 OR produk_terpilih.status == 'nonaktif') THEN lewati produk tersebut; pilih peringkat produk berikutnya dalam kategori.`
4. **Aturan Diversifikasi Menu (*Diversity Rule*)**:
   - `IF (kategori_rekomendasi == kategori_sebelumnya) THEN batasi maksimal 2 item per kategori agar rekomendasi menyajikan variasi produk (misal: kopi + kue).`
