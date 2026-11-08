# Evaluasi tambahan (dihitung ulang dari dataset_bersih.csv)

## Tugas 1 — klasifikasi kategori
- Akurasi KNN (K=20): 34.68% | baseline mayoritas: 38.10%
- Top-3: KNN 79.11% vs baseline tiga kategori terpopuler 83.73% (per segmen: 83.73%)
- Top-2: KNN 62.48% vs baseline 67.22%

## Tugas 2 — keramaian

| Skema | Model | MAE | RMSE | R² |
|---|---|---|---|---|
| tugas2_acak | knn_dengan_bulan (K=17) | 6.15 | 8.18 | 0.617 |
| tugas2_acak | knn_tanpa_bulan (K=20) | 7.68 | 10.38 | 0.384 |
| tugas2_acak | baseline_rata_per_jam | 7.42 | 10.09 | 0.417 |
| tugas2_acak | baseline_rata_per_jam_bulan_terakhir | 10.82 | 13.54 | -0.050 |
| tugas2_temporal_JanMei_Jun | knn_dengan_bulan (K=18) | 8.73 | 11.91 | 0.501 |
| tugas2_temporal_JanMei_Jun | knn_tanpa_bulan (K=20) | 12.25 | 16.41 | 0.052 |
| tugas2_temporal_JanMei_Jun | baseline_rata_per_jam | 11.93 | 15.89 | 0.111 |
| tugas2_temporal_JanMei_Jun | baseline_rata_per_jam_bulan_terakhir | 8.14 | 10.94 | 0.578 |
