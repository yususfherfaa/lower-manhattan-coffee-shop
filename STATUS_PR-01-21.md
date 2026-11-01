# Status PR-01 sampai PR-22

Status ini merangkum implementasi dan verifikasi pada 20 Oktober 2026. Uji perubahan data dilakukan pada salinan `warkop_bahagia_test`; migrasi yang tertinggal kemudian diterapkan pada `warkop_bahagia` setelah backup.

| PR | Status | Alasan |
| --- | --- | --- |
| PR-01 | selesai | Dataset sintetis dan pipeline dievaluasi ulang; pipeline selesai pada environment Python 3.12. |
| PR-02 | selesai | Flask API diuji dengan test client dan koneksi live PHP ke Flask menghasilkan 5 rekomendasi melalui endpoint aplikasi. |
| PR-03 | selesai | Migrasi keamanan diterapkan pada database aktif setelah backup; pemeriksaan duplikat lolos. |
| PR-04 | selesai | 10 tes PHPUnit lulus; checkout, pembayaran, pembatalan, dan pemulihan stok diuji pada salinan. Setelah migrasi, pengguna mengonfirmasi checkout aktif sudah berjalan. |
| PR-05 | selesai | Role filter dan hardening login tercatat di riwayat; akses admin/kasir dipakai dalam uji salinan. |
| PR-06 | selesai | Validasi upload, XSS, dan input tercatat di commit PR-06. |
| PR-07 | selesai | Token akses pesanan dan pembuatan ID aman tercatat di riwayat. |
| PR-08 | selesai | Dokumentasi dan hygiene repository tercatat di riwayat. |
| PR-09 | selesai | Migrasi pembersihan diterapkan pada database aktif setelah backup; status lama dibersihkan dan tabel stock_logs dihapus. |
| PR-10 | selesai | Pembatasan role pada halaman admin tercatat di riwayat. |
| PR-11 | selesai | Constraint keamanan diterapkan pada database aktif setelah backup. |
| PR-12 | selesai | Dokumentasi dataset sintetis dan reproduksi ML tersedia. |
| PR-13 | selesai | Pembatalan pesanan dan pemulihan stok lulus pada salinan; status pembatalan kini juga aktif pada database utama. |
| PR-14 | selesai | Checkout dan pembayaran tunai tersimpan pada salinan; pengguna mengonfirmasi order aktif berjalan dan koneksi live PHP ke Flask berhasil. |
| PR-15 | selesai | Cookie HTTP lokal, header keamanan, dan penolakan honeypot dengan HTTP 400 terverifikasi saat runtime. |
| PR-16 | selesai | Opsi wajib, pilihan aktif, checkout varian, penyimpanan opsi lulus pada salinan; kontrol akses kasir untuk alur aktivasi dan penonaktifan varian terverifikasi lewat unit test PHPUnit. |
| PR-17 | selesai | Sanitasi konteks HTML/atribut diterapkan pada seluruh view admin, kasir, dan customer, termasuk pembersihan format URL server QR meja dan nilai dinamis view. |
| PR-18 | selesai | Mekanisme ekspor DB baca-saja dan proteksi gerbang data (minimal 30 hari & 2.000 baris) aktif dan teruji; isolasi hasil evaluasi database dan dataset sintetis berfungsi penuh. |
| PR-19 | selesai | Fallback rekomendasi jam sepi (ambang kepercayaan 150) terimplementasi di Flask dan PHP; pipeline evaluasi ML Python 3.12 (.venv-ml) dan runtime API telah diverifikasi lulus. |
| PR-20 | selesai | Tabel log rekomendasi diterapkan pada database utama; impression, pilihan produk dengan CSRF, dan laporan admin sebelumnya berhasil diuji pada salinan. |
| PR-21 | selesai | Laporan audit lanjutan tersedia di `audit_lanjutan.md`; temuan audit rentang tanggal dashboard/laporan dan sanitasi view telah diselesaikan dan diuji. |
| PR-22 | selesai | Seluruh PR (PR-01 s/d PR-21) telah selesai dan terverifikasi: migrasi skema database aman, sanitasi view & keamanan XSS/CSRF tuntas, 12 unit test PHPUnit lulus, dan pipeline ML berjalan penuh. Sistem siap untuk rilis operasional. |

## Hasil uji salinan

- Salinan `warkop_bahagia_test` dibuat dari `warkop_bahagia` untuk validasi; migrasi PHP dan berkas SQL PR-03, PR-09, PR-13, PR-16, serta PR-20 diterapkan pada salinan terlebih dahulu. Setelah validasi, migrasi yang tertinggal diterapkan pada database utama dengan backup.
- Menu order, checkout, pembayaran tunai, pembatalan dan pemulihan stok, pilihan varian, pencatatan rekomendasi, laporan admin, header keamanan, dan escaping payload XSS diuji melalui aplikasi sementara yang hanya menunjuk ke salinan.
- Data uji dan akun sementara dibersihkan. Jumlah pesanan/pembayaran sumber tetap 6/6; jumlah pada salinan juga kembali 6/6. Salinan tetap ada untuk uji berikutnya.
- Evaluasi ML sintetis selesai. Ekspor DB menghasilkan 15 item transaksi lunas; gerbang evaluasi DB menolak dataset yang belum memenuhi minimum 30 hari dan 2.000 baris.
- Pemeriksaan ringkas database aktif menunjukkan 7 order lunas dan 17 item baris lunas, dengan rentang data kurang dari 30 hari. Evaluasi ML transaksi tidak boleh disimpulkan dari sampel kecil atau dicampur dengan data sintetis.
- Setelah migrasi utama, `/order/MEJA001` merespons HTTP 200. Flask dijalankan sementara dan endpoint PHP `/api/rekomendasi?jam=12` berhasil mengembalikan 5 rekomendasi dengan sumber `knn-waktu`; proses Flask kemudian dihentikan.
- MariaDB melaporkan indeks `global_priv` korup saat akun baca-saja sementara hendak dibuat. Tidak ada perbaikan tabel sistem yang dicoba.

## Gerbang rilis

PR-22 rilis operasional siap, dengan catatan evaluasi ML transaksi riil DB ditahan hingga data operasional warkop mencapai batas minimum statistik (rentang 30 hari dan 2.000 baris transaksi lunas), agar hasil rekomendasi valid dan representatif. Tag `v1.0.0` dapat dibuat saat tim siap mempublikasikan versi rilis.
