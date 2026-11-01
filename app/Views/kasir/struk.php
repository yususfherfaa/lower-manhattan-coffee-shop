<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk Pembayaran #<?= esc($order['id_order']) ?> — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
<style>
/* TAMPILAN KERTAS STRUK THERMAL MODERN */
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap');

body {
    font-family: 'Space Mono', 'Courier New', Courier, monospace;
    background: #FAF5EE;
    color: #2b1f17;
    margin: 0;
    padding: 24px 10px;
    display: flex;
    justify-content: center;
}

.receipt-wrapper {
    width: 320px;
    background: #FFFDF9;
    padding: 24px 20px;
    border-radius: 4px;
    box-shadow: 0 4px 16px rgba(74, 52, 38, 0.08);
    border: 1px solid #EFE3D3;
}

.receipt-header {
    text-align: center;
    margin-bottom: 12px;
}
.receipt-header h2 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #4A3426;
}
.receipt-header p {
    margin: 2px 0;
    font-size: 11px;
    color: #6d5444;
}

.dashed-line {
    border: none;
    border-top: 1px dashed #B08F6E;
    margin: 10px 0;
}

.receipt-meta {
    font-size: 11px;
    line-height: 1.6;
    color: #4A3426;
}
.receipt-meta div {
    display: flex;
    justify-content: space-between;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
td {
    padding: 4px 0;
    vertical-align: top;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}

.total-row {
    font-size: 13px;
    font-weight: 700;
    color: #4A3426;
}

.receipt-footer {
    text-align: center;
    margin-top: 14px;
    font-size: 11px;
    color: #8B6B4F;
    line-height: 1.5;
}

.action-buttons {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}
.action-btn {
    flex: 1;
    padding: 8px;
    text-align: center;
    border-radius: 6px;
    font-size: 11px;
    font-family: sans-serif;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    border: none;
}
.btn-print {
    background: #C47F5E;
    color: #fff;
}
.btn-back {
    background: #EFE3D3;
    color: #4A3426;
}

@media print {
    body {
        background: #fff !important;
        padding: 0 !important;
    }
    .receipt-wrapper {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 58mm;
    }
    .action-buttons {
        display: none !important;
    }
}
</style>
</head>

<body onload="window.print()">

<div class="receipt-wrapper">

    <div class="receipt-header">
        <h2><?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></h2>
        <p><?= esc(setting('alamat') ?? 'Lower Manhattan, New York') ?></p>
        <p><?= esc(setting('kontak') ?? '-') ?></p>
    </div>

    <hr class="dashed-line">

    <div class="receipt-meta">
        <div><span>No. Order :</span> <strong>#<?= esc($order['id_order']) ?></strong></div>
        <div><span>Tanggal   :</span> <span><?= esc($order['created_at']) ?></span></div>
        <div><span>Kasir     :</span> <span><?= esc($payment['nama_kasir'] ?? '-') ?></span></div>
        <div><span>Meja      :</span> <strong><?= esc($order['id_meja']) ?></strong></div>
        <div><span>Pelanggan :</span> <span><?= esc($order['nama_pelanggan']) ?></span></div>
    </div>

    <hr class="dashed-line">

    <table>
        <?php foreach($items as $i): ?>
        <tr>
            <td>
                <?php
                    $ukuranLabel = catalog_product_size_label($i);
                    $namaMenu = catalog_product_display_name($i);
                ?>
                <?= esc($namaMenu) ?>
                <?php if ($ukuranLabel !== null): ?>
                    <br><small style="color:#8B6B4F;">Ukuran: <?= esc($ukuranLabel) ?></small>
                <?php endif; ?>
                <?php if (!empty($i['opsi'])): ?>
                    <br><small style="color:#8B6B4F;">Varian: <?= esc($i['opsi']) ?></small>
                <?php endif; ?>
                <?php if (!empty($i['catatan'])): ?>
                    <br><small style="color:#8B6B4F;">Catatan: <?= esc($i['catatan']) ?></small>
                <?php endif; ?>
                <?php if (!empty($i['suhu'])): ?>
                    <br><small style="color:#8B6B4F;"><?= $i['suhu'] === 'panas' ? 'Panas' : 'Es' ?></small>
                <?php endif; ?>
                <br>
                    <small style="color:#8B6B4F;"><?= (int) $i['qty'] ?> x Rp <?= number_format($i['harga'], 0, ',', '.') ?></small>
            </td>
            <td class="text-right">
                Rp <?= number_format($i['subtotal'], 0, ',', '.') ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <hr class="dashed-line">

    <table>
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="text-right">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><?= esc(strtoupper($payment['metode_pembayaran'] ?? 'CASH')) ?></td>
            <td class="text-right">Rp <?= number_format($payment['uang_dibayar'] ?? 0, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>KEMBALIAN</td>
            <td class="text-right">Rp <?= number_format($payment['kembalian'] ?? 0, 0, ',', '.') ?></td>
        </tr>
    </table>

    <hr class="dashed-line">

    <div class="receipt-footer">
        Terima kasih atas kunjungannya! 🙏<br>
        Selamat menikmati hidangan kami.
    </div>

    <div class="action-buttons">
        <button onclick="window.print()" class="action-btn btn-print">🖨️ Cetak Ulang</button>
        <a href="<?= base_url('/kasir/pesanan') ?>" class="action-btn btn-back">← Selesai</a>
    </div>

</div>

<script>
setTimeout(function(){
    window.location.href = "<?= base_url('/kasir/pesanan') ?>";
}, 10000);
</script>

</body>
</html>
