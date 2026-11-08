# PROMPT AI AGENT — GAMBAR QR DEMO "LOWER MANHATTAN COFFEE SHOP" (RESOLUSI HD) MENGGANTIKAN QRIS LAMA

Prompt ini melengkapi `PROMPT_FIX_HALAMAN_QRIS.md`. Jalankan setelah (atau bersamaan dengan) prompt tersebut. Salin seluruh isi di bawah garis ini ke AI agent.

---

## A. KONTEKS DAN KEPUTUSAN DESAIN (BACA DULU)

Gambar QRIS yang saat ini terunggah menampilkan kartu merchant berlogo bank dengan nama "WARKOP BAHAGIA". Pemilik proyek ingin tampilannya berganti menjadi "Lower Manhattan Coffee Shop" dalam kualitas HD.

**Mengedit teks di gambar QRIS asli TIDAK dilakukan**, dan agent dilarang melakukannya (upscale, retouch, tempel teks, ganti nama pada kartu). Alasannya: isi kode QR (termasuk nama merchant dan rekening tujuan) tidak berubah ketika teks di sekitarnya diganti. Hasilnya, pembayar melihat nama "Lower Manhattan Coffee Shop" tetapi uangnya masuk ke merchant lain. Itu menyesatkan dan menyerupai pemalsuan alat pembayaran. Logo bank dan logo QRIS juga merek pihak lain yang tidak boleh ditiru.

**Yang dikerjakan:** membuat gambar QR **DEMO** baru, resolusi tinggi, berisi nama toko yang benar, dengan isi QR berupa teks biasa yang jelas bukan pembayaran, tanpa logo bank/QRIS/e-wallet. Ini cocok karena toko adalah proyek akademik dengan dataset simulasi.

Jika pemilik kelak ingin menerima pembayaran sungguhan, QRIS baru harus diminta dari penyedia (bank/PJSP) atas nama usaha yang benar, lalu diunggah apa adanya lewat halaman admin tanpa diubah. Itu di luar tugas ini.

Penulisan nama: tampilkan **"Lower Manhattan Coffee Shop"** (ejaan "Coffee"). Bentuk "LowerManhattanCoffeShop" hanya dipakai sebagai nama berkas/database teknis (mis. `lowermanhattancoffeshop`), bukan teks yang tampil. Catat keputusan ini di dokumen.

## ATURAN KERAS

1. Dilarang mengubah, mengunggah-ulang, meningkatkan resolusi, memotong, atau menimpa gambar QRIS asli. Jangan mendekode atau menampilkan isi QR asli. Jangan menyalin nama merchant, NMID, atau rekening dari gambar lama ke mana pun.
2. Gambar baru tidak boleh memuat: logo QRIS/GPN, logo bank (BTN, balé, dll.), logo e-wallet (GoPay, OVO, DANA, ShopeePay, dll.), maupun format isi QRIS/EMVCo (string yang diawali `000201`).
3. Dilarang mengubah logika pembayaran, token pesanan, polling status, atau data di database utama. Uji hanya di DB test; backup dulu. Jangan mengedit migrasi lama.
4. Buat branch `qr-demo-lowermanhattan` dan tag `backup-sebelum-qr-demo` sebelum mengubah apa pun.
5. Jangan menampilkan isi `.env`, password, hash, token, atau kunci.
6. Jangan menyatakan "selesai" tanpa bukti (keluaran perintah, hasil decode, ukuran berkas, screenshot).

---

## B. FASE 0 — AUDIT (READ-ONLY)

1. Buat branch/tag pengaman.
2. Cari berkas gambar QRIS lama: pengaturan `qris` di DB test, berkas di `public/uploads/` dengan nama berbau qris/barcode, `uploads/qris-placeholder.svg`, dan berkas gambar yang dirujuk view `customer/payment_qris.php` dan `admin/settings.php`. Catat NAMA berkas dan ukuran saja.
3. Periksa apakah gambar QRIS lama **terlacak git** (`git ls-files`, `git log --stat -- <path>`) dan apakah repo bersifat publik. Jika terlacak, catat di laporan (jangan menghapus riwayat; hanya laporkan agar pengguna memutuskan).
4. Periksa pustaka yang tersedia: Python (`segno`, `qrcode`, `Pillow`, `cairosvg`, `opencv-python` atau `pyzbar`) dan PHP (`endroid/qr-code` sudah dipakai untuk QR meja). Pilih satu jalur dan jelaskan alasannya; pasang paket hanya di lingkungan virtual/composer dev, bukan global.
5. Keluaran: `docs/audit_qr_demo.md`.

## C. FASE 1 — SKRIP PEMBUAT GAMBAR DEMO

Buat `tools/generate_demo_qr.py` (atau `.php` bila memakai Endroid), dapat dijalankan ulang (reprodusibel, tanpa angka acak) dengan parameter nama toko dan teks demo.

**Isi QR (payload)** berupa teks biasa, bukan format QRIS:
`DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan`
Pastikan payload tidak diawali `000201` dan tidak memuat NMID, rekening, atau nama bank.

**Spesifikasi QR:** koreksi kesalahan level H, quiet zone minimal 4 modul, modul persegi dan tajam (tanpa anti-aliasing), warna hitam pekat di atas putih pekat (kontras maksimum, tanpa gradasi/logo di tengah).

