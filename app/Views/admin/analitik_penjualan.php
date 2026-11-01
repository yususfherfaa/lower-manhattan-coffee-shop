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
        
        .metric-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .m-card { background: #fff; border: 1px solid var(--krim, #F3E9DC); border-radius: 12px; padding: 18px; box-shadow: 0 4px 12px rgba(43,27,18,0.04); }
        .m-card-title { font-size: 13px; color: #776; text-transform: uppercase; font-weight: 600; margin-bottom: 8px; }
        .m-card-value { font-size: 24px; font-weight: 700; color: var(--karamel, #B5763A); }
        .m-card-sub { font-size: 12px; color: #887; margin-top: 4px; }
        
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 24px; }
        @media(max-width: 768px) { .analytics-grid { grid-template-columns: 1fr; } }
        
        .a-card { background: #fff; border: 1px solid var(--krim, #F3E9DC); border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(43,27,18,0.04); }
        .a-card-title { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--kopi-tua, #4A2C1A); margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
        
        .table-responsive { width: 100%; overflow-x: auto; }
        table.custom-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.custom-tbl th { background: var(--krim, #F3E9DC); color: var(--kopi-tua, #4A2C1A); text-align: left; padding: 10px 12px; font-weight: 600; }
        table.custom-tbl td { padding: 9px 12px; border-bottom: 1px solid #f0eae1; }
        table.custom-tbl tr:hover { background-color: #faf6f0; }
        
        .badge-crowd { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-sepi { background: #E8F5E9; color: #2E7D32; }
        .badge-sedang { background: #FFF3E0; color: #E65100; }
        .badge-ramai { background: #FFEBEE; color: #C62828; }
        
        .chart-figure { margin: 0; }
        .img-preview { display: block; width: 100%; max-height: 420px; object-fit: contain; border-radius: 8px; border: 1px solid #eee; margin-top: 10px; background: #fff; }
        .chart-caption { color: #776; font-size: 12px; line-height: 1.5; margin-top: 8px; }
    </style>
</head>
<body>
    <?= view('layout/navbar_admin') ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">☕ Analitik Pola Penjualan & Keramaian Coffee Shop</h1>
            <p class="page-desc">Visualisasi pola penjualan dataset Maven Roasters yang sudah dibersihkan untuk cabang Lower Manhattan. Grafik berikut merangkum distribusi transaksi dan kepadatan kunjungan berdasarkan data historis.</p>
        </div>

        <div class="metric-cards">
            <div class="m-card">
                <div class="m-card-title">Dataset Acuan</div>
                <div class="m-card-value"><?= $summary['total_transactions'] > 0 ? number_format($summary['total_transactions'], 0, ',', '.') : '-' ?></div>
                <div class="m-card-sub">Transaksi Lower Manhattan yang sudah dibersihkan</div>
            </div>
            <div class="m-card">
                <div class="m-card-title">Kategori Terpopuler</div>
                <div class="m-card-value">
                    <?php if ($summary['top_category'] !== null && $summary['top_category_share'] !== null): ?>
                        <?= esc($summary['top_category']) ?> (<?= number_format($summary['top_category_share'], 1, ',', '.') ?>%)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="m-card-sub">Proporsi transaksi pada kategori teratas</div>
            </div>
            <div class="m-card">
                <div class="m-card-title">Puncak Jam Keramaian</div>
                <div class="m-card-value"><?= $summary['peak_hour'] !== null ? sprintf('%02d:00 – %02d:59', $summary['peak_hour'], $summary['peak_hour']) : '-' ?></div>
                <div class="m-card-sub">
                    <?= $summary['peak_hour_transactions'] !== null ? number_format($summary['peak_hour_transactions'], 0, ',', '.') . ' transaksi pada periode dataset' : 'Belum ada data transaksi' ?>
                </div>
            </div>
            <div class="m-card">
                <div class="m-card-title">Jam Tutup / Sepi</div>
                <div class="m-card-value"><?= $summary['quiet_hour'] !== null ? sprintf('%02d:00 – %02d:59', $summary['quiet_hour'], $summary['quiet_hour']) : '-' ?></div>
                <div class="m-card-sub">
                    <?= $summary['quiet_hour_average'] !== null ? 'Rata-rata ' . number_format($summary['quiet_hour_average'], 1, ',', '.') . ' transaksi/hari' : 'Belum ada data transaksi' ?>
                </div>
            </div>
        </div>

        <div class="analytics-grid">
            <!-- Grafik Pola Jam -->
            <div class="a-card">
                <h3 class="a-card-title">📈 Pola Transaksi per Jam Operasional</h3>
                <p style="font-size:13px; color:#666;">Grafik batang menunjukkan jumlah transaksi historis per jam operasional. Gunakan untuk melihat jam dengan volume tertinggi dan perubahan aktivitas menjelang tutup.</p>
                <figure class="chart-figure">
                    <img src="<?= base_url('assets/charts/distribusi_jam.png') ?>" alt="Grafik distribusi jumlah transaksi Lower Manhattan per jam operasional" class="img-preview" loading="lazy">
                    <figcaption class="chart-caption">Sumber: hasil olah dataset transaksi Maven Roasters, difilter ke Store Location Lower Manhattan.</figcaption>
                </figure>
            </div>

            <!-- Heatmap Kepadatan -->
            <div class="a-card">
                <h3 class="a-card-title">🔥 Heatmap Kepadatan Hari vs Jam</h3>
                <p style="font-size:13px; color:#666;">Heatmap membandingkan volume transaksi pada setiap kombinasi hari dan jam. Warna yang lebih gelap berarti jumlah transaksi relatif lebih tinggi.</p>
                <figure class="chart-figure">
                    <img src="<?= base_url('assets/charts/heatmap_hari_jam.png') ?>" alt="Heatmap kepadatan transaksi Lower Manhattan menurut hari dan jam" class="img-preview" loading="lazy">
                    <figcaption class="chart-caption">Sumber: hasil olah dataset transaksi Maven Roasters, difilter ke Store Location Lower Manhattan.</figcaption>
                </figure>
            </div>

            <!-- Tabel Agregat Jam -->
            <div class="a-card">
                <h3 class="a-card-title">⏱️ Ringkasan Rata-rata Keramaian per Jam</h3>
                <div class="table-responsive">
                    <table class="custom-tbl">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Rata-rata Transaksi</th>
                                <th>Total Penjualan (Qty)</th>
                                <th>Status Keramaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($aggJam)): ?>
                                <?php 
                                // Group by jam to display 15 operational hours
                                $jamSummary = [];
                                foreach ($aggJam as $r) {
                                    $j = $r['jam'];
                                    if (!isset($jamSummary[$j])) {
                                        $jamSummary[$j] = ['avg' => 0, 'qty' => 0, 'label' => $r['label_keramaian'], 'count' => 0];
                                    }
                                    $jamSummary[$j]['avg'] += $r['avg_transaksi'];
                                    $jamSummary[$j]['qty'] += $r['total_qty'];
                                    $jamSummary[$j]['count']++;
                                }
                                foreach ($jamSummary as $j => $vals): 
                                    $avgVal = round($vals['avg'] / max(1, $vals['count']), 1);
                                    $badgeCls = $avgVal > 35 ? 'badge-ramai' : ($avgVal > 15 ? 'badge-sedang' : 'badge-sepi');
                                ?>
                                <tr>
                                    <td><strong><?= sprintf('%02d:00 - %02d:59', $j, $j) ?> WIB</strong></td>
                                    <td><?= number_format($avgVal, 1) ?> tx/hari</td>
                                    <td><?= number_format($vals['qty']) ?> pcs</td>
                                    <td><span class="badge-crowd <?= $badgeCls ?>"><?= $avgVal > 35 ? 'Ramai' : ($avgVal > 15 ? 'Sedang' : 'Sepi') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; color:#999;">Belum ada data tabel agregat. Jalankan impor SQL Plan B terlebih dahulu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Proporsi Kategori per Segmen -->
            <div class="a-card">
                <h3 class="a-card-title">🍰 Komposisi Kategori Produk per Segmen Waktu</h3>
                <div class="table-responsive">
                    <table class="custom-tbl">
                        <thead>
                            <tr>
                                <th>Segmen</th>
                                <th>Kategori</th>
                                <th>Total Terjual</th>
                                <th>Proporsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($aggKat)): ?>
                                <?php foreach (array_slice($aggKat, 0, 12) as $row): ?>
                                <tr>
                                    <td><span style="text-transform:capitalize; font-weight:600;"><?= esc($row['segmen']) ?></span></td>
                                    <td><?= esc($row['product_category']) ?></td>
                                    <td><?= number_format($row['total_penjualan']) ?> item</td>
                                    <td><strong><?= number_format($row['proporsi'] * 100, 1) ?>%</strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; color:#999;">Belum ada data kategori segmen.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
