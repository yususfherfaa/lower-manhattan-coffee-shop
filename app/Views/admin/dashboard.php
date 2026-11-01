<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin — <?= esc($nama_warkop) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .dashboard-header {
        margin-bottom: var(--sp-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--sp-4);
    }
    .dashboard-header h1 {
        font-size: var(--fs-h1);
        margin: 0;
    }
    .dashboard-header p {
        color: var(--taupe);
        margin: 4px 0 0 0;
        font-size: var(--fs-sm);
    }
    .stats-grid-admin {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--sp-5);
        margin-bottom: var(--sp-8);
    }
    .stat-card-beige {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-lg);
        padding: var(--sp-6);
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: var(--sp-4);
        transition: all var(--dur) var(--ease);
    }
    .stat-card-beige:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--beige);
    }
    .stat-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--sand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
        border: 2px solid var(--beige);
    }
    .stat-meta {
        flex: 1;
        min-width: 0;
    }
    .stat-meta h3 {
        color: var(--taupe);
        font-size: var(--fs-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 6px 0;
        font-family: var(--font-body);
        font-weight: 600;
    }
    .stat-meta .stat-num {
        color: var(--espresso);
        font-family: var(--font-heading);
        font-size: 1.85rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }
    .action-grid-admin {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: var(--sp-4);
        margin-top: var(--sp-4);
    }
    .action-card {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-md);
        padding: var(--sp-5);
        text-decoration: none;
        color: var(--espresso);
        display: flex;
        align-items: center;
        gap: var(--sp-4);
        transition: all var(--dur-fast) var(--ease);
        box-shadow: var(--shadow-sm);
    }
    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--terracotta);
        background: #fff;
    }
    .action-card .action-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--r-sm);
        background: var(--grad-btn);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .action-card .action-text {
        font-weight: 600;
        font-size: var(--fs-sm);
        color: var(--espresso);
    }
    .action-card .action-arrow {
        margin-left: auto;
        color: var(--taupe);
        font-size: 14px;
        transition: transform var(--dur-fast) var(--ease);
    }
    .action-card:hover .action-arrow {
        transform: translateX(4px);
        color: var(--terracotta);
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_admin') ?>

<div class="container reveal">

    <div class="dashboard-header">
        <div>
            <h1>Dashboard Administrator</h1>
            <p>Ringkasan penjualan, kasir, dan inventaris <?= esc($nama_warkop) ?></p>
        </div>
        <div class="text-sm text-muted">
            📅 <?= date('d M Y') ?>
        </div>
    </div>

    <!-- 4 KARTU STATISTIK -->
    <div class="stats-grid-admin">

        <div class="stat-card-beige">
            <div class="stat-icon-wrap">📋</div>
            <div class="stat-meta">
                <h3>Total Pesanan Hari Ini</h3>
                <p class="stat-num count-up"><?= (int)$total_pesanan ?></p>
            </div>
        </div>

        <div class="stat-card-beige">
            <div class="stat-icon-wrap">💰</div>
            <div class="stat-meta">
                <h3>Total Pendapatan Hari Ini</h3>
                <p class="stat-num count-up">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="stat-card-beige">
            <div class="stat-icon-wrap">👥</div>
            <div class="stat-meta">
                <h3>Total Kasir</h3>
                <p class="stat-num count-up"><?= (int)$jumlah_kasir ?></p>
            </div>
        </div>

        <div class="stat-card-beige">
            <div class="stat-icon-wrap">☕</div>
            <div class="stat-meta">
                <h3>Total Menu</h3>
                <p class="stat-num count-up"><?= (int)$jumlah_produk ?></p>
            </div>
        </div>

    </div>

    <!-- MENU AKSI CEPAT -->
    <h2 style="text-align:left; font-size:var(--fs-h3); margin-bottom:var(--sp-4);">Aksi Cepat</h2>

    <div class="action-grid-admin">
        <a href="<?= base_url('/admin/recommendation-acceptance') ?>" class="action-card">
            <div class="action-icon" style="background:var(--button-success);">🎯</div>
            <div>
                <div class="action-text">Efektivitas Rekomendasi ML</div>
                <small class="text-muted" style="font-size:11px;">Tingkat penerimaan rekomendasi per jam</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/admin/qr-meja') ?>" class="action-card">
            <div class="action-icon">📱</div>
            <div>
                <div class="action-text">Generate QR Meja</div>
                <small class="text-muted" style="font-size:11px;">Cetak tent card QR pesanan meja</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/admin/kasir') ?>" class="action-card">
            <div class="action-icon">👥</div>
            <div>
                <div class="action-text">Kelola Kasir</div>
                <small class="text-muted" style="font-size:11px;">Akun kasir aktif & kredensial</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/laporan') ?>" class="action-card">
            <div class="action-icon">📈</div>
            <div>
                <div class="action-text">Laporan Penjualan</div>
                <small class="text-muted" style="font-size:11px;">Rekap transaksi & ekspor PDF</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/laporan-stok') ?>" class="action-card">
            <div class="action-icon">📦</div>
            <div>
                <div class="action-text">Laporan Stok & Menu</div>
                <small class="text-muted" style="font-size:11px;">Persediaan bahan & status menu</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/admin/settings') ?>" class="action-card">
            <div class="action-icon">⚙️</div>
            <div>
                <div class="action-text">Pengaturan Sistem</div>
                <small class="text-muted" style="font-size:11px;">Identitas coffee shop, QRIS & host</small>
            </div>
            <span class="action-arrow">→</span>
        </a>
    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc($nama_warkop) ?> — Dashboard Admin
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>