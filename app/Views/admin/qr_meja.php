<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate QR Meja — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .qr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--sp-6);
        margin-top: var(--sp-6);
    }
    .tent-card {
        background: var(--ivory);
        border: 2px solid var(--beige);
        border-radius: var(--r-xl);
        padding: var(--sp-6);
        text-align: center;
        box-shadow: var(--shadow-md);
        transition: all var(--dur) var(--ease);
        position: relative;
        overflow: hidden;
    }
    .tent-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--terracotta);
    }
    .tent-header {
        font-family: var(--font-heading);
        font-size: 1.35rem;
        color: var(--espresso);
        font-weight: 700;
        margin-bottom: 4px;
    }
    .tent-sub {
        font-size: var(--fs-xs);
        color: var(--taupe);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: var(--sp-4);
    }
    .qr-image-wrap {
        background: #fff;
        padding: 16px;
        border-radius: var(--r-lg);
        display: inline-block;
        border: 1px solid var(--sand);
        box-shadow: var(--shadow-sm);
        margin-bottom: var(--sp-4);
    }
    .qr-image-wrap img {
        width: 170px;
        height: 170px;
        display: block;
    }
    .link-box-beige {
        background: var(--cream);
        border: 1px dashed var(--beige);
        padding: 8px 12px;
        border-radius: var(--r-sm);
        font-size: 11px;
        color: var(--mocha);
        word-break: break-all;
        margin-bottom: var(--sp-4);
        font-family: monospace;
    }
    .btn-qr-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: var(--r-btn);
        font-size: var(--fs-xs);
        font-weight: 600;
        text-decoration: none;
        margin: 3px;
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-view-beige {
        background: var(--sand);
        color: var(--espresso);
        border: 1px solid var(--beige);
    }
    .btn-view-beige:hover {
        background: var(--beige);
        color: var(--espresso);
    }
    .btn-download-beige {
        background: var(--grad-btn);
        color: #fff;
        box-shadow: var(--shadow-btn);
    }
    .btn-download-beige:hover {
        filter: brightness(1.08);
        box-shadow: var(--shadow-btn-hover);
        color: #fff;
    }
    @media print {
        .admin-navbar, .page-header-actions, footer { display: none !important; }
        body { background: #fff !important; }
        .tent-card {
            border: 2px dashed #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_admin') ?>

<div class="container reveal">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="margin:0;">Generate QR Code Meja</h1>
            <p class="text-muted" style="margin:4px 0 0 0;">Cetak kartu meja (tent card) agar pelanggan dapat langsung memesan dari meja.</p>
        </div>
        <div class="page-header-actions">
            <button onclick="window.print()" class="btn btn-neutral btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                🖨️ Cetak Semua QR Meja
            </button>
        </div>
    </div>

    <div style="background:var(--sand); border:1px solid var(--beige); border-radius:var(--r-lg); padding:12px 18px; margin-top:var(--sp-4); display:flex; align-items:center; gap:12px; font-size:var(--fs-sm); color:var(--espresso);">
        <span style="font-size:22px; flex-shrink:0;">💡</span>
        <div>
            <strong>Panduan Meja:</strong> Cetak QR dan letakkan pada tent card masing-masing nomor meja. Pelanggan cukup memindai dengan kamera HP tanpa perlu mengunduh aplikasi untuk memesan langsung.
        </div>
    </div>

    <div class="qr-grid">

        <?php foreach($meja as $m): ?>

            <?php
            $cleanHost = preg_replace('/^https?:\/\//i', '', trim($serverUrl));
            $cleanHost = preg_replace('/[^\w\.\:\-\/]/', '', $cleanHost);
            $tableId = rawurlencode($m['id_meja']);
            $url = $cleanHost !== '' ? 'http://' . $cleanHost . '/order/' . $tableId : base_url('order/' . $tableId);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . rawurlencode($url);
            ?>

            <div class="tent-card">

                <div class="tent-header">
                    🍽️ <?= esc($m['id_meja']) ?>
                </div>

                <div class="tent-sub">
                    Pindai & Pesan Menu
                </div>

                <div class="qr-image-wrap">
                    <img src="<?= esc($qrUrl, 'attr') ?>" alt="QR <?= esc($m['id_meja']) ?>" loading="lazy">
                </div>

                <div class="link-box-beige">
                    <?= esc($url) ?>
                </div>

                <div style="display:flex; justify-content:center; gap:6px;">
                    <a href="<?= esc($url, 'attr') ?>" target="_blank" class="btn-qr-action btn-view-beige">
                        🔗 Tes Link
                    </a>

                    <a href="<?= esc($qrUrl, 'attr') ?>" download="<?= esc($m['id_meja'], 'attr') ?>.png" class="btn-qr-action btn-download-beige">
                        ⬇️ Unduh QR
                    </a>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>
