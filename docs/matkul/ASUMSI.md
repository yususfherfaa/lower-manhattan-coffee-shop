# ASUMSI STRUKTUR & KETENTUAN MATA KULIAH

Dokumen ini mencatat seluruh asumsi dan butir *default* yang digunakan dalam penyusunan berkas pada folder `matkul/` (`machine-learning/` dan `kecerdasan-buatan/`).

Karena bagian **B. KETENTUAN RESMI MATA KULIAH** pada prompt dibiarkan kosong oleh pengguna, sesuai Aturan Prioritas (Bagian B poin 2), seluruh struktur direktori, penamaan berkas, komponen laporan, dan skenario pengujian mengacu pada **Struktur Default Bagian D, E, F, dan G**.

---

## 1. Identitas Mata Kuliah & Pengampu
- **Nama Mata Kuliah 1**: Machine Learning (Kode / SKS: Default kurikulum UBSI Informatika)
- **Nama Mata Kuliah 2**: Kecerdasan Buatan (Kode / SKS: Default kurikulum UBSI Informatika)
- **Dosen Pengampu**: Ditandai `[ISI NAMA DOSEN PENGAMPU]` pada berkas laporan gabungan dan dokumentasi untuk diisi secara mandiri oleh mahasiswa saat pengumpulan akhir.
- **Kelas & Kampus**: 15.5A.05, Universitas Bina Sarana Informatika (UBSI) Kampus Kaliabang.
- **Anggota Kelompok**:
  1. Yusuf Herfa Auliansyah (NIM: 15240075)
  2. Yusuf Mansur (NIM: 15240047)
  3. Rizqi Nuzul Firman (NIM: 15240116)

---

## 2. Struktur Berkas & Format Pengumpulan
- **Struktur Folder**:
  - `matkul/machine-learning/` (8 dokumen bab Markdown, subfolder `notebooks/`, `gambar/`, `tabel/`, `bukti/`).
  - `matkul/kecerdasan-buatan/` (6 dokumen bab Markdown, subfolder `diagram/`, `skenario_uji/`, `bukti/`).
  - `matkul/laporan/` (2 berkas laporan monolitik/gabungan siap cetak atau ekspor ke PDF/Word: `Laporan_Machine_Learning.md` dan `Laporan_Kecerdasan_Buatan.md`).
- **Format Bahasa**: Bahasa Indonesia baku ragam ilmiah akademik.
- **Format Pengumpulan Default**: Berkas Markdown yang terstruktur dalam repositori Git lokal/GitHub, siap diekspor menggunakan panduan pandoc/VS Code Markdown PDF jika dosen meminta pengumpulan dalam bentuk PDF/Word atau LMS.

---

## 3. Data & Metodologi
- **Sifat Dataset**: Dataset publik simulasi *Coffee Shop Sales (Maven Roasters)* periode 1 Januari s.d. 30 Juni 2023 difilter khusus cabang Lower Manhattan (`store_id = 5`, 47.782 baris). **Bukan data transaksi operasional nyata UMKM**.
- **Metode**: K-Nearest Neighbors (KNN) dengan $K=20$ untuk klasifikasi rekomendasi kategori menu (Tugas 1) dan $K=17$ untuk regresi jumlah transaksi per jam (Tugas 2).
- **Integritas Metrik**:
  - Akurasi KNN klasifikasi kategori produk tercatat 34,89%, berada di bawah baseline mayoritas (38,10%).
  - Metrik Top-3 Hit Rate KNN mencapai 78,83%, namun baseline sederhana (tiga kategori terpopuler konstan: Coffee, Tea, Bakery) mencapai 83,73% (keseluruhan cabang Lower Manhattan) dan berkisar antara 82,66% - 85,99% di tiap segmen waktu. Sesuai etika ilmiah, 78,83% tidak disajikan sebagai klaim keunggulan melainkan dianalisis secara jujur dan transparan.
  - Sinyal prediktif berbasis waktu terbukti nyata pada volume transaksi per jam (KNN Regresi $R^2 = 0,6191$ vs baseline $0,4173$).

---

## 4. Eksekusi Notebook & Ketergantungan Lingkungan
- Lingkungan eksekusi Python yang digunakan adalah virtual environment lokal `.\.venv-ml` (Python 3.12.6) yang memuat library utama: `scikit-learn==1.8.0`, `pandas==2.2.3`, `numpy==2.1.3`, `flask==3.1.1`, dan `matplotlib==3.10.9`.
- Notebook `.ipynb` disusun dalam format JSON standar Jupyter v4 (`nbformat=4`) dengan keluaran eksekusi yang valid, sel Markdown komprehensif, dan sel kode yang dapat dieksekusi ulang secara deterministik menggunakan *fixed random seed* (`random_state=42`).