**Spesifikasi kartu (potret):**
- Kanvas 2048 × 2896 piksel (rasio A-series), latar putih/krim `#FBF6EE`, sudut membulat, bingkai espresso `#2B1B12`.
- Bagian atas: teks "Lower Manhattan Coffee Shop" (huruf tebal berserif, warna `#2B1B12`), di bawahnya "MODE DEMO" pada pita berwarna merah-bata `#A63D2F` dengan teks putih.
- Tengah: QR (lebar sekitar 60% kanvas).
- Bawah: "Bukan pembayaran sungguhan. Hanya untuk demonstrasi aplikasi." dan baris kecil "Proyek akademik – UBSI Kaliabang".
- Tanpa logo pihak ketiga, tanpa teks "QRIS" bergaya logo, tanpa NMID.
- Font: gunakan font bebas lisensi yang tertanam (mis. Playfair Display / Inter dari berkas lokal, atau fallback sistem) dan tuliskan sumber/lisensinya di dokumen. Pastikan teks tidak terpotong dan terbaca pada 390 px lebar.

**Keluaran:**
- `public/uploads/qris-demo-lowermanhattan.png` (2048 × 2896, PNG, 300 dpi, tanpa transparansi bermasalah) — jaga ukuran berkas wajar (< 1,5 MB; optimalkan tanpa menurunkan ketajaman; batas unggah admin 2 MB).
- `public/uploads/qris-demo-lowermanhattan.svg` (vektor, untuk cetak/zoom tanpa pecah).
- Perbarui `uploads/qris-placeholder.svg` agar konsisten (versi demo yang sama), tetapi jangan menghapus berkas lama lain.

## D. FASE 2 — VERIFIKASI GAMBAR (WAJIB, DENGAN BUKTI)

1. Ukuran piksel, dpi, ukuran berkas, dan format (`file`/`identify`).
2. **Uji pindai:** dekode PNG dan SVG (dirender ke PNG) dengan pustaka decoder; payload hasil dekode harus persis sama dengan payload yang dimaksud dan **bukan** diawali `000201`. Tunjukkan keluaran dekode.
3. Uji pindai ulang setelah gambar diperkecil ke 390 px lebar dan 600 px lebar (simulasi layar ponsel); QR harus tetap terbaca.
4. Pemeriksaan kontras teks (rasio ≥ 4,5:1) dan pemeriksaan visual: tidak ada teks terpotong atau tumpang tindih.
5. Pemeriksaan bahwa tidak ada logo pihak ketiga di gambar (tinjau visual dan jelaskan).
6. Simpan pratinjau `docs/screenshots/qr_demo/kartu_qr_demo.png` (versi ringan untuk dokumentasi).

## E. FASE 3 — INTEGRASI KE APLIKASI

1. Atur pengaturan `qris` (di DB TEST) ke `uploads/qris-demo-lowermanhattan.png` dan `qris_mode` = `demo` (kunci dari `PROMPT_FIX_HALAMAN_QRIS.md`; bila belum diterapkan, terapkan bagian itu atau tulis temuanmu). Sediakan `database/fix_qris_gambar.sql` (idempoten, dengan README satu paragraf) agar pengguna menjalankannya sendiri di DB utama setelah backup. Jangan menulis ke DB utama.
2. Halaman `/payment-qris/...`: gambar tampil tajam di layar 1× dan 2× (gunakan `max-width:100%`, `height:auto`, atribut `width/height` agar tidak melompat). Pertahankan `onerror` ke placeholder. Alt: "Kode QR demo untuk Lower Manhattan Coffee Shop".
3. Pita "MODE DEMO — bukan pembayaran sungguhan" di halaman tetap tampil (dari prompt QRIS) sehingga pesan demo muncul di halaman dan di gambar.
4. Pratinjau di `/admin/settings` menampilkan gambar baru; unggahan tetap dibatasi JPG/PNG/WebP dan 2 MB.
5. Uji manual terhadap DB test: buat pesanan QRIS, buka halaman, pastikan gambar baru tampil, lalu ubah pengaturan dan pastikan perubahan berlaku. Screenshot sebelum/sesudah di `docs/screenshots/qr_demo/` (390 px dan 1280 px); bila tidak bisa dijalankan, tandai "tidak dijalankan" beserta alasan, jangan membuat gambar palsu.
6. Jalankan seluruh suite PHPUnit dan tunjukkan tidak ada regresi; tambahkan satu tes yang memastikan halaman memuat gambar demo dan pita demo saat `qris_mode=demo`.

## F. FASE 4 — GAMBAR LAMA & KEBERSIHAN

1. Gambar QRIS lama (merchant "WARKOP BAHAGIA"): jangan dihapus otomatis dari server. Jika terlacak git, usulkan langkah `git rm --cached` + entri `.gitignore` untuk `public/uploads/qris-*` yang bukan demo, dan jelaskan bahwa riwayat git tetap memuatnya (pengguna yang memutuskan perlu menulis ulang riwayat atau tidak). Lakukan `git rm` hanya jika terbukti tidak dirujuk kode atau tes; jika ragu, pertahankan dan laporkan.
2. Pastikan tidak ada teks "WARKOP BAHAGIA" atau logo bank tersisa pada aset yang dirujuk aplikasi (cari di `public/uploads/`, `public/assets/`, view). Dokumentasi sejarah dibiarkan.

## G. DOKUMENTASI

- `docs/qr_demo.md`: alasan tidak mengedit QRIS asli, cara membuat ulang gambar (perintah persis), spesifikasi, hasil uji pindai, cara mengganti ke QRIS sungguhan (minta dari bank/PJSP, unggah apa adanya, set `qris_mode=live`), dan peringatan soal merek dan pembayaran palsu.
- Tambahkan satu entri di `CHANGELOG.md` dan perbarui README bagian pengaturan.

## H. LAPORAN AKHIR

Ringkas dan jujur: berkas dibuat/diubah, hasil dekode QR dan ukuran gambar, hasil tes, screenshot, status gambar lama (terlacak git atau tidak), langkah manual pengguna (jalankan SQL di DB utama setelah backup, putuskan soal riwayat git), serta pengingat bahwa gambar ini demo dan bukan alat pembayaran.
