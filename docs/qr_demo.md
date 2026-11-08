# Kartu kode QR demo

Kartu ini adalah ilustrasi demonstrasi, bukan alat pembayaran. QR berisi teks biasa `DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan`; bukan payload pembayaran. Gambar QR asli tidak diedit, di-upscale, dipotong, didekode, atau ditimpa. Mengganti teks pada kartu pembayaran tanpa mengganti QR tujuan dapat menyesatkan pembayar dan menyerupai pemalsuan alat pembayaran.

Nama tampilan yang digunakan adalah **Lower Manhattan Coffee Shop** (ejaan “Coffee”). Bentuk `LowerManhattanCoffeShop` hanya untuk identifier teknis, bukan teks yang tampil. Kartu tidak memakai logo bank, QRIS/GPN, e-wallet, ataupun logo pihak ketiga.

## Membuat ulang

Python 3.13.14 digunakan saat verifikasi. Buat virtual environment dan pasang dependensi yang dipatok:

```powershell
python -m venv .venv-qr-demo
.\.venv-qr-demo\Scripts\Activate.ps1
python -m pip install -r tools\requirements-qr-demo.txt
python tools\generate_demo_qr.py --store-name "Lower Manhattan Coffee Shop" --demo-label "MODE DEMO"
```

Generator deterministik (tidak memakai angka acak) dan sekaligus memindai keluaran PNG, SVG yang dirender oleh Microsoft Edge/Google Chrome headless, dan hasil resize 390/600 px. Font Windows yang dipakai dalam hasil ini adalah `C:\Windows\Fonts\georgiab.ttf` (Georgia Bold, font sistem Microsoft berlisensi proprietary; tidak disalin atau di-embed). Sistem tanpa Georgia memakai DejaVu Serif Bold jika tersedia (DejaVu Fonts License/Bitstream Vera license), lalu serif tebal sistem. Pustaka QR memakai koreksi kesalahan H dan quiet zone empat modul. PNG berformat RGB, berukuran 2048 × 2896, metadata 300 dpi; SVG bersifat vektor. Rasio kontras yang dicetak oleh skrip harus minimal 4,5:1.

Keluaran generator:

- `public/uploads/qris-demo-lowermanhattan.png`
- `public/uploads/qris-demo-lowermanhattan.svg`
- `public/uploads/qris-placeholder.svg`
- `docs/screenshots/qr_demo/kartu_qr_demo.png` (pratinjau ringan)

## Pengaturan dan mode live

Setelah backup database utama dan menyalin aset PNG ke server, tinjau lalu jalankan `database/fix_qris_gambar.sql` pada database yang benar. Skrip itu idempoten dan mengatur `qris` serta `qris_mode=demo`. Jangan jalankan skrip terhadap database yang tidak dipastikan sebagai target. Uji order manual tidak dijalankan karena tidak ada backup DB test yang dapat diverifikasi.

Untuk menerima pembayaran sungguhan, minta QR baru yang diterbitkan bank/PJSP atas nama usaha yang benar. Unggah berkas resmi apa adanya melalui `/admin/settings`, pilih mode **Live**, dan pastikan pengaturan database menunjuk ke aset tersebut. Jangan mengedit isi atau tampilan QR resmi, menempelkan logo pihak lain, atau menampilkan aset demo sebagai alat pembayaran.

## Hasil verifikasi

Generator memindai hasil berikut; seluruh payload sama persis dan bukan payload pembayaran:

```text
SCAN PNG: DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan
SCAN SVG-rendered PNG: DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan
SCAN PNG at 390px: DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan
SCAN PNG at 600px: DEMO|Lower Manhattan Coffee Shop|bukan-pembayaran-sungguhan
CONTRAST espresso on cream: 15.39:1
CONTRAST white on brick: 6.31:1
CONTRAST black on white: 21.00:1
```

Metadata keluaran: PNG 2048 × 2896 RGB, sekitar 300 dpi, 116.118 byte; SVG 23.734 byte. Pratinjau ringan `kartu_qr_demo.png` adalah PNG 600 × 848, 118.733 byte. Pratinjau diperiksa secara visual: nama, pita, QR dan teks bawah tidak terpotong/tumpang tindih; tidak ada logo pihak ketiga atau logo pembayaran.

Uji manual halaman pada DB test serta screenshot halaman 390/1280 px **tidak dijalankan**: tidak ada backup DB test yang dapat diverifikasi. Tidak ada screenshot halaman yang dibuat sebagai pengganti. Pratinjau kartu generator bukan screenshot halaman aplikasi.

PHPUnit: tes terfokus demo QR lulus (1 tes, 6 assertions); tes selain integrasi database lulus (18 tes, 40 assertions). Percobaan full-suite menghasilkan 1 error pada `RekomendasiEndToEndTest` karena Flask di `127.0.0.1:5001` tidak aktif. Tes itu membaca jumlah log di database `warkop_bahagia_test`, lalu gagal saat request Flask sebelum insert log. Tes tidak diulang karena ia menulis ke DB test dan backup belum diverifikasi.

`public/uploads/qris.png` dan placeholder lama sudah terlacak, tetapi tidak dapat dipastikan dari nama saja apakah PNG tersebut adalah gambar pembayaran lama; karena itu aset dipertahankan. Jika pemilik memastikan `qris.png` tidak dirujuk pengaturan/kode/tes dan ingin berhenti melacaknya, langkahnya adalah `git rm --cached public/uploads/qris.png`. Untuk nama lama berpola `qris-*`, aturan `.gitignore` yang disarankan adalah:

```gitignore
/public/uploads/qris-*
!/public/uploads/qris-demo-lowermanhattan.png
!/public/uploads/qris-demo-lowermanhattan.svg
!/public/uploads/qris-placeholder.svg
```

Perubahan tracking ini tidak diterapkan karena pemilik perlu memutuskan; `git rm --cached` tidak menghapus riwayat Git, dan penulisan ulang riwayat memerlukan keputusan terpisah.
