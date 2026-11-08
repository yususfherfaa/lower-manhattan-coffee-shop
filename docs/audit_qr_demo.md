# Audit aset QR demo

Audit read-only dilakukan pada 2 November 2026. Branch `qr-demo-lowermanhattan` dan tag `backup-sebelum-qr-demo` dibuat sebelum perubahan; tag menunjuk commit awal `e1a4c56`.

## Aset dan database

| Lokasi | Ukuran | Status Git |
|---|---:|---|
| `public/uploads/qris.png` | 8.995 byte | Terlacak; ada di commit awal |
| `public/uploads/qris-placeholder.svg` | 769 byte | Terlacak; ada di commit awal |
| `public/uploads/1792432539_332f0ac0cf359c9c21b1.jpeg` | 147.788 byte | Tidak terlacak |

Pemeriksaan hanya mencatat nama dan ukuran; isi QR/gambar lama tidak dibuka atau didekode. `.gitignore` mengabaikan unggahan runtime, tetapi SVG placeholder dan `qris.png` tetap terlacak. Repositori GitHub `yususfherfaa/lower-manhattan-coffee-shop` terdeteksi publik. Riwayat tidak diubah. Aset lama dipertahankan karena keputusan penghapusan dan penulisan ulang riwayat memerlukan persetujuan pemilik.

Konfigurasi database test pada `phpunit.xml.dist` dikomentari. Satu tes integrasi lama secara eksplisit menimpa nama database menjadi `warkop_bahagia_test`, memakai host/kredensial konfigurasi aplikasi, lalu menulis log rekomendasi. Nilai pengaturan `qris` tidak dibaca dan tidak ada koneksi ke database utama yang dicoba. Tidak ada backup test DB terverifikasi untuk uji QR manual.

## Pustaka dan keputusan

- Python yang tersedia adalah 3.13.14; Pillow tersedia secara global, sedangkan `segno`, `qrcode`, `cairosvg`, OpenCV, dan `pyzbar` tidak tersedia.
- PHP 8.2.12 tersedia; Composer mendeklarasikan `endroid/qr-code` 6.x. Namun, alur ini memerlukan komposisi kartu PNG/SVG dan uji pindai multi-resolusi, sehingga generator Python memakai `qrcode`, Pillow, dan ZXing C++ di virtual environment proyek; SVG dirender oleh Edge/Chrome yang tersedia di sistem. Tidak ada paket yang dipasang secara global.
- Perintah pemasangan dan generator dicatat di [qr_demo.md](qr_demo.md).

## Kesimpulan audit

Mode demo sebelumnya dapat memilih gambar dari pengaturan yang ada, sehingga berisiko menampilkan QR tersimpan lama bersama peringatan demo. Perubahan aplikasi mengunci mode demo ke aset demo, mempertahankan batas upload JPG/PNG/WebP 2 MB, dan tidak mengubah alur pesanan, token, polling, migrasi lama, atau database utama.
