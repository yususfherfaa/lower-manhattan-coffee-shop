<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tingkat Penerimaan Rekomendasi — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .page-header {
        margin-bottom: var(--sp-6);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: var(--sp-4);
    }
    .page-header h1 {
        font-size: var(--fs-h1);
        margin: 0;
        color: var(--espresso);
    }
    .page-header p {
        color: var(--taupe);
        margin: 6px 0 0 0;
        font-size: var(--fs-sm);
        max-width: 650px;
        line-height: 1.5;
    }
    .info-banner {
        background: var(--sand);
        border: 1px solid var(--beige);
        border-radius: var(--r-lg);
        padding: var(--sp-4) var(--sp-5);
        margin-bottom: var(--sp-6);
        display: flex;
        align-items: center;
        gap: var(--sp-4);
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .info-banner-icon {
        font-size: 24px;
        flex-shrink: 0;
    }
    .stats-grid-recom {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: var(--sp-4);
        margin-bottom: var(--sp-6);
    }
    .stat-card-recom {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-lg);
        padding: var(--sp-5);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: var(--sp-4);
        transition: all var(--dur) var(--ease);
    }
    .stat-card-recom:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--beige);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--r-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .icon-impression { background: rgba(196, 127, 94, 0.15); color: var(--terracotta); }
    .icon-click { background: rgba(124, 154, 142, 0.2); color: var(--sage); }
    .icon-rate { background: rgba(179, 139, 109, 0.2); color: var(--amber); }
    .icon-peak { background: rgba(43, 27, 23, 0.1); color: var(--espresso); }

    .stat-detail h3 {
        color: var(--taupe);
        font-size: var(--fs-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 4px 0;
        font-weight: 600;
    }
    .stat-detail .val {
        color: var(--espresso);
        font-family: var(--font-heading);
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.1;
    }

    .content-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: var(--sp-6);
    }
    @media (max-width: 900px) {
        .content-layout {
            grid-template-columns: 1fr;
        }
    }

    .card-panel {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-xl);
        padding: var(--sp-6);
        box-shadow: var(--shadow-md);
    }
    .card-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--sp-4);
        border-bottom: 1px solid var(--sand);
        padding-bottom: var(--sp-3);
    }
    .card-panel-header h2 {
        font-size: var(--fs-h3);
        margin: 0;
        color: var(--espresso);
    }

    .table-recom {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--fs-sm);
    }
    .table-recom thead th {
        background: var(--espresso);
        color: var(--cream);
        font-weight: 600;
        text-align: left;
        padding: 12px 14px;
        border-radius: var(--r-sm) var(--r-sm) 0 0;
    }
    .table-recom thead th:first-child { border-radius: var(--r-sm) 0 0 0; }
    .table-recom thead th:last-child { border-radius: 0 var(--r-sm) 0 0; }
    .table-recom tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--sand);
        color: var(--espresso);
    }
    .table-recom tbody tr:hover td {
        background: rgba(226, 205, 177, 0.2);
    }

    .rate-bar-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rate-bar-bg {
        flex: 1;
        height: 8px;
        background: var(--sand);
        border-radius: var(--r-pill);
        overflow: hidden;
    }
    .rate-bar-fill {
        height: 100%;
        background: var(--grad-btn);
        border-radius: var(--r-pill);
    }

    .badge-time {
        display: inline-block;
        padding: 3px 8px;
        background: var(--sand);
        border-radius: var(--r-pill);
        font-weight: 700;
        font-size: 11px;
    }
    .badge-percent {
        font-weight: 700;
        color: var(--terracotta);
        min-width: 45px;
        text-align: right;
    }

    .top-item-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .top-item-row {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px dashed var(--sand);
    }
    .top-item-row:last-child {
        border-bottom: none;
    }
    .top-item-name {
        font-weight: 600;
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .top-item-count {
        background: var(--sage-light);
        color: var(--sage);
        padding: 2px 8px;
        border-radius: var(--r-pill);
        font-weight: 700;
        font-size: var(--fs-xs);
        margin-left: auto;
        white-space: nowrap;
    }
    .top-item-image {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        object-fit: cover;
        border-radius: var(--r-md);
        border: 1px solid var(--sand);
        background: var(--cream);
    }
    .top-item-copy {
        min-width: 0;
    }
    .top-item-category {
        display: block;
        margin-top: 3px;
        color: var(--taupe);
        font-size: var(--fs-xs);
    }
    </style>
</head>
<body>

<?= view('layout/navbar_admin') ?>

<main class="container reveal" style="padding-top:var(--sp-6); padding-bottom:var(--sp-8);">

    <div class="page-header">
        <div>
            <h1>Tingkat Penerimaan Rekomendasi</h1>
            <p>
                Laporan evaluasi efektivitas modul <strong>Machine Learning (K-Nearest Neighbor)</strong>. Memantau seberapa sering menu yang direkomendasikan pada jam tertentu diminati dan dipilih oleh pelanggan saat memesan dari meja.
            </p>
        </div>
        <div>
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-neutral btn-sm">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="info-banner">
        <div class="info-banner-icon">💡</div>
        <div>
            <strong>Fungsi Halaman:</strong> Ketika pelanggan membuka menu meja, sistem menampilkan 5 rekomendasi berbasis waktu (<em>Impression / Ditampilkan</em>). Jika pelanggan menekan salah satu rekomendasi untuk masuk ke keranjang, interaksi dicatat sebagai <em>Dipilih (Conversion)</em> untuk mengukur relevansi menu.
        </div>
    </div>

    <!-- KARTU STATISTIK RINGKASAN -->
    <div class="stats-grid-recom">
        <div class="stat-card-recom">
            <div class="stat-icon icon-impression">👁️</div>
            <div class="stat-detail">
                <h3>Total Ditampilkan</h3>
                <div class="val"><?= number_format($totalDitampilkan, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="stat-card-recom">
            <div class="stat-icon icon-click">🛒</div>
            <div class="stat-detail">
                <h3>Total Dipilih</h3>
                <div class="val"><?= number_format($totalDipilih, 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="stat-card-recom">
            <div class="stat-icon icon-rate">🎯</div>
            <div class="stat-detail">
                <h3>Penerimaan Rata-Rata</h3>
                <div class="val"><?= number_format($tingkatRataRata, 1, ',', '.') ?>%</div>
            </div>
        </div>
        <div class="stat-card-recom">
            <div class="stat-icon icon-peak">⏰</div>
            <div class="stat-detail">
                <h3>Jam Teraktif</h3>
                <div class="val"><?= esc($jamTerbaik) ?></div>
            </div>
        </div>
    </div>

    <?php if (!$available): ?>
        <div role="status" style="background:var(--brick-light);color:var(--brick);padding:16px;border-radius:var(--r-md);margin-top:16px;">
            ⚠️ Tabel log rekomendasi belum tersedia di database. Pastikan migrasi berkas <code>database/migrasi_recommendation_logs.sql</code> telah dijalankan.
        </div>
    <?php elseif ($rows === []): ?>
        <div class="card-panel" style="text-align:center;padding:40px 20px;">
            <div style="font-size:40px;margin-bottom:12px;">📊</div>
            <h3 style="margin:0 0 6px;">Belum Ada Data Log Rekomendasi</h3>
            <p class="text-muted" style="max-width:500px;margin:0 auto;">
                Data interaksi rekomendasi akan otomatis tercatat ketika pelanggan membuka tautan pesanan meja (<code>/order/MEJA...</code>) dan melihat atau memilih rekomendasi menu.
            </p>
        </div>
    <?php else: ?>

        <div class="content-layout">

            <!-- PANEL TABEL RINCIAN PER JAM -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <h2>Distribusi Rekomendasi per Jam Operasional</h2>
                    <span class="text-muted" style="font-size:var(--fs-xs);">Periode 24 Jam</span>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table-recom">
                        <thead>
                            <tr>
                                <th style="width:15%;">Jam</th>
                                <th style="width:20%;text-align:center;">Ditampilkan</th>
                                <th style="width:20%;text-align:center;">Dipilih</th>
                                <th style="width:45%;">Tingkat Penerimaan (CTR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td>
                                        <span class="badge-time">
                                            <?= esc(str_pad((string) $row['jam'], 2, '0', STR_PAD_LEFT)) ?>.00 WIB
                                        </span>
                                    </td>
                                    <td style="text-align:center;font-weight:600;">
                                        <?= esc((string) $row['ditampilkan']) ?> kali
                                    </td>
                                    <td style="text-align:center;font-weight:600;color:var(--sage);">
                                        <?= esc((string) $row['dipilih']) ?> kali
                                    </td>
                                    <td>
                                        <div class="rate-bar-container">
                                            <div class="rate-bar-bg">
                                                <div class="rate-bar-fill" style="width: <?= min(100, (float)$row['tingkat']) ?>%;"></div>
                                            </div>
                                            <span class="badge-percent">
                                                <?= esc(number_format((float) $row['tingkat'], 1, ',', '.')) ?>%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL INFORMASI TAMBAHAN & TOP PRODUK -->
            <div style="display:flex; flex-direction:column; gap:var(--sp-5);">

                <div class="card-panel">
                    <div class="card-panel-header">
                        <h2>Menu Rekomendasi Terfavorit</h2>
                    </div>
                    <?php if (empty($topProducts)): ?>
                        <p class="text-muted" style="font-size:var(--fs-sm);margin:0;">Belum ada menu yang dipilih dari rekomendasi.</p>
                    <?php else: ?>
                        <ul class="top-item-list">
                            <?php foreach ($topProducts as $item): ?>
                                <li class="top-item-row">
                                    <img
                                        class="top-item-image"
                                        src="<?= esc(product_image_url($item['gambar'] ?? null, $item['kategori'] ?? null)) ?>"
                                        alt="Foto <?= esc($item['nama']) ?>"
                                        loading="lazy"
                                    >
                                    <div class="top-item-copy">
                                        <span class="top-item-name"><?= esc($item['nama']) ?></span>
                                        <?php if (!empty($item['kategori'])): ?>
                                            <span class="top-item-category"><?= esc($item['kategori']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['deskripsi'])): ?>
                                            <span class="top-item-category"><?= esc($item['deskripsi']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="top-item-count"><?= esc($item['jumlah_terpilih']) ?>x dipilih</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p class="text-muted" style="font-size:var(--fs-xs);margin:12px 0 0;">
                        Ilustrasi kategori ditampilkan jika foto khusus produk belum diunggah melalui Manajemen Menu kasir.
                    </p>
                </div>

                <div class="card-panel" style="background:var(--cream);border-color:var(--nude);">
                    <h3 style="font-size:var(--fs-sm);margin:0 0 8px;color:var(--espresso);">ℹ️ Cara Kerja Evaluasi</h3>
                    <p style="font-size:var(--fs-xs);color:var(--taupe);line-height:1.5;margin:0;">
                        Tingkat penerimaan (<em>Acceptance Rate</em>) yang tinggi mengindikasikan model rekomendasi cerdas menyajikan menu yang sesuai dengan selera pelanggan pada rentang waktu tersebut (pagi, siang, sore, atau malam).
                    </p>
                </div>

            </div>

        </div>

    <?php endif; ?>

</main>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>
