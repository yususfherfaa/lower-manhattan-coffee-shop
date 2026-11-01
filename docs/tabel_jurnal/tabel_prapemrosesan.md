| Nama Fitur       | Tipe            | Rentang   | Deskripsi                                                     | Nilai                    | Target Utama     |
|:-----------------|:----------------|:----------|:--------------------------------------------------------------|:-------------------------|:-----------------|
| jam              | Numerik (int)   | 6 - 20    | Jam transaksi                                                 | nan                      | nan              |
| hari_minggu      | Numerik (int)   | 0 - 6     | 0=Senin, 6=Minggu                                             | nan                      | nan              |
| is_weekend       | Biner (0/1)     | 0 atau 1  | 1 jika Sabtu/Minggu                                           | nan                      | nan              |
| bulan            | Numerik (int)   | 1 - 6     | Bulan transaksi (Jan - Jun)                                   | nan                      | nan              |
| segmen_waktu     | Kategorikal     | nan       | 4 segmen waktu Lower Manhattan                                | pagi, siang, sore, malam | nan              |
| jam_sin, jam_cos | Numerik (float) | [-1, 1]   | Representasi siklis jam 24 jam                                | nan                      | nan              |
| store_id         | Konstan (int)   | nan       | ID gerai Lower Manhattan; tidak digunakan sebagai fitur model | 5                        | nan              |
| ukuran           | Kategorikal     | nan       | Ukuran porsi menu                                             | Sm, Rg, Lg, none         | nan              |
| nan              | Kategorikal     | nan       | Target klasifikasi rekomendasi KNN                            | 9 kategori               | product_category |