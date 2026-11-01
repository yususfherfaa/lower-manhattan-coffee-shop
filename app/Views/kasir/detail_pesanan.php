<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pesanan #<?= esc($order['id_order']) ?> — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .detail-card {
        max-width: 900px;
        margin: var(--sp-6) auto;
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-xl);
        padding: var(--sp-8);
        box-shadow: var(--shadow-lg);
    }
    .order-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--sp-4);
        background: var(--cream);
        padding: var(--sp-4) var(--sp-5);
        border-radius: var(--r-md);
        margin: var(--sp-4) 0 var(--sp-6) 0;
        border: 1px solid var(--sand);
    }
    .order-meta-item small {
        display: block;
        color: var(--taupe);
        font-size: var(--fs-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 2px;
    }
    .order-meta-item strong {
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .table-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--sp-4);
        border-radius: var(--r-md);
        overflow: hidden;
    }
    .table-items th {
        background: var(--espresso);
        color: var(--cream);
        padding: 12px 14px;
        font-size: var(--fs-sm);
        text-align: left;
    }
    .table-items td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--sand);
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .table-items tr:nth-child(even) td {
        background: rgba(250, 245, 238, 0.5);
    }
    .summary-box-beige {
        margin-top: var(--sp-5);
        padding: var(--sp-5);
        background: var(--cream);
        border: 1px solid var(--sand);
        border-radius: var(--r-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: var(--fs-h3);
        font-family: var(--font-heading);
        font-weight: 700;
        color: var(--espresso);
    }
    .payment-box-beige {
        margin-top: var(--sp-6);
        padding: var(--sp-5);
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-md);
        box-shadow: var(--shadow-sm);
    }
    .payment-box-beige h3 {
        margin: 0 0 var(--sp-3) 0;
        font-size: 1.1rem;
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<div class="container reveal">

    <div class="detail-card">
        <?php $isPaid = !empty($payment); ?>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <a href="<?= base_url('/kasir/pesanan') ?>" class="btn btn-neutral btn-sm" style="background:var(--sand); color:var(--espresso); border:1px solid var(--beige);">
                ← Kembali ke Daftar Pesanan
            </a>
            <?php if ($isPaid): ?>
                <a href="<?= base_url('/kasir/struk/'.$order['id_order']) ?>" class="btn btn-neutral btn-sm" style="background:var(--sage); color:#fff;">
                    🧾 Cetak Struk
                </a>
            <?php else: ?>
                <a href="<?= base_url('/kasir/bayar/'.$order['id_order']) ?>" class="btn btn-sm">
                    💳 Proses Bayar
                </a>
            <?php endif; ?>
        </div>

        <h1 style="margin:var(--sp-5) 0 var(--sp-2) 0; font-size:var(--fs-h2);">
            Detail Pesanan #<?= esc($order['id_order']) ?>
        </h1>

        <div class="order-meta-grid">
            <div class="order-meta-item">
                <small>Meja</small>
                <strong>🍽️ <?= esc($order['id_meja']) ?></strong>
            </div>
            <div class="order-meta-item">
                <small>Nama Pelanggan</small>
                <strong>👤 <?= esc($order['nama_pelanggan']) ?></strong>
            </div>
            <div class="order-meta-item">
                <small>Waktu Pemesanan</small>
                <strong>⏱️ <?= esc($order['created_at']) ?></strong>
            </div>
            <div class="order-meta-item">
                <small>Status</small>
                <strong style="color:<?= $isPaid ? 'var(--sage)' : 'var(--amber)' ?>;">
                    <?= $isPaid ? '✓ Lunas' : '⏳ Menunggu Pembayaran' ?>
                </strong>
            </div>
        </div>

        <h3 style="margin-bottom:var(--sp-2);">Rincian Item</h3>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th style="text-align:center; width:80px;">Qty</th>
                    <th style="text-align:right; width:140px;">Harga Satuan</th>
                    <th style="text-align:right; width:140px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $i): ?>
                <tr>
                    <td>
                        <?php
                            $ukuranLabel = catalog_product_size_label($i);
                            $namaMenu = catalog_product_display_name($i);
                        ?>
                        <strong><?= esc($namaMenu) ?></strong>
                        <?php if ($ukuranLabel !== null): ?>
                            <div style="margin-top:3px;color:var(--taupe);font-size:var(--fs-xs);">Ukuran: <?= esc($ukuranLabel) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($i['opsi'])): ?>
                            <div style="margin-top:3px;color:var(--terracotta);font-size:var(--fs-xs);">Varian: <?= esc($i['opsi']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($i['catatan'])): ?>
                            <div style="margin-top:3px;color:var(--espresso);font-size:var(--fs-xs);"><strong>Catatan:</strong> <?= esc($i['catatan']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($i['suhu'])): ?>
                            <div style="margin-top:3px;color:var(--taupe);font-size:var(--fs-xs);"><?= $i['suhu'] === 'panas' ? 'Panas' : 'Es' ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;"><?= esc($i['qty']) ?></td>
                    <td style="text-align:right;">Rp <?= number_format($i['harga'], 0, ',', '.') ?></td>
                    <td style="text-align:right; font-weight:600;">Rp <?= number_format($i['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-box-beige">
            <span>Total Tagihan</span>
            <span style="color:var(--terracotta);">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
        </div>

        <div class="payment-box-beige">
            <h3>Informasi Pembayaran</h3>

            <?php if($payment): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                    <div>
                        <small style="color:var(--taupe);">Metode Pembayaran</small>
                        <p style="margin:2px 0 0 0; font-weight:600;"><?= strtoupper(esc($payment['metode_pembayaran'])) ?></p>
                    </div>
                    <div>
                        <small style="color:var(--taupe);">Uang Diterima</small>
                        <p style="margin:2px 0 0 0; font-weight:600;">Rp <?= number_format($payment['uang_dibayar'], 0, ',', '.') ?></p>
                    </div>
                    <div>
                        <small style="color:var(--taupe);">Kembalian</small>
                        <p style="margin:2px 0 0 0; font-weight:600; color:var(--sage);">Rp <?= number_format($payment['kembalian'], 0, ',', '.') ?></p>
                    </div>
                    <div>
                        <small style="color:var(--taupe);">Kasir Bertugas</small>
                        <p style="margin:2px 0 0 0; font-weight:600;"><?= esc($payment['nama_kasir'] ?? '-') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div style="background:var(--amber-light); color:var(--amber); padding:var(--sp-3) var(--sp-4); border-radius:var(--r-sm); border:1px solid var(--amber); font-weight:600;">
                    ⏳ Pesanan ini belum diselesaikan pembayarannya.
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>