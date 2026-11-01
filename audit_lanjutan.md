# Audit Lanjutan (PR-21)

Audit statis pada controller dan view admin/kasir yang diminta. Tidak ada kode aplikasi yang diubah. Pemeriksaan tidak membaca `.env` dan tidak melakukan uji payload langsung.

## Temuan

| File dan baris | Risiko | Saran |
|---|---|---|
| `app/Controllers/Admin/Dashboard.php:27-28,35-36`; `app/Controllers/Kasir/Dashboard.php:31-32,40-41` | Filter “hari ini” memakai batas akhir `23:59:59` eksklusif (`<`). Transaksi tepat pada detik terakhir tidak ikut, dan timestamp pecahan detik sesudah `23:59:59.000` juga terlewat. | Bentuk rentang setengah terbuka: mulai hari ini inklusif dan sebelum awal hari berikutnya. Pastikan zona waktu aplikasi tetap `Asia/Jakarta`. |
| `app/Controllers/Kasir/Laporan.php:70-78,133-141,216-217,351-353` | Filter tanggal laporan memakai `<= tanggal 23:59:59`. Tanggal akhir dapat kehilangan transaksi dengan pecahan detik setelah waktu tersebut. Controller PDF menetapkan zona waktu Jakarta (baris 191), sedangkan `index()` bergantung pada konfigurasi waktu aplikasi untuk nilai tanggal default. | Gunakan batas sebelum awal hari setelah tanggal akhir. Tegaskan zona waktu aplikasi pada konfigurasi bersama agar tampilan dan PDF konsisten. |
| `app/Views/admin/qr_meja.php:137-138,152,156,160,164` | URL QR dibentuk dari `serverUrl` setting dan dicetak langsung ke `src`, teks, `href`, serta nama unduhan. Nilai setting dapat diedit dari admin; karakter khusus atau skema URL tak diharapkan dapat merusak konteks HTML/tautan. | Validasi skema dan host `serverUrl`; encode data QR; cetak atribut dengan `esc($value, 'attr')` dan teks dengan `esc($value)`. |
| `app/Views/kasir/menu/index.php:269` | `suhu_tetap` dicetak tanpa escape. Nilainya berasal dari data produk; bila data tersimpan berisi markup, browser dapat menafsirkannya sebagai HTML. | Escape nilai, atau petakan nilai enum yang dikenal ke label tetap sebelum dicetak. |
| `app/Views/admin/laporan_stok.php:118` | Nilai stok dicetak langsung di teks badge. Kolom ini semestinya numerik, jadi dampak saat ini rendah, tetapi output tetap bergantung pada data database. | Cast ke integer atau gunakan `esc()` sebelum dicetak. |
| `app/Views/kasir/bayar.php:203-204,237` | Total pesanan dicetak mentah ke atribut `min`, `placeholder`, dan `value`. Sumbernya nilai total database dan semestinya numerik; escape/cast mengurangi risiko bila data tidak sesuai tipe. | Validasi sebagai angka dan cast ke integer atau escape dengan konteks atribut. |

## Pemeriksaan lain

- **Kontrol role:** route laporan dan laporan stok memakai filter `auth` serta `role` (`app/Config/Routes.php:48-56`); grup `/admin` dan `/kasir` juga memakai filter role (`:74-90`). Route QR meja menambahkan filter admin (`:85`). Controller yang ditinjau memiliki pemeriksaan login di dashboard; beberapa controller lain mengandalkan filter route. Dari konfigurasi route yang diperiksa, tidak tampak route tanpa filter untuk aksi admin/kasir tersebut.
- **Input dan SQL:** input tanggal laporan diperiksa format kalender dan urutannya (`app/Controllers/Kasir/Laporan.php:10-21,34-35,198-200`). Nilai filter `id_user` dan parameter where diteruskan melalui Query Builder, bukan dirangkai menjadi SQL mentah. Tetap validasi `id_user` terhadap format/keberadaan user untuk konsistensi respons.
- **N+1:** query di dashboard dan halaman pesanan/detail/struk dilakukan dalam jumlah tetap per request atau memakai join; tidak ditemukan query database yang dijalankan di dalam loop per baris hasil pada file controller yang ditinjau. `LaporanStok` mengambil produk dan aktivitas secara terpisah, bukan query per produk.
- **Uang:** tampilan rupiah memakai `number_format(..., 0, ',', '.')`; nominal diasumsikan bilangan rupiah tanpa pecahan. Query jumlah memakai nilai database dan `array_sum` pada laporan. Pastikan kolom uang bertipe integer/decimal sesuai aturan nominal dan jangan mengubahnya menjadi float bila data kelak mendukung pecahan.
- **Zona waktu:** PDF laporan stok dan laporan penjualan menetapkan `Asia/Jakarta`; dashboard bergantung pada timezone konfigurasi aplikasi. Samakan aturan timezone di konfigurasi aplikasi dan seluruh filter tanggal.

## Cakupan

File controller yang diminta: dashboard admin, laporan stok, setting admin (bagian selain upload), dashboard kasir, detail pesanan, daftar pesanan, dan struk. View yang diperiksa berada di `app/Views/admin` dan `app/Views/kasir`. Audit ini statis; pengujian hak akses dan payload runtime berada di luar PR audit ini.
