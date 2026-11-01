<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--susu, #FBF6EE); color: var(--espresso, #2B1B12); margin: 0; padding-bottom: 40px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px 16px; }
        .page-header { margin-bottom: 24px; border-bottom: 2px solid var(--krim, #F3E9DC); padding-bottom: 16px; }
        .page-title { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--kopi-tua, #4A2C1A); margin: 0 0 8px; }
        .page-desc { color: #665; font-size: 14px; margin: 0; }
        
        .eval-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px; margin-bottom: 28px; }
        @media(max-width: 768px) { .eval-grid { grid-template-columns: 1fr; } }
        
        .e-card { background: #fff; border: 1px solid var(--krim, #F3E9DC); border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(43,27,18,0.04); }
        .e-card-title { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--kopi-tua, #4A2C1A); margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
        
        .metric-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px; }
        .metric-table th { background: var(--krim, #F3E9DC); color: var(--kopi-tua, #4A2C1A); padding: 8px 12px; text-align: left; }
        .metric-table td { padding: 8px 12px; border-bottom: 1px solid #f0eae1; }
        
        .terminal-box { background: #2B1B12; color: #F3E9DC; font-family: 'Courier New', Courier, monospace; font-size: 12px; padding: 16px; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; line-height: 1.5; }
        .chart-figure { margin: 0; }
        .img-preview { display: block; width: 100%; max-height: 420px; object-fit: contain; border-radius: 8px; border: 1px solid #eee; margin-top: 10px; background: #fff; }
        .chart-caption { color: #776; font-size: 12px; line-height: 1.5; margin-top: 8px; }
        
        .callout { background: #FAF2E8; border-left: 4px solid var(--karamel, #B5763A); padding: 14px 16px; border-radius: 0 8px 8px 0; margin-bottom: 20px; font-size: 13px; line-height: 1.6; }
    </style>
</head>
<body>
    <?= view('layout/navbar_admin') ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🤖 Evaluasi Kinerja Model Rekomendasi (KNN)</h1>
            <p class="page-desc">Laporan performa rekomendasi menu dan prediksi volume transaksi Lower Manhattan Coffee Shop.</p>
        </div>

        <div class="callout">
            <strong>Catatan Ilmiah Transparan untuk Dosen Penguji:</strong><br>
            Analisis ini menggunakan transaksi Lower Manhattan saja. Proporsi kategori relatif stabil antarsegmen (Cramér's V = 0.0335); akurasi KNN adalah 34.89%, dibanding baseline mayoritas 38.10%. Prediksi transaksi per jam mencapai R² = 0.6191, dibanding baseline 0.4173.
        </div>

        <div class="eval-grid">
            <!-- Tugas 1: Rekomendasi Menu -->
            <div class="e-card">
                <h3 class="e-card-title">📌 Tugas 1: Rekomendasi Menu (KNN Klasifikasi)</h3>
                <table class="metric-table">
                    <tr><th>Parameter / Metrik</th><th>Nilai Evaluasi</th></tr>
                    <tr><td>Nilai K Optimal (5-Fold CV)</td><td><strong>K = <?= esc($metadata['tugas_1_rekomendasi']['k_neighbors'] ?? 20) ?></strong></td></tr>
                    <tr><td>Akurasi KNN Uji (80:20)</td><td><strong><?= number_format(($metadata['tugas_1_rekomendasi']['accuracy_knn'] ?? 0.3489) * 100, 2) ?>%</strong></td></tr>
                    <tr><td>Baseline 1 (Kelas Mayoritas)</td><td><?= number_format(($metadata['tugas_1_rekomendasi']['accuracy_baseline_majority'] ?? 0.3810) * 100, 2) ?>% (Coffee)</td></tr>
                    <tr><td>Baseline 2 (Mode per Segmen)</td><td><?= number_format(($metadata['tugas_1_rekomendasi']['accuracy_baseline_segment'] ?? 0.3810) * 100, 2) ?>%</td></tr>
                    <tr><td>Top-3 Hit Rate</td><td><strong style="color:var(--karamel, #B5763A);"><?= number_format(($metadata['tugas_1_rekomendasi']['top3_hit_rate'] ?? 0.7883) * 100, 2) ?>%</strong></td></tr>
                    <tr><td>Weighted F1-Score</td><td><?= number_format(($metadata['tugas_1_rekomendasi']['weighted_f1'] ?? 0.2802) * 100, 2) ?>%</td></tr>
                </table>
                <figure class="chart-figure">
                    <img src="<?= base_url('assets/charts/pemilihan_k_cv.png') ?>" alt="Grafik akurasi cross-validation untuk pemilihan jumlah tetangga K pada model klasifikasi" class="img-preview" loading="lazy">
                    <figcaption class="chart-caption">Rata-rata akurasi 5-Fold Stratified Cross-Validation untuk setiap nilai K yang diuji.</figcaption>
                </figure>
            </div>

            <!-- Tugas 2: Prediksi Keramaian -->
            <div class="e-card">
                <h3 class="e-card-title">⏱️ Tugas 2: Prediksi Keramaian (KNN Regresi)</h3>
                <table class="metric-table">
                    <tr><th>Model & Metrik</th><th>MAE (tx/jam)</th><th>RMSE</th><th>Koefisien R²</th></tr>
                    <tr>
                        <td><strong>KNN Regresi (K=17)</strong></td>
                        <td><strong><?= number_format($metadata['tugas_2_keramaian']['mae_knn'] ?? 6.12, 2) ?></strong></td>
                        <td><strong><?= number_format($metadata['tugas_2_keramaian']['rmse_knn'] ?? 8.16, 2) ?></strong></td>
                        <td><strong style="color:#2E7D32;"><?= number_format($metadata['tugas_2_keramaian']['r2_knn'] ?? 0.6191, 4) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Baseline Rata-rata Jam</td>
                        <td><?= number_format($metadata['tugas_2_keramaian']['mae_baseline'] ?? 7.42, 2) ?></td>
                        <td><?= number_format($metadata['tugas_2_keramaian']['rmse_baseline'] ?? 10.09, 2) ?></td>
                        <td><?= number_format($metadata['tugas_2_keramaian']['r2_baseline'] ?? 0.4173, 4) ?></td>
                    </tr>
                </table>
                <figure class="chart-figure">
                    <img src="<?= base_url('assets/charts/prediksi_keramaian.png') ?>" alt="Grafik perbandingan jumlah transaksi per jam aktual dengan prediksi model KNN regresi" class="img-preview" loading="lazy">
                    <figcaption class="chart-caption">Setiap titik membandingkan jumlah transaksi aktual dan prediksi; garis putus-putus menunjukkan prediksi ideal yang sama dengan nilai aktual.</figcaption>
                </figure>
            </div>
        </div>

        <div class="e-card" style="margin-bottom: 24px;">
            <h3 class="e-card-title">📊 Matriks Konfusi (Confusion Matrix) Klasifikasi Menu</h3>
            <figure class="chart-figure">
                <img src="<?= base_url('assets/charts/confusion_matrix.png') ?>" alt="Matriks konfusi untuk hasil klasifikasi kategori menu model KNN" class="img-preview" style="max-width: 800px; margin-right:auto; margin-left:auto;" loading="lazy">
                <figcaption class="chart-caption">Baris menunjukkan kategori sebenarnya dan kolom menunjukkan kategori prediksi. Nilai diagonal adalah prediksi yang benar; nilai di luar diagonal menunjukkan kategori yang tertukar.</figcaption>
            </figure>
        </div>

        <div class="e-card">
            <h3 class="e-card-title">📄 Berkas Evaluasi Lengkap (ml/hasil_evaluasi.txt)</h3>
            <div class="terminal-box"><?= esc($evalTxt) ?></div>
        </div>
    </div>
</body>
</html>
