# DEPLOYMENT MICROSERVICE FLASK DAN INTEGRASI SISTEM

**Mata Kuliah**: Machine Learning  
**Komponen**: Layanan REST API Microservice (`ml/app_flask.py`)  
**Integrasi**: Klien CodeIgniter 4 (`app/Libraries/FlaskRecommendationClient.php`)  

---

## 1. Arsitektur Komunikasi Sistem

Sistem mengadopsi arsitektur terdistribusi *loosely coupled* antara antarmuka web dan mesin kecerdasan buatan:
- **Web Application Core**: CodeIgniter 4 (PHP 8.2 / Web Server Apache XAMPP).
- **Machine Learning Engine**: Python Flask Microservice yang berjalan secara lokal pada port **5001** (`http://127.0.0.1:5001`).
- **Protokol Pertukaran**: RESTful JSON melalui HTTP GET/POST via cURL.

```
+---------------------------+             HTTP GET/POST              +-----------------------------+
|    CodeIgniter 4 (Web)    | -------------------------------------> |    Flask Microservice       |
|  - RekomendasiAPI.php     |                                        |  - app_flask.py (Port 5001) |
|  - FlaskRecommendation... | <------------------------------------- |  - predict.py (KNN Models)  |
+---------------------------+              JSON Response             +-----------------------------+
              |                                                                     |
     (Fallback Otomatis                                                    (Memuat Model .pkl)
     Bila Layanan Mati)                                                             |
              v                                                                     v
   popularitas_segmen.json                                                model_rekomendasi.pkl
                                                                          model_keramaian.pkl
```

---

## 2. Spesifikasi Endpoint REST API

### 2.1. `GET /health`
- **Fungsi**: Memeriksa ketersediaan dan status detak jantung (*health check*) layanan Flask.
- **Contoh Permintaan**:
  ```http
  GET http://127.0.0.1:5001/health HTTP/1.1
  Host: 127.0.0.1:5001
  ```
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "UP",
    "service": "Lower Manhattan Coffee Shop ML Microservice",
    "timestamp": "2026-11-02T08:20:00.123456",
    "version": "2.0.0-planB"
  }
  ```

### 2.2. `GET /model-info`
- **Fungsi**: Mengembalikan informasi metadata model, versi pustaka, tanggal pelatihan, dan hash integritas dataset.
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "success",
    "metadata": {
      "model_version": "2.0.0-planB",
      "timestamp_trained": "2026-10-28 13:08:19",
      "store_location": "Lower Manhattan",
      "store_id": 5,
      "training_rows": 47782,
      "dataset_sha256": "fade0fa022a56467b1071429f265e3252d7069fbc02e29d24381578d6625f7bd"
    }
  }
  ```

### 2.3. `GET /api/rekomendasi`
- **Fungsi**: Menghasilkan rekomendasi daftar menu dan estimasi keramaian berdasarkan parameter jam dan menit.
- **Parameter Kueri**:
  - `jam`: Integer (0–23), contoh `?jam=9`.
  - `menit`: Integer (0–59), contoh `&menit=30`.
- **Contoh Permintaan**:
  ```http
  GET http://127.0.0.1:5001/api/rekomendasi?jam=9&menit=30 HTTP/1.1
  ```
- **Contoh Respons (JSON)**:
  ```json
  {
    "status": "success",
    "data": {
      "jam": 9,
      "menit": 30,
      "hari": "Senin",
      "segmen": "pagi",
      "status_keramaian": "Ramai",
      "estimasi_transaksi_per_jam": 34.5,
      "rekomendasi": [
        {
          "peringkat": 1,
          "kategori": "Coffee",
          "nama_produk": "Brewed Coffee",
          "skor_probabilitas": 0.45
        },
        {
          "peringkat": 2,
          "kategori": "Bakery",
          "nama_produk": "Croissant",
          "skor_probabilitas": 0.25
        }
      ]
    }
  }
  ```

### 2.4. `POST /predict-crowd`
- **Fungsi**: Memprediksi secara spesifik volume transaksi per jam untuk perencanaan operasional kasir.
- **Payload (JSON)**:
  ```json
  {
    "jam": 14,
    "hari": 2,
    "bulan": 5
  }
  ```
- **Respons (JSON)**:
  ```json
  {
    "status": "success",
    "estimasi_transaksi": 24.8,
    "label_keramaian": "Sedang",
    "ambang_batas": {
      "sepi_max": 11.0,
      "sedang_max": 28.0
    }
  }
  ```

---

## 3. Mekanisme Toleransi Kesalahan (Fallback di CI4)

Untuk menjamin layanan pelanggan tidak pernah terputus meskipun microservice Flask mengalami gangguan teknis atau mati, diimplementasikan mekanisme *fail-safe* pada `app/Controllers/RekomendasiAPI.php`:

1. **Batas Waktu Koneksi Singkat (Timeout Gate)**:
   - cURL dikonfigurasi dengan batas waktu koneksi **1 detik** dan batas waktu transfer total **3 detik**.
2. **Fallback Berbasis Pengetahuan Historis (*Heuristic Fallback*)**:
   - Jika panggilan cURL gagal atau mengembalikan status HTTP selain 200, kontroler secara otomatis beralih memuat berkas konfigurasi lokal `ml/config/popularitas_segmen.json`.
   - Sistem tetap menyajikan rekomendasi menu berdasarkan modus historis segmen waktu tersebut.
   - Indikator peringatan dicatat pada log internal CodeIgniter tanpa memunculkan galat pada layar pelanggan.
3. **Keamanan CORS (Cross-Origin Resource Sharing)**:
   - Layanan Flask membatasi origin hanya pada domain lokal resmi (`http://localhost:8080`, `http://127.0.0.1:8080`, `http://localhost`, `http://127.0.0.1`) untuk mencegah eksploitasi antardomain.
