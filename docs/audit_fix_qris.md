# LAPORAN AUDIT IDENTITAS & HALAMAN PEMBAYARAN QRIS (FASE 0 — READ-ONLY)

**Repositori**: `lower-manhattan-coffee-shop`  
**Waktu Audit**: 2026-11-02 09:32:00 WIB  
**Branch Git**: `fix-qris-identitas`  
**Tag Git**: `backup-sebelum-fix-qris`  
**Backup Basis Data**: `backup_settings_sebelum_fix_qris.sql` (Dump tabel `settings` via mysqldump)

---

## 1. Ringkasan Eksekutif & Temuan Masalah

Berdasarkan pemeriksaan langsung pada basis kode dan tangkapan layar pembayaran QRIS (`/payment-qris/{id_order}/{id_meja}`):

1. **Hardcode Alamat & NMID di View**:
   - Berkas: `app/Views/customer/payment_qris.php` (baris 338–339).
   - Teks tertanam:
     - Alamat: `"Jl. Kaliabang No.8, Perwira, Bekasi Utara, Kota Bekasi, Jawa Barat"`
     - NMID: `"NMID: ID102435780413"`
   - Dampak: Nilai ini statis dan tidak berubah sekalipun admin mengedit pengaturan identitas toko.
2. **Duplikasi / Format Membingungkan Judul Merchant**:
   - Berkas: `app/Views/customer/payment_qris.php` (baris 337).
   - Kode: `<?= esc($merchant_name) ?>, <?= esc(ucwords(strtolower($merchant_address))) ?>`.
   - Menghasilkan teks: `"Lower Manhattan Coffee Shop, Bekasi Utara"` lalu diikuti alamat panjang di bawahnya.
3. **Ketergantungan Kunci Pengaturan Lama**:
   - Berkas: `app/Controllers/PaymentQris.php` (baris 28–29).
   - Menggunakan kunci `nama_warkop` dan nilai cadangan hardcode `'Bekasi Utara'`.
4. **Gambar QRIS yang Tampil di Halaman**:
   - Pengaturan aktif di database: `qris => uploads/1792432539_332f0ac0cf359c9c21b1.jpeg`.
   - Gambar tersebut menampilkan merchant kartu *"balé by btn"* dengan nama **"WARKOP BAHAGIA"** (NMID: ID102435780413), yang merupakan sisa artefak proyek lama dan **tidak sesuai dengan entitas Lower Manhattan Coffee Shop**.

---

## 2. Tabel Pemetaan Temuan di Seluruh Repositori

| No | Lokasi Berkas | Baris | Teks / Kunci Terdeteksi | Kategori Temuan | Tindakan yang Direncanakan |
|---|---|---|---|---|---|
| 1 | `app/Views/customer/payment_qris.php` | 338 | `"Jl. Kaliabang No.8, Perwira, Bekasi Utara..."` | (a) Hardcode Tampilan | Ganti dengan `esc($alamat_lengkap)` dari pengaturan; sembunyikan jika kosong. |
| 2 | `app/Views/customer/payment_qris.php` | 339 | `"NMID: ID102435780413"` | (a) Hardcode Tampilan | Ganti dengan `NMID: esc($nmid)`; sembunyikan jika kosong/demo. |
| 3 | `app/Views/customer/payment_qris.php` | 337 | `$merchant_name, ucwords($merchant_address)` | (a) Format Membingungkan | Ganti menjadi baris nama toko mandiri: `esc($nama_toko)`. |
| 4 | `app/Controllers/PaymentQris.php` | 28–30 | `nama_warkop`, cadangan `'Bekasi Utara'` | (b) Kunci Lama & Fallback | Gunakan `StoreIdentity::get()`, hapus fallback `'Bekasi Utara'`, gunakan `'Lower Manhattan Coffee Shop'`. |
| 5 | `app/Views/kasir/bayar.php` | 250 | `"mutasi saldo di aplikasi rekening/QRIS warkop"` | (a) Teks Tampilan Kasir | Ganti kata `"warkop"` menjadi `"toko / QRIS"` yang netral. |
| 6 | `app/Controllers/Admin/Setting.php` | 45 | Form field whitelist `nama_warkop` | (b) Kunci Pengaturan | Tambahkan dukungan kunci baru: `nama_toko`, `alamat_lengkap`, `nmid`, `qris_mode`. Simpan kompatibilitas ke `nama_warkop`. |
| 7 | `app/Views/admin/settings.php` | 134 | Input field `nama_warkop` | (a) Form Tampilan Admin | Perbarui label menjadi "Nama Toko / Coffee Shop", tambahkan input Alamat Lengkap, NMID, dan Mode QRIS. |
| 8 | Berkas Dokumen Sejarah (`CHANGELOG.md`, `Prompt-Revisi-Program.md`, dll.) | Beragam | "Warkop Bahagia", "Plan A", dll. | (e) Dokumentasi Riwayat | **Sengaja Dipertahankan** (Aturan Bagian E). |

---

## 3. Rencana Arsitektur Perubahan (Fase 1 & Fase 2)

1. **Kelas Pembantu Sentral `app/Libraries/StoreIdentity.php`**:
   - Membaca pengaturan dengan hierarki:
     - Nama Toko: `nama_toko` → `nama_warkop` → `'Lower Manhattan Coffee Shop'`.
     - Alamat Lengkap: `alamat_lengkap` → `alamat` → `''` (kosong).
     - NMID: `nmid` (validasi regex `^[A-Z]{2}[0-9A-Z]{8,20}$`) → jika tidak valid/kosong, kembalikan `null`.
     - Mode QRIS: `qris_mode` (`'demo'` atau `'live'`). Default: `'demo'`.
     - Gambar QRIS: jika mode `'demo'` atau file tidak ada, arahkan ke `'uploads/qris-placeholder.svg'` dan set `is_demo = true`.
2. **Toleransi & Migrasi Idempoten**:
   - Menyediakan migrasi baru `App\Database\Migrations\AddStoreIdentityAndQrisSettings` yang idempoten (hanya insert bila kunci belum ada).
   - Menyediakan skrip SQL mandiri `database/fix_qris_pengaturan.sql` untuk pengguna menjalankan di DB utama setelah backup.
3. **Pemberitahuan Mode Demo**:
   - Menambahkan pita visual (*badge/banner*) jelas pada mode demo: `"MODE DEMO — bukan pembayaran sungguhan"`.

