# MODUL PRAKTIKUM KECERDASAN BUATAN — LOWER MANHATTAN COFFEE SHOP

Dokumen ini merupakan indeks panduan untuk seluruh materi tugas mata kuliah **Kecerdasan Buatan (Artificial Intelligence)** pada repositori `lower-manhattan-coffee-shop`.

---

## 1. Peta Dokumen & Berkas Pendukung

Folder ini memuat 6 dokumen bab kajian akademik, diagram alir sistem cerdas (.mmd), skenario uji kasus, serta bukti verifikasi:

| No | Berkas Bab | Topik Pembahasan Utama | Rujukan Kunci |
|---|---|---|---|
| 01 | [`01_deskripsi_sistem_cerdas.md`](01_deskripsi_sistem_cerdas.md) | Paradigma Agen Cerdas PEAS, persepsi, pengetahuan, penalaran, aksi | Batasan lingkup (*in-scope* / *non-scope*) |
| 02 | [`02_representasi_pengetahuan.md`](02_representasi_pengetahuan.md) | Taksonomi 9 kategori, segmentasi waktu, aturan kuantil keramaian | `popularitas_segmen.json`, ambang sepi/ramai |
| 03 | [`03_metode_ai_knn_dan_aturan.md`](03_metode_ai_knn_dan_aturan.md) | Pembelajaran berbasis instans (KNN), perbandingan metode, sistem aturan | Jarak Euclidean, hybrid reasoning |
| 04 | [`04_alur_inferensi.md`](04_alur_inferensi.md) | Alur komputasi lengkap, penanganan kegagalan (*fail-safe*), contoh numerik | Diagram alir Mermaid, studi kasus Senin 08:30 |
| 05 | [`05_pengujian_sistem.md`](05_pengujian_sistem.md) | Pengujian fungsional, nilai batas pergantian segmen, uji ketahanan Flask | 10 skenario uji empiris |
| 06 | [`06_analisis_etika_dan_keterbatasan.md`](06_analisis_etika_dan_keterbatasan.md) | Etika AI, privasi konsumen (*zero PII*), bias geografis data simulasi | Tanggung jawab manusia (*Human-in-the-Loop*) |

---

## 2. Struktur Direktori Praktikum

```
matkul/kecerdasan-buatan/
├── README.md                      # Indeks modul (berkas ini)
├── 01_deskripsi_sistem_cerdas.md
├── 02_representasi_pengetahuan.md
├── 03_metode_ai_knn_dan_aturan.md
├── 04_alur_inferensi.md
├── 05_pengujian_sistem.md
├── 06_analisis_etika_dan_keterbatasan.md
├── diagram/
│   ├── alur_inferensi.mmd
│   └── arsitektur_sistem.mmd
├── skenario_uji/
│   └── hasil_pengujian_skenario.csv
└── bukti/
    ├── contoh_respons_api.json
    └── status_verifikasi_sistem.txt
```

---

## 3. Komponen Utama Agen Cerdas

- **Algoritma Utama**: K-Nearest Neighbors ($K=20$ untuk rekomendasi menu, $K=17$ untuk prediksi volume kepadatan per jam).
- **Aturan Bisnis (*Heuristics*)**: Fallback otomatis ke modus historis segmen waktu saat terjadi keterlambatan atau kegagalan jaringan HTTP microservice Flask.
- **Prinsip Operasional**: Menjunjung tinggi privasi konsumen tanpa melacak data identitas individu pelanggan, serta menyajikan transparansi bahwa rekomendasi dibangkitkan dari dataset publik simulasi Maven Roasters Lower Manhattan.
